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


$require_login = true;

require_once "../../../../../include/baseTheme.php";
require_once 'mentoring_doc_init.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/drives/clouddrive.php';
require_once 'modules/mentoring/functions.php';

if(defined('MENTORING_GROUP_DOCUMENTS')){
    mentoring_program_access();
}

mentoring_doc_init();

$can_upload_replacement = $can_upload_mentoring;

$data['menuTypeID'] = 2;
$toolName = $langDoc;

enableCheckFileSize();

if (isset($_GET['uploadPath'])) {
    $data['uploadPath'] = q($_GET['uploadPath']);
} else {
    $data['uploadPath'] = '';
}

$data['can_upload_mentoring'] = $can_upload_mentoring;
$data['backUrl'] = documentBackLink($data['uploadPath']);
$data['upload_target_url'] = $upload_target_url;

if ($can_upload_mentoring) {
    $navigation[] = array('url' => $data['backUrl'], 'name' => $pageName);

    $data['languages'] = $fileLanguageNames;
    $data['copyrightTitles'] = array(
                            '0' => $langCopyrightedUnknown,
                            '2' => $langCopyrightedFree,
                            '1' => $langCopyrightedNotFree,
                            '3' => $langCreativeCommonsCCBY,
                            '4' => $langCreativeCommonsCCBYSA,
                            '5' => $langCreativeCommonsCCBYND,
                            '6' => $langCreativeCommonsCCBYNC,
                            '7' => $langCreativeCommonsCCBYNCSA,
                            '8' => $langCreativeCommonsCCBYNCND);

    $data['pendingCloudUpload'] = CloudDriveManager::getFileUploadPending();

    $data['externalFile'] = false;
    if ($data['pendingCloudUpload']) {
        $pageName = $langDownloadFile;
    } else if (isset($_GET['ext'])) {
        $data['externalFile'] = true;
        $pageName = $langExternalFile;
    } else {
        $pageName = $langDownloadFile;
    }
    
}

if(!defined('MENTORING_MYDOCS') and !defined('MENTORING_COMMON_DOCUMENTS')){
    $data['group_id'] = $program_group_id; 
    $data['is_group_doc'] = 1;

    $checkIsCommon = Database::get()->querySingle("SELECT common FROM mentoring_group 
                                                WHERE id = ?d 
                                                AND mentoring_program_id = ?d",$program_group_id,$mentoring_program_id)->common;

    if($checkIsCommon == 1){
        $data['isCommonGroup'] = 1;
    }else{
        $data['isCommonGroup'] = 0;
    }

 }else{
     $data['is_group_doc'] = 0;
 }


 $data['backButton'] = action_bar(array(
    array('title' => $langBackPage,
        'url' => $data['backUrl'],
        'button-class' => 'backButtonMentoring',
        'icon' => 'fa-chevron-left',
        'level' => 'primary-label')));

view('modules.mentoring.programs.group.document.upload', $data);