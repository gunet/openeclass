<?
include '../../include/baseTheme.php';
require_once 'auth.inc.php';

$nameTools = get_auth_info($auth);
$navigation[]= array ("url"=>"registration.php", "name"=> "$langNewProfAccount¡ctivation");
$nameTools = "$langUserData";

$found = 0;
$tool_content = "";

$errormessage1 = "<tr valign=\"top\" bgcolor=\"$color2\"><td><font size=\"2\" face=\"arial, helvetica\"><p>&nbsp;</p>";
$errormessage3 = "</font><p>&nbsp;</p><br><br><br></td></tr>";
$errormessage2 = "<p>$ldapback<a href=\"ldapnewprof.php\">$ldaplastpage</a></p>$errormessage3";
$is_submit = isset($_POST['is_submit'])?$_POST['is_submit']:'';
$ldap_email = isset($_POST['ldap_email'])?$_POST['ldap_email']:'';
$ldap_passwd = isset($_POST['ldap_passwd'])?$_POST['ldap_passwd']:'';
$auth = isset($_POST['auth'])?$_POST['auth']:0;

if(!empty($is_submit))
{
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
		$tool_content .= "$langLdapRequest: $ldap_email<br>";
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
		if($is_valid)
		{
			$auth_allow = 1;		// Successfully connected
		}
		else
		{
			$tool_content .= "<br />$langConnNo!<br />";
			$auth_allow = 0;
		}	
		if($auth_allow==1)
		{	
			$tool_content .= "
				<table>
				<tr>
					<td width=\"700\">
					<FIELDSET>
	  				<LEGEND>$langProfAccount</LEGEND>
					<form name=\"registration\" action=\"newprof_second.php\" method=\"post\">
					<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"100%\">
					<tr>
						<td>$langSurname:</td>   
						<td><input type=\"text\" name=\"nom_form\" size=\"30\" value=\"\"><font size=\"1\">&nbsp;(*)</font></td>
					</tr>
					<tr>
						<td>$langName:</td>   
						<td><input type=\"text\" name=\"prenom_form\" size=\"30\" value=\"\"><font size=\"1\">&nbsp;(*)</font></td>
					</tr>
					<tr>
						<td>E-mail:</td>   
						<td><input type=\"text\" name=\"email_form\" size=\"30\" value=\"\"></td>
					</tr>		
					<tr>
						<td>$langTel:</td>   
						<td><input type=\"text\" name=\"userphone\" size=\"30\" value=\"\"></td>
					</tr>		
					<tr>
        				<td>".$langComments."<br><font size=\"1\">".$profreason."</td>
						<td><textarea name=\"usercomment\" COLS=\"35\" ROWS=\"4\" WRAP=\"SOFT\">".@$usercomment."</textarea>
						<font size=\"1\">&nbsp;(*)</font>
        				</td>
        			</tr>
					<tr>
        				<td>".$langDepartment.":</td>
       					<td><select name=\"department\">";
        $deps=mysql_query("SELECT name FROM faculte order by id",$db);
        while ($dep = mysql_fetch_array($deps)) 
        {
        	$tool_content .= "
						<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
        $tool_content .= "
						</select>
						<font size=\"1\">&nbsp;(*)</font>
        				</td>
        			</tr>	
					<tr>
						<td colspan=\"2\">&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\"></td>
					</tr>
					</table>   
					<input type=\"hidden\" name=\"uname\" value=\"".$ldap_email."\">
					<input type=\"hidden\" name=\"password\" value=\"".$ldap_passwd."\">
					<input type=\"hidden\" name=\"auth\" value=\"".htmlspecialchars($auth)."\">
					</form>		
					</FIELDSET>
					</td>
				</tr>
				<tr>
					<td  align='right'><font size=\"1\">".$langRequiredFields."</font></td>
				</tr>
				</table>
			";

		}
		else
		{
			$tool_content .= "<br />$langAuthNoValidUser<br />";
		}
	}
		
}   // end of initial if

// Check if a user with usename $login already exists in the requests(prof_request)
function user_exists_request($login) 
{
	global $mysqlMainDb;
	global $db;
	$username_check = mysql_query("SELECT profuname FROM `$mysqlMainDb`.prof_request 
	WHERE profuname='".mysql_real_escape_string($login)."'",$db);
	if (mysql_num_rows($username_check) > 0) 
	{
		return TRUE;
	} 
	else 
	{
		return FALSE;
	}
}
	
draw($tool_content,0,'auth');
?>
