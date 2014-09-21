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

$nameTools = $langEditUnit;

load_js('tools.js');

if (isset($_GET['edit'])) { // display form for editing course unit
    $id = $_GET['edit'];
    $cu = Database::get()->querySingle("SELECT id, title, comments FROM course_units WHERE id = ?d AND course_id = ?d",$id, $course_id);   
    if (!$cu) {
        $nameTools = $langUnitUnknown;
        $tool_content .= "<p class='caution'>$langUnknownResType</p>";
        draw($tool_content, 2, null, $head_content);
        exit;
    } 
    $unittitle = " value='" . htmlspecialchars($cu->title, ENT_QUOTES) . "'";
    $unitdescr = $cu->comments;
    $unit_id = $cu->id;
} else {
    $nameTools = $langAddUnit;
    $unitdescr = $unittitle = '';
}

if (isset($_GET['next'])) {
    $action = "index.php?course=$course_code&amp;id=$unit_id";
} else {
    $action = "${urlServer}courses/$course_code/";
}

$tool_content .= "<form method='post' action='$action' onsubmit=\"return checkrequired(this, 'unittitle');\">
    <fieldset>
    <legend>$nameTools</legend>";
if (isset($unit_id)) {
    $tool_content .= "<input type='hidden' name='unit_id' value='$unit_id'>";
}
$tool_content .= "
    <table class='tbl' width='100%'>
    <tr>
      <th width='150'>$langUnitTitle:</th>
    </tr>
    <tr>
      <td><input type='text' name='unittitle' size='50' maxlength='255' $unittitle ></td>
    </tr>
    <tr>
      <th valign='top'>$langUnitDescr:</th>
    </tr>
    <tr>
      <td>" . rich_text_editor('unitdescr', 4, 20, $unitdescr) . "</td>
    </tr>
    <tr>
      <td class='right'><input type='submit' name='edit_submit' value='$langSubmit'></td>
    </tr>
    </table>
    </fieldset>
    </form>";
draw($tool_content, 2, null, $head_content);

