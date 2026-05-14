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

$require_current_course = true;
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'modules/questionnaire/functions.php';
require_once 'modules/session/functions.php';

// Check if uid is the coordinator or the consultant of the current session.
if (isset($_GET['session']) && !$is_coordinator && !is_session_consultant(intval($_GET['session']),$course_id)) {
    Session::flash('message', $langForbidden);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/session/index.php?course=$course_code"); 
}

$sessionInfo = Database::get()->querySingle("SELECT * FROM mod_session WHERE id = ?d AND course_id = ?d", intval($_GET['session']), $course_id);
$title = $sessionInfo->title;
$consultantName = uid_to_name($sessionInfo->creator);
$startSession = date('d-m-Y H:i', strtotime($sessionInfo->start));
$endSession = date('d-m-Y H:i', strtotime($sessionInfo->finish));

$heading = array(
    $langName,
    $langTitle,
    $langConsultant,
    $langStartSession,
    $langFinishSession,
    $langCompletionResources,
    $langPercentageSessionCompletion
);
$data[] = $heading;
// Data row

$resources = strip_tags(session_completed_resources_by_user($sessionInfo->id, $course_id, intval($_GET['u'])));
$new = str_replace('&#x2718;', '(OXI)', $resources);
$new2 = str_replace('&#10004;', '(NAI)', $new);

$data[] = array(
    uid_to_name(intval($_GET['u'])),
    $title,
    $consultantName,
    $startSession,
    $endSession,
    $new2,
    intval($_GET['per']) . '%'
);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langResults);
// Default column width
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . '__' . $title . '__' . uid_to_name(intval($_GET['u'])) . '.xlsx';
// Γράφει τα δεδομένα
$sheet->fromArray($data, null, 'A1');
// Υπολογίζει τελευταία στήλη
$lastColumn = Coordinate::stringFromColumnIndex(count($data[0]));
// Bold στα headers
$sheet->getStyle("A1:{$lastColumn}1")
      ->getFont()
      ->setBold(true);
// Italic στο header row
$sheet->getStyle("A1:{$lastColumn}1")
      ->getFont()
      ->setItalic(true);
// Auto size columns
foreach (range('A', $lastColumn) as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}
// File output
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;