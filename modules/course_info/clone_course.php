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

$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'archive_functions.php';
require_once 'restore_functions.php';

load_js('jstree3');
$treeObj = new Hierarchy();
$allow_clone = false;
$allowables = null;

$up = new Permissions();
// $atleastone is set to true by init when a department admin can admin this course
if ($is_power_user or $is_admin) {
    $allow_clone = true;
} elseif ($is_departmentmanage_user and $atleastone) {
    $allow_clone = true;
    if (get_config('restrict_teacher_owndep')) {
        // Department admin can create course only in own departments
        $user = new User();
        $userdeps = $user->getDepartmentIds($uid);
        $subs = $treeObj->buildSubtreesFull($userdeps);
        $allowables = [];
        foreach ($subs as $node) {
            if (intval($node->allow_course) === 1) {
                $allowables[] = $node->id;
            }
        }
    }
} elseif ($up->has_course_clone_permission()) {
    $allow_clone = true;
}

// access control
if (!$allow_clone) {
    header("Location:" . $urlServer . "index.php");
    exit();
}

$toolName = $langCloneCourse;
$_POST['restoreThis'] = null; // satisfy course_details_form()
$_POST['create_users'] = null; // no need to try recreating accounts while cloning

if (isset($_POST['create_restored_course'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $currentCourseCode = $course_code;

    $restoreThis = $webDir . '/courses/tmpUnzipping/' .
        $uid . '/' . safe_filename();
    make_dir($restoreThis);
    archiveTables($course_id, $restoreThis);
    recurse_copy($webDir . '/courses/' . $course_code,
        $restoreThis . '/html');

    register_posted_variables(array(
        'course_code' => true,
        'course_lang' => true,
        'course_title' => true,
        'course_desc' => true,
        'course_vis' => true,
        'course_prof' => true), 'all');

    create_restored_course($tool_content, $restoreThis, $course_code, $course_lang, $course_title, $course_desc, $course_vis, $course_prof, true);
    $course_code = $currentCourseCode; // revert course code to the correct value
} else {
    $desc = Database::get()->querySingle("SELECT description FROM course WHERE id = ?d", $course_id)->description;
    $old_deps = array();
    Database::get()->queryFunc("SELECT department FROM course_department WHERE course = ?d",
        function ($dep) use ($treeObj, &$old_deps) {
            $old_deps[] = array('name' => $treeObj->getFullPath($dep->department));
        }, $course_id);

    $tool_content = course_details_form($public_code, $currentCourseName, $course_prof_names, $currentCourseLanguage, null, $visible, $desc, $old_deps, $allowables);
}

draw($tool_content, 2, null, $head_content);
