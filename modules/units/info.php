<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
 *  @file info.php
 *  @brief edit or create course unit
 */

$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'AddCourseUnits';
require_once '../../include/baseTheme.php';
require_once 'modules/tags/moduleElement.class.php';

load_js('tools.js');
load_js('select2');

if (isset($_GET['edit'])) {
    $pageName = $langEditUnit;
    $id = $_GET['edit'];
    $cu = Database::get()->querySingle("SELECT id, title, comments
        FROM course_units WHERE id = ?d AND course_id = ?d",
        $id, $course_id);
    if (!$cu) {
        Session::Messages($langUnknownResType);
        redirect_to_home_page("courses/$course_code/");
    }
    $unitDescr = $cu->comments;
    $data['unitTitle'] = $cu->title;
    $data['unitId'] = $cu->id;
} else {
    $pageName = $langAddUnit;
    $unitDescr = '';
    $data['unitTitle'] = $data['unitId'] = null;
}

$data['postUrl'] = "index.php?course=$course_code" .
    ($data['unitId'] ? "&id=$data[unitId]": '');
$data['descriptionEditor'] = rich_text_editor('unitdescr', 10, 20, $unitDescr);
$data['tagInput'] = $data['unitId']? Tag::tagInput($data['unitId']): Tag::tagInput();

view('modules.units.info', $data);

