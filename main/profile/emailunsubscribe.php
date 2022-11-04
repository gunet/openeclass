<?php

/* ========================================================================
 * Open eClass 3.12
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
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

$require_login = true;
$require_valid_uid = true;
include '../../include/baseTheme.php';
load_js('tools.js');

$toolName = $langMyProfile;
$pageName = "$langNotifyActions $langsOfCourses";
$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);

check_uid();

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_POST['unsub'])) {
        Database::get()->query("UPDATE user SET receive_mail = " . EMAIL_NOTIFICATIONS_ENABLED . " WHERE id = ?d", $uid);
    }
    if (isset($_POST['cid'])) {  // change email subscription for one course
        $cid = getDirectReference($_POST['cid']);
        if (isset($_POST['c_unsub'])) {
            Database::get()->query("UPDATE course_user SET receive_mail = ". EMAIL_NOTIFICATIONS_ENABLED . "
                                WHERE user_id = ?d AND course_id = ?d", $uid, $cid);
        } else {
            Database::get()->query("UPDATE course_user SET receive_mail = " .EMAIL_NOTIFICATIONS_DISABLED . "
                                WHERE user_id = ?d AND course_id = ?d", $uid, $cid);
        }
        $course_title = course_id_to_title($cid);
        $message = q(sprintf($course_title, $langEmailUnsubSuccess));
        //Session::Messages($message, "alert-success");
        Session::flash('message',$message);
        Session::flash('alert-class', 'alert-success');
    } else { // change email subscription for all courses
        foreach ($_SESSION['courses'] as $course_code => $c_value) {
            $cid = course_code_to_id($course_code);
            if (isset($_POST['c_unsub']) and array_key_exists($course_code, $_POST['c_unsub'])) {
                $receive_mail = EMAIL_NOTIFICATIONS_ENABLED;
            } else {
                $receive_mail = EMAIL_NOTIFICATIONS_DISABLED;
            }
            Database::get()->query("UPDATE course_user SET receive_mail = ?d
                WHERE user_id = ?d AND course_id = ?d", $receive_mail, $uid, $cid);
        }
        //Session::Messages($langWikiEditionSucceed, "alert-success");
        Session::flash('message',$langWikiEditionSucceed);
        Session::flash('alert-class', 'alert-success');
    }
    redirect_to_home_page("main/profile/display_profile.php");
} else {
    $data['uid'] = $uid;
    $data['action_bar'] = action_bar(
        [
            [
                'title' => $langBack,
                'url' => 'display_profile.php',
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            ]
        ]);

    if (get_config('email_verification_required') or get_config('dont_mail_unverified_mails')) {
        $user_email_status = get_mail_ver_status($uid);
        if ($user_email_status == EMAIL_VERIFICATION_REQUIRED or
                $user_email_status == EMAIL_UNVERIFIED) {
            $data['mailNotVerified'] = true;
        }
    }

    if (!get_user_email_notification_from_courses($uid)) {
        $data['mail_notification'] = true;
    }
}

$data['menuTypeID'] = 1;
view('main.profile.subscribe', $data);
