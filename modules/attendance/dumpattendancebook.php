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


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_editor = true;

include '../../include/baseTheme.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langAttendanceAbsences);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . "_list_attendance_users.xlsx";
$course_title = course_id_to_title($course_id);

$data[] = [ $course_title ];
$data[] = [];
$data[] = [ $langSurname, $langName, $langAm, $langUsername, $langEmail, $langAttendanceAbsences ];

if (isset($_GET['activity_id'])) {
    $activities = Database::get()->queryArray("SELECT id, title
            FROM attendance_activities WHERE attendance_id = ?d AND id = ?d",
                getDirectReference($_GET['attendance_id']), $_GET['activity_id']);
} else {
    $activities = Database::get()->queryArray("SELECT id, title
    FROM attendance_activities WHERE attendance_id = ?d",
        getDirectReference($_GET['attendance_id']));
}

foreach ($activities as $act) {
    $title = !empty($act->title) ? $act->title : $langGradebookNoTitle;
    $data[] = [ $title ];

    $entries = Database::get()->queryArray("SELECT surname, givenname, username, am, email, attendance_users.uid, attend
                    FROM attendance_users
                    LEFT JOIN attendance_book
                        ON attendance_book.uid = attendance_users.uid
                        AND attendance_activity_id = ?d
                    JOIN user
                        ON user.id = attendance_users.uid
                    WHERE attendance_id = ?d
                    ORDER BY surname, givenname", $act->id, getDirectReference($_GET['attendance_id']));

    foreach ($entries as $item) {
        $data[] = [ $item->surname, $item->givenname, $item->am, $item->username, $item->email, $item->attend ];
    }
    $data[] = [];
}

$header_style = [
    'font' => ['italic' => true],
    'color' => [ Color::COLOR_DARKBLUE ]
];

for ($j = 4; $j <= count($activities)*(count($entries)+1)+3; $j=$j+count($entries)+2) {
    $sheet->mergeCells("A$j:F$j");
    $cells = [1, $j];
    $sheet->getCell($cells)->getStyle()->applyFromArray($header_style);
}

$sheet->mergeCells("A1:F1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 6; $i++) {
    $cells = [$i, 3];
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
