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
    $u = intval($_REQUEST['u']);
    $_SESSION['u_tmp'] = $u;
}

if (!isset($_REQUEST['u'])) {
    $u = $_SESSION['u_tmp'];
}

$verified_mail = isset($_REQUEST['verified_mail']) ? intval($_REQUEST['verified_mail']) : 2;

load_js('jstree3');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_expires_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-left',
                language: '".$language."',
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

    $info = Database::get()->querySingle("SELECT surname, givenname, username, password, email,
                              phone, registered_at, expires_at, status, am,
                              verified_mail, whitelist
                         FROM user WHERE id = ?s", $u);
    if (isset($_POST['submit_editauth'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $auth = intval($_POST['auth']);
        $oldauth = array_search($info->password, $auth_ids);
        $tool_content .= "<div class='alert alert-success'>$langQuotaSuccess.";
        if ($auth == 1 and $oldauth != 1) {
            $tool_content .= " <a href='password.php?userid=" . getIndirectReference($u) . "'>$langEditAuthSetPass</a>";
            $newpass = '.';
        } else {
            $newpass = $auth_ids[$auth];
        }
        $tool_content .= "</div>";
        Database::get()->query("UPDATE user SET password = ?s WHERE id = ?s", $newpass, $u);
        $info->password = $newpass;
    }

    if (isset($_POST['delete_ext_uid'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        Database::get()->query('DELETE FROM user_ext_uid WHERE user_id = ?d AND auth_id = ?d',
            $u, $_POST['delete_ext_uid']);
        Session::Messages($langSuccessfulUpdate, 'alert-success');
        redirect_to_home_page('modules/admin/edituser.php?u=' . $u);
    }

    // change user authentication method
    if (isset($_GET['edit']) and $_GET['edit'] = 'auth') {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?u=$u", 'name' => $langEditUser);
        $pageName = "$langEditAuth ". q($info->username);
        $current_auth = 1;
        $auth_names[1] = get_auth_info(1);
        foreach (get_auth_active_methods() as $auth) {
            if($auth < 8) {
                $auth_names[$auth] = get_auth_info($auth);
                if ($info->password == $auth_ids[$auth]) {
                    $current_auth = $auth;
                }
            }
        }
        $tool_content .= "<div class='form-wrapper'>
                            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]'>
                            <fieldset>
                            <div class='form-group'>
                            <label class='col-sm-2 control-label'>$langEditAuthMethod:</label>
                              <div class='col-sm-10'>" . selection($auth_names, 'auth', intval($current_auth), "class='form-control'") . "</div>
                            </div>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <input class='btn btn-primary' type='submit' name='submit_editauth' value='$langModify'>
                              </div>
                            <input type='hidden' name='u' value='$u'>
                            </fieldset>
                            ". generate_csrf_token_form_field() ."
                            </form>
                            </div>";
        draw($tool_content, 3, null, $head_content);
        exit;
    }
    if (!$u_submitted) { // if the form was not submitted
        // Display Actions Toolbar
        $ind_u = getIndirectReference($u);
        $tool_content .= action_bar(array(
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

        $tool_content .= "<div class='form-wrapper'>
                    <form class='form-horizontal' role='form' name='edituser' method='post' action='$_SERVER[SCRIPT_NAME]' onsubmit='return validateNodePickerForm();'>
                    <fieldset>
                    <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langSurname</label>
                      <div class='col-sm-10'>
                        <input class='form-control' type='text' name='lname' size='50' value='" . q($info->surname) . "'>
                      </div>
                    </div>
                    <div class='form-group'>
                      <label class='col-sm-2 control-label'>$langName</label>
                       <div class='col-sm-10'>
                        <input  class='form-control' type='text' name='fname' size='50' value='" . q($info->givenname) . "'>
                        </div>
                   </div>";
            $tool_content .= "<div class='form-group'>
                     <label class='col-sm-2 control-label'>$langUsername</label>";
        if (!in_array($info->password, $auth_ids)) {
            $tool_content .= "<div class='col-sm-10'>
                            <input  class='form-control' type='text' name='username' size='50' value='" . q($info->username) . "'>
                        </div>";

        } else {    // means that it is external auth method, so the user cannot change this password
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
            $auth_text = get_auth_info($auth);
            $tool_content .= "<div class='col-sm-10'>
                                <p class='form-control-static'><b>" . q($info->username) . "</b> [" . $auth_text . "] <input  class='form-control' type='hidden' name='username' value=" . q($info->username) . "></p>
                            </div>";
        }
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>
          <label class='col-sm-2 control-label'>e-mail</label>
          <div class='col-sm-10'><input  class='form-control' type='text' name='email' size='50' value='" . q(mb_strtolower(trim($info->email))) . "' /></div>
        </div>";

        $tool_content .= "<div class='form-group'>
            <label class='col-sm-2 control-label'>$langEmailVerified: </label>
            <div class='col-sm-10'>";
        $verified_mail_data = array();
        $verified_mail_data[0] = $m['pending'];
        $verified_mail_data[1] = $m['yes'];
        $verified_mail_data[2] = $m['no'];

        $tool_content .= selection($verified_mail_data, "verified_mail", intval($info->verified_mail), "class='form-control'");
        $tool_content .= "</div></div>";

        $tool_content .= "<div class='form-group'>
        <label class='col-sm-2 control-label'>$langAm: </label>
          <div class='col-sm-10'><input  class='form-control' type='text' name='am' size='50' value='" . q($info->am) . "' /></div>
        </div>
        <div class='form-group'>
          <label class='col-sm-2 control-label'>$langTel: </label>
          <div class='col-sm-10'><input  class='form-control' type='text' name='phone' size='50' value='" . q($info->phone) . "' /></div>
        </div>
        <div class='form-group'>
          <label class='col-sm-2 control-label'>$langFaculty:</label>
        <div class='col-sm-10'>";
        if (isDepartmentAdmin()) {
            list($js, $html) = $tree->buildUserNodePickerIndirect(array('defaults' => $user->getDepartmentIds($u), 'allowables' => $user->getDepartmentIds($uid)));
        } else {
            list($js, $html) = $tree->buildUserNodePickerIndirect(array('defaults' => $user->getDepartmentIds($u)));
        }
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</div></div>
        <div class='form-group'>
          <label class='col-sm-2 control-label'>$langProperty:</label>
          <div class='col-sm-10'>";
        if ($info->status == USER_GUEST) { // if we are guest user do not display selection
            $tool_content .= selection(array(USER_GUEST => $langGuest), 'newstatus', intval($info->status), "class='form-control'");
        } else {
            $tool_content .= selection(array(USER_TEACHER => $langTeacher,
                USER_STUDENT => $langStudent), 'newstatus', intval($info->status), "class='form-control'");
        }
        $tool_content .= "</div></div>";
        $reg_date = DateTime::createFromFormat("Y-m-d H:i:s", $info->registered_at);
        $exp_date = DateTime::createFromFormat("Y-m-d H:i:s", $info->expires_at);
        $tool_content .= "<div class='form-group'>
                <label class='col-sm-2 control-label'>$langRegistrationDate:</label>
                <div class='col-sm-10'><p class='form-control-static'>" . $reg_date->format("d-m-Y H:i") . "</p></div>
            </div>
         <div class='input-append date form-group' id='user_date_expires_at' data-date='" . $exp_date->format("d-m-Y H:i") . "' data-date-format='dd-mm-yyyy'>
         <label class='col-sm-2 control-label'>$langExpirationDate: </label>
            <div class='col-xs-10 col-sm-9'>
                <input class='form-control' name='user_date_expires_at' type='text' value='" . $exp_date->format("d-m-Y H:i") . "'>
            </div>
        <div class='col-xs-2 col-sm-1'>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>
         </div>
        <div class='form-group'>
          <label class='col-sm-2 control-label'>$langUserID: </label>
          <div class='col-sm-10'><p class='form-control-static'>$u</p></div>
        </div>
        <div class='form-group'>
          <label class='col-sm-2 control-label'>$langUserWhitelist</label>
          <div class='col-sm-10'><textarea rows='6' cols='60' name='user_upload_whitelist'>" . q($info->whitelist) . "</textarea></div>
        </div>";
        // Show HybridAuth provider data
        $ext_uid = Database::get()->queryArray('SELECT * FROM user_ext_uid WHERE user_id = ?d', $u);
        if ($ext_uid) {
            $tool_content .= "<div class='form-group'>
                <label class='col-sm-2 control-label'>$langProviderConnectWith:</label>
                <div class='col-sm-10'>
                    <div class='row'>";
            foreach ($ext_uid as $ext_uid_item) {
                $lcProvider = $auth_ids[$ext_uid_item->auth_id];
                $providerName = $authFullName[$ext_uid_item->auth_id];
                $tool_content .= "
                        <div class='col-xs-2 text-center'>
                          <img src='$themeimg/$lcProvider.png' alt='$langLoginVia'><br>$providerName<br>
                          <button type='submit' name='delete_ext_uid' value='$ext_uid_item->auth_id'>$langProviderDeleteConnection</button>
                        </div>";
                
            }
            $tool_content .= "</div></div></div>";
        }
        //show custom profile fields input
        if ($info->status != USER_GUEST) {
            $tool_content .= render_profile_fields_form(array('origin' => 'admin_edit_profile', 'user_id' => $u));
        }
        $tool_content .= "<input type='hidden' name='u' value='$u' />
        <input type='hidden' name='u_submitted' value='1' />
        <input type='hidden' name='registered_at' value='" . $info->registered_at . "' />
        <div class='col-sm-offset-2 col-sm-10'>
	    <input class='btn btn-primary' type='submit' name='submit_edituser' value='$langModify' />
        </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."
        </form>
        </div>";
        $sql = Database::get()->queryArray("SELECT a.code, a.title, a.id, a.visible, DATE(b.reg_date) AS reg_date, b.status
                            FROM course AS a
                            JOIN course_department ON a.id = course_department.course
                            JOIN hierarchy ON course_department.department = hierarchy.id
                            LEFT JOIN course_user AS b ON a.id = b.course_id
                            WHERE b.user_id = ?s ORDER BY b.status, hierarchy.name", $u);

        // user is registered to courses
        if (count($sql) > 0) {
            $tool_content .= "<h4>$langStudentParticipation</h4>
                    <div class='table-responsive'>
                    <table class='table-default'>
                    <tr>
                    <th class='text-left'>$langCode</th>
                    <th class='text-left'>$langLessonName</th>
                    <th>$langCourseRegistrationDate</th>
                    <th>$langProperty</th>
                    <th>$langActions</th>
                    </tr>";
            foreach ($sql as $logs) {
                if ($logs->visible == COURSE_INACTIVE) {
                    $tool_content .= "<tr class='not_visible'>";
                }
                $tool_content .= "<td><a href='{$urlServer}courses/$logs->code/'>" . q($logs->code) . "</a></td>
                        <td>" . q($logs->title) . "</td><td align='center'>";
                if ($logs->reg_date == '0000-00-00') {
                    $tool_content .= $langUnknownDate;
                } else {
                    $tool_content .= " " . nice_format($logs->reg_date) . " ";
                }
                $tool_content .= "</td><td class='text-center'>";
                if ($logs->status == USER_TEACHER) {
                    $tool_content .= $langTeacher;
                    $tool_content .= "</td><td align='center'>---</td></tr>\n";
                } else {
                    if ($logs->status == USER_STUDENT) {
                        $tool_content .= $langStudent;
                    } else {
                        $tool_content .= $langVisitor;
                    }
                    $tool_content .= "</td><td class='text-center'>" .
                            icon('fa-ban', $langUnregCourse, "unreguser.php?u=$u&amp;c=$logs->id") . "</tr>";
                }
            }
            $tool_content .= "</table></div>";
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langNoStudentParticipation</div>";
        }
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
            draw($tool_content, 3, null, $head_content);
            exit();
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
    $tool_content .= "<div class='alert alert-danger'>$langError <a href='listcours.php'>$back</a></div>";
}
draw($tool_content, 3, null, $head_content);
