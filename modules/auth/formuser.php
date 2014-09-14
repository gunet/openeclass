<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

include '../../include/baseTheme.php';
include 'include/sendMail.inc.php';
include 'include/lib/hierarchy.class.php';

$tree = new Hierarchy();

load_js('jquery');
load_js('jquery-ui');
load_js('jstree');

$navigation[] = array('url' => 'registration.php', 'name' => $langNewUser);

$prof = isset($_REQUEST['p']) ? intval($_REQUEST['p']) : 0;
$am = !empty($_REQUEST['am']) ? intval($_REQUEST['am']) : '';
$nameTools = $prof ? $langReqRegProf : $langUserRequest;

$user_registration = get_config('user_registration');
$eclass_prof_reg = get_config('eclass_prof_reg');
$eclass_stud_reg = get_config('eclass_stud_reg'); // student registration via eclass
// security check
if (!$user_registration) {
    $tool_content .= "<div class='caution'>$langForbidden</div>";
    draw($tool_content, 0);
    exit;
}
if ($prof and !$eclass_prof_reg) {
    $tool_content .= "<div class='caution'>$langForbidden</div>";
    draw($tool_content, 0);
    exit;
}

if (!$prof and $eclass_stud_reg != 1) {
    $tool_content .= "<div class='caution'>$langForbidden</div>";
    draw($tool_content, 0);
    exit;
}

$am_required = !$prof && get_config('am_required');
$errors = array();

$all_set = register_posted_variables(array(
    'usercomment' => true,
    'givenname' => true,
    'surname' => true,
    'username' => true,
    'userphone' => $prof,
    'usermail' => true,
    'am' => $am_required,
    'department' => true,
    'captcha_code' => false));

if (!$all_set) {
    $errors[] = $langFieldsMissing;
}

if (!email_seems_valid($usermail)) {
    $errors[] = $langEmailWrong;
    $all_set = false;
} else {
    $usermail = mb_strtolower(trim($usermail));
}

// check if the username is already in use
$username = canonicalize_whitespace($username);
if (user_exists($username)) {
    $errors[] = $langUserFree;
    $all_set = false;
}

// check if exists user request with the same username
if (user_app_exists($username)) {
    $errors[] = $langUserFree3;
    $all_set = false;
}

if (get_config("display_captcha")) {
    // captcha check
    include 'include/securimage/securimage.php';
    $securimage = new Securimage();
    if ($securimage->check($captcha_code) == false) {
        $errors[] = $langCaptchaWrong;
        $all_set = false;
    }
}

if (isset($_POST['submit'])) {
    foreach ($errors as $message) {
        $tool_content .= "<p class='alert1'>$message</p>";
    }
}

if ($all_set) {
    $email_verification_required = get_config('email_verification_required');
    $emailhelpdesk = get_config('email_helpdesk');
    if (!$email_verification_required) {
        $verified_mail = 2;
    } else {
        $verified_mail = 0;
    }

    // register user request
    $status = $prof ? USER_TEACHER : USER_STUDENT;
    $res = Database::get()->query("INSERT INTO user_request SET
			givenname = ?s, surname = ?s, username = ?s, email = ?s,
			am = ?s, faculty_id = ?d, phone = ?s,
			state = 1, status = $status,
			verified_mail = ?d, date_open = " . DBHelper::timeAfter() . ",
			comment = ?s, lang = ?s, request_ip = ?s",
            $givenname, $surname, $username, $usermail, $am, $department, $userphone, $verified_mail, $usercomment, $language, $_SERVER['REMOTE_ADDR']);   
    $request_id = $res->lastInsertID;

    // email does not need verification -> mail helpdesk
    if (!$email_verification_required) {
        //----------------------------- Email Request Message --------------------------
        $dep_body = $tree->getFullPath($department);
        $subject = $prof ? $mailsubject : $mailsubject2;
        $MailMessage = $mailbody1 . $mailbody2 . "$givenname $surname\n\n" .
                $mailbody3 . $mailbody4 . $mailbody5 .
                ($prof ? $mailbody6 : $mailbody8) .
                "\n\n$langFaculty: $dep_body\n$langComments: $usercomment\n" .
                "$langAm: $am\n" .
                "$langProfUname: $username\n$langProfEmail : $usermail\n" .
                "$contactphone: $userphone\n\n\n$logo\n\n";

        if (!send_mail('', $usermail, '', $emailhelpdesk, $subject, $MailMessage, $charset)) {
            $tool_content .= "<p class='alert1'>$langMailErrorMessage&nbsp; <a href='mailto:$emailhelpdesk' class='mainpage'>$emailhelpdesk</a>.</p>";
        }

        // User Message
        $tool_content .= "<div class='success'>" .
                ($prof ? $langDearProf : $langDearUser) .
                "!<br />$success</div><p>$infoprof<br /><br />$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</p>";
    }
    // email needs verification -> mail user
    else {
        $hmac = token_generate($username . $usermail . $request_id);
        //----------------------------- Email Verification -----------------------
        $subject = $langMailVerificationSubject;
        $MailMessage = sprintf($mailbody1 . $langMailVerificationBody1, $urlServer . 'modules/auth/mail_verify.php?h=' . $hmac . '&rid=' . $request_id);
        $emailhelpdesk = get_config('email_helpdesk');
        if (!send_mail('', $emailhelpdesk, '', $usermail, $subject, $MailMessage, $charset)) {
            $mail_ver_error = sprintf("<p class='alert1'>" . $langMailVerificationError, $usermail, $urlServer . "modules/auth/registration.php", "<a href='mailto:$emailhelpdesk' class='mainpage'>$emailhelpdesk</a>.</p>");
            $tool_content .= $mail_ver_error;
            draw($tool_content, 0);
            exit;
        }

        // User Message
        $tool_content .= "<div class='success'>" .
                ($prof ? $langDearProf : $langDearUser) .
                "<br />$langMailVerificationSuccess
			$langMailVerificationSuccess2</div><br /><p>$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</p>";
    }
    draw($tool_content, 0);
    exit();

// first time we visit the form or on error
} else {
    // display the form
    $phone_star = $prof ? '&nbsp;&nbsp;(*)' : '';
    $tool_content .= "<p>" .
            ($prof ? $langInfoProfReq : $langInfoStudReq) . "</p><br />
        <form action='$_SERVER[SCRIPT_NAME]' method='post'>
         <input type='hidden' name='p' value='$prof'>
         <fieldset>
          <legend>$langUserData</legend>
          <table class='tbl'>
          <tr>
            <th>$langName</th>
            <td><input type='text' name='givenname' value='" . q($givenname) . "' size='30' maxlength='60'>&nbsp;&nbsp;(*)</td>
          </tr>
          <tr>
            <th>$langSurname</th>
            <td><input type='text' name='surname' value='" . q($surname) . "' size='30' maxlength='60'>&nbsp;&nbsp;(*)</td>
          </tr>
          <tr>
            <th>$langPhone</th>
            <td colspan='2'><input type='text' name='userphone' value='" . q($userphone) . "' size='20' maxlength='20'>$phone_star</td>
          <tr>
            <th>$langUsername</th>
            <td><input type='text' name='username' size='30' maxlength='50' value='" . q($username) . "'>&nbsp;&nbsp;<small>(*)&nbsp;$langUserNotice</small></td>
          </tr>
          <tr>
            <th>$langProfEmail</th>
            <td><input type='text' name='usermail' value='" . q($usermail) . "' size='30' maxlength='100'>&nbsp;&nbsp;(*)</td>
          </tr>";
    if (!$prof) {
        $tool_content .= "<tr>
                <th>$langAm</th>
                <td colspan='2'><input type='text' name='am' value='" . q($am) . "' size='20' maxlength='20'>" .
                ($am_required ? '&nbsp;&nbsp;(*)' : '') . "</td>
                </tr>";
    }
    $tool_content .= "<tr>
            <th>$langComments</th>
            <td><textarea name='usercomment' cols='30' rows='4'>" . q($usercomment) . "</textarea>&nbsp;&nbsp;<small>(*) $profreason</small></td>
            </tr>
            <tr>
            <th>$langFaculty&nbsp;</th>
            <td>";
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => $department, 'tree' => null, 'useKey' => 'id', 'where' => "AND node.allow_user = true", 'multiple' => false));
    $head_content .= $js;
    $tool_content .= $html;
    $tool_content .= "</td></tr><tr><th>$langLanguage</th><td>";
    $tool_content .= lang_select_options('localize');
    $tool_content .= "</td></tr>";
    if (get_config("display_captcha")) {
        $tool_content .= "<tr>
		<th class='left'><img id='captcha' src='../../include/securimage/securimage_show.php' alt='CAPTCHA Image' /></th>
		<td colspan='2'><input type='text' name='captcha_code' maxlength='6' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)&nbsp;$langTipCaptcha</small></td>
		</tr>";
    }
    $tool_content .= "<tr>
        <td>&nbsp;</td>
        <td class='right'><input type='submit' class='ButtonSubmit' name='submit' value='" . q($langSubmitNew) . "' /></td>
        </tr>
        </table>
        </fieldset>
        </form>
        <div class='right smaller'>$langRequiredFields</div>";
}

draw($tool_content, 0, null, $head_content);
