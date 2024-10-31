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

$require_login = true;
require_once '../include/baseTheme.php';

// Feed of course information the current user has editor rights in for select2
// Combines courses where status == teacher with:
// For admin: all courses

$pageSize = 30;
$courselist = null;
if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 1;
}
$offset = $pageSize * ($page - 1);
$limit = "LIMIT $offset, $pageSize";

if (isset($_GET['term'])) {
    $q = '%' . $_GET['term'] . '%';

    if ($is_admin) {
        $courselist = Database::get()->queryArray("SELECT id, title, public_code
            FROM course LEFT JOIN course_user
                ON course.id = course_user.course_id AND course_user.user_id = ?d
            WHERE (title LIKE ?s OR code LIKE ?s OR public_code LIKE ?s)
            ORDER BY course_user.course_id IS NULL, title, public_code, code
            $limit", $uid, $q, $q, $q);
    } else {
        $courselist = Database::get()->queryArray("SELECT id, title, public_code
            FROM course JOIN course_user ON course.id = course_user.course_id
            WHERE course_user.user_id = ?d AND
                  (title LIKE ?s OR code LIKE ?s OR public_code LIKE ?s)
            ORDER BY title, public_code, code
            $limit", $uid, $q, $q, $q);
    }

} else {
    if ($is_admin) {
        $courselist = Database::get()->queryArray("SELECT id, title, public_code
            FROM course LEFT JOIN course_user
                ON course.id = course_user.course_id AND course_user.user_id = ?d
            ORDER BY course_user.course_id IS NULL, title, public_code, code
            $limit", $uid);
    } else {
        $courselist = Database::get()->queryArray("SELECT id, title, public_code
            FROM course JOIN course_user ON course.id = course_user.course_id
            WHERE course_user.user_id = ?d
            ORDER BY title, public_code, code
            $limit", $uid);
    }
}

$more = false;
if ($courselist) {
    if (count($courselist) == $pageSize) {
        $more = true;
    }
    foreach ($courselist as $course) {
        $courses[] = array('id' => $course->id, 'text' => $course->title . ' (' . $course->public_code . ')');
    }
} else {
    $courses[] = [];
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['results' => $courses, 'pagination' => ['more' => $more]], JSON_UNESCAPED_UNICODE);
