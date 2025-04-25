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

$require_admin = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'modules/admin/hierarchy_validations.php';

load_js('tools.js');
load_js('bootstrap-datetimepicker');

$tree = new Hierarchy();
$user = new User();
$toolName = $langAdmin;
$pageName = $langStatOfFaculty;

$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$navigation[] = array("url" => "index.php?t=a", "name" => $langUsage);

load_js('jstree3');

if (isDepartmentAdmin()) {
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
} else {
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false));
}
$head_content .= $js;
$data['html'] = $html;

if (isset($_GET['user_date_start'])) {
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_GET['user_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');
    $user_date_start = $uds->format('d-m-Y H:i');
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P2Y'));
    $u_date_start = $date_start->format('Y-m-d H:i');
    $user_date_start = $date_start->format('d-m-Y H:i');
}
if (isset($_GET['user_date_end'])) {
    $ude = DateTime::createFromFormat('d-m-Y H:i', $_GET['user_date_end']);
    $u_date_end = $ude->format('Y-m-d H:i');
    $user_date_end = $ude->format('d-m-Y H:i');
} else {
    $date_end = new DateTime();
    $date_start->sub(new DateInterval('P1M'));
    $u_date_end = $date_end->format('Y-m-d H:i');
    $user_date_end = $date_end->format('d-m-Y H:i');
}

if (isset($_GET['stats_submit'])) {
    if (isset($_GET['formsearchfaculte'])) {
        $searchfaculte = intval($_GET['formsearchfaculte']);
        if ($searchfaculte) {
            $subs = $tree->buildSubtrees(array($searchfaculte));
            $ids = 0;
            foreach ($subs as $key => $id) {
                $terms[] = $id;
                $ids++;
            }
            $query = ' AND hierarchy.id IN (' . implode(', ', array_fill(0, $ids, '?d')) . ')';
        } else {
            $query = $terms = '';
        }
    }

    // only one course
    if (isset($_GET['c'])) {
        $navigation[] = array("url" => "faculty_stats.php?formsearchfaculte=1&user_date_start=$_GET[user_date_start]&user_date_end=$_GET[user_date_end]&stats_submit=true", "name" => $langStatOfFaculty);
        $pageName = $langStatsCourse;
        $month_stats =  [];
        $start = new DateTime($u_date_start);
        $end = new DateTime($u_date_end);

        $interval = new DateInterval('P1M'); // per month
        $period = new DatePeriod($start, $interval, $end);

        $name = Database::get()->querySingle("SELECT name FROM hierarchy, course, course_department WHERE hierarchy.id = course_department.department
                                         AND course_department.course = course.id AND course.id = ?d", $_GET['c'])->name;
        $data['name'] = $tree->unserializeLangField($name);
        $data['course'] = $course = Database::get()->querySingle("SELECT title, prof_names, code, visible FROM course WHERE id = ?d", $_GET['c']);
        $data['users'] = Database::get()->querySingle("SELECT COUNT(user_id) AS users FROM course_user WHERE course_id = ?d", $_GET['c'])->users;

        $data['visibility_icon'] = course_access_icon($course->visible);
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
            $month_stats[] = [
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
        $data['month_stats'] = array_reverse($month_stats);

    } else { // courses list
        if (!empty($query)) {
            $data['s'] = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course, course_department, hierarchy
                                            WHERE course.id = course_department.course
                                            AND hierarchy.id = course_department.department
                                            $query", $terms)->total;
        } else { // get all courses
            $data['s'] = Database::get()->querySingle("SELECT COUNT(*) AS total FROM course, course_department, hierarchy
                                            WHERE course.id = course_department.course
                                            AND hierarchy.id = course_department.department")->total;
        }
        $data['all'] = Database::get()->querySingle("SELECT COUNT(*) AS num_of_courses FROM course")->num_of_courses;

        if (!empty($query)) {
            $data['sql'] = Database::get()->queryArray("SELECT course.id, course.code, course.visible, title, prof_names, DATE_FORMAT(created, '%d-%m-%Y %h:%m') AS creation_time
                                            FROM course, course_department, hierarchy
                                                WHERE course.id = course_department.course
                                                AND hierarchy.id = course_department.department $query
                                                ORDER by creation_time DESC", $terms);
        } else { // get all courses
            $data['sql'] = Database::get()->queryArray("SELECT course.id, course.code, course.visible, title, prof_names, DATE_FORMAT(created, '%d-%m-%Y %h:%m') AS creation_time
                                FROM course, course_department, hierarchy
                                    WHERE course.id = course_department.course
                                    AND hierarchy.id = course_department.department
                                    ORDER by creation_time DESC");
        }
    }
}

$data['u_date_start'] = $u_date_start;
$data['u_date_end'] = $u_date_end;
$data['user_date_start'] = $user_date_start;
$data['user_date_end'] = $user_date_end;

view('admin.other.stats.faculty_stats', $data);
