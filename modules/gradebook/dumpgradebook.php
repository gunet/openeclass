<?php
/* ========================================================================
 * Open eClass 3.5
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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_editor = true;

include '../../include/init.php';
require_once 'functions.php';

if (isset($_GET['t'])) {
    $t = intval($_GET['t']);
}

$gid = getDirectReference($_GET['gradebook_id']);
$gradebook_title = get_gradebook_title($gid);
$range = get_gradebook_range($gid);

$filename = $course_code . "_users_gradebook.xlsx";

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langResults);
$sheet->getDefaultColumnDimension()->setWidth(20);
$data = [];

if ($t == 1) { // download gradebook activities results
    $data[] = [ $langSurname, $langName, $langAm, $langUsername, $langGroups, $langEmail, $langGradebookGrade ];
    $data[] = []; // blank line
    $activities = Database::get()->queryArray("SELECT id, title FROM gradebook_activities WHERE gradebook_id = ?d", $gid);
    foreach ($activities as $act) {
        $title = !empty($act->title) ? $act->title : $langGradebookNoTitle;
        $data[] = [ $title ];
        $entries = Database::get()->queryArray("SELECT surname, givenname, username, am, email, gradebook_users.uid AS uid, grade
                    FROM gradebook_users
                    LEFT JOIN gradebook_book
                        ON gradebook_book.uid = gradebook_users.uid
                        AND gradebook_activity_id = ?d
                    JOIN user
                        ON user.id = gradebook_users.uid
                    WHERE gradebook_id = ?d
                    ORDER BY surname", $act->id, $gid);
        foreach ($entries as $item) {
            $user_group = user_groups($course_id, $item->uid, 'txt');
            if (!is_null($item->grade)) {
                $data[] = [$item->surname, $item->givenname, $item->am, $item->username, $user_group, $item->email, round($item->grade * $range, 2)];
            } else {
                $data[] = [ $item->surname, $item->givenname, $item->am, $item->username, $user_group, $item->email, $item->grade ];
            }
        }
        $data[] = []; // blank line
    }

    // format first row
    for ($i=1; $i<=6; $i++) {
        $cells = [$i, 1];
        $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
    }

    $header_style = [
        'font' => ['italic' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'color' => [Color::COLOR_DARKBLUE]
    ];

    if (isset($entries)) {
        $step = count($entries)+2;
        $total_entries = count($activities)*(count($entries)+2)+1;
        for ($i = 3; $i < $total_entries; $i+=$step) {
            $sheet->getStyle("A$i")->applyFromArray($header_style);
            $sheet->mergeCells("A$i:F$i");
        }
    }


} elseif ($t == 2) { // download gradebook users results
    // data header
    $data_header = [];
    // mapping of activity id's to output columns
    $actId = array();
    $actCounter = 0;
    $header1 = [ $langSurname, $langName, $langAm, $langUsername, $langGroups, $langEmail ];
    $activities = Database::get()->queryArray("SELECT id, title FROM gradebook_activities WHERE gradebook_id = ?d", $gid);
    foreach ($activities as $act) {
        $actId[$act->id] = $actCounter++;
        $activities_header[] = $act->title;
    }
    $header2 = [ $langGradebookTotalGrade ];
    $data_header = array_merge($header1, $activities_header, $header2);
    $columns = count($data_header);
    $data[] = $data_header;
    // user grades
    $range = get_gradebook_range($gid);
    $sql_users = Database::get()->queryArray("SELECT uid, givenname, surname, username, am, email
                                            FROM gradebook_users
                                            JOIN user
                                            ON user.id = gradebook_users.uid
                                            WHERE gradebook_id = ?d
                                            ORDER BY surname", $gid);
    foreach ($sql_users as $item) {
        $data_user_details = $data_user_grades = $data_user_grade_total = [];
        $user_group = user_groups($course_id, $item->uid, 'txt');
        array_push($data_user_details, $item->surname,
                                     $item->givenname,
                                     $item->am,
                                     $item->username,
                                     $user_group,
                                     $item->email);

        $sql_grades = Database::get()->queryArray("SELECT gradebook_activity_id, grade FROM gradebook_book
                                        JOIN gradebook_activities
                                            ON gradebook_activity_id = gradebook_activities.id
                                            AND gradebook_id = ?d
                                            AND uid = ?d", $gid, $item->uid);
        $data_user_grades = array_fill(0, $actCounter, '-');
        foreach ($sql_grades as $g) {
            $position = $actId[$g->gradebook_activity_id];
            $data_user_grades[$position] = round($g->grade * $range, 2); // activities grade
        }
        $data_user_grade_total = [ userGradeTotal($gid, $item->uid, true) ]; // total grade
        $data_user = array_merge($data_user_details, $data_user_grades, $data_user_grade_total);
        $data[] = $data_user;
    }


    // format first row
    for ($i=1; $i < $columns; $i++) {
        $cells = [$i, 1];
        $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
    }
    // format `total grade` column
    $sheet->getCell([$columns, 1])->getStyle()->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_RED);

} elseif ($t == 3) { // download gradebook activity results
    $activity_id = $_GET['activity_id'];

    $activity_title = get_gradebook_activity_title($gid, $activity_id);

    $data[] = [ $activity_title ];
    $data[] = [ $langSurname, $langName, $langAm, $langUsername, $langEmail, $langGradebookGrade ];
    $entries = Database::get()->queryArray("SELECT surname, givenname, username, am, email, gradebook_users.uid, grade
                    FROM gradebook_users
                    LEFT JOIN gradebook_book
                        ON gradebook_book.uid = gradebook_users.uid
                        AND gradebook_activity_id = ?d
                    JOIN user
                        ON user.id = gradebook_users.uid
                    WHERE gradebook_id = ?d
                    ORDER BY surname",
            $activity_id, $gid);
    foreach ($entries as $item) {
        if (!is_null($item->grade)) {
            $data[] = [ $item->surname, $item->givenname, $item->am, $item->username, $item->email, round($item->grade * $range, 2) ];
        } else {
            $data[] = [ $item->surname, $item->givenname, $item->am, $item->username, $item->email, $item->grade ];
        }
    }

    $header_style = [
        'font' => ['italic' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'color' => [Color::COLOR_DARKBLUE]
    ];
    // format first row
    for ($i=1; $i <= 6; $i++) {
        $cells = [$i, 1];
        $sheet->getCell($cells)->getStyle()->applyFromArray($header_style);
    }
    $sheet->mergeCells("A1:F1");
    // format second row
    for ($i=1; $i <= 6; $i++) {
        $cells = [$i, 2];
        $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
    }
}

// create spreadsheet
$sheet->fromArray($data, NULL);
// file output
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
set_content_disposition('attachment', $filename);
$writer->save("php://output");
