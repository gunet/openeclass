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
 * @file disabled_session_slots.php
 * @brief Disable sessions
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

    $session_edit_id = 0;
    if(isset($_GET['edit']) && isset($_GET['session'])){
        $session_edit_id = $_GET['session'];
    }

    if($result_sessions){
        foreach($result_sessions as $row){
            $sessionArr[] = [
                'id' => $row->id,
                'creator' => $row->creator,
                'title' => getTitleSession($row->id,$course_id),
                'start' => $row->start,
                'end' => $row->finish,
                'className' => getClassSession($row->id,$session_edit_id),
                'backgroundColor' => backgroundColorSession($row->id,$session_edit_id),
                'course_id' => $row->course_id
            ];
        }
    }

    header('Content-Type: application/json');

    echo json_encode($sessionArr);

    exit();


}

function getTitleSession($sid,$cid){

    global $langTitle, $langConsultant, $langParticipants;

    $html = "";
    $creator = "";
    $title_session = "";
    $html_participant = "";

    $creator_info = Database::get()->querySingle("SELECT mod_session.id,mod_session.creator,mod_session.title,user.id,user.givenname,user.surname FROM mod_session
                                             LEFT JOIN user ON mod_session.creator=user.id
                                             WHERE mod_session.id=?d
                                             AND mod_session.course_id=?d",$sid,$cid);
    if($creator_info){
        $title_session = $creator_info->title;
        $creator = $creator_info->givenname . " " . $creator_info->surname;
    }

    $participants_info = Database::get()->queryArray("SELECT mod_session_users.session_id,mod_session_users.participants,user.id,user.givenname,user.surname FROM mod_session_users
                                                        LEFT JOIN user ON mod_session_users.participants=user.id
                                                        WHERE mod_session_users.session_id=?d",$sid);
         
    if(count($participants_info)>0){
        $html_participant .= "<ul class='list-group list-group-flush'>";
        foreach($participants_info as $p){
            $html_participant .= "<li class='list-group-item element'>". $p->givenname . "&nbsp;" . $p->surname . "</li>";
        }
        $html_participant .= "</ul>";
    }


    $html .= "
        <div class='col-12 container-events-available px-2'>
            <div class='col-12 mb-1'>
                <p class='Neutral-800-cl text-decoration-underline mb-0'>$langTitle</p>
                <small class='Neutral-800-cl mb-0'>$title_session</small>
            </div>
            <div class='col-12 mb-1'>
                <p class='Neutral-800-cl text-decoration-underline mb-0'>$langConsultant</p>
                <small class='Neutral-800-cl mb-0'>$creator</small>
            </div>
            <div class='col-12 mb-1'>
                <p class='Neutral-800-cl text-decoration-underline mb-0'>$langParticipants</p>
                <small class='Neutral-800-cl mb-0'>$html_participant</small>
            </div>
        </div>
    ";

    return $html;
}

function backgroundColorSession($sid,$sid_edit){

    $color = "";

    if($sid != $sid_edit){// show other sessions
        $color = "#0073E6";
    }else{// edit session
        $color = "#1E7E0E";
    }
    
    return $color;
}

function getClassSession($sid,$sid_edit){
    $class_name = "";

    if($sid != $sid_edit){// show other sessions
        $class_name = "exist_event_session";
    }else{// edit session
        $class_name = "exist_edit_event_session";
    }
    
    return $class_name;
}