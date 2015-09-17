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

$require_current_course = TRUE;
$require_course_admin = TRUE;

require_once '../../include/init.php';
require_once 'group_functions.php';
require_once 'modules/usage/duration_query.php';

$group_id = intval($_REQUEST['group_id']);
initialize_group_info($group_id);

if (!$is_editor and !$is_tutor) {
    header('Location: group_space.php?course=' . $course_code . '&group_id=' . $group_id);
    exit;
}
    
if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
    $sendSep = true;
} else {
    $charset = 'UTF-8';
    $sendSep = false;
}
$crlf = "\r\n";

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=groupuserduration.csv");

if ($sendSep) {
    echo 'sep=;', $crlf;
}
if (isset($_REQUEST['u_date_start']) and isset($_REQUEST['u_date_end'])) {
    $u_date_start = $_REQUEST['u_date_start'];
    $u_date_end = $_REQUEST['u_date_end'];
} else {
    $min_date = Database::get()->querySingle("SELECT MIN(day) AS minday FROM actions_daily WHERE course_id = ?d", $course_id)->minday;        
    $u_date_start = strftime('%Y-%m-%d', strtotime($min_date));
    $u_date_end = strftime('%Y-%m-%d', strtotime('now'));
}

if (isset($u_date_start) and isset($u_date_end)) {
    $first_line = "$langFrom $u_date_start $langAs $u_date_end";
} else {
    $date_spec = '';
}
echo csv_escape($first_line), $crlf, $crlf,
join(';', array_map("csv_escape", array($langSurname, $langName, $langAm, $langGroup, $langDuration))),
$crlf;
$totalDuration = 0;

$result = user_duration_query($course_id, $u_date_start, $u_date_end, $group_id);

foreach ($result as $row) {
    echo csv_escape($row->surname) . ";" .
    csv_escape($row->givenname) . ";" .
    csv_escape($row->am) . ";" .
    csv_escape($group_name) . ";" .
    csv_escape(format_time_duration(0 + $row->duration)) . ";" .
    csv_escape(round($row->duration / 3600));
    echo $crlf;
}

