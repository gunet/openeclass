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

/**
 * @file newuser.php
 * @brief user registration process
 */

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/phpass/PasswordHash.php';

require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';

$display_captcha = get_config("display_captcha") && function_exists('imagettfbbox');

$tree = new Hierarchy();
$userObj = new User();

load_js('jstree3d');
load_js('pwstrength.js');
$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

    var lang = {
hContent;
$head_content .= "pwStrengthTooShort: '" . js_escape($langPwStrengthTooShort) . "', ";
$head_content .= "pwStrengthWeak: '" . js_escape($langPwStrengthWeak) . "', ";
$head_content .= "pwStrengthGood: '" . js_escape($langPwStrengthGood) . "', ";
$head_content .= "pwStrengthStrong: '" . js_escape($langPwStrengthStrong) . "'";
$head_content .= <<<hContent
    };

    $(document).ready(function() {
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });
    });

/* ]]> */
</script>
hContent;

$pageName = $langUserDetails;
$navigation[] = array("url" => "registration.php", "name" => $langNewUser);

$user_registration = get_config('user_registration');
$eclass_stud_reg = get_config('eclass_stud_reg'); // student registration via eclass

if (!$user_registration or $eclass_stud_reg != 2) {
    $tool_content .= "<div class='alert alert-info'>$langStudentCannotRegister</div>";
    draw($tool_content, 0);
    exit;
}

// display form
if (!isset($_POST['submit'])) {
    if (get_config('email_required')) {
        $email_message = $langCompulsory;
    } else {
        $email_message = $langOptional;
    }
    if (get_config('am_required')) {
        $am_message = $langCompulsory;
    } else {
        $am_message = $langOptional;
    }
    $tool_content .= action_bar(array(
                    array('title' => $langBack,
                        'url' => "{$urlAppend}modules/auth/registration.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')), false);
    @$tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' onsubmit='return validateNodePickerForm();'>
            <fieldset>
            <div class='form-group'>
                <label for='Name' class='col-sm-2 control-label'>$langName:</label>
                <div class='col-sm-10'>
                  <input class='form-control' type='text' name='givenname_form' size='30' maxlength='50' value='" . q($_GET['givenname_form']) . "' placeholder='$langName'>
                </div>
            </div>
            <div class='form-group'>
                <label for='SurName' class='col-sm-2 control-label'>$langSurname:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='surname_form' size='30' maxlength='100' value='" . q($_GET['surname_form']) . "' placeholder='$langSurname'>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserName' class='col-sm-2 control-label'>$langUsername:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='uname' value='" . q($_GET['uname']) . "' size='30' maxlength='30'  autocomplete='off' placeholder='$langUserNotice'>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserPass' class='col-sm-2 control-label'>$langPass:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='password' name='password1' size='30' maxlength='30' autocomplete='off'  id='password' placeholder='$langUserNotice'><span id='result'></span>
                </div>
            </div>
            <div class='form-group'>
              <label for='UserPass2' class='col-sm-2 control-label'>$langConfirmation:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='password' name='password' size='30' maxlength='30' autocomplete='off'/>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserEmail' class='col-sm-2 control-label'>$langEmail:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='email' size='30' maxlength='100' value='" . q($_GET['email']) . "' placeholder='$email_message'>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserAm' class='col-sm-2 control-label'>$langAm:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='am' size='20' maxlength='20' value='" . q($_GET['am']) . "' placeholder='$am_message'>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserPhone' class='col-sm-2 control-label'>$langPhone:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='phone' size='20' maxlength='20' value='" . q($_GET['phone']) . "' placeholder = '$langOptional'>
                </div>
            </div>
            <div class='form-group'>
              <label for='UserFac' class='col-sm-2 control-label'>$langFaculty:</label>
                <div class='col-sm-10'>";
            list($js, $html) = $tree->buildUserNodePicker();
            $head_content .= $js;
            $tool_content .= $html;
            $tool_content .= "</div>
            </div>
            <div class='form-group'>
              <label for='UserLang' class='col-sm-2 control-label'>$langLanguage:</label>
              <div class='col-sm-10'>";
            $tool_content .= lang_select_options('localize', "class='form-control'");
            $tool_content .= "</div>
            </div>";
            if ($display_captcha) {
                $tool_content .= "<div class='form-group'>
                      <div class='col-sm-offset-2 col-sm-10'><img id='captcha' src='{$urlAppend}include/securimage/securimage_show.php' alt='CAPTCHA Image' /></div><br>
                      <label for='Captcha' class='col-sm-2 control-label'>$langCaptcha:</label>
                      <div class='col-sm-10'><input type='text' name='captcha_code' maxlength='6'/></div>
                    </div>";
            }
        $tool_content .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'>
                        <input class='btn btn-primary' type='submit' name='submit' value='" . q($langRegistration) . "' />
                    </div></div>
        </fieldset>
      </form>
      </div>";

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
        'surname_form' => true,
        'givenname_form' => true,
        'password' => true,
        'password1' => true,
        'email' => $email_arr_value,
        'phone' => false,
        'am' => $am_arr_value));

    if (!isset($_POST['department'])) {
        $departments = array();
        $missing = false;
    } else {
        $departments = $_POST['department'];
    }

    $registration_errors = array();
    // check if there are empty fields
    if (!$missing) {
        $registration_errors[] = $langFieldsMissing;
    } else {
        $uname = canonicalize_whitespace($uname);
        // check if the username is already in use
        $username_check = Database::get()->querySingle("SELECT username FROM user WHERE username = ?s", $uname);
        if ($username_check) {
            $registration_errors[] = $langUserFree;
        }
        if ($display_captcha) {
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
    } else {
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
        
        $hasher = new PasswordHash(8, false);
        $password_encrypted = $hasher->HashPassword($password);

        $q1 = Database::get()->query("INSERT INTO user (surname, givenname, username, password, email,
                                 status, am, phone, registered_at, expires_at,
                                 lang, verified_mail, whitelist, description)
                      VALUES (?s, ?s, ?s, '$password_encrypted', ?s, " . USER_STUDENT . ", ?s, ?s, " . DBHelper::timeAfter() . ",
                              " . DBHelper::timeAfter(get_config('account_duration')) . ", ?s, $verified_mail, '', '')",
                            $surname_form, $givenname_form, $uname, $email, $am, $phone, $language);
        $last_id = $q1->lastInsertID;
        $userObj->refresh($last_id, $departments);
        user_hook($last_id);

        if ($vmail) {
            $hmac = token_generate($uname . $email . $last_id);
        }

        $emailsubject = "$langYourReg $siteName";
        $telephone = get_config('phone');
        $administratorName = get_config('admin_name');
        $emailhelpdesk = get_config('email_helpdesk');
        $emailbody = "$langDestination $givenname_form $surname_form\n" .
                "$langYouAreReg $siteName $langSettings $uname\n" .
                "$langPass: $password\n$langAddress $siteName: " .
                "$urlServer\n" .
                ($vmail ? "\n$langMailVerificationSuccess.\n$langMailVerificationClick\n$urlServer" . "modules/auth/mail_verify.php?h=" . $hmac . "&id=" . $last_id . "\n" : "") .
                "$langProblem\n$langFormula\n" .
                "$administratorName\n" .
                "$langManager $siteName \n$langTel $telephone\n" .
                "$langEmail: $emailhelpdesk";

        // send email to user
        if (!empty($email)) {
            send_mail('', '', '', $email, $emailsubject, $emailbody, $charset);
            $user_msg = $langPersonalSettings;
        } else {
            $user_msg = $langPersonalSettingsLess;
        }

        // verification needed
        if ($vmail) {
            $user_msg .= "$langMailVerificationSuccess: <strong>$email</strong>";
        }
        // login user
        else {
            $myrow = Database::get()->querySingle("SELECT id, surname, givenname FROM user WHERE id = ?d", $last_id);
            $uid = $myrow->id;
            $surname = $myrow->surname;
            $givenname = $myrow->givenname;

            Database::get()->query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                             VALUES (?d, ?s, NOW(), 'LOGIN')", $uid, $_SERVER['REMOTE_ADDR']);
            $_SESSION['uid'] = $uid;
            $_SESSION['status'] = USER_STUDENT;
            $_SESSION['givenname'] = $givenname_form;
            $_SESSION['surname'] = $surname_form;
            $_SESSION['uname'] = $uname;
            $session->setLoginTimestamp();
            $tool_content .= "<p>$langDear " . q("$givenname_form $surname_form") . ",</p>";
        }
        // user msg
        $tool_content .= "<div class='alert alert-success'><p>$user_msg</p></div>";

        // footer msg
        if (!$vmail) {
            $tool_content .= "<p>$langPersonalSettingsMore</p>";
        } else {
            $tool_content .=
                    "<p>$langMailVerificationSuccess2.
                                 <br /><br />$click <a href='$urlServer'
                                 class='mainpage'>$langHere</a> $langBackPage</p>";
        }
    } else {
        // errors exist - registration failed
        $tool_content .= "<div class='alert alert-danger'>";
        foreach ($registration_errors as $error) {
            $tool_content .= " $error";
        }
        $tool_content .= "</div><p><a href='$_SERVER[SCRIPT_NAME]?" .
                'givenname_form=' . urlencode($givenname_form) .
                '&amp;surname_form=' . urlencode($surname_form) .
                '&amp;uname=' . urlencode($uname) .
                '&amp;email=' . urlencode($email) .
                '&amp;am=' . urlencode($am) .
                '&amp;phone=' . urlencode($phone) .
                "'>$langAgain</a></p>";
    }
} // end of registration

draw($tool_content, 0, null, $head_content);
