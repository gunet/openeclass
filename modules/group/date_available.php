<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

if (!is_group_visible($group_id, $course_id) and !$is_editor) {
    Session::flash('message',$langForbidden);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/group/index.php?course=$course_code");
}

initialize_group_info($group_id);

if(!is_group_visible($group_id, $course_id) and !$is_editor){
    Session::flash('message',$langForbidden);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page("modules/group/index.php?course=$course_code");
}

$toolName = $langAddAvailableDateForGroupAdmin;
$pageName = $langAddAvailableDateForGroupAdmin;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroups);
$navigation[] = array('url' => "group_space.php?course=$course_code&group_id=$group_id", 'name' => q($group_name));

$data['group_tutors'] = $group_tutors = group_tutors($group_id);
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
}elseif(isset($_GET['show_tutor'])){
    $toolName = $langAvailableDateForGroupAdmin;
    $pageName = $langAvailableDateForGroupAdmin;
    $data['is_member'] = $is_member;
    view('modules.group.show_tutor_available', $data);
}else{
    view('modules.group.show_date_available', $data);
}




