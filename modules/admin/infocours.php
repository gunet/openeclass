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
 * @file infocours.php
 * @brief edit basic course information
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

load_js('jstree3');

// Update course basic information
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $departments = isset($_POST['department']) ? arrayValuesDirect($_POST['department']) : array();

    // if depadmin then diff new/old deps and if new or deleted deps are out of juristinction, then error
    if (isDepartmentAdmin()) {
        $olddeps = $course->getDepartmentIds($cId);

        foreach ($departments as $depId) {
            if (!in_array($depId, $olddeps)) {
                validateNode(intval($depId), true);
            }
        }

        foreach ($olddeps as $depId) {
            if (!in_array($depId, $departments)) {
                validateNode($depId, true);
            }
        }
    }

    // Update query
    Database::get()->query("UPDATE course SET title = ?s,
                    prof_names = ?s
                    WHERE code = ?s", $_POST['title'], $_POST['titulary'], $_GET['c']);
    $course->refresh($cId, $departments);
    
    Session::Messages($langModifDone, 'alert-success');
    redirect_to_home_page('modules/admin/infocours.php?c='.$_GET['c']);
}

$data['course'] = Database::get()->querySingle("SELECT course.code AS code, course.title AS title, course.prof_names AS prof_name, course.id AS id
                                        FROM course
                                       WHERE course.code = ?s" ,$_GET['c']);
$toolName = $langCourseInfo;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'editcours.php?c=' . q($_GET['c']), 'name' => $langCourseEdit);

if (isset($_GET['c'])) {
    $data['action_bar'] = action_bar(array(
     array('title' => $langBack,
           'url' => "editcours.php?c=".q($_GET['c']),
           'icon' => 'fa-reply',
           'level' => 'primary-label')));
} else {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
}

if (isDepartmentAdmin()) {
    list($js, $html) = $tree->buildCourseNodePickerIndirect(array('defaults' => $course->getDepartmentIds($data['course']->id), 'allowables' => $user->getDepartmentIds($uid)));
} else {
    list($js, $html) = $tree->buildCourseNodePickerIndirect(array('defaults' => $course->getDepartmentIds($data['course']->id)));
}
$head_content .= $js;
$data['node_picker'] = $html;
    
$data['menuTypeID'] = 3;
view ('admin.courses.infocours', $data);
