<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_current_course = true;
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'include/action.php';
require_once 'modules/progress/ViewingEvent.php';

$action = new action();
$action->record(MODULE_ID_VIDEO);

// ----------------------
// play videolink
// ----------------------
$row = Database::get()->querySingle("SELECT * FROM videolink WHERE course_id = ?d AND id = ?d", $course_id, $_GET['id']);

if ($row) {
    // trigger gamification
    $videoData = new stdClass();
    $videoData->courseId = $course_id;
    $videoData->uid = $uid;
    $videoData->activityType = ViewingEvent::VIDEOLINK_ACTIVITY;
    $videoData->module = MODULE_ID_VIDEO;
    $videoData->resource = intval($_GET['id']);
    ViewingEvent::trigger(ViewingEvent::NEWVIEW, $videoData);

    $vObj = MediaResourceFactory::initFromVideoLink($row);
    echo MultimediaHelper::medialinkIframeObject($vObj);
} else
    header("Location: {$urlServer}modules/video/index.php?course=$course_code");
