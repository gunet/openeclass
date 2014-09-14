<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


/* ===========================================================================
  change_user.php
  ==============================================================================
  @Description: Allows platform admin to login as another user without
  asking for a password
  ==============================================================================
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
$nameTools = $langChangeUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

if (isset($_REQUEST['username'])) {
    $sql = "SELECT user.id, surname, username, password, givenname, status, email,
                   admin.user_id AS is_admin, lang
                FROM user LEFT JOIN admin ON user.id = admin.user_id
                WHERE username ";

    if (get_config('case_insensitive_usernames')) {
        $sql .= '= ?s';
    } else {
        $sql .= 'COLLATE utf8_bin = ?s';
    }
    $myrow = Database::get()->querySingle($sql, $_REQUEST['username']);
    if ($myrow) {
        $_SESSION['uid'] = $myrow->id;
        $_SESSION['surname'] = $myrow->surname;
        $_SESSION['givenname'] = $myrow->givenname;
        $_SESSION['status'] = $myrow->status;
        $_SESSION['email'] = $myrow->email;
        $_SESSION['is_admin'] = !(!($myrow->is_admin)); // double 'not' to handle NULL
        $_SESSION['uname'] = $myrow->username;
        $_SESSION['langswitch'] = $myrow->lang;
        redirect_to_home_page();
    } else {
        $tool_content = "<div class='caution'>" . sprintf($langChangeUserNotFound, canonicalize_whitespace(q($_POST['username']))) . "</div>";
    }
}

$tool_content .= "<legend><form action='$_SERVER[SCRIPT_NAME]' method='post'>$langUsername: <input type='text' name='username' /></form></legend>";
draw($tool_content, 3);
