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
 *
 * @file date_available.php
 * @brief Display user available date
 */
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

//show all events
if(isset($_POST['action']) or isset($_GET['view'])) {

    if(isset($_GET['view']) and isset($_GET['show_m'])){

        $tutor_id = intval($_GET['show_m']);

        $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

        $eventArr = array();


        $result_events = Database::get()->queryArray("SELECT id,user_id,start,end FROM date_availability_user
                                                        WHERE start BETWEEN (?t) AND (?t)
                                                        AND user_id = ?d",$start,$end,$tutor_id);



        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'title' => nameTutor($row->user_id,$row->start,$row->end),
                    'start' => $row->start,
                    'end' => $row->end,
                    'user_id' => $row->user_id,
                    //'className' => dontShowTutorIfIsNotTutorOfGroup($row->user_id,$tutor_id),
                    'backgroundColor' => getBackgroundEvent($row->user_id,$row->start,$row->end)
                ];
            }
        }

        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();

    }

    // add new event section
    elseif($_POST['action'] == "add"){

        $add = Database::get()->query("INSERT INTO date_availability_user SET
                            user_id = ?d,
                            start = ?t,
                            end = ?t", $_POST['user'], date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s',strtotime($_POST["end"])));

            if($add){
                echo 1;
            }else{
                echo 0;
            }

        exit();

    }

    //update event
    elseif($_POST['action'] == "update"){

        //get old date for tutor before changing his date
        $old_date = Database::get()->querySingle("SELECT * FROM date_availability_user WHERE id = ?d",$_POST['id']);
        $old_tutor = $old_date->user_id;
        $old_start = $old_date->start;
        $old_end = $old_date->end;

        //check if exists mentee who have been made a book
        $checkExistSimpleUser = Database::get()->querySingle("SELECT COUNT(id) as c FROM date_booking
                                                            WHERE teacher_id = ?d
                                                            AND start = ?t
                                                            AND end = ?t",$old_tutor,$old_start,$old_end)->c;

        if($checkExistSimpleUser == 0 and (($old_tutor == $uid) or $is_admin)){
            if($_POST['user_id'] == $uid or $is_admin){
                $update = Database::get()->query("UPDATE date_availability_user SET start = ?t, end = ?t
                                                WHERE id = ?d
                                                AND user_id = ?d",date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s', strtotime($_POST["end"])), $_POST["id"], $_POST["user_id"]);

                echo 1;

            }else{
                echo 0;
            }
        }else{
            echo 2;
        }


        exit();

    }

    // remove event
    elseif($_POST['action'] == "delete"){

        //get old date for tutor before changing his date
        $old_date = Database::get()->querySingle("SELECT * FROM date_availability_user WHERE id = ?d",$_POST['id']);
        if($old_date){
            $old_tutor = $old_date->user_id;
            $old_start = $old_date->start;
            $old_end = $old_date->end;
        }


         //check if exists simple user who have made a book
         $checkExistSimpleUser = Database::get()->querySingle("SELECT COUNT(id) as c FROM date_booking
                                                            WHERE teacher_id = ?d
                                                            AND start = ?t
                                                            AND end = ?t",$old_tutor,$old_start,$old_end)->c;

        if($checkExistSimpleUser == 0){
            $event_id = $_POST['id'];
            $check = Database::get()->querySingle("SELECT user_id FROM date_availability_user WHERE id = ?d",$event_id)->user_id;

            if(($check == $uid) or $is_admin){
                $del = Database::get()->query("DELETE FROM date_availability_user WHERE id = ?d",$event_id);
                if($del){
                    echo 1;
                }
            }else{
                echo 0;
            }
        }else{
            echo 2;
        }

        exit();

    }

}


function getBackgroundEvent($userId,$start,$end){

    $color = '';

    //if exists booking
    $existBooking = Database::get()->querySingle("SELECT COUNT(id) as c FROM date_booking
                                                        WHERE teacher_id = ?d
                                                        AND start = ?t
                                                        AND end = ?t",$userId,$start,$end)->c;
    if($existBooking > 0){
        $color = '#FFC0CB';
    }else{
        $color .= '#1E7E0E';
    }

    return $color;

}

function nameTutor($userId,$start,$end){
    global $uid, $langName, $langBooking;

    $name = "";
    $TutorGivenname = Database::get()->querySingle("SELECT `givenname` FROM user WHERE id = ?d",$userId)->givenname;
    $TutorSurname = Database::get()->querySingle("SELECT `surname` FROM user WHERE id = ?d",$userId)->surname;

    $booking = Database::get()->querySingle("SELECT * FROM date_booking
                                                    WHERE teacher_id = ?d
                                                    AND start = ?t AND end = ?t",$userId,$start,$end);

    $name .= "<div class='col-12 container-events-available px-2'>";
    if($booking){
        $name .= "
                    <div class='col-12 mb-1'>
                        <p class='Neutral-800-cl text-decoration-underline mb-0'>$langName</p>
                        <small class='Neutral-800-cl mb-0'>$TutorGivenname $TutorSurname</small>
                    </div>


                    <div class='col-12 mb-1'>
                        <p class='Neutral-800-cl text-decoration-underline mb-0'>$langBooking</p>
                        <small class='Neutral-800-cl mb-0'>$booking->title</small>
                    </div>
                    ";
    }else{
        $name .= "
                    <div class='col-12 mb-1'>
                        <p class='Neutral-800-cl text-decoration-underline mb-0'>$langName</p>
                        <small class='Neutral-800-cl mb-0'>$TutorGivenname $TutorSurname</small>
                    </div>

                    ";
    }

    $name .= "</div>";
    return $name;
}





