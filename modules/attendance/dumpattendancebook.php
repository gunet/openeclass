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
$require_course_admin = true;

include '../../include/init.php';

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
} else {
    $charset = 'UTF-8';
}
$crlf = "\r\n";

if (!$is_editor) {
    Session::Messages($langForbidden);
    redirect_to_home_page('modules/glossary/index.php?course=' . $course_code);
}

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=listattendanceusers.csv");


$sql = Database::get()->queryArray("SELECT id, title FROM attendance_activities WHERE attendance_id = ?d", $_GET['attendance_id']);
foreach ($sql as $act) {
    $title = !empty($act->title) ? $act->title : $langGradebookNoTitle;
    echo csv_escape($title). "$crlf";
    echo join(';', array_map("csv_escape", array($langSurname, $langName, $langAm, $langUsername, $langEmail, $langAttendanceAbsences)));
    echo $crlf;
    $sql2 = Database::get()->queryArray("SELECT uid, attend FROM attendance_book WHERE attendance_activity_id = ?d", $act->id);
    foreach ($sql2 as $u) {
        $userdata = Database::get()->querySingle("SELECT surname, givenname, username, am, email FROM user WHERE id = ?d", $u->uid);
        echo join(';', array_map("csv_escape", array($userdata->surname, $userdata->givenname, $userdata->am, $userdata->username, $userdata->email, $u->attend)));
        echo "$crlf";
    }
    echo "$crlf";
    echo "$crlf";
}
