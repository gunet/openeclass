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
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_course_reviewer = true;

include '../../include/baseTheme.php';
require_once 'process_functions.php';

if (isset($_GET['certificate_id'])) {
    $element = 'certificate';
    $element_id = "$_GET[certificate_id]";
} else {
    $element = 'badge';
    $element_id = "$_GET[badge_id]";
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langResults);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . "_users_progress_results.xlsx";
$course_title = course_id_to_title($course_id);

$data[] = [ "$course_title - " . get_cert_title($element, $element_id) . ""];
$data[] = [];
$data[] = [ $langSurname, $langName, $langAm, $langUsername, $langEmail, $langProgress, $langCompletedIn ];

$sql = Database::get()->queryArray("SELECT user.surname, user.givenname, user, completed, completed_criteria, total_criteria, assigned
                                            FROM user_{$element}
                                            JOIN course_user ON user_{$element}.user = course_user.user_id
                                             JOIN user ON user.id = user_{$element}.user
                                                AND course_user.status = " .USER_STUDENT . "
                                                AND editor = 0
                                                AND course_id = ?d
                                                AND $element = ?d
                                            ORDER BY user.surname, user.givenname
                                            ASC", $course_id, $element_id);

foreach ($sql as $user_data) {
    $data[] = [ uid_to_name($user_data->user, 'surname'),
                uid_to_name($user_data->user, 'givenname'),
                uid_to_am($user_data->user),
                uid_to_name($user_data->user, 'username'),
                uid_to_email($user_data->user),
                round($user_data->completed_criteria / $user_data->total_criteria * 100, 0) . '%',
                format_locale_date(strtotime($user_data->assigned), 'short')
               ];
}

$sheet->mergeCells("A1:G1");
$sheet->getCell('A1')->getStyle()->getFont()->setBold(true)->setSize(13);
for ($i = 1; $i <= 7; $i++) {
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
