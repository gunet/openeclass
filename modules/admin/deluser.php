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


$require_usermanage_user = true;
include '../../include/baseTheme.php';

// get the incoming values and initialize them
if (isset($_GET['u'])) {
    $data['user'] = $user = $_GET['u'];
} else {
    forbidden();
}

if ($user) {
    $data['u_account'] = $u_account = q(uid_to_name($user, 'username'));
    $data['u_realname'] = $u_realname = q(uid_to_name($user));
} else {
    Session::flash('message',$langErrorDelete);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page('modules/admin/listusers.php');
}

if (isset($_POST['doit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    if (get_admin_rights($user) > 0) {
        Session::flash('message', $langTryDeleteAdmin);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/admin/deluser.php?u=$user");
    } else {
        if (deleteUser($user, true)) {
            Session::flash('message',"$langWithUsername \"$u_account\" ($u_realname) $langWasDeleted.");
            Session::flash('alert-class', 'alert-info');
        } else {
            Session::flash('message',$langErrorDelete);
            Session::flash('alert-class', 'alert-danger');
        }
        redirect_to_home_page('modules/admin/listusers.php');
    }
}

$toolName = $langUnregUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$pageName = $langConfirmDelete;

$data['action_bar'] = action_bar(array(
        array('title' => $langBackAdmin,
              'url' => "listusers.php",
              'icon' => 'fa-reply',
              'level' => 'primary')));

view ('admin.users.deluser', $data);
