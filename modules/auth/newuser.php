<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/phpass/PasswordHash.php';

require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';

$tree = new hierarchy();
$userObj = new user();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');

$nameTools = $langUserDetails;
$navigation[] = array("url"=>"registration.php", "name"=> $langNewUser);

$disable_eclass_stud_reg = get_config('disable_eclass_stud_reg');
if ($disable_eclass_stud_reg) {
	$tool_content .= "<div class='td_main'>$langForbidden</div>";
	draw($tool_content,0);
	exit;
}

if (get_config('close_user_registration')) {
	$tool_content .= "<div class='td_main'>$langForbidden</div>";
	draw($tool_content,0);
	exit;
}

// display form
if (!isset($_POST['submit'])) {
	if (get_config('email_required')) {
		$email_message = "(*)";
	} else {
		$email_message = $langEmailNotice;
	}
	if (get_config('am_required')) {
		$am_message = "&nbsp;&nbsp;<small>(*)</small>";
	} else {
		$am_message = '';
	}
	@$tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>
        <fieldset>
        <legend>$langUserData</legend>
	<table width='100%' class='tbl'>
	<tr>
	<th class='left' width='180'>$langName:</th>
	<td colspan='2'><input type='text' name='prenom_form' size='30' maxlength='50' value='".q($_GET['prenom_form'])."' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langSurname:</th>
	<td colspan='2'><input type='text' name='nom_form' size='30' maxlength='100' value='".q($_GET['nom_form'])."' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langUsername:</th>
	<td colspan='2'><input type='text' name='uname' value='".q($_GET['uname'])."' size='30' maxlength='30' class='FormData_InputText' />&nbsp;&nbsp;<small>(*) $langUserNotice</small></td>
	</tr>
	<tr>
	<th class='left'>$langPass:</th>
	<td colspan='2'><input type='password' name='password1' size='30' maxlength='30' autocomplete='off' class='FormData_InputText' />&nbsp;&nbsp;<small>(*) $langUserNotice</small></td>
	</tr>
	<tr>
	<th class='left'>$langConfirmation:</th>
	<td colspan='2'><input type='password' name='password' size='30' maxlength='30' autocomplete='off' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)</small></td>
	</tr>
	<tr>
	<th class='left'>$langEmail:</th>
	<td valign='top'><input type='text' name='email' size='30' maxlength='100' value='".q($_GET['email'])."' class='FormData_InputText' /></td>
	<td><small>$email_message</small></td>
	</tr>
	<tr>
	<th class='left'>$langAm:</th>
	<td colspan='2' valign='top'><input type='text' name='am' size='20' maxlength='20' value='".q($_GET['am'])."' class='FormData_InputText' />$am_message</td>
	</tr>
        <tr>
	<th class='left'>$langPhone:</th>
	<td colspan='2' valign='top'><input type='text' name='phone' size='20' maxlength='20' value='".q($_GET['phone'])."' class='FormData_InputText' /></td>
	</tr>
	<tr>
	<th class='left'>$langFaculty:</th>
        <td colspan='2'>";
        list($js, $html) = $tree->buildUserNodePicker();
        $head_content .= $js;
        $tool_content .= $html;
	$tool_content .= "\n</td></tr>
	<tr>
	<th class='left'>$langLanguage:</th>
	<td width='1'>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td>
	<td><small>$langTipLang2</small></td>
	</tr>";
	if (get_config("display_captcha")) {
		$tool_content .= "<tr>
		<th class='left'><img id='captcha' src='{$urlAppend}include/securimage/securimage_show.php' alt='CAPTCHA Image' /></th>
		<td colspan='2'><input type='text' name='captcha_code' maxlength='6' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)&nbsp;$langTipCaptcha</small></td>
		</tr>";
	}
	$tool_content .= "<tr>
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
	if (get_config('email_required')) {
		$email_arr_value = true;
	} else {
		$email_arr_value = false;
	}
	if (get_config('am_required')) {
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
                                                   'phone' => false,
                                                   'am' => $am_arr_value));

        if (!isset($_POST['department'])) {
            $departments = array();
            $missing = false;
        } else
            $departments = $_POST['department'];

	$registration_errors = array();
	// check if there are empty fields
	if (!$missing) {
		$registration_errors[] = $langFieldsMissing;
	} else {
		$uname = canonicalize_whitespace($uname);
		// check if the username is already in use
		$q2 = "SELECT username FROM user WHERE username = ".autoquote($uname);
		$username_check = db_query($q2);
		if ($myusername = mysql_fetch_array($username_check)) {
			$registration_errors[] = $langUserFree;
		}
		if (get_config('display_captcha')) {
			// captcha check
			require_once 'include/securimage/securimage.php';
			$securimage = new Securimage();

			if ($securimage->check($_POST['captcha_code']) == false) {
				$registration_errors[] = $langCaptchaWrong;
			}
		}
	}
	if (!empty($email) and !email_seems_valid($email)) {
		$registration_errors[] = $langEmailWrong;
	}
	else {
		$email = mb_strtolower(trim($email));
	}
	if ($password != $_POST['password1']) { // check if the two passwords match
		$registration_errors[] = $langPassTwice;
	}
	if (count($registration_errors) == 0) {
		if (get_config('email_verification_required') && !empty($email)) {
			$verified_mail = 0;
			$vmail = TRUE;
		} else {
			$verified_mail = 2;
			$vmail = FALSE;
		}

		$registered_at = time();
		$expires_at = time() + get_config('account_duration');

		$password = unescapeSimple($password);
		$hasher = new PasswordHash(8, false);
		$password_encrypted = $hasher->HashPassword($password);

		$q1 = "INSERT INTO user
			(nom, prenom, username, password, email, statut, am, phone, registered_at, expires_at, lang, verified_mail)
			VALUES (". quote($nom_form) .",
				". quote($prenom_form) .",
				". quote($uname) .",
				'$password_encrypted',
				". quote($email) .",
				5,
				". quote($am) .",
                                ". quote($phone) .",
				$registered_at, $expires_at,
                                " . quote($language) . ",
                                $verified_mail)";
		$inscr_user = db_query($q1);
		$last_id = mysql_insert_id();
                $userObj->refresh($last_id, $departments);

		if ($vmail) {
			$hmac = token_generate($uname.$email.$last_id);
		}

		$emailsubject = "$langYourReg $siteName";
		$uname = autounquote($uname);
                $telephone = get_config('phone');
                $administratorName = get_config('admin_name');
                $emailhelpdesk = get_config('email_helpdesk');
		$emailbody = "$langDestination $prenom_form $nom_form\n" .
			"$langYouAreReg $siteName $langSettings $uname\n" .
			"$langPass: $password\n$langAddress $siteName: " .
			"$urlServer\n" .
			($vmail?"\n$langMailVerificationSuccess.\n$langMailVerificationClick\n$urlServer"."modules/auth/mail_verify.php?h=".$hmac."&id=".$last_id."\n":"") .
			"$langProblem\n$langFormula\n" .
			"$administratorName\n" .
			"$langManager $siteName \n$langTel $telephone\n" .
			"$langEmail: $emailhelpdesk";

		// send email to user
		if (!empty($email)) {
			send_mail('', '', '', $email, $emailsubject, $emailbody, $charset);
			$user_msg = $langPersonalSettings;
		}
		else {
			$user_msg = $langPersonalSettingsLess;
		}

		// verification needed
		if ($vmail) {
			$user_msg .= "$langMailVerificationSuccess: <strong>$email</strong>";
		}
		// login user
		else {
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
			$_SESSION['user_perso_active'] = true;
			$tool_content .= "<p>$langDear " . q("$prenom $nom") . ",</p>";
		}
		// user msg
		$tool_content .=
			"<div class='success'>" .
			"<p>$user_msg</p>" .
			"</div>";

		// footer msg
		if (!$vmail) {
			$tool_content .= "<p>$langPersonalSettingsMore</p>";
		}
		else {
			$tool_content .=
				"<p>$langMailVerificationSuccess2.
				 <br /><br />$click <a href='$urlServer'
				 class='mainpage'>$langHere</a> $langBackPage</p>";
		}
	} else {
		// errors exist - registration failed
		$tool_content .= "<p class='caution'>";
		foreach ($registration_errors as $error) {
			$tool_content .= " $error";
		}
		$tool_content .= "<p><a href='$_SERVER[SCRIPT_NAME]?" .
                        'prenom_form=' . urlencode($prenom_form) .
                        '&amp;nom_form=' . urlencode($nom_form) .
                        '&amp;uname=' . urlencode($uname) .
                        '&amp;email=' . urlencode($email) .
                        '&amp;am=' . urlencode($am) .
                        '&amp;phone=' . urlencode($phone) .
                        "'>$langAgain</a></p>";
	}
} // end of registration

draw($tool_content,0, null, $head_content);
