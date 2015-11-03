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
 * @abstract This component verifies user's email address according to the verification code
 *
 */

$mail_ver_excluded = true;
include '../../include/baseTheme.php';
include 'include/sendMail.inc.php';
require_once 'include/lib/hierarchy.class.php';
$pageName = $langMailVerify;
$tree = new Hierarchy();

$code = (isset($_GET['h']) && ctype_xdigit($_GET['h'])) ? $_GET['h'] : NULL;
$req_id = (isset($_GET['rid']) && is_numeric($_GET['rid'])) ? intval($_GET['rid']) : NULL;
$u_id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? intval($_GET['id']) : NULL;

if (!empty($code) and (!empty($u_id) or !empty($req_id))) {
    // user has applied for account
    if (!empty($req_id)) {
        $qry = "SELECT id, username, email, verified_mail, givenname, surname,
                       faculty_id, phone, am, state, status, comment, lang
                    FROM user_request WHERE id = $req_id";
        $id = $req_id;
    }
    // no user application. user account has been created with pending mail verification
    elseif (!empty($u_id)) {
        $qry = "SELECT id, username, email, verified_mail FROM user WHERE id = $u_id";
        $id = $u_id;
    }
    // no id given
    else {
        $user_error_msg = $langMailVerifyNoId;        
    }
    $res = Database::get()->querySingle($qry);
    if ($res) {                                
            if (get_config('case_insensitive_usernames')) {
                $username = strtolower($res->username);
            } else {
                $username = $res->username;
            }
            $email = $res->email;
            // success
            if (token_validate($username . $email . $id, $code)) {
                $verified_mail = intval($res->verified_mail);
                // update user's application
                if (!empty($req_id) and ($verified_mail !== 1)) {
                    Database::get()->query("UPDATE user_request SET verified_mail = 1 WHERE id = ?d", $req_id);
                    $department = $tree->getFullPath($res->faculty_id);
                    $prof = isset($res->status) && intval($res->status) === 1 ? 1 : NULL;
                    $givenname = $res->givenname;
                    $surname = $res->surname;
                    $am = $res->am;
                    $usercomment = $res->comment;
                    $usermail = $res->email;
                    $userphone = $res->phone;

                    $subject = $prof ? $mailsubject : $mailsubject2;

                    $emailAdministrator = get_config('email_sender');

                    $header_html_topic_notify = "<!-- Header Section -->
                    <div id='mail-header'>
                        <br>
                        <div>
                            <div id='header-title'>$mailbody1</div>
                        </div>
                    </div>";

                    $body_html_topic_notify = "<!-- Body Section -->
                    <div id='mail-body'>
                        <br>
                        <div id='mail-body-inner'>
                        $mailbody2 $givenname $surname $mailbody3 $mailbody4 $mailbody5 ".($prof ? $mailbody6 : $mailbody8)."
                            <ul id='forum-category'>
                                <li><span><b>$langFaculty:</b></span> <span>$department</span></li>
                                <li><span><b>$langComments:</b></span> <span>$usercomment</a></span></li>
                                <li><span><b>$langAm :</b></span> <span>$am</span></li>
                                <li><span><b>$langProfUname:</b></span> <span> $username </span></li>
                                <li><span><b>$langProfEmail:</b></span> <span> $usermail </span></li>
                                <li><span><b>$contactphone:</b></span> <span> $userphone </span></li>
                            </ul><br><br>$logo
                        </div>
                    </div>";

                    $MailMessage = $header_html_topic_notify.$body_html_topic_notify;

                    $plainMailMessage = html2text($MailMessage);

                    if (!send_mail_multipart($siteName, $emailAdministrator, '', get_config('email_helpdesk'), $subject, $plainMailMessage, $MailMessage, $charset, "Reply-To: $usermail")) {
                        $user_msg = $langMailErrorMessage;
                    } else {
                        $user_msg = $infoprof;
                    }
                }
                // update user's account
                elseif (!empty($u_id) and ($verified_mail !== 1)) {
                    Database::get()->query("UPDATE `user` SET verified_mail = 1 WHERE id = ?d", $u_id);                    
                    $user_msg = $langMailVerifySuccessU;
                    if (isset($_SESSION['mail_verification_required'])) {
                        unset($_SESSION['mail_verification_required']);
                    }
                }
                // don't update twice (application)
                elseif (($verified_mail == 1) && !empty($req_id)) {
                    $user_msg = $infoprof;
                    if (isset($_SESSION['mail_verification_required'])) {
                        unset($_SESSION['mail_verification_required']);
                    }
                    $tool_content = "<div class='alert alert-info'>$langMailVerifySuccess2 </div>
					<p>$user_msg<br /><br />$click <a href='$urlServer' class='mainpage'>$langHere</a>
						$langBackPage</p>";
                    draw($tool_content, 0);
                    exit;
                }
                // don't update twice (no application)
                elseif (($verified_mail == 1) && !empty($u_id)) {
                    $user_msg = $langMailVerifySuccessU;
                    if (isset($_SESSION['mail_verification_required'])) {
                        unset($_SESSION['mail_verification_required']);
                    }
                    $tool_content = "<div class='alert alert-info'>$langMailVerifySuccess2 </div>
					<p>$user_msg<br /><br />$click <a href='$urlServer' class='mainpage'>$langHere</a>
						$langBackPage</p>";
                    draw($tool_content, 0);
                    exit;
                }

                $tool_content = "<div class='alert alert-success'>$langMailVerifySuccess </div>
					<p>$user_msg<br /><br />$click <a href='$urlServer' class='mainpage'>$langHere</a>
					$langBackPage</p>";
            }
            // code and id given but they are wrong!
            else {
                $user_error_msg = $langMailVerifyCodeError;
                $tool_content = "<div class='alert alert-danger'>$user_error_msg </div>
					<p>$click <a href='$urlServer' class='mainpage'>$langHere</a>
					$langBackPage</p>";
            }
    } else {
        if (!empty($req_id)) {
            $user_error_msg = $langMailVerifyNoApplication;
        } else {
            $user_error_msg = $langMailVerifyNoAccount;
        }
        $tool_content = "<div class='alert alert-danger'>$user_error_msg </div>
                            <p>$click <a href='$urlServer' class='mainpage'>$langHere</a>
                            $langBackPage</p>";
    }    
}
// no code given and no id given
else {
    $user_error_msg = $langMailVerifyNoCode;
    $tool_content = "<div class='alert alert-danger'>$user_error_msg </div>
		<p>$click <a href='$urlServer' class='mainpage'>$langHere</a>
		$langBackPage</p>";
}
draw($tool_content, 0);
