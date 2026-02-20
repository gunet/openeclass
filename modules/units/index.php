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


/**
 * @file index.php
 * @brief Units display module
 */

$require_current_course = true;
$guest_allowed = true;
$require_help = TRUE;
$helpTopic = 'course_units';

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/action.php';
require_once 'functions.php';
require_once 'modules/document/doc_init.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/tc/functions.php';

if ($is_editor or $is_course_reviewer) {
    $helpSubTopic = 'units_actions';
} else {
    $assign_to_specific = Database::get()->querySingle("SELECT assign_to_specific FROM course_units WHERE id = ?d", $_GET['id'])->assign_to_specific;
    if (!has_access_to_unit($_GET['id'], $assign_to_specific, $uid)) {
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("courses/$course_code/");
    }
}

doc_init();

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
}

$action = new action();
$action->record(MODULE_ID_UNITS);

$pageName = ''; // delete $pageName set in doc_init.php
$toolName = $langCourseUnits;
$lang_editor = $language;
load_js('tools.js');
load_js('sortable/Sortable.min.js');
ModalBoxHelper::loadModalBox(true);

$q = Database::get()->querySingle("SELECT flipped_flag FROM course WHERE code = ?s", $course_code);

if ($q->flipped_flag =="2"){
    // Handle unit resource reordering
    if ($is_editor and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if (isset($_POST['toReorder'])) {
            reorder_table('unit_resources', 'unit_id', $id, $_POST['toReorder'], $_POST['prevReorder'] ?? null);
            exit;
        }
    }
} else {
    // Handle unit resource reordering
    if ($is_editor and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if (isset($_POST['toReorder'])) {
            reorder_table('unit_resources', 'unit_id', $id, $_POST['toReorder'],
                isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
            exit;
        }
    }

}

if (isset($_POST['edit_submit'])) {
    handle_unit_info_edit();
}

if ($is_editor) {
    process_actions();
}

// check if we are trying to access a protected resource directly
$access = Database::get()->querySingle("SELECT public FROM course_units WHERE id = ?d", $id);
if ($access) {
    if (!resource_access(1, $access->public)) {
        Session::flash('message',$langForbidden);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("courses/$course_code/");
    }
}

if ($is_editor) {
    $data['editUrl'] = "info.php?course=$course_code&edit=$id&next=1";
    $data['manageUrl'] = "manage.php?course=$course_code&manage=1&unit_id=$id";
    $data['insertBaseUrl'] = $urlAppend . "modules/units/insert.php?course=$course_code&id=$id&type=";
    $visibility_check = $check_start_week = '';
    $query = "SELECT id, title, comments, start_week, finish_week, visible, public FROM course_units "
        . "WHERE course_id = ?d ";
} else {
    $visibility_check = "AND visible=1";
    $check_start_week = " AND (start_week <= CURRENT_DATE() OR start_week IS NULL)";
    $query = "SELECT id, title, comments, start_week, finish_week, visible, public FROM course_units "
        . "WHERE course_id = ?d "
        . "AND visible = 1 ";
}
if (isset($id) and $id !== false) {
    $info = Database::get()->querySingle("SELECT * FROM course_units WHERE id = ?d AND course_id = ?d $visibility_check $check_start_week", $id, $course_id);
    if ($info) {
        $data['pageName'] = $info->title;
        $data['comments'] = standard_text_escape($info->comments);
        $data['unitId'] = $info->id;
        $data['course_start_week'] = $data['course_finish_week'] = '';
        if (!(is_null($info->start_week))) {
            $data['course_start_week'] = "$langFrom2 " . format_locale_date(strtotime($info->start_week), 'short', false);
        }
        if (!(is_null($info->finish_week))) {
            $data['course_finish_week'] = "$langTill " . format_locale_date(strtotime($info->finish_week), 'short', false);
        }
    }
}

if (!isset($info) or !$info) {
    Session::flash('message',$langUnknownResType);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("courses/$course_code/");
}

$all_units = Database::get()->queryArray($query, $course_id);

if (!$is_editor && !$is_course_reviewer) {
    $user_units = findUserVisibleUnits($uid, $all_units);
} else {
    $user_units = $all_units;
}

foreach ($user_units as $user_unit) {
    $userUnitsIds[] = $user_unit->id;
}

// Links for next/previous unit
foreach (array('previous', 'next') as $i) {
    if ($i == 'previous') {
        $op = '<=';
        $dir = 'DESC';
    } else {
        $op = '>=';
        $dir = '';
    }

    if (isset($_SESSION['uid']) and isset($_SESSION['status'][$course_code]) and $_SESSION['status'][$course_code]) {
        $access_check = "";
    } else {
        $access_check = "AND public = 1";
    }
    $q = Database::get()->querySingle("SELECT id, title, start_week, finish_week, public FROM course_units
                       WHERE course_id = ?d
                             AND id <> ?d
                             AND visible < 2
                             AND `order` $op $info->order
                             AND `order` >= 0
                             $visibility_check
                             $access_check
                             $check_start_week
                       ORDER BY `order` $dir
                       LIMIT 1", $course_id, $id);

    // security check
    if (!in_array($id, $userUnitsIds)) {
        redirect_to_home_page("courses/$course_code/");
    }

    if ($q and in_array($q->id, $userUnitsIds)) {
        $data[$i . 'Title'] = $q->title;
        $data[$i . 'Link'] = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&id=" . $q->id;
    } else {
        $data[$i . 'Link'] = null;
    }
}

$moduleTag = new ModuleElement($id);
$data['id'] = $id;
$data['tags_list'] = $moduleTag->showTags();
$data['units'] = Database::get()->queryArray("SELECT id, title, start_week, visible FROM course_units
             WHERE course_id = ?d AND `order` >= 0
                   $visibility_check $check_start_week
             ORDER BY `order`", $course_id);

$data['base_url'] = $base_url = $urlAppend . "modules/units/insert.php?course=$course_code&id=$id&type=";

if($is_editor) {
    $data['q_in_class'] = Database::get()->queryArray("SELECT ID, activity_id, visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d",
        $course_code, $id, 0);

    $data['q_in_home'] = Database::get()->queryArray("SELECT ID,activity_id,visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d",
        $course_code, $id, 1);

    $data['q_after_class'] = Database::get()->queryArray("SELECT ID,activity_id,visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d",
        $course_code, $id, 2);
} else {
    $data['q_in_class'] = Database::get()->queryArray("SELECT ID, activity_id, visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d AND visible=?d",
        $course_code, $id, 0,1);

    $data['q_in_home'] = Database::get()->queryArray("SELECT ID,activity_id,visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d AND visible=?d",
        $course_code, $id, 1,1);

    $data['q_after_class'] = Database::get()->queryArray("SELECT ID,activity_id,visible FROM course_units_activities
             WHERE course_code = ?s AND unit_id = ?d AND activity_type=?d AND visible=?d",
        $course_code, $id, 2,1);
}
$cu_indirect = getIndirectReference($id);

$data['q'] = Database::get()->querySingle("SELECT flipped_flag FROM course WHERE code = ?s", $course_code);
$data['activities'] = $activities;

$data['tool_content_units'] = show_resources($data['unitId']);

view('modules.units.index', $data);
