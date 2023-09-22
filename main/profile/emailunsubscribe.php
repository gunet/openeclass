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
$require_help = true;
$helpTopic = 'portfolio';
$helpSubTopic = 'profile_change';
require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

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
        $course_title = course_id_to_title($cid);
        if (isset($_POST['c_unsub'])) {
            Database::get()->query("UPDATE course_user SET receive_mail = ". EMAIL_NOTIFICATIONS_ENABLED . "
                                WHERE user_id = ?d AND course_id = ?d", $uid, $cid);
            Log::record(0, 0, LOG_PROFILE, array(
                        'uid' => intval($_SESSION['uid']),
                        'email_notifications' => 1,
                        'course_title' => $course_title
                        ));
        } else {
            Database::get()->query("UPDATE course_user SET receive_mail = " . EMAIL_NOTIFICATIONS_DISABLED . "
                                WHERE user_id = ?d AND course_id = ?d", $uid, $cid);
            Log::record(0, 0, LOG_PROFILE, array(
                'uid' => intval($_SESSION['uid']),
                'email_notifications' => 0,
                'course_title' => $course_title
            ));
        }
        $message = q(sprintf($course_title, $langEmailUnsubSuccess));
        Session::Messages($message, "alert-success");
    } else { // change email subscription for all courses
        foreach ($_SESSION['courses'] as $course_code => $c_value) {
            $cid = course_code_to_id($course_code);
            $course_title = course_id_to_title($cid);
            if (isset($_POST['c_unsub']) and array_key_exists($course_code, $_POST['c_unsub'])) {
                $receive_mail = EMAIL_NOTIFICATIONS_ENABLED;
            } else {
                $receive_mail = EMAIL_NOTIFICATIONS_DISABLED;
            }
            Database::get()->query("UPDATE course_user SET receive_mail = ?d
                WHERE user_id = ?d AND course_id = ?d", $receive_mail, $uid, $cid);
            Log::record(0, 0, LOG_PROFILE, array(
                'uid' => intval($_SESSION['uid']),
                'email_notifications' => $receive_mail,
                'course_title' => $course_title
            ));
        }
        Session::Messages($langWikiEditionSucceed, "alert-success");
    }
    redirect_to_home_page("main/profile/display_profile.php");
} else {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => 'display_profile.php',
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>";
    if (get_config('email_verification_required') or get_config('dont_mail_unverified_mails')) {
        $user_email_status = get_mail_ver_status($uid);
        if ($user_email_status == EMAIL_VERIFICATION_REQUIRED or
                $user_email_status == EMAIL_UNVERIFIED) {
            $link = "<a href = '{$urlAppend}modules/auth/mail_verify_change.php?from_profile=true'>$langHere</a>.";
            $tool_content .= "<div class='alert alert-warning'>$langMailNotVerified $link</div>";
        }
    }
    if (!get_user_email_notification_from_courses($uid)) {
        $head_content .= '<script type="text/javascript">$(control_deactivate);</script>';
        $tool_content .= "<div class='alert alert-info'>$langEmailUnsubscribeWarning</div>
                                  <input type='checkbox' id='unsub' name='unsub' value='1'>&nbsp;$langEmailFromCourses";
    }
    $tool_content .= "<div class='alert alert-info'>$langInfoUnsubscribe</div>
                          <div id='unsubscontrols'>";
    if (isset($_REQUEST['cid'])) { // one course only
        $cid = intval($_REQUEST['cid']);
        $course_title = course_id_to_title($cid);
        $selected = get_user_email_notification($uid, $cid) ? 'checked' : '';
        $tool_content .= "<input type='checkbox' name='c_unsub' value='1' $selected>&nbsp;" . q($course_title) . "<br />";
        $tool_content .= "<input type='hidden' name='cid' value='" . getIndirectReference($cid) . "'>";
    } else { // displays all courses
        foreach ($_SESSION['courses'] as $code => $status) {
            $title = course_code_to_title($code);
            $cid = course_code_to_id($code);
            $selected = get_user_email_notification($uid, $cid) ? 'checked' : '';
            if (course_status($cid) != COURSE_INACTIVE) {
                $tool_content .= "<div class='checkbox'><label><input type='checkbox' name='c_unsub[$code]' value='1' $selected>&nbsp;" . q($title) . "</label></div>";
            }
        }
    }
    $tool_content .= "</div>
                    <br>
                        <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
                        <a class='btn btn-default' href='display_profile.php'>$langCancel<a>";
    $tool_content .= generate_csrf_token_form_field() ."</form>";
}

draw($tool_content, 1, null, $head_content);
