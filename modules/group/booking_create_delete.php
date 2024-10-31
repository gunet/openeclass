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
 * @file booking_create_delete.php
 * @brief Display user available date
 */
$require_login = true;
$require_current_course = true;
$require_user_registration = TRUE;
$require_help = true;
$helpTopic = 'available_dates';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';

//show all events
if(isset($_POST['action']) or isset($_GET['view'])) {

    if(isset($_GET['view']) and isset($_GET['show_tutor']) and isset($_GET['show_group'])){

        $group_id = intval($_GET['show_group']);
        $tutor_id = intval($_GET['show_tutor']);

        $start = date('Y-m-d H:i:s',strtotime($_GET['start']));
        $end = date('Y-m-d H:i:s',strtotime($_GET['end']));

        $eventArr = array();

        $result_events = Database::get()->queryArray("SELECT id,user_id,group_id,start,end FROM tutor_availability_group
                                                        WHERE start BETWEEN (?t) AND (?t)
                                                        AND user_id = ?d
                                                        AND group_id = ?d",$start,$end,$tutor_id,$group_id);

        if($result_events){
            foreach($result_events as $row){
                $eventArr[] = [
                    'id' => $row->id,
                    'tutor' => $row->user_id,
                    'group' => $row->group_id,
                    'title' => TitleBooking($row->start,$row->end,$row->user_id,$row->group_id),
                    'start' => $row->start,
                    'end' => $row->end,
                    'className' => classNameBooking($row->start,$row->end,$row->user_id,$row->group_id),
                    'backgroundColor' => ColorExistBooking($row->start,$row->end,$row->user_id,$row->group_id)
                ];
            }
        }

        header('Content-Type: application/json');

        echo json_encode($eventArr);

        exit();


    }

    // add new event section
    elseif($_POST['action'] == "add"){

        //Before add booking, check if tutor has deleted the current date for booking
        $checkDateTutorExist = Database::get()->querySingle("SELECT COUNT(id) as c FROM tutor_availability_group
                                                                WHERE user_id = ?d
                                                                AND group_id = ?d
                                                                AND start = ?t
                                                                AND end = ?t
                                                                AND lesson_id = ?d",$_POST['tutor_Id'],$_POST['group_Id'],$_POST["start"],$_POST["end"],$_POST['course_Id'])->c;

        if($checkDateTutorExist > 0){

            //check if another user has made booking before continue
            $checkOtherUserBooking = Database::get()->querySingle("SELECT COUNT(id) as c FROM booking
                                                                        WHERE lesson_id = ?d
                                                                        AND group_id = ?d
                                                                        AND tutor_id = ?d
                                                                        AND start = ?t
                                                                        AND end = ?t",$_POST['course_Id'],$_POST['group_Id'],$_POST['tutor_Id'],$_POST["start"],$_POST["end"])->c;


            if($checkOtherUserBooking == 0){

                $add = Database::get()->query("INSERT INTO booking SET
                                    lesson_id = ?d,
                                    group_id = ?d,
                                    tutor_id = ?d,
                                    title = ?s,
                                    start = ?t,
                                    end = ?t",$_POST["course_Id"], $_POST['group_Id'], $_POST["tutor_Id"],$_POST['title'],date('Y-m-d H:i:s', strtotime($_POST["start"])), date('Y-m-d H:i:s',strtotime($_POST["end"])));



                $add_bookind_by_user = Database::get()->query("INSERT INTO booking_user SET
                                                booking_id = ?d,
                                                simple_user_id = ?d",$add->lastInsertID,$uid);


                //send email to the tutor about the booking from user
                $userName = $_POST['title'];
                $tutorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$_POST["tutor_Id"])->givenname;
                $tutorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$_POST["tutor_Id"])->surname;
                $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id = ?d",$_POST["tutor_Id"])->email;
                $dateFrom = $_POST["start"];
                $dateEnd = $_POST["end"];

                $emailHeader = "
                <!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langAddBookingByUser</div>
                            </div>
                        </div>";

                $emailMain = "
                <!-- Body Section -->
                    <div id='mail-body'>
                        <br>
                        <div>$langDetailsBooking</div>
                        <div id='mail-body-inner'>
                            <ul id='forum-category'>
                                <li><span><b>$langName: </b></span> <span>$userName</span></li>
                                <li><span><b>$langTutor: </b></span> <span>$tutorName $tutorSurname</span></li>
                                <li><span><b>$langDate: </b></span>$dateFrom - $dateEnd<span></span></li>
                            </ul>
                        </div>
                        <div>
                            <br>
                            <p>$langProblem</p><br>" . get_config('admin_name') . "
                            <ul id='forum-category'>
                                <li>$langManager: $siteName</li>
                                <li>$langTel: -</li>
                                <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                            </ul>
                        </div>
                    </div>";

                $emailsubject = $siteName.':'.$langAddBookingByUser;

                $emailbody = $emailHeader.$emailMain;

                $emailPlainBody = html2text($emailbody);

                send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);

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

        $event_id = $_POST['id'];
        $tutor_availabity_group_id = Database::get()->queryArray("SELECT * FROM tutor_availability_group WHERE id = ?d",$event_id);

        $tutor_user = '';
        $tutor_group = '';
        $tutor_start = '';
        $tutor_end = '';
        if(count($tutor_availabity_group_id) > 0){
            foreach($tutor_availabity_group_id as $m){
                $tutor_user = $m->user_id;
                $tutor_group = $m->group_id;
                $tutor_start = $m->start;
                $tutor_end = $m->end;
            }

            $bookingId = Database::get()->querySingle("SELECT id FROM booking
                                                        WHERE lesson_id = ?d
                                                        AND group_id = ?d
                                                        AND tutor_id = ?d
                                                        AND start = ?t
                                                        AND end = ?t",$course_id,$tutor_group,$tutor_user,$tutor_start,$tutor_end)->id;

            //send email to the tutor about canceling booking by user
            $userName = Database::get()->querySingle("SELECT title FROM booking WHERE id = ?d",$bookingId)->title;
            $tutorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id IN (SELECT tutor_id FROM booking WHERE id = ?d)",$bookingId)->givenname;
            $tutorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id IN (SELECT tutor_id FROM booking WHERE id = ?d)",$bookingId)->surname;
            $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT tutor_id FROM booking WHERE id = ?d)",$bookingId)->email;
            $dateFrom = Database::get()->querySingle("SELECT start FROM booking WHERE id = ?d",$bookingId)->start;
            $dateEnd = Database::get()->querySingle("SELECT end FROM booking WHERE id = ?d",$bookingId)->end;

            $del = Database::get()->query("DELETE FROM booking 
                                            WHERE lesson_id = ?d
                                            AND group_id = ?d
                                            AND tutor_id = ?d
                                            AND start = ?t
                                            AND end = ?t",$course_id,$tutor_group,$tutor_user,$tutor_start,$tutor_end);

            if($del){

                $emailHeader = "
                <!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langDeleteBookingByUser</div>
                            </div>
                        </div>";

                $emailMain = "
                <!-- Body Section -->
                    <div id='mail-body'>
                        <br>
                        <div>$langDetailsBooking</div>
                        <div id='mail-body-inner'>
                            <ul id='forum-category'>
                                <li><span><b>$langName: </b></span> <span>$userName</span></li>
                                <li><span><b>$langTutor: </b></span> <span>$tutorName $tutorSurname</span></li>
                                <li><span><b>$langDate: </b></span>$dateFrom - $dateEnd<span></span></li>
                            </ul>
                        </div>
                        <div>
                            <br>
                            <p>$langProblem</p><br>" . get_config('admin_name') . "
                            <ul id='forum-category'>
                                <li>$langManager: $siteName</li>
                                <li>$langTel: -</li>
                                <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                            </ul>
                        </div>
                    </div>";

                $emailsubject = $siteName.':'.$langDeleteBookingByUser.'--'.$userName;

                $emailbody = $emailHeader.$emailMain;

                $emailPlainBody = html2text($emailbody);

                send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);

                echo 1;
            }

        }else{
            echo 0;
        }

        exit();

    }

}


function classNameBooking($bookingTutorStart,$bookingTutorEnd,$tutor_id,$group_id){
    global $uid, $course_id;

    $html_bookingClassName = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingTutorEnd < $now){
        $hasExpired = true;
    }

    //check if other mentee has booking with this mentor before continue
    $checkBookingByOtherUserOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM booking
                                                                      WHERE id IN (SELECT booking_id FROM booking_user WHERE simple_user_id NOT IN (?d))
                                                                      AND lesson_id = ?d AND group_id = ?d
                                                                      AND tutor_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$course_id,$group_id,$tutor_id,$bookingTutorStart,$bookingTutorEnd)->id;

    if($checkBookingByOtherUserOfGroup == 0 and !$hasExpired){
        $BookingByUser = Database::get()->queryArray("SELECT * FROM booking 
                                                    WHERE lesson_id = ?d 
                                                    AND group_id = ?d 
                                                    AND tutor_id = ?d
                                                    AND start = ?t 
                                                    AND end = ?t
                                                    AND id IN (SELECT booking_id FROM booking_user WHERE simple_user_id = ?d)",$course_id,$group_id,$tutor_id,$bookingTutorStart,$bookingTutorEnd,$uid);

        if(count($BookingByUser) > 0){
            $html_bookingClassName = 'bookingDelete';
        }else{
            $html_bookingClassName = 'bookingAdd';
        }
    }else{
        if($hasExpired){
            $html_bookingClassName = 'pe-none opacity-help';
        }else{
            $html_bookingClassName = 'pe-none';
        }

    }


    return $html_bookingClassName;
}

function TitleBooking($bookingTutorStart,$bookingTutorEnd,$tutor_id,$group_id){
    global $uid, $course_id ,$langHaveDoneBooking, $langDoBooking, $langDisableBooking, $langBookingIsDone, $langAcceptBooking, $langYes, $langNo;

    $html_bookingTitle = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingTutorEnd < $now){
        $hasExpired = true;
    }

    //check if another user has booked with this tutor before continue
    $checkBookingByOtherUserOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM booking
                                                                      WHERE id IN (SELECT booking_id FROM booking_user WHERE simple_user_id NOT IN (?d))
                                                                      AND lesson_id = ?d AND group_id = ?d
                                                                      AND tutor_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$course_id,$group_id,$tutor_id,$bookingTutorStart,$bookingTutorEnd)->id;

    if($checkBookingByOtherUserOfGroup == 0 and !$hasExpired){
        $BookingByUser = Database::get()->queryArray("SELECT * FROM booking 
                                                    WHERE lesson_id = ?d 
                                                    AND group_id = ?d 
                                                    AND tutor_id = ?d
                                                    AND start = ?t 
                                                    AND end = ?t
                                                    AND id IN (SELECT booking_id FROM booking_user WHERE simple_user_id = ?d)",$course_id,$group_id,$tutor_id,$bookingTutorStart,$bookingTutorEnd,$uid);

        if(count($BookingByUser) > 0){
            $html_bookingTitle .= "<p class='text-center TextBold smallText simple-user-booking-event'>$langHaveDoneBooking</p>";
            foreach($BookingByUser as $b){
                if($b->accepted == 1){
                    $html_bookingTitle .= "<p class='text-center TextBold smallText simple-user-booking-event mt-1'>$langAcceptBooking: $langYes</p>";
                }else{
                    $html_bookingTitle .= "<p class='text-center TextBold smallText simple-user-booking-event mt-1'>$langAcceptBooking: $langNo</p>";
                }
            }
        }else{
            $html_bookingTitle .= "<p class='text-center TextBold simple-user-booking-event smallText'>$langDoBooking<p>";
        }
    }else{
        if($hasExpired){
            $html_bookingTitle .= "<p class='text-center TextBold simple-user-booking-event smallText'>$langDisableBooking</p>";
        }else{
            $html_bookingTitle .= "<p class='text-center TextBold simple-user-booking-event smallText'>$langBookingIsDone</p>";
        }

    }


    return $html_bookingTitle;
}


function ColorExistBooking($bookingTutorStart,$bookingTutorEnd,$tutor_id,$group_id){
    global $uid, $course_id;

    $html_bookingExist = '';

    $hasExpired = false;
    $now = date('Y-m-d H:i:s', strtotime('now'));
    if($bookingTutorEnd < $now){
        $hasExpired = true;
    }

    //check if other mentee has booking with this mentor before continue
    $checkBookingByOtherUserOfGroup = Database::get()->querySingle("SELECT COUNT(id) as id FROM booking
                                                                      WHERE id IN (SELECT booking_id FROM booking_user WHERE simple_user_id NOT IN (?d))
                                                                      AND lesson_id = ?d AND group_id = ?d
                                                                      AND tutor_id = ?d AND start = ?t
                                                                      AND end = ?t",$uid,$course_id,$group_id,$tutor_id,$bookingTutorStart,$bookingTutorEnd)->id;

    if($checkBookingByOtherUserOfGroup == 0 and !$hasExpired){
        $BookingByUser = Database::get()->queryArray("SELECT * FROM booking 
                                                        WHERE lesson_id = ?d 
                                                        AND group_id = ?d 
                                                        AND tutor_id = ?d
                                                        AND start = ?t 
                                                        AND end = ?t
                                                        AND id IN (SELECT booking_id FROM booking_user WHERE simple_user_id = ?d)",$course_id,$group_id,$tutor_id,$bookingTutorStart,$bookingTutorEnd,$uid);

        if(count($BookingByUser) > 0){
            $html_bookingExist = '#1E7E0E';
            foreach($BookingByUser as $b){
                if($b->accepted == 1){
                    $html_bookingExist = '#FFC0CB';
                }else{
                    $html_bookingExist = '#1E7E0E';
                }
            }
        }else{
            $html_bookingExist = '#337ab7';
        }
    }else{
        if($hasExpired){
            $html_bookingExist = '#000000';
        }else{
            $html_bookingExist = '#ffa500';
        }

    }

    return $html_bookingExist;
}




