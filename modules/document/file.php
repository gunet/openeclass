<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/


/*===========================================================================
file.php
 * @version $Id$
*/

session_start();

// save current course
if (isset($_SESSION['dbname'])) {
        define('old_dbname', $_SESSION['dbname']);
}

$uri = str_replace('//', chr(1), preg_replace('/^.*file\.php\??\//', '', $_SERVER['REQUEST_URI']));
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

// record file access
$action = new action();
$action->record('MODULE_ID_DOCS');

include 'doc_init.php';
include '../../include/lib/forcedownload.php';

if (defined('GROUP_DOCUMENTS')) {
        if (!$uid) {
                error($langNoRead);
        }
        if (!($is_adminOfCourse or $is_member)) {
                error($langNoRead);
        }
} else {
        $basedir = "{$webDir}courses/$dbname/document";
}

$file_info = public_path_to_disk_path($path_components);
if ($file_info['visibility'] != 'v' and !$is_adminOfCourse) {
        error($langNoRead);
}

if (file_exists($basedir . $file_info['path'])) {
        send_file_to_client($basedir . $file_info['path'], $file_info['filename'], true);
} else {
        not_found(preg_replace('/^.*file\.php/', '', $uri));
}

