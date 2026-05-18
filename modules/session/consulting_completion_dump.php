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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Check if uid is the coordinator or the consultant of the course.
$check = false;
if (!$is_coordinator && !$is_consultant) {
    $check = true;
} elseif (!isset($_GET['format']) or (isset($_GET['format']) && $_GET['format'] != 'excel')) {
    $check = true;
}

if ($check) {
    Session::flash('message', $langForbidden);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/session/index.php?course=$course_code"); 
}


///////////////////////////////////////////////////////////////////////////////////////////////////////////

// =========================================
// Initialize data array
// =========================================

$data = [];

// =========================================
// Header row
// =========================================

$heading = [
    $langName,
    $langSSession,
    $langConsultant,
    $langStartSession,
    $langFinishSession,
    $langCompletionResources,
    $langPercentageSessionCompletion
];

$data[] = $heading;

// =========================================
// Data rows
// =========================================

foreach ($users_actions as $user_key => $session) {

    foreach ($session as $s) {

        $resources = session_completed_resources_by_user(
            $s->id,
            $course_id,
            $user_key
        );

        // Remove HTML
        $resources = strip_tags($resources);

        // Decode HTML entities
        $resources = html_entity_decode(
            $resources,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        // Replace symbols
        $resources = str_replace('✘', '(OXI)', $resources);
        $resources = str_replace('✔', '(NAI)', $resources);

        $data[] = [
            (string) uid_to_name((int)$user_key),
            (string) $s->title,
            (string) uid_to_name((int)$s->creator),
            (string) $s->start,
            (string) $s->finish,
            (string) $resources,
            (string) ($s->percentage . '%')
        ];
    }
}

// =========================================
// Create spreadsheet
// =========================================

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle($langResults);

// =========================================
// Insert data
// =========================================

$sheet->fromArray($data, null, 'A1');

// =========================================
// Header styling
// =========================================

$lastColumn = Coordinate::stringFromColumnIndex(count($heading));

$sheet->getStyle("A1:{$lastColumn}1")
    ->getFont()
    ->setBold(true);

$sheet->getStyle("A1:{$lastColumn}1")
    ->getFont()
    ->setItalic(true);

// =========================================
// Auto-size columns
// =========================================

for ($col = 1; $col <= count($heading); $col++) {

    $column = Coordinate::stringFromColumnIndex($col);

    $sheet->getColumnDimension($column)
        ->setAutoSize(true);
}

// =========================================
// Wrap text for all cells
// =========================================

$lastRow = count($data);

$sheet->getStyle("A1:{$lastColumn}{$lastRow}")
    ->getAlignment()
    ->setWrapText(true);

// =========================================
// Freeze header row
// =========================================

$sheet->freezePane('A2');

// =========================================
// Safe filename
// =========================================
$fileTitle = isset($_GET['user_rep']) ? (string) uid_to_name($_GET['user_rep']) : $langAll;
$filename = preg_replace(
    '/[^\p{L}\p{N}_\-]/u',
    '_____',
    $course_code . '_____' .
    $langReportAttendances . '_____' .
    $fileTitle
);

$filename .= '.xlsx';

// =========================================
// Clear output buffer
// =========================================

if (ob_get_length()) {
    ob_end_clean();
}

// =========================================
// Output headers
// =========================================

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header('Cache-Control: max-age=0');

// =========================================
// Save file
// =========================================

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;