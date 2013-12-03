<?php
/* ========================================================================
 * Open eClass 2.8
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

require_once '../../include/baseTheme.php';
require_once '../../include/lib/forcedownload.php';
require_once '../../include/lib/mediaresource.factory.php';

if ( !isset($_GET['course']) || !isset($_GET['id']) ) {
    header("Location: ${urlServer}");
    exit();
}

if (strpos($_GET['course'], '..') !== false) {
    header("Location: ${urlServer}");
    exit();
}
    
// locate course id
$course_id = null;
$res1 = db_query("SELECT cours.cours_id FROM cours WHERE cours.code = ". quote(q($_GET['course'])));
$row1 = mysql_fetch_array($res1);
if (!empty($row1))
    $course_id = intval($row1['cours_id']);

if ($course_id == null) {
    //header("Location: ${urlServer}");
    //exit();
    echo "null cid";
}

/*if ($uid) {
    require_once '../../include/action.php';
    $action = new action();
    $action->record('MODULE_ID_VIDEO');
}*/

$dbname = q($_GET['course']);
mysql_select_db($dbname);
// ----------------------
// download video
// ----------------------
$res2 = db_query("SELECT * 
                   FROM video 
                  WHERE id = " . intval($_GET['id']));
$row2 = mysql_fetch_array($res2);

if (empty($row2)) {
    //header("Location: ${urlServer}");
    //exit();
    echo "no video found";
}

$valid = ($uid || course_status($course_id) == COURSE_OPEN) ? true : token_validate($row2['path'], $_GET['token'], 30);
if (!$valid) {
   //header("Location: ${urlServer}");
   //exit();
    echo "invalid access";
}

$row2['course_id'] = course_code_to_id($dbname);
$vObj = MediaResourceFactory::initFromVideo($row2);
$real_file = $webDir . "/video/" . q($_GET['course']) . q($vObj->getPath());
send_file_to_client($real_file, my_basename(q($vObj->getUrl())), 'inline', true);
