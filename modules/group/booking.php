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
require_once 'include/log.class.php';
require_once 'modules/group/group_functions.php';

initialize_group_id();

if (!is_group_visible($group_id, $course_id)) {
    Session::flash('message',$langForbidden);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/group/index.php?course=$course_code");
}

initialize_group_info($group_id);

if(!$is_member){
    Session::flash('message',$langForbidden);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/group/index.php?course=$course_code");
}

$gr_tutor_ids = array();
if(count($tutors) > 0){
    foreach($tutors as $t){
        array_push($gr_tutor_ids,$t->user_id);
    }
    if(isset($_GET['tutor_id']) && !in_array($_GET['tutor_id'],$gr_tutor_ids)){
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/group/index.php?course=$course_code");
    }
}



$pageName = $langAddBooking;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);
$navigation[] = array('url' => "group_space.php?course=$course_code&group_id=$group_id", 'name' => q($group_name));

$data['group_id'] = $group_id;
$data['tutor_id_for_booking'] = '';
if(isset($_GET['tutor_id'])){
    $data['tutor_id_for_booking'] = intval($_GET['tutor_id']);
    $booking_to_the_username = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$data['tutor_id_for_booking'])->givenname;
    $booking_to_the_surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$data['tutor_id_for_booking'])->surname;
    $toolName = $langAddBooking."(".$booking_to_the_username." ".$booking_to_the_surname.")";
}

$data['booking_by_username'] = $booking_by_username = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$uid)->givenname;
$data['booking_by_surname'] = $booking_by_surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$uid)->surname;

$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "date_available.php?course=$course_code&group_id=$group_id&show_tutor=1",
        'icon' => 'fa-reply',
        'level' => 'primary'
    )));

view('modules.group.booking', $data);





