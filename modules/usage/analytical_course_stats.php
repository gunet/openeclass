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

$require_admin = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';

$navigation[] = ['url' => '../admin/index.php', 'name' => $langAdmin];
$navigation[] = ['url' => 'index.php?t=a', 'name' => $langUsage];

$toolName = $langAdmin;
$pageName = $langDetails;

$tree = new Hierarchy();

$tool_content .= "<div class='table-responsive'>
                    <table class='table-default table-logs'>
                    <thead><tr class='list-header'>
                        <th>$langFaculties</th>
                        <th>$langTeachers</th>
                        <th>$langStudents</th>
                        <th>$langCourses</th>
                        <th>$langAnnouncements</th>
                        <th>$langMessages</th>
                        <th>$langDoc</th>
                        <th>$langExercises</th>
                        <th>$langWorks</th>
                    </tr></thead>";

$r = $tree->buildRootIdsArray();

foreach ($r as $data) {
    $q = Database::get()->queryArray("SELECT id, name FROM hierarchy WHERE id = $data ORDER BY lft");

    foreach ($q as $faculty) {
        $deps = $faculty->id;
        $name = $tree->getNodeName($deps);
        $subs = $tree->buildSubtrees(array($deps));
        foreach ($subs as $dep_id) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>" . $tree->getNodeName($dep_id). "</td>";
            $stats = faculty_stats($dep_id);
            foreach ($stats as $data) {
                $tool_content .= "<td>$data</td>";
            }
            $tool_content .= "</tr>";
        }
    }
}
$tool_content.= "</table></div>";

draw($tool_content, 3, null, $head_content);

/**
 * @brief calculate statistics for a given department
 * @param $fac_id
 * @return array
 */
function faculty_stats($fac_id) {

    $cnt_users_teacher = Database::get()->querySingle("SELECT COUNT(DISTINCT user.id) AS cnt
             FROM user, user_department
            WHERE status <> " . USER_TEACHER . "
              AND expires_at > NOW()
              AND user.id = user_department.user
              AND department = ?d", $fac_id)->cnt;

    $cnt_users_student = Database::get()->querySingle("SELECT COUNT(DISTINCT user.id) AS cnt
             FROM user, user_department
            WHERE status <> " . USER_STUDENT . "
              AND expires_at > NOW()
              AND user.id = user_department.user
              AND department = ?d", $fac_id)->cnt;

    $cnt_courses = Database::get()->querySingle("SELECT COUNT(DISTINCT course.id) AS cnt
         FROM course, course_department              
        WHERE visible <> " . COURSE_INACTIVE . "
          AND course = course.id
          AND course_department.department = ?d", $fac_id)->cnt;

    $cnt_announcements = Database::get()->querySingle("SELECT COUNT(*) AS cnt
         FROM course, course_department, announcement
        WHERE course.visible <> " . COURSE_INACTIVE . "
          AND course = course.id
          AND announcement.course_id = course.id
          AND announcement.visible = 1
          AND department = ?d", $fac_id)->cnt;

    $cnt_messages = Database::get()->querySingle("SELECT COUNT(*) AS cnt
         FROM course, course_department, dropbox_msg
        WHERE course.visible <> " . COURSE_INACTIVE . "
          AND dropbox_msg.timestamp > unix_timestamp('2020-06-30')
          AND course = course.id
          AND dropbox_msg.course_id = course.id
          AND department = ?d", $fac_id)->cnt;

    $cnt_documents = Database::get()->querySingle("SELECT COUNT(*) AS cnt
         FROM course, course_department, document
        WHERE course.visible <> " . COURSE_INACTIVE . "
          AND course = course.id
          AND document.course_id = course.id
          AND department = ?d", $fac_id)->cnt;

    $cnt_exercises = Database::get()->querySingle("SELECT COUNT(*) AS cnt
         FROM course, course_department, exercise
        WHERE course.visible <> " . COURSE_INACTIVE . "
          AND course = course.id
          AND exercise.course_id = course.id
          AND department = ?d", $fac_id)->cnt;

    $cnt_assignments = Database::get()->querySingle("SELECT COUNT(*) AS cnt
         FROM course, course_department, assignment
        WHERE course.visible <> " . COURSE_INACTIVE . "
          AND course = course.id
          AND assignment.course_id = course.id
          AND department = ?d", $fac_id)->cnt;

    $data = [ $cnt_courses, $cnt_users_teacher, $cnt_users_student, $cnt_announcements, $cnt_messages, $cnt_documents, $cnt_exercises, $cnt_assignments ];

    return $data;
}
