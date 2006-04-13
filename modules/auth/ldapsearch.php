<? 
/*
      +----------------------------------------------------------------------+
      | e-class version 1.0                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$		     |
      +----------------------------------------------------------------------+
      |   $Id$
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
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
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
      |                                                                      |
      | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      +----------------------------------------------------------------------+
 */


$langFiles = array('registration','gunet');
include '../../include/init.php';

$nameTools = $langLDAPUser;
$navigation[]= array ("url"=> "newuser_info.php", "name"=> $reguser);
begin_page();

$found = 0;

if (!isset($userMailCanBeEmpty)) {	
	$userMailCanBeEmpty = true;
} 

// added by adia
// change behaviour depending on $prof 
if (isset($prof) and $prof == 1) {
	// creating prof
	$lastpage = 'ldapnewprof.php';
	$userdescr = $langTheTeacher;
} else {
  // creating user
	$lastpage = 'ldapnewuser.php';
	$userdescr = $langTheUser;
}

$errormessage1 = "<tr valign=\"top\" align=\"center\" bgcolor=\"$color2\"><td><font size=\"2\" face=\"arial, helvetica\"><p>&nbsp;</p>";
$errormessage3 = "</font><p>&nbsp;</p><br><br><br></td></tr>";
$errormessage2 = "<p>$ldapback <a href=\"$lastpage\">$ldaplastpage</a></p>$errormessage3";
if($is_submit)
{
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
	@$emailtohostname = substr( $email, ( strrpos( $email, "@" ) +1 ));
	$emailtohostname = substr( $ldap_email, ( strrpos( $ldap_email, "@" ) +1 ));
if ($ldap_server == '0') {
	echo $errormessage1 . $ldapchoice . $errormessage2;
} elseif (empty($ldap_email) or empty($ldap_passwd)) {
	echo $errormessage1 . $ldapempty . $errormessage2;
} elseif (user_exists($ldap_email)) {
	echo $errormessage1 . $ldapuserexists . $errormessage2;
} else {

		
$list = explode("_",$ldap_server);	
$ldapServer = $list[0];
$basedn = $list[1];
$institut_id = $list[2];


#########- tsipa -  ##############################

	$ds=ldap_connect($ldapServer);  //get the ldapServer, baseDN from the db
	if ($ds) { 
    		$r=@ldap_bind($ds);     // this is an "anonymous" bind, typically
                           		// read-only access
		if ($r) {
    			$mailadd=ldap_search($ds, $basedn, "mail=".$ldap_email);  
    			$info = ldap_get_entries($ds, $mailadd);
			if ($info["count"] == 0) { //Den vre8hke eggrafh
    				echo "$errormessage1 
				      $ldapnorecords  
				      $errormessage2";
				      exit;
			}
			else if ($info["count"] == 1) { // user found
				$authbind=@ldap_bind($ds,$info[0]["dn"],$ldap_passwd);
				if ($authbind) {
					echo "$errormessage1
					   ${userdescr} $ldapfound";
					$cn = explode(" ", $info[0]["cn"][0]);
					$nomvar = $cn[0];
					$prenomvar = $cn[1];
					$emailvar = $info[0]["mail"][0];
					$passwordvar = $ldap_passwd;
					$found = 1;	
				} else {
					echo  $errormessage1.
					      $ldapwrongpasswd.
					      $errormessage2."
					      </table>
					      </body>
					      </html>
					";
				} 
			} // end of user found
		 else { 
    		echo $errormessage1. $ldapproblem. $userdescr.
				      $ldapcontact. 
				      $errormessage2;
					exit;  
		}
if ($info["count"] == 1) {
?>
<tr><td>
<form name="registration" action="newuser_second.php" method="post">
	<input type=hidden name="institut" value=<?= $institut_id ?> >
	<input type=hidden name="nom_form" value=<?= $nomvar ?> >
	<input type=hidden name="prenom_form" value=<?= $prenomvar ?> >
	<input type=hidden name="email" value=<?= $emailvar ?> >
	
	<table cellpadding="3" cellspacing="0" border="0" width="100%">
		<tr valign="top" bgcolor="<?= $color2 ?>">
			<td><font size="2" face="arial, helvetica">
				<? echo $ldapnamesur?>&nbsp;:</font>
			</td>   
			<td>
				<? echo "$nomvar $prenomvar"?>
			</td>
		</tr>
		<tr bgcolor="<?= $color2;?>">
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
		} // end of if info count
	}  // end of bind if
    	ldap_close($ds);
	echo "<tr valign=\"top\" align=\"center\" bgcolor=\"$color2\"><td><br><h4>$ldaperror</h4>";
        echo "<h4>$ldapcontact</h4>";
        echo "<a href=\"../../index.php\">$back</a>";
        echo "<br><br></td></tr>";
} else {
	echo "<tr valign=\"top\" align=\"center\" bgcolor=\"$color2\"><td><br><h4>$ldaperror</h4>";
        echo "<h4>$ldapcontact</h4>";
        echo "<a href=\"../../index.php\">$back</a>";
        echo "<br><br></td></tr>";
	}
}

}   // end of initial if

// Check if a user with usename $login already exists
function user_exists($login) {

global $mysqlMainDb;

	$username_check = mysql_query("
		SELECT username
	        FROM `$mysqlMainDb`.user
	        WHERE username='$login'");
	if (mysql_num_rows($username_check) > 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}

?>
</table>
</body>
</html>
