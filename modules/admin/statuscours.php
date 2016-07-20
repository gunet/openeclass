<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
        Session::Messages($langCourseStatusChangedSuccess, 'alert-success');
    }
    // Nothing updated
    else {
        Session::Messages($langNoChangeHappened, 'alert-warning');
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
              'level' => 'primary-label')));
} else {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));           
}

$data['menuTypeID'] = 3;
view('admin.courses.statuscours', $data);

