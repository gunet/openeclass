<?
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$ |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Geschι <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFiles = array('registration', 'admin', 'gunet');
include('../../include/init.php');

$nameTools = $langByLdap;

$navigation[]= array ("url"=>"../admin/", "name"=> $admin);
$navigation[]= array ("url"=>"ldapnewprof.php", "name"=> $regprof);
begin_page();

$found = 0;

if (!isset($userMailCanBeEmpty)) {	
	$userMailCanBeEmpty = true;
} 

$errormessage1 = "
	<tr valign=\"top\" bgcolor=\"$color2\"><td><font size=\"2\" face=\"arial, helvetica\"><p>&nbsp;</p>";
$errormessage3 = "</font><p>&nbsp;</p><br><br><br></td></tr>";
$errormessage2 = "<p>Επιστροφή στην <a href=\"ldapnewprof.php\">προηγούμενη σελίδα</a></p>$errormessage3";
if($is_submit)
{
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
	$emailtohostname = substr( $email, ( strrpos( $email, "@" ) +1 ));
	$emailtohostname = substr( $ldap_email, ( strrpos( $ldap_email, "@" ) +1 ));
if ($ldap_server == '0') {
	 echo $errormessage1.$ldapchoice.$errormessage2;	
} elseif (empty($ldap_email)) {
		echo $errormessage1. $ldapempty. $errormessage2;
} else {

$list = explode("_",$ldap_server);	
$ldapServer = $list[0];
$basedn = $list[1];
$institut_id = $list[2];

	$ds=ldap_connect($ldapServer);  //get the ldapServer, baseDN from the db
	if ($ds) { 
    		$r=@ldap_bind($ds);     // this is an "anonymous" bind, typically
                           		// read-only access
		if ($r) {
    			$mailadd=ldap_search($ds, $basedn, "mail=".$ldap_email);  
    			$info = ldap_get_entries($ds, $mailadd);
			if ($info["count"] == 0) { //Den vre8hke eggrafh
    				echo "$errormessage1 
				      Δεν βρέθηκαν εγγραφές. Πιθανόν να δώσατε λάθος στοιχεία.  
				      $errormessage2";
			} else if ($info["count"] == 1) { //Vre8hke o xrhsths 
				echo "$errormessage1
				   Ο διδάσκοντας βρέθηκε στον εξυπηρέτη LDAP.";
				$cn = explode(" ", $info[0]["cn"][0]);
				$nomvar = $cn[0];
				$prenomvar = $cn[1];
				$emailvar = $info[0]["mail"][0];
				$found = 1;	
			} else { 
    				echo "$errormessage1 Υπάρχει πρόβλημα με τα στοιχεία του διδάσκοντα. Παρακαλούμε επικοινωνήστε με τον 
διαχειριστή του εξυπηρέτη LDAP. 
				$errormessage2";
			}
			if ($info["count"] == 1) {
?>
<tr><td>
<form name="registration" action="newprof_second.php" method="post">
	<input type="hidden" name="institut" value="<? echo $institut_id ?>" >
  	<input type="hidden" name="uname" value="<? echo $emailvar ?>" >
	<input type="hidden" name="nom_form" value="<? echo $nomvar ?>" >
	<input type="hidden" name="prenom_form" value="<? echo $prenomvar ?>" >
	<input type="hidden" name="email_form" value="<? echo $emailvar ?>" >
	<input type="hidden" name="password" value="LDAP user">
	<input type="hidden" name="password1" value="LDAP user">
	
	<table cellpadding="3" cellspacing="0" border="0" width="100%">
		<tr valign="top" bgcolor="<? echo $color2 ?>">
			<td><font size="2" face="arial, helvetica">
				<? echo "Ονοματεπώνυμο:"?>&nbsp;:</font>
			</td>   
			<td>
				<? echo "$nomvar $prenomvar"?>
			</td>
		</tr>
		<tr bgcolor="<? echo $color2;?>">
			<td><font size="2" face="arial, helvetica">
				<? echo $langEmail;?>&nbsp;:</font>
			</td>
			<td>
				<? echo $info[0]["mail"][0] ?> 
			</td>
		</tr>
		<tr bgcolor="<?= $color2;?>">
			<td>
				&nbsp;
			</td>
			<td>
				<input type="submit" name="submit" value="<?= $langOk;?>" >
			</td>
		</tr>
	</table>                        
</form>
</td></tr>
<? 
}
	ldap_close($ds);
} else {
    	echo "<tr><td><h4>$ldaperror</h4></td></tr>";
	}
}
 	else {
    		echo "<tr><td><h4>$ldaperror</h4></td></tr>";
	}

}

}   // end of initial if

?>
</table>
</body>
</html>
