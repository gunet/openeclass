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
        ]);
        if (!$ok) {
            Access::error(2, 'Required parameters for user unenrolement missing: user_id, course_id');
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
        Database::get()->query("DELETE FROM course_user
            WHERE course_id = ?d AND user_id = ?d",
            $course->id, $user->id);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
        exit();
    } else {
        Access::error(2, 'Required POST parameters for user enrolement missing: user_id, course_id, [role_id = {student|teacher|teacher_assistant}]');
    }
}

chdir('..');
require_once 'apiCall.php';
