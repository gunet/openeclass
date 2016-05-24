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
 * @file delcours.php
 * @brief delete course
 */


$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';

if (isset($_GET['c'])) {
   $data['course_id'] = $course_id = intval(getDirectReference($_GET['c']));
} else {
    $data['course_id'] = $course_id = 0;
}

require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

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
    delete_course($course_id);
    // Display confirmatiom message for course deletion
    Session::Messages($langCourseDelSuccess, "alert-success");
    redirect_to_home_page('modules/admin/listcours.php');
}

$toolName = $langCourseDel;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

// If course deleted go back to listcours.php
if (isset($_GET['c']) && !isset($_GET['delete'])) {
    $data['action_bar'] = action_bar([
                    [
                        'title' => $langBack,
                        'url' => "listcours.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label'
                    ]
                ]);   
} else {
    $data['action_bar'] = action_bar([
                    [
                        'title' => $langBack,
                        'url' => "index.php",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label'
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

$data['menuTypeID'] = 3;
view ('admin.courses.delcours', $data);



