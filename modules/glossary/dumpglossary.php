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
$require_editor = true;

require_once '../../include/baseTheme.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langGlossary);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . '_glossary.xlsx';
$course_title = course_id_to_title($course_id);

$data[] = [ $course_title ];
$data[] = [];
$data[] = [ $langGlossaryTerm, $langGlossaryDefinition, $langGlossaryUrl ];

$sql = Database::get()->queryFunc("SELECT term, definition, url FROM glossary
                            WHERE course_id = ?d
                            ORDER BY `order`",
            function ($item) use (&$data) {
                $data[] = [ $item->term, $item->definition, $item->url ];
            }, $course_id);

$sheet->mergeCells("A1:C1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 3; $i++) {
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
