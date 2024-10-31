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

require_once '../../include/baseTheme.php';
require_once 'modules/lti_consumer/lti-functions.php';
require_once 'modules/progress/ViewingEvent.php';

$course_id = null;
$course_code = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = (file_get_contents('php://input'));
    $xml = simplexml_load_string($data);
    if (!$xml) {
        error_log('Invalid XML content');
        die();
    }
    $body = $xml->imsx_POXBody;
    foreach ($body->children() as $child) {
        $messagetype = $child->getName();
    }
    if ($messagetype != 'replaceResultRequest') {
        error_log('Invalid message type');
        die();
    }
    $sourcedid = $xml->imsx_POXBody->replaceResultRequest->resultRecord->sourcedGUID->sourcedId;
    list($course_id, $poll_id, $user_id) = lti_verify_extract_poll_sourcedid($sourcedid);
    // check if already participated
    $has_participated = Database::get()->querySingle("SELECT COUNT(*) as counter
        FROM poll_user_record WHERE uid = ?d AND pid = ?d", $user_id, $poll_id)->counter;
    if ($has_participated == 0) {
        Database::get()->query("INSERT INTO poll_user_record (pid, uid) VALUES (?d, ?d)", $poll_id, $user_id);
    }
    unset($_SESSION['POLL_POST_LAUNCH_'.$user_id.'_'.$poll_id.'_COURSE_ID']);
    unset($_SESSION['POLL_POST_LAUNCH_'.$user_id.'_'.$poll_id.'_COURSE_CODE']);
    $eventData = new stdClass();
    $eventData->courseId = $course_id;
    $eventData->uid = $user_id;
    $eventData->activityType = ViewingEvent::QUESTIONNAIRE_ACTIVITY;
    $eventData->module = MODULE_ID_QUESTIONNAIRE;
    $eventData->resource = intval($poll_id);
    ViewingEvent::trigger(ViewingEvent::NEWVIEW, $eventData);

    echo '<?xml version="1.0" encoding="UTF-8"?>
        <statusinfo>
        <codemajor>Success</codemajor>
        </statusinfo>';
    exit;
}

// Handle return from LimeSurvey when questionnaire id is not passed in URL
// Will not work correctly if user opens two questionnaires simultaneously
if ($uid and !isset($_GET['id'])) {
    array_map(function ($key) use ($uid) {
        if (preg_match("/POLL_POST_LAUNCH_{$uid}_(\d+)_COURSE_ID/", $key, $m)) {
            $_GET['id'] = $m[1];
        }
    }, array_keys($_SESSION));
}

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
        Session::flash('message',$langPollSubmitted);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
    }

    unset($_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$pid.'_COURSE_ID']);
    unset($_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$pid.'_COURSE_CODE']);
}

exit;

function lti_verify_extract_poll_sourcedid($sourcedid) {
    // extract sourcedid info
    $sourcediddata = explode('-', $sourcedid);
    if (count($sourcediddata) != 4) {
        error_log("Invalid lis_result_sourcedid, exiting ($sourcedid)...");
        die();
    }
    $token = $sourcediddata[0] . "-" . $sourcediddata[1];
    $poll_id = intval($sourcediddata[2]);
    $uid = intval($sourcediddata[3]);

    // locate/validate poll, lti, user and token
    $poll = Database::get()->querySingle("SELECT pid, course_id FROM poll WHERE pid = ?d", $poll_id);
    if (!$poll) {
        error_log("No questionnaire found, exiting ($sourcedid)...");
        die();
    }
    $ts_valid_time = 24 * 60 * 60; // wait 24 hours for response
    if (!token_validate("$poll_id-$uid", $token, $ts_valid_time)) {
        error_log("Invalid token, exiting ($sourcedid)...");
        die();
    }
    $user = Database::get()->querySingle("SELECT id FROM user WHERE id  = ?d", $uid);
    if (!$user) {
        error_log("No user found, exiting ($sourcedid)...");
        die();
    }

    return array($poll->course_id, $poll->pid, $user->id);
}

