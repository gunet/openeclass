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

$require_admin = TRUE;
$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'administrators';

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/admin/hierarchy_validations.php';

$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];

$tree = new Hierarchy;

// Initialize the incoming variables
$username = isset($_POST['username']) ? trim($_POST['username']) : null;

if (isset($_POST['submit']) and isset($_POST['adminrights']) and $username) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $res = Database::get()->querySingle("SELECT id FROM user WHERE username = ?s", $username);
    if ($res) {
        $user_id = $res->id;
        if ($user_id == $uid) {
            Session::flash('message',$langErrorAddaAdmin);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/addadmin.php');
        }
        $privilege = [
            'admin' => ADMIN_USER,
            'poweruser' => POWER_USER,
            'manageuser' => USERMANAGE_USER,
            'managedepartment' => DEPARTMENTMANAGE_USER,
        ][$_POST['adminrights']];
        if (!is_null($privilege)) {
            Database::get()->query('DELETE FROM admin WHERE user_id = ?d', $user_id);
            if ($privilege == DEPARTMENTMANAGE_USER) {
                if (!isset($_POST['adminDeps']) or !$_POST['adminDeps']) {
                    Session::flash('message',$langEmptyAddNode);
                    Session::flash('alert-class', 'alert-danger');
                    redirect_to_home_page('modules/admin/addadmin.php?add=add');
                }
                $affected = 1;
                foreach ($_POST['adminDeps'] as $dep_id) {
                    validateNode($dep_id, false);
                    $affected *= Database::get()->query('INSERT INTO admin
                        SET user_id = ?d, privilege = ?d, department_id = ?d',
                        $user_id, $privilege, $dep_id)->affectedRows;
                }
            } else {
                $affected = Database::get()->query('INSERT INTO admin
                    SET user_id = ?d, privilege = ?d',
                    $user_id, $privilege)->affectedRows;
            }
            if ($affected) {
                Session::flash('message',"$langTheUser <b>" . q($username) . "</b> $langDone");
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page('modules/admin/addadmin.php');
            }
        } else {
            Session::flash('message',$langError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/addadmin.php?add=add');
        }
    } else {
        Session::flash('message',"$langTheUser " . q($username) . " $langNotFound");
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/admin/addadmin.php?add=add');
    }
} else if (isset($_GET['delete'])) { // delete admin users
    $aid = getDirectReference($_GET['delete']);
    if ($aid != 1) { // admin user (with id = 1) cannot be deleted
        if (Database::get()->query("DELETE FROM admin WHERE user_id = ?d", $aid)->affectedRows > 0) {
            Session::flash('message',$langNotAdmin);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message',"$langDeleteAdmin " . q($aid) . " $langNotFeasible");
            Session::flash('alert-class', 'alert-danger');
        }
    } else {
        Session::flash('message',$langCannotDeleteAdmin);
        Session::flash('alert-class', 'alert-danger');
    }
    redirect_to_home_page('modules/admin/addadmin.php');
}


if (isset($_GET['add']) or isset($_GET['edit'])) {
    load_js('jstree3');
    $navigation[] = ['url' => 'addadmin.php', 'name' => $langAdmins];
    $adminDeps = [];
    if (isset($_GET['edit'])) {
        $toolName = $langAdmin;
        $pageName = $langEditPrivilege;
        $user_id = getDirectReference($_GET['edit']);
        $user = Database::get()->querySingle('SELECT * FROM user WHERE id = ?d', $user_id);
        $username = q($user->username);
        $usernameValue = " readonly value='$username'";
        $roles = Database::get()->queryArray('SELECT * FROM admin WHERE user_id = ?d', $user_id);
        $privilege = $roles[0]->privilege;
        $data['checked'] = [
            'admin' => $privilege == ADMIN_USER? ' checked': '',
            'poweruser' => $privilege == POWER_USER? ' checked': '',
            'manageuser' => $privilege == USERMANAGE_USER? ' checked': '',
            'managedepartment' => $privilege == DEPARTMENTMANAGE_USER? ' checked': '',
        ];
        if ($privilege == DEPARTMENTMANAGE_USER) {
            $adminDeps = array_map(function ($item) {
                return $item->department_id;
            }, $roles);
        }
    } else {
        $toolName = $langAdmin;
        $pageName = $langAddAdmin;
        $usernameValue = " placeholder='$langUsername'";
        $data['checked'] = [
            'admin' => '',
            'poweruser' => '',
            'manageuser' => '',
            'managedepartment' => '',
        ];
    }

    $data['usernameValue'] = $usernameValue;

    list($pickerJs, $pickerHtml) = $tree->buildNodePicker([
        'params' => 'name="adminDeps[]"',
        'multiple' => true,
        'defaults' => $adminDeps]);
    $head_content .= $pickerJs;

    $data['pickerHtml'] = $pickerHtml;

    $data['showFormAdmin'] = true;

}else {
    $toolName = $langAdmin;
    $pageName = $langAdmins;
    $data['showFormAdmin'] = false;
    $data['action_bar'] = action_bar([
        [ 'title' => $langAdd,
          'url' => 'addadmin.php?add=admin',
          'icon' => 'fa-plus-circle',
          'button-class' => 'btn-success',
          'level' => 'primary-label' ]

    ]);
}


$data['admins'] = $admins = Database::get()->queryArray('SELECT admin.*, user.username, user.givenname, user.surname 
    FROM user, admin
    WHERE user.id = admin.user_id
    ORDER BY user_id');

$out = [];
foreach ($admins as $admin) {
    if (isset($out[$admin->user_id])) {
        $out[$admin->user_id]->department_id[] = $admin->department_id;
    } else {
        if (!is_null($admin->department_id)) {
            $admin->department_id = [$admin->department_id];
        }
        $out[$admin->user_id] = $admin;
    }
}
foreach ($out as $row) {
    $message = [
        ADMIN_USER => $langAdministrator,
        POWER_USER => $langPowerUser,
        USERMANAGE_USER => $langManageUser,
        DEPARTMENTMANAGE_USER => $langManageDepartment,
    ][$row->privilege];
    if ($row->privilege == DEPARTMENTMANAGE_USER) {
        $data['message'][$row->user_id] = '<ul>' .
            implode('', array_map(function ($department_id) use ($tree) {
                return '<li>' . $tree->getFullPath($department_id) . '</li>';
            }, $row->department_id)) .
            '</ul>';
    }
}

view ('admin.users.addadmin', $data);

