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
require_once 'modules/group/group_functions.php';

if (empty($_REQUEST['path_id'])) { // path id can not be empty
    header("Location: ./index.php?course=$course_code");
    exit();
} else {
    $path_id = intval($_REQUEST['path_id']);
}

// get infos about the learningPath
$learnPathName = Database::get()->querySingle("SELECT `name` FROM `lp_learnPath` WHERE `learnPath_id` = ?d AND `course_id` = ?d", $path_id, $course_id);

if (!$learnPathName) {
    header("Location: ./index.php?course=$course_code");
    exit();
}
$course_title = course_code_to_title($_GET['course']);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($course_title);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . " - " . htmlspecialchars($learnPathName->name) . "_user_stats.xlsx";

$data[] = [ $learnPathName->name ];
$data[] = [];
$data[] = [ $langSurnameName, $langEmail, $langAm, $langGroup, $langAttempts, $langAttemptStarted, $langAttemptAccessed, $langTotalTimeSpent, $langLessonStatus, $langProgress ];

$usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`, U.`email`, U.`am`
            FROM `user` AS U, `course_user` AS CU
            WHERE U.`id`= CU.`user_id`
            AND CU.`course_id` = ?d
            ORDER BY U.`surname` ASC, U.`givenname` ASC", $course_id);

foreach ($usersList as $user) {
    list($lpProgress, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb) = get_learnPath_progress_details($path_id, $user->id);

    $ug = user_groups($course_id, $user->id, 'csv');
    $lp_total_status = disp_lesson_status($lpTotalStatus);
    $lp_total_started = format_locale_date(strtotime($lpTotalStarted), 'short');
    $lp_total_accessed = format_locale_date(strtotime($lpTotalAccessed), 'short');

    $data[] = [ "$user->surname $user->givenname", $user->email, $user->am, $ug, $lpAttemptsNb, $lp_total_started, $lp_total_accessed, $lpTotalTime, $lp_total_status, $lpProgress . '%' ];
}

$sheet->mergeCells("A1:J1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 10; $i++) {
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
