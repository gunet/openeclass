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
    $data['u_realname'] = q(uid_to_name($user));
} else {
    Session::Messages($langErrorDelete, 'alert-danger');
    redirect_to_home_page('modules/admin/listusers.php');    
}

if (isset($_POST['doit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    if (get_admin_rights($user) > 0) {
        Session::Messages($langTryDeleteAdmin, 'alert-danger');
        redirect_to_home_page("modules/admin/deluser.php?u=$user");
    } else {
        if (deleteUser($user, true)) {
            Session::Messages("$langWithUsername \"$u_account\" ($u_realname) $langWasDeleted.", 'alert-info');
        } else {
            Session::Messages($langErrorDelete, 'alert-danger');
        }
        redirect_to_home_page('modules/admin/listusers.php');
    }
}

$toolName = $langUnregUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$pageName = $langConfirmDelete;

$data['action_bar'] = action_bar(array(
        array('title' => $langBackAdmin,
              'url' => "modules/admin/listusers.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));

$data['menuTypeID'] = 3;
view ('admin.users.deluser', $data);
