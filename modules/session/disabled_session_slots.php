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
$require_help = TRUE;
$helpTopic = 'course_sessions';

require_once '../../include/baseTheme.php';


if(isset($_GET['course']) and isset($_GET['show_sessions'])){

    $course_id = $_GET['course'];

    $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
    $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

    $sessionArr = array();

    $result_sessions = Database::get()->queryArray("SELECT * FROM mod_session WHERE course_id = ?d",$course_id);

    if($result_sessions){
        foreach($result_sessions as $row){
            $sessionArr[] = [
                'id' => $row->id,
                'creator' => $row->creator,
                'title' => $row->title,
                'start' => $row->start,
                'end' => $row->finish,
                'className' => 'exist_event_session',
                'backgroundColor' => '#000000',
                'course_id' => $row->course_id
            ];
        }
    }

    header('Content-Type: application/json');

    echo json_encode($sessionArr);

    exit();


}