<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

// setup
$require_current_course = true;
$require_editor = true;
$require_help = true;
$helpTopic = 'Video';

// dependencies
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/action.php';
require_once 'inc/video_functions.php';

$action = new action();
$action->record('MODULE_ID_VIDEO');

// navigation
$toolName = $langVideo;
$pageName = $langQuotaBar;
$backPath = $urlAppend . "modules/video/index.php?course=" . $course_code;
$navigation[] = array('url' => $backPath, 'name' => $langVideo);

// data and view
$data = array();
list($diskQuotaVideo, $updir, $diskUsed) = getQuotaInfo($course_code, $webDir);
$data['showQuota'] = showquota($diskQuotaVideo, $diskUsed, $backPath);
view('modules.video.showQuota', $data);
