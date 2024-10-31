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

ini_set('log_errors_max_len', 0);
set_time_limit(0);

error_log("=== received a tii outcome services request ===");

// receive xml data
$data = file_get_contents('php://input');

$origentity = libxml_disable_entity_loader(true);
$xml = simplexml_load_string($data);
if (!$xml) {
    libxml_disable_entity_loader($origentity);
    error_log('Invalid XML content');
    die();
}
libxml_disable_entity_loader($origentity);

$body = $xml->imsx_POXBody;
foreach ($body->children() as $child) {
    $messagetype = $child->getName();
}

if ($messagetype != 'replaceResultRequest') {
    die();
}

$sourcedid = $xml->imsx_POXBody->replaceResultRequest->resultRecord->sourcedGUID->sourcedId;
$resultScore = $xml->imsx_POXBody->replaceResultRequest->resultRecord->result->resultScore->textString;

$score = (string) $resultScore;
$gradef = floatval($score);
if ( $gradef < 0.0 || $gradef > 1.0 ) {
    error_log('Score not between 0.0 and 1.0');
    die();
}

require_once '../../include/baseTheme.php';
require_once 'modules/lti_consumer/lti-functions.php';
require_once 'modules/work/functions.php';
require_once 'modules/gradebook/functions.php';
require_once 'modules/progress/AssignmentEvent.php';
require_once 'include/log.class.php';

// submit grade
list($assignment_id, $uid, $assignment, $lti, $user) = lti_verify_extract_sourcedid($sourcedid, PHP_INT_MAX);
$sid = Database::get()->querySingle("SELECT id FROM assignment_submit WHERE uid = ?d AND assignment_id = ?d", $uid, $assignment_id)->id;
$grade = ($gradef * $assignment->max_grade);
if ($gradef == 0) {
    $grade = NULL;
    $gradef = NULL;
}

if (Database::get()->query("UPDATE assignment_submit SET grade = ?f, grade_submission_date = NOW(), grade_submission_ip = ?s WHERE id = ?d",
                                        $grade, Log::get_client_ip(), $sid)->affectedRows > 0) {

    triggerGame($assignment->course_id, $uid, $assignment_id);
    triggerAssignmentAnalytics($assignment->course_id, $uid, $assignment_id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
    triggerAssignmentAnalytics($assignment->course_id, $uid, $assignment_id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
    Log::record($assignment->course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $sid,
        'title' => $assignment->title,
        'grade' => $grade,
        'comments' => ''));

    //update gradebook if needed
    update_gradebook_book($uid, $assignment_id, $gradef, GRADEBOOK_ACTIVITY_ASSIGNMENT);
}
