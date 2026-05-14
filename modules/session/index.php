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


/**
 * @file index.php
 * @brief Sessions display module
 */

$require_login = true;
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'course_sessions';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/action.php';
require_once 'modules/progress/process_functions.php';
require_once 'functions.php';

check_activation_of_collaboration();

is_session_type_course();

$action = new action();
$action->record(MODULE_ID_SESSION);

load_js('tools.js');
load_js('datatables');

$pageName = $langSession;
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));

if (isset($_POST['action']) and $_POST['action'] == 'expired_session') {
    if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
        $finish = Database::get()->querySingle("SELECT finish FROM mod_session WHERE id = ?d", $_POST['id'])->finish;
        if ($current_time > $finish) {
            echo 1;
        } else {
            echo 0;
        }
        exit();
    }
}

if(isset($_POST['user_registration'])){
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    Database::get()->query("UPDATE mod_session_users SET
                            is_accepted = ?d
                            WHERE session_id = ?d
                            AND participants = ?d",1,$_POST['about_session'],$uid);

    // Update tc participants for specific user
    update_tc_participants($_POST['about_session']);

    if(participation_in_session($_POST['about_session'])){

        $message = "$langUserHasAcceptedSession" . "&nbsp;" . participant_name($uid);
        $session_title = title_session($course_id,$_POST['about_session']);
        $course_title = course_id_to_title($course_id);
        $consultant_id = get_session_consultant($_POST['about_session'],$course_id);
        $emailConsultant = uid_to_email($consultant_id);
        $emailHeader = "
        <!-- Header Section -->
                <div id='mail-header'>
                    <br>
                    <div>
                        <div id='header-title'>$session_title&nbsp;&nbsp;<span>($course_title)</span></div>
                    </div>
                </div>";

        $emailMain = "
        <!-- Body Section -->
            <div id='mail-body'>
                <br>
                <div id='mail-body-inner'>
                   <p>$message</p>
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

        if(get_user_email_notification($consultant_id)){
            send_mail_multipart('', '', '', $emailConsultant, $emailsubject, $emailPlainBody, $emailbody);
        }

        Session::flash('message',$langCompleteRegistration);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/session/session_space.php?course=$course_code&session=".$_POST['about_session']);
    }else{
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/session/index.php?course=$course_code");
    }
}

// Show remote or not sessions
$remoteType = -1;
$sql_remote = '';
if(isset($_POST['remoteType']) and $_POST['remoteType'] == 1){
    $sql_remote = "AND type_remote = 1";
    $remoteType = 1;
}elseif(isset($_POST['remoteType']) and $_POST['remoteType'] == 0){
    $sql_remote = "AND type_remote = 0";
    $remoteType = 0;
}
$data['remoteType'] = $remoteType;

$sessionType = '';
$sql_session = '';
if(isset($_POST['sessionType']) and $_POST['sessionType'] == 'one'){
    $sql_session = "AND type = 'one'";
    $sessionType = 'one';
}elseif(isset($_POST['sessionType']) and $_POST['sessionType'] == 'group'){
    $sql_session = "AND type = 'group'";
    $sessionType = 'group';
}
$data['sessionType'] = $sessionType;

$for_consultant = 0;
$sql_consultant = '';
$sql_consultant_args = [];
$data['all_consultants'] = [];

// Delete session from consultant or course tutor
if(isset($_POST['delete_session'])){
    delete_session($_POST['session_id']);
    Session::flash('message',$langDelSessionSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/index.php?course=".$course_code);
}
// Delete all sessions from course tutor
if(isset($_GET['delete_all_sessions'])){
    if (!$is_coordinator) {
        Session::flash('message', $langForbidden);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/session/index.php?course=$course_code");
    }

    $session_ids = Database::get()->queryArray("SELECT id FROM mod_session WHERE course_id = ?d", $course_id);
    foreach($session_ids as $s){
        delete_session($s->id);
    }
    Session::flash('message',$langDelAllSessionSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/index.php?course=".$course_code);
}
// Leave session from simple
if(isset($_POST['leave_session'])){
    Database::get()->query("DELETE FROM mod_session_users WHERE session_id = ?d AND participants = ?d AND is_accepted = ?d",$_POST['session_leave_id'],$uid,1);
    Session::flash('message',$langLeaveSessionSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/index.php?course=".$course_code);
}

// Users mode (coordinator, consultant, simple user)
if ($is_coordinator) {
    require_once 'modules/session/index_coordinator.php';
} elseif ($is_consultant && !$is_coordinator) {
    require_once 'modules/session/index_consultant.php';
} else {
    require_once 'modules/session/index_user.php';
}

// About the next session or session in progress.
$limit = "LIMIT 1";
$sql_session = "";
$data['next_session'] = array();
$data['current_sessions'] = array();

if($is_consultant && !$is_coordinator){
    $sql_session = "AND creator = ?d";
}elseif($is_simple_user){
    $sql_session = "AND id IN (SELECT session_id FROM mod_session_users 
                                WHERE participants = ?d AND is_accepted = 1)";
}
if (!empty($sql_session)) {
    $query_vars = [$course_id,$uid];
} else {
    $query_vars = [$course_id];
}

if(($is_consultant && !$is_coordinator) or ($is_simple_user)){
    $data['next_session'] = Database::get()->queryArray("SELECT * FROM mod_session 
                                                            WHERE course_id = ?d
                                                            AND `start` > NOW() 
                                                            AND visible = 1
                                                            $sql_session
                                                            ORDER BY `start` ASC $limit", $query_vars);
}elseif($is_coordinator){
    $minDate = Database::get()->querySingle("SELECT MIN(`start`) AS st FROM mod_session 
                                            WHERE course_id = ?d
                                            AND `start` > NOW()
                                            AND visible = 1", $course_id);

    if($minDate){
        $data['next_session'] = Database::get()->queryArray("SELECT * FROM mod_session 
                                                            WHERE course_id = ?d
                                                            AND `start` = ?t", $course_id, $minDate->st);
    }
}

if (!empty($sql_session)) {
    $query_vars = [$course_id,1,$uid];
} else {
    $query_vars = [$course_id,1];
}
$data['current_sessions'] = $course_sessions = Database::get()->queryArray("SELECT * FROM mod_session
                                                                            WHERE course_id = ?d
                                                                            AND visible = ?d
                                                                            $sql_session
                                                                            ORDER BY `start` ASC", $query_vars);
                                                                            
view('modules.session.index', $data);
