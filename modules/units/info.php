<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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

$data['start_week'] = $data['finish_week'] = '';
if (isset($_GET['edit'])) {
    $pageName = $langEditUnit;
    $id = $_GET['edit'];
    $cu = Database::get()->querySingle("SELECT id, title, comments, 
                                        start_week, finish_week
                                    FROM course_units WHERE id = ?d AND course_id = ?d",
                                        $id, $course_id);
    if (!$cu) {
        Session::Messages($langUnknownResType);
        redirect_to_home_page("courses/$course_code/");
    }
    $unitDescr = $cu->comments;
    if (!(($cu->start_week == '0000-00-00') or (is_null($cu->start_week)))) {
        $data['start_week'] = DateTime::createFromFormat('Y-m-d', $cu->start_week)->format('d-m-Y');
    }
    if (!(($cu->finish_week == '0000-00-00') or (is_null($cu->finish_week)))) {        
        $data['finish_week'] = DateTime::createFromFormat('Y-m-d', $cu->finish_week)->format('d-m-Y');
    }
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
$data['tagInput'] = $data['unitId']? eClassTag::tagInput($data['unitId']): eClassTag::tagInput();

view('modules.units.info', $data);

