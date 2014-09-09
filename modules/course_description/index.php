<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/course_metadata/CourseXML.php';
require_once 'include/log.php';

// track stats
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_DESCRIPTION);

$nameTools = $langCourseDescription;
$unit_id = description_unit_id($course_id);

ModalBoxHelper::loadModalBox();
if ($is_editor) {
    load_js('tools.js');
    $tool_content .= "
	<div id='operations_container'>
	  <ul id='opslist'>
		<li><a href='edit.php?course=$course_code'>$langEditCourseProgram</a></li>
	  </ul>
	</div>";

    processActions();

    if (isset($_POST['saveCourseDescription'])) {
        if (isset($_POST['editId'])) {
            updateCourseDescription($_POST['editId'], $_POST['editTitle'], $_POST['editComments'], $_POST['editType']);
        } else {
            updateCourseDescription(null, $_POST['editTitle'], $_POST['editComments'], $_POST['editType']);
        }
    } else if (isset($_POST['submit']) && isset($_POST['edIdBloc'])) {
        // Save results from block edit (save action)
        $res_id = intval($_POST['edIdBloc']);
        if ($res_id == -1) {
            $unit_id = description_unit_id($course_id);
            add_unit_resource($unit_id, 'description', $res_id, $_POST['edTitleBloc'], $_POST['edContentBloc']);
            header("Location: {$urlServer}courses/$course_code");
            exit;
        }
    }
}

$q = Database::get()->queryArray("SELECT id, title, comments, type, visible FROM course_description WHERE course_id = ?d ORDER BY `order`", $course_id);
if ($q && count($q) > 0) {
    $i = 0;
    foreach ($q as $row) {
        $tool_content .= "
        <table width='100%' class='tbl_border'>
        <tr class='odd'>
         <td class='bold'>" . q($row->title) . "</td>" .
                handleActions($row->id, $row->visible, $i, count($q)) . "
        </tr>";
        $tool_content .= handleType($row->type);
        $tool_content .= "<tr>";
        $colspan = ($is_editor) ? "colspan='6'" : "";
        $tool_content .= "<td $colspan>" . standard_text_escape($row->comments) . "</td>";
        $tool_content .= "</tr></table><br />";
        $i++;
    }
} else {
    $tool_content .= "<p class='alert1'>$langThisCourseDescriptionIsEmpty</p>";
}

add_units_navigation(true);
draw($tool_content, 2, null, $head_content);

// Helper Functions

function handleActions($cDescId, $visible, $counter, $total) {
    global $is_editor, $langEdit, $langDelete,
    $langAddToCourseHome, $langDown, $langUp,
    $langConfirmDelete, $course_code, $themeimg;

    if (!$is_editor) {
        return '';
    }

    $cDescId = intval($cDescId);
    $counter = intval($counter);
    $total = intval($total);
    $icon_vis = (intval($visible) === 1) ? 'publish.png' : 'unpublish.png';
    $edit_link = "edit.php?course=$course_code&amp;id=$cDescId";

    // edit
    $content = "<td width='3'><a href='$edit_link'>" .
            "<img src='$themeimg/edit.png' title='" . q($langEdit) . "' alt='" . q($langEdit) . "' /></a></td>";

    // delete
    $content .= "<td width='3'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del=$cDescId'" .
            " onClick=\"return confirmation('" . js_escape($langConfirmDelete) . "')\">" .
            "<img src='$themeimg/delete.png' " .
            "title='" . q($langDelete) . "' alt='" . q($langDelete) . "'></a></td>";

    // visibility
    $content .= "<td width='3'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vis=$cDescId'>" .
            "<img src='$themeimg/$icon_vis' " .
            "title='" . q($langAddToCourseHome) . "' alt='" . q($langAddToCourseHome) . "'></a></td>";

    // down
    if ($counter + 1 < $total) {
        $content .= "\n          <td width='12'><div align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;down=$cDescId'>" .
                "<img src='$themeimg/down.png' title='" . q($langDown) . "' alt='" . q($langDown) . "'></a></div></td>";
    } else {
        $content .= "\n          <td width='12'>&nbsp;</td>";
    }

    // up
    if ($counter > 0) {
        $content .= "<td width='12'><div align='left'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;up=$cDescId'>" .
                "<img src='$themeimg/up.png' title='" . q($langUp) . "' alt='" . q($langUp) . "'></a></div></td>";
    } else {
        $content .= "\n          <td width='12'>&nbsp;</td>";
    }

    return $content;
}

function handleType($typeId) {
    global $is_editor, $language;

    $typeId = intval($typeId);
    if ($typeId <= 0) {
        return '';
    }
    $colspan = ($is_editor) ? "colspan='6'" : "";

    $res = Database::get()->querySingle("SELECT title FROM course_description_type WHERE id = ?d", $typeId);

    $title = $titles = @unserialize($res->title);
    if ($titles !== false) {
        if (isset($titles[$language]) && !empty($titles[$language])) {
            $title = $titles[$language];
        } else if (isset($titles['en']) && !empty($titles['en'])) {
            $title = $titles['en'];
        } else {
            $title = array_shift($titles);
        }
    }

    return "<tr><td $colspan><em>$title</em></td></tr>";
}

function processActions() {
    global $tool_content, $langResourceCourseUnitDeleted, $course_id, $course_code;

    if (isset($_REQUEST['del'])) { // delete resource from course unit
        $res_id = intval($_REQUEST['del']);
        Database::get()->query("DELETE FROM course_description WHERE id = ?d AND course_id = ?d", $res_id, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
        $tool_content .= "<p class='success'>$langResourceCourseUnitDeleted</p>";
    } elseif (isset($_REQUEST['vis'])) { // modify visibility in text resources only 
        $res_id = intval($_REQUEST['vis']);
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_description WHERE id = ?d AND course_id = ?d", $res_id, $course_id);
        $newvis = (intval($vis->visible) === 1) ? 0 : 1;
        Database::get()->query("UPDATE course_description SET `visible` = ?d, update_dt = NOW() WHERE id = ?d AND course_id = ?d", $newvis, $res_id, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    } elseif (isset($_REQUEST['down'])) { // change order down
        $res_id = intval($_REQUEST['down']);
        move_order('course_description', 'id', $res_id, 'order', 'down', "course_id = $course_id");
    } elseif (isset($_REQUEST['up'])) { // change order up
        $res_id = intval($_REQUEST['up']);
        move_order('course_description', 'id', $res_id, 'order', 'up', "course_id = $course_id");
    }
}

function updateCourseDescription($cdId, $title, $comments, $type) {
    global $course_id, $course_code;
    $type = (isset($type)) ? intval($type) : null;

    if ($cdId !== null) {
        Database::get()->query("UPDATE course_description SET
                title = ?s,
                comments = ?s,
                type = ?d,
                update_dt = NOW()
                WHERE id = ?d", $title, $comments, $type, intval($cdId));
    } else {
        $res = Database::get()->querySingle("SELECT MAX(`order`) AS max FROM course_description WHERE course_id = ?d", $course_id);
        $maxorder = ($res->max !== false) ? intval($res->max) + 1 : 1;

        Database::get()->query("INSERT INTO course_description SET
                course_id = ?d,
                title = ?s,
                comments = ?s,
                type = ?d,
                `order` = ?d,
                update_dt = NOW()", $course_id, $title, purify($comments), $type, $maxorder);
    }
    CourseXMLElement::refreshCourse($course_id, $course_code);
}
