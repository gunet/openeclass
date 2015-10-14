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
require_once 'modules/game/ViewingEvent.php';

$action = new action();
$action->record(MODULE_ID_VIDEO);

// ----------------------
// play video
// ----------------------
$row = Database::get()->querySingle("SELECT * FROM video WHERE course_id = ?d AND id = ?d", $course_id, $_GET['id']);

if ($row) {
    // trigger gamification
    $videoData = new stdClass();
    $videoData->courseId = $course_id;
    $videoData->uid = $uid;
    $videoData->activityType = ViewingEvent::VIDEO_ACTIVITY;
    $videoData->module = MODULE_ID_VIDEO;
    $videoData->resource = intval($_GET['id']);
    ViewingEvent::trigger(ViewingEvent::NEWVIEW, $videoData);
    
    $vObj = MediaResourceFactory::initFromVideo($row);
    $token = token_generate($row->path, true);                         // generate new token
    $vObj->setAccessURL($vObj->getAccessURL() . '&amp;token=' . $token); // append token to accessurl
    echo MultimediaHelper::mediaHtmlObject($vObj);
} else {
    header("Location: ${urlServer}modules/video/index.php?course=$course_code");
}
