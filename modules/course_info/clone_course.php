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
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $currentCourseCode = $course_code;
    $message_restore_users = '';
    $restoreThis = $webDir . '/courses/tmpUnzipping/' .
        $uid . '/' . safe_filename();
    make_dir($restoreThis);
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

    $new_course_code = create_restored_course($restoreThis, $course_code, $course_lang, $course_title, $course_desc, $course_vis, $course_prof);    
    $course_code = $currentCourseCode; // revert course code to the correct value
    $data['coursedir'] = "${webDir}/courses/$new_course_code";
    $data['restore_users'] = $message_restore_users;
    
    $backUrl = $urlAppend . (isset($currentCourseCode)? "courses/$currentCourseCode/": 'modules/admin/');
    $data['new_action_bar'] = action_bar(array(
        array('title' => $langEnter,
              'url' => $urlAppend . "courses/$new_course_code/",
              'icon' => 'fa-arrow-right',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        array('title' => $langBack,
              'url' => $backUrl,
              'icon' => 'fa-reply',
              'level' => 'primary-label')), false);    
} else {
    $desc = Database::get()->querySingle("SELECT description FROM course WHERE id = ?d", $course_id)->description;
    $old_faculty = array();
    Database::get()->queryFunc("SELECT department FROM course_department WHERE course = ?d",
        function ($dep) use ($treeObj, &$old_faculty) {
            $old_faculty[] = array('name' => $treeObj->getFullPath($dep->department));
        }, $course_id);

    $data['action_bar'] = action_bar(array(
                                array('title' => $langBack,
                                      'url' => "index.php?course=$course_code",
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label')));
    
    list($tree_js, $tree_html) = $treeObj->buildCourseNodePickerIndirect();    
    $head_content = $tree_js;
    $data['course_node_picker'] = $tree_html;
        
    if (is_array($old_faculty)) {
        foreach ($old_faculty as $entry) {
            $old_faculty_names[] = q(Hierarchy::unserializeLangField($entry['name']));
        }
        $old_faculty = implode('<br>', $old_faculty_names);
    } else {
        $old_faculty = q(Hierarchy::unserializeLangField($faculty));
    }
    
    $data['formAction'] = $_SERVER['SCRIPT_NAME'];
    if (isset($GLOBALS['course_code'])) {
        $data['formAction'] .= '?course=' . $GLOBALS['course_code'];
    }
    $data['old_faculty'] = $old_faculty;
    $data['code'] = q($public_code);
    $data['title'] = q($currentCourseName);
    $data['prof'] = q($titulaires);
    
    $data['lang_selection'] = lang_select_options('course_lang');
    $data['rich_text_editor'] = rich_text_editor('course_desc', 10, 40, purify($desc));    
    $data['visibility_select'] = visibility_select($visible);        
}

$data['menuTypeID'] = 2;
view('modules.course_info.clone_course', $data);