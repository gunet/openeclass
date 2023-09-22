<?php

/* ========================================================================
 * Open eClass 3.15
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Guest';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

if (get_config('course_guest') == 'off') {
    redirect_to_home_page('modules/user/?course=' . $course_code);
}

$toolName = $langUsers;
$pageName = $langAddGuest;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsers);

load_js('pwstrength.js');
load_js('bootstrap-datetimepicker');
$head_content .= "<script type='text/javascript'>
    var lang = {
                'pwStrengthTooShort': '" . js_escape($langPwStrengthTooShort) . "',
                'pwStrengthWeak': '" . js_escape($langPwStrengthWeak) . "',
                'pwStrengthGood': '" . js_escape($langPwStrengthGood) . "',
                'pwStrengthStrong': '" . js_escape($langPwStrengthStrong) . "'
        };

    $(document).ready(function() {
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });
    });
    $(function() {
        $('#user_date_expires_at').datetimepicker({
            format: 'dd-mm-yyyy hh:ii',
            pickerPosition: 'bottom-right',
            language: '".$language."',
            minuteStep: 10,
            autoclose: true
        });
    });
</script>";

$default_guest_username = $langGuestUserName . $course_code;

$tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "index.php?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'
                 )));

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $password = $_POST['guestpassword'];
    createguest($default_guest_username, $course_id, $password);
    Session::Messages($langGuestSuccess, 'alert-success');
    if ($password === '') {
        Session::Messages($langGuestWarnEmptyPassword, 'alert-warning');
    }
    redirect_to_home_page("modules/user/?course=$course_code");
} else {
    $guest_info = guestinfo($course_id);
    if ($guest_info) {
        if ($guest_info->password === '') {
            $tool_content .= "<div class='alert alert-warning'>$langGuestWarnEmptyPassword</div>";
        } else {
            $tool_content .= "<div class='alert alert-info'>$langGuestExist</div>";
        }
        $expirationDate = DateTime::createFromFormat("Y-m-d H:i:s", $guest_info->expires_at);
        $submit_label = $langModify;
    } else {
        $guest_info = new stdClass();
        $guest_info->givenname = $langGuestName;
        $guest_info->surname = $langGuestSurname;
        $guest_info->username = $default_guest_username;
        $submit_label = $langAdd;
        $expirationDate = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now") + get_config('account_duration')));
    }
    $tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <fieldset>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>$langName:</label>
            <div class='col-sm-10'>
                <input class='form-control' value='".q($guest_info->givenname)."' disabled>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>$langSurname:</label>
            <div class='col-sm-10'>
                <input class='form-control' value='".q($guest_info->surname)."' disabled>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>$langUsername:</label>
            <div class='col-sm-10'>
                <input class='form-control' value='".q($guest_info->username)."' disabled>
            </div>
        </div>
        <div class='form-group'>
            <label for='password' class='col-sm-2 control-label'>$langPass:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='guestpassword' value='' id='password' autocomplete='off' placeholder='$langAskGuest'>
                <span id='result'></span>
            </div>
        </div>
        <div class='input-append date form-group'>
            <label class='col-sm-2 control-label'>$langExpirationDate:</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <input class='form-control' id='user_date_expires_at' name='user_date_expires_at' type='text' value='" . $expirationDate->format("d-m-Y H:i") . "'>
                    <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
                </div>
            </div>
        </div>
        <div class='col-sm-offset-2 col-sm-10'>
          <input class='btn btn-primary' type='submit' name='submit' value='$submit_label'>
          <a href='index.php?course=$course_code' class='btn btn-default'>$langCancel</a>
        </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."
        </form>
        </div>";
}
draw($tool_content, 2, null, $head_content);

/**
 * @brief create guest account or update password if it already exists
 * @return void
 */
function createguest($username, $course_id, $password): void
{
    global $langGuestName, $langGuestSurname;

    if ($password !== '') {
        $password = password_hash($password, PASSWORD_DEFAULT);
    }

    $q = Database::get()->querySingle("SELECT user_id from course_user WHERE status= " . USER_GUEST . " AND course_id = $course_id");
    if ($q) {
        $guest_id = $q->user_id;
        $expires_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['user_date_expires_at']);
        $user_expires_at = $expires_at->format("Y-m-d H:i");
        Database::get()->query("UPDATE user SET password = ?s, expires_at = ?s WHERE id = ?d", $password, $user_expires_at, $guest_id);
    } else {
        if (isset($_POST['user_date_expires_at'])) {
            $expires_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['user_date_expires_at']);
            $user_expires_at = $expires_at->format("Y-m-d H:i");
        } else {
            $expires_at = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now + 1 year")));
            $user_expires_at = $expires_at->format("Y-m-d H:i");
        }
        $q = Database::get()->query("INSERT INTO user (surname, givenname, username, password, status, registered_at, expires_at, whitelist, description, verified_mail, receive_mail)
                                            VALUES (?s, ?s, ?s, ?s, " . USER_GUEST . ",
                                                " . DBHelper::timeAfter() . ", ?s,
                                                '','', " . EMAIL_UNVERIFIED . ", " . EMAIL_NOTIFICATIONS_DISABLED . ")",
                                            $langGuestSurname, $langGuestName, $username, $password, $user_expires_at);
        $guest_id = $q->lastInsertID;
        // update personal calendar info table
        // we don't check if trigger exists since it requires `super` privilege
        Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $guest_id);
    }
    Database::get()->query("INSERT IGNORE INTO course_user (course_id, user_id, status, receive_mail, reg_date)
                                VALUES (?d, ?d, " . USER_GUEST . ", " . EMAIL_NOTIFICATIONS_DISABLED . " , " . DBHelper::timeAfter().")", $course_id, $guest_id);
    Log::record($course_id, MODULE_ID_USERS, LOG_INSERT, array('uid' => $guest_id, 'right' => '+10'));
}

/**
 * @brief check if guest account exists and return account information
 * @param type $course_id
 * @return boolean
 */
function guestinfo($course_id) {

    $q = Database::get()->querySingle("SELECT surname, givenname, username, password, expires_at FROM user, course_user
                       WHERE user.id = course_user.user_id AND
                             course_user.status = " . USER_GUEST . " AND
                             course_user.course_id = ?d", $course_id);
    if (!$q) {
        return false;
    } else {
        return $q;
    }
}
