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

// sso transition
if (isset($_GET['transition'])) {
    if ($_GET['transition'] == 'true') {
                Session::flash('message',"ΠΡΟΣΟΧΗ! Θα ενεργοποιήσετε τη διαδικασία μετάβασης του τρόπου πιστοποίησης των χρηστών σε CAS (Single Sign-ON).
                Επιβεβαιώστε την ενέργειά σας.
                <ul>
                <li><a href='$_SERVER[SCRIPT_NAME]?do_transition=true'><strong>Ναι</strong></a></li>
                <li><a href='$_SERVER[SCRIPT_NAME]?do_transition=cancel'><strong>Όχι</strong></a></li>
                </ul>");
            Session::flash('alert-class', 'alert-warning');
    } else {
                Session::flash('message',"ΠΡΟΣΟΧΗ! Θα απενεργοποιήσετε τη διαδικασία μετάβασης του τρόπου πιστοποίησης των χρηστών σε CAS (Single Sign-ON).
                Επιβεβαιώστε την ενέργειά σας.
                <ul>
                <li><a href='$_SERVER[SCRIPT_NAME]?do_transition=false'><strong>Ναι</strong></a></li>
                <li><a href='$_SERVER[SCRIPT_NAME]?do_transition=cancel'><strong>Όχι</strong></a></li>
                </ul>");
            Session::flash('alert-class', 'alert-warning');
    }
    redirect_to_home_page('modules/admin/auth.php');
}

if (isset($_GET['do_transition'])) {
    if ($_GET['do_transition'] == 'true') {
        require_once 'modules/auth/transition/Transition.class.php';
        set_config('sso_transition', true);
        Transition::create_table();
        Session::flash('message',"Η διαδικασία μετάβασης των χρηστών στο τρόπο πιστοποίησης μέσω CAS ενεργοποιήθηκε.");
        Session::flash('alert-class', 'alert-success');
    } else if ($_GET['do_transition'] == 'false') {
        set_config('sso_transition', false);
        Session::flash('message',"Η διαδικασία μετάβασης των χρηστών στο τρόπο πιστοποίησης μέσω CAS απενεργοποιήθηκε.");
        Session::flash('alert-class', 'alert-success');
    } else {
        redirect_to_home_page('modules/admin/auth.php');
    }
}
// end of sso transition

if (isset($_GET['auth'])) {
    $auth = $_GET['auth'];
    if (isset($_GET['q'])) { // activate / deactivate authentication method
        $q = $_GET['q'];
        $s = get_auth_settings($auth);
        $settings = $s['auth_settings'];

        if (empty($settings) and $auth != 1) {
            Session::flash('message',"$langErrActiv $langActFailure");
            Session::flash('alert-class', 'alert-danger');
        } else {
            Database::get()->query("UPDATE auth SET auth_default = ?d WHERE auth_id = ?d", $q, $auth);
            Session::flash('message',($q? $langActSuccess: $langDeactSuccess) . get_auth_info($auth));
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page('modules/admin/auth.php');
    } elseif (isset($_GET['p'])) {// modify primary authentication method
        if ($_GET['p'] == 1) {
            Database::get()->query("UPDATE auth SET auth_default = 1 WHERE auth_default <> 0");
            Database::get()->query("UPDATE auth SET auth_default = 2 WHERE auth_id = ?d", $auth);
            Session::flash('message',$langPrimaryAuthTypeChanged);
            Session::flash('alert-class', 'alert-success');
        } else {
            Database::get()->query("UPDATE auth SET auth_default = 1 WHERE auth_id = ?d", $auth);
            Session::flash('message',$langSecondaryAuthTypeChanged);
            Session::flash('alert-class', 'alert-success');
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
                    'level' => 'primary')
                ),false);

$data['auth_ids'] = $auth_ids;
$data['auth_active_ids'] = $auth_active_ids = get_auth_active_methods();
$data['auth_disabled_ids'] = $auth_disabled_ids = $auth_ids;
$data['authMethods'] = $authMethods = Database::get()->queryArray("SELECT * FROM auth 
                            WHERE auth_default <> 0 OR auth_id = 1 OR auth_settings <> ''
                            ORDER BY auth_default DESC, auth_id");

foreach ($authMethods as $authMethod) {
    unset($auth_disabled_ids[$authMethod->auth_id]);
}

unset($auth_disabled_ids[14]); // Remove LTI users auth

$data['add_options'] = array_map(function ($auth_id) {
    return [
        'title' => get_auth_info($auth_id),
        'url' => "auth_process.php?auth=$auth_id",
        'icon' => 'fa-plus-circle'];
}, array_keys($auth_disabled_ids));

view ('admin.users.auth.index', $data);
