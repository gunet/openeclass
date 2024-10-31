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

/**
 * @file change_user.php
 * @brief  Allows platform admin to login as another user without asking for password
 */

$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'other_user_connection';

require_once '../../include/baseTheme.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';

$toolName = $langChangeUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (!($is_admin or $is_power_user or $is_usermanage_user or $is_departmentmanage_user)) {
    redirect_to_home_page();
}

if (isset($_REQUEST['username'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $sql = "SELECT user.id, surname, username, password, givenname, status, email,
                   admin.user_id AS is_admin, admin.privilege, lang
                FROM user LEFT JOIN admin ON user.id = admin.user_id
                WHERE username ";

    if (get_config('case_insensitive_usernames')) {
        $sql .= 'COLLATE utf8mb4_general_ci = ?s';
    } else {
        $sql .= 'COLLATE utf8mb4_bin = ?s';
    }
    $myrow = Database::get()->querySingle($sql, $_REQUEST['username']);
    if ($myrow) {
        if ($is_departmentmanage_user and !$is_power_user) {
            // Department admin - check if the user belongs to admin's departments
            $user = new User();
            $tree = new Hierarchy();

            $admindeps = array_map(function ($node) {
                return $node->id;
            }, $tree->buildSubtreesFull($user->getAdminDepartmentIds($uid)));
            $userdeps = array_map(function ($node) {
                return $node->id;
            }, $tree->buildSubtreesFull($user->getDepartmentIds($myrow->id)));
            $common_deps = array_intersect($admindeps, $userdeps);
            if (!$common_deps) {
                redirect_to_home_page();
            }
        }
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
        $_SESSION['uid'] = $myrow->id;
        $_SESSION['surname'] = $myrow->surname;
        $_SESSION['givenname'] = $myrow->givenname;
        $_SESSION['status'] = $myrow->status;
        $_SESSION['email'] = $myrow->email;
        if (!is_null($myrow->is_admin)) {
            $_SESSION['is_admin'] = $myrow->privilege == ADMIN_USER;
            $_SESSION['is_power_user'] = $myrow->privilege == POWER_USER;
            $_SESSION['is_usermanage_user'] = $myrow->privilege == USERMANAGE_USER;
            $_SESSION['is_departmentmanage_user'] = $myrow->privilege == DEPARTMENTMANAGE_USER;
        }
        $_SESSION['uname'] = $myrow->username;
        $_SESSION['langswitch'] = $myrow->lang;
        redirect_to_home_page();
    } else {
        $message = sprintf($langChangeUserNotFound, canonicalize_whitespace(q($_POST['username'])));
        Session::flash('message',$message);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/admin/change_user.php');
    }
}

view('admin.users.change_user');
