<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2020  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/**
 * @file change_user.php
 * @brief  Allows platform admin to login as another user without asking for password
 */


$require_admin = true;
require_once '../../include/baseTheme.php';
$pageName = $langChangeUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

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
        //Session::Messages($message, 'alert-danger');
        Session::flash('message',$message); 
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/admin/change_user.php');
    }
}

$data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                    'url' => "index.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary')
                ),false);

$data['menuTypeID'] = 3;

view('admin.users.change_user', $data);
