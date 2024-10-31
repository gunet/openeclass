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
    global $webDir;

    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['course_id'])) {
            Access::error(2, 'Required parameter user_id missing');
        }
        if (!isset($_POST['groupname'])) {
            Access::error(2, 'Required parameter groupname missing');
        }
        $course = Database::get()->querySingle('SELECT id, code, visible FROM course
            WHERE code = ?s AND visible <> ?d',
            $_POST['course_id'], COURSE_INACTIVE);
        if (!$course) {
            Access::error(3, "Course with id '$_GET[course_id]' not found");
        }
        $secret_directory = uniqid('');
        $secret_path = "$webDir/courses/{$course->code}/group/$secret_directory";
        make_dir("$secret_path");
        touch("$secret_path/index.php");
        $group = Database::get()->query('INSERT INTO `group` SET
            course_id = ?d, name = ?s, description = \'\', secret_directory = ?s',
            $course->id, canonicalize_whitespace($_POST['groupname']),
            $secret_directory);
        if ($group) {
            header('Content-Type: application/json');
            echo json_encode(['id' => $group->lastInsertID]);
            exit();
        } else {
            Access::error(10, "Error creating group in course '$_GET[course_id]'");
        }
    } else {
        if (isset($_GET['group_id'])) {
            $group = Database::get()->querySingle('SELECT * FROM `group`
                WHERE id = ?d', $_GET['group_id']);
            if (!$group) {
                Access::error(3, "Group with id '$_GET[group_id]' not found");
            } else {
                $group_data = [
                        'id' => $group->id,
                        'name' => $group->name,
                ];
            }
            header('Content-Type: application/json');
            echo json_encode($group_data, JSON_UNESCAPED_UNICODE);
            exit();
        } elseif (isset($_GET['course_id'])) {
            $course = Database::get()->querySingle('SELECT id, code, visible FROM course
                WHERE code = ?s AND visible <> ?d',
                $_GET['course_id'], COURSE_INACTIVE);
            if (!$course) {
                Access::error(3, "Course with id '$_GET[course_id]' not found");
            }
            $groups = Database::get()->queryArray('SELECT * FROM `group`
                WHERE course_id = ?d', $course->id);
            if (!$groups) {
                $group_data = [];
            } else {
                $group_data = array_map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                    ];
                }, $groups);
            }
            header('Content-Type: application/json');
            echo json_encode($group_data, JSON_UNESCAPED_UNICODE);
            exit();
        } else {
            Access::error(3, 'Required POST parameters: course_id, groupname - required GET parameters: course_id or group_id');
        }
    }
}

chdir('..');
require_once 'apiCall.php';
