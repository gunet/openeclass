<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

  ============================================================================
  @Description: Main script for the work tool
  ============================================================================
 */

require_once '../../include/baseTheme.php';
require_once 'modules/lti_consumer/lti-functions.php';
require_once 'modules/progress/ViewingEvent.php';

$course_id = null;
$course_code = null;

if (isset($_GET['id']) && intval($_GET['id']) > 0 && $uid) {
    $pid = intval($_GET['id']);
    // check if already participated
    $has_participated = Database::get()->querySingle("SELECT COUNT(*) as counter FROM poll_user_record WHERE uid = ?d AND pid = ?d", $uid, $pid)->counter;
    if ($has_participated == 0) {
        Database::get()->query("INSERT INTO poll_user_record (pid, uid) VALUES (?d, ?d)", $pid, $uid);
    }
    $launchcontainer = Database::get()->querySingle("SELECT launchcontainer FROM poll WHERE pid = ?d", $pid)->launchcontainer;

    $course_id = $_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$pid.'_COURSE_ID'];
    $course_code = $_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$pid.'_COURSE_CODE'];

    $eventData = new stdClass();
    $eventData->courseId = $course_id;
    $eventData->uid = $uid;
    $eventData->activityType = ViewingEvent::QUESTIONNAIRE_ACTIVITY;
    $eventData->module = MODULE_ID_QUESTIONNAIRE;
    $eventData->resource = intval($pid);
    ViewingEvent::trigger(ViewingEvent::NEWVIEW, $eventData);

    if ($launchcontainer == LTI_LAUNCHCONTAINER_EMBED) {
        echo "<p>".$langPollSubmitted."</p>";
        echo "<p>".$langPollOutcomeClose."</p>";
    } else {
        Session::Messages($langPollSubmitted);
        redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
    }

    unset($_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$pid.'_COURSE_ID']);
    unset($_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$pid.'_COURSE_CODE']);
}

die();
