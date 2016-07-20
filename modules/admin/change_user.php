<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

if (isset($_REQUEST['username'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $sql = "SELECT user.id, surname, username, password, givenname, status, email,
                   admin.user_id AS is_admin, lang
                FROM user LEFT JOIN admin ON user.id = admin.user_id
                WHERE username ";

    if (get_config('case_insensitive_usernames')) {
        $sql .= 'COLLATE utf8_general_ci = ?s';
    } else {
        $sql .= 'COLLATE utf8_bin = ?s';
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
        $_SESSION['is_admin'] = !(!($myrow->is_admin)); // double 'not' to handle NULL
        $_SESSION['uname'] = $myrow->username;
        $_SESSION['langswitch'] = $myrow->lang;
        redirect_to_home_page();
    } else {
        $message = sprintf($langChangeUserNotFound, canonicalize_whitespace(q($_POST['username'])));
        Session::Messages($message, 'alert-danger');
        redirect_to_home_page('modules/admin/change_user.php');
    }
}
$pageName = $langChangeUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                    'url' => "index.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ),false);

$data['menuTypeID'] = 3;

view('admin.users.change_user', $data);
