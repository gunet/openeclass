<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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
        $old_dbname = $_SESSION['dbname'];
}

$uri = str_replace('//', chr(1), strstr($_SERVER['REQUEST_URI'], 'file.php/'));
$path_components = split('/', $uri);
array_shift($path_components);

// temporary course change
$dbname = addslashes(array_shift($path_components));

$require_current_course = true;
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/forcedownload.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_DOCS');
/**************************************/

$basedir = "{$webDir}courses/$dbname/document";

$depth = 1;
$path = '';
foreach ($path_components as $component) {
        $component = urldecode(str_replace(chr(1), '/', $component));
        $q = db_query("SELECT path, visibility, format,
                              (LENGTH(path) - LENGTH(REPLACE(path, '/', ''))) AS depth
                       FROM document WHERE filename = " . quote($component) .
                       " AND path LIKE '$path%' HAVING depth = $depth");
        $r = mysql_fetch_array($q);
        $path = $r['path'];
        $depth++;
}
if ($r['visibility'] != 'v' and !$is_adminOfCourse) {
        $_SESSION['errMessage'] = $l_noread;
        session_write_close();
        // restore current course
        if (isset($old_dbname)) {
                $dbname = $old_dbname;
        }
        header("Location: $urlServer" );
}
if (!preg_match("/\.$r[format]$/", $component)) {
        $component .= '.' . $r['format'];
}

// restore current course
if (isset($old_dbname)) {
        $dbname = $old_dbname;
}
if (file_exists($basedir . $r['path'])) {
        send_file_to_client($basedir . $r['path'], $component, true);
} else {
        header('Location: ', $urlServer);
}
