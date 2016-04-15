<?php

/* ========================================================================
 * Open eClass 3.3
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * @brief define authentication methods and settings
 * @file auth.php
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

if (isset($_GET['auth'])) {
    $auth = getDirectReference($_GET['auth']);
    if (isset($_GET['q'])) { // activate / deactivate authentication method
        $q = $_GET['q'];
        $s = get_auth_settings($auth);
        $settings = $s['auth_settings'];

        if (empty($settings) and $auth != 1) {
            Session::Messages("$langErrActiv $langActFailure", 'alert-danger');
        } else {
            Database::get()->query("UPDATE auth SET auth_default = ?d WHERE auth_id = ?d", $q, $auth);
            Session::Messages(($q? $langActSuccess: $langDeactSuccess) . get_auth_info($auth), 'alert-success');
        }
        redirect_to_home_page('modules/admin/auth.php');
    } elseif (isset($_GET['p'])) {// modify primary authentication method
        if ($_GET['p'] == 1) {
            Database::get()->query("UPDATE auth SET auth_default = 1 WHERE auth_default <> 0");
            Database::get()->query("UPDATE auth SET auth_default = 2 WHERE auth_id = ?d", $auth);
            Session::Messages($langPrimaryAuthTypeChanged, 'alert-success');
        } else {
            Database::get()->query("UPDATE auth SET auth_default = 1 WHERE auth_id = ?d", $auth);
            Session::Messages($langSecondaryAuthTypeChanged, 'alert-success');
        }
        redirect_to_home_page('modules/admin/auth.php');
    }
}

$toolName = $langUserAuthentication;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$action_bar = action_bar(array(
                array('title' => $langBack,
                    'url' => "$_SERVER[PHP_SELF]",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ),false);

$data['auth_ids'] = $auth_ids;
$data['auth_active_ids'] = $auth_active_ids = get_auth_active_methods();
$data['authMethods'] = $authMethods = Database::get()->queryArray("SELECT * FROM auth ORDER BY auth_default DESC, auth_id");
    
$data['menuTypeID'] = 3;
view ('admin.users.auth', $data);

