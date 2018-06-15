<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
 * Password reset component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @abstract This component resets the user's password after verifying
 *     their information through a challenge/response system.
 *
 */
use Hautelook\Phpass\PasswordHash;

include '../../include/baseTheme.php';
include 'auth.inc.php';
include 'include/sendMail.inc.php';
$pageName = $lang_remind_pass;


// Password reset link is valid for 1 hour = 3600 sec
define('TOKEN_VALID_TIME', 3600);

$data['emailhelpdesk'] = q(get_config('email_helpdesk'));

function password_is_editable($password) {
    global $auth_ids;

    if (in_array($password, $auth_ids)) {
        return false; // not editable, external auth method
    } else {
        return true;  // editable
    }
}

if (isset($_REQUEST['u']) and isset($_REQUEST['h'])) {
    $data['change_ok'] = false;
    $data['userUID'] = intval($_REQUEST['u']);
    $valid = token_validate('password' . $data['userUID'], $_REQUEST['h'], TOKEN_VALID_TIME);
    $res = Database::get()->querySingle("SELECT id FROM user WHERE id = ?d AND password NOT IN ('" . implode("', '", $auth_ids) . "')", $data['userUID']);
    $error_messages = array();
    if ($valid and $res) {
        $data['is_valid'] = true;
        if (isset($_POST['newpass']) and isset($_POST['newpass1']) and
                count($error_messages = acceptable_password($_POST['newpass'], $_POST['newpass1'])) == 0) {

            $data['action_bar'] = action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);

            $hasher = new PasswordHash(8, false);
            $q1 = Database::get()->query("UPDATE user SET password = ?s
                                                      WHERE id = ?d",
                    $hasher->HashPassword($_POST['newpass']), $data['userUID']);
            if ($q1->affectedRows > 0) {
                $data['user_pass_updated'] = true;

                $data['change_ok'] = true;
            }
        } elseif (count($error_messages)) {
            $data['user_pass_notupdate'] = true;
        }
    } else {
        $data['action_bar'] = action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
    }
} elseif (isset($_POST['send_link'])) {

    $data['email'] = isset($_POST['email']) ? mb_strtolower(trim($_POST['email'])) : '';
    $data['userName'] = isset($_POST['userName']) ? canonicalize_whitespace($_POST['userName']) : '';
    /*     * *** If valid e-mail address was entered, find user and send email **** */
    $data['res_first_attempt'] = Database::get()->querySingle("SELECT u.id, u.surname, u.givenname, u.username, u.password, u.status FROM user u
                    LEFT JOIN admin a ON (a.user_id = u.id)
                    WHERE u.email = ?s AND
                    BINARY u.username = ?s AND
                    a.user_id IS NULL AND
                    (u.last_passreminder IS NULL OR DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR) >= u.last_passreminder)", $data['email'], $data['userName']); //exclude admins and currently pending requests

    $data['found_editable_password'] = false;
    if ($data['res_first_attempt']) {
        if (password_is_editable($data['res_first_attempt']->password)) {
            $id = $data['res_first_attempt']->id;
            $token = token_generate('password' . $id, true);
            $resetUrl = $urlServer . "modules/auth/lostpass.php?u=$id&amp;h=$token";
            $data['found_editable_password'] = true;

            // prepare instruction for password reset
            $header_html_topic_notify = "<!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <div id='header-title'>$langPassResetIntro</div>
                    <div>$langPassResetIntro2 $data[emailhelpdesk]</div>
                </div>
            </div>";

            $body_html_topic_notify = "<!-- Body Section -->
            <div id='mail-body'>
                <br>
                <div><b>$langHowToResetTitle</b></div><br>
                <div id='mail-body-inner'>
                    $langPassResetGoHere<br><br><a href='$resetUrl'>$resetUrl</a>
                </div>
            </div>";

            $text = $header_html_topic_notify . $body_html_topic_notify;

            $plainText = html2text($text);
            // store the timestamp of this action (password reminding and token generation)
            Database::get()->query("UPDATE user SET last_passreminder = CURRENT_TIMESTAMP WHERE id = ?d" , $id);
        } else { //other type of auth...
            $data['auth'] = array_search($data['res_first_attempt']->password, $auth_ids) or 1;
        }

        /*         * *** Account details found, now send e-mail **** */
        if ($data['found_editable_password']) {
            $emailsubject = $lang_remind_pass;
            $data['mail_sent'] = send_mail_multipart('', '', '', $data['email'], $emailsubject, $plainText, $text);
        }
    } else {
        $data['res_second_attempt'] = Database::get()->querySingle("SELECT u.id, u.surname, u.givenname, u.username, u.password, u.status FROM user u
                    LEFT JOIN admin a ON (a.user_id = u.id)
                    WHERE u.email = ?s AND
                    BINARY u.username = ?s AND
                    a.user_id IS NULL AND
                    (u.last_passreminder IS NOT NULL OR DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR) < u.last_passreminder)", $data['email'], $data['userName']);
    }
}

$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
          'url' => $urlServer,
          'icon' => 'fa-reply',
          'level' => 'primary-label',
          'button-class' => 'btn-default')), false);

$data['menuTypeID'] = 0;
$data['error_messages'] = $error_messages;
view('modules.auth.lostpass', $data);
