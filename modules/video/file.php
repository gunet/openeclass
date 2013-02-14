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

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/action.php';

// ----------------------
// download video
// ----------------------
// TODO: encapsulate access token in db query
$res = db_query("SELECT video.* 
                   FROM video 
                   JOIN course ON video.course_id = course.id 
                  WHERE course.code = '" . q($_GET['course']) . "' 
                    AND video.id = " . intval($_GET['id']));
$row = mysql_fetch_array($res);

if (!empty($row)) {
    // TODO: needs uid and courseId
    //$action = new action();
    //$action->record(MODULE_ID_VIDEO);    
    $vObj = MediaResourceFactory::initFromVideo($row);
    $real_file = $webDir . "/video/" . q($_GET['course']) . q($vObj->getPath());
    send_file_to_client($real_file, my_basename(q($vObj->getUrl())), null, true);
} else
    header("Location: ${urlServer}");

