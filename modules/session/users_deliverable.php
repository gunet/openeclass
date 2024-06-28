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
 * @file users_deliverable.php
 * @brief Display uploaded docs by users
 */

$require_login = true;
$require_current_course = true;
$require_consultant = true;
$require_help = TRUE;
$helpTopic = 'course_sessions_deliverable_docs';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

check_activation_of_collaboration();

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}
elseif(isset($_GET['id'])){
    $data['sessionID'] = $sessionID = $_GET['id'];
}

session_exists($sessionID);

load_js('tools.js');
load_js('datatables');

$sessionTitle = title_session($course_id,$sessionID);
$pageName = $langDocSender;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);

$data['is_tutor_course'] = $is_tutor_course = is_tutor_course($course_id,$uid);
$data['is_consultant'] = $is_consultant = is_consultant($course_id,$uid);
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));
student_view_is_active();

$user_ids = [];
$deliverable_info = [];
Database::get()->queryFunc("SELECT user.id, user.surname, user.givenname, document.filename
                                FROM mod_session_users, user, document
                                WHERE mod_session_users.session_id = ?d AND
                                      mod_session_users.participants = user.id AND
                                      mod_session_users.participants = document.lock_user_id AND
                                      document.course_id = ?d AND
                                      document.subsystem = ?d AND
                                      document.subsystem_id = ?d
                                ORDER BY user.surname, user.givenname",
    function ($item) use (&$deliverable_info,&$user_ids) {
        $user_ids[]= $item->id;
        $indexes = array_keys($user_ids, $item->id);
        if(count($indexes) > 1){
            foreach($deliverable_info as $i => $val){
                if($val['user_id'] == $item->id && $val['user_total_docs'] < count($indexes)){
                    unset($deliverable_info[$i]);
                }
            }
        }
        $deliverable_info[] = [ 
                                'user_id' => $item->id, 
                                'user_surname' => $item->surname, 
                                'user_givenname' => $item->givenname, 
                                'user_total_docs' => count($indexes) 
                               ];
    }, $sessionID,$course_id,MYSESSIONS,$sessionID);

$data['deliverable_info'] = $deliverable_info;

view('modules.session.users_deliverable', $data);
