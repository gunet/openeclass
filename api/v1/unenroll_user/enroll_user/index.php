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

function api_method($access) {
    global $user_id, $course_id, $role_id;

    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ok = register_posted_variables([
            'user_id' => true,
            'course_id' => true,
            'role_id' => false,
        ]);
        if (!$ok) {
            Access::error(2, 'Required parameters for user enrolement missing: user_id, course_id, [role_id = {student|teacher|teacher_assistant}]');
        }
        $user = Database::get()->querySingle('SELECT id FROM user WHERE id = ?d', $user_id);
        if (!$user) {
            Access::error(3, "User with id '$user_id' not found");
        }
        $course = Database::get()->querySingle('SELECT id, code
            FROM course WHERE code = ?s', $_POST['course_id']);
        if (!$course) {
            Access::error(3, "Course with id '$course_id' not found");
        }
        $is_editor = 0;
        if (!$role_id) {
            $role_id = USER_STUDENT;
        } else {
            if ($role_id == 'teacher_assistant') {
                $is_editor = 1;
            }
            $role_id = ($role_id == 'teacher'? USER_TEACHER: USER_STUDENT);
        }
        Database::get()->query("INSERT INTO course_user
            SET course_id = ?d, user_id = ?d, status = ?d, editor = ?d,
                reg_date = NOW(), receive_mail = 1, document_timestamp = NOW()
            ON DUPLICATE KEY UPDATE status = ?d, editor = ?d",
                $course->id, $user->id, $role_id, $is_editor, $role_id, $is_editor);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
        exit();
    } else {
        Access::error(2, 'Required POST parameters for user enrolement missing: user_id, course_id, [role_id = {student|teacher|teacher_assistant}]');
    }
}

chdir('..');
require_once 'index.php';
