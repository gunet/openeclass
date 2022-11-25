<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

/*
 * User mail verification
 *
 * @author Kapetanakis Giannis <bilias@edu.physics.uoc.gr>
 *
 * @abstract This component sends email verification code and can change user's email address
 *
 */

$require_login = true;
$require_valid_uid = true;
$mail_ver_excluded = true;
include '../../include/baseTheme.php';
include 'include/sendMail.inc.php';
$toolName = $langMailVerify;

if (isset($_GET['from_profile'])) {
    $navigation[] = array('url' => $urlAppend . 'main/profile/display_profile.php',
                          'name' => $langMyProfile);
}

$uid = (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) ? $_SESSION['uid'] : NULL;

if (empty($uid)) {
    $tool_content .= "<div class='alert alert-danger'>$langMailVerificationError2</div> ";
    draw($tool_content, 0);
    exit;
}
// email address may have already been verified or verification may no longer be needed
if (!get_config('email_verification_required') or
        get_mail_ver_status($uid) == EMAIL_VERIFIED or
        (isset($_POST['enter']) and !get_config('email_required'))) {
    if (isset($_SESSION['mail_verification_required'])) {
        unset($_SESSION['mail_verification_required']);
    }
    redirect_to_home_page('main/portfolio.php');
}

if (!empty($_POST['submit'])) {
    if (!empty($_POST['email']) && valid_email($_POST['email'])) {
        $email = $_POST['email'];
        // user put a new email address update db and session
        if ($email != $_SESSION['email']) {
            $_SESSION['email'] = $email;
            Database::get()->query("UPDATE user SET email = ?s WHERE id = ?d", $email, $uid);
        }
        //send new code
        if (get_config('case_insensitive_usernames')) {
            $hmac = token_generate(strtolower($_SESSION['uname']) . $email . $uid);
        } else {
            $hmac = token_generate($_SESSION['uname'] . $email . $uid);
        }

        $activateLink = "<a href='".$urlServer."modules/auth/mail_verify.php?h=".$hmac."&amp;id=".$uid."'>".$urlServer."modules/auth/mail_verify.php?h=".$hmac."&amp;id=".$uid."</a>";

        $subject = $langMailChangeVerificationSubject;

        $header_html_topic_notify = "<!-- Header Section -->
    <div id='mail-header'>
        <br>
        <div>
            <div id='header-title'>$subject</div>
        </div>
    </div>";

        $body_html_topic_notify = "<!-- Body Section -->
    <div id='mail-body'>
        <br>
        <div id='mail-body-inner'>".
            sprintf($langMailVerificationChangeBody, $activateLink)."
        </div>
    </div>";

        $MailMessage = $header_html_topic_notify.$body_html_topic_notify;

        $plainMailMessage = html2text($MailMessage);

        $emailhelpdesk = get_config('email_helpdesk');
        $emailAdministrator = get_config('email_sender');
        if (!send_mail_multipart($siteName, $emailAdministrator, '', $email, $subject, $plainMailMessage, $MailMessage)) {
            $mail_ver_error = sprintf("<div class='alert alert-warning'>" . $langMailVerificationError, $email, $urlServer . "auth/registration.php", "<a href='mailto:" . q($emailhelpdesk) . "' class='mainpage'>" . q($emailhelpdesk) . "</a>.</div>");
            $tool_content .= $mail_ver_error;
        } else {
            $tool_content .=
                action_bar(array(
                    array(
                        'title' => $langBack,
                        'icon' => 'fa-reply',
                        'level' => 'primary-label',
                        'url' => $urlAppend))) .
                "<div class='alert alert-success'>$langMailVerificationSuccess4</div> ";
        }
    }
    // email wrong or empty
    else {
        $tool_content .= "<div class='alert alert-danger'>$langMailVerificationWrong</div> ";
    }
} else {
    if (get_config('alt_auth_stud_reg') == 2) {
        if (get_config('email_required')) {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-info'>$langMailVerificationReq</div></div>";
        } else {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-info'>$langEmailInfo <br><br> $langEmailNotice</div></div>";
        }
    } else if (isset($_SESSION['mail_verification_required'])) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-info'>$langMailVerificationReq</div></div>";
    }
}

if (empty($_POST['email']) or !valid_email($_POST['email'])) {
    $tool_content .= "<div class='col-12'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' method='post' role='form' action='$_SERVER[SCRIPT_NAME]'>
        <fieldset>
            <div class='form-group'>
                <label class='col-sm-6 control-label-notes'>$lang_email</label>
                <div class='col-sm-12'>
                    <input class='form-control' type='text' name='email' size='30' maxlength='40' value='" . q($_SESSION['email']) . "' placeholder='$langMailVerificationAddrChange'>
                </div>
            </div>
            <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-center align-items-center'>
                    <input class='btn submitAdminBtn' type='submit' name='submit' value='$langMailVerificationNewCode'>" .
                    (isset($_GET['from_profile']) || get_config('email_required')? '':
                        " <input class='btn submitAdminBtn ms-1' type='submit' name='enter' value='$langCancelAndEnter'>") .
                    (isset($_GET['from_profile']) && !get_config('mail_verification_required')?
                        " <a href='{$urlAppend}main/profile/display_profile.php' class='btn btn-outline-secondary cancelAdminBtn ms-1' type='button'>$langCancel</a>": '') . "
                </div>
            </div>
        </fieldset>
    </form>
    </div></div>";
}

draw($tool_content, $uid? 1: 0);

exit;
