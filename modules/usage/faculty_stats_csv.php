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
require_once 'include/lib/csv.class.php';

$tree = new Hierarchy();
$user = new User();

if (isset($_GET['c'])) {
    $csv = new CSV();
    if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
        $csv->setEncoding('UTF-8');
    }
    $csv->filename = course_id_to_title($_GET['c']) . '.csv';
    $code = course_id_to_code(intval($_GET['c']));
    $u_date_start = $_GET['user_date_start'];
    $u_date_end = $_GET['user_date_end'];

    $name = Database::get()->querySingle("SELECT name FROM hierarchy, course, course_department WHERE hierarchy.id = course_department.department
                                     AND course_department.course = course.id AND course.id = ?d", $_GET['c'])->name;
    $csv->outputRecord($tree->unserializeLangField($name));
    $course = Database::get()->querySingle("SELECT title, prof_names, code, visible FROM course WHERE id = ?d", $_GET['c']);
    $users = Database::get()->querySingle("SELECT COUNT(user_id) AS users FROM course_user WHERE course_id = ?d", $_GET['c'])->users;
    $csv->outputRecord($course->title, $course->code)
        ->outputRecord($langCourseVis, course_status_message($_GET['c']))
        ->outputRecord($langTeacher, $course->prof_names)
        ->outputRecord($langUsers, $users)
        ->outputRecord()
        ->outputRecord($langMonth, $langMonthlyCourseRegistrations);

    $q2 = Database::get()->queryArray("SELECT COUNT(*) AS registrations, MONTH(reg_date) AS month, YEAR(reg_date) AS year FROM course_user
                WHERE course_id = ?d AND (reg_date BETWEEN ?s AND ?s)
                    AND status = " . USER_STUDENT . " GROUP BY month, year ORDER BY reg_date ASC",
            $_GET['c'], $u_date_start, $u_date_end);
    foreach ($q2 as $data) {
        $csv->outputRecord($data->month . '-' . $data->year, $data->registrations);
    }

    $csv->outputRecord()->outputRecord($langMonth, $langVisits, $langUsers);
    $q3 = Database::get()->queryArray("SELECT COUNT(*) AS cnt, module_id, COUNT(DISTINCT user_id) AS users FROM actions_daily
            WHERE day BETWEEN ?s AND ?s AND course_id = ?d
            GROUP BY module_id", $u_date_start, $u_date_end, $_GET['c']);
    foreach ($q3 as $data) {
        if ($data->module_id > 0) {
            if ($data->module_id == MODULE_ID_UNITS) { // course_units
                $mod_id = $static_modules[$data->module_id];
            } else {
                $mod_id = $modules[$data->module_id];
            }
            $csv->outputRecord($mod_id['title'], $data->cnt, $data->users);
        }
    }

    $csv->outputRecord()->outputRecord($langModule, $langVisits, $langUsers);
    $q3 = Database::get()->queryArray("SELECT COUNT(*) AS cnt, module_id, COUNT(DISTINCT user_id) AS users FROM actions_daily
            WHERE (day BETWEEN ?s AND ?s) AND course_id = ?d
            GROUP BY module_id", $u_date_start, $u_date_end, $_GET['c']);
    foreach ($q3 as $data) {
        if ($data->module_id > 0) {
            if ($data->module_id == MODULE_ID_UNITS) { // course_units
                $mod_id = $static_modules[$data->module_id];
            } else {
                $mod_id = $modules[$data->module_id];
            }
            $csv->outputRecord($mod_id['title'], $data->cnt, $data->users);
        }
    }
}

