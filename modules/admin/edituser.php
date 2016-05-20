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
 * @file edituser.php
 * @brief edit user info
 */

$require_usermanage_user = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'hierarchy_validations.php';
require_once 'modules/admin/custom_profile_fields_functions.php';

$tree = new Hierarchy();
$user = new User();

if (isset($_REQUEST['u'])) {
    $data['u'] = $u = intval($_REQUEST['u']);
    $_SESSION['u_tmp'] = $u;
}

if (!isset($_REQUEST['u'])) {
    $data['u'] = $u = $_SESSION['u_tmp'];
}

$verified_mail = isset($_REQUEST['verified_mail']) ? intval($_REQUEST['verified_mail']) : 2;

load_js('jstree3');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_expires_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                minuteStep: 10,
                autoclose: true
            });
        });
    </script>";

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listusers.php', 'name' => $langListUsersActions);
$toolName = "$langEditUser: " . uid_to_name($u);

$u_submitted = isset($_POST['u_submitted']) ? $_POST['u_submitted'] : '';

if ($u) {
    if (isDepartmentAdmin())
        validateUserNodes(intval($u), true);

   $data['info'] = $info = Database::get()->querySingle("SELECT surname, givenname, username, password, email,
                              phone, registered_at, expires_at, status, am,
                              verified_mail, whitelist
                         FROM user WHERE id = ?s", $u);
    if (isset($_POST['submit_editauth'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        checkSecondFactorChallenge();
        $auth = intval($_POST['auth']);
        $oldauth = array_search($info->password, $auth_ids);
        $extra_msg = '' ;
        if ($auth == 1 and $oldauth != 1) {
            $extra_msg = " <a href='password.php?userid=" . getIndirectReference($u) . "'>$langEditAuthSetPass</a>";
            $newpass = '.';
        } else {
            $newpass = $auth_ids[$auth];
        }

        Database::get()->query("UPDATE user SET password = ?s WHERE id = ?s", $newpass, $u);
        $info->password = $newpass;
        Session::Messages($langQuotaSuccess, 'alert-success');
        redirect_to_home_page('modules/admin/edituser.php');
    }

    if (isset($_POST['delete_ext_uid'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        checkSecondFactorChallenge();
        Database::get()->query('DELETE FROM user_ext_uid WHERE user_id = ?d AND auth_id = ?d',
            $u, $_POST['delete_ext_uid']);
        Session::Messages($langSuccessfulUpdate, 'alert-success');
        redirect_to_home_page('modules/admin/edituser.php?u=' . $u);
    }

    // change user authentication method
    if (isset($_GET['edit']) and $_GET['edit'] = 'auth') {
        $data['current_auth'] = 1;
        $data['auth_names'][1] = get_auth_info(1);
        foreach (get_auth_active_methods() as $auth) {
            if($auth < 8) {
                $data['auth_names'][$auth] = get_auth_info($auth);
                if ($info->password == $auth_ids[$auth]) {
                    $data['current_auth'] = $auth;
                }
            }
        }
        $data['menuTypeID'] = 3;
        view('admin.users.edituserauth', $data);
        exit();    
    }
    if (!$u_submitted) { // if the form was not submitted
        // Display Actions Toolbar
        $ind_u = getIndirectReference($u);
        $data['action_bar'] = action_bar(array(
            array('title' => $langUserMerge,
                'url' => "mergeuser.php?u=" . getIndirectReference($u),
                'icon' => 'fa-share-alt',
                'level' => 'primary-label',
                'show' => ($u != 1 and get_admin_rights($u) < 0)),
            array('title' => $langChangePass,
                'url' => "password.php?userid=" . getIndirectReference($u),
                'icon' => 'fa-key',
                'level' => 'primary-label',
                'show' => !(in_array($info->password, $auth_ids))),
            array('title' => $langEditAuth,
                'url' => "$_SERVER[SCRIPT_NAME]?u=$u&amp;edit=auth",
                'icon' => 'fa-key',
                'level' => 'primary'),
            array('title' => $langDelUser,
                'url' => "deluser.php?u=$ind_u",
                'icon' => 'fa-times',
                'level' => 'primary',
                'show' => $u > 1),
            array('title' => $langBack,
                'url' => "listusers.php",
                'icon' => 'fa-reply',
                'level' => 'primary')
        ));



        if (in_array($info->password, $auth_ids)) {
            switch ($info->password) {
                case "pop3": $auth = 2;
                    break;
                case "imap": $auth = 3;
                    break;
                case "ldap": $auth = 4;
                    break;
                case "db": $auth = 5;
                    break;
                case "shibboleth": $auth = 6;
                    break;
                case "cas": $auth = 7;
                    break;
                default: $auth = 1;
                    break;
            }
            $data['auth_info'] = get_auth_info($auth);
        }

        $data['verified_mail_data'] = array();
        $data['verified_mail_data'][0] = $m['pending'];
        $data['verified_mail_data'][1] = $m['yes'];
        $data['verified_mail_data'][2] = $m['no'];


        if (isDepartmentAdmin()) {
            list($js, $html) = $tree->buildUserNodePickerIndirect(array('defaults' => $user->getDepartmentIds($u), 'allowables' => $user->getDepartmentIds($uid)));
        } else {
            list($js, $html) = $tree->buildUserNodePickerIndirect(array('defaults' => $user->getDepartmentIds($u)));
        }
        $head_content .= $js;
        $data['html'] = $html;
        $data['reg_date'] = DateTime::createFromFormat("Y-m-d H:i:s", $info->registered_at);
        $data['exp_date'] = DateTime::createFromFormat("Y-m-d H:i:s", $info->expires_at);

        // Show HybridAuth provider data
        $data['ext_uid'] = Database::get()->queryArray('SELECT * FROM user_ext_uid WHERE user_id = ?d', $u);
        
        $data['sql'] = Database::get()->queryArray("SELECT a.code, a.title, a.id, a.visible, DATE(b.reg_date) AS reg_date, b.status
                            FROM course AS a                            
                            LEFT JOIN course_user AS b ON a.id = b.course_id
                            WHERE b.user_id = ?s ORDER BY b.status", $u);
        $data['auth_ids'] = $auth_ids;
        $data['menuTypeID'] = 3;
        view('admin.users.edituser', $data);
        exit();            
    } else { // if the form was submitted then update user

        // get the variables from the form and initialize them
        $fname = isset($_POST['fname']) ? $_POST['fname'] : '';
        $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
        // trim white spaces in the end and in the beginning of the word
        $username = isset($_POST['username']) ?$_POST['username'] : '';
        $email = isset($_POST['email']) ? mb_strtolower(trim($_POST['email'])) : '';
        $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
        $am = isset($_POST['am']) ? $_POST['am'] : '';
        $departments = isset($_POST['department']) ? arrayValuesDirect($_POST['department']) : 'NULL';
        $newstatus = isset($_POST['newstatus']) ? $_POST['newstatus'] : 'NULL';
        $registered_at = isset($_POST['registered_at']) ? $_POST['registered_at'] : '';
        if (isset($_POST['user_date_expires_at'])) {
            $expires_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['user_date_expires_at']);
            $user_expires_at = $expires_at->format("Y-m-d H:i");
            $user_date_expires_at = $expires_at->format("d-m-Y H:i");
        }

        $user_upload_whitelist = isset($_POST['user_upload_whitelist']) ? $_POST['user_upload_whitelist'] : '';
        $user_exist = FALSE;
        // check if username is free
        if (Database::get()->querySingle("SELECT username FROM user
                                           WHERE id <> ?d AND
                                                 username = ?s", $u, $username)) {
            $user_exist = TRUE;
        }
        
        //check for validation errors in custom profile fields
        $cpf_check = cpf_validate_format();
        
        // check if there are empty fields
        if (empty($fname) or empty($lname) or empty($username) or cpf_validate_required_edituser() === false) {
            Session::Messages($langFieldsMissing, 'alert-danger');
            redirect_to_home_page('modules/admin/edituser.php?u=' . $u);
        } elseif (isset($user_exist) and $user_exist == true) {
            Session::Messages($langUserFree, 'alert-danger');
            redirect_to_home_page('modules/admin/edituser.php?u=' . $u);
        } elseif ($cpf_check[0] === false) {
            $cpf_error_str = '';
            unset($cpf_check[0]);
            foreach ($cpf_check as $cpf_error) {
                $cpf_error_str .= $cpf_error;
            }
            $tool_content .= "<div class='alert alert-danger'>$cpf_error_str <br>
                                <a href='$_SERVER[SCRIPT_NAME]'>$langAgain</a></div";
            Session::Messages("$cpf_error_str<br><a href='$_SERVER[SCRIPT_NAME]'>$langAgain</a>", 'alert-danger');
            redirect_to_home_page('modules/admin/edituser.php?u=' . $u);
        }

        if ($registered_at > $user_expires_at) {
            Session::Messages($langExpireBeforeRegister, 'alert-warning');
        }

        // email cannot be verified if there is no mail saved
        if (empty($email) and $verified_mail) {
            $verified_mail = 2;
        }

        // if depadmin then diff new/old deps and if new or deleted deps are out of juristinction, then error
        if (isDepartmentAdmin()) {
            $olddeps = $user->getDepartmentIds(intval($u));

            foreach ($departments as $depId) {
                if (!in_array($depId, $olddeps)) {
                    validateNode(intval($depId), true);
                }
            }

            foreach ($olddeps as $depId) {
                if (!in_array($depId, $departments)) {
                    validateNode($depId, true);
                }
            }
        }
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        checkSecondFactorChallenge();
        $user->refresh(intval($u), $departments);
        user_hook($u);
        $qry = Database::get()->query("UPDATE user SET surname = ?s,
                                givenname = ?s,
                                username = ?s,
                                email = ?s,
                                status = ?d,
                                phone = ?s,
                                expires_at = ?t,
                                am = ?s,
                                verified_mail = ?d,
                                whitelist = ?s
                      WHERE id = ?d", $lname, $fname, $username, $email, $newstatus, $phone, $user_expires_at, $am, $verified_mail, $user_upload_whitelist, $u);
            //update custom profile fields
            $cpf_updated = process_profile_fields_data(array('uid' => $u, 'origin' => 'admin_edit_profile'));
            if ($qry->affectedRows > 0 || $cpf_updated === true) {
                Session::Messages($langSuccessfulUpdate, 'alert-info');
        } else {
            Session::Messages($langUpdateNoChange, 'alert-warning');
        }
        redirect_to_home_page('modules/admin/edituser.php?u=' . $u);
    }
} else {
    redirect_to_home_page('modules/admin/listusers.php?search=yes');
}


