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

$require_current_course = TRUE;
$require_editor = true;

include '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
include 'exercise.class.php';
include 'question.class.php';

$exerciseId = $_GET['exerciseId'];
$objExercise = new Exercise();
$found = $objExercise->read($exerciseId);
if (!$found) { // exercise not found
    Session::flash('message',$langExerciseNotFound);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

$toolName = $langExerciseUserGroupNoSubmission;
$pageName = $objExercise->selectTitle();
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

$ex = Database::get()->querySingle("SELECT * FROM exercise WHERE id = ?d AND course_id = ?d", $exerciseId, $course_id);
if ($ex->assign_to_specific == 1) { // specific users
    $user_ids_no_sub = Database::get()->queryArray("SELECT u.id AS user_id, u.givenname, u.surname, u.email 
                                                    FROM exercise_to_specific ets 
                                                    JOIN user u ON ets.user_id = u.id 
                                                    WHERE ets.exercise_id = ?d 
                                                    AND u.id NOT IN (SELECT `uid` FROM exercise_user_record WHERE eid = ?d)", $exerciseId, $exerciseId);
} elseif ($ex->assign_to_specific == 2) { // specific group
    $group_ids = [];
    $g_ids = Database::get()->queryArray("SELECT group_id FROM exercise_to_specific WHERE exercise_id = ?d", $exerciseId);
    foreach ($g_ids as $g) {
        $group_ids[] = $g->group_id; 
    }
    $group_ids_str = implode(',',$group_ids);
    $user_ids_no_sub = Database::get()->queryArray("SELECT u.id AS user_id, u.givenname, u.surname, u.email
                                                    FROM group_members gm
                                                    JOIN user u ON gm.user_id = u.id
                                                    WHERE gm.user_id IN (SELECT user_id FROM group_members WHERE group_id IN ($group_ids_str))
                                                    AND gm.is_tutor = ?d
                                                    AND u.id NOT IN (SELECT `uid` FROM exercise_user_record WHERE eid = ?d)", 0, $exerciseId);

} else { // all users - only students
    $user_ids_no_sub = Database::get()->queryArray("SELECT u.id AS user_id, u.givenname, u.surname, u.email
                                                    FROM course_user cu
                                                    JOIN user u ON cu.user_id = u.id
                                                    WHERE cu.course_id = ?d
                                                    AND cu.status = ?d
                                                    AND cu.tutor = ?d
                                                    AND cu.editor = ?d
                                                    AND cu.reviewer = ?d
                                                    AND cu.course_reviewer = ?d
                                                    AND u.id NOT IN (SELECT `uid` FROM exercise_user_record WHERE eid = ?d)", $course_id, USER_STUDENT, 0, 0, 0, 0, $exerciseId);
}

if (isset($_GET['notify_users'])) {
    ex_notify_users($ex->title, $ex->start_date, $ex->end_date, $user_ids_no_sub);
    $number_of_users = count($user_ids_no_sub);
    $message = $langNotifyEmailToUsers . "<strong> $number_of_users </strong>" . $langUsers;
    Session::flash('message', $message);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/exercise/index.php?course=' . $course_code);
}

if (count($user_ids_no_sub) > 0) {
    $tool_content .= "
                <div class='col-12'>
                  <div class='alert alert-warning'>
                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                        <span>$langUsersListWithNoSubmission</span>
                  </div>
                  <ul class='list-group list-group-flush mt-3'>
                    <li class='list-group-item list-group-item-action'>$langUsers</li>";
                    foreach ($user_ids_no_sub as $u) {
                        $tool_content .= "<li class='list-group-item element d-flex justify-content-between align-items-center'>" . display_user($u->user_id) . "<p>$u->email</p></li>";
                    }
$tool_content .= "</ul>
                  <div class='mt-4'><small class='helpblock Accent-200-cl'>(*)$langNotifyUsersViaEmail</small></div>
                  <a class='btn submitAdminBtn d-inline-flex mt-2' href='{$urlAppend}modules/exercise/users_no_submission.php?course=$course_code&amp;exerciseId=$exerciseId&amp;notify_users=true'>$langSendReminder</a>
                </div>";
} else {
    $tool_content .= "<div class='col-12'>
                        <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            <span>$langNoUsersForExerSubmission</span>
                        </div>
                      </div>";
}

draw($tool_content, 2, null, $head_content);


/**
 * @brief Send an e-mail notification for new messages to subscribed users
 * @param type $ex_title
 * @param type $ex_start
 * @param type $ex_end
 * @param type $users_info
 * @param array $users_info
 */
function ex_notify_users($ex_title, $ex_start, $ex_end, $users_info = array()) {
    global $course_code, $course_id, $siteName, $langStartDate, $langEndDate, 
            $langNotifyUnSubmittedUsers, $langProblem, $langManager, $langTel, 
            $langEmail, $langTitle, $langLink;

    $course_title = course_code_to_title($course_code);
    $link_platform = get_config('base_url');
    $emailHeader = "
    <!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <strong>$course_title</strong><br><br>
                    <div id='header-title'><p>$langNotifyUnSubmittedUsers</p></div>
                </div>
            </div>";

    $emailMain = "
    <!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div id='mail-body-inner'>
                <ul>
                    <li>
                      <span><b>$langTitle: </b>$ex_title</span> 
                    </li>
                    <li>
                      <span><b>$langStartDate: </b>$ex_start</span> 
                    </li>
                    <li>
                      <span><b>$langEndDate: </b>$ex_end</span>
                    </li>
                    <li>
                        <span><b>$langLink: </b><a href='$link_platform'>$link_platform</a></span>
                    </li>
                </ul>
            </div>
            <div>
                <br>
                <p>$langProblem</p><br>" . get_config('admin_name') . "
                <ul id='forum-category'>
                    <li>$langManager: $siteName</li>
                    <li>$langTel: -</li>
                    <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                </ul>
            </div>
        </div>";

    $emailsubject = $siteName;

    $emailbody = $emailHeader.$emailMain;

    $emailPlainBody = html2text($emailbody);

    foreach($users_info as $u){
        if(get_user_email_notification($u->user_id, $course_id)){
            send_mail_multipart('', '', '', $u->email, $emailsubject, $emailPlainBody, $emailbody);
        }
    }
    
}