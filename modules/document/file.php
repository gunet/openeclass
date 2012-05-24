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

/*===========================================================================
file.php
 * @version $Id$
*/

// playmode is used in order to re-use this script's logic via play.php
$is_in_playmode = false;
$is_in_lightstyle = false;
if (defined('FILE_PHP__PLAY_MODE'))
    $is_in_playmode = true;

session_start();

if (isset($_SESSION['FILE_PHP__LIGHT_STYLE'])) {
    $is_in_lightstyle = true;
    unset($_SESSION['FILE_PHP__LIGHT_STYLE']);
}

// save current course
if (isset($_SESSION['course_code'])) {
        define('old_course_code', $_SESSION['course_code']);
}

$uri = preg_replace('/\?[^?]*$/', '', 
                    $_SERVER['REQUEST_URI']);

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
$_SESSION['course_code'] = $cinfo_components[0];

if (isset($cinfo_components[1])) {
        $group_id = intval($cinfo_components[1]);
        define('GROUP_DOCUMENTS', true);
} else {
        unset($group_id);
}

$require_current_course = true;
$guest_allowed = true;

include '../../include/init.php';
include '../../include/action.php';

// check user's access to cours
check_cours_access();

// record file access
$action = new action();
$action->record(MODULE_ID_DOCS);

include 'doc_init.php';
include '../../include/lib/forcedownload.php';

if (defined('GROUP_DOCUMENTS')) {
        if (!$uid) {
                error($langNoRead);
        }
        if (!($is_editor or $is_member)) {
                error($langNoRead);
        }
} else {
        $basedir = "{$webDir}courses/$course_code/document";
}

$file_info = public_path_to_disk_path($path_components);
if ($file_info['visibility'] != 'v' and !$is_editor) {
        error($langNoRead);
}

if (file_exists($basedir . $file_info['path'])) {
    if (!$is_in_playmode)
        send_file_to_client($basedir . $file_info['path'], $file_info['filename']);
    else {
        require_once ('../video/video_functions.php');
        require_once '../../include/lib/fileDisplayLib.inc.php';
        
        $mediaPath = file_url($file_info['path'], $file_info['filename']);
        $mediaURL = $urlServer .'modules/document/document.php?course='. $course_code .'&amp;download='. $file_info['path'];
        if (defined('GROUP_DOCUMENTS'))
            $mediaURL = $urlServer .'modules/group/document.php?course='. $course_code .'&amp;group_id='.$group_id.'&amp;download='. $file_info['path'];
        
        $htmlout = (!$is_in_lightstyle) ? media_html_object($mediaPath, $mediaURL) : media_html_object($mediaPath, $mediaURL, '#ffffff', '#000000');
        echo $htmlout;
        exit();
    }
} else {
        not_found(preg_replace('/^.*file\.php/', '', $uri));
}

function check_cours_access() {
	global $course_code;

	$qry = "SELECT id, code, visible FROM `course` WHERE code='$course_code'";
	$result = db_query($qry);

	// invalid lesson code
	if (mysql_num_rows($result) != 1) {
		redirect_to_home_page();
		exit;
	}

	$cours = mysql_fetch_array($result);

	switch($cours['visible']) {
		case '2': return; 	// cours is open
		case '1': 
		case '0': 
		default: 
                // check if user has access to course
                if (isset($_SESSION['status'][$course_code]) && ($_SESSION['status'][$course_code] >= 1)) {
                        return;
                }
                else {
                        redirect_to_home_page();
                }
	}
	exit;
}
