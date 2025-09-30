<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/*
 * Password reset component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component resets the user's password after verifying
 * his/hers  information through a challenge/response system.
 *
 */

include '../../include/baseTheme.php';
include 'modules/auth/auth.inc.php';
include 'include/sendMail.inc.php';

// Password reset link is valid for 1 hour = 3600 sec
define('TOKEN_VALID_TIME', 3600);

$emailhelpdesk = q(get_config('email_helpdesk'));

if (isset($_REQUEST['u']) and isset($_REQUEST['h'])) {
    $data['change_ok'] = false;
    $data['userUID'] = intval($_REQUEST['u']);
    $valid = token_validate('password' . $data['userUID'], $_REQUEST['h'], TOKEN_VALID_TIME);
    $res = Database::get()->querySingle("SELECT id, last_passreminder FROM user WHERE id = ?d AND password NOT IN ('" . implode("', '", $auth_ids) . "')", $data['userUID']);
    $error_messages = array();
    if ($valid and $res and !is_null($res->last_passreminder)) {
        $data['is_valid'] = true;
        if (isset($_POST['newpass']) and isset($_POST['newpass1']) and
                count($error_messages = acceptable_password($_POST['newpass'], $_POST['newpass1'])) == 0) {
                    $q1 = Database::get()->query("UPDATE user SET password = ?s, last_passreminder = null WHERE id = ?d", password_hash($_POST['newpass'], PASSWORD_DEFAULT), $data['userUID']);
            if ($q1->affectedRows > 0) {
                Session::flash('message', $langAccountResetSuccess1);
                Session::flash('alert-class', 'alert-success');
                $change_ok = true;
            }
        } elseif (count($error_messages)) {
            Session::flash('message', $error_messages);
            Session::flash('alert-class', 'alert-warning');
        }
    } else {
        Session::flash('message', "$langAccountResetInvalidLink");
        Session::flash('alert-class', 'alert-danger');
    }
} elseif (isset($_POST['send_link'])) {

    $email = isset($_POST['email']) ? mb_strtolower(trim($_POST['email'])) : '';
    $userName = isset($_POST['userName']) ? canonicalize_whitespace($_POST['userName']) : '';
    /* *** If valid e-mail address was entered, find user and send email *****/
    $res = Database::get()->querySingle("SELECT u.id, u.surname, u.givenname, u.username, u.password, u.status FROM user u
                    LEFT JOIN admin a ON (a.user_id = u.id)
                    WHERE u.email = ?s AND
                    BINARY u.username = ?s AND
                    a.user_id IS NULL AND
                    (u.last_passreminder IS NULL OR DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR) >= u.last_passreminder)", $email, $userName); //exclude admins and currently pending requests

    $found_editable_password = false;
    if ($res) {
        if (password_is_editable($res->password)) {
            $found_editable_password = true;
            //prepare instruction for password reset
            $text = $langPassResetIntro . $emailhelpdesk;
            $text .= $langHowToResetTitle;
            $text .= $langPassResetGoHere;
            $text .= $urlServer . "modules/auth/lostpass.php?u=$res->id&h=" .
                token_generate('password' . $res->id, true);

            $header_html_topic_notify = "<!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <div id='header-title'>$langPassResetIntro</div>
                    <div>$langPassResetIntro2 $emailhelpdesk</div>
                </div>
            </div>";

            $body_html_topic_notify = "<!-- Body Section -->
            <div id='mail-body'>
                <br>
                <div><b>$langHowToResetTitle</b></div><br>
                <div id='mail-body-inner'>
                    $langPassResetGoHere<br><br><a href='$urlServer"."modules/auth/lostpass.php?u=$res->id&h=" .
                token_generate('password' . $res->id, true)."'>$urlServer"."modules/auth/lostpass.php?u=$res->id&h=" .
                token_generate('password' . $res->id, true)."</a>
                </div>
            </div>";

            $text = $header_html_topic_notify.$body_html_topic_notify;

            $plainText = html2text($text);
            // store the timestamp of this action (password reminding and token generation)
            Database::get()->query("UPDATE user SET last_passreminder = CURRENT_TIMESTAMP WHERE id = ?d" , $res->id);
        } else { //other type of auth...
            $auth = array_search($res->password, $auth_ids) or 1;
            $message = "$langPassCannotChange1 $langPassCannotChange2 " . get_auth_info($auth) . "
             $langPassCannotChange3 <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a> $langPassCannotChange4";
            Session::flash('message', $message);
            Session::flash('alert-class', 'alert-danger');
        }

        /* *** Account details found, now send e-mail **** */
        if ($found_editable_password) {
            $emailsubject = $lang_remind_pass;
            if (!send_mail_multipart('', '', '', $email, $emailsubject, $plainText, $text)) {
                $message_error = "$langAccountEmailError1 $langAccountEmailError2 $email.
                    $langAccountEmailError3 <a href='mailto:$emailhelpdesk'>$emailhelpdesk</a>";
                Session::flash('message', $message_error);
                Session::flash('alert-class', 'alert-danger');
            } elseif (!isset($auth)) {
                $alert = "$lang_pass_email_ok " . q($email);
                Session::flash('message', $alert);
                Session::flash('alert-class', 'alert-success');
            }
        }
    } else {
        $res = Database::get()->querySingle("SELECT u.id, u.surname, u.givenname, u.username, u.password, u.status FROM user u
                    LEFT JOIN admin a ON (a.user_id = u.id)
                    WHERE u.email = ?s AND
                    BINARY u.username = ?s AND
                    a.user_id IS NULL AND
                    (u.last_passreminder IS NOT NULL OR DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR) < u.last_passreminder)", $email, $userName);

        if ($res) {
            Session::flash('message', $langLostPassPending);
            Session::flash('alert-class', 'alert-danger');
        } else {
            Session::flash('message', $langAccountNotFound);
            Session::flash('alert-class', 'alert-danger');
        }
    }
}

$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => $urlServer,
        'icon' => 'fa-reply',
        'level' => 'primary',
        'button-class' => 'btn-secondary')
), false);

$data['menuTypeID'] = 0;
view('modules.auth.lostpass', $data);

function password_is_editable($password) {
    global $auth_ids;

    if (in_array($password, $auth_ids)) {
        return false; // not editable, external auth method
    } else {
        return true;  // editable
    }
}
