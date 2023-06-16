<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_login = true;
$require_editor = true;

include '../../include/init.php';
require_once 'modules/tc/functions.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langUsers);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . '_bbb_duration.xlsx';
$course_title = course_id_to_title($course_id);

if (isset($_GET['meeting_id'])) {
    $meetingid = $_GET['meeting_id'];
} else {
    exit;
}

$data[] = [ $course_title ];
$data[] = [];
$data[] = [ $langSurnameName, $langBBB, $langTotalDuration ];

$result = Database::get()->queryArray("SELECT meetingid, bbbuserid, totaltime, date FROM tc_attendance
                                                WHERE tc_attendance.meetingid = ?s
                                            ORDER BY date DESC", $meetingid);
$temp_date = null;
foreach ($result as $row) {
    if ($row->date != $temp_date) {
        $data[] = [];
        $data[] =  [ format_locale_date(strtotime($row->date), 'full') ];
        $temp_date = $row->date;
    }
    $user_full_name = Database::get()->querySingle("SELECT fullName FROM tc_log
                            WHERE tc_log.bbbuserid = ?s ORDER BY id DESC LIMIT 1", $row->bbbuserid)->fullName;
    $tc_title = get_tc_title($row->meetingid);
    $data[] = [ $user_full_name, $tc_title, format_time_duration(0 + 60 * $row->totaltime) ];
}


$sheet->mergeCells("A1:C1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 3; $i++) {
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
