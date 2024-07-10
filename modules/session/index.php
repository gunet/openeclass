<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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

if(isset($_POST['user_registration'])){
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    Database::get()->query("UPDATE mod_session_users SET
                            is_accepted = ?d
                            WHERE session_id = ?d
                            AND participants = ?d",1,$_POST['about_session'],$uid);

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
  
        send_mail_multipart('', '', '', $emailConsultant, $emailsubject, $emailPlainBody, $emailbody);

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

// Delete session from consultant or course tutor
if(isset($_POST['delete_session'])){
    delete_session($_POST['session_id']);
    Session::flash('message',$langDelSessionSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/index.php?course=".$course_code);
}

// Delete all sessions from course tutor
if(isset($_GET['delete_all_sessions'])){
    $session_ids = Database::get()->queryArray("SELECT id FROM mod_session");
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

// An consultant can create a session
if($is_coordinator or $is_consultant){

    $data['action_bar'] = action_bar([
        [ 
            'title' => $langAddSession,
            'url' => 'new.php?course=' . $course_code,
            'icon' => 'fa-plus-circle',
            'button-class' => 'btn-success',
            'level' => 'primary-label',
            'show' => ($is_editor || !$is_course_reviewer)
        ],
        [
            'title' => $langTableCompletedConsulting,
            'url' => $urlAppend . "modules/session/consulting_completion.php?course=" . $course_code,
            'icon' => 'fa-solid fa-users',
            'button-class' => 'btn-success',
            'level' => 'primary-label',
            'show' => !$is_simple_user
        ],
        [ 
            'title' => $langSummaryScheduledSessions,
            'url' => 'session_scheduled.php?course=' . $course_code,
            'icon' => 'fa-solid fa-list',
            'button-class' => 'btn-success',
            'level' => 'primary-label'
        ],
        [
            'title' => $langCompletedConsulting,
            'url' => $urlAppend . "modules/session/completion.php?course=" . $course_code . "&addSessions=true",
            'icon' => 'fa-solid fa-medal',
            'button-class' => 'btn-success',
            'show' => ($is_editor || !$is_course_reviewer)
        ],
        [
            'title' => $langPercentageCompletedConsultingByUser,
            'url' => $urlAppend . "modules/session/completion.php?course=" . $course_code . "&showCompletedConsulting=true",
            'icon' => 'fa-solid fa-percent',
            'button-class' => 'btn-success'
        ],
        [
            'title' => $langDelAllSessions,
            'url' => $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&delete_all_sessions=true',
            'class' => "delete",
            'confirm' => $langContinueToDelAllSessions,
            'icon' => 'fa-xmark',
            'show' => ($is_coordinator && $is_consultant)
        ],
    ], false);

    if($is_coordinator){ // is tutor course
        $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                        WHERE course_id = ?d
                                                                        $sql_remote
                                                                        $sql_session
                                                                        ORDER BY start ASC",$course_id); 
                                                                        
    }elseif($is_consultant){// is consultant user
        $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                    WHERE course_id = ?d
                                                                    AND creator = ?d
                                                                    $sql_remote
                                                                    $sql_session
                                                                    ORDER BY start ASC",$course_id,$uid); 
    }

    if(count($data['individuals_group_sessions']) > 0){
        $participants = array();
        foreach ($data['individuals_group_sessions'] as $s) {

            $all_participants_ids = session_participants_ids($s->id);
            foreach($all_participants_ids as $p){
                // This refers to session completion with completed tc.
                check_session_completion_by_tc_completed($s->id,$p);
                // This refers to session completion for other activities.
                check_session_progress($s->id,$p);  // check session completion - call to Game.php
            }

            $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $s->id);
            $per = $has_badge = 0;
            if ($sql_badge) {
                $badge_id = $sql_badge->id;
                $has_badge = $badge_id;
                $participants = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d",$s->id,1);
                if(count($participants) > 0){
                    foreach($participants as $p){
                        $per = $per + get_cert_percentage_completion_by_user('badge',$badge_id,$p->participants);
                    }
                }

            }
            $number_percentage = (count($participants) > 0) ? $per/count($participants) : $per;
            $s->percentage = round($number_percentage);
            $s->has_badge = $has_badge;
            $s->consultant = participant_name($s->creator);
        }
    }

}else{// is simple user

    $data['action_bar'] = action_bar([
        [
            'title' => $langPercentageCompletedConsulting,
            'url' => $urlAppend . "modules/session/completion.php?course=" . $course_code . "&showCompletedConsulting=true",
            'icon' => 'fa-solid fa-percent',
            'level' => 'primary-label',
            'button-class' => 'btn-success'
        ],
        [ 
            'title' => $langSummaryScheduledSessions,
            'url' => 'session_scheduled.php?course=' . $course_code,
            'icon' => 'fa-solid fa-list',
            'button-class' => 'btn-success',
            'level' => 'primary-label'
        ],
    ], false);
    
    $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                            WHERE visible = ?d
                                            AND course_id = ?d
                                            $sql_remote
                                            $sql_session
                                            AND id IN (SELECT session_id FROM mod_session_users
                                                        WHERE participants = ?d)
                                            ORDER BY start ASC",1,$course_id,$uid);


    foreach ($data['individuals_group_sessions'] as $s) {
        // This refers to session completion with completed tc.
        check_session_completion_by_tc_completed($s->id,$uid);
        // This refers to session completion for other activities.
        check_session_progress($s->id,$uid);  // check session completion - call to Game.php
    }

    $visible_sessions_id = [];
    $visible_user_sessions = findUserVisibleSessions($uid, $data['individuals_group_sessions']);
    foreach ($visible_user_sessions as $d) {
        $visible_sessions_id[] = $d->id;
    }

    foreach($data['individuals_group_sessions'] as $cu){
        $not_shown = false;
        $vis = $cu->visible;
        $per = 0;
        $has_badge = 0;
        if(participation_in_session($cu->id)){
            if (!(is_null($cu->start)) and (date('Y-m-d H:i:s') < $cu->start)) {
                $not_shown = true;
                $icon = icon('fa-clock fa-md', $langSessionNotStarted);
                $has_badge = -1;
            } else if (!in_array($cu->id, $visible_sessions_id)) {
                $not_shown = true;
                $icon = icon('fa-minus-circle fa-md', $langSessionNotCompleted);
                $has_badge = -2;
            } else {
                if (in_array($cu->id, $visible_sessions_id)) {
                    $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $cu->id);
                    if ($sql_badge) {
                        $badge_id = $sql_badge->id;
                        $has_badge = $badge_id;
                        $per = get_cert_percentage_completion('badge', $badge_id);
                        if ($per == 100) {
                            $icon = icon('fa-check-circle fa-md', $langInstallEnd);
                        } else {
                            $icon = icon('fa-hourglass-2 fa-md', $per . "%");
                        }
                    }
                }
            }
        }
        $cu->display = ($vis == 0 or $not_shown) ? 'not_visible' : '';
        $cu->icon = $icon ?? '';
        $cu->percentage = round($per);
        $cu->has_badge = $has_badge;
        $cu->consultant = participant_name($cu->creator);
        $cu->is_accepted_user = Database::get()->querySingle("SELECT is_accepted FROM mod_session_users WHERE session_id = ?d AND participants = ?d",$cu->id,$uid)->is_accepted;
    }

}

view('modules.session.index', $data);
