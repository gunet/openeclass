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
 * @file delcours.php
 * @brief delete course
 */


$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';

if (isset($_GET['c'])) {
   $data['course_id'] = $course_id = intval($_GET['c']);
} else {
    $data['course_id'] = $course_id = 0;
}

require_once 'include/log.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';
require_once 'modules/course_info/archive_functions.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

// validate course Id
validateCourseNodes($course_id, isDepartmentAdmin());

// Delete course
if (isset($_GET['delete']) && $course_id) {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    if(showSecondFactorChallenge() != ""){
      $_POST['sfaanswer'] = $_GET['sfaanswer'];
      checkSecondFactorChallenge();
    }

    $course_code = course_id_to_code($course_id);
    $course_title = course_id_to_title($course_id);
    // first archive course
    $zipfile = doArchive($course_id, $course_code);

    $garbage = "$webDir/courses/garbage";
    $target = "$garbage/$course_code.$_SESSION[csrf_token]";
    is_dir($target) or make_dir($target);
    touch("$garbage/index.html");
    rename($zipfile, "$target/$course_code.zip");
    // delete course
    delete_course($course_id);
    // logging
    Log::record(0, 0, LOG_DELETE_COURSE, array('id' => $course_id,
        'code' => $course_code,
        'title' => $course_title));

    // Display confirmation message for course deletion
    Session::flash('message',$langCourseDelSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/listcours.php');
}

$toolName = $langAdmin;
$pageName = $langCourseDel;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

// If course deleted go back to listcours.php
if (isset($_GET['c']) && !isset($_GET['delete'])) {
    $data['action_bar'] = action_bar([
                    [
                        'title' => $langBack,
                        'url' => "listcours.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary'
                    ]
                ]);
} else {
    $data['action_bar'] = action_bar([
                    [
                        'title' => $langBack,
                        'url' => "index.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary'
                    ]
                ]);
}

if (!Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $course_id)) {
    redirect_to_home_page('modules/admin/index.php');
}
$data['asktotp'] = "";
if (showSecondFactorChallenge() != "") {
    $data['asktotp'] = " onclick=\"var totp=prompt('Type 2FA:','');this.setAttribute('href', this.getAttribute('href')+'&sfaanswer='+escape(totp));\" ";
}

view ('admin.courses.delcours', $data);
