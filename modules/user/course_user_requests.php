<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

$require_current_course = true;
$require_course_admin = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

$toolName = $langUserRequests;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsers);

$tool_content .= action_bar(array(
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

if (isset($_POST['rejected_req_id'])) { // do reject course user request
    $from_name = uid_to_name($uid, 'fullname');        
    $to_address = uid_to_email($_POST['rejected_uid']);
    $subject = "$langCourse: " .  course_id_to_title($course_id) . " - $langCourseUserRequestReject";
    $mailHeader = "<div id='mail-header'><div><br>
                    <div id='header-title'>$langTeacher: $from_name</div>
		</div></div>";    
    $mailMain = "<div id='mail-body'><br><div><b>$langReasonReject:</b></div>
		<div id='mail-body-inner'>$_POST[rej_content]</div></div>";
        
    $message = $mailHeader.$mailMain;    
    $plainMessage = html2text($message);    
    if (!send_mail_multipart('', '', '', $to_address, $subject, $plainMessage, $message, $GLOBALS['charset'])) {
        $tool_content .= "<div class='alert alert-warning'>$GLOBALS[langErrorSendingMessage]</div>";
    }
    Database::get()->query("UPDATE course_user_request SET status = 0 WHERE id = ?d", $_POST['rejected_req_id']);        
    $tool_content .= "<div class='alert alert-success'>$langRequestReject</div>";
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
                if (!empty($email) and email_seems_valid($email)) {
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
                    send_mail_multipart('', '', '', $email, $emailsubject, $plainemailbody, $emailbody, $charset);
                }
            }
            // close user request
            Database::get()->query("UPDATE course_user_request SET status = 2 WHERE id = ?d", $_GET['rid']);
            Session::Messages($langCourseUserRegDone, 'alert-success');
            redirect_to_home_page('modules/user/course_user_requests.php?course=' . $course_code);
        } else {
            $tool_content .= "<div class='alert alert-danger'>$langCourseUserRegError</div>";
        }
    } else {        
        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' method='post' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
	<fieldset>
        <div class='col-sm-12'><label>$langReasonReject</label></div>
        <div class='col-sm-12'><label>$langFrom:&nbsp;</label><small>" . uid_to_name($uid, 'fullname') . "</small></div>
        <div class='col-sm-12'><label>$langSendTo:&nbsp;</label><small>" . uid_to_name($_GET['u'], 'fullname') . "</small></div>
        <div class='form-group'>
            <div class='col-sm-12'>
              <textarea name='rej_content' rows='8' cols='80'></textarea>
            </div>
	</div>
        <div class='form-group'>
            <div class='col-sm-offset-1 col-sm-11'>
                <input class='btn btn-primary' type='submit' name='submit' value='" . q($langRejectRequest) . "'>
            </div>
        </div>		
        ". generate_csrf_token_form_field() ."
        <input type='hidden' name='rejected_req_id' value='$_GET[rid]'>
            <input type='hidden' name='rejected_uid' value='$_GET[u]'>
	</fieldset></form></div>";                
    }
} else { // display course user requests
    $sql = Database::get()->queryArray("SELECT id, uid, course_id, comments, ts FROM course_user_request WHERE course_id = ?d AND status = 1", $course_id);
    if ($sql) {  
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr>";
        $tool_content .= "<th width='320'>$langSurnameName</th><th>$langComments</th><th width='110'>$langDateRequest</th><th width='80' class='text-center'>".icon('fa-gears')."</th>";
        $tool_content .= "</tr>";
        foreach ($sql as $udata) {
            $am_message = '';
            $user_am = uid_to_am($udata->uid);
            if ($user_am) {
                $am_message = "$langAm: $user_am";            
            }
            
            $tool_content .= "<tr>";
            $tool_content .= "<td>" . display_user($udata->uid, false)."<br>&nbsp;&nbsp;<small>$am_message</small></td>";
            $tool_content .= "<td>" . q($udata->comments) . "</td>";
            $tool_content .= "<td>" . nice_format(date('Y-m-d', strtotime($udata->ts))) . "</td>";
            $tool_content .= "<td>".
                            action_button(array(
                                array('title' => $langRegistration, 
                                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$udata->uid&amp;rid=$udata->id&amp;reg=true",
                                  'icon' => 'fa-plus',
                                  'level' => 'primary'),
                                array('title' => $langRejectRequest, 
                                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$udata->uid&amp;rid=$udata->id&amp;rej=true",
                                  'icon' => 'fa-times',
                                  'level' => 'primary')                                 
                                 )).
                        "</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langUserNoRequests</div>";
    }
}

draw($tool_content, 2);
