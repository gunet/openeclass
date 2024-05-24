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
require_once 'functions.php';

$action = new action();
$action->record(MODULE_ID_SESSION);

$pageName = $langSession;
$data['is_tutor_course'] = $is_tutor_course = is_tutor_course($course_id,$uid);
$data['is_consultant'] = $is_consultant = is_consultant($course_id,$uid);
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));

// Delete session from consultant or course tutor
if(isset($_POST['delete_session'])){
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
if($is_editor){

    $data['action_bar'] = action_bar([
        [ 'title' => $langAddSession,
          'url' => 'new.php?course=' . $course_code,
          'icon' => 'fa-plus-circle',
          'button-class' => 'btn-success',
          'level' => 'primary-label' ]
    ], false);

    if($is_tutor_course){ // is tutor course
        $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                        WHERE visible = ?d
                                                                        AND course_id = ?d",1,$course_id); 
                                                                        
    }elseif($is_consultant){// is consultant user
        $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                    WHERE visible = ?d
                                                                    AND course_id = ?d
                                                                    AND ( finish > NOW() OR start > NOW() )
                                                                    AND creator = ?d",1,$course_id,$uid); 
    }

}else{// is simple user

    $data['action_bar'] = action_bar([
        [ 'title' => $langBack,
          'url' => $urlAppend . 'courses/' . $course_code . '/',
          'icon' => 'fa-reply',
          'button-class' => 'btn-success',
          'level' => 'primary-label' ]
    ], false);
    
    $data['individuals_group_sessions'] = Database::get()->queryArray("SELECT * FROM mod_session
                                                                    WHERE visible = ?d
                                                                    AND course_id = ?d
                                                                    AND ( finish > NOW() OR start > NOW() )
                                                                    AND id IN (SELECT session_id FROM mod_session_users
                                                                                WHERE participants = ?d)",1,$course_id,$uid); 
}

view('modules.session.index', $data);
