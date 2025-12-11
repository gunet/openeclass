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
    global $user_id, $group_id, $course_id, $role_id;

    $help_text = 'Required parameters for user enrolement missing: user_id, {course_id|group_id}, [role_id = {student|teacher|teacher_assistant}]';
    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ok = register_posted_variables([
            'user_id' => true,
            'course_id' => false,
            'group_id' => false,
            'role_id' => false,
        ]);
        if (!$group_id && !$course_id) {
            $ok = false;
        }
        if (!$ok) {
            Access::error(2, $help_text);
        }
        $user = Database::get()->querySingle('SELECT id FROM user WHERE id = ?d', $user_id);
        if (!$user) {
            Access::error(3, "User with id '$user_id' not found");
        }
        if ($course_id) {
            $course = Database::get()->querySingle('SELECT id FROM course
                WHERE code = ?s', $course_id);
            if (!$course) {
                Access::error(3, "Course with id '$course_id' not found");
            }
        } else {
            $course = null;
        }
        if ($group_id) {
            $group = Database::get()->querySingle('SELECT id, course_id
                FROM `group` WHERE id = ?s', $group_id);
            if (!$group) {
                Access::error(3, "Group with id '$group_id' not found");
            }
            if ($course and $course->id != $group->course_id) {
                Access::error(4, "Group with id '$group_id' doesn't belong to course '$course_id'");
            }
        } else {
            $group = null;
        }
        $is_editor = 0;
        if (!$role_id) {
            $role_id = USER_STUDENT;
        } elseif (in_array($role_id, [USER_STUDENT, USER_TEACHER])) {
            $role_id = $role_id;
        } else {
            if ($role_id == 'teacher_assistant') {
                $is_editor = 1;
            }
            $role_id = ($role_id == 'teacher'? USER_TEACHER: USER_STUDENT);
        }
        if ($course) {
            $course_id = $course->id;
        } else {
            $course_id = $group->course_id;
        }
        Database::get()->query("INSERT IGNORE INTO course_user
            SET course_id = ?d, user_id = ?d, status = ?d, editor = ?d,
                reg_date = NOW(), receive_mail = 1, document_timestamp = NOW()
            ON DUPLICATE KEY UPDATE status = ?d, editor = ?d",
                $course_id, $user->id, $role_id, $is_editor, $role_id, $is_editor);
        if ($group) {
            Database::get()->query("INSERT IGNORE INTO group_members
                SET group_id = ?d, user_id = ?d, is_tutor = 0, description = ''",
                    $group->id, $user->id);
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
        exit();
    } else {
        Access::error(2, $help_text);
    }
}

chdir('..');
require_once 'apiCall.php';
