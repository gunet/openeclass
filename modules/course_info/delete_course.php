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

$require_current_course = TRUE;
$require_course_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'archive_functions.php';

$toolName = $langCourseInfo;
$pageName = $langDelCourse;

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langCourseInfo);
if (isset($_POST['delete'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $data['action_bar'] = action_bar(array(
        array('title' => "$langBackHome $siteName",
            'url' => '../../index.php',
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    // first archive course
    $zipfile = doArchive($course_id, $course_code);

    $garbage = "$webDir/courses/garbage";
    $target = "$garbage/$course_code.$_SESSION[csrf_token]";
    is_dir($target) or make_dir($target);
    touch("$garbage/index.html");
    rename($zipfile, "$target/$course_code.zip");

    delete_course($course_id);

    // logging
    Log::record(0, 0, LOG_DELETE_COURSE, array('id' => $course_id,
                                               'code' => $course_code,
                                               'title' => $currentCourseName));
    //Session::Messages("$langTheCourse <b>" . q($currentCourseName) . " ($course_code)</b> $langHasDel", 'alert-info');
    Session::flash('message',"$langTheCourse <b>" . q($currentCourseName) . " ($course_code)</b> $langHasDel");
    Session::flash('alert-class', 'alert-info');
    unset($_SESSION['dbname']);
    redirect_to_home_page('main/portfolio.php');
} else {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => "index.php?course=" . q($course_code),
              'icon' => 'fa-reply',
              'level' => 'primary')));
    
    $data['form_url'] = "$_SERVER[SCRIPT_NAME]?course=$course_code";
}

$data['menuTypeID'] = 2;
view('modules.course_info.delete_course', $data);
