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
require_once 'admin.inc.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'hierarchy_validations.php';

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

load_js('jstree');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#id_expires_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', 
                pickerPosition: 'bottom-left', 
                language: '".$language."',
                autoclose: true    
            });
        });
    </script>";

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listusers.php', 'name' => $langListUsersActions);
$nameTools = $langEditUser;

$u_submitted = isset($_POST['u_submitted']) ? $_POST['u_submitted'] : '';

if ($u) {
    if (isDepartmentAdmin())
        validateUserNodes(intval($u), true);

    $info = Database::get()->querySingle("SELECT surname, givenname, username, password, email,
                              phone, registered_at, expires_at, status, am,
                              verified_mail, whitelist
                         FROM user WHERE id = ?s", $u);
    if (isset($_POST['submit_editauth'])) {
        $auth = intval($_POST['auth']);
        $oldauth = array_search($info->password, $auth_ids);
        $tool_content .= "<div class='alert alert-success'>$langQuotaSuccess.";
        if ($auth == 1 and $oldauth != 1) {
            $tool_content .= " <a href='password.php?userid=$u'>$langEditAuthSetPass</a>";
            $newpass = '.';
        } else {
            $newpass = $auth_ids[$auth];
        }
        $tool_content .= "</div>";
        Database::get()->query("UPDATE user SET password = ?s WHERE id = ?s", $newpass, $u);
        $info->password = $newpass;
    }
    if (isset($_GET['edit']) and $_GET['edit'] = 'auth') {
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?u=$u", 'name' => $langEditUser);
        $nameTools = $langEditAuth;
        $current_auth = 1;
        $auth_names[1] = get_auth_info(1);
        foreach (get_auth_active_methods() as $auth) {
            $auth_names[$auth] = get_auth_info($auth);
            if ($info->password == $auth_ids[$auth]) {
                $current_auth = $auth;
            }
        }
        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]'>
                        <fieldset>
                        <legend>$langEditAuth: " . q($info->username) . "</legend>
                        <table class='tbl' width='100%'>
                        <tr>
                          <th width='170' class='left'>$langEditAuthMethod</th>
                          <td>" . selection($auth_names, 'auth', intval($current_auth)) . "</td>
                        </tr>
                        <tr>
                          <th>&nbsp;</th>
                          <td class='right'>
                            <input type='hidden' name='u' value='$u'>
                            <input class='btn btn-primary' type='submit' name='submit_editauth' value='$langModify'>
                          </td>
                        </tr>
                        </table>
                        </fieldset>
                        </form>";
        draw($tool_content, 3, null, $head_content);
        exit;
    }
    if (!$u_submitted) { // if the form was not submitted
        $tool_content .= "<div id='operations_container'>
                     <ul id='opslist'>";
        if ($u != 1 and get_admin_rights($u) < 0) {
            $tool_content .= "<li><a href='mergeuser.php?u=$u'>$langUserMerge</a></li>\n";
        }
        if (!in_array($info->password, $auth_ids)) {
            $tool_content .= "<li><a href='password.php?userid=$u'>" . $langChangePass . "</a></li>";
        }
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;edit=auth'>$langEditAuth</a></li>
              <li><a href='deluser.php?u=$u'>$langDelUser</a></li>
              <li><a href='listusers.php'>$langBack</a></li>";
        $tool_content .= "</ul></div>";
        $tool_content .= "
                    <form name='edituser' method='post' action='$_SERVER[SCRIPT_NAME]' onsubmit='return validateNodePickerForm();'>
                    <fieldset>
                    <legend>$langEditUser: " . q($info->username) . "</legend>
                    <table class='tbl' width='100%'>
                    <tr>
                      <th width='170' class='left'>$langSurname:</th>
                      <td><input type='text' name='lname' size='50' value='" . q($info->surname) . "' /></td>
                    </tr>
                    <tr>
                      <th class='left'>$langName:</th>
                      <td><input type='text' name='fname' size='50' value='" . q($info->givenname) . "' /></td>
                   </tr>";

        if (!in_array($info->password, $auth_ids)) {
            $tool_content .= "
                   <tr>
                     <th class='left'>$langUsername:</th>
                     <td><input type='text' name='username' size='50' value='" . q($info->username) . "' /></td>
                   </tr>";
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
            $tool_content .= "
                <tr>
                <th class='left'>" . $langUsername . "</th>
                <td><b>" . q($info->username) . "</b> [" . $auth_text . "] <input type='hidden' name='username' value=" . q($info->username) . "> </td>
                </tr>";
        }
        $tool_content .= "
        <tr>
          <th class='left'>e-mail: </th>
          <td><input type='text' name='email' size='50' value='" . q(mb_strtolower(trim($info->email))) . "' /></td>
        </tr>";

        $tool_content .= "<tr>
       <th>$langEmailVerified: </th>
       <td>";
        $verified_mail_data = array();
        $verified_mail_data[0] = $m['pending'];
        $verified_mail_data[1] = $m['yes'];
        $verified_mail_data[2] = $m['no'];

        $tool_content .= selection($verified_mail_data, "verified_mail", intval($info->verified_mail));
        $tool_content .= "</td></tr>";

        $tool_content .= "
        <tr>
          <th class='left'>$langAm: </th>
          <td><input type='text' name='am' size='50' value='" . q($info->am) . "' /></td>
        </tr>
        <tr>
          <th class='left'>$langTel: </th>
          <td><input type='text' name='phone' size='50' value='" . q($info->phone) . "' /></td>
        </tr>
        <tr>
          <th class='left'>$langFaculty:</th>
        <td>";
        if (isDepartmentAdmin())
            list($js, $html) = $tree->buildUserNodePicker(array('defaults' => $user->getDepartmentIds($u), 'allowables' => $user->getDepartmentIds($uid)));
        else
            list($js, $html) = $tree->buildUserNodePicker(array('defaults' => $user->getDepartmentIds($u)));
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</td></tr>
        <tr>
          <th class='left'>$langProperty:</th>
          <td>";
        if ($info->status == USER_GUEST) { // if we are guest user do not display selection
            $tool_content .= selection(array(USER_GUEST => $langGuest), 'newstatus', intval($info->status));
        } else {
            $tool_content .= selection(array(USER_TEACHER => $langTeacher,
                USER_STUDENT => $langStudent), 'newstatus', intval($info->status));
        }
           //<input type='text' name='expires_at' value='" . datetime_remove_seconds($info->expires_at) . "'>
        $tool_content .= "</td>";
        $reg_date = DateTime::createFromFormat("Y-m-d H:i:s", $info->registered_at);        
        $tool_content .= "
        <tr>
          <th class='left'>$langRegistrationDate:</th>
          <td>" . $reg_date->format("d-m-Y H:i") . "</td>
        </tr>
        <tr>
         <th class='left'>$langExpirationDate: </th>
         <td>
         <div class='input-append date form-group' id='id_expires_at' data-date='" . $info->expires_at . "' data-date-format='dd-mm-yyyy'>
        <div class='col-xs-11'>        
            <input name='expires_at' type='text' value='$info->expires_at'>
        </div>
        <span class='add-on'><i class='fa fa-times'></i></span>
        <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>          
         </td></tr>
        <tr>
          <th>$langUserID: </th>
          <td>$u</td>
        </tr>
        <tr>
          <th>$langUserWhitelist</th>
          <td><textarea rows='6' cols='60' name='user_upload_whitelist'>" . q($info->whitelist) . "</textarea></td>
        </tr>
        <tr>
        <th>&nbsp;</th>
        <td class='right'>
	    <input type='hidden' name='u' value='$u' />
	    <input type='hidden' name='u_submitted' value='1' />
	    <input type='hidden' name='registered_at' value='" . $info->registered_at . "' />
	    <input class='btn btn-primary' type='submit' name='submit_edituser' value='$langModify' />
       </td>
     </tr>
     </table>
     </fieldset>
     </form>";
        $sql = Database::get()->queryArray("SELECT a.code, a.title, a.id, a.visible, b.reg_date, b.status
                            FROM course AS a
                            JOIN course_department ON a.id = course_department.course
                            JOIN hierarchy ON course_department.department = hierarchy.id
                            LEFT JOIN course_user AS b ON a.id = b.course_id
                            WHERE b.user_id = ?s ORDER BY b.status, hierarchy.name", $u);

        // user is registered to courses
        if (count($sql) > 0) {
            $tool_content .= "<p class='title1'>$langStudentParticipation</p>
                    <table class='tbl_alt' width='100%'>
                    <tr>
                    <th colspan='2'><div align='left'>$langCode</div></th>
                    <th><div align='left'>$langLessonName</div></th>
                    <th>$langCourseRegistrationDate</th><th>$langProperty</th><th>$langActions</th>
                    </tr>";
            $k = 0;
            foreach ($sql as $logs) {
                if ($logs->visible == COURSE_INACTIVE) {
                    $tool_content .= "<tr class='invisible'>";
                } else {
                    if ($k % 2 == 0) {
                        $tool_content .= "<tr class='even'>";
                    } else {
                        $tool_content .= "<tr class='odd'>";
                    }
                }
                $tool_content .= "<td width='1'><img src='$themeimg/arrow.png' title='bullet'></td>
                        <td><a href='{$urlServer}courses/$logs->code/'>" . q($logs->code) . "</a></td>
                        <td>" . q($logs->title) . "</td><td align='center'>";
                if ($logs->reg_date == '0000-00-00') {
                    $tool_content .= $langUnknownDate;
                } else {
                    $tool_content .= " " . nice_format($logs->reg_date) . " ";
                }
                $tool_content .= "</td><td align='center'>";
                if ($logs->status == 1) {
                    $tool_content .= $langTeacher;
                    $tool_content .= "</td><td align='center'>---</td></tr>\n";
                } else {
                    if ($logs->status == 5) {
                        $tool_content .= $langStudent;
                    } else {
                        $tool_content .= $langVisitor;
                    }
                    $tool_content .= "</td><td align='center'>" .
                            icon('fa-ban', $langUnregCourse, "unreguser.php?u=$u&amp;c=$logs->id") . "</tr>\n";
                }
                $k++;
            }
            $tool_content .= "</table>";
        } else {
            $tool_content .= "<div class='alert alert-danger'>$langNoStudentParticipation</div>";
            if ($u > 1) {
                $tool_content .= "<p class='btn btn-danger'><a href='unreguser.php?u=$u'>$langDelete</a></p>";
            } else {
                $tool_content .= "<div class='alert alert-danger'>$langCannotDeleteAdmin</div>";
            }
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
        $departments = isset($_POST['department']) ? $_POST['department'] : 'NULL';
        $newstatus = isset($_POST['newstatus']) ? $_POST['newstatus'] : 'NULL';
        $registered_at = isset($_POST['registered_at']) ? $_POST['registered_at'] : '';        
        $expires_at = isset($_POST['expires_at']) ? $_POST['expires_at'] : '';          
        $user_expires_at = DateTime::createFromFormat("Y-m-d H:i:s", $expires_at);
        $date_expires_at = $user_expires_at->format("Y-m-d H:i:s");        
        $user_upload_whitelist = isset($_POST['user_upload_whitelist']) ? $_POST['user_upload_whitelist'] : '';
        $user_exist = FALSE;
        // check if username is free
        if (Database::get()->querySingle("SELECT username FROM user
                                           WHERE id <> ?s AND
                                                 username = ?s", $u, $username)) {
            $user_exist = TRUE;
        }

        // check if there are empty fields
        if (empty($fname) or empty($lname) or empty($username)) {
            $tool_content .= "<table width='99%'><tbody><tr>
                                <td class='alert alert-danger' height='60'><p>$langFieldsMissing</p>
                                  <p><a href='$_SERVER[SCRIPT_NAME]'>$langAgain</a></p>
                                </td></tr></tbody></table><br><br>";
            draw($tool_content, 3, ' ', $head_content);
            exit();
        } elseif (isset($user_exist) and $user_exist == true) {
            $tool_content .= "<table width='100%'><tbody><tr>
                                <td class='alert alert-danger' height='60'><p>$langUserFree</p>
                                  <p><a href='$_SERVER[SCRIPT_NAME]'>$langAgain</a></p>
                                </td></tr></tbody></table><br><br>";
            draw($tool_content, 3, null, $head_content);
            exit();
        }
        
        if ($registered_at > $date_expires_at) {            
            $tool_content .= "<center><br /><b>$langExpireBeforeRegister<br /><br />
                    <a href='edituser.php?u=$u'>$langAgain</a></b><br />";
        } else {
            if ($u == 1)
                $departments = array();

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
                          WHERE id = ?d", $lname, $fname, $username, $email, $newstatus, $phone, $date_expires_at, $am, $verified_mail, $user_upload_whitelist, $u);
            if ($qry->affectedRows > 0) {
                    $tool_content .= "<center><br /><b>$langSuccessfulUpdate</b><br /><br />";                
            } else {                                                
                    $tool_content .= "<center><br /><b>$langUpdateNoChange</b><br /><br />";                
            }
            $tool_content .= "<a href='listusers.php'>$langBack</a></center>";
        }
    }
} else {
    $tool_content .= "<h1>$langError</h1><p><a href='listcours.php'>$back</p>";
}
draw($tool_content, 3, null, $head_content);
