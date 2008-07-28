<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$require_help = TRUE;
$require_login = true;
$helpTopic = 'Profile';
include '../../include/baseTheme.php';
include "../auth/auth.inc.php";
$require_valid_uid = TRUE;
$tool_content = "";

check_uid();
$nameTools = $langModifProfile;
check_guest();

if (isset($submit) && (!isset($ldap_submit)) && !isset($changePass)) {
	// check if username exists
	$username_check=mysql_query("SELECT username FROM user WHERE username='".escapeSimple($username_form)."'");
	while ($myusername = mysql_fetch_array($username_check))
	{
		$user_exist=$myusername[0];
	}

	// check if there are empty fields
	if (empty($nom_form) OR empty($prenom_form) OR empty($username_form)) {
		header("location:". $_SERVER['PHP_SELF']."?msg=4");
		exit();
	}
	
	elseif (empty($email_form) and check_prof()) {
		header("location:". $_SERVER['PHP_SELF']."?msg=4");
		exit();
	}

	elseif (strstr($username_form, "'") or strstr($username_form, '"') or strstr($username_form, '\\')){
		header("location:". $_SERVER['PHP_SELF']."?msg=10");
		exit();
	}

	// check if username is free
	elseif(isset($user_exist) AND ($username_form==$user_exist) AND ($username_form!=$uname)) {
		header("location:". $_SERVER['PHP_SELF']."?msg=5");
		exit();
	}

	// check if email is valid
	elseif (!email_seems_valid($email_form) and check_prof()) {
		header("location:". $_SERVER['PHP_SELF']."?msg=6");
		exit();
	}

	// everything is ok
	else {
		##[BEGIN personalisation modification]############
		if (!isset($persoStatus) || $persoStatus == "") $persoStatus = "no";
		else  $persoStatus = "yes";
		$userLanguage = $_REQUEST['userLanguage'];
		$username_form = escapeSimple($username_form);
		if(mysql_query("UPDATE user
	        SET nom='$nom_form', prenom='$prenom_form',
	        username='$username_form', email='$email_form', am='$am_form',
	            perso='$persoStatus', lang='$userLanguage'
	        WHERE user_id='".$_SESSION["uid"]."'")){
		header("location:". $_SERVER['PHP_SELF']."?msg=1");
		exit();
	        }
	}
}	// if submit

##[BEGIN personalisation modification - For LDAP users]############
if (isset($submit) && isset($ldap_submit) && ($ldap_submit == "ON")) {

	$userLanguage = $_REQUEST['userLanguage'];
	if (!isset($persoStatus) || $persoStatus == "")
		$persoStatus = "no";
	else  
		$persoStatus = "yes";
	mysql_query(" UPDATE user SET perso = '$persoStatus', 
			lang = '$userLanguage' WHERE user_id='".$_SESSION["uid"]."' ");
	if (session_is_registered("user_perso_active") && $persoStatus=="no") session_unregister("user_perso_active");
	if ($userLang == "el") {
		$_SESSION['langswitch'] = "greek";
	} else {
		$_SESSION['langswitch'] = "english";
	}

	header("location:". $_SERVER['PHP_SELF']."?msg=1");
	exit();
}
##[END personalisation modification]############

//Show message if exists
if(isset($msg))
{
	switch ($msg){
		case 1: { //profile information changed successfully (not the password data!)
			$message = $langProfileReg;
			$urlText = $langHome;
			$type = "success";
			break;
		}
		case 3: { //pass too easy
			$message = $langPassTooEasy.": <strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong>";
			$urlText = "";
			$type = "caution";
			break;
		}
		case 4: { // empty fields check
			$message = $langFields;
			$urlText = "";
			$type = "caution";
			break;
		}
		case 5: {//username already exists
			$message = $langUserTaken;
			$urlText = "";
			$type = "caution";
			break;
		}
		case 6: {//email not valid
			$message = $langEmailWrong;
			$urlText = "";
			$type = "caution";
			break;
		}
		case 10: { // invalid characters
			$message = $langInvalidCharsUsername;
			$urlText = "";
      $type = "caution";
      break;
		}
		default:die("invalid message id");
	}

	$tool_content .=  "<table width=\"99%\"><tbody><tr><td class=\"$type\">$message<br>
    	<a href=\"../../index.php\">$urlText</a></td></tr></tbody></table><br/>";

}

$sqlGetInfoUser ="SELECT nom, prenom, username, password, email, am, perso, lang
    FROM user WHERE user_id='".$uid."'";
$result=mysql_query($sqlGetInfoUser);
$myrow = mysql_fetch_array($result);

$nom_form = $myrow['nom'];
$prenom_form = $myrow['prenom'];
$username_form = $myrow['username'];
$password_form = $myrow['password'];
$email_form = $myrow['email'];
$am_form = $myrow['am'];
##[BEGIN personalisation modification, added 'personalisation on SELECT]############
$persoStatus=	$myrow['perso'];
$userLang = $myrow['lang'];

if ($persoStatus == "yes") $checkedPerso = "checked";
else $checkedPerso = "";

if ($userLang == "el") {
	$checkedLangEl = "checked";
	$checkedLangEn = "";
} else {
	$checkedLangEl = "";
	$checkedLangEn = "checked";
}
##[END personalisation modification]############

session_unregister("uname");
session_unregister("pass");
session_unregister("nom");
session_unregister("prenom");

$uname = $username_form;
$pass = $password_form;
$nom = $nom_form;
$prenom = $prenom_form;

session_register("uname");
session_register("pass");
session_register("nom");
session_register("prenom");

##[BEGIN personalisation modification]############IT DOES NOT UPDATE THE DB!!!
if ($persoStatus=="yes" && session_is_registered("perso_is_active")) session_register("user_perso_active");
if ($persoStatus=="no" && session_is_registered("perso_is_active")) session_unregister("user_perso_active");

if ($userLang == "el") {
	$_SESSION['langswitch'] = "greek";
	$_SESSION['langLinkText'] = "English";
	$_SESSION['langLinkURL'] = "?localize=en";
} else {
	$_SESSION['langswitch'] = "english";
	$_SESSION['langLinkText'] = "Ελληνικά";
	$_SESSION['langLinkURL'] = "?localize=el";
}
##[END personalisation modification]############

$sec = $urlSecure.'modules/profile/profile.php';
$passurl = $urlSecure.'modules/profile/password.php';
$authmethods = array("imap","pop3","ldap","db");

if ((!isset($changePass)) || isset($_POST['submit'])) {
	$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\">";
	if(!in_array($password_form,$authmethods)) {
		$tool_content .= "<li><a href=\"".$passurl."\">".$langChangePass."</a></li>";
	}
	$tool_content .= " <li><a href='../unreguser/unreguser.php'>$langUnregUser</a></li>";
	$tool_content .= "</ul></div>";
	$tool_content .= "<form method=\"post\" action=\"$sec?submit=yes\"><br/>
    <table width=\"99%\">
    <tbody>
    <tr>
       <th width=\"150\" class='left'>$langName</th>
       <td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"prenom_form\" value=\"$prenom_form\"></td>
    </tr>
    <tr>
       <th width=\"150\" class='left'>$langSurname</th>
       <td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"nom_form\" value=\"$nom_form\"></td>
    </tr>";

	if(!in_array($password_form,$authmethods)) {
		$tool_content .= "<tr>
       <th width=\"150\" class='left'>$langUsername</th>
       <td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"username_form\" value=\"$username_form\"></td>
    </tr>";
	}
	else		// means that it is external auth method, so the user cannot change this password
	{
		switch($password_form)
		{
			case "pop3": $auth=2;break;
			case "imap": $auth=3;break;
			case "ldap": $auth=4;break;
			case "db": $auth=5;break;
			default: $auth=1;break;
		}
		$auth_text = get_auth_info($auth);
		$tool_content .= "
    <tr>
      <th width=\"150\" class='left'>".$langUsername. "</th>
      <td class=\"caution_small\">&nbsp;&nbsp;&nbsp;&nbsp;<b>".$username_form."</b> [".$auth_text."]
        <input type=\"hidden\" name=\"username_form\" value=\"$username_form\">
      </td>
    </tr>";
	}

	$tool_content .= "
    <tr>
        <th width=\"150\" class='left'>$langEmail</th>
        <td><input class='FormData_InputText' type=\"text\" size=\"40\" name=\"email_form\" value=\"$email_form\"></td>
    <tr>
        <th width=\"150\" class='left'>$langAm</th>
        <td><input class='FormData_InputText' type=\"text\" size=\"20\" name=\"am_form\" value=\"$am_form\"></td>
    </tr>";
	##[BEGIN personalisation modification]############
	if (session_is_registered("perso_is_active")) {
		$tool_content .="<tr>
      <th width=\"150\" class='left'>$langPerso</th>
      <td><input class='FormData_InputText' type=checkbox name='persoStatus' value=\"yes\" $checkedPerso></td>
    </tr>";
	}
	##[END personalisation modification]############
	$tool_content .= "<tr>
      <th class='left'>$langLanguage</th>
      <td>
        <input type='radio' name='userLanguage' value='el' $checkedLangEl>$langGreek<br>
        <input type='radio' name='userLanguage' value='en'  $checkedLangEn>$langEnglish
      </td>
    </tr>
	<tr>
      <th>&nbsp;</th>
      <td><input type=\"Submit\" name=\"submit\" value=\"$langModify\"></td>
    </tr>
    </tbody></table>
    </form>
   ";
}
draw($tool_content, 1);
?>
