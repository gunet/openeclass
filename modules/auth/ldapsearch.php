<? 
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	ldapsearch.php
	@last update: 27-06-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
  @Description: This script/file tries to authenticate the user, using
  his user/pass pair and the authentication method defined by the admin
  
==============================================================================
*/

include '../../include/baseTheme.php';
include 'auth.inc.php';
$auth = isset($_POST['auth'])?$_POST['auth']:'';

$nameTools = get_auth_info($auth);
$navigation[]= array ("url"=>"registration.php", "name"=> "$langNewUserAccount¡ctivation");
$nameTools = "$langUserData";

// Initialise $tool_content
$tool_content = "";
// Main body

$found = 0;

// get the values from ldapnewuser.php
$ldap_email = isset($_POST['ldap_email'])?$_POST['ldap_email']:'';
$ldap_passwd = isset($_POST['ldap_passwd'])?$_POST['ldap_passwd']:'';
$is_submit = isset($_POST['is_submit'])?$_POST['is_submit']:'';

$lastpage = 'ldapnewuser.php?auth='.$auth;
$userdescr = $langTheUser;

//$errormessage1 = "<p>&nbsp;</p>";
$errormessage2 = "<br/><p>$ldapback <a href=\"$lastpage\">$ldaplastpage</a></p>";

if(!empty($is_submit))
{
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
	@$emailtohostname = substr( $email, ( strrpos( $email, "@" ) +1 ));
	$emailtohostname = substr( $ldap_email, ( strrpos( $ldap_email, "@" ) +1 ));
	
	if (empty($ldap_email) or empty($ldap_passwd)) // check for empty username-password
	{
		$tool_content .= "
		<table width=\"99%\">
		<tbody>
		<tr>
		  <td class=\"caution\" height='60'><p>$ldapempty  $errormessage2 </p></td>
		</tr>
		</tbody>
		</table>";
	} 
	elseif (user_exists($ldap_email)) // check if the user already exists
	{
		$tool_content .= "
		<table width=\"99%\">
		<tbody>
		<tr>
		  <td class=\"caution\" height='60'><p>$ldapuserexists $errormessage2</p></td>
		</tr>
		</tbody>
		</table>";
		//$tool_content .= $errormessage1 . $ldapuserexists . $errormessage2;
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
					<td width=\"600\">
					<FIELDSET>
	  				<LEGEND>$langUserAccount</LEGEND>
					<form action=\"newuser_second.php\" method=\"post\">
					<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"100%\">
					<tr valign=\"top\">
						<td>".$langName."</td>
						<td><input type=\"text\" name=\"prenom_form\"><font size=\"1\">&nbsp;(*)</font></td>
					</tr>
					<tr>
						<td>".$langSurname."</td>
						<td><input type=\"text\" name=\"nom_form\"><font size=\"1\">&nbsp;(*)</font></td>
					</tr>
					<tr>
						<td>".$langEmail."</td>
						<td><input type=\"text\" name=\"email\"></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><font size=\"1\">".$langEmailNotice."</font></td>
					</tr>
					<tr>
						<td>".$langAm."</td>
					<td><input type=\"text\" name=\"am\"></td>
					</tr>
					<tr>
						<td>".$langDepartment."</td>
						<td>
						<select name=\"department\">
			";
			$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id",$db);
			while ($dep = mysql_fetch_array($deps)) 
			$tool_content .= "\n
							<option value=\"$dep[1]\">$dep[0]</option>
			";
			
			$tool_content .= "
						</select></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\"></td>
					</tr>
						<input type=\"hidden\" name=\"uname\" value=\"".$ldap_email."\">
						<input type=\"hidden\" name=\"password\" value=\"".$ldap_passwd."\">
						<input type=\"hidden\" name=\"auth\" value=\"".$auth."\">
					</table>
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

$tool_content .= "</table>";
$tool_content .= "<br />";

draw($tool_content,0,'auth');
?>
