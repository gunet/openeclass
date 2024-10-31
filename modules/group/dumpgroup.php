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
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'group_functions.php';

$group_id = intval($_REQUEST['group_id']);
initialize_group_info($group_id);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langUsers);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . '_group_' . $group_name . '.xlsx';

$data[] = [ $group_name ];
$data[] = [];
$data[] = [ $langSurname, $langName, $langEmail, $langAm, $langUsername ];

Database::get()->queryFunc("SELECT user.id, user.surname, user.givenname, user.email, user.am, user.username, group_members.is_tutor
                                FROM group_members, user
                                WHERE group_members.group_id = ?d AND
                                      group_members.user_id = user.id
                                ORDER BY user.surname, user.givenname",
    function ($item) use (&$data) {
        $data[] = [ $item->surname, $item->givenname, $item->email, $item->am, $item->username ];
    }, $group_id);

$sheet->mergeCells("A1:E1");
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
