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

$require_current_course = true;
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/action.php';

$action = new action();
$action->record(MODULE_ID_VIDEO);

// ----------------------
// play videolink
// ----------------------
$res = db_query("SELECT * FROM videolinks WHERE course_id = $course_id AND id = " . intval($_GET['id']));
$row = mysql_fetch_array($res);

if (!empty($row)) {
    $vObj = MediaResourceFactory::initFromVideoLink($row);
    echo MultimediaHelper::medialinkIframeObject($vObj);
} else
    header("Location: ${urlServer}modules/video/index.php?course=$course_code");
