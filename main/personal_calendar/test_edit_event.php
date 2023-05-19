<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */


/*
 * Events Component
 *
 * @version 1.0
 * @abstract This component displays personal user events and offers several operations on them.
 * The user can:
 * 1. Add new personal events
 * 2. Delete personal events (one by one or all at once)
 * 3. Modify existing events
 * 4. Associate events with courses and course objects
 */

 $require_login = true;
 require_once '../../include/baseTheme.php';


if(isset($_GET['eventID'])){
    $eventId = $_GET['eventID'];

    $eventArr = array();

    if(isset($_GET['theUser']) and $_GET['theUser'] == 'admin'){
        $result_events = Database::get()->queryArray("SELECT *FROM admin_calendar WHERE id = ?d",$eventId);
    }else{
        $result_events = Database::get()->queryArray("SELECT *FROM personal_calendar WHERE id = ?d",$eventId);
    }
   

   
    if($result_events){
        foreach($result_events as $row){
            $eventArr[] = [
                'id' => $row->id,
                'title' => $row->title,
                'start' => $row->start,
                'end' => $row->end,
                'user_id' => $row->user_id
            ];
        }
    }
    
    header('Content-Type: application/json');

    echo json_encode($eventArr);

    exit();
}