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
include '../../include/baseTheme.php';
require_once 'auth.inc.php';
//check_admin();
$nameTools = $langByLdap;

$navigation[]= array ("url"=>"../admin/", "name"=> $admin);
$navigation[]= array ("url"=>"ldapnewprof.php", "name"=> $regprof);

$found = 0;

if (!isset($userMailCanBeEmpty)) 
{	
	$userMailCanBeEmpty = true;
} 

$errormessage1 = "<tr valign=\"top\" bgcolor=\"$color2\"><td><font size=\"2\" face=\"arial, helvetica\"><p>&nbsp;</p>";
$errormessage3 = "</font><p>&nbsp;</p><br><br><br></td></tr>";
$errormessage2 = "<p>Επιστροφή στην <a href=\"ldapnewprof.php\">προηγούμενη σελίδα</a></p>$errormessage3";
$is_submit = isset($_POST['is_submit'])?$_POST['is_submit']:'';
$ldap_email = isset($_POST['ldap_email'])?$_POST['ldap_email']:'';
$ldap_passwd = isset($_POST['ldap_passwd'])?$_POST['ldap_passwd']:'';

if(!empty($is_submit))
{
	
	$auth = get_auth_id();
	if (empty($ldap_email) or empty($ldap_passwd)) // check for empty username-password
	{
		$tool_content .= $errormessage1 . $ldapempty . $errormessage2;
		$auth_allow = 0;
	} 
	elseif (user_exists($ldap_email)) // check if the user already exists
	{
		$tool_content .= $errormessage1 . $ldapuserexists . $errormessage2;
		$auth_allow = 0;
	} 
	elseif (user_exists_request($ldap_email)) // check if the user already exists in prof_request
	{
		$tool_content .= "There is already a request for user:$ldap_email<br>Cannot Proceed<br>";
		$auth_allow = 0;
	}
	else 
	{
		// try to authenticate him
		$auth_method_settings = get_auth_settings($auth);		// get the db settings of the authentication method defined
		switch($auth)			// now get the connection settings
		{
			case '2':	$pop3host = str_replace("pop3host=","",$auth_method_settings['auth_settings']);
							break;
			case '3':	$imaphost = str_replace("imaphost=","",$auth_method_settings['auth_settings']);
							break;
			case '4':	$ldapsettings = $auth_method_settings['auth_settings'];
					    $ldap = explode("|",$ldapsettings);
					    $ldaphost = str_replace("ldaphost=","",$ldap[0]);	//ldaphost
					    $ldapbind_dn = str_replace("ldapbind_dn=","",$ldap[1]);	//ldapbase_dn
					    $ldapbind_user = str_replace("ldapbind_user=","",$ldap[2]);	//ldapbind_user
					    $ldapbind_pw = str_replace("ldapbind_pw=","",$ldap[3]);		// ldapbind_pw
							break;
			case '5':	$dbsettings = $auth_method_settings['auth_settings'];
    					$edb = explode("|",$dbsettings);
    					$dbhost = str_replace("dbhost=","",$edb[0]);	//dbhost
    					$dbname = str_replace("dbname=","",$edb[1]);	//dbname
    					$dbuser = str_replace("dbuser=","",$edb[2]);//dbuser
    					$dbpass = str_replace("dbpass=","",$edb[3]);// dbpass
					    $dbtable = str_replace("dbtable=","",$edb[4]);//dbtable
					    $dbfielduser = str_replace("dbfielduser=","",$edb[5]);//dbfielduser
					    $dbfieldpass = str_replace("dbfieldpass=","",$edb[6]);//dbfieldpass
							break;
			default:
							break;
		}
		
		$is_valid = auth_user_login($auth,$ldap_email,$ldap_passwd);
		//$tool_content .= "is_valid: ".$is_valid."<br />";
		if($is_valid)
		{
			//$tool_content .= "<br />Successfully connected<br />";
			$auth_allow = 1;
		}
		else
		{
			$tool_content .= "<br />The connection does not seem to work!!<br />";
			$auth_allow = 0;
		}	
		if($auth_allow==1)
		{	
			$tool_content .= "<form name=\"registration\" action=\"newprof_second.php\" method=\"post\">
						<table width=\"100%\">
							<tr>
								<td>Eπώνυμο:</td>   
								<td><input type=\"text\" name=\"nom_form\" size=\"30\" value=\"\"></td>
							</tr>
							<tr>
								<td>Όνομα:</td>   
								<td><input type=\"text\" name=\"prenom_form\" size=\"30\" value=\"\"></td>
							</tr>
							<tr>
								<td>E-mail:</td>   
								<td><input type=\"text\" name=\"email_form\" size=\"30\" value=\"\"></td>
							</tr>		
							<tr>
								<td>Phone:</td>   
								<td><input type=\"text\" name=\"userphone\" size=\"30\" value=\"\"></td>
							</tr>		
							<tr bgcolor=\"".$color2."\">
        <td>".$profcomment."<br><font size=\"1\">".$profreason."
       	</td>
	<td>
        <textarea name=\"usercomment\" COLS=\"35\" ROWS=\"4\" WRAP=\"SOFT\">".@$usercomment."</textarea>
	<font size=\"1\">&nbsp;(*)</font>
        </td>
        </tr>
	<tr bgcolor=\"".$color2."\">
        <td>".$langDepartment.":</td>
        <td><select name=\"department\">";
        $deps=mysql_query("SELECT name FROM faculte order by id",$db);
        while ($dep = mysql_fetch_array($deps)) 
        {
        	$tool_content .= "<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
        $tool_content .= "</select>
        </td>
        </tr>														
							<tr>
								<td></td>
								<td><input type=\"submit\" name=\"submit\" value=\"".$langOk."\"></td>
							</tr>
						</table>   
						<input type=\"hidden\" name=\"uname\" value=\"".$ldap_email."\">
						<input type=\"hidden\" name=\"password\" value=\"".$ldap_passwd."\">
						<input type=\"hidden\" name=\"auth\" value=\"".$auth."\">
						</form>";
		}
		else
		{
			$tool_content .= "<br />NO VALID USER in the auth method.Cannot register him<br />";
		}
	}
		


}   // end of initial if

// Check if a user with usename $login already exists
function user_exists($login) 
{
	global $mysqlMainDb;
	global $db;
	$username_check = mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='$login'",$db);
	if (mysql_num_rows($username_check) > 0) 
	{
		return TRUE;
	} 
	else 
	{
		return FALSE;
	}
}

// Check if a user with usename $login already exists in the requests(prof_request)
function user_exists_request($login) 
{
	global $mysqlMainDb;
	global $db;
	$username_check = mysql_query("SELECT profuname FROM `$mysqlMainDb`.prof_request 
	WHERE profuname='$login'",$db);
	if (mysql_num_rows($username_check) > 0) 
	{
		return TRUE;
	} 
	else 
	{
		return FALSE;
	}
}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	
	if ($ldap_server == '0') 
	{
		$tool_content .= $errormessage1.$ldapchoice.$errormessage2;	
	} 
	elseif (empty($ldap_email)) 
	{
		$tool_content .= $errormessage1. $ldapempty. $errormessage2;
	} 
	else 
	{
		$list = explode("_",$ldap_server);	
		$ldapServer = $list[0];
		$basedn = $list[1];
		$institut_id = $list[2];

		$ds=ldap_connect($ldapServer);  //get the ldapServer, baseDN from the db
		if ($ds) 
		{ 
    	$r=@ldap_bind($ds);     // this is an "anonymous" bind, typically read-only access
			if ($r) 
			{
				$mailadd=ldap_search($ds, $basedn, "mail=".$ldap_email);  
    		$info = ldap_get_entries($ds, $mailadd);
				if ($info["count"] == 0) 	//Den vre8hke eggrafh
				{ 
					echo "$errormessage1 Δεν βρέθηκαν εγγραφές. Πιθανόν να δώσατε λάθος στοιχεία. $errormessage2";
				} 
				else if ($info["count"] == 1) 	//Vre8hke o xrhsths 
				{ 
					echo "$errormessage1 Ο διδάσκοντας βρέθηκε στον εξυπηρέτη LDAP.";
					$cn = explode(" ", $info[0]["cn"][0]);
					$nomvar = $cn[0];
					$prenomvar = $cn[1];
					$emailvar = $info[0]["mail"][0];
					$found = 1;	
				} 
				else 
				{ 
    			echo "$errormessage1 Υπάρχει πρόβλημα με τα στοιχεία του διδάσκοντα. Παρακαλούμε επικοινωνήστε με τον διαχειριστή του εξυπηρέτη LDAP. 
					$errormessage2";
				}
				if ($info["count"] == 1) 
				{
					$tool_content .= "<tr><td>
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

				}
				ldap_close($ds);
			} 
			else 
			{
    		echo "<tr><td><h4>$ldaperror</h4></td></tr>";
			}
		}
 		else 
 		{
    		echo "<tr><td><h4>$ldaperror</h4></td></tr>";
		}

	}

}   // end of initial if
*/


$tool_content .= "</table>";

draw($tool_content,0);

?>