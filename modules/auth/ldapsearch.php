<? 
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2007  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
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
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
  @Description: This script/file tries to authenticate the user, using
  his user/pass pair and the authentication method defined by the admin
==============================================================================
*/

include '../../include/baseTheme.php';
require_once 'auth.inc.php';

$nameTools = get_auth_info($auth);
$navigation[]= array ("url"=>"registration.php", "name"=> "$langNewUserAccountActivation");
$nameTools = $langUserData;
$tool_content = "";

// get the values from ldapnewuser.php
$ldap_email = isset($_POST['ldap_email'])?$_POST['ldap_email']:'';
$ldap_passwd = isset($_POST['ldap_passwd'])?$_POST['ldap_passwd']:'';
$is_submit = isset($_POST['is_submit'])?$_POST['is_submit']:'';

//$lastpage = 'ldapnewuser.php?auth='.$auth;
$lastpage = 'ldapnewuser.php?auth='.$auth.'&ldap_email='.$ldap_email.'&ldap_passwd='.$ldap_passwd;
$userdescr = $langTheUser;

$errormessage = "<br/><p>$ldapback <a href=\"$lastpage\">$ldaplastpage</a></p>";

if(!empty($is_submit))
{
	if (empty($ldap_email) or empty($ldap_passwd)) // check for empty username-password
	{
		$tool_content .= "<table width=\"99%\"><tbody><tr>
		  <td class=\"caution\" height='60'><p>$ldapempty  $errormessage</p></td>
		</tr></tbody></table>";
	} 
	elseif (user_exists($ldap_email)) // check if the user already exists
	{
		$tool_content .= "<table width=\"99%\"><tbody><tr>
		  <td class=\"caution\" height='60'><p>$ldapuserexists $errormessage</p></td>
			</tr></tbody></table>";
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

		if($is_valid) {  // Successfully connected
			$tool_content .= "
    	<table width=\"99%\" align='left' class='FormData'>
	    <thead>
  	  <tr>
      <td>
      <form action=\"newuser_second.php\" method=\"post\">
      <table width=\"100%\">
       <tbody>
       <tr>  
         <th class='left' width='20%'>".$langName."</th>
         <td width='10%'><input class='FormData_InputText' type=\"text\" name=\"prenom_form\"" .
        (isset($GLOBALS['auth_user_info'])?
                (' value="' . $GLOBALS['auth_user_info']['firstname'] . '"'): '') . "></td>
         <td><small>(*)</small></td>
       </tr>
       <tr>
         <th class='left'>".$langSurname."</th>
         <td><input type=\"text\" name=\"nom_form\" class='FormData_InputText'" .
        (isset($GLOBALS['auth_user_info'])?
                (' value="' . $GLOBALS['auth_user_info']['lastname'] . '"'): '') . "></td>
         <td><small>(*)</small></td>
       </tr>
       <tr>
         <th class='left'>".$langEmail."</th>
         <td><input type=\"text\" name=\"email\" class='FormData_InputText'" .
        (isset($GLOBALS['auth_user_info'])?
                (' value="' . $GLOBALS['auth_user_info']['email'] . '"'): '') . "></td>
         <td><small>".$langEmailNotice."</small></td>
       </tr>
       <tr>
         <th class='left'>".$langAm."</th>
         <td><input type=\"text\" name=\"am\" class='FormData_InputText'></td>
         <td>&nbsp;</td>
       </tr>
       <tr>
         <th class='left'>".$langDepartment."</th>
         <td>
         <select name=\"department\">
         ";
			$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id",$db);
			while ($dep = mysql_fetch_array($deps)) 
			$tool_content .= "\n
			    <option value=\"$dep[1]\">$dep[0]</option>
			";
			
			$tool_content .= "</select></td></tr>
       <tr>
         <th class='left'>&nbsp;</th>
         <td><input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\">
             <input type=\"hidden\" name=\"uname\" value=\"".$ldap_email."\">
             <input type=\"hidden\" name=\"password\" value=\"".$ldap_passwd."\">
             <input type=\"hidden\" name=\"auth\" value=\"".$auth."\">
         </td>
         <td><p align='right'>".$langRequiredFields."</p></td>
         </tr>
         </tbody>
         </table>
         </form>
         </td></tr></thead></table>";
		}
		else // not connected
		{
			$tool_content .= "<br />$langConnNo<br />";
			$tool_content .= "<br />$langAuthNoValidUser<br />";
			$tool_content .= "<br><center><a href='$lastpage'>$langBack</a></center></br>";
		}
	}

}   // end of initial if

draw($tool_content,0,'auth');
