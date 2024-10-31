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
$require_course_admin = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

$toolName = $langUserRequests;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsers);

$action_bar = action_bar(array(
        array('title' => "$langUsers",
            'url' => "index.php?course=$course_code",
            'icon' => 'fa-users',
            'level' => 'primary-label'),
        array('title' => "$langBackRequests",
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary-label',
            'show' => isset($_GET['rid']))
        ));
$tool_content .= $action_bar;

if (isset($_POST['rejected_req_id'])) { // do reject course user request
    $from_name = q(uid_to_name($uid, 'fullname'));
    $from_address = uid_to_email($uid);
    $to_address = uid_to_email($_POST['rejected_uid']);
    $subject = "$langCourse: " .  course_id_to_title($course_id) . " - $langCourseUserRequestReject";
    $mailHeader = "<div id='mail-header'><div><br>
                    <div id='header-title'>$langTeacher: $from_name</div>
        </div></div>";
    $mailMain = "<div id='mail-body'><br><div><b>$langReasonReject:</b></div>
        <div id='mail-body-inner'>$_POST[rej_content]</div></div>";

    $message = $mailHeader.$mailMain;
    $plainMessage = html2text($message);
    if (!send_mail_multipart($from_name, $from_address, '', $to_address, $subject, $plainMessage, $message)) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$GLOBALS[langErrorSendingMessage]</span></div></div>";
    }
    Database::get()->query("UPDATE course_user_request SET status = 0 WHERE id = ?d", $_POST['rejected_req_id']);
    $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langRequestReject</span></div></div>";
}


if (isset($_GET['rid'])) {
    if (isset($_GET['reg'])) {
        $sql = Database::get()->query("INSERT IGNORE INTO course_user
                    SET user_id = ?d, course_id = ?d,
                        status = " . USER_STUDENT . ",
                        reg_date = " . DBHelper::timeAfter() . ",
                        document_timestamp = " . DBHelper::timeAfter(),
                    $_GET['u'], $course_id);
        if ($sql) {
            if ($sql->affectedRows) { // notify user if registered
                $email = uid_to_email($_GET['u']);
                if (!empty($email) and valid_email($email)) {
                    $emailsubject = "$langYourReg " . course_id_to_title($course_id);
                    $emailbody = "$langNotifyRegUser1 '" . course_id_to_title($course_id) .
                        "' $langNotifyRegUser2 $langFormula \n$gunet";
                    $header_html_topic_notify = "<!-- Header Section -->
                        <div id='mail-header'><br><div><div id='header-title'>$langYourReg " .
                        course_id_to_title($course_id) . "</div></div></div>";
                    $body_html_topic_notify = "<!-- Body Section -->
                        <div id='mail-body'><br>
                        <div id='mail-body-inner'>
                        $langNotifyRegUser1 '" . course_id_to_title($course_id) . "' $langNotifyRegUser2
                        <br><br>$langFormula<br>$gunet
                        </div>
                        </div>";

                    $emailbody = $header_html_topic_notify . $body_html_topic_notify;
                    $plainemailbody = html2text($emailbody);
                    send_mail_multipart('', '', '', $email, $emailsubject, $plainemailbody, $emailbody);
                }
            }
            // close user request
            Database::get()->query("UPDATE course_user_request SET status = 2 WHERE id = ?d", $_GET['rid']);
            Session::flash('message',$langCourseUserRegDone);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page('modules/user/course_user_requests.php?course=' . $course_code);
        } else {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langCourseUserRegError</span></div></div>";
        }
    } else {
        $tool_content .= "<div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>";
        $tool_content .= "<form class='form-horizontal' method='post' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
    <fieldset>
        <legend class='mb-0' aria-label='$langForm'></legend>
        <div class='col-sm-12'><div class='control-label-notes'>$langReasonReject</div></div>
        <div class='col-sm-12'><div class='control-label-notes'>$langFrom:&nbsp;</div><small>" . q(uid_to_name($uid, 'fullname')) . "</small></div>
        <div class='col-sm-12'><div class='control-label-notes'>$langSendTo:&nbsp;</div><small>" . q(uid_to_name($_GET['u'], 'fullname')) . "</small></div>
        <div class='form-group mt-3'>
            <div class='col-sm-12'>
              <textarea aria-label='$langTypeOutMessage' name='rej_content' rows='8' cols='80'></textarea>
            </div>
    </div>
        <div class='form-group mt-3'>
            <div class='col-sm-offset-1 col-sm-11'>
                <input class='btn submitAdminBtn' type='submit' name='submit' value='" . q($langRejectRequest) . "'>
            </div>
        </div>
        ". generate_csrf_token_form_field() ."
        <input type='hidden' name='rejected_req_id' value='$_GET[rid]'>
            <input type='hidden' name='rejected_uid' value='$_GET[u]'>
    </fieldset></form></div></div><div class='d-none d-lg-block'>
    <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
</div>
</div>";
    }
} else { // display course user requests
    $sql = Database::get()->queryArray("SELECT id, uid, course_id, comments, ts FROM course_user_request WHERE course_id = ?d AND status = 1", $course_id);
    if ($sql) {
        $tool_content .= "<div class='col-sm-12'><div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'>";
        $tool_content .= "<th width='320'>$langSurnameName</th><th>$langComments</th><th width='170'>$langDateRequest</th><th width='80' aria-label='$langSettingSelect'>".icon('fa-gears')."</th>";
        $tool_content .= "</tr></thead>";
        foreach ($sql as $udata) {
            $am_message = '';
            $user_am = uid_to_am($udata->uid);
            if ($user_am) {
                $am_message = "$langAm: $user_am";
            }

            $tool_content .= "<tr>";
            $tool_content .= "<td>" . display_user($udata->uid, false)."<br>&nbsp;&nbsp;<small>$am_message</small></td>";
            $tool_content .= "<td>" . q($udata->comments) . "</td>";
            $tool_content .= "<td>" . format_locale_date(strtotime($udata->ts)) . "</td>";
            $tool_content .= "<td class='text-end'>".
                            action_button(array(
                                array('title' => $langRegistration,
                                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$udata->uid&amp;rid=$udata->id&amp;reg=true",
                                  'icon' => 'fa-plus',
                                  'level' => 'primary'),
                                array('title' => $langRejectRequest,
                                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$udata->uid&amp;rid=$udata->id&amp;rej=true",
                                  'icon' => 'fa-solid fa-xmark Accent-200-cl',
                                  'level' => 'primary')
                                 )).
                        "</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div></div>";
    } else {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langUserNoRequests</span></div></div>";
    }
}

draw($tool_content, 2);
