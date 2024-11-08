<?php

/* ========================================================================
 * Open eClass 4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

include '../../include/baseTheme.php';
include 'auth.inc.php';

if (!isset($_GET['user']) or !isset($_GET['token'])) {
    forbidden();
}

if (!token_validate("login user=$_GET[user]", $_GET['token'], 500)) {
    Session::Messages('Login link expired - please try again', 'alert-warning');
    redirect_to_home_page();
}

if (get_config('case_insensitive_usernames')) {
    $sqlLogin = "COLLATE utf8mb4_general_ci = ?s";
} else {
    $sqlLogin = "COLLATE utf8mb4_bin = ?s";
}
$user_info_object = Database::get()->querySingle("SELECT id, surname, givenname, password,
                        username, status, email, lang, verified_mail, am
                    FROM user WHERE username $sqlLogin", $_GET['user']);
if ($user_info_object) {
    $userObj = new User();
    $options = login_hook(array(
        'accept' => true,
        'user_id' => $user_info_object->id,
        'attributes' => [],
        'status' => $user_info_object->status,
        'departments' => $userObj->getDepartmentIds($user_info_object->id),
        'am' => $user_info_object->am));

    if (!$options['accept']) {
        deny_access();
    }

    $is_active = is_active_account($user_info_object->id, true);

    // check for admin privileges
    $admin_rights = get_admin_rights($user_info_object->id);
    if ($admin_rights == ADMIN_USER) {
        $is_active = 1;   // admin user is always active
        $_SESSION['is_admin'] = 1;
    } elseif ($admin_rights == POWER_USER) {
        $_SESSION['is_power_user'] = 1;
    } elseif ($admin_rights == USERMANAGE_USER) {
        $_SESSION['is_usermanage_user'] = 1;
    } elseif ($admin_rights == DEPARTMENTMANAGE_USER) {
        $_SESSION['is_departmentmanage_user'] = 1;
    }
    if ($is_active) {
        $_SESSION['uid'] = $user_info_object->id;
        $_SESSION['uname'] = $user_info_object->username;
        $_SESSION['surname'] = $user_info_object->surname;
        $_SESSION['givenname'] = $user_info_object->givenname;
        $_SESSION['status'] = $user_info_object->status;
        $_SESSION['email'] = $user_info_object->email;
        $GLOBALS['language'] = $_SESSION['langswitch'] = $user_info_object->lang;
        user_hook($user_info_object->id);
        $session->setLoginTimestamp();
        $session->setLoginMethod('eclass');
        redirect_to_home_page('main/portfolio.php');
    } else {
        $warning = "$langAccountInactive1 " .
            "<a href='modules/auth/contactadmin.php?userid={$user_info_object->id}&amp;h=" .
            token_generate("userid={$user_info_object->id}") . "'>$langAccountInactive2</a>";
        Session::Messages($warning);
        redirect_to_home_page();
    }
}

deny_access();
