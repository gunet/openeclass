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
$require_valid_uid = TRUE;
$require_help = TRUE;
$helpTopic = 'available_booking';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';

$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);

$toolName = $langMYBookings;

load_js('datatables');

$data['tutor_id'] = $tutor_id = intval($_GET['user_id']);
$data['TutorGivenname'] = $TutorGivenname = Database::get()->querySingle("SELECT `givenname` FROM user WHERE id = ?d",$data['tutor_id'])->givenname;
$data['TutorSurname'] = $TutorSurname = Database::get()->querySingle("SELECT `surname` FROM user WHERE id = ?d",$data['tutor_id'])->surname;
$user_status = Database::get()->querySingle("SELECT `status` FROM user WHERE id = ?d",$data['tutor_id'])->status;

//Get status for user
$is_user_teacher = false;
if($user_status == USER_TEACHER){
    $is_user_teacher = true;
}
$data['is_user_teacher'] = $is_user_teacher;

if(isset($_POST['accept_book'])){

    $accept_booking = Database::get()->query("UPDATE date_booking SET accepted = ?d WHERE id = ?d",1,$_POST['accept_booking_id']);
    if($accept_booking){
        $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT student_id FROM date_booking_user WHERE booking_id = ?d)",$_POST['accept_booking_id'])->email;
        $details_booking = Database::get()->queryArray("SELECT * FROM date_booking WHERE id = ?d",$_POST['accept_booking_id']);

        if(count($details_booking) > 0){
            foreach($details_booking as $d){
                $userName = $d->title;
                $tutorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$d->tutor_id)->givenname;
                $tutorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$d->tutor_id)->surname;
                $dateFrom = $d->start;
                $dateEnd = $d->end;
            }

            Session::flash('message',$langBookingHasAccepted.$userName);
            Session::flash('alert-class', 'alert-success');


            $emailHeader = "
                <!-- Header Section -->
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langYourBookingHasAccepted</div>
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
                            <li><strong>$langUpdateSoon</strong></li>
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

                $emailsubject = $siteName.':'.$langYourBookingHasAccepted;

                $emailbody = $emailHeader.$emailMain;

                $emailPlainBody = html2text($emailbody);

                send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);

                redirect_to_home_page("main/profile/available_booking.php?user_id=".$uid);

        }


    }
}



if(isset($_POST['delete_book'])){

    $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT student_id FROM date_booking_user WHERE booking_id = ?d)",$_POST['booking_id'])->email;
    $details_booking = Database::get()->queryArray("SELECT * FROM date_booking WHERE id = ?d",$_POST['booking_id']);

    if(count($details_booking) > 0){
        foreach($details_booking as $d){
            $userName = $d->title;
            $tutorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$d->tutor_id)->givenname;
            $tutorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$d->tutor_id)->surname;
            $dateFrom = $d->start;
            $dateEnd = $d->end;
        }
    }

    Database::get()->query("DELETE FROM date_booking WHERE id = ?d",$_POST['booking_id']);
    Session::flash('message',$langBookingHasCalceled);
    Session::flash('alert-class', 'alert-success');


    $emailHeader = "
    <!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <div id='header-title'>$langYourBookingHasCanceled</div>
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

    $emailsubject = $siteName.':'.$langYourBookingHasCanceled;

    $emailbody = $emailHeader.$emailMain;

    $emailPlainBody = html2text($emailbody);

    send_mail_multipart('', '', '', $emailUser, $emailsubject, $emailPlainBody, $emailbody);

    redirect_to_home_page("main/profile/available_booking.php?user_id=".$uid);
}


if(isset($_POST['delete_history_book'])){
    Database::get()->query("DELETE FROM date_booking WHERE id = ?d",$_POST['booking_history_id']);
    Session::flash('message',$langBookingHasDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("main/profile/available_booking.php?user_id=".$uid);
}


$now = date('Y-m-d H:i:s', strtotime('now'));


//user student
$data['bookings'] = array();
$data['booking_history'] = array();
if(isset($_GET['myBooks'])){
    $data['bookings'] = Database::get()->queryArray("SELECT date_booking.id,date_booking.teacher_id,date_booking.title,date_booking.start,date_booking.end,date_booking.accepted,user.givenname,user.surname FROM date_booking
                                                        INNER JOIN user 
                                                        ON date_booking.teacher_id = user.id  
                                                        WHERE date_booking.id IN (SELECT booking_id FROM date_booking_user WHERE student_id = ?d)
                                                        AND (date_booking.start <= ?t AND date_booking.end >= ?t OR date_booking.start > ?t)
                                                        ORDER BY date_booking.start ASC",$uid,$now,$now,$now);
}else{//user teacher

        $data['bookings'] = Database::get()->queryArray("SELECT * FROM date_booking 
                                                            LEFT JOIN date_booking_user 
                                                            ON date_booking_user.booking_id = date_booking.id 
                                                            WHERE date_booking.teacher_id = ?d
                                                            AND (date_booking.start <= ?t AND date_booking.end >= ?t OR date_booking.start > ?t)
                                                            ORDER BY start ASC",$uid,$now,$now,$now);

        $data['booking_history'] = Database::get()->queryArray("SELECT * FROM date_booking 
                                                                LEFT JOIN date_booking_user 
                                                                ON date_booking_user.booking_id = date_booking.id 
                                                                WHERE date_booking.teacher_id = ?d
                                                                AND date_booking.end < ?t
                                                                ORDER BY start ASC",$uid,$now);
}



$data['action_bar'] =
                action_bar(array(
                    array('title' => $langBack,
                        'url' => $urlAppend . "main/profile/display_profile.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary')
                    ));

$data['menuTypeID'] = 1;
view('main.profile.available_booking', $data);




