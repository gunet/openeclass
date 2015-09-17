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
                pickerPosition: 'bottom-left', 
                language: '" . $language . "',
                autoclose: true    
            });
        });
    </script>";

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langSearchUser;

// get the incoming values
$inactive_checked = (isset($_GET['search']) and $_GET['search'] == 'inactive') ?
        ' checked' : '';
$lname = isset($_GET['lname']) ? $_GET['lname'] : '';
$fname = isset($_GET['fname']) ? $_GET['fname'] : '';
$uname = isset($_GET['uname']) ? canonicalize_whitespace($_GET['uname']) : '';
$am = isset($_GET['am']) ? $_GET['am'] : '';
$verified_mail = isset($_GET['verified_mail']) ? intval($_GET['verified_mail']) : 3;
$user_type = isset($_GET['user_type']) ? intval($_GET['user_type']) : '';
$auth_type = isset($_GET['auth_type']) ? intval($_GET['auth_type']) : '';
$email = isset($_GET['email']) ? mb_strtolower(trim($_GET['email'])) : '';
$reg_flag = isset($_GET['reg_flag']) ? intval($_GET['reg_flag']) : '';
$user_registered_at = isset($_GET['user_registered_at']) ? $_GET['user_registered_at'] : '';

if (isset($_GET['department'])) {
    $depts_defaults = array('params' => 'name="department"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'defaults' => array_map('intval', $_GET['department']));
} else {
    $depts_defaults = array('params' => 'name="department"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false);
}

if (isDepartmentAdmin()) {
    $allowables = array('allowables' => $user->getDepartmentIds($uid));
    $depts_defaults = array_merge($depts_defaults, $allowables);
}

// Display Actions Toolbar
$tool_content .= action_bar(array(
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

//Preparing form data
$usertype_data = array(
    0 => $langAllUsers,
    USER_TEACHER => $langTeacher,
    USER_STUDENT => $langStudent,
    USER_GUEST => $langGuest);
$verified_mail_data = array(
    EMAIL_VERIFICATION_REQUIRED => $m['pending'],
    EMAIL_VERIFIED => $m['yes'],
    EMAIL_UNVERIFIED => $m['no'],
    3 => $langAllUsers);
$authtype_data = $auth_ids;
$authtype_data[0] = $langAllAuthTypes;

$tree = new Hierarchy();
list($js, $html) = $tree->buildNodePicker($depts_defaults);
$head_content .= $js;

// display the search form
$tool_content .= "
<div class='form-wrapper'>
<form class='form-horizontal' role='form' action='listusers.php' method='get' name='user_search'>
<fieldset>
    <div class='form-group'>
        <label for='uname' class='col-sm-2 control-label'>$langUsername:</label>
        <div class='col-sm-10'>
            <input class='form-control' type='text' name='uname' id='uname' value='" . q($uname) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='fname' class='col-sm-2 control-label'>$langName:</label>
        <div class='col-sm-10'>
            <input class='form-control' type='text' name='fname' id='fname' value='" . q($fname) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='lname' class='col-sm-2 control-label'>$langSurname:</label>
        <div class='col-sm-10'>
            <input class='form-control' type='text' name='lname' id='lname' value='" . q($lname) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='email' class='col-sm-2 control-label'>$langEmail:</label>
        <div class='col-sm-10'>
            <input class='form-control' type='text' name='email' id='email' value='" . q($email) . "'>
        </div>
    </div>  
    <div class='form-group'>
        <label for='am' class='col-sm-2 control-label'>$langAm:</label>
        <div class='col-sm-10'>
            <input class='form-control' type='text' name='am' id='am' value='" . q($am) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label class='col-sm-2 control-label'>$langUserType:</label>
        <div class='col-sm-10'>
            " . selection($usertype_data, 'user_type', 0, 'class="form-control"') . "
        </div>
    </div>
    <div class='form-group'>
        <label class='col-sm-2 control-label'>$langAuthMethod:</label>
        <div class='col-sm-10'>
            " . selection($authtype_data, 'auth_type', 0, 'class="form-control"') . "
        </div>
    </div>
    <div class='form-group'>
        <label class='col-sm-2 control-label'>$langRegistrationDate:</label>
        <div class='col-sm-5'>
            " . selection(array('1' => $langAfter, '2' => $langBefore), 'reg_flag', $reg_flag, 'class="form-control"') . "
        </div>
        <div class='col-sm-5'>       
            <input class='form-control' name='user_registered_at' id='id_user_registered_at' type='text' value='$user_registered_at' data-date-format='dd-mm-yyyy' placeholder='$langRegistrationDate'>
        </div>   
    </div>
    <div class='form-group'>
        <label class='col-sm-2 control-label'>$langEmailVerified:</label>
        <div class='col-sm-10'>
            " . selection($verified_mail_data, 'verified_mail', $verified_mail, 'class="form-control"') . "
        </div>
    </div>
    <div class='form-group'>
        <label for='dialog-set-value' class='col-sm-2 control-label'>$langFaculty:</label>
        <div class='col-sm-10'>
            $html
        </div>
    </div>
    <div class='form-group'>
        <label for='search_type' class='col-sm-2 control-label'>$langSearchFor:</label>
        <div class='col-sm-10'>
            <select class='form-control' name='search_type' id='search_type'>
              <option value='exact'>$langSearchExact</option>
              <option value='begin'>$langSearchStartsWith</option>
              <option value='contains' selected>$langSearchSubstring</option>
            </select>
        </div>
    </div>
    <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-2'>
            <div class='checkbox'>
              <label>
                <input type='checkbox' name='search' value='inactive'$inactive_checked>
                $langInactiveUsers
              </label>
            </div> 
        </div>
    </div>    
    <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-2'>
            <input class='btn btn-primary' type='submit' value='$langSearch'>
            <a class='btn btn-default' href='index.php'>$langCancel</a>
        </div>
    </div>
</fieldset>
</form></div>";
// end form

// display administrator menu
draw($tool_content, 3, null, $head_content);
