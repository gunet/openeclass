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
        define('GROUP_DOCUMENTS', false);
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

if (GROUP_DOCUMENTS) {
        if (!$uid) {
                error($langNoRead);
        }
        if (!($is_adminOfCourse or $is_member)) {
                error($langNoRead);
        }
} else {
        $basedir = "{$webDir}courses/$dbname/document";
}

$depth = 1;
$path = '';
foreach ($path_components as $component) {
        $component = urldecode(str_replace(chr(1), '/', $component));
        $q = db_query("SELECT path, visibility, format,
                              (LENGTH(path) - LENGTH(REPLACE(path, '/', ''))) AS depth
                              FROM document
                              WHERE $group_sql AND
                                    filename = " . quote($component) . " AND
                                    path LIKE '$path%' HAVING depth = $depth");
        if (!$q or mysql_num_rows($q) == 0) {
                not_found();
        }
        $r = mysql_fetch_array($q);
        $path = $r['path'];
        $depth++;
}

if ($r['visibility'] != 'v' and !$is_adminOfCourse) {
        error($langNoRead);
}
if (!preg_match("/\.$r[format]$/", $component)) {
        $component .= '.' . $r['format'];
}

restore_saved_course();
if (file_exists($basedir . $r['path'])) {
        send_file_to_client($basedir . $r['path'], $component, true);
} else {
        header('Location: ', $urlServer);
}

// Restore current course
function restore_saved_course()
{
        if (defined('old_dbname')) {
                $_SESSION['dbname'] = old_dbname;
        }
}

function not_found()
{
        global $uri;
        restore_saved_course();
        header("HTTP/1.0 404 Not Found");
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head>',
             '<title>404 Not Found</title></head><body>',
             '<h1>Not Found</h1><p>The requested path "',
             htmlspecialchars(preg_replace('/^.*file\.php/', '', $uri)),
             '" was not found.</p></body></html>';
        exit;
}

function error($message)
{
        global $urlServer;
        $_SESSION['errMessage'] = $message;
        restore_saved_course();
        session_write_close();
        header("Location: $urlServer" );
        exit;
}
