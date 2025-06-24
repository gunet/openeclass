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
    global $webDir, $username, $firstname, $lastname, $emailaddress, $auth, $adt, $password,$auth_ids,$hybridAuthMethods;

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
            'auth' => false,
        ]);
        if (!$ok) {
            Access::error(2, 'Required parameters for user creation missing: username, firstname, lastname, emailaddress, [adt], [password], [auth]');
        }


        if (!empty($auth)) {
            require_once __DIR__ . '/../../../modules/auth/auth.inc.php';
            array_push($hybridAuthMethods, 'eclass');

            $active_auth_methods = get_auth_active_methods();
            $active_auth_names = array_map(function($id) use ($auth_ids) {
                return isset($auth_ids[$id]) ? $auth_ids[$id] : null;
            }, $active_auth_methods);

            $allowed_auth_names = array_diff($active_auth_names, $hybridAuthMethods);

            if (!in_array($auth, $active_auth_names) || in_array($auth, $hybridAuthMethods)) {
                Access::error(2, 'Invalid authentication method. Allowed methods are: ' . implode(', ', $allowed_auth_names));
            }
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

                if ( !empty($auth) ) {
                    Database::get()->query('UPDATE user SET password = ?s WHERE id = ?d', $auth, $user->id);
                } else if (!$password == '') {
                    $password_encrypted = password_hash($password, PASSWORD_DEFAULT);
                    Database::get()->query('UPDATE user SET password = ?s WHERE id = ?d', $password_encrypted, $user->id);
                }
                if (isset($_POST['adt'])) {
                    Database::get()->query('UPDATE user SET am = ?s WHERE id = ?d', $adt, $user->id);
                }
            }
            $statusmsg = 'updated';
            $user_id = $user->id;
        } else {
            if (!empty($auth)) {
                $password_encrypted = $auth;
            } else {
                if ($password == '') {
                    $password = choose_password_strength();
                }
                $password_encrypted = password_hash($password, PASSWORD_DEFAULT);
            }

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
        user_hook($id);
        header('Content-Type: application/json');
        $response = ['id' => $user_id, 'status' => $statusmsg];
        if (!empty($auth)) {
            $response['auth'] = $auth;
        } elseif (isset($password) && $password !== '') {
            $response['password'] = $password;
        }
        echo json_encode($response);
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
