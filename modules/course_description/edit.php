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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'course_description';
$require_login = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

$toolName = $langSyllabus;
$pageName = $langEditCourseProgram;
$navigation[] = array('url' => "../course_info/index.php?course=$course_code", 'name' => $langCourseInfo);
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSyllabus);

if (isset($_REQUEST['id'])) {
    $data['editId'] = intval(getDirectReference($_REQUEST['id']));
    $course_desc = Database::get()->querySingle("SELECT title, comments, type FROM course_description WHERE course_id = ?d AND id = ?d", $course_id, $data['editId']);
    $data['cdtitle'] = Session::has('editTitle') ? Session::get('editTitle') : $course_desc->title;
    $comments = Session::has('editComments') ? Session::get('editComments') : $course_desc->comments;
    $data['defaultType'] = Session::has('editType') ? Session::get('editType') : $course_desc->type;
} else {
    $data['editId'] = false;
    $data['cdtitle'] = Session::has('editTitle') ? Session::get('editTitle') : "";
    $comments = Session::has('editComments') ? Session::get('editComments') : "";
    $data['defaultType'] = Session::has('editType') ? Session::get('editType') : "";
}

$types = Database::get()->queryArray("SELECT id, title FROM course_description_type ORDER BY `order`");
$data['types'] = array();
$data['types'][''] = '';
foreach ($types as $type) {
    $title = $titles = @unserialize($type->title);
    if ($titles !== false) {
        if (isset($titles[$language]) && !empty($titles[$language])) {
            $title = $titles[$language];
        } else if (isset($titles['en']) && !empty($titles['en'])) {
            $title = $titles['en'];
        } else {
            $title = array_shift($titles);
        }
    }
    $data['types'][$type->id] = $title;
}
$data['titleError'] = Session::getError('editTitle') ? " has-error" : "";

$data['text_area_comments'] = rich_text_editor('editComments', 4, 20, $comments);

$data['form_buttons'] = form_buttons(array(
            array(
                'class' => 'submitAdminBtn',
                'text'  =>  $langSave,
                'name'  =>  'saveCourseDescription',
                'value' =>  $langAdd
            ),
            array(
                'class' => 'cancelAdminBtn ms-1',
                'href'  =>  "index.php?course=$course_code"
            )
        ));
view('modules.course.description.edit', $data);
