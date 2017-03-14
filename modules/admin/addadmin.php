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

$toolName = $langAdmins;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

// Initialize the incoming variables
$username = isset($_POST['username']) ? $_POST['username'] : '';

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

if (isset($_POST['submit']) and ! empty($username)) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
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
                $tool_content .= "<div class='alert alert-success'>
                    $langTheUser <b>" . q($username) . "</b> $langDone</div>";
            }
        } else {
            $tool_content .= "<div class='alert alert-danger'>$langError</div>";
        }
    } else {
        $tool_content .= "<div class='alert alert-danger'>$langTheUser " . q($username) . " $langNotFound.</div>";
    }
} else if (isset($_GET['delete'])) { // delete admin users
    $aid = intval(getDirectReference($_GET['aid']));
    if ($aid != 1) { // admin user (with id = 1) cannot be deleted
        if (Database::get()->query("DELETE FROM admin WHERE admin.user_id = ?d", $aid)->affectedRows > 0) {
            $tool_content .= "<div class='alert alert-success'>$langNotAdmin</div>";
        } else {
            $tool_content .= "<div class='alert alert-danger'>$langDeleteAdmin" . q($aid) . " $langNotFeasible</p>";
        }
    } else {
        $tool_content .= "<div class='alert alert-danger'>$langCannotDeleteAdmin</div>";
    }
}

$tool_content .= printform($langUsername);

$tool_content .= "<table class='table-default'>
        <tr>
          <th class='center'>ID</th>
          <th>$langSurnameName</th>
          <th>$langUsername</th>
          <th class='center'>$langRole</th>
          <th class='text-center'>" . icon('fa-gears') . "</th>
        </tr>";

// Display the list of admins
Database::get()->queryFunc("SELECT id, givenname, surname, username, admin.privilege as privilege
                    FROM user, admin
                    WHERE user.id = admin.user_id
                    ORDER BY id", function ($row) use (&$tool_content, $langAdministrator, $langPowerUser, $langManageUser, $langManageDepartment, $themeimg, $langDelete) {
    $tool_content .= "<tr>
        <td align='left'>" . q($row->id) . ".</td>
        <td>" . q($row->givenname) . " " . q($row->surname) . "</td>
        <td>" . q($row->username) . "</td>";
    switch ($row->privilege) {
        case '0': $message = $langAdministrator;
            break;
        case '1': $message = $langPowerUser;
            break;
        case '2': $message = $langManageUser;
            break;
        case '3' : $message = $langManageDepartment;
            break;
    }
    $tool_content .= "<td align='left'>$message</td>";
    if ($row->id != 1) {
        $tool_content .="<td class='center'>" .
                action_button(array(
                    array('title' => $langDelete,
                        'url' => "$_SERVER[SCRIPT_NAME]?delete=1&amp;aid=" . q(getIndirectReference($row->id)),
                        'class' => 'delete',
                        'icon' => 'fa-times'),
                )) .
                "</td>";
    } else {
        $tool_content .= "<td class='center'></td>";
    }
    $tool_content .= "</tr>";
});
$tool_content .= "</table>";

draw($tool_content, 3);


/**
 * @brief display administrator search form for grantint user administrator privileges
 * @global type $langAdd
 * @global type $langAdministrator
 * @global type $langPowerUser
 * @global type $langManageUser
 * @global type $langAddRole
 * @global type $langHelpAdministrator
 * @global type $langHelpPowerUser
 * @global type $langHelpManageUser
 * @global type $langUserFillData
 * @global type $langManageDepartment
 * @global type $langHelpManageDepartment
 * @param type $message
 * @return string
 */
function printform($message) {

    global $langAdd, $langAdministrator, $langPowerUser, $langManageUser, $langAddRole,
    $langHelpAdministrator, $langHelpPowerUser, $langHelpManageUser, $langUsername,
    $langManageDepartment, $langHelpManageDepartment;

    $ret = "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' name='makeadmin' action='$_SERVER[SCRIPT_NAME]'>";
    $ret .= "<fieldset>
                <div class='form-group'>
                    <label for='username' class='col-sm-2 control-label'>" . $message . "</label>
                    <div class='col-sm-10'><input class='form-control' type='text' name='username' size='30' maxlength='30' placeholder='$langUsername'></div>
                </div>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langAddRole</label>
                        <div class='col-sm-10'>
                            <div class='radio'>
                                <input type='radio' name='adminrights' value='admin' checked>$langAdministrator<span class='help-block'><small>$langHelpAdministrator</small></span>
                            </div>
                            <div class='radio'>
                                <input type='radio' name='adminrights' value='poweruser'>$langPowerUser<span class='help-block'><small>$langHelpPowerUser&nbsp;</small></span>
                            </div>
                            <div class='radio'>
                                <input type='radio' name='adminrights' value='manageuser'>$langManageUser<span class='help-block'><small>$langHelpManageUser</small></span>
                            </div>
                            <div class='radio'>
                                <input type='radio' name='adminrights' value='managedepartment'>$langManageDepartment<span class='help-block'><small>$langHelpManageDepartment</small></span>
                            </div>
                        </div>
                    </label>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' type='submit' name='submit' value='$langAdd'>
                    </div>
                </div>       
            </fieldset>
            ". generate_csrf_token_form_field() ."
            </form>
        </div>";
    return $ret;
}
