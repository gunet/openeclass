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


/* ===========================================================================
  auth_process.php
  @author: Kapetanakis Giannis <bilias@edu.physics.uoc.gr>
  ==============================================================================
  @Description: User Authentication Methods Change

  The admin can: - change authentication method for users

  ==============================================================================
 */

$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

if (isset($_GET['auth'])) {
    $auth = getDirectReference($_GET['auth']);
    $_SESSION['auth_temp'] = $auth;
}

if (!isset($auth)) {
    $auth = $_SESSION['auth_temp'];
}

$auth_change = isset($_REQUEST['auth_change']) ? intval($_REQUEST['auth_change']) : false;
register_posted_variables(array('submit' => true));

if ($submit && $auth && $auth_change) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (Database::get()->query("UPDATE user SET password=?s WHERE password=?s AND id != 1", $auth_ids[$auth_change], $auth_ids[$auth])->affectedRows >= 1) {
        Session::Messages($langAuthChangeYes, 'alert-success');
        redirect_to_home_page('modules/admin/auth.php');
    }
}

$auth_methods = get_auth_active_methods();
foreach ($auth_methods as $key => $value) {
    // remove current auth method
    if ($auth == $value or $value == 1) { // cannot change to eclass native method
        unset($auth_methods[$key]);
    }
}
foreach ($auth_methods as $value) {
    $data['auth_methods_active'][$value] = $auth_ids[$value];
}

$toolName = $langAuthChangeUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'auth.php', 'name' => $langUserAuthentication);

$data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                    'url' => "auth.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ));

$data['menuTypeID'] = 3;
view ('admin.users.auth.auth_change', $data);
