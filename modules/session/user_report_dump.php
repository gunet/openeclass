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

if (!isset($_GET['session']) or !isset($_GET['u'])) {
    Session::flash('message', $langForbidden);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/session/index.php?course=$course_code"); 
}

// Check if uid is the coordinator or the consultant of the current session.
if (isset($_GET['session']) && !$is_coordinator && !is_session_consultant(intval($_GET['session']),$course_id)) {
    Session::flash('message', $langForbidden);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/session/index.php?course=$course_code"); 
}

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
    $langDate,
    $langStartSession,
    $langFinishSession,
    $langCompletionResources,
    $langPercentageSessionCompletion
];

$data[] = $heading;

// =========================================
// Resources processing
// =========================================

$resources = session_completed_resources_by_user(
    (int)$_GET['session'],
    $course_id,
    (int)$_GET['u']
);

// Remove HTML tags
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
$consultantName = str_replace('&nbsp;', ' ', $user_information['tutor']);
// =========================================
// Data row
// =========================================

$data[] = [
    (string) uid_to_name((int)$_GET['u']),
    (string) $user_information['title'],
    (string) $consultantName,
    (string) $user_information['date'],
    (string) $user_information['start_date'],
    (string) $user_information['end_date'],
    (string) $resources,
    (string) ($user_information['percentage'] . '%')
];

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
// Wrap text
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

$filename = preg_replace(
    '/[^\p{L}\p{N}_\-]/u',
    '_',
    $course_code .
    '_____ΠΑΡΟΥΣΙΟΛΟΓΙΟ_____' .
    $user_information['title'] .
    '_____' .
    uid_to_name((int)$_GET['u'])
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

header('Expires: 0');

header('Pragma: public');

// =========================================
// Save file
// =========================================

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;