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
$require_current_course = true;
$require_user_registration = TRUE;
$require_help = true;
$helpTopic = 'available_dates';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';

initialize_group_id();

if (!is_group_visible($group_id, $course_id) and !$is_editor) {
    Session::flash('message',$langForbidden);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/group/index.php?course=$course_code");
}

initialize_group_info($group_id);

$toolName = $langAddAvailableDateForGroupAdmin;
$pageName = $langAddAvailableDateForGroupAdmin;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);
$navigation[] = array('url' => "group_space.php?course=$course_code&group_id=$group_id", 'name' => q($group_name));

$data['group_tutors'] = $group_tutors = $tutors;
$data['group_id'] = $group_id;
$data['is_tutor'] = $is_tutor;

$group_tutor_ids = array();
$data['tutor_name'] = '';
$data['surname_name'] = '';
$data['nextAvDate'] = '';
if(count($group_tutors) > 0){
    foreach($group_tutors as $t){
        array_push($group_tutor_ids,$t->user_id);
    }
    $data['tutor_name'] = database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$uid)->givenname;
    $data['surname_name'] = database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$uid)->surname;

    $nextAvDate = array();
    foreach($group_tutors as $tutor){
        $nextAvDate[] = getNextAvailableDateOfTutor($tutor->user_id, $group_id, $course_id);
    }
    $data['nextAvDate'] = $nextAvDate;
}
$data['group_tutor_ids'] = $group_tutor_ids;

if(isset($_GET['add_for_tutor'])){
    if($is_editor){
        $data['tutor_id'] = intval($_GET['add_for_tutor']);
        $data['lesson_id'] = $course_id;
        $data['TutorGivenname'] = $TutorGivenname = Database::get()->querySingle("SELECT `givenname` FROM user WHERE id = ?d",$data['tutor_id'])->givenname;
        $data['TutorSurname'] = $TutorSurname = Database::get()->querySingle("SELECT `surname` FROM user WHERE id = ?d",$data['tutor_id'])->surname;
        $toolName = $langAddAVailableDateWith." ".$TutorGivenname." ".$TutorSurname;
        $data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                'url' => "date_available.php?course=$course_code&group_id=$group_id",
                'icon' => 'fa-reply',
                'level' => 'primary'
            )));
        view('modules.group.add_date_available', $data);
    }else{
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    }
}elseif(isset($_GET['show_tutor'])){// user student
    if($is_member){
        $toolName = $langAvailableDateForGroupAdmin;
        $pageName = $langAvailableDateForGroupAdmin;
        $data['is_member'] = $is_member;
        $data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                'url' => "group_space.php?course=$course_code&group_id=$group_id",
                'icon' => 'fa-reply',
                'level' => 'primary'
            )));
        view('modules.group.show_tutor_available', $data);
    }else{
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    }
}elseif(isset($_GET['bookings_of_tutor'])){
    if($is_editor){
        $data['tutor_id'] = $tutor_id = intval($_GET['bookings_of_tutor']);
        $data['TutorGivenname'] = $TutorGivenname = Database::get()->querySingle("SELECT `givenname` FROM user WHERE id = ?d",$data['tutor_id'])->givenname;
        $data['TutorSurname'] = $TutorSurname = Database::get()->querySingle("SELECT `surname` FROM user WHERE id = ?d",$data['tutor_id'])->surname;
        $toolName = $langAVailableBookingsForTutor." ".$TutorGivenname." ".$TutorSurname;
        $data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                'url' => "date_available.php?course=$course_code&group_id=$group_id",
                'icon' => 'fa-reply',
                'level' => 'primary'
            )));

        if(isset($_POST['accept_booking'])){

            $accept_booking = Database::get()->query("UPDATE booking SET accepted = ?d WHERE id = ?d",1,$_POST['accept_booking_id']);
            if($accept_booking){
                $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT simple_user_id FROM booking_user WHERE booking_id = ?d)",$_POST['accept_booking_id'])->email;
                $details_booking = Database::get()->queryArray("SELECT * FROM booking WHERE id = ?d",$_POST['accept_booking_id']);

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

                        redirect_to_home_page("modules/group/date_available.php?course=".$course_code."&group_id=".$group_id."&bookings_of_tutor=".$tutor_id);

                }


            }
        }



        if(isset($_POST['delete_booking'])){

            $emailUser = Database::get()->querySingle("SELECT email FROM user WHERE id IN (SELECT simple_user_id FROM booking_user WHERE booking_id = ?d)",$_POST['booking_id'])->email;
            $details_booking = Database::get()->queryArray("SELECT * FROM booking WHERE id = ?d",$_POST['booking_id']);

            if(count($details_booking) > 0){
                foreach($details_booking as $d){
                    $userName = $d->title;
                    $tutorName = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$d->tutor_id)->givenname;
                    $tutorSurname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$d->tutor_id)->surname;
                    $dateFrom = $d->start;
                    $dateEnd = $d->end;
                }
            }

            Database::get()->query("DELETE FROM booking WHERE id = ?d",$_POST['booking_id']);
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

            redirect_to_home_page("modules/group/date_available.php?course=".$course_code."&group_id=".$group_id."&bookings_of_tutor=".$tutor_id);
        }


        $now = date('Y-m-d H:i:s', strtotime('now'));

        $data['bookings'] = Database::get()->queryArray("SELECT * FROM booking 
                                                            LEFT JOIN booking_user 
                                                            ON booking_user.booking_id = booking.id 
                                                            WHERE booking.lesson_id = ?d 
                                                            AND booking.group_id = ?d
                                                            AND booking.tutor_id = ?d
                                                            AND (booking.start <= ?t AND booking.end >= ?t OR booking.start > ?t)
                                                            ORDER BY start ASC",$course_id,$group_id,$tutor_id,$now,$now,$now);

        $data['booking_history'] = Database::get()->queryArray("SELECT * FROM booking 
                                                                    LEFT JOIN booking_user 
                                                                    ON booking_user.booking_id = booking.id 
                                                                    WHERE booking.lesson_id = ?d 
                                                                    AND booking.group_id = ?d
                                                                    AND booking.tutor_id = ?d
                                                                    AND booking.end < ?t
                                                                    ORDER BY start ASC",$course_id,$group_id,$tutor_id,$now);



        view('modules.group.tutor_bookings', $data);
    }else{
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    }
}elseif(isset($_GET['booking_with_tutor'])){// user student
    if($is_member){
        $now = date('Y-m-d H:i:s', strtotime('now'));
        $data['is_member'] = $is_member;
        $data['tutor_id'] = $tutor_id = intval($_GET['booking_with_tutor']);
        $data['TutorGivenname'] = $TutorGivenname = Database::get()->querySingle("SELECT `givenname` FROM user WHERE id = ?d",$data['tutor_id'])->givenname;
        $data['TutorSurname'] = $TutorSurname = Database::get()->querySingle("SELECT `surname` FROM user WHERE id = ?d",$data['tutor_id'])->surname;
        $toolName = $langMYBookings;
        $data['bookings_user'] = Database::get()->queryArray("SELECT * FROM booking WHERE
                                id IN (SELECT booking_id FROM booking_user WHERE simple_user_id = ?d)
                                AND lesson_id = ?d
                                AND group_id = ?d
                                AND tutor_id = ?d
                                AND (start <= ?t AND end >= ?t OR start > ?t)
                                ORDER BY start ASC",$uid,$course_id,$group_id,$tutor_id,$now,$now,$now);
        $data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                'url' => "date_available.php?course=$course_code&group_id=$group_id&show_tutor=1",
                'icon' => 'fa-reply',
                'level' => 'primary'
            )));
        view('modules.group.show_user_bookings', $data);
    }else{
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    }
}elseif(isset($_GET['history_booking'])){
    if($is_editor){
        $data['tutor'] = $tutor = intval($_GET['history_booking']);
        if(isset($_POST['delete_history_booking_id'])){
            $del = Database::get()->query("DELETE FROM booking WHERE id = ?d",$_POST['del_booking_id']);
            if($del){
                Session::flash('message',$langDelHistoryBook);
                Session::flash('alert-class', 'alert-success');
            }
            redirect_to_home_page("modules/group/date_available.php?course=".$course_code."&group_id=".$group_id."&history_booking=".$tutor);
        }

        if($is_tutor or $is_course_admin){
            $data['booking_history'] = Database::get()->queryArray("SELECT booking.id,booking.tutor_id,booking.title,booking.start,booking.end,booking.accepted,user.givenname,user.surname FROM booking 
                                                                    INNER JOIN user 
                                                                    ON booking.tutor_id = user.id 
                                                                    WHERE booking.end < NOW() 
                                                                    AND booking.group_id = ?d 
                                                                    AND booking.tutor_id = ?d",$group_id, $tutor);
            $data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                    'url' => "date_available.php?course=$course_code&group_id=$group_id",
                    'icon' => 'fa-reply',
                    'level' => 'primary'
                )));
        }
        view('modules.group.show_history_bookings', $data);
    }else{
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    }
}else{
    if($is_course_admin or $is_tutor){
        if($is_editor){
            $data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                    'url' => "group_space.php?course=$course_code&group_id=$group_id",
                    'icon' => 'fa-reply',
                    'level' => 'primary'
                )));
            view('modules.group.show_date_available', $data);
        }else{
            Session::flash('message',$langForbidden);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/group/index.php?course=$course_code");
        }
    }else{
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    }

}




