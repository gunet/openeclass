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

require_once 'include/lib/learnPathLib.inc.php';
require_once 'modules/learnPath/viewerlib/response_helpers.php';

// handling of the API form when POSTed by the SCORM API

function updateProgress(): string {
    global $uid;

    if (!$uid) {
        return resp_return_json([
            'ok' => false,
            'error' => 'Not authorized',
            'code' => 'LP_FORBIDDEN',
        ]);
    }

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        return resp_return_json([
            'ok' => false,
            'error' => 'Invalid token',
            'code' => 'LP_FORBIDDEN',
        ]);
    }

    if (!isset($_POST['ump_id'])) {
        return resp_return_json([
            'ok' => false,
            'error' => 'Invalid input',
            'code' => 'LP_BAD_REQUEST',
        ]);
    }

    $ump_id = (int) $_POST['ump_id'];
    $ump = Database::get()->querySingle(
        'SELECT user_module_progress_id, learnPath_id FROM lp_user_module_progress WHERE user_module_progress_id = ?d AND user_id = ?d',
        $ump_id,
        $uid
    );
    if (!$ump) {
        return resp_return_json([
            'ok' => false,
            'error' => 'Progress record not found',
            'code' => 'LP_NOT_FOUND',
        ]);
    }

    $lesson_status_value = strtoupper($_POST['lesson_status'] ?? '');
    $allowed_statuses = ['NOT ATTEMPTED', 'INCOMPLETE', 'COMPLETED', 'PASSED', 'FAILED', 'BROWSED', 'UNKNOWN'];
    if ($lesson_status_value !== '' && !in_array($lesson_status_value, $allowed_statuses)) {
        $lesson_status_value = '';
    }

    $credit_value = strtoupper($_POST['credit'] ?? '');
    if ($credit_value !== '' && !in_array($credit_value, ['CREDIT', 'NO-CREDIT'])) {
        $credit_value = '';
    }

    // set values for the scores
    $raw_value = isset($_POST['raw']) && $_POST['raw'] !== '' ? max(0, min(100, (int) $_POST['raw'])) : -1;
    $scoreMin_value = isset($_POST['scoreMin']) && $_POST['scoreMin'] !== '' ? max(0, min(100, (int) $_POST['scoreMin'])) : -1;
    $scoreMax_value = isset($_POST['scoreMax']) && $_POST['scoreMax'] !== '' ? max(0, min(100, (int) $_POST['scoreMax'])) : -1;

    $progress_measure = null;
    if (isset($_POST['progress_measure']) && $_POST['progress_measure'] !== '') {
        $progress_measure = max(0.0, min(1.0, (float) $_POST['progress_measure']));
    }

    // next visit of the sco will not be the first so entry must be set to RESUME
    $exit_value = $_POST['exit'] ?? '';
    $entry_value = '';
    if ($exit_value == 'time-out' || $exit_value == 'normal') {
        $entry_value = 'AB-INITIO';
    } elseif ($exit_value == 'suspend' || $exit_value == 'logout') {
        $entry_value = 'RESUME';
    }

    // Set lesson status to COMPLETED if the SCO didn't change it itself.
    if ($lesson_status_value == 'NOT ATTEMPTED') {
        $lesson_status_value = 'COMPLETED';
    }

    // set credit if needed
    if ($lesson_status_value == 'COMPLETED' || $lesson_status_value == 'PASSED') {
        if (strtoupper($_POST['credit'] ?? '') == 'CREDIT') {
            $credit_value = 'CREDIT';
        }
    }

    //set maxScore to 100 if the SCO didn't change it itself, but gave raw
    if ($raw_value > 0 && $raw_value <= 100 && $scoreMax_value <= 0) {
        $scoreMax_value = 100;
    }

    $total_time_value = $_POST['total_time'] ?? '';
    $session_time_formatted = $_POST['session_time'] ?? '';
    if (isScorm2004Time($_POST['session_time'] ?? '')) {
        $total_time_value = addScorm2004Time($_POST['total_time'] ?? '', $_POST['session_time'] ?? '');
        $session_time_formatted = addScorm2004Time('0000:00:00.00', $_POST['session_time'] ?? '');
    } elseif (isScormTime($_POST['session_time'] ?? '')) {
        $total_time_value = addScormTime($_POST['total_time'] ?? '', $_POST['session_time'] ?? '');
        $session_time_formatted = $_POST['session_time'] ?? '';
    }

    $sql = "UPDATE `lp_user_module_progress`
            SET
                `lesson_location` = ?s,
                `lesson_status` = ?s,
                `entry` = ?s,
                `raw` = ?d,
                `scoreMin` = ?d,
                `scoreMax` = ?d,
                `total_time` = ?s,
                `session_time` = ?s,
                `progress_measure` = ?f,
                `suspend_data` = ?s,
                `credit` = ?s,
                `accessed` = " . DBHelper::timeAfter() . "
          WHERE `user_module_progress_id` = ?d";
    Database::get()->query(
        $sql,
        $_POST['lesson_location'] ?? '',
        $lesson_status_value,
        $entry_value,
        $raw_value,
        $scoreMin_value,
        $scoreMax_value,
        $total_time_value,
        $session_time_formatted,
        $progress_measure,
        $_POST['suspend_data'] ?? '',
        $credit_value,
        $ump_id
    );

    $lp = Database::get()->querySingle("SELECT lp.learnPath_id, lp.course_id 
            FROM lp_user_module_progress lump
            JOIN lp_learnPath lp ON (lp.learnPath_id = lump.learnPath_id)
            WHERE lump.user_module_progress_id = ?d", $ump_id);

    if ($lp) {
        triggerLPGame($lp->course_id, $uid, $lp->learnPath_id, LearningPathEvent::UPDPROGRESS);
        triggerLPAnalytics($lp->course_id, $uid, $lp->learnPath_id);
    }

    $progress = 0;
    if ($lp) {
        $progress = get_learnPath_progress($lp->learnPath_id, $uid);
    }
    $scaled = null;
    if ($scoreMax_value > 0) {
        $scaled = $raw_value / $scoreMax_value;
    }

    return resp_return_json([
        'ok' => true,
        'progress' => $progress,
        'lesson_status' => $lesson_status_value,
        'credit' => $credit_value,
        'score' => [
            'raw' => $raw_value,
            'min' => $scoreMin_value,
            'max' => $scoreMax_value,
            'scaled' => $scaled,
        ],
    ]);
}
