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
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/action.php';
require_once 'modules/progress/process_functions.php';
require_once 'functions.php';

check_activation_of_collaboration();

$action = new action();
$action->record(MODULE_ID_SESSION);

load_js('tools.js');
load_js('datatables');

$pageName = $langSession;
$data['is_tutor_course'] = $is_tutor_course = is_tutor_course($course_id,$uid);
$data['is_consultant'] = $is_consultant = is_consultant($course_id,$uid);
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));
student_view_is_active();

// Show remote or not sessions
$remoteType = 1;
$sql_remote = "AND type_remote = 1";
if(isset($_POST['show_remote']) and $_POST['show_remote'] == 0){
    $sql_remote = "AND type_remote = 0";
    $remoteType = 0;
}
$data['remoteType'] = $remoteType;

// Delete session from consultant or course tutor
if(isset($_POST['delete_session'])){
    $sqlbadge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $_POST['session_id']);
    if($sqlbadge){
        $badge_id = $sqlbadge->id;
        $res = Database::get()->querySingle("SELECT id FROM badge_criterion WHERE badge = ?d",$badge_id);
        $badge_criterion_id = $res->id;
        Database::get()->query("DELETE FROM user_badge_criterion WHERE badge_criterion = ?d",$badge_criterion_id);
        Database::get()->query("DELETE FROM user_badge WHERE badge = ?d",$badge_id);
        Database::get()->query("DELETE FROM badge_criterion WHERE id = ?d",$badge_criterion_id);
        Database::get()->query("DELETE FROM badge WHERE id = ?d",$badge_id);
    }
    Database::get()->query("DELETE FROM session_prerequisite WHERE session_id = ?d OR prerequisite_session = ?d",$_POST['session_id'],$_POST['session_id']);
    $dirname = "$webDir/courses/$course_code/session/session_" . $_POST['session_id'];
    if (file_exists($dirname)) {
        array_map('unlink', glob("$dirname/*.*"));
        rmdir($dirname);
    }
    Database::get()->query("DELETE FROM mod_session WHERE id = ?d",$_POST['session_id']);
    Session::flash('message',$langDelSessionSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/index.php?course=".$course_code);
}

// Leave session from simple
if(isset($_POST['leave_session'])){
    Database::get()->query("DELETE FROM mod_session_users WHERE session_id = ?d AND participants = ?d",$_POST['session_leave_id'],$uid);
    Session::flash('message',$langLeaveSessionSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/session/index.php?course=".$course_code);
}

// An consultant can create a session
if($is_tutor_course or $is_consultant){

    $data['action_bar'] = action_bar([
        [ 'title' => $langAddSession,
          'url' => 'new.php?course=' . $course_code,
          'icon' => 'fa-plus-circle',
          'button-class' => 'btn-success',
          'level' => 'primary-label' ],
        [
            'title' => $langCompletedConsulting,
            'url' => $urlAppend . "modules/session/completion.php?course=" . $course_code . "&addSessions=true",
            'icon' => 'fa-solid fa-medal',
            'button-class' => 'btn-success',
            'level' => 'primary-label'
        ],
        [
            'title' => $langTableCompletedConsulting,
            'url' => $urlAppend . "modules/session/completion.php?course=" . $course_code . "&showCompletedConsulting=true",
            'icon' => 'fa-solid fa-list',
            'button-class' => 'btn-success'
        ],
    ], false);

    if($is_tutor_course){ // is tutor course
        $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                        WHERE course_id = ?d
                                                                        $sql_remote
                                                                        ORDER BY start ASC",$course_id); 
                                                                        
    }elseif($is_consultant){// is consultant user
        $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                    WHERE course_id = ?d
                                                                    AND creator = ?d
                                                                    $sql_remote
                                                                    ORDER BY start ASC",$course_id,$uid); 
    }

    if(count($data['individuals_group_sessions']) > 0){
        $participants = array();
        foreach ($data['individuals_group_sessions'] as $s) {
            $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $s->id);
            $per = $has_badge = 0;
            if ($sql_badge) {
                $badge_id = $sql_badge->id;
                $has_badge = $badge_id;
                $participants = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d",$s->id);
                if(count($participants) > 0){
                    foreach($participants as $p){
                        $per = $per + get_cert_percentage_completion_by_user('badge',$badge_id,$p->participants);
                    }
                }

            }
            $s->percentage = (count($participants) > 0) ? $per/count($participants) : $per;
            $s->has_badge = $has_badge;
        }
    }

}else{// is simple user

    $data['action_bar'] = action_bar([
        [
            'title' => $langTableCompletedConsulting,
            'url' => $urlAppend . "modules/session/completion.php?course=" . $course_code . "&showCompletedConsulting=true",
            'icon' => 'fa-solid fa-list',
            'level' => 'primary',
            'button-class' => 'btn-success'
        ],
    ], false);
    
    $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                            WHERE visible = ?d
                                            AND course_id = ?d
                                            $sql_remote
                                            AND id IN (SELECT session_id FROM mod_session_users
                                                        WHERE participants = ?d)
                                            ORDER BY start ASC",1,$course_id,$uid); 


    foreach ($data['individuals_group_sessions'] as $s) {
        check_session_progress($s->id);  // check session completion - call to Game.php
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
        $cu->display = ($vis == 0 or $not_shown) ? 'not_visible' : '';
        $cu->icon = $icon ?? '';
        $cu->percentage = $per;
        $cu->has_badge = $has_badge;
    }

}
view('modules.session.index', $data);
