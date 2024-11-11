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

/**
 * 	@file searchuser.php
 *      @brief: user search form based upon criteria/filters
 */
$require_usermanage_user = TRUE;
$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'user_search';

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$user = new User();

load_js('jstree3');
load_js('bootstrap-datepicker');

$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];
//$toolName = $langSearchUser;
$toolName = $langAdmin;
$pageName = $langSearchUser;

// get the incoming values

$data['inactive_checked'] = (isset($_GET['search']) and $_GET['search'] == 'inactive') ? ' checked' : '';
$data['lname'] = $_GET['lname'] ?? '';
$data['fname'] = $_GET['fname'] ?? '';
$data['uname'] = isset($_GET['uname']) ? canonicalize_whitespace($_GET['uname']) : '';
$data['am'] = $_GET['am'] ?? '';
$data['verified_mail'] = isset($_GET['verified_mail']) ? intval($_GET['verified_mail']) : 3;
$data['email'] = isset($_GET['email']) ? mb_strtolower(trim($_GET['email'])) : '';
$data['reg_flag'] = isset($_GET['reg_flag']) ? intval($_GET['reg_flag']) : '';
$data['user_registered_at'] = $_GET['user_registered_at'] ?? '';
$data['user_expires_until'] = $_GET['user_expires_until'] ?? '';
$data['user_last_login'] = $_GET['user_last_login'] ?? '';

if (isset($_GET['department'])) {
    $depts_defaults = array('params' => 'name="department"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'defaults' => array_map('intval', $_GET['department']));
} else {
    $depts_defaults = array('params' => 'name="department"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false);
}

if (isDepartmentAdmin()) {
    $allowables = array('allowables' => $user->getAdminDepartmentIds($uid));
    $depts_defaults = array_merge($depts_defaults, $allowables);
}

// Display Actions Toolbar
$data['action_bar'] = action_bar(array(
            array('title' => $langAllUsers,
                'url' => "listusers.php?search=yes",
                'icon' => 'fa-solid fa-users',
                'level' => 'primary-label'),
            array('title' => $langInactiveUsers,
                'url' => "listusers.php?search=inactive",
                'icon' => 'fa-solid fa-user-xmark',
                'level' => 'primary-label'),
            array('title' => $langWillExpireUsers,
                'url' => "listusers.php?search=wexpire",
                'icon' => 'fa-solid fa-user-slash',
                'level' => 'primary-label'),
            ));


//Preparing form data
$data['usertype_data'] = array(
    0 => $langAllUsers,
    USER_TEACHER => $langUsersWithTeacherRights,
    USER_STUDENT => $langUsersWithNoTeacherRights,
    USER_GUEST => $langGuests);
$data['verified_mail_data'] = array(
    EMAIL_VERIFICATION_REQUIRED => $m['pending'],
    EMAIL_VERIFIED => $langYes,
    EMAIL_UNVERIFIED => $langNo,
    3 => $langAllUsers);
$data['authtype_data'] = $auth_ids;
$data['authtype_data'][0] = $langAllAuthTypes;

$tree = new Hierarchy();
list($js, $html) = $tree->buildNodePicker($depts_defaults);
$head_content .= $js;
$data['html'] = $html;

view('admin.users.search_user', $data);
