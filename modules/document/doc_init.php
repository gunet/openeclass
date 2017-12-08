<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

function doc_init() {
    global $urlAppend, $course_id, $course_code, $webDir, $can_upload, $langEBook,
        $is_editor, $is_admin, $navigation, $subsystem, $subsystem_id, $langFileAdmin,
        $group_id, $groupset, $group_name, $base_url, $upload_target_url, $group_sql, $langDoc,
        $group_hidden_input, $basedir, $ebook_id, $uid, $session, $pageName,
        $langGroupDocumentsLink, $is_member, $secret_directory, $langGroups,
        $langCommonDocs, $langAdmin, $langMyDocs;

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
        if (!isset($ebook_id) and isset($_REQUEST['ebook_id'])) {
            $ebook_id = intval($_REQUEST['ebook_id']);
        }
        $subsystem = EBOOK;
        $subsystem_id = $ebook_id;
        $groupset = "ebook_id=$ebook_id&amp;";
        $group_id = '';
        $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;' . $groupset;
        $upload_target_url = 'document.php?course=' . $course_code . '&amp;ebook_id=' . $ebook_id . (isset($_REQUEST['from']) ? '&amp;from=ebookEdit' : '');
        $group_sql = "course_id = $course_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id";
        $group_hidden_input = "<input type='hidden' name='ebook_id' value='$ebook_id'>";
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
            make_dir($basedir);
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
    } elseif (defined('MY_DOCUMENTS')) {
        $subsystem = MYDOCS;
        $subsystem_id = $uid;
        $groupset = '';
        $base_url = $_SERVER['SCRIPT_NAME'] . '?';
        $upload_target_url = 'index.php';
        $group_id = '';
        $group_sql = "subsystem = $subsystem AND subsystem_id = $uid";
        $group_hidden_input = '';
        $basedir = $webDir . '/courses/mydocs/' . $uid;
        if (!is_dir($basedir)) {
            make_dir($basedir);
        }
        $pageName = $langMyDocs;
        // Saved course code so that file picker menu doesn't lose
        // the current course if we're in a course
        if (isset($_GET['course']) and $_GET['course']) {
            define('SAVED_COURSE_CODE', $_GET['course']);
            define('SAVED_COURSE_ID', course_code_to_id(SAVED_COURSE_CODE));
            $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . SAVED_COURSE_CODE . '&amp;';
        }
        $course_id = -1;
        $course_code = '';
        $can_upload = $session->user_id == $uid;
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
}
