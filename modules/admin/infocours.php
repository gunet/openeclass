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

    $departments = isset($_POST['department']) ? $_POST['department'] : array();

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
    Session::flash('message',$langModifDone);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/editcours.php?c='.$_GET['c']);
}

$data['course'] = Database::get()->querySingle("SELECT course.code AS code, course.title AS title, course.prof_names AS prof_name, course.id AS id, course.is_collaborative AS is_collaborative
                                        FROM course
                                       WHERE course.code = ?s" ,$_GET['c']);
$toolName = $langAdmin;
$pageName = $langCourseInfo;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'editcours.php?c=' . q($_GET['c']), 'name' => $langCourseEdit);

if (isset($_GET['c'])) {
    $data['action_bar'] = action_bar(array(
     array('title' => $langBack,
           'url' => "editcours.php?c=".q($_GET['c']),
           'icon' => 'fa-reply',
           'level' => 'primary')));
} else {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary')));
}

if (isDepartmentAdmin()) {
    list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($data['course']->id), 'allowables' => $user->getDepartmentIds($uid)));
} else {
    list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($data['course']->id)));
}
$head_content .= $js;
$data['node_picker'] = $html;

view ('admin.courses.infocours', $data);
