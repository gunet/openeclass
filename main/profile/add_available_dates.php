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
$helpTopic = 'date_user';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

$navigation[] = array('url' => 'display_profile.php', 'name' => $langMyProfile);

//For user with status USER_STUDENT in order to do a reservation with a USER_TEACHER in the calendar events
if(isset($_GET['do_booking'])){
    //Get all users with status -USER_TEACHER- and display them into a list for a simple user.
    //This user can choose the user_teacher for making a reservation with him.

    $data['showUsers'] = '';
    if(isset($_GET['show_all_users'])){
        //This code refers to all available user teachers for a reservation by a simple user.
        load_js('datatables');
        load_js('datatables_bootstrap');
        $toolName = $langDisplayAvailableUsersForBooking;
        $pageName = $toolName;
        $data['user_teachers'] = $user_teachers = Database::get()->queryArray("SELECT DISTINCT user.id,user.givenname,user.surname,user.email FROM user 
                                                                                LEFT JOIN date_availability_user
                                                                                ON user.id = date_availability_user.user_id
                                                                                WHERE user.status = ?d
                                                                                AND date_availability_user.end > NOW()",USER_TEACHER);
        $data['showUsers'] = true;

        $data['action_bar'] =
                action_bar(array(
                    array('title' => $langBack,
                        'url' => $urlAppend . "main/profile/display_profile.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary')
                    ));
    }
    if(isset($_GET['bookWith'])){
        //This code refers to the reservation of user.
        $data['tutor_id'] = $tutor_id = intval($_GET['uBook']);
        $booking_with_username = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$tutor_id)->givenname;
        $booking_with_surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$tutor_id)->surname;
        $toolName = $booking_with_username."&nbsp;".$booking_with_surname;
        $pageName = $toolName;
        $data['booking_by_username'] = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$uid)->givenname;
        $data['booking_by_surname'] = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$uid)->surname;
        $data['showUsers'] = false;

        $data['action_bar'] =
        action_bar(array(
            array('title' => $langBack,
                'url' => $urlAppend . "main/profile/display_profile.php?id=$_GET[uBook]&token=$_GET[token]",
                'icon' => 'fa-reply',
                'level' => 'primary')
            ));

    }

    $data['menuTypeID'] = 1;
    view('main.profile.do_booking', $data);
}else{//For USER_TEACHER in order to add his available date in the calendar events
    $toolName = $langAvailableDateForUser;
    $pageName = $toolName;
    $data['action_bar'] =
                action_bar(array(
                    array('title' => $langBack,
                        'url' => $urlAppend . "main/profile/display_profile.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary')
                    ));
    $data['menuTypeID'] = 1;
    view('main.profile.add_available_dates', $data);
}



