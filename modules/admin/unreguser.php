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
require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

$toolName = $langUnregUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

// get the incoming values and initialize them
$u = isset($_GET['u']) ? intval($_GET['u']) : false;
$data['c'] = $c = isset($_GET['c']) ? intval($_GET['c']) : false;
$doit = isset($_GET['doit']);

if (isset($doti)) {
    $data['action_bar'] = action_bar(array(    
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
} else {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => "edituser.php?u=$u",
              'icon' => 'fa-reply',
              'level' => 'primary-label'),
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
}

if (isDepartmentAdmin()) {
    validateUserNodes(intval($u), true);
}

$data['u_account'] = $u ? q(uid_to_name($u, 'username')) : '';
$data['u_realname'] = $u ? q(uid_to_name($u)) : '';
$userdata = user_get_data($u);
$u_status = $userdata->status;

if ($doit) {
    if ($c and $u) {
        $q = Database::get()->query("DELETE from course_user WHERE user_id = ?d AND course_id = ?d", $u, $c);
        if ($q->affectedRows>0) {
            Database::get()->query("DELETE FROM group_members
                            WHERE user_id = ?d AND
                            group_id IN (SELECT id FROM `group` WHERE course_id = ?d)", $u, $c);
            $message = "$langWithUsername $u_accoun $langWasCourseDeleted <em>" . q(course_id_to_title($c)) . "</em>";
            Session::Messages($message, 'alert-info');
        }
    } else {
        Session::Messages($langErrorDelete, 'alert-danger');
    }
    redirect_to_home_page("edituser.php?u=$u");
}

$data['menuTypeID'] = 3;
view('admin.users.unreguser', $data);
