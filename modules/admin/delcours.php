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
    $course_id = intval(getDirectReference($_GET['c']));
} else {
    $course_id = 0;
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

$toolName = $langCourseDel;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

// If course deleted go back to listcours.php
if (isset($_GET['c']) && !isset($_GET['delete'])) {
    $tool_content .= action_bar(array(
     array('title' => $langBack,
           'url' => "listcours.php",
           'icon' => 'fa-reply',
           'level' => 'primary-label')));   
} else {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
}

// Delete course
if (isset($_GET['delete']) && $course_id) {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();
    if(showSecondFactorChallenge()!=""){
      $_POST['sfaanswer'] = $_GET['sfaanswer'];
      checkSecondFactorChallenge();
    }
    delete_course($course_id);
    $tool_content .= "<div class='alert alert-success'>" . $langCourseDelSuccess . "</div>";
}
// Display confirmatiom message for course deletion
else {
    if (!Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $course_id)) {
        $tool_content .= "<p class='right'><a href='index.php'>$langBack</a></p>";
        draw($tool_content, 3);
        exit();
    }
    $tool_content .= "<div class='alert alert-danger'>" . $langCourseDelConfirm2 . " <em>" . q(course_id_to_title($course_id)) . "</em>;
		<br><br><i>" . $langNoticeDel . "</i><br>
		</div>";
    if(showSecondFactorChallenge()!=""){
        $asktotp = " onclick=\"var totp=prompt('Type 2FA:','');this.setAttribute('href', this.getAttribute('href')+'&sfaanswer='+escape(totp));\" ";
    }
    $tool_content .= "<ul class='list-group'>
                        <li class='list-group-item'><a href='" . $_SERVER['SCRIPT_NAME'] . "?c=" . getIndirectReference($course_id) . "&amp;delete=yes&" .generate_csrf_token_link_parameter() . "' $asktotp><b>$langYes</b></a></li>
                        <li class='list-group-item'><a href='listcours.php'><b>$langNo</b></a></li>
                    </ul>";
}

draw($tool_content, 3);
