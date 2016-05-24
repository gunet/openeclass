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

$require_admin = TRUE;

require_once '../../include/baseTheme.php';

// Initialize the incoming variables
$username = isset($_POST['username']) ? $_POST['username'] : '';

if (isset($_POST['submit']) && !empty($username)) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $res = Database::get()->querySingle("SELECT id FROM user WHERE username=?s", $username);
    if ($res) {
        $user_id = $res->id;
        switch ($_POST['adminrights']) {
            case 'admin': $privilege = '0'; // platform admin user
                break;
            case 'poweruser': $privilege = '1'; // power user
                break;
            case 'manageuser': $privilege = '2'; //  manage user accounts
                break;
            case 'managedepartment' : $privilege = '3'; // manage departments
                break;
        }

        if (isset($privilege)) {
            if (Database::get()->querySingle("SELECT * FROM admin WHERE user_id = ?d", $user_id)) {
                $affected = Database::get()->query("UPDATE admin SET privilege = ?d
                                WHERE user_id = ?d", $privilege, $user_id)->affectedRows;
            } else {
                $affected = Database::get()->query("INSERT INTO admin VALUES(?d,?d)", $user_id, $privilege)->affectedRows;
            }
            if ($affected > 0) {
                $message_type = "alert-success";
                $message = $langTheUser. " " . $username . " ". $langWith . " id=" . $user_id . " " . $langDone;                  
            }
        } else {
            $message_type = "alert-danger";
            $message = $langError;            
        }
    } else {
        $message_type = "alert-danger";
        $message = $langTheUser . " " . $username . " " . $langNotFound;
    }
    Session::Messages($message, $message_type);
    redirect_to_home_page('modules/admin/addadmin.php');
} else if (isset($_GET['delete'])) { // delete admin users
    $aid = intval(getDirectReference($_GET['aid']));
    if ($aid != 1) { // admin user (with id = 1) cannot be deleted
        if (Database::get()->query("DELETE FROM admin WHERE admin.user_id = ?d", $aid)->affectedRows > 0) {
            $message_type = "alert-success";
            $message = $langNotAdmin;                   
        } else {
            $message_type = "alert-danger";
            $message = $langDeleteAdmin. " " . $aid . " " . $langNotFeasible;                  
        }
    } else {
        $message_type = "alert-danger";
        $message = $langCannotDeleteAdmin;          
    }
    Session::Messages($message, $message_type);
    redirect_to_home_page('modules/admin/addadmin.php');
}

$toolName = $langAdmins;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$data['action_bar'] = action_bar([
            [
                'title' => $langBack,
                'url' => "index.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            ]
        ]);

$data['admins'] = Database::get()->queryArray("SELECT id, givenname, surname, username, admin.privilege as privilege
                    FROM user, admin
                    WHERE user.id = admin.user_id
                    ORDER BY id");
$data['menuTypeID'] = 3;
view ('admin.users.addadmin', $data);

