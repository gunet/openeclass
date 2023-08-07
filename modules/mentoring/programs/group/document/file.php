<?php

/* ========================================================================
 * Open eClass 3.8
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
  @file file.php
  @brief serve files for subsystem documents
 */


// playmode is used in order to re-use this script's logic via play.php
$is_in_playmode_mentoring = false;
if (defined('MENTORING_FILE_PHP__PLAY_MODE')) {
    $is_in_playmode_mentoring = true;
}

session_start();

$_SERVER['REQUEST_URI'] = str_replace("file.php?", "file.php/", $_SERVER['REQUEST_URI'] ?? '');
$uri = preg_replace('/\?[^?]*$/', '', $_SERVER['REQUEST_URI']);

// If URI contains backslashes, redirect to forward slashes
if (stripos($uri, '%5c') !== false) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . str_ireplace('%5c', '/', $uri));
    exit;
}

$uri = (!$is_in_playmode_mentoring) ? str_replace('//', chr(1), preg_replace('/^.*file\.php\??\//', '', $uri)) : str_replace('//', chr(1), preg_replace('/^.*play\.php\??\//', '', $uri));

$path_components = explode('/', $uri);

$count = 0;
foreach($path_components as $p){
    if($count == 0){
        $mentoring_program_code = $p;
    }
    $count++;
}



// temporary course change
$cinfo = addslashes(array_shift($path_components));
$cinfo_components = explode(',', $cinfo);

if ($cinfo_components[0] == 'common') {
    define('MENTORING_COMMON_DOCUMENTS', true);
}else if ($cinfo_components[0] == 'user') {
    define('MENTORING_MYDOCS', true);
    $mentoring_mydocs_uid = $cinfo_components[1];
}
elseif (isset($cinfo_components[1])) {
    $program_group_id = $cinfo_components[1];
    define('MENTORING_GROUP_DOCUMENTS', true);
} else {
    unset($program_group_id);
}



$guest_allowed = true;
require_once '../../../../../include/init.php';
require_once 'include/action.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'modules/progress/ViewingEvent.php';
require_once 'modules/mentoring/functions.php';
require_once 'modules/mentoring/programs/group/document/mentoring_doc_init.php';

if (defined('MENTORING_MYDOCS')) {
    // temporary change, uid is used to get the full path in doc_init
    $uid = $mentoring_mydocs_uid;
}

mentoring_doc_init();

$uid = $session->user_id;

if (defined('MENTORING_GROUP_DOCUMENTS')) {
    if (!$uid) {
        forbidden();
    }
    if (!($is_editor_mentoring_group or $is_member)) {
        forbidden();
    }
}

$file_info = public_path_to_disk_path($path_components);
if (!$file_info->visible == 1 or !$file_info->public == 1) {
    forbidden();
}
if (!$is_editor_mentoring_group and !mentoring_resource_access($file_info->visible, $file_info->public)) {
    forbidden();
}

if ($file_info->extra_path) {
    // $disk_path is set if common file link
    $disk_path = common_doc_path($file_info->extra_path, true);
    if (!$disk_path) {
        // external file URL
        //triggerGame($file_info->id);
        header("Location: $file_info->extra_path");
        exit;
    } elseif (!$common_doc_visible) {
        forbidden(preg_replace('/^.*file\.php/', '', $uri));
    }
} else {
    // Normal file
    $disk_path = $basedir . $file_info->path;
}

if (file_exists($disk_path)) {
   
    if (!$is_in_playmode_mentoring) {
        //triggerGame($file_info->id);
        send_file_to_client($disk_path, $file_info->filename);
    } else {
        require_once 'include/lib/fileDisplayLib.inc.php';
        require_once 'include/lib/multimediahelper.class.php';

        $mediaPath = mentoring_file_url($file_info->path, $file_info->filename);
        $mediaURL = $urlServer . 'modules/mentoring/programs/group/document/index.php?download=' . $file_info->path;
        if (defined('MENTORING_GROUP_DOCUMENTS')) {
            $mediaURL = $urlServer . 'modules/mentoring/programs/group/index.php?group_id=' . $program_group_id . '&amp;download=' . $file_info->path;
        }
        $token = token_generate($file_info->path, true);
        $mediaAccess = $mediaPath . '?token=' . $token;

        //triggerGame($file_info->id);
        echo MultimediaHelper::mediaHtmlObjectRaw($mediaAccess, $mediaURL, $mediaPath);
        exit();
    }
} else {
    not_found(preg_replace('/^.*file\.php/', '', $uri));
}




