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

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';

$tree = new Hierarchy();
$user = new User();

if (isset($_GET['c'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($langUsage);
    $sheet->getDefaultColumnDimension()->setWidth(30);
    $filename = course_id_to_title($_GET['c']) . '.xlsx';

    $code = course_id_to_code(intval($_GET['c']));

    $u_date_start = $_GET['user_date_start'];
    $u_date_end = $_GET['user_date_end'];

    $name = Database::get()->querySingle("SELECT name FROM hierarchy, course, course_department WHERE hierarchy.id = course_department.department
                                     AND course_department.course = course.id AND course.id = ?d", $_GET['c'])->name;
    $data[] = [ $tree->unserializeLangField($name) ];

    $course = Database::get()->querySingle("SELECT title, prof_names, code, visible FROM course WHERE id = ?d", $_GET['c']);
    $users = Database::get()->querySingle("SELECT COUNT(user_id) AS users FROM course_user WHERE course_id = ?d", $_GET['c'])->users;
    $data[] = [];
    $message = $course->title . ' (' . $course->code . ')';
    $data[] = [ $message ];
    $data[] = [ $langCourseVis, course_status_message($_GET['c']) ];
    $data[] = [ $langTeacher, $course->prof_names ];
    $data[] = [ $langUsers, $users ];
    $data[] = [];
    $data[] = [ $langMonth, $langMonthlyCourseRegistrations ];

    $q2 = Database::get()->queryArray("SELECT COUNT(*) AS registrations, MONTH(reg_date) AS month, YEAR(reg_date) AS year FROM course_user
                WHERE course_id = ?d AND (reg_date BETWEEN ?s AND ?s)
                    AND status = " . USER_STUDENT . " GROUP BY month, year ORDER BY year, month ASC",
            $_GET['c'], $u_date_start, $u_date_end);
    foreach ($q2 as $item) {
        $data[] = [ $item->month . '-' . $item->year, $item->registrations ];
    }
    $data[] = [];
    $data[] = [ $langMonth, $langVisits, $langUsers ];
    $q3 = Database::get()->queryArray("SELECT MONTH(day) AS month, YEAR(day) AS year, COUNT(*) AS visits, COUNT(DISTINCT user_id) AS users FROM actions_daily
                        WHERE (day BETWEEN ?s AND ?s) AND course_id = ?d GROUP BY month, year ORDER BY year, month ASC", $u_date_start, $u_date_end, $_GET['c']);
    $total_visits = $total_users = 0;
    foreach ($q3 as $item) {
            $data[]= [ $item->month . '-' . $item->year, $item->visits, $item->users ];
            $total_visits += $item->visits;
            $total_users += $item->users;
    }
    $data[] = [ $langTotal, $total_visits, $total_users ];
    $data[] = [];
    $data[] = [ $langModule, $langVisits, $langUsers ];
    $q4 = Database::get()->queryArray("SELECT COUNT(*) AS cnt, module_id, COUNT(DISTINCT user_id) AS users FROM actions_daily
            WHERE (day BETWEEN ?s AND ?s) AND course_id = ?d
            GROUP BY module_id", $u_date_start, $u_date_end, $_GET['c']);
    foreach ($q4 as $item) {
        if ($item->module_id > 0) {
            if ($item->module_id == MODULE_ID_UNITS) { // course_units
                $mod_id = $static_modules[$item->module_id];
            } else {
                $mod_id = $modules[$item->module_id];
            }
            $data[] = [ $mod_id['title'], $item->cnt, $item->users ];
        }
    }

    $sheet->mergeCells("A1:C1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    $sheet->mergeCells("A3:C3");
    $sheet->getCell('A3')->getStyle()->getFont()->setBold(true);

// create spreadsheet
    $sheet->fromArray($data, NULL);

// file output
    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    set_content_disposition('attachment', $filename);
    $writer->save("php://output");
    exit;
}
