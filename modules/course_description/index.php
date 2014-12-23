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

$pageName = $langCourseDescription;

ModalBoxHelper::loadModalBox();
if ($is_editor) {
    load_js('tools.js');
    $tool_content .= "
	<div id='operations_container'>" .
            action_bar(array(
                array('title' => $langEditCourseProgram,
                    'url' => "edit.php?course=$course_code",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'))) .
            "</div>";

    processActions();

    if (isset($_POST['saveCourseDescription'])) {
        if (isset($_POST['editId'])) {
            updateCourseDescription($_POST['editId'], $_POST['editTitle'], $_POST['editComments'], $_POST['editType']);
        } else {
            updateCourseDescription(null, $_POST['editTitle'], $_POST['editComments'], $_POST['editType']);
        }
        Session::Messages($langCourseUnitAdded,"alert-success");
        redirect_to_home_page("modules/course_description/index.php");
    }
}

$q = Database::get()->queryArray("SELECT id, title, comments, type, visible FROM course_description WHERE course_id = ?d ORDER BY `order`", $course_id);
if ($q && count($q) > 0) {
    $i = 0;
    foreach ($q as $row) {
        $tool_content .= "            
            <div class='panel panel-action-btn-default ".($row->visible ? "" : "not_visible")."'>
              <div class='panel-heading'>
                <div class='pull-right'>".
                action_button(
                        array(
                            array('title' => q($langDelete),
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;del=$row->id",
                                'icon' => 'fa-times',
                                'class' => 'delete',
                                'confirm' => $langConfirmDelete),
                            array(
                                'title' => q($langEdit),
                                'url' => "edit.php?course=$course_code&amp;id=$row->id",
                                'icon' => 'fa-edit'
                            ),
                            array('title' => $langAddToCourseHome,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vis=$row->id",
                                'icon' => $row->visible ? 'fa-eye-slash' : 'fa-eye'
                            ),
                            array('title' => q($langUp),
                                'icon' => 'fa-arrow-up',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;up=$row->id",
                                'show' => $i > 0),
                            array('title' => q($langDown),
                                'icon' => 'fa-arrow-down',
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;down=$row->id",
                                'show' => $i + 1 < count($q))
                        )
                ) ."</div>
              <h3 class='panel-title'>$row->title</h3>      
              </div>
              <div class='panel-body'>"
                .handleType($row->type)."<br><br>"
               . standard_text_escape($row->comments) . 
              "</div>
            </div>";
        $i++;
    }
} else {
    $tool_content .= "<div class='alert alert-warning'>$langThisCourseDescriptionIsEmpty</div>";
}

add_units_navigation(true);
draw($tool_content, 2, null, $head_content);

// Helper Functions

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
        Session::Messages($langResourceCourseUnitDeleted, "alert-success");
        redirect_to_home_page("modules/course_description/index.php?course=$course_code");
    } elseif (isset($_REQUEST['vis'])) { // modify visibility in text resources only 
        $res_id = intval($_REQUEST['vis']);
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_description WHERE id = ?d AND course_id = ?d", $res_id, $course_id);
        $newvis = (intval($vis->visible) === 1) ? 0 : 1;
        Database::get()->query("UPDATE course_description SET `visible` = ?d, update_dt = NOW() WHERE id = ?d AND course_id = ?d", $newvis, $res_id, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
        redirect_to_home_page("modules/course_description/index.php?course=$course_code");
    } elseif (isset($_REQUEST['down'])) { // change order down
        $res_id = intval($_REQUEST['down']);
        move_order('course_description', 'id', $res_id, 'order', 'down', "course_id = $course_id");
        redirect_to_home_page("modules/course_description/index.php?course=$course_code");
    } elseif (isset($_REQUEST['up'])) { // change order up
        $res_id = intval($_REQUEST['up']);
        move_order('course_description', 'id', $res_id, 'order', 'up', "course_id = $course_id");
        redirect_to_home_page("modules/course_description/index.php?course=$course_code");
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
