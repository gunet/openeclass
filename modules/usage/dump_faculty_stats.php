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

    $start = new DateTime($u_date_start);
    $end = new DateTime($u_date_end);

    $interval = new DateInterval('P1M'); // per month
    $period = new DatePeriod($start, $interval, $end);

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
    $data[] = [ $langMonth, $langTeachers, $langStudents, $langGuests, $langDoc, $langExercises, $langWorks, $langAnnouncements, $langMessages, $langForums ];

    foreach ($period as $dt) {
        $start = $dt->format('Y-m-d');
        $cnt_prof = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM course_user
                                                    WHERE course_id = ?d
                                                    AND status = " . USER_TEACHER . "
                                                    AND reg_date <= ?t",
            $_GET['c'], $start)->cnt;
        $cnt_students = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM course_user
                                                    WHERE course_id = ?d
                                                    AND status = " . USER_STUDENT . "
                                                    AND reg_date <= ?t",
            $_GET['c'], $start)->cnt;
        $cnt_guests = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM course_user
                                                    WHERE course_id = ?d
                                                    AND status = " . USER_GUEST . "
                                                    AND reg_date <= ?t",
            $_GET['c'], $start)->cnt;
        $cnt_documents = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM document
                                                    WHERE course_id = ?d
                                                    AND date <= ?t",
            $_GET['c'], $start)->cnt;
        $cnt_announcements = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM announcement
                                                    WHERE course_id = ?d
                                                    AND date <= ?t",
            $_GET['c'], $start)->cnt;
        $cnt_messages = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM dropbox_msg
                                                    WHERE course_id = ?d                                                    
                                                    AND FROM_UNIXTIME(timestamp, '%Y-%m-%d') <= ?t",
            $_GET['c'], $start)->cnt;
        $cnt_exercises = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM exercise WHERE course_id = ?d",
            $_GET['c'])->cnt;
        $cnt_assignments = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM assignment WHERE course_id = ?d",
            $_GET['c'])->cnt;
        $cnt_forum_posts = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM forum WHERE course_id = ?d",
            $_GET['c'])->cnt;
        $monthly_stats[] = [
            'start' => $dt->format('m-Y'),
            'prof' => $cnt_prof,
            'students' => $cnt_students,
            'guests' => $cnt_guests,
            'documents' => $cnt_documents,
            'announcements' => $cnt_announcements,
            'messages' => $cnt_messages,
            'exercises' => $cnt_exercises,
            'assignments' => $cnt_assignments,
            'forum_posts' => $cnt_forum_posts,
        ];
    }
    $output_data = $data + array_reverse($monthly_stats);
    $sheet->mergeCells("A1:J1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    $sheet->mergeCells("A3:J3");
    $sheet->getCell('A3')->getStyle()->getFont()->setBold(true);

    // create spreadsheet
    $sheet->fromArray($output_data, NULL);
    // file output
    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    set_content_disposition('attachment', $filename);
    $writer->save("php://output");
    exit;
}
