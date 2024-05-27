<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = TRUE;
$require_course_reviewer = TRUE;

require_once '../../include/baseTheme.php';
require_once 'exercise.class.php';
require_once 'modules/exercise/question.class.php';
require_once 'modules/exercise/answer.class.php';

$exerciseId = getDirectReference($_GET['exerciseId']);
$objExercise = new Exercise();
$objExercise->read($exerciseId);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langResults);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . '_' . $exerciseId . '_' . date('Y-m-d') . '.xlsx';
$course_title = course_id_to_title($course_id);

$data[] = [ $course_title ];

// exercise details
$exercise_details = $objExercise->selectTitle();
$exercise_details .= " $langTotalScore: " . $objExercise->selectTotalWeighting();
if (!empty($objExercise->selectStartDate())) {
    $exercise_details .= " $langStart: " . format_locale_date(strtotime($objExercise->selectStartDate()), 'short');
}
if (!empty($objExercise->selectEndDate())) {
    $exercise_details .= " $langPollEnd: " . format_locale_date(strtotime($objExercise->selectEndDate()), 'short');
}

$data[] = [ $exercise_details ];
$data[] = [];
$data[] = [ $langSurname, $langName, $langAm, $langGroup, $langStart, $langExerciseDuration, $langTotalScore ];

$result = Database::get()->queryArray("(SELECT DISTINCT uid, surname, givenname, am
                                                    FROM `exercise_user_record`
                                                    JOIN user ON uid = id
                                                    WHERE eid = ?d
                                                    AND attempt_status != " . ATTEMPT_CANCELED . ")
                                                UNION
                                                    (SELECT 0 as uid, '$langAnonymous' AS surname, '$langUser' AS givenname, '' as am
                                                    FROM `exercise_user_record` WHERE eid = ?d
                                                    AND attempt_status != " . ATTEMPT_CANCELED . "
                                                    AND uid = 0)
                                                ORDER BY surname, givenname"
                                                , $exerciseId, $exerciseId);

foreach ($result as $row) {
    $sid = $row->uid;
    $surname = $row->surname;
    $name = $row->givenname;
    $am = $row->am;
    $ug = user_groups($course_id, $sid, 'txt');

    $result2 = Database::get()->queryArray("SELECT record_start_date,
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
        } else {
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                $total_score = $row2->total_score;
            }
        }
        $record_start_date = format_locale_date(strtotime($row2->record_start_date), 'short');
        $data[] = [ $surname, $name, $am, $ug, $record_start_date, $duration, $total_score ];
    }
}

$sheet->mergeCells("A1:G1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
$sheet->getCell('A2')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 7; $i++) {
    $cells = [$i, 4];
    $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
}
// create spreadsheet
$sheet->fromArray($data, NULL);

// file output
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
set_content_disposition('attachment', $filename);
$writer->save("php://output");
exit;
