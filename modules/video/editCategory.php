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

// setup
$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'Video';

// dependencies
require_once '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'video_functions.php';

$action = new action();
$action->record('MODULE_ID_VIDEO');
$data = array();

// navigation
$toolName = $langVideo;
if (isset($_GET['id'])) {
    $pageName = $langCategoryMod;
} else {
    $pageName = $langCategoryAdd;
}
$backPath = $data['backPath'] = $urlAppend . "modules/video/index.php?course=" . $course_code;
$navigation[] = array('url' => $backPath, 'name' => $langVideo);

// load requested category for editing
if (isset($_GET['id'])) {
    $data['currentcat'] = Database::get()->querySingle("SELECT * FROM video_category WHERE id = ?d AND course_id = ?d", $_GET['id'], $course_id);
}

// handle submitted data
if (isset($_POST['submitCategory'])) {
    submit_video_category($course_id, $course_code);
    Session::flash('message',$langCatVideoDirectoryCreated);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/video/index.php?course=" . $course_code);
}

view('modules.video.editcategory', $data);
