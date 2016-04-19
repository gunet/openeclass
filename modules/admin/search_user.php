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
 * 	@file searchuser.php
 *      @brief: user search form based upon criteria/filters
 */
$require_usermanage_user = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$user = new User();

load_js('jstree3');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#id_user_registered_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', 
                pickerPosition: 'bottom-right', 
                language: '" . $language . "',
                autoclose: true    
            });
        });
    </script>";

$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];
$toolName = $langSearchUser;
// Display Actions Toolbar
$data['action_bar'] = action_bar(array(
            array('title' => $langAllUsers,
                'url' => "listusers.php?search=yes",
                'icon' => 'fa-search',
                'level' => 'primary-label'),
            array('title' => $langInactiveUsers,
                'url' => "listusers.php?search=inactive",
                'icon' => 'fa-search',
                'level' => 'primary-label'),
            array('title' => $langBack,
                'url' => "index.php",
                'icon' => 'fa-reply',
                'level' => 'primary')));

// get the incoming values
$data['inactive_checked'] = isset($_GET['search']) && $_GET['search'] == 'inactive';
$data['lname'] = isset($_GET['lname']) ? $_GET['lname'] : '';
$data['fname'] = isset($_GET['fname']) ? $_GET['fname'] : '';
$data['uname'] = isset($_GET['uname']) ? canonicalize_whitespace($_GET['uname']) : '';
$data['am'] = isset($_GET['am']) ? $_GET['am'] : '';
$data['verified_mail'] = isset($_GET['verified_mail']) ? intval($_GET['verified_mail']) : 3;
//$user_type = isset($_GET['user_type']) ? intval($_GET['user_type']) : '';
//$auth_type = isset($_GET['auth_type']) ? intval($_GET['auth_type']) : '';
$data['email'] = isset($_GET['email']) ? mb_strtolower(trim($_GET['email'])) : '';
$data['reg_flag'] = isset($_GET['reg_flag']) ? intval($_GET['reg_flag']) : '';
$data['user_registered_at'] = isset($_GET['user_registered_at']) ? $_GET['user_registered_at'] : '';

//Preparing form data
$data['usertype_data'] = array(
    0 => $langAllUsers,
    USER_TEACHER => $langTeacher,
    USER_STUDENT => $langStudent,
    USER_GUEST => $langGuest);
$data['verified_mail_data'] = array(
    EMAIL_VERIFICATION_REQUIRED => $m['pending'],
    EMAIL_VERIFIED => $m['yes'],
    EMAIL_UNVERIFIED => $m['no'],
    3 => $langAllUsers);
$data['authtype_data'] = $auth_ids;
$data['authtype_data'][0] = $langAllAuthTypes;

if (isset($_GET['department'])) {
    $depts_defaults = array('params' => 'name="department"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'defaults' => array_map('intval', arrayValuesDirect($_GET['department'])));
} else {
    $depts_defaults = array('params' => 'name="department"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false);
}

if (isDepartmentAdmin()) {
    $allowables = array('allowables' => $user->getDepartmentIds($uid));
    $depts_defaults = array_merge($depts_defaults, $allowables);
}
$tree = new Hierarchy();
list($js, $html) = $tree->buildNodePickerIndirect($depts_defaults);
$head_content .= $js;
$data['html'] = $html; 

$data['menuTypeID'] = 3;
view('admin.users.search_user', $data);

