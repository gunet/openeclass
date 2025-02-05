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

    $bgColor = '';
    if(isset($_GET['theUser']) and $_GET['theUser'] == 'admin'){
        $result_events = Database::get()->queryArray("SELECT * FROM admin_calendar WHERE id = ?d",$eventId);
        $bgColor = '#006400';
    }else{
        $result_events = Database::get()->queryArray("SELECT * FROM personal_calendar WHERE id = ?d",$eventId);
        $bgColor = '#800080';
    }
   

   
    if($result_events){
        foreach($result_events as $row){
            if (is_null($row->end) or $row->end == "0000-00-00 00:00:00") {
                $duration_arr = explode(':', $row->duration);
                $startDatetime = date('Y-m-d H:i:s', strtotime($row->start));
                $duration_text = '+'.$duration_arr[0].' hours'.' +'.$duration_arr[1].' minutes';
                $enddateEvent = date('Y-m-d H:i:s', strtotime($startDatetime . $duration_text));
                $table = (isset($_GET['theUser']) and $_GET['theUser'] == 'admin') ? 'admin_calendar' : 'personal_calendar';
                Database::get()->query("UPDATE $table SET end = ?t WHERE id = ?d", $enddateEvent, $row->id);
                $enddate = $enddateEvent;
            } else {
                $enddate = $row->end;
            }
            $eventArr[] = [
                'id' => $row->id,
                'title' => $row->title,
                'start' => $row->start,
                'end' => $enddate,
                'user_id' => $row->user_id,
                'backgroundColor' => $bgColor
            ];
        }
    }
    
    header('Content-Type: application/json');

    echo json_encode($eventArr);

    exit();
}