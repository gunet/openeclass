<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';

$tree = new Hierarchy();
$user = new User();

if (isset($_GET['c'])) {
    $name = course_id_to_title($_GET['c']).".csv";
    $code = course_id_to_code(intval($_GET['c']));
    $u_date_start = $_GET['user_date_start'];
    $u_date_end = $_GET['user_date_end'];
    $filename = "$code";
    if (isset($_GET['enc']) and $_GET['enc'] == 'w') {
        $charset = 'Windows-1253';
        $sendSep = true;
    } else {
        $charset = 'UTF-8';
        $sendSep = false;
    }

    $crlf = "\r\n";
    header("Content-Type: text/csv; charset=$charset");
    header("Content-Disposition: attachment; filename=\"".$name."\"");

    if ($sendSep) {
        echo 'sep=;', $crlf;
    }

    $name = Database::get()->querySingle("SELECT name FROM hierarchy, course, course_department WHERE hierarchy.id = course_department.department
                                     AND course_department.course = course.id AND course.id = ?d", $_GET['c'])->name;
    echo "$crlf" . $tree->unserializeLangField($name) . "$crlf";
    $course = Database::get()->querySingle("SELECT title, prof_names, code, visible FROM course WHERE id = ?d", $_GET['c']);
    $users = Database::get()->querySingle("SELECT COUNT(user_id) AS users FROM course_user WHERE course_id = ?d", $_GET['c'])->users;
    echo "$crlf" . csv_escape($course->title) . ";" . csv_escape($course->code);
    echo "$crlf" . csv_escape($langCourseVis). ";". csv_escape(course_status_message($_GET['c']));
    echo "$crlf" . csv_escape($langTeacher) . ";" . csv_escape($course->prof_names);
    echo "$crlf" . csv_escape($langUsers) . ";" .csv_escape($users);

    echo "$crlf $crlf" . csv_escape($langMonth) . ";" . csv_escape($langMonthlyCourseRegistrations);
    $q2 = Database::get()->queryArray("SELECT COUNT(*) AS registrations, MONTH(reg_date) AS month, YEAR(reg_date) AS year FROM course_user
                WHERE course_id = ?d AND (reg_date BETWEEN '$u_date_start' AND '$u_date_end')
                    AND status = " . USER_STUDENT . " GROUP BY month, year ORDER BY reg_date ASC", $_GET['c']);
    foreach ($q2 as $data) {
        echo "$crlf" . csv_escape($data->month) . "-" . csv_escape($data->year) . ";" . csv_escape($data->registrations);
    }

    echo "$crlf $crlf" . csv_escape($langMonth) . ";" . csv_escape($langVisits) . ";" . csv_escape($langUsers);
    $q3 = Database::get()->queryArray("SELECT COUNT(*) AS cnt, module_id, COUNT(DISTINCT user_id) AS users FROM actions_daily
            WHERE (day BETWEEN '$u_date_start' AND '$u_date_end') AND course_id = ?d
            GROUP BY module_id", $_GET['c']);
    foreach ($q3 as $data) {
        if ($data->module_id > 0) {
            if ($data->module_id == MODULE_ID_UNITS) { // course_units
                $mod_id = $static_modules[$data->module_id];
            } else {
                $mod_id = $modules[$data->module_id];
            }
            echo "$crlf";
            echo "".csv_escape($mod_id['title']).";".csv_escape($data->cnt).";".csv_escape($data->users)."";
        }
    }

    echo "$crlf $crlf" . csv_escape($langModule).";".csv_escape($langVisits).";".csv_escape($langUsers);
    $q3 = Database::get()->queryArray("SELECT COUNT(*) AS cnt, module_id, COUNT(DISTINCT user_id) AS users FROM actions_daily
            WHERE (day BETWEEN '$u_date_start' AND '$u_date_end') AND course_id = ?d
            GROUP BY module_id", $_GET['c']);
    foreach ($q3 as $data) {
        if ($data->module_id > 0) {
            if ($data->module_id == MODULE_ID_UNITS) { // course_units
                $mod_id = $static_modules[$data->module_id];
            } else {
                $mod_id = $modules[$data->module_id];
            }
            echo "$crlf";
            echo "".csv_escape($mod_id['title']).";".csv_escape($data->cnt) .";".csv_escape($data->users)."";
        }
    }
}
