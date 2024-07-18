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
 * @file upload.php
 * @brief upload form for subsystem documents
 */



$require_admin = defined('COMMON_DOCUMENTS');
$require_current_course = !(defined('COMMON_DOCUMENTS') or defined('MY_DOCUMENTS'));
$require_login = true;

require_once "../../include/baseTheme.php";
require_once "modules/document/doc_init.php";
require_once 'modules/drives/clouddrive.php';
require_once 'include/course_settings.php';

doc_init();

$can_upload_replacement = $can_upload;
$pendingCloudUpload = '';
if ($subsystem == MAIN and get_config('enable_docs_public_write') and
    setting_get(SETTING_DOCUMENTS_PUBLIC_WRITE)) {
        $can_upload = true;
}

if (defined('COMMON_DOCUMENTS')) {
    $toolName = $langCommonDocs;
} elseif (defined('MY_DOCUMENTS')) {
    if ($session->status == USER_TEACHER and !get_config('mydocs_teacher_enable')) {
        redirect_to_home_page();
    }
    if ($session->status == USER_STUDENT and !get_config('mydocs_student_enable')) {
        redirect_to_home_page();
    }
    $toolName = $langMyDocs;
} else if (isset($_GET['ext'])) {
    $pageName = $langExternalFile;
} else {
    $pageName = $langUpload;
}

enableCheckFileSize();

if (defined('EBOOK_DOCUMENTS')) {
    $navigation[] = array('url' => 'edit.php?course=' . $course_code . '&amp;id=' . $ebook_id, 'name' => $langEBookEdit);
}

if (isset($_GET['uploadPath'])) {
    $uploadPath = q($_GET['uploadPath']);
} else {
    $uploadPath = '';
}

$backUrl = documentBackLink($uploadPath);

if ($can_upload) {
    $navigation[] = array('url' => $backUrl, 'name' => $langDoc);
    $pendingCloudUpload = CloudDriveManager::getFileUploadPending();
}

$data['can_upload_replacement'] = $can_upload_replacement;
$data['can_upload'] = $can_upload;
$data['backUrl'] = $backUrl;
$data['upload_target_url'] = $upload_target_url;
$data['uploadPath'] = $uploadPath;
$data['group_hidden_input'] = $group_hidden_input;
$data['pendingCloudUpload'] = $pendingCloudUpload;
$license_title = [];

foreach ($license as $license_selection) {
    $license_title[] = $license_selection['title'];
}

$data['license_title'] = $license_title;

view("modules.document.upload", $data);
