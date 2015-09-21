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
require_once 'include/log.php';
require_once 'archive_functions.php';

$toolName = $langCourseInfo;
$pageName = $langDelCourse;

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langCourseInfo);
if (isset($_POST['delete'])) {
    $tool_content .= action_bar(array(
        array('title' => "$langBackHome $siteName",
            'url' => '../../index.php',
            'icon' => 'fa-reply',
            'level' => 'primary-label')));
    
    // first archive course
    doArchive($course_id, $course_code);    
    
    $garbage = "$webDir/courses/garbage";
    if (!is_dir($garbage)) {
        mkdir($garbage, 0775);
    }
    rename("$webDir/courses/archive/$course_code", "$garbage/$course_code");
     
    delete_course($course_id);
    
    // logging
    Log::record(0, 0, LOG_DELETE_COURSE, array('id' => $course_id,
                                               'code' => $course_code,
                                               'title' => $currentCourseName));
    Session::Messages("$langTheCourse <b>" . q($currentCourseName) . " ($course_code)</b> $langHasDel", 'alert-info');
    unset($_SESSION['dbname']);
    redirect_to_home_page('main/portfolio.php');
} else {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "index.php?course=" . q($course_code),
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    
    $tool_content .= "<div class='alert alert-danger'>
            $langByDel_A <b>" . q($currentCourseName) . " ($course_code) ;</b></div>
    <div class='form-wrapper'>
    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
    <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-5'>
            <input class='btn btn-primary' type='submit' name='delete' value='$langDelete'>
        </div>
    </div>
    <span class='help-block'><small>$langByDel</small></span></form></div>";
}
draw($tool_content, 2);
