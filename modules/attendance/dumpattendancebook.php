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

$require_current_course = true;
$require_editor = true;

include '../../include/init.php';
require_once 'include/lib/csv.class.php';

$csv = new CSV();
if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . "_list_attendance_users.csv";

$activities = Database::get()->queryArray("SELECT id, title
    FROM attendance_activities WHERE attendance_id = ?d",
    getDirectReference($_GET['attendance_id']));
foreach ($activities as $act) {
    $title = !empty($act->title) ? $act->title : $langGradebookNoTitle;
    $csv->outputRecord($title)
        ->outputRecord($langSurname, $langName, $langAm, $langUsername, $langEmail, $langAttendanceAbsences);
    $entries = Database::get()->queryArray("SELECT surname, givenname, username, am, email, attend
        FROM attendance_book, user
        WHERE attendance_book.uid = user.id AND attendance_activity_id = ?d", $act->id);
    foreach ($entries as $item) {
        $csv->outputRecord($item->surname, $item->givenname, $item->am, $item->username, $item->email, $item->attend);
    }
    $csv->outputRecord();
}
