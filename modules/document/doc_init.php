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

/**
 * @file doc_init.php
 * @brief initialize various subsystems for subsystem document
 */
$can_upload = $is_editor || $is_admin;
if (defined('GROUP_DOCUMENTS')) {
    require_once 'modules/group/group_functions.php';
    $subsystem = GROUP;
    initialize_group_id();
    initialize_group_info($group_id);
    if (!$uid or !($is_member or $can_upload)) {
        forbidden();
    }
    $subsystem_id = $group_id;
    $groupset = "group_id=$group_id&amp;";
    $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;' . $groupset;
    $upload_target_url = 'document.php?course=' . $course_code;
    $group_sql = "course_id = $course_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id";
    $group_hidden_input = "<input type='hidden' name='group_id' value='$group_id' />";
    $basedir = $webDir . '/courses/' . $course_code . '/group/' . $secret_directory;
    $can_upload = $can_upload || $is_member;
    $pageName = $langGroupDocumentsLink;
    $navigation[] = array('url' => $urlAppend . 'modules/group/index.php?course=' . $course_code, 'name' => $langGroups);
    $navigation[] = array('url' => $urlAppend . 'modules/group/group_space.php?course=' . $course_code . '&amp;group_id=' . $group_id, 'name' => q($group_name));
} elseif (defined('EBOOK_DOCUMENTS')) {
    if (isset($_REQUEST['ebook_id'])) {
        $ebook_id = intval($_REQUEST['ebook_id']);
    }
    $subsystem = EBOOK;
    $subsystem_id = $ebook_id;
    $groupset = "ebook_id=$ebook_id&amp;";
    $group_id = '';
    $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;' . $groupset;
    $upload_target_url = 'document.php?course=' . $course_code;
    $group_sql = "course_id = $course_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id";
    $group_hidden_input = "<input type='hidden' name='ebook_id' value='$ebook_id' />";
    $basedir = $webDir . '/courses/' . $course_code . '/ebook/' . $ebook_id;
    $pageName = $langFileAdmin;
    $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langEBook);    
} elseif (defined('COMMON_DOCUMENTS')) {
    $subsystem = COMMON;
    $subsystem_id = 'NULL';
    $groupset = '';
    $base_url = $_SERVER['SCRIPT_NAME'] . '?';
    $upload_target_url = 'commondocs.php';
    $group_id = '';
    $group_sql = "course_id = -1 AND subsystem = $subsystem";
    $group_hidden_input = '';
    $basedir = $webDir . '/courses/commondocs';
    if (!is_dir($basedir)) {
        mkdir($basedir, 0775);
    }
    $pageName = $langCommonDocs;
    $navigation[] = array('url' => $urlAppend . 'modules/admin/index.php', 'name' => $langAdmin);
    // Saved course code so that file picker menu doesn't lose
    // the current course if we're in a course
    if (isset($_GET['course']) and $_GET['course']) {
        define('SAVED_COURSE_CODE', $_GET['course']);
        define('SAVED_COURSE_ID', course_code_to_id(SAVED_COURSE_CODE));
        $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . SAVED_COURSE_CODE . '&amp;';
    }
    $course_id = -1;
    $course_code = '';
} else {
    $subsystem = MAIN;
    $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;';
    $upload_target_url = 'index.php?course=' . $course_code;
    $subsystem_id = 'NULL';
    $group_id = '';
    $groupset = '';
    $group_sql = "course_id = $course_id AND subsystem = $subsystem";
    $group_hidden_input = '';
    $basedir = $webDir . '/courses/' . $course_code . '/document';
    $pageName = $langDoc;
}

