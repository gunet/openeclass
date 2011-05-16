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
$tool_content = "";	
$nameTools = $langUserDetails;
$navigation[] = array("url"=>"registration.php", "name"=> $langNewUser);

if (isset($close_user_registration) and $close_user_registration == TRUE) {
	$tool_content .= "<div class='td_main'>$langForbidden</div>";
        draw($tool_content,0);
	exit;
}
 
$lang = langname_to_code($language);

// display form
if (!isset($_POST['submit'])) {
	if (get_config("email_required")) {
		$email_message = "(*)";
	} else {
		$email_message = $langEmailNotice;
	}
	if (get_config("am_required")) {
		$am_message = "&nbsp;&nbsp;<small>(*)</small>";
	} else {
		$am_message = '';
	}
	@$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
        <fieldset>
        <legend>$langUserData</legend>
	<table width='100%' class='tbl'>
	<tr>
	<th class='left' width='180'>$langName:</th>
	<td colspan='2'><input type='text' name='prenom_form' value='".$_GET['prenom_form']."' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langSurname:</th>
	<td colspan='2'><input type='text' name='nom_form' value='".$_GET['nom_form']."' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langUsername:</th>
	<td colspan='2'><input type='text' name='uname' value='".$_GET['uname']."' size='20' maxlength='20' class='FormData_InputText' />&nbsp;&nbsp;<small>(*) $langUserNotice</small></td>
	</tr>
	<tr>
	<th class='left'>$langPass:</th>
	<td colspan='2'><input type='password' name='password1' size='20' maxlength='20' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langConfirmation:</th>
	<td colspan='2'><input type='password' name='password' size='20' maxlength='20' class='FormData_InputText' />&nbsp;&nbsp;<small>(*) $langUserNotice</small></td>
	</tr>
	<tr>
	<th class='left'>$langEmail:</th>
	<td valign='top'><input type='text' name='email' value='".$_GET['email']."' class='FormData_InputText' /></td>
	<td><small>$email_message</small></td>
	</tr>
	<tr>
	<th class='left'>$langAm:</th>
	<td colspan='2' valign='top'><input type='text' name='am' value='".$_GET['am']."' class='FormData_InputText' />$am_message</td>
	</tr>
	<tr>
	<th class='left'>$langFaculty:</th>
		<td colspan='2'><select name='department'>";
	$deps = db_query("SELECT name, id FROM faculte ORDER BY id");
	while ($dep = mysql_fetch_array($deps)) {
		$tool_content .= "\n<option value='".$dep[1]."'>".$dep[0]."</option>";
	}
	$tool_content .= "\n</select>
	</td></tr>
	<tr>
	<th class='left'>$langLanguage:</th>
	<td width='1'>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td>
	<td><small>$langTipLang2</small></td>
	</tr>
	<tr>
	<th class='left'>&nbsp;</th>
	<td colspan='2' class='right'>
	<input type='submit' name='submit' value='".$langRegistration."' />
	</td>
	</tr>
	</table>
	</fieldset>
	</form>
<div class='right smaller'>$langRequiredFields</div>
";
} else {
	if (get_config("email_required")) {
		$email_arr_value = true;
	} else {
		$email_arr_value = false;
	}
	if (get_config("am_required")) {
		$am_arr_value = true;
	} else {
		$am_arr_value = false;
	}
	$missing = register_posted_variables(array('uname' => true,
					'nom_form' => true,
					'prenom_form' => true,
					'password' => true,
					'password1' => true,
					'email' => $email_arr_value,
					'department' => true,
					'am' => $am_arr_value));	
	$registration_errors = array();
	// check if there are empty fields
	if (!$missing) {
		$registration_errors[] = $langEmptyFields;
	} else {
		// check if the username is already in use
		$q2 = "SELECT username FROM `$mysqlMainDb`.user WHERE username = ".autoquote($uname);
		$username_check = db_query($q2);
		if ($myusername = mysql_fetch_array($username_check)) {
			$registration_errors[] = $langUserFree;
		}
	}
	if (!empty($email) and !email_seems_valid($email)) {
		$registration_errors[] = $langEmailWrong;
	}
	if ($password != $_POST['password1']) { // check if the two passwords match
		$registration_errors[] = $langPassTwice;
	} 
	if (count($registration_errors) == 0) {
		$emailsubject = "$langYourReg $siteName";
		$uname = unescapeSimple($uname); 
		$password = unescapeSimple($password);
		$emailbody = "$langDestination $prenom_form $nom_form\n" .
			"$langYouAreReg $siteName $langSettings $uname\n" .
			"$langPass: $password\n$langAddress $siteName: " .
			"$urlServer\n$langProblem\n$langFormula" .
			"$administratorName $administratorSurname" .
			"$langManager $siteName \n$langTel $telephone \n" .
			"$langEmail: $emailhelpdesk";
	send_mail('', '', '', $email, $emailsubject, $emailbody, $charset);
	$registered_at = time();
	$expires_at = time() + $durationAccount;  
	// manage the store/encrypt process of password into database
	$uname = escapeSimple($uname);  
	$password = escapeSimpleSelect($password); 
	$password_encrypted = md5($password);
	
	$q1 = "INSERT INTO `$mysqlMainDb`.user
		(nom, prenom, username, password, email, statut, department, am, registered_at, expires_at, lang)
		VALUES (". autoquote($nom_form) .",
			". autoquote($prenom_form) .",
			". autoquote($uname) .",
			'$password_encrypted',
			". autoquote($email) .",
			5,
			". intval($department) .",
			". autoquote($am) .",
			$registered_at, $expires_at,
			'$lang')";
	$inscr_user = db_query($q1);
	$last_id = mysql_insert_id();
	$result = db_query("SELECT user_id, nom, prenom FROM `$mysqlMainDb`.user WHERE user_id = $last_id");
	while ($myrow = mysql_fetch_array($result)) {
		$uid = $myrow[0];
		$nom = $myrow[1];
		$prenom = $myrow[2];
	}
	db_query("INSERT INTO `$mysqlMainDb`.loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
		VALUES ($uid, '".$_SERVER['REMOTE_ADDR']."', NOW(), 'LOGIN')");
	$_SESSION['uid'] = $uid;
	$_SESSION['statut'] = 5;
	$_SESSION['prenom'] = $prenom;
	$_SESSION['nom'] = $nom;
	$_SESSION['uname'] = $uname;
	// registration form
	$tool_content .= "<p>$langDear " . q("$prenom $nom") . ",</p>" .
			"<div class='success'>" .
			"<p>$langPersonalSettings</p>" .
			"</div>" .
			"<p>$langPersonalSettingsMore</p>";
	} else {
		// errors exist - registration failed
		$tool_content .= "<p class='caution'>";
		foreach ($registration_errors as $error) {
			$tool_content .= "$error";
		}
		$tool_content .= "<p><a href='$_SERVER[PHP_SELF]?prenom_form=$_POST[prenom_form]&amp;nom_form=$_POST[nom_form]&amp;uname=$_POST[uname]&amp;email=$_POST[email]&amp;am=$_POST[am]'>$langAgain</a></p>";
	}
} // end of registration

draw($tool_content,0);
