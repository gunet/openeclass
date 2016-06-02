<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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


$require_current_course = TRUE;
require_once '../../include/init.php';
require_once 'include/lib/csv.class.php';

if ($is_editor) {
    $exerciseId = filter_input(INPUT_GET, 'exerciseId', FILTER_SANITIZE_NUMBER_INT);
    $csv = new CSV();
    $csv->filename = $course_code . '_' . $exerciseId . '_' . date('Y-m-d') . '.csv';
    $csv->outputRecord($langSurname, $langName, $langAm, $langStart, $langExerciseDuration, $langStudentTotalScore, $langTotalScore);

    $result = Database::get()->queryArray("SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid = ?d", $exerciseId);

    foreach ($result as $row) {
        $sid = $row->uid;
        $surname = uid_to_name($sid, 'surname');
        $name = uid_to_name($sid, 'givenname');
        $am = uid_to_am($sid);

        $result2 = Database::get()->queryArray("SELECT DATE_FORMAT(record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date,
			record_end_date, TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date)) AS time_duration,
			total_score, total_weighting
			FROM `exercise_user_record` WHERE uid = ?d AND eid = ?d", $sid, $exerciseId);
        
        foreach ($result2 as $row2) {
            if ($row2->time_duration == '00:00:00' or empty($row2->time_duration)) { // for compatibility
                $duration = $langNotRecorded;
            } else {
                $duration = format_time_duration($row2->time_duration);
            }
            $csv->outputRecord($surname, $name, $am, $row2->record_start_date, $duration, $row2->total_score, $row2->total_weighting);
        }
    }
}

