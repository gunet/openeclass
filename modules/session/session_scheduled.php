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
 * @file session_scheduled.php
 * @brief Display a detailed table about scheduled session for user
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'functions.php';

check_activation_of_collaboration();

$pageName = $langSummaryScheduledSessions;

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);

load_js('datatables');

$sql = "";
$data['current_time'] = $current_time = date('Y-m-d H:i:s', strtotime('now'));
if($is_consultant && !$is_coordinator){
    $sql = "AND creator = ?d";
}elseif($is_simple_user){
    $sql = "AND id IN (SELECT session_id FROM mod_session_users WHERE participants = ?d AND is_accepted = 1)";
}
if (!empty($sql)) {
    $query_vars = [$course_id, 1, $uid];
} else {
    $query_vars = [$course_id, 1];
}

$sessions = Database::get()->queryArray("SELECT * FROM mod_session 
                                    WHERE course_id = ?d
                                    AND visible = ?d
                                    $sql
                                    ORDER BY start ASC", $query_vars);

if($is_simple_user && count($sessions) > 0){
    $visible_sids = array();
    $user_sessions = findUserVisibleSessions($uid, $sessions);
    foreach ($user_sessions as $d) {
        $visible_sids[] = $d->id;
    }
    foreach($sessions as $s){
        $IncompletedPrereq = 0;
        if(!in_array($s->id, $visible_sids)){
            $IncompletedPrereq = 1;
        }
        $s->display = (($s->start > $current_time) or $IncompletedPrereq) ? 'pe-none opacity-help' : '';
        $s->has_prereq = $IncompletedPrereq;
    }
}

$data['sessions'] = $sessions;

view('modules.session.session_scheduled', $data);
