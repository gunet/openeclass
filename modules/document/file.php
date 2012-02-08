<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

session_start();

// save current course
if (isset($_SESSION['dbname'])) {
        define('old_dbname', $_SESSION['dbname']);
}

$uri = preg_replace('/\?[^?]*$/', '', 
                    $_SERVER['REQUEST_URI']);

// If URI contains backslashes, redirect to forward slashes
if (stripos($uri, '%5c') !== false) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . str_ireplace('%5c', '/', $uri));
        exit;
}

$uri = str_replace('//', chr(1), preg_replace('/^.*file\.php\??\//', '', $uri));
$path_components = explode('/', $uri);

// temporary course change
$cinfo = addslashes(array_shift($path_components));
$cinfo_components = explode(',', $cinfo);
$_SESSION['dbname'] = $cinfo_components[0];

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
        $basedir = "{$webDir}courses/$dbname/document";
}

$file_info = public_path_to_disk_path($path_components);
if ($file_info['visibility'] != 'v' and !$is_editor) {
        error($langNoRead);
}

if (file_exists($basedir . $file_info['path'])) {
        send_file_to_client($basedir . $file_info['path'], $file_info['filename']);
} else {
        not_found(preg_replace('/^.*file\.php/', '', $uri));
}

function check_cours_access() {
	global $mysqlMainDb, $currentCourse, $dbname, $statut;

	// $dbname is used in filepath so we stick to this instead of $currentCourse
	$qry = "SELECT cours_id, code, visible FROM `cours` WHERE code='$dbname'";
	$result = db_query($qry, $mysqlMainDb);

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
			// check if user has access to cours
			if (isset($_SESSION['status'][$dbname]) && ($_SESSION['status'][$dbname] >= 1)) {
				return;
			}
			else {
				redirect_to_home_page();
			}
	}

	exit;
}
