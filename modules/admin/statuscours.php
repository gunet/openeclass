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
 * @file statuscours.php
 * @brief Edit course status
 */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';

if (!isset($_GET['c'])) {
    die();
}

require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

// validate course Id
$cId = course_code_to_id($_GET['c']);
validateCourseNodes($cId, isDepartmentAdmin());

$data['course'] = Database::get()->querySingle("SELECT * FROM course WHERE code = ?s", $_GET['c']);
// Update course status
if (isset($_POST['submit'])) {
    // Update query
    $sql = Database::get()->query("UPDATE course SET visible=?d WHERE code=?s", $_POST['formvisible'], $_GET['c']);
    // Some changes occured
    if ($sql->affectedRows > 0) {
        Session::flash('message',$langCourseStatusChangedSuccess);
        Session::flash('alert-class', 'alert-success');
    }
    // Nothing updated
    else {
        Session::flash('message',$langNoChangeHappened);
        Session::flash('alert-class', 'alert-warning');
    }
    redirect_to_home_page('modules/admin/statuscours.php?c='.$data['course']->code);
}

$toolName = $langCourseStatus;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourse);
$navigation[] = array('url' => 'editcours.php?c=' . q($_GET['c']), 'name' => $langCourseEdit);

if (isset($_GET['c'])) {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => "editcours.php?c=$_GET[c]",
              'icon' => 'fa-reply',
              'level' => 'primary')));
} else {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary')));
}

view('admin.courses.statuscours', $data);

