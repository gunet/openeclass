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
 * @file editcours.php
 * @brief modify course details
 */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$data['tree'] = new Hierarchy();
$course = new Course();
$user = new User();

if (isset($_GET['c'])) {
    $data['c'] = $c = q($_GET['c']);
    $_SESSION['c_temp'] = $c;
}

if (!isset($c)) {
    $data['c'] = $c = $_SESSION['c_temp'];
}

// validate course Id
$data['cId'] = $cId = course_code_to_id($c);
validateCourseNodes($cId, isDepartmentAdmin());

$toolName = $langCourseEdit;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);

$data['action_bar'] = action_bar([
                [
                    'title' => $langBack,
                    'url' => "searchcours.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label'
                ]
            ]);

// A course has been selected
if (isset($c)) {    
    // Get information about selected course
    $data['course'] = Database::get()->querySingle("SELECT code, title, prof_names, visible, doc_quota, video_quota, group_quota, dropbox_quota
			FROM course WHERE code = ?s", $c);
    $data['departments'] = $course->getDepartmentIds($cId);
}

$data['menuTypeID'] = 3;
view ('admin.courses.editcours', $data);
