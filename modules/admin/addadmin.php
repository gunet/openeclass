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

$require_departmentmanage_user = true;
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

if (isset($_POST['submit']) and $username) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (!$is_admin && $is_departmentmanage_user) {
        $res = getTenantUserIfBelongs($username);
    } else {
        $res = Database::get()->querySingle("SELECT id FROM user WHERE username = ?s", $username);
    }
    if ($res) {
        $user_id = $res->id;
        if ($user_id == $uid) {
            Session::flash('message', $langErrorAddaAdmin);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/addadmin.php');
        }
        if(isset($_POST['adminrights'])){
            $privilege = [
                'admin' => ADMIN_USER,
                'poweruser' => POWER_USER,
                'manageuser' => USERMANAGE_USER,
                'managedepartment' => DEPARTMENTMANAGE_USER,
            ][$_POST['adminrights']];
        }
        if (!$is_admin && $is_departmentmanage_user) {
            $privilege = DEPARTMENTMANAGE_USER;
        }
        if (!is_null($privilege)) {
            Database::get()->query('DELETE FROM admin WHERE user_id = ?d', $user_id);
            if ($privilege == DEPARTMENTMANAGE_USER) {
                $affected = 1;
                if (!$is_admin && $is_departmentmanage_user) {
                    $dep_id = getCurrentTenant()->department_id;
                    validateNode($dep_id, false);
                    $affected *= Database::get()->query(
                        'INSERT INTO admin
                        SET user_id = ?d, privilege = ?d, department_id = ?d',
                        $user_id,
                        $privilege,
                        $dep_id
                    )->affectedRows;
                } else {
                    if (!isset($_POST['adminDeps']) or !$_POST['adminDeps']) {
                        Session::flash('message', $langEmptyAddNode);
                        Session::flash('alert-class', 'alert-danger');
                        redirect_to_home_page('modules/admin/addadmin.php?add=add');
                    }
                    foreach ($_POST['adminDeps'] as $dep_id) {
                        validateNode($dep_id, false);
                        $affected *= Database::get()->query(
                            'INSERT INTO admin
                            SET user_id = ?d, privilege = ?d, department_id = ?d',
                            $user_id,
                            $privilege,
                            $dep_id
                        )->affectedRows;
                    }
                }
            } else {
                $affected = Database::get()->query(
                    'INSERT INTO admin
                    SET user_id = ?d, privilege = ?d',
                    $user_id,
                    $privilege
                )->affectedRows;
            }
            if ($affected) {
                Session::flash('message', "$langTheUser <b>" . q($username) . "</b> $langDone");
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page('modules/admin/addadmin.php');
            }
        } else {
            Session::flash('message', $langError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/addadmin.php?add=add');
        }
    } else {
        Session::flash('message', "$langTheUser " . q($username) . " $langNotFound");
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/admin/addadmin.php?add=add');
    }
} else if (isset($_GET['delete'])) { // delete admin users
    $aid = getDirectReference($_GET['delete']);
    if ($aid != 1) { // admin user (with id = 1) cannot be deleted
        if (Database::get()->query("DELETE FROM admin WHERE user_id = ?d", $aid)->affectedRows > 0) {
            Session::flash('message', $langNotAdmin);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message', "$langDeleteAdmin " . q($aid) . " $langNotFeasible");
            Session::flash('alert-class', 'alert-danger');
        }
    } else {
        Session::flash('message', $langCannotDeleteAdmin);
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
        if (!$is_admin && $is_departmentmanage_user) {
            $user = getTenantUserIfBelongs($user_id);
        } else {
            $user = Database::get()->querySingle('SELECT * FROM user WHERE id = ?d', $user_id);
        }
        if (!$user) {
            Session::flash('message', "$langTheUser " . q($username) . " $langNotFound");
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/admin/addadmin.php');
        }
        $username = q($user->username);
        $usernameValue = " readonly value='$username'";
        $roles = Database::get()->queryArray('SELECT * FROM admin WHERE user_id = ?d', $user_id);
        $privilege = $roles[0]->privilege;
        $data['checked'] = [
            'admin' => $privilege == ADMIN_USER ? ' checked' : '',
            'poweruser' => $privilege == POWER_USER ? ' checked' : '',
            'manageuser' => $privilege == USERMANAGE_USER ? ' checked' : '',
            'managedepartment' => $privilege == DEPARTMENTMANAGE_USER ? ' checked' : '',
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
        'defaults' => $adminDeps
    ]);
    $head_content .= $pickerJs;

    $data['pickerHtml'] = $pickerHtml;

    $data['showFormAdmin'] = true;
} else {
    $toolName = $langAdmin;
    $pageName = $langAdmins;
    $data['showFormAdmin'] = false;
    $data['action_bar'] = action_bar([
        [
            'title' => $langAdd,
            'url' => 'addadmin.php?add=admin',
            'icon' => 'fa-plus-circle',
            'button-class' => 'btn-success',
            'level' => 'primary-label'
        ]

    ]);
}


$rows = Database::get()->queryArray("
    SELECT admin.*, user.username, user.givenname, user.surname
    FROM user
    JOIN admin ON user.id = admin.user_id
    ORDER BY user.id
");

$admins = [];

foreach ($rows as $row) {
    $uid = $row->user_id;

    if (!$is_admin && $is_departmentmanage_user) {
        if (!getTenantUserIfBelongs($uid)) {
            continue;
        }
    }

    if (!isset($admins[$uid])) {
        $admins[$uid] = (object)[
            'id' => $row->id,
            'user_id' => $uid,
            'username' => $row->username,
            'givenname' => $row->givenname,
            'surname' => $row->surname,
            'roles' => [],
            'department_paths' => []
        ];
    }

    switch ($row->privilege) {
        case ADMIN_USER:
            $admins[$uid]->roles[] = $langAdministrator;
            break;

        case POWER_USER:
            $admins[$uid]->roles[] = $langPowerUser;
            break;

        case USERMANAGE_USER:
            $admins[$uid]->roles[] = $langManageUser;
            break;

        case DEPARTMENTMANAGE_USER:
            if (!in_array($langManageDepartment, $admins[$uid]->roles)) {
                $admins[$uid]->roles[] = $langManageDepartment;
            }
                 
            if ($row->department_id) {
                $admins[$uid]->department_paths[] = $tree->getFullPath($row->department_id);
            }
            break;
    }
}

$data['admins'] = array_values($admins);
$data['tree'] = $tree;

view('admin.users.addadmin', $data);
