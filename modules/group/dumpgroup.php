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

require_once '../../include/init.php';
require_once 'group_functions.php';
require_once 'include/lib/csv.class.php';

$group_id = intval($_REQUEST['group_id']);
initialize_group_info($group_id);

$csv = new CSV();

if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}
$csv->filename = $course_code . '_group_' . $group_name . '.csv';

// dump group users
if (isset($_GET['u']) and $_GET['u'] == 1) {
    $csv->outputRecord($group_name);
    $csv->outputRecord($langSurname, $langName, $langEmail, $langAm, $langUsername);
    Database::get()->queryFunc("SELECT user.id, user.surname, user.givenname, user.email, user.am, user.username, group_members.is_tutor
                                    FROM group_members, user
                                    WHERE group_members.group_id = ?d AND
                                          group_members.user_id = user.id
                                    ORDER BY user.surname, user.givenname",
        function ($item) use ($csv) {
            $csv->outputRecord($item->surname, $item->givenname, $item->email, $item->am, $item->username);
        }, $group_id);
} else {
    // dump group users duration
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
    $csv->outputRecord($first_line)->outputRecord($langSurname, $langName, $langAm, $langGroup, $langDuration);
    $totalDuration = 0;

    $result = user_duration_query($course_id, $u_date_start, $u_date_end, $group_id);

    foreach ($result as $row) {
        $csv->outputRecord($row->surname, $row->givenname, $row->am,
            $group_name, format_time_duration(0 + $row->duration),
            round($row->duration / 3600));
    }
}

