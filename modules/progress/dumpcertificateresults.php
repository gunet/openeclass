<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_editor = true;

include '../../include/init.php';
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

$data[] = [ get_cert_title($element, $element_id) ];
$data[] = [];
$data[] = [ $langSurname, $langName, $langAm, $langUsername, $langEmail, $langProgress ];

$sql = Database::get()->queryArray("SELECT user, completed, completed_criteria, total_criteria FROM user_{$element}
                                        WHERE $element = ?d", $element_id);

foreach ($sql as $user_data) {
    $data[] = [ uid_to_name($user_data->user, 'surname'),
                uid_to_name($user_data->user, 'givenname'),
                uid_to_am($user_data->user),
                uid_to_name($user_data->user, 'username'),
                uid_to_email($user_data->user),
                round($user_data->completed_criteria / $user_data->total_criteria * 100, 0) . '%'
               ];
}

$sheet->mergeCells("A1:F1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 6; $i++) {
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
