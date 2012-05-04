<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

require_once('../../include/lib/user.class.php');
require_once('../../include/lib/hierarchy.class.php');

$tree = new hierarchy();
$userObj = new user();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');

$nameTools = $langNewUser;
$navigation[] = array ('url' => '../admin/', 'name' => $langAdmin);

// Initialise $tool_content
$tool_content = "";
$submit = isset($_POST['submit'])?$_POST['submit']:'';
// ----------------------------
// register user
// ----------------------------

if($submit) {
   // register user
  $nom_form = isset($_POST['nom_form'])?$_POST['nom_form']:'';
  $prenom_form = isset($_POST['prenom_form'])?$_POST['prenom_form']:'';
  $uname = isset($_POST['uname'])?canonicalize_whitespace($_POST['uname']):'';
  $password = isset($_POST['password'])?$_POST['password']:'';
  $email_form = isset($_POST['email_form'])?mb_strtolower(trim($_POST['email_form'])):'';
  $departments = isset($_POST['department']) ? $_POST['department'] : array();
  $localize = isset($_POST['localize'])?$_POST['localize']:'';
  $lang = langname_to_code($localize);	

      // check if user name exists
  $username_check = db_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='$uname'");
  while ($myusername = mysql_fetch_array($username_check)) {
    $user_exist=$myusername[0];
  }

// check if there are empty fields
  if (empty($nom_form) or empty($prenom_form) or empty($password)
        or empty($uname) or empty($email_form)) {
      $tool_content .= error_screen($langFieldsMissing);
      $tool_content .= end_tables();
  }
  elseif(isset($user_exist) and $uname==$user_exist) {
      $tool_content .= error_screen($langUserFree);
      $tool_content .= end_tables();
 }

// check if email syntax is valid
 elseif(!email_seems_valid($email_form)) {
        $tool_content .= error_screen($langEmailWrong);
        $tool_content .= end_tables();
 }


// registration accepted

  else {
    $emailsubject = "$langYourReg $siteName"; // $langAsUser

      $emailbody = "
$langDestination $prenom_form $nom_form

$langYouAreReg$siteName, $langSettings $uname
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

$administratorName $administratorSurname
$langManager $siteName
$langTel $telephone
$langEmail : $emailhelpdesk
";

send_mail('', '', '', $email_form, $emailsubject, $emailbody, $charset);


// register user
    $registered_at = time();
    $expires_at = time() + $durationAccount;

    $password_encrypted = md5($password);
    $inscr_user = db_query("INSERT INTO `$mysqlMainDb`.user
      (user_id, nom, prenom, username, password, email, statut, registered_at, expires_at, lang)
      VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email_form', '5', '$registered_at', '$expires_at', '$lang')");
    $uid = mysql_insert_id();
    $userObj->refresh($uid, $departments);

    // close request
        $rid = intval($_POST['rid']);
        db_query("UPDATE user_request set status = 2,
         date_closed = NOW() WHERE id = '$rid'");

    $tool_content .= "<tr><td valign='top' align='center' class='alert1'>$usersuccess
    <br><br><a href='../admin/listreq.php?type=user' class='mainpage'>$langBack</a>";
  }

} else {

//---------------------------
// 	display form
// ---------------------------

if (isset($_GET['lang'])) {
	$lang = $_GET['lang'];
	$lang = langname_to_code($language);
}

$tool_content .= "<table width=\"99%\"><tbody>
   <tr>
    <td>
    <form action='$_SERVER[PHP_SELF]' method='post' onsubmit='return validateNodePickerForm();'>
    <table border=0 cellpadding='1' cellspacing='2' border='0' width='100%' align=center>
	<thead>
    <tr>
    <th class='left' width=20%>$langSurname</th>
	 <td><input type='text' class=auth_input_admin name='nom_form' value='".@$ps."' >
	<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class='left'>$langName</th>
	  <td><input type='text' class=auth_input_admin name='prenom_form' value='".@$pn."' >
	<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class='left'>$langUsername</th>
	  <td><input type='text' class=auth_input_admin name='uname' value='".@$pu."'>
		<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class='left'>$langPass&nbsp;:</th>
	  <td><input type='text' class=auth_input_admin name='password' value=".create_pass()."></td>
	  </tr>
	  <tr>
    	<th class='left'>$langEmail</th>
	  <td><input type='text' class=auth_input_admin name='email_form' value='".@$pe."'>
		<small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
	  <th class='left'>$langFaculty &nbsp;
		</span></th><td>";
        list($js, $html) = $tree->buildUserNodePicker('name="department[]"');
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</td>";
	$tool_content .= "<tr><th class='left'>$langLanguage</th><td>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td></tr>";

	$tool_content .= "</td></tr><tr><td colspan=\"2\">".$langRequiredFields."</td></tr>
		<tr><td>&nbsp;</td>
		<td><input type=\"submit\" name=\"submit\" value=\"".$langSubmit."\" ></td>
		</tr></thead></table>
		<input type='hidden' name='rid' value='".@$id."'>
		</tbody></table></form>";
    $tool_content .= "<center><p><a href=\"../admin/index.php\">$langBack</p></center>";

} // end of if 

draw($tool_content,3, 'auth', $head_content);

// -----------------
// functions
// -----------------
function error_screen($message) {

	global $langTryAgain;

	return "<tr height='80'><td colspan='3' valign='top' align='center' class=alert1>$message</td></tr><br><br>
      <tr height='30' valign='top' align='center'><td align=center>
      <a href='../admin/listreq.php?type=user' class=mainpage>$langTryAgain</a><br><br></td></tr>";
}

function end_tables() {
	global $langBack;
	
	$retstring = "</td></tr><tr><td align=right valign=bottom height='180'>";
	$retstring .= "<a href='../admin/index.php' class=mainpage>$langBack&nbsp;</a>";
	$retstring .= "</td></tr></table>";
	
	return $retstring;
}

?>
