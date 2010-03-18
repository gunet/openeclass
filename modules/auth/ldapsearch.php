<? 
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/
/*===========================================================================
	ldapsearch.php
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
  @Description: This script/file tries to authenticate the user, using
  his user/pass pair and the authentication method defined by the admin
==============================================================================
*/

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php' ;
require_once 'auth.inc.php';

$tool_content = "";

if (isset($_GET['auth']) or isset($_POST['auth']))
	$_SESSION['u_tmp']=$auth;
if(!isset($_GET['auth']) or !isset($_POST['auth']))
	$auth=$_SESSION['u_tmp'];

$nameTools = get_auth_info($auth);
$navigation[]= array ("url"=>"registration.php", "name"=> "$langNewUser");
$navigation[]= array ("url"=>"ldapnewuser.php?auth=$auth", "name"=> "$langConfirmUser");
$nameTools = $langUserData;

$ldap_email = isset($_POST['ldap_email'])?$_POST['ldap_email']:'';
$ldap_passwd = isset($_POST['ldap_passwd'])?$_POST['ldap_passwd']:'';
$is_submit = isset($_POST['is_submit'])?$_POST['is_submit']:'';

$lastpage = 'ldapnewuser.php?auth='.$auth.'&ldap_email='.$ldap_email;
$errormessage = "<br/><p>$ldapback <a href=\"$lastpage\">$ldaplastpage</a></p>";

if(!empty($is_submit))
{
	if (empty($ldap_email) or empty($ldap_passwd)) // check for empty username-password
	{
		$tool_content .= "<table width=\"99%\"><tbody><tr>
		  <td class=\"caution\" height='60'><p>$ldapempty  $errormessage</p></td>
		</tr></tbody></table>";
	} 
	else 
	{
		// try to authenticate him
		$auth_method_settings = get_auth_settings($auth);
		switch($auth) // now get the connection settings
		{
			case '2': $pop3host = str_replace("pop3host=","",$auth_method_settings['auth_settings']);
				break;
			case '3': $imaphost = str_replace("imaphost=","",$auth_method_settings['auth_settings']);
				break;
			case '4': $ldapsettings = $auth_method_settings['auth_settings'];
				    $ldap = explode("|",$ldapsettings);
				    $ldaphost = str_replace("ldaphost=","",$ldap[0]);	//ldaphost
				    $ldapbind_dn = str_replace("ldapbind_dn=","",$ldap[1]);	//ldapbase_dn
				    $ldapbind_user = str_replace("ldapbind_user=","",$ldap[2]);	//ldapbind_user
				    $ldapbind_pw = str_replace("ldapbind_pw=","",$ldap[3]);		// ldapbind_pw
				break;
			case '5': $dbsettings = $auth_method_settings['auth_settings'];
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
			$tool_content .= "<table width=\"99%\" align='left' class='FormData'><thead>
			<tr><td>
			<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">" .
					(isset($GLOBALS['auth_user_info'])?
			('<input type="hidden" name="prenom_form" value="' . $GLOBALS['auth_user_info']['firstname'] .
			'" /><input type="hidden" name="nom_form" value="' . $GLOBALS['auth_user_info']['lastname'] .
			'" /><input type="hidden" name="email" value="' . $GLOBALS['auth_user_info']['email'] . '" />'): '') . "
			<p class='success'>$langTheUser $ldapfound </p>
			<table width='100%'>
			<tbody>
			<tr><th class='left' width='20%'>".$langName."</th>
			<td width='10%'>".(isset($GLOBALS['auth_user_info'])?
        		 $GLOBALS['auth_user_info']['firstname']: '<input class="FormData_InputText" type="text" name="prenom_form" size="38" />')."</td>
			</tr>
			<tr><th class='left'>".$langSurname."</th>
			<td width='10%'>".(isset($GLOBALS['auth_user_info'])?$GLOBALS['auth_user_info']['lastname']: '<input class="FormData_InputText" type="text" name="nom_form" size="38" />')."</td>
			</tr>
			<tr><th class='left'>".$langEmail."</th>
			<td width='10%'>".(isset($GLOBALS['auth_user_info'])?$GLOBALS['auth_user_info']['email']: '<input class="FormData_InputText" type="text" name="email" size="38" />')."</td>
			</tr>
			<tr><th class='left'>".$langAm."</th>
			<td><input type='text' name='am' class='FormData_InputText' /></td>
			<td>&nbsp;</td>
			</tr>
			<tr><th class='left'>".$langFaculty."</th>
			<td>
			<select name='department'>";
			$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id",$db);
			while ($dep = mysql_fetch_array($deps))  {
				$tool_content .= "\n<option value='$dep[1]'>$dep[0]</option>";
			}
			$tool_content .= "</select></td></tr>";
			$tool_content .= "<tr><th class='left'>$langLanguage</th><td width='1'>";
			$tool_content .= lang_select_options('localize');
			$tool_content .= "</td></tr>";
			$tool_content .= "<tr><th class='left'>&nbsp;</th>
			<td><input type='submit' name='submit' value='$langRegistration' />
			<input type='hidden' name='uname' value='$ldap_email' />
			<input type='hidden' name='password' value='$ldap_passwd' />
			<input type='hidden' name='auth' value='$auth' />
			</td></tr>
			</tbody></table>
			</form>
			</td></tr></thead></table>";
		}
		else // not connected
		{
			$tool_content .= "<table width='99%'><tbody>";
			$tool_content .= "<tr><td class='caution' height='60'>$langConnNo <br />$langAuthNoValidUser</td></tr>";
			$tool_content .= "<tr><td><a href='$lastpage'>$langBack</a></td</tr></div></tbody></table>";
		}
	}
	draw($tool_content,0,'auth');
	exit();
}   // end of if is_submit()

// ----------------------------------------------
// registration
// ----------------------------------------------

if (isset($_POST['submit'])) {
	$uname = $_POST['uname'];
	$registration_errors = array();
		// check if there are empty fields
		if (empty($_POST['nom_form']) or empty($_POST['prenom_form']) or empty($uname)) {
			$registration_errors[] = $langEmptyFields;
		} else {
		// check if the username is already in use
			$q2 = "SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($uname)."'";
			$username_check = mysql_query($q2);
			if ($myusername = mysql_fetch_array($username_check)) {
				$registration_errors[] = $langUserFree;
			}
		}
		if (!empty($email) and !email_seems_valid($email)) {
			$registration_errors[] = $langEmailWrong;
		}
	
		$auth_method_settings = get_auth_settings($auth);
		$password = $auth_method_settings['auth_name'];
	
	if (count($registration_errors) == 0) {
		$emailsubject = "$langYourReg $siteName";
		$emailbody = "$langDestination $prenom_form $nom_form\n" .
			"$langYouAreReg $siteName $langSettings $uname\n" .
			"$langPassSameAuth\n$langAddress $siteName: " .
			"$urlServer\n$langProblem\n$langFormula" .
			"$administratorName $administratorSurname" .
			"$langManager $siteName \n$langTel $telephone \n" .
			"$langEmail: $emailhelpdesk";
	
		send_mail('', '', '', $email, $emailsubject, $emailbody, $charset);
		$registered_at = time();
		$expires_at = time() + $durationAccount;
		$authmethods = array("2","3","4","5");
		$uname = escapeSimple($uname);
		$lang = langname_to_code($language);
	
		$q1 = "INSERT INTO `$mysqlMainDb`.user 
			SET nom = '$nom_form', prenom = '$prenom_form', 
			username = '$uname', password = '$password', email = '$email',
			statut = '5', department = '$department',
			am = '$am', registered_at = ".$registered_at.",
			expires_at = ".$expires_at. ",
			lang = '$lang'";
	
		$inscr_user = db_query($q1);
		$last_id = mysql_insert_id();
		$result=mysql_query("SELECT user_id, nom, prenom FROM `$mysqlMainDb`.user WHERE user_id='$last_id'");
		while ($myrow = mysql_fetch_array($result)) {
			$uid=$myrow[0];
			$nom=$myrow[1];
			$prenom=$myrow[2];
		}
	
		db_query("INSERT INTO loginout  SET id_user = '$uid',
			ip = '".$REMOTE_ADDR."', `when` = NOW(), action = 'LOGIN'", $mysqlMainDb);
		$_SESSION['uid'] = $uid;
		$_SESSION['statut'] = 5;
		$_SESSION['prenom'] = $prenom;
		$_SESSION['nom'] = $nom;
		$_SESSION['uname'] = $uname;
	
		$tool_content .= "<table width='99%'><tbody><tr>" .
			"<td class='well-done' height='60'>" .
			"<p>$langDear $prenom $nom,</p>" .
			"<p>$langPersonalSettings</p></td>" .
			"</tr></tbody></table><br /><br />" .
			"<p>$langPersonalSettingsMore</p>";
	} else {
		// errors exist - registration failed
		$tool_content .= "<table width='99%'><tbody><tr><td class='caution' height='60'>";
		foreach ($registration_errors as $error) {
			$tool_content .= "<p>$error</p>";
		}
		$tool_content .= "<p><a href='javascript:history.go(-1)'>$langAgain</a></p>" .
		"</td></tr></tbody></table><br /><br />";
	}
} // end of submit

draw($tool_content,0,'auth');
