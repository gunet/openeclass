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
    global $webDir, $username, $firstname, $lastname, $emailaddress, $adt, $password;

    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ok = register_posted_variables([
            'username' => true,
            'firstname' => true,
            'lastname' => true,
            'emailaddress' => true,
            'adt' => false,
            'password' => false,
        ]);
        if (!$ok) {
            Access::error(2, 'Required parameters for user creation missing: username, firstname, lastname, emailaddress, [adt], [password]');
        }
        if (get_config('case_insensitive_usernames')) {
            $qry = "COLLATE utf8mb4_general_ci = ?s";
        } else {
            $qry = "COLLATE utf8mb4_bin = ?s";
        }
        $user = Database::get()->querySingle("SELECT * FROM user WHERE username $qry", $username);
        if ($user) {
            $admin_check = Database::get()->querySingle('SELECT * FROM admin WHERE user_id = ?d LIMIT 1', $user->id);
            if ($admin_check) {
                Access::error(403, 'Mofifying privileged users is not allowed');
            }
            if ($user->surname != $lastname or $user->givenname != $firstname or $user->email != $emailaddress or $user->am != $adt or $password !== '') {
                Database::get()->query('UPDATE user SET surname = ?s, givenname = ?s, email = ?s
                    WHERE id = ?d',
                    $lastname, $firstname, $emailaddress, $user->id);
                if ($password !== '') {
                    Database::get()->query('UPDATE user SET password = ?s WHERE id = ?d',
                        password_hash($password, PASSWORD_DEFAULT), $user->id);
                }
                if (isset($_POST['adt'])) {
                    Database::get()->query('UPDATE user SET am = ?s WHERE id = ?d',
                        $adt, $user->id);
                }
            }
            $statusmsg = 'updated';
            $user_id = $user->id;
        } else {
            $password = choose_password_strength();
            $password_encrypted = password_hash($password, PASSWORD_DEFAULT);
            $user = Database::get()->query("INSERT INTO user
                SET surname = ?s, givenname = ?s, username = ?s, password = ?s,
                    email = ?s, status = ?d, registered_at = " . DBHelper::timeAfter() . ",
                    expires_at = DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND),
                    lang = ?s, am = ?s, email_public = 0, phone_public = 0, am_public = 0, pic_public = 0,
                    description = '', verified_mail = " . EMAIL_VERIFIED . ", whitelist = ''",
                $lastname, $firstname, $username, $password_encrypted,
                mb_strtolower(trim($emailaddress)), USER_STUDENT, get_config('default_language'),
                $adt);
            if (!$user) {
                Access::error(20, 'Error creating user');
            }
            $user_id = $user->lastInsertID;
            Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $user_id);
            $statusmsg = 'created';
        }
        user_hook($user_id);
        header('Content-Type: application/json');
        $response = ['id' => $user_id, 'status' => $statusmsg];
        if (isset($password) and $password !== '') {
            $response['password'] = $password;
        }
        echo json_encode($response);
        exit();
    } elseif (isset($_GET['course_id'])) {
        $course_id = course_code_to_id($_GET['course_id']);
        if ($access->allCourses or in_array($course_id, $access->courseIDs)) {
            $users = Database::get()->queryArray('SELECT user.id, username, givenname, surname, email, am
                FROM user, course_user
                WHERE user.id = course_user.user_id AND course_user.course_id = ?d',
                $course_id);
            echo json_encode(array_map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'firstname' => $user->givenname,
                    'lastname' => $user->surname,
                    'emailaddress' => $user->email,
                    'adt' => $user->am];
            }, $users), JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            Access::error(403, 'Course is not valid for this API token', 403);
        }
    } elseif (isset($_GET['group_id'])) {
        $course_id = Database::get()->querySingle('SELECT course_id
            FROM `group`
            WHERE id = ?d', $_GET['group_id']);
        if (!$course_id) {
            Access::error(404, "Group with id '$_GET[group_id]' not found", 404);
        }
        if ($course_id and $access->allCourses or in_array($course_id->course_id, $access->courseIDs)) {
            $users = Database::get()->queryArray('SELECT user.id, username, givenname, surname, email, am
                FROM user, group_members
                WHERE user.id = group_members.user_id',
                $_GET['group_id']);
            echo json_encode(array_map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'firstname' => $user->givenname,
                    'lastname' => $user->surname,
                    'emailaddress' => $user->email,
                    'adt' => $user->am];
            }, $users), JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            Access::error(403, 'Course is not valid for this API token', 403);
        }
    } elseif (isset($_GET['id']) or isset($_GET['username'])) {
        if (isset($_GET['username'])) {
            if (get_config('case_insensitive_usernames')) {
                $qry = "COLLATE utf8mb4_general_ci = ?s";
            } else {
                $qry = "COLLATE utf8mb4_bin = ?s";
            }
            $user = Database::get()->querySingle("SELECT * FROM user WHERE username $qry", $_GET['username']);
            if (!$user) {
                Access::error(3, "User with username '$_GET[username]' not found");
            }
        } else {
            $user = Database::get()->querySingle('SELECT * FROM user WHERE id = ?d', $_GET['id']);
            if (!$user) {
                Access::error(3, "User with id '$_GET[id] not found");
            }
        }
        header('Content-Type: application/json');
        echo json_encode([
            'id' => $user->id,
            'username' => $user->username,
            'firstname' => $user->givenname,
            'lastname' => $user->surname,
            'emailaddress' => $user->email,
            'adt' => $user->am,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        Access::error(2, 'Required POST parameters for user creation missing: username, firstname, lastname, emailaddress, adt');
    }
}

require_once '../../../include/lib/pwgen.inc.php';
chdir('..');
require_once 'apiCall.php';
