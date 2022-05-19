<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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
require_once 'functions.php';
require_once 'modules/document/doc_init.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/tc/functions.php';

doc_init();

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
}

$toolName = $langCourseUnits;
$pageName = ''; // delete $pageName set in doc_init.php
$lang_editor = $language;
load_js('tools.js');
load_js('sortable/Sortable.min.js');
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
    $info = Database::get()->querySingle("SELECT * FROM course_units
        WHERE id = ?d AND course_id = ?d $visibility_check $check_start_week", $id, $course_id);
    if ($info) {
        $data['pageName'] = $info->title;
        $data['comments'] = trim($info->comments);
        $data['unitId'] = $info->id;
        $data['course_start_week'] = $data['course_finish_week'] = '';
        if (!(is_null($info->start_week))) {
            $data['course_start_week'] = "$langFrom2 " . nice_format($info->start_week);
        }
        if (!(is_null($info->finish_week))) {
            $data['course_finish_week'] = "$langTill " . nice_format($info->finish_week);
        }
    } else {
        Session::Messages($langUnknownResType);
        redirect_to_home_page("courses/$course_code/");
    }
}

$all_units = Database::get()->queryArray($query, $course_id);

if (!$is_editor) {
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
        $access_check = '';
    } else {
        $access_check = "AND public = 1";
    }

    $q = Database::get()->querySingle("SELECT id, title, start_week, finish_week, public FROM course_units
                       WHERE course_id = ?d
                             AND id <> ?d
                             AND `order` $op $info->order
                             AND `order` >= 0
                             $visibility_check
                             $access_check
                             $check_start_week
                       ORDER BY `order` $dir
                       LIMIT 1", $course_id, $id);

    if ($q and in_array($q->id, $userUnitsIds)) {
        $data[$i . 'Title'] = $q->title;
        $data[$i . 'Link'] = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&id=" . $q->id;
    } else {
        $data[$i . 'Link'] = null;
    }
}

$moduleTag = new ModuleElement($id);
$data['tags_list'] = $moduleTag->showTags();
$data['units'] = Database::get()->queryArray("SELECT id, title, start_week FROM course_units
             WHERE course_id = ?d AND `order` > 0
                   $visibility_check $check_start_week
             ORDER BY `order`", $course_id);

view('modules.units.index', $data);
