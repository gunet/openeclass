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

/**
 * Build the SCORM API data array for a given module.
 *
 * Extracts the PHP-side CMI initialization logic from scormAPI
 * into a reusable function. Used by both the initial page render and the
 * prepareModule AJAX endpoint.
 *
 * @param int      $pathId    Learning path ID
 * @param int      $moduleId  Module ID (lp_module.module_id)
 * @param int      $attempt   Attempt number
 * @param int|null $userId    User ID
 * @param int      $courseId  Course ID
 * @return array   Associative array with keys:
 *                 - 'sco': array of CMI values for JS injection
 *                 - 'ump_id': int user_module_progress_id (0 for anonymous)
 *                 - 'isAnonymous': bool
 */
function buildScormApiData(int $pathId, int $moduleId, int $attempt, ?int $userId, int $courseId, bool $attemptClean = false): array {
    $isAnonymous = empty($userId);

    $userProgressionDetails = null;
    $userProgressionDetailsPrev = null;

    if ($userId) {
        $userDetails = Database::get()->querySingle("SELECT surname, givenname
                  FROM `user` AS U WHERE U.`id` = ?d", $userId);

        $sql = "SELECT *
                  FROM `lp_user_module_progress` AS UMP,
                       `lp_rel_learnPath_module` AS LPM,
                       `lp_module` AS M
                 WHERE UMP.`user_id` = ?d
                   AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
                   AND M.`module_id` = LPM.`module_id`
                   AND LPM.`learnPath_id` = ?d
                   AND LPM.`module_id` = ?d
                   AND M.`course_id` = ?d
                   AND UMP.`attempt` = ?d";
        $userProgressionDetails = Database::get()->querySingle($sql, $userId, $pathId, $moduleId, $courseId, $attempt);

        // fetch previous attempt if available
        if ($attempt > 1 && !$attemptClean) {
            $userProgressionDetailsPrev = Database::get()->querySingle($sql, $userId, $pathId, $moduleId, $courseId, $attempt - 1);
        }
    }

    if (!$userId || !$userProgressionDetails) {
        $sco = [
            'student_id'       => '-1',
            'student_name'     => 'Anonymous, User',
            'lesson_location'  => '',
            'credit'           => 'no-credit',
            'lesson_status'    => 'NOT ATTEMPTED',
            'entry'            => 'AB-INITIO',
            'raw'              => '',
            'scoreMin'         => '',
            'scoreMax'         => '',
            'scoreScaled'      => '',
            'total_time'       => '0000:00:00.00',
            'suspend_data'     => '',
            'launch_data'      => '',
            'progress_measure' => '',
        ];
        $umpId = 0;
    } else {
        $studentName = $userDetails
            ? $userDetails->surname . ', ' . $userDetails->givenname
            : '';
        $sco = [
            'student_id'       => (string) $userId,
            'student_name'     => $studentName,
            'lesson_location'  => $userProgressionDetails->lesson_location,
            'credit'           => strtolower($userProgressionDetails->credit),
            'lesson_status'    => strtolower($userProgressionDetails->lesson_status),
            'entry'            => strtolower($userProgressionDetails->entry),
            'raw'              => ($userProgressionDetails->raw == -1) ? '' : '' . $userProgressionDetails->raw,
            'scoreMin'         => ($userProgressionDetails->scoreMin == -1) ? '' : '' . $userProgressionDetails->scoreMin,
            'scoreMax'         => ($userProgressionDetails->scoreMax == -1) ? '' : '' . $userProgressionDetails->scoreMax,
            'scoreScaled'      => '',
            'total_time'       => $userProgressionDetails->total_time,
            'suspend_data'     => $userProgressionDetails->suspend_data,
            'launch_data'      => $userProgressionDetails->launch_data,
            'progress_measure' => '',
        ];

        if ($userProgressionDetails->raw > 0 && $userProgressionDetails->scoreMax > 0) {
            $sco['scoreScaled'] = $userProgressionDetails->raw / $userProgressionDetails->scoreMax;
        }

        // use suspend data from previous attempt if available
        if ($userProgressionDetailsPrev && $userProgressionDetailsPrev->suspend_data) {
            $sco['suspend_data'] = $userProgressionDetailsPrev->suspend_data;
        }

        if (property_exists($userProgressionDetails, 'progress_measure') && $userProgressionDetails->progress_measure !== null) {
            $sco['progress_measure'] = $userProgressionDetails->progress_measure;
        }

        $umpId = (int) $userProgressionDetails->user_module_progress_id;
    }

    // common vars
    $sco['lesson_mode'] = 'normal';
    $sco['_children'] = 'student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,exit,session_time';
    $sco['score_children'] = 'raw,min,max';
    $sco['exit'] = '';
    $sco['session_time'] = '0000:00:00.00';

    return [
        'sco'         => $sco,
        'ump_id'      => $umpId,
        'isAnonymous' => $isAnonymous,
    ];
}
