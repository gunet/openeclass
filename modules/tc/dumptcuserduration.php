<?php

$require_current_course = true;
$require_login = true;
$require_editor = true;

include '../../include/init.php';
require_once 'modules/tc/functions.php';
require_once 'include/lib/csv.class.php';

$csv = new CSV();
$csv->setEncoding('UTF-8');
$csv->filename = $course_code . '_bbb_duration.csv';

if (isset($_GET['meeting_id'])) {
    $meetingid = $_GET['meeting_id'];
} else {
    exit;
}

$csv->outputHeaders($langSurnameName, $langBBB, $langTotalDuration);

$result = Database::get()->queryArray("SELECT meetingid, bbbuserid, totaltime, date FROM tc_attendance
                                                WHERE tc_attendance.meetingid = ?s
                                            ORDER BY date DESC", $meetingid);

$temp_date = null;
foreach ($result as $row) {
    if ($row->date != $temp_date) {
        $csv->outputRecord(format_locale_date(strtotime($row->date)));
        $temp_date = $row->date;
    }
    $user_full_name = Database::get()->querySingle("SELECT fullName FROM tc_log
                            WHERE tc_log.bbbuserid = ?s ORDER BY id DESC LIMIT 1", $row->bbbuserid)->fullName;
    $tc_title = get_tc_title($row->meetingid);
    $csv->outputRecord($user_full_name, $tc_title, format_time_duration(0 + 60 * $row->totaltime));
}
