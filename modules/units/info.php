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
 *  @file info.php
 *  @brief edit course unit
 */

$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'AddCourseUnits';
require_once '../../include/baseTheme.php';
require_once 'modules/tags/moduleElement.class.php';
$pageName = $langEditUnit;

load_js('tools.js');
load_js('select2');

if (isset($_GET['edit'])) { // display form for editing course unit
    $id = $_GET['edit'];
    $cu = Database::get()->querySingle("SELECT id, title, comments FROM course_units WHERE id = ?d AND course_id = ?d",$id, $course_id);   
    if (!$cu) {
        $pageName = $langUnitUnknown;
        $tool_content .= "<div class='alert alert-danger'>$langUnknownResType</div>";
        draw($tool_content, 2, null, $head_content);
        exit;
    } 
    $unittitle = " value='" . htmlspecialchars($cu->title, ENT_QUOTES) . "'";
    $tagsInput = Tag::tagInput($id);
    $unitdescr = $cu->comments;
    $unit_id = $cu->id;
} else {
    $pageName = $langAddUnit;
    $tagsInput = Tag::tagInput();
    $unitdescr = $unittitle = '';
}

$actionAppend = isset($unit_id) ? "&amp;id=$unit_id" : "";

$tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "${urlServer}courses/$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary-label')),false);
$tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code$actionAppend' onsubmit=\"return checkrequired(this, 'unittitle');\">";

if (isset($unit_id)) {
    $tool_content .= "<input type='hidden' name='unit_id' value='$unit_id'>";
}
$tool_content .= "<div class='form-group'>
                    <label for='unitTitle' class='col-sm-2 control-label'>$langTitle</label>
                    <div class='col-sm-10'>
                        <input type='text' class='form-control' id='unitTitle' name='unittitle' $unittitle>
                    </div>
                  </div>
            <div class='form-group'>
                <label for='unitdescr' class='col-sm-2 control-label'>$langUnitDescr</label>
                <div class='col-sm-10'>
                    " . rich_text_editor('unitdescr', 10, 20, $unitdescr) . "
                </div>
            </div>
            " . $tagsInput . "
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='edit_submit' value='" . q($langSubmit) . "'>
                </div>
            </div>            
        </form>
    </div>";

draw($tool_content, 2, null, $head_content);

