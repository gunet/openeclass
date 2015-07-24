<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

$require_current_course = true;
$require_course_admin = true;
require_once '../../include/baseTheme.php';

// access control
if (!get_config('allow_teacher_clone_course') && !$is_admin) {
    header("Location:" . $urlServer . "index.php");
    exit();
}

require_once 'include/lib/hierarchy.class.php';
require_once 'archive_functions.php';
require_once 'restore_functions.php';

$toolName = $langCloneCourse;
$treeObj = new Hierarchy();
$_POST['restoreThis'] = null; // satisfy course_details_form()

if (isset($_POST['create_restored_course'])) {
    $currentCourseCode = $course_code;

    $restoreThis = $webDir . '/courses/tmpUnzipping/' .
        $uid . '/' . safe_filename();
    mkdir($restoreThis, 0755, true);
    archiveTables($course_id, $course_code, $restoreThis);
    recurse_copy($webDir . '/courses/' . $course_code,
        $restoreThis . '/html');

    register_posted_variables(array(
        'course_code' => true,
        'course_lang' => true,
        'course_title' => true,
        'course_desc' => true,
        'course_vis' => true,
        'course_prof' => true), 'all');
    create_restored_course($tool_content, $restoreThis, $course_code, $course_lang, $course_title, $course_desc, $course_vis, $course_prof);
    $course_code = $currentCourseCode; // revert course code to the correct value
} else {
    $desc = Database::get()->querySingle("SELECT description FROM course WHERE id = ?d", $course_id)->description;
    $old_deps = array();
    Database::get()->queryFunc("SELECT department FROM course_department WHERE course = ?d",
        function ($dep) use ($treeObj, &$old_deps) {
            $old_deps[] = array('name' => $treeObj->getFullPath($dep->department));
        }, $course_id);

    $tool_content = course_details_form($public_code, $currentCourseName, $titulaires, $currentCourseLanguage, null, $visible, $desc, $old_deps);
}

load_js('jstree3d');
list($js, $html) = $treeObj->buildCourseNodePicker();
$head_content .= $js;
draw($tool_content, 2, null, $head_content);
