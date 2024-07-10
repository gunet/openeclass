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
 * @file consultinh_completion.php
 * @brief Display a detailed table about consulting completion for each user
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'functions.php';

check_activation_of_collaboration();

$pageName = $langTableCompletedConsulting;

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

$users_actions = [];
$sql_consultant = "";
if($is_consultant && !$is_coordinator){
    $sql_consultant = "AND creator = $uid";
}
$res = Database::get()->queryFunc("SELECT user_id FROM course_user 
                                   WHERE course_id = ?d 
                                   AND status = ?d 
                                   AND tutor = ?d 
                                   AND editor = ?d 
                                   AND course_reviewer = ?d", function($result) use(&$course_id, &$users_actions, &$langAttemptActive, &$langCompletedSession, &$sql_consultant)  {
                                        $user_badge_sessions = Database::get()->queryArray("SELECT id,title,start FROM mod_session 
                                                                                     WHERE course_id = ?d AND visible = ?d
                                                                                     AND id IN (SELECT session_id FROM mod_session_users
                                                                                                    WHERE participants = ?d 
                                                                                                    AND is_accepted = ?d)
                                                                                     AND id IN (SELECT session_id FROM badge WHERE course_id = ?d AND session_id > 0)
                                                                                     $sql_consultant", $course_id, 1, $result->user_id, 1, $course_id);
                                        if(count($user_badge_sessions)){
                                            $users_actions[$result->user_id] = $user_badge_sessions;
                                            if(count($users_actions) > 0){
                                                foreach($users_actions as $key => $val){
                                                    $per = 0;
                                                    foreach($val as $v){
                                                        $badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $v->id);
                                                        if($badge){
                                                            $per = get_cert_percentage_completion_by_user('badge',$badge->id,$key);
                                                        }
                                                        if($per < 100){
                                                            $icon_badge = "<span class='badge Accent-200-bg'>$langAttemptActive</span>"; 
                                                        }else{
                                                            $icon_badge = "<span class='badge Success-200-bg'>$langCompletedSession</span>";
                                                        }
                                                        $v->completion = $icon_badge;
                                                    }
                                                }
                                            }
                                        }
                                 }, $course_id, USER_STUDENT, 0, 0, 0);

$data['users_actions'] = $users_actions;

view('modules.session.consulting_completion', $data);
