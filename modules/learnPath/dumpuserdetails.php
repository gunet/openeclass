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
 * ======================================================================== */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = TRUE;
$require_editor = TRUE;

include '../../include/init.php';
require_once 'include/lib/learnPathLib.inc.php';

if (empty($_REQUEST['uInfo'])) { // user info can not be empty
    header("Location: ./index.php?course=$course_code");
    exit();
} else {
    $uInfo = intval($_REQUEST['uInfo']);
}

// check if user is in this course
$rescnt = Database::get()->querySingle("SELECT COUNT(*) AS count
            FROM `course_user` as `cu` , `user` as `u`
            WHERE `cu`.`user_id` = `u`.`id`
            AND `cu`.`course_id` = ?d
            AND `u`.`id` = ?d", $course_id, $uInfo)->count;

if ($rescnt == 0) {
    header("Location: ./index.php?course=$course_code");
    exit();
}

// get list of learning paths of this course
$lpList = Database::get()->queryArray("SELECT name, learnPath_id FROM lp_learnPath WHERE course_id = ?d ORDER BY `rank`", $course_id);

// get info about the user
$uDetails = Database::get()->querySingle("SELECT surname, givenname, email FROM `user` WHERE id = ?d", $_REQUEST['uInfo']);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langTracking);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . " - " . htmlspecialchars($uDetails->surname . " " . $uDetails->givenname) . "_user_stats.xlsx";

$course_title = course_code_to_title($_GET['course']);
$user_details = htmlspecialchars($uDetails->surname . " " . $uDetails->givenname);
$data[] = [ $user_details . ' (' . $course_title . ')' ];
$data[] = [];
$data[] = [ $langLearnPath, $langAttempts, $langAttemptStarted, $langAttemptAccessed, $langTotalTimeSpent, $langLessonStatus, $langProgress ];

$totalProgress = 0;
$totalTimeSpent = "0000:00:00";
foreach ($lpList as $lpDetails) {
    list($lpProgress, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb) = get_learnPath_progress_details($lpDetails->learnPath_id, $uInfo);
    $totalProgress += $lpProgress;
    if (!empty($lpTotalTime)) {
        $totalTimeSpent = addScormTime($totalTimeSpent, $lpTotalTime);
    }
    $lp_total_status = disp_lesson_status($lpTotalStatus);
    $data[] = [ $lpDetails->name, $lpAttemptsNb, $lpTotalStarted, $lpTotalAccessed, $lpTotalTime, $lp_total_status, $lpProgress . '%'];
}

if (count($lpList) > 0) {
    $total_progress = round($totalProgress/count($lpList));
    $data[] = [];
    $data[] = [ $langTotal, '', '', '', $totalTimeSpent, '', $total_progress . '%' ];
}

$sheet->mergeCells("A1:F1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 7; $i++) {
    $sheet->getCellByColumnAndRow($i, 3)->getStyle()->getFont()->setBold(true);
}
// create spreadsheet
$sheet->fromArray($data, NULL);
// file output
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=$filename");
$writer->save("php://output");
exit;
