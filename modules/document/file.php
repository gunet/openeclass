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
  @file file.php
  @brief serve files for subsystem documents
 */
// playmode is used in order to re-use this script's logic via play.php
$is_in_playmode = false;
if (defined('FILE_PHP__PLAY_MODE')) {
    $is_in_playmode = true;
}

session_start();

// save current course
if (isset($_SESSION['course_code'])) {
    define('old_course_code', $_SESSION['course_code']);
}

// lpmode is used for learning path
$is_in_lpmode = false;
if (isset($_SESSION['FILE_PHP__LP_MODE']) && $_SESSION['FILE_PHP__LP_MODE'] == true) {
    $is_in_lpmode = true;
}

$uri = preg_replace('/\?[^?]*$/', '', $_SERVER['REQUEST_URI']);

// If URI contains backslashes, redirect to forward slashes
if (stripos($uri, '%5c') !== false) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . str_ireplace('%5c', '/', $uri));
    exit;
}

$uri = (!$is_in_playmode) ? str_replace('//', chr(1), preg_replace('/^.*file\.php\??\//', '', $uri)) : str_replace('//', chr(1), preg_replace('/^.*play\.php\??\//', '', $uri));
$path_components = explode('/', $uri);

// temporary course change
$cinfo = addslashes(array_shift($path_components));
$cinfo_components = explode(',', $cinfo);
if ($cinfo_components[0] == 'common') {
    define('COMMON_DOCUMENTS', true);
} elseif ($cinfo_components[0] == 'user') {
    define('MY_DOCUMENTS', true);
    $mydocs_uid = $cinfo_components[1];
} else {
    $require_current_course = true;
    $_SESSION['dbname'] = $cinfo_components[0];
    if (isset($cinfo_components[1])) {
        $group_id = intval($cinfo_components[1]);
        define('GROUP_DOCUMENTS', true);
    } else {
        unset($group_id);
    }
}

$guest_allowed = true;
require_once '../../include/init.php';
require_once 'include/action.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'modules/game/ViewingEvent.php';
require_once 'modules/document/doc_init.php';

if (!(defined('COMMON_DOCUMENTS') or defined('MY_DOCUMENTS'))) {
    // check user's access to cours
    check_cours_access();
    // record file access
    if ($uid) {
        $action = new action();
        $action->record(MODULE_ID_DOCS);
    } else {
        $course_id = $_SESSION['course_id']; // anonymous with access token needs course id set
    }
}

if (defined('MY_DOCUMENTS')) {
    // temporary change, uid is used to get the full path in doc_init()
    $uid = $mydocs_uid;
}
doc_init();
$uid = $session->user_id;

if (defined('GROUP_DOCUMENTS')) {
    if (!$uid) {
        forbidden();
    }
    if (!($is_editor or $is_member)) {
        forbidden();
    }
}

$file_info = public_path_to_disk_path($path_components);
if (!$is_editor and !resource_access($file_info->visible, $file_info->public)) {
    forbidden();
}

if ($file_info->extra_path) {
    // $disk_path is set if common file link
    $disk_path = common_doc_path($file_info->extra_path, true);
    if (!$disk_path) {
        // external file URL
        triggerGame($file_info->id);
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
    if (!$is_in_playmode) {
        $valid = $uid ||
            defined('COMMON_DOCUMENTS') ||
            defined('MY_DOCUMENTS') ||
            course_status($course_id) == COURSE_OPEN ||
            (isset($_GET['token']) && token_validate($file_info->path, $_GET['token'], 30));
        if (!$valid) {
            not_found(preg_replace('/^.*file\.php/', '', $uri));
            exit();
        }

        $is_android = false;
        $useragent=$_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/(android).+mobile/i', $useragent)) {
            $is_android = true;
        }

        if ($is_in_lpmode && $is_android) {
            require_once 'include/lib/fileDisplayLib.inc.php';
            //$dl_url = $urlServer . 'modules/document/index.php?course=' . $course_code . '&amp;download=' . getIndirectReference($file_info->path);
            $dl_url = file_url($file_info->path);
            echo $langMailVerificationClick . " " . "<a href='" . $dl_url . "'>". $langDownload . "</a>";
            unset($_SESSION['FILE_PHP__LP_MODE']);
            exit();
        }
        triggerGame($file_info->id);
        send_file_to_client($disk_path, $file_info->filename);
    } else {
        require_once 'include/lib/fileDisplayLib.inc.php';
        require_once 'include/lib/multimediahelper.class.php';

        $mediaPath = file_url($file_info->path, $file_info->filename);
        $mediaURL = $urlServer . 'modules/document/index.php?course=' . $course_code . '&amp;download=' . $file_info->path;
        if (defined('GROUP_DOCUMENTS'))
            $mediaURL = $urlServer . 'modules/group/index.php?course=' . $course_code . '&amp;group_id=' . $group_id . '&amp;download=' . $file_info->path;
        $token = token_generate($file_info->path, true);
        $mediaAccess = $mediaPath . '?token=' . $token;
        
        triggerGame($file_info->id);
        echo MultimediaHelper::mediaHtmlObjectRaw($mediaAccess, $mediaURL, $mediaPath);
        exit();
    }
} else {
    not_found(preg_replace('/^.*file\.php/', '', $uri));
}

function check_cours_access() {
    global $course_code, $uid, $uri;

    if (!$uid && !isset($course_code))
        $course_code = $_SESSION['course_code'];

    $cours = Database::get()->querySingle("SELECT id, code, visible FROM `course` WHERE code=?s", $course_code);

    // invalid lesson code
    if (!$cours) {
        redirect_to_home_page();
        exit;
    }

    if ($cours->visible != COURSE_OPEN && !$uid && !isset($_GET['token'])) { // anonymous needs access token for closed courses
        not_found(preg_replace('/^.*\.php/', '', $uri));
        exit(0);
    }

    if (!$uid) {
        $_SESSION['course_id'] = $cours->id;
        return; // do not do own course check if anonymous with access token
    }

    switch ($cours->visible) {
        case '2': return;  // cours is open
        case '1':
        case '0':
        default:
            // check if user has access to course
            if (isset($_SESSION['courses'][$course_code]) && ($_SESSION['courses'][$course_code] >= 1)) {
                return;
            } else {
                redirect_to_home_page();
                exit(0);
            }
    }
    exit;
}

function triggerGame($documentId) {
    global $course_id, $uid;
    
    $eventData = new stdClass();
    $eventData->courseId = $course_id;
    $eventData->uid = $uid;
    $eventData->activityType = ViewingEvent::DOCUMENT_ACTIVITY;
    $eventData->module = MODULE_ID_DOCS;
    $eventData->resource = intval($documentId);
    
    ViewingEvent::trigger(ViewingEvent::NEWVIEW, $eventData);
}
