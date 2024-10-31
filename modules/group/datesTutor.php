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

$require_login = true;
$require_current_course = true;
$require_user_registration = TRUE;
$require_help = true;
$helpTopic = 'available_dates';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';


//show all events
if(isset($_GET['view'])) {

    if(isset($_GET['view']) and isset($_GET['show_tutor']) and isset($_GET['show_group']) and isset($_GET['lesson_id'])){

        $lesson_id = $_GET['lesson_id'];
        $group_id = $_GET['show_group'];
        $tutor_id = $_GET['show_tutor'];


        $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

        $eventArr = array();


        $result_events = Database::get()->queryArray("SELECT id,lesson_id,user_id,group_id,start,end FROM tutor_availability_group
                                                        WHERE start BETWEEN (?t) AND (?t)
                                                        AND end > NOW()
                                                        AND lesson_id = ?d
                                                        AND group_id = ?d
                                                        AND user_id = ?d",$start,$end,$lesson_id,$group_id,$tutor_id);



        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'lesson' => $row->lesson_id,
                    'title' => showTimeEvent($row->start,$row->end,$row->id),
                    'start' => $row->start,
                    'end' => $row->end,
                    'group_id' => $row->group_id,
                    'user_id' => $row->user_id
                ];
            }
        }

        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();


    }


}

function showTimeEvent($startTime,$endTime,$evtId){

    global $urlAppend,$langViewHour;

    $availableTime = "<span style='background-color:transparent;' class=' tutor-available-event-date'>".date('H:i', strtotime($startTime)).' - '.date('H:i', strtotime($endTime))."</span>";

    return $availableTime;

}

