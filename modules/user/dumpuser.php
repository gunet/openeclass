<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

$require_current_course = true;
$require_course_admin = true;

include '../../include/init.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langUsers);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . '_users.xlsx';
$course_title = course_id_to_title($course_id);

$data[] = [ $course_title ];
$data[] = [];
$data[] = [ $langSurname, $langName, $langEmail, $langAm, $langUsername, $langRegistrationDate, $langGroups ];

$sql = Database::get()->queryFunc("SELECT user.id, user.surname, user.givenname, user.email, user.am, user.username, course_user.reg_date
                        FROM course_user, user
                        WHERE `user`.`id` = `course_user`.`user_id` AND
                              `course_user`.`course_id` = ?d
                        ORDER BY user.surname, user.givenname",
            function ($item) use (&$data, $course_id) {
                    $ug = user_groups($course_id, $item->id, 'txt');
                    $data[] =  [ $item->surname, $item->givenname, $item->email, $item->am, $item->username, $item->reg_date, $ug ];
            }, $course_id);

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
