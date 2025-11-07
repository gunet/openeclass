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


$require_login = TRUE;
require_once '../include/baseTheme.php';
require_once 'include/log.class.php';

$toolName = $langMyProfile;
$pageName = $langUnregUser;
$navigation[] = array('url' => 'profile/profile.php', 'name' => $langModifyProfile);

$data['display_form'] = false;
$data['user_deleted'] = false;

if (!isset($_POST['doit'])) {
    // admin cannot be deleted
    if ($is_admin) {
        Session::flash('message', "$langAdminNo");
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('main/profile/display_profile.php');
    } else {
        $q = Database::get()->querySingle("SELECT code, visible FROM course, course_user
			WHERE course.id = course_user.course_id
                AND course.visible != " . COURSE_INACTIVE . "
                AND user_id = ?d LIMIT 1", $uid);
        if (!$q) {
            $data['display_form'] = true;
        } else {
            Session::flash('message', "$langExplain");
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('main/profile/display_profile.php');
        }
    }  //endif is admin
} else {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($uid)) {
        $un = uid_to_name($uid, 'username');
        $n = uid_to_name($uid);
        deleteUser($uid, false);
        // action logging
        Log::record(0, 0, LOG_DELETE_USER, array('uid' => $uid,
            'username' => $un,
            'name' => $n));

        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
        session_destroy();
        $uid = 0;

        $data['action_bar'] = action_bar(array(
            array('title' => $langLogout,
                'url' => "../index.php?logout=yes",
                'icon' => 'fa-sign-out',
                'level' => 'primary-label')));

        $data['user_deleted'] = true;
    }
}

view("main.profile.unreguser", $data);
