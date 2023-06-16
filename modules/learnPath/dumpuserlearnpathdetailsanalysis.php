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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langTrackAllPathExplanation);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . "_learning_path_user_stats_analysis.xlsx";

$course_title = course_code_to_title($_GET['course']);

$data[] = [ $course_title ];
$data[] = [];
$data[] = [ $langSurnameName, $langLearnPath, $langAttempts, $langTotalTimeSpent, $langProgress ];

$usersList = Database::get()->queryArray("SELECT U.`surname`, U.`givenname`, U.`id`, U.`email`
        FROM `user` AS U, `course_user` AS CU
        WHERE U.`id`= CU.`user_id`
        AND CU.`course_id` = ?d
        ORDER BY U.`surname` ASC, U.`givenname` ASC", $course_id);

foreach ($usersList as $user) {
    $learningPathList = Database::get()->queryArray("SELECT learnPath_id, name FROM lp_learnPath WHERE course_id = ?d", $course_id);
    $iterator = 1;
    $globalprog = 0;
    $globaltime = "00:00:00";
    $lpaths = array();

    foreach ($learningPathList as $learningPath) {
        // % progress
        list($prog, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb) = get_learnPath_progress_details($learningPath->learnPath_id, $user->id);

        if ($prog >= 0) {
            $globalprog += $prog;
        }

        if (!empty($lpTotalTime)) {
            $globaltime = addScormTime($globaltime, $lpTotalTime);
        }

        $lpContent = array('', $learningPath->name, $lpAttemptsNb, $lpTotalTime, $prog);
        $lpaths[] = $lpContent;
        $iterator++;
    }
    $total = round($globalprog / ($iterator - 1));
    if ($globaltime === "00:00:00") {
        $globaltime = "";
    }

    $data[] = [ "$user->surname $user->givenname ($user->email)", ' ', ' ', $globaltime, $total . '%'];
    foreach ($lpaths as $lpContent) {
        $data[] = [ $lpContent[0], $lpContent[1], $lpContent[2], $lpContent[3], $lpContent[4] . '%' ];
    }
}

$sheet->mergeCells("A1:E1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 5; $i++) {
    $sheet->getCellByColumnAndRow($i, 3)->getStyle()->getFont()->setBold(true);
}
// create spreadsheet
$sheet->fromArray($data, NULL);
// file output
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
set_content_disposition('attachment', $filename);
$writer->save("php://output");
exit;
