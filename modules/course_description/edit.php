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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Coursedescription';
$require_login = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/log.php';

$toolName = $langCourseDescription;
$pageName = $langEditCourseProgram;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langCourseProgram);

$head_content .= "
<script type='text/javascript'>
    $(document).on('change', '#typSel', function (e) {
        $('#titleSel').val( $(this).children(':selected').text() );
    });
</script>";

if (isset($_REQUEST['id'])) {
    $data['editId'] = getDirectReference($_REQUEST['id']);
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
$data['action_bar'] = action_bar(array(
            array('title' => trans('langBack'),
                  'url' => "index.php?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label')));

$data['text_area_comments'] = rich_text_editor('editComments', 4, 20, $comments);

$data['form_buttons'] = form_buttons(array(
            array(
                'text'  =>  $langSave,
                'name'  =>  'saveCourseDescription',
                'value' =>  $langAdd
            ),
            array(
                'href'  =>  "index.php?course=$course_code"
            )
        ));
view('modules.course.description.create', $data);