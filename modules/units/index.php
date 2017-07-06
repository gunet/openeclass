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
 * @file index.php
 * @brief Units display module
 */

$require_current_course = true;
$guest_allowed = true;
$require_help = TRUE;
$helpTopic = 'course_units';
$helpSubTopic = 'units_actions';

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/action.php';
require_once 'modules/units/functions.php';
require_once 'modules/document/doc_init.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';

doc_init();

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
}

$pageName = ''; // delete $pageName set in doc_init.php
$toolName = $langCourseUnits;
$lang_editor = $language;
load_js('tools.js');
ModalBoxHelper::loadModalBox(true);

if (isset($_POST['edit_submit'])) {
    handle_unit_info_edit();
}

process_actions();

// check if we are trying to access a protected resource directly
$access = Database::get()->querySingle("SELECT public FROM course_units WHERE id = ?d", $id);
if ($access) {
    if (!resource_access(1, $access->public)) {
        Session::Messages($langForbidden, 'alert-danger');
        redirect_to_home_page("courses/$course_code/");
    }
}

if ($is_editor) {
    $data['editUrl'] = "info.php?course=$course_code&amp;edit=$id&amp;next=1";
    $data['insertBaseUrl'] = $urlAppend . "modules/units/insert.php?course=$course_code&amp;id=$id&amp;type=";
    $visibility_check = '';
} else {
    $visibility_check = "AND visible=1";
}
if (isset($id) and $id !== false) {
    $info = Database::get()->querySingle("SELECT * FROM course_units
        WHERE id = ?d AND course_id = ?d $visibility_check", $id, $course_id);
    if ($info) {
        $data['pageName'] = $info->title;
        $data['comments'] = trim($info->comments);
        $data['unitId'] = $info->id;
    } else {
        Session::Messages($langUnknownResType);
        redirect_to_home_page("courses/$course_code/");
    }
}


// Links for next/previous unit
if (isset($_SESSION['uid']) and isset($_SESSION['status'][$course_code]) and $_SESSION['status'][$course_code]) {
    $access_check = '';
} else {
    $access_check = "AND public = 1";
}
foreach (array('previous', 'next') as $i) {
    if ($i == 'previous') {
        $op = '<=';
        $dir = 'DESC';
    } else {
        $op = '>=';
        $dir = '';
    }
    $q = Database::get()->querySingle("SELECT id, title, public FROM course_units
                       WHERE course_id = ?d
                             AND id <> ?d
                             AND `order` $op ?d
                             AND `order` >= 0
                             $visibility_check
                             $access_check
                       ORDER BY `order` $dir
                       LIMIT 1", $info->order, $course_id, $id);
    if ($q) {
        $data[$i . 'Title'] = $q->title;
        $data[$i . 'Link'] = $urlAppend . "?course=$course_code&id=" . $q->id;
    } else {
        $data[$i . 'Link'] = null;
    }
}

$moduleTag = new ModuleElement($id);
$data['tags'] = $moduleTag->showTags();
$data['units'] = Database::get()->queryArray("SELECT id, title FROM course_units
             WHERE course_id = ?d AND `order` > 0
                   $visibility_check
             ORDER BY `order`", $course_id);

view('modules.units.index', $data);


/**
 *
 * @global type $langCourseUnitModified
 * @global type $langCourseUnitAdded
 * @global type $course_id
 * @global type $course_code
 * @global type $webDir
 * @return type
 */
function handle_unit_info_edit() {
    global $course_id, $course_code, $webDir;

    $title = $_REQUEST['unittitle'];
    $descr = $_REQUEST['unitdescr'];
    if (isset($_REQUEST['unit_id'])) { // update course unit
        $unit_id = $_REQUEST['unit_id'];
        Database::get()->query("UPDATE course_units SET
                                        title = ?s,
                                        comments = ?s
                                    WHERE id = ?d AND course_id = ?d", $title, $descr, $unit_id, $course_id);
        // tags
        if (isset($_POST['tags'])) {
            $tagsArray = explode(',', $_POST['tags']);
            $moduleTag = new ModuleElement($unit_id);
            $moduleTag->syncTags($tagsArray);
        }
        $successmsg = trans('langCourseUnitModified');
    } else { // add new course unit
        $order = units_maxorder(course_id) + 1;
        $q = Database::get()->query("INSERT INTO course_units SET
                                  title = ?s, comments = ?s, visible = 1,
                                 `order` = ?d, course_id = ?d", $title, $descr, $order, $course_id);
        $successmsg = trans('langCourseUnitAdded');
        $unit_id = $q->lastInsertID;
        // tags
        if (isset($_POST['tags'])) {
            $tagsArray = explode(',', $_POST['tags']);
            $moduleTag = new ModuleElement($unit_id);
            $moduleTag->attachTags($tagsArray);
        }
    }
    // update index
    require_once 'modules/search/indexer.class.php';
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNIT, $unit_id);
    Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
    // refresh course metadata
    require_once 'modules/course_metadata/CourseXML.php';
    CourseXMLElement::refreshCourse($course_id, $course_code);
}

/**
 * Return the maximum order for course units in a course
 */
function units_maxorder($course_id) {
    $maxorder = Database::get()->querySingle("SELECT MAX(`order`) AS max_order
                    FROM course_units WHERE course_id = ?d", $course_id)->max_order;

    if ($maxorder <= 0) {
        return 0;
    }
    return $maxorder;
}
