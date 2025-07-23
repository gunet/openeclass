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

$require_current_course = true;
$require_help = true;
$helpTopic = 'course_users';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

if (get_config('course_guest') == 'off') {
    redirect_to_home_page('modules/user/index.php?course=' . $course_code);
}

$up = new Permissions();

if (!$up->has_course_users_permission()) {
    Session::Messages($langCheckCourseAdmin, 'alert-danger');
    redirect_to_home_page('courses/'. $course_code);
}

$toolName = $langUsers;
$pageName = $langAddGuest;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsers);

load_js('pwstrength.js');
load_js('bootstrap-datetimepicker');

$default_guest_username = $langGuestUserName . $course_code;

$data['guest_info_message'] = '';

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $password = $_POST['guestpassword'];
    createguest($default_guest_username, $course_id, $password);
    Session::flash('message',$langGuestSuccess);
    Session::flash('alert-class', 'alert-success');
    if ($password === '') {
        Session::flash('message',$langGuestWarnEmptyPassword);
        Session::flash('alert-class', 'alert-warning');
    }
    redirect_to_home_page("modules/user/index.php?course=$course_code");
} else {
    $guest_info = guestinfo($course_id);
    if ($guest_info) {
        if ($guest_info->password === '') {
            $data['guest_info_message'] = "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langGuestWarnEmptyPassword</span></div></div>";
        } else {
            $data['guest_info_message'] = "<div class='col-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langGuestExist</span></div></div>";
        }
        $data['expirationDate'] = $expirationDate = DateTime::createFromFormat("Y-m-d H:i:s", $guest_info->expires_at);
        $submit_label = $langModify;
    } else {
        $guest_info = new stdClass();
        $guest_info->givenname = $langGuestName;
        $guest_info->surname = $langGuestSurname;
        $guest_info->username = $default_guest_username;
        $submit_label = $langAdd;
        $data['expirationDate'] = $expirationDate = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now") + get_config('account_duration')));
    }
}

$data['guest_info'] = $guest_info;
$data['submit_label'] = $submit_label;

view('modules.user.guestuser', $data);


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
