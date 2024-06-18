<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
    global $urlAppend, $course_id, $course_code, $webDir, $can_upload, $group_name, 
        $is_editor, $is_admin, $navigation, $subsystem, $subsystem_id, $secret_directory,
        $group_id, $groupset, $base_url, $group_name, $upload_target_url, $group_sql, $is_member,
        $group_hidden_input, $basedir, $ebook_id, $uid, $session, $pageName, $sessionID, 
        $is_session_doc, $is_consultant, $uploaded_docs_by_users, $user_uploader;

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
        $pageName = trans('langGroupDocumentsLink');
        $navigation[] = array('url' => $urlAppend . 'modules/group/index.php?course=' . $course_code, 'name' => trans('langGroups'));
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
        $pageName = trans('langFileAdmin');
        $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => trans('langEBook'));
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
        $pageName = trans('langCommonDocs');
        $navigation[] = array('url' => $urlAppend . 'modules/admin/index.php', 'name' => trans('langAdmin'));
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
        $pageName = trans('langMyDocs');
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
        $group_id = '';
        $groupset = '';
        $group_hidden_input = '';
        $pageName = trans('langDoc');
        if (isset($is_session_doc) and $is_session_doc){
            $subsystem = MYSESSIONS;
            $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;session=' . $sessionID;
            $upload_target_url = $urlAppend . 'modules/session/resourse.php?course=' . $course_code . '&amp;session=' . $sessionID;
            $subsystem_id = $sessionID;
            $group_sql = "course_id = $course_id AND subsystem = $subsystem";
            $basedir = $webDir . '/courses/' . $course_code . '/session/session_' . $sessionID;            
        }else if (isset($uploaded_docs_by_users) and $uploaded_docs_by_users) {
            $subsystem = MYSESSIONS;
            $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;session=' . $sessionID;
            $upload_target_url = $urlAppend . 'modules/session/resourse.php?course=' . $course_code . '&amp;session=' . $sessionID;
            $subsystem_id = $sessionID;
            $group_sql = "course_id = $course_id AND subsystem = $subsystem AND subsystem_id = $subsystem_id";
            $basedir = $webDir . '/courses/' . $course_code . '/session/session_' . $sessionID . '/' . $user_uploader;
        } else{
            $subsystem = MAIN;
            $base_url = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;';
            $upload_target_url = 'index.php?course=' . $course_code;
            $subsystem_id = 'NULL';
            $group_sql = "course_id = $course_id AND subsystem = $subsystem";
            $basedir = $webDir . '/courses/' . $course_code . '/document';
        }
    }
}
