<?php

/* ========================================================================
 * Open eClass 3.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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

require_once 'exercise.class.php';

$require_current_course = TRUE;
$require_editor = TRUE;

require_once '../../include/init.php';
require_once 'include/lib/csv.class.php';
require_once 'modules/exercise/question.class.php';
require_once 'modules/exercise/answer.class.php';

$exerciseId = getDirectReference($_GET['exerciseId']);
$objExercise = new Exercise();
$objExercise->read($exerciseId);
$csv = new CSV();
$csv->filename = $course_code . '_' . $exerciseId . '_' . date('Y-m-d') . '.csv';

$headings = array();
$csv->outputRecord($langSurname, $langName, $langAm, $langGroup, $langStart,
    $langExerciseDuration, $langStudentTotalScore, $langTotalScore,
    $headings);

$result = Database::get()->queryArray("SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid = ?d", $exerciseId);

foreach ($result as $row) {
    $sid = $row->uid;
    $surname = uid_to_name($sid, 'surname');
    $name = uid_to_name($sid, 'givenname');
    $am = uid_to_am($sid);
    $ug = user_groups($course_id, $sid, 'txt');

    $result2 = Database::get()->queryArray("SELECT DATE_FORMAT(record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date,
        record_end_date, TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date)) AS time_duration,
        total_score, total_weighting, eurid, attempt_status
        FROM `exercise_user_record` WHERE uid = ?d AND eid = ?d  
        ORDER BY record_start_date DESC", $sid, $exerciseId);

    foreach ($result2 as $row2) {
        if ($row2->time_duration == '00:00:00' or empty($row2->time_duration)) { // for compatibility
            $duration = $langNotRecorded;
        } else {
            $duration = format_time_duration($row2->time_duration);
        }
        $exerciseRange = $objExercise->selectRange();
        $total_score = '';
        if ($exerciseRange > 0) {
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                $total_score = $objExercise->canonicalize_exercise_score($row2->total_score, $row2->total_weighting);
            }
            $total_weighting = $exerciseRange;
        } else {
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                $total_score = $row2->total_score;
            }
            $total_weighting = $row2->total_weighting;
        }

        $csv->outputRecord($surname, $name, $am, $ug, $row2->record_start_date,
            $duration, $total_score, $total_weighting);
    }
}