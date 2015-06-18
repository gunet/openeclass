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

$uid = (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) ? $_SESSION['uid'] : NULL;

if (empty($uid)) {
    $tool_content .= "<div class='alert alert-danger'>$langMailVerificationError2</div> ";
    draw($tool_content, 0);
    exit;
}
// user might already verified mail account or verification is no more needed
if (!get_config('email_verification_required') or
    get_mail_ver_status($uid) == EMAIL_VERIFIED or
    isset($_SESSION['cas_email']) or
    isset($_POST['enter'])) {
    	if (isset($_SESSION['mail_verification_required'])) {
        	unset($_SESSION['mail_verification_required']);
    	}
    	header("Location:" . $urlServer);
    	exit;
}

if (!empty($_POST['submit'])) {
    if (!empty($_POST['email']) && email_seems_valid($_POST['email'])) {
        $email = $_POST['email'];
        // user put a new email address update db and session
        if ($email != $_SESSION['email']) {
            $_SESSION['email'] = $email;
            Database::get()->query("UPDATE user SET email = ?s WHERE id = ?d", $email, $uid);            
        }
        //send new code
        $hmac = token_generate($_SESSION['uname'] . $email . $uid);

        $subject = $langMailChangeVerificationSubject;
        $MailMessage = sprintf($mailbody1 . $langMailVerificationChangeBody, $urlServer . 'modules/auth/mail_verify.php?h=' . $hmac . '&id=' . $uid);
        $emailhelpdesk = get_config('email_helpdesk');
        $emailAdministrator = get_config('email_sender');
        if (!send_mail($siteName, $emailAdministrator, '', $email, $subject, $MailMessage, $charset, "Reply-To: $emailhelpdesk")) {
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
			$tool_content .= "<div class='alert alert-info'>$langEmailInfo <br><br> $langEmailNotice</div>";
	} else if (!empty($_SESSION['mail_verification_required']) && ($_SESSION['mail_verification_required'] === 1)) {
	    	$tool_content .= "<div class='alert alert-info'>$langMailVerificationReq</div>";
	}
}

if (empty($_POST['email']) or !email_seems_valid($_POST['email'])) {
    $tool_content .= "<div class='form-wrapper'>
    	<form class='form-horizontal' method='post' role='form' action='$_SERVER[SCRIPT_NAME]'>
        <fieldset>
			<div class='form-group'>
                <label class='col-sm-2'>$lang_email:</label>
                <div class='col-sm-10'>
					<input class='form-control' type='text' name='email' size='30' maxlength='40' value='" . q($_SESSION['email']) . "' placeholder='$langMailVerificationAddrChange'>
				</div>
			</div>
			<div class='form-group'>
				<div class='col-sm-offset-2 col-sm-10'>
					<input class='btn btn-primary' type='submit' name='submit' value='$langMailVerificationNewCode'>
					<input class='btn btn-primary' type='submit' name='enter' value='$langCancelAndEnter'>
				</div>
			</div>
        </fieldset>
    </form>
    </div>";
}

draw($tool_content, $uid? 1: 0);

exit;
