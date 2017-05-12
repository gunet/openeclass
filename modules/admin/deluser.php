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


$require_usermanage_user = true;
include '../../include/baseTheme.php';
$toolName = $langUnregUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$pageName = $langConfirmDelete;
$tool_content .= action_bar(array(
        array('title' => $langBackAdmin,
              'url' => "index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label')));

// get the incoming values and initialize them
if (isset($_GET['u'])) {
    $user = $_GET['u'];    
} else {
    forbidden();
}

if ($user) {
    $u_account = q(uid_to_name($user, 'username'));
    $u_realname = q(uid_to_name($user));
    $u_desc = "<em>$u_realname ($u_account)</em>";
}

if (!isset($_POST['doit'])) {
    if ($user) {
        if (get_admin_rights($user) > 0) {
            $tool_content .= "<div class='alert alert-warning'>" .
                sprintf($langCantDeleteAdmin, $u_desc) . ' ' .
                $langIfDeleteAdmin .
                "</div>";
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langConfirmDeleteQuestion1 $u_desc<br>
                $langConfirmDeleteQuestion3
              </div>
              <form method='post' action='$_SERVER[SCRIPT_NAME]?u=$user'>
                <input class='btn btn-danger' type='submit' name='doit' value='$langDelete'>
                ". generate_csrf_token_form_field() ."
              </form>";
        }
    } else {
        $tool_content .= "<div class='alert alert-danger'>$langErrorDelete</div>";
    }
} else {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (get_admin_rights($user) > 0) {
        Session::Messages($langTryDeleteAdmin, 'alert-danger');
        redirect_to_home_page("modules/admin/deluser.php?u=$user");
    } else {
        if (deleteUser($user, true)) {
            Session::Messages("$langWithUsername \"$u_account\" ($u_realname) $langWasDeleted.", 'alert-info');
        } else {
            Session::Messages($langErrorDelete, 'alert-danger');
        }
        redirect_to_home_page('modules/admin/listusers.php');
    }
}
draw($tool_content, 3);
