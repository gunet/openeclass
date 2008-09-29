<?
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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
	newuser.php
* @version $Id$
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================

 	Purpose: The file displays the form that that the candidate user must fill
 	in with all the basic information.

==============================================================================
*/

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
include 'auth.inc.php';
$nameTools = $langUserDetails;
// Main body
$navigation[] = array("url"=>"registration.php", "name"=> $langNewUser);

$tool_content = "";	// Initialise $tool_content

if (isset($close_user_registration) and $close_user_registration == TRUE) {
	$tool_content .= "<div class='td_main'>$langForbidden</div>";
        draw($tool_content,0);
	exit;
 }
// security check
if (isset($_POST['localize'])) {
	$language = preg_replace('/[^a-z]/', '', $_POST['localize']);
}
if ($language == 'greek')
	$lang = 'el';
elseif ($language == 'english')
	$lang = 'en';

// display form
if (!isset($submit)) {
	// Main body
	@$tool_content .= "
<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">
<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
<thead>
<tr>
  <td>
  <table width=\"100%\" align='left' class='FormData'>
  <thead>
  <tr>
    <th class='left' width='220'>$langName</th>
    <td colspan=\"2\"><input type=\"text\" name=\"prenom_form\" value=\"$prenom_form\" class='FormData_InputText'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langSurname</th>
    <td colspan=\"2\"><input type=\"text\" name=\"nom_form\" value=\"$nom_form\" class='FormData_InputText'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langUsername</th>
    <td colspan=\"2\"><input type=\"text\" name=\"uname\" value=\"$uname\" size=\"20\" maxlength=\"20\" class='FormData_InputText'>&nbsp;&nbsp;<small>(*) $langUserNotice</small></td>
  </tr>
  <tr>
    <th class='left'>$langPass</th>
    <td colspan=\"2\"><input type=\"password\" name=\"password1\" size=\"20\" maxlength=\"20\" class='FormData_InputText'>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langConfirmation</th>
    <td colspan=\"2\"><input type=\"password\" name=\"password\" size=\"20\" maxlength=\"20\" class='FormData_InputText'>&nbsp;&nbsp;<small>(*) $langUserNotice</small></td>
  </tr>
  <tr>
    <th class='left'>$langEmail</th>
    <td valign=\"top\"><input type=\"text\" name=\"email\" value=\"$email\" class='FormData_InputText'></td>
    <td><small>$langEmailNotice</small></td>
  </tr>
  <tr>
    <th class='left'>$langAm</th>
    <td colspan=\"2\"><input type=\"text\" name=\"am\" value=\"$am\" class='FormData_InputText'></td>
  </tr>
  <tr>
    <th class='left'>$langDepartment</th>
	<td colspan=\"2\"><select name=\"department\">";
$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id");
while ($dep = mysql_fetch_array($deps)) {
	$tool_content .= "\n<option value=\"".$dep[1]."\">".$dep[0]."</option>";
	}
$tool_content .= "\n</select>
    </td>
  </tr>
   <tr>
      <th class='left'>$langLanguage</th>
      <td width='1'>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td>
      <td><small>$langTipLang2</small></td>
    </tr>
  <tr>
    <th class='left'>&nbsp;</th>
    <td colspan=\"2\">
	  <input type=\"hidden\" name=\"auth\" value=\"1\">
      <input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\">
    </td>
  </tr>
  </thead>
  </table>
      <div align=\"right\"><small>$langRequiredFields</small></div>
  </td>
</tr>
</thead>
</table>
</form>";

} else {

// trim white spaces in the end and in the beginning of the word
$uname = preg_replace('/\s+/', ' ', trim(isset($_POST['uname'])?$_POST['uname']:''));

// registration
$registration_errors = array();

// check if there are empty fields
        if (empty($nom_form) or empty($prenom_form) or empty($password) or empty($uname)) {
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
        if (!empty($auth_method_settings) and $auth != 1) {
                $password = $auth_method_settings['auth_name'];
        } else {
                // check if the two passwords match
                if ($password != $_POST['password1']) {
                        $registration_errors[] = $langPassTwice;
                } elseif (strtoupper($password) == strtoupper($uname)
                          or strtoupper($password) == strtoupper($nom_form)
                          or strtoupper($password) == strtoupper($prenom_form)
                          or strtoupper($password) == strtoupper($email)) {
                        // if the passwd is too easy offer a password sugestion
                        $registration_errors[] = $langPassTooEasy . ': <strong>' .
                                substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8) . '</strong>';
                }
        }

if (count($registration_errors) == 0) {
    $emailsubject = "$langYourReg $siteName";
		$uname = unescapeSimple($uname); // un-escape the characters: simple and double quote
		$password = unescapeSimple($password);
                if((!empty($auth_method_settings)) && ($auth!=1)) {
                        $emailbody = "$langDestination $prenom_form $nom_form\n" .
                                "$langYouAreReg $siteName $langSettings $uname\n" .
                                "$langPassSameAuth\n$langAddress $siteName: " .
                                "$urlServer\n$langProblem\n$langFormula" .
                                "$administratorName $administratorSurname" .
                                "$langManager $siteName \n$langTel $telephone \n" .
                                "$langEmail: $emailAdministrator";
                }
    else
    {
                        $emailbody = "$langDestination $prenom_form $nom_form\n" .
                                "$langYouAreReg $siteName $langSettings $uname\n" .
                                "$langPass: $password\n$langAddress $siteName: " .
                                "$urlServer\n$langProblem\n$langFormula" .
                                "$administratorName $administratorSurname" .
                                "$langManager $siteName \n$langTel $telephone \n" .
                                "$langEmail: $emailAdministrator";
    }

send_mail($siteName, $emailAdministrator, '', $email, $emailsubject, $emailbody, $charset);
    $registered_at = time();
    $expires_at = time() + $durationAccount;  //$expires_at = time() + 31536000;

    // manage the store/encrypt process of password into database
    $authmethods = array("2","3","4","5");
    $uname = escapeSimple($uname);  // escape the characters: simple and double quote
    $password = escapeSimpleSelect($password);  // escape the characters: simple and double quote
    if(!in_array($auth,$authmethods)) {
      $password_encrypted = md5($password);
    } else {
           $password_encrypted = $password;
    }
    $q1 = "INSERT INTO `$mysqlMainDb`.user
      (user_id, nom, prenom, username, password, email, statut, department, am, registered_at, expires_at, lang)
      VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email','5',
        '$department','$am',".$registered_at.",".$expires_at.",'$lang')";

    $inscr_user = mysql_query($q1);
    $last_id = mysql_insert_id();
    $result=mysql_query("SELECT user_id, nom, prenom FROM `$mysqlMainDb`.user WHERE user_id='$last_id'");
    while ($myrow = mysql_fetch_array($result)) {
      $uid=$myrow[0];
      $nom=$myrow[1];
      $prenom=$myrow[2];
    }
mysql_query("INSERT INTO `$mysqlMainDb`.loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action)
      VALUES ('', '".$uid."', '".$REMOTE_ADDR."', NOW(), 'LOGIN')");
    session_register("uid");
    session_register("statut");
    session_register("prenom");
    session_register("nom");
    session_register("uname");

    // registration form
    $tool_content .= "<table width='99%'><tbody><tr>" .
                                 "<td class='well-done' height='60'>" .
                                 "<p>$langDear $prenom $nom,</p>" .
                                 "<p>$langPersonalSettings</p></td>" .
                                 "</tr></tbody></table><br /><br />" .
                                 "<p>$langPersonalSettingsMore</p>";
        } else {
                // errors exist - registration failed
                $tool_content .= "<table width='99%'><tbody><tr>" .
                                 "<td class='caution' height='60'>";
                foreach ($registration_errors as $error) {
                        $tool_content .= "<p>$error</p>";
                }
                $tool_content .= "<p><a href='$_SERVER[PHP_SELF]?prenom_form=$_POST[prenom_form]&nom_form=$_POST[nom_form]&uname=$_POST[uname]&email=$_POST[email]&am=$_POST[am]'>$langAgain</a></p>" .
                                 "</td></tr></tbody></table><br /><br />";
        }

} // end of registration

draw($tool_content,0);
?>
