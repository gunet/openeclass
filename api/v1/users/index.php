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
        $ok = register_posted_variables([
            'username' => true,
            'firstname' => true,
            'lastname' => true,
            'emailaddress' => true,
            'adt' => false,
        ]);
        if (!$ok) {
            Access::error(2, 'Required parameters for user creation missing: username, firstname, lastname, emailaddress, [adt]');
        }
        if (get_config('case_insensitive_usernames')) {
            $qry = "COLLATE utf8mb4_general_ci = ?s";
        } else {
            $qry = "COLLATE utf8mb4_bin = ?s";
        }
        $user = Database::get()->querySingle("SELECT * FROM user WHERE username $qry", $username);
        if ($user) {
            if ($user->surname != $lastname or $user->givenname != $firstname or $user->email != $emailaddress) {
                Database::get()->query('UPDATE user SET surname = ?s, givenname = ?s, email = ?s
                    WHERE id = ?d', $user->id);
            }
            $statusmsg = 'User updated';
            $user_id = $user_id;
        } else {
            $password = choose_password_strength();
            $password_encrypted = password_hash($password, PASSWORD_DEFAULT);
            $user = Database::get()->query("INSERT INTO user
                SET surname = ?s, givenname = ?s, username = ?s, password = ?s,
                    email = ?s, status = ?d, registered_at = " . DBHelper::timeAfter() . ",
                    expires_at = DATE_ADD(NOW(), INTERVAL " . get_config('account_duration') . " SECOND),
                    lang = ?s, am = ?s, email_public = 0, phone_public = 0, am_public = 0, pic_public = 0,
                    description = '', verified_mail = " . EMAIL_VERIFIED . ", whitelist = ''",
                $GLOBALS['lastname'], $GLOBALS['firstname'], $GLOBALS['username'], $password_encrypted,
                mb_strtolower(trim($GLOBALS['emailaddress'])), USER_STUDENT, get_config('default_language'),
                $GLOBALS['adt']);
            if (!$user) {
                Access::error(20, 'Error creating user');
            }
            $user_id = $user->lastInsertID;
            Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $user_id);
            $statusmsg = 'User created';
        }
        user_hook($id);
        header('Content-Type: application/json');
        echo json_encode(['id' => $user_id, 'status' => $statusmsg]);
        exit();
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
