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
$toolName = $langUserAuthentication;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tool_content .= action_bar([
    [ 'title' => $langBack,
      'url' => "{$urlAppend}modules/admin/",
      'icon' => 'fa-reply',
      'level' => 'primary-label' ],
], false);

// sso transition
if (isset($_GET['transition'])) {
    if ($_GET['transition'] == 'true') {
        Session::Messages("ΠΡΟΣΟΧΗ! Θα ενεργοποιήσετε τη διαδικασία μετάβασης του τρόπου πιστοποίησης των χρηστών σε CAS (Single Sign-ON).
                                Επιβεβαιώστε την ενέργειά σας.
                                <ul>
                                <li><a href='$_SERVER[SCRIPT_NAME]?do_transition=true'><strong>Ναι</strong></a></li>
                                <li><a href='$_SERVER[SCRIPT_NAME]?do_transition=cancel'><strong>Όχι</strong></a></li>
                                </ul>", 'alert-warning');
    } else {
        Session::Messages("ΠΡΟΣΟΧΗ! Θα απενεργοποιήσετε τη διαδικασία μετάβασης του τρόπου πιστοποίησης των χρηστών σε CAS (Single Sign-ON).
                                Επιβεβαιώστε την ενέργειά σας.
                                <ul>
                                <li><a href='$_SERVER[SCRIPT_NAME]?do_transition=false'><strong>Ναι</strong></a></li>
                                <li><a href='$_SERVER[SCRIPT_NAME]?do_transition=cancel'><strong>Όχι</strong></a></li>
                                </ul>", 'alert-warning');
    }
    redirect_to_home_page('modules/admin/auth.php');
}

if (isset($_GET['do_transition'])) {
    if ($_GET['do_transition'] == 'true') {
        require_once 'modules/auth/transition/Transition.class.php';
        set_config('sso_transition', true);
        Transition::create_table();
        Session::Messages("Η διαδικασία μετάβασης των χρηστών στο τρόπο πιστοποίησης μέσω CAS ενεργοποιήθηκε.", 'alert-success');
    } else if ($_GET['do_transition'] == 'false') {
        set_config('sso_transition', false);
        Session::Messages("Η διαδικασία μετάβασης των χρηστών στο τρόπο πιστοποίησης μέσω CAS απενεργοποιήθηκε.", 'alert-success');
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
} else {
    $auth_active_ids = get_auth_active_methods();
    $tool_content .= "<div class='alert alert-info'><label>$langMethods</label><ul>";
    foreach ($auth_ids as $auth_id => $auth_name) {
        $auth_count = count_auth_users($auth_id);
        $auth_active = in_array($auth_id, $auth_active_ids);
        if ($auth_count > 0 or $auth_active) {
            $auth_search_link = ($auth_count == 0)? '0':
                "<a href='listusers.php?fname=&amp;lname=&amp;am=&amp;user_type=0&amp;auth_type=$auth_id&amp;reg_flag=1&amp;user_registered_at=&verified_mail=3&amp;email=&amp;uname=&amp;department=0'>$auth_count</a>";
            if ($auth_id != 1 and $auth_count > 0) {
                $auth_change_link = " - <a href='auth_change.php?auth=$auth_id'>$langAuthChangeUser</a>";
            } else {
                $auth_change_link = '';
            }
            if (!$auth_active) {
                $auth_warn = "<br><span class='label label-warning'>$langAuthWarnInactive</span>";
            } else {
                $auth_warn = '';
            }
            $tool_content .= "<li>" . q(get_auth_info($auth_id)) . " ($langNbUsers: $auth_search_link$auth_change_link)$auth_warn</li>";
        }
    }
    $tool_content .= "</ul></div>";
    $authMethods = Database::get()->queryArray("SELECT * FROM auth
        WHERE auth_default <> 0 OR auth_id = 1 OR auth_settings <> ''
        ORDER BY auth_default DESC, auth_id");
    $tool_content .= "<div class='table-responsive'><table class='table-default'>";
    $tool_content .= "<th>$langAllAuthTypes</th><th class='text-right'>".icon('fa-gears', $langActions)."</th>";
    $auth_disabled_ids = $auth_ids;
    foreach ($authMethods as $info) {
        unset($auth_disabled_ids[$info->auth_id]);
        $auth_id = $info->auth_id;
        $auth_name = $info->auth_name;
        $active = $info->auth_default;
        $primary = $info->auth_default > 1;
        $primaryLabel = $primary? "&nbsp;&nbsp;<small><span class='label label-default'>$langPrimaryAuthType</span></small>": '';
        $visibility = $active? '': ' class=not_visible';
        $activation_url = "auth.php?auth=$auth_id&amp;q=" . !$active;
        $activation_title = $active? $langDeactivate: $langActivate;
        $activation_icon = $active? 'fa-toggle-off': 'fa-toggle-on';
        $tool_content .= "<tr><td$visibility>" . strtoupper($auth_name) . "$primaryLabel</td><td class='option-btn-cell'>";
        $tool_content .= action_button(array(
            array('title' => $langAuthSettings,
                  'url' => "auth_process.php?auth=$auth_id",
                  'icon' => 'fa-gear'),
            array('title' => $langPrimaryAuthType,
                  'url' => "auth.php?auth=$auth_id&amp;p=1",
                  'icon' => 'fa-flag',
                  'show' => $active and !$primary),
            array('title' => $langSecondaryAuthType,
                  'url' => "auth.php?auth=$auth_id&amp;p=0",
                  'icon' => 'fa-circle-o',
                  'show' => $primary),
            array('title' => $langConnTest,
                  'url' => "auth_test.php?auth=$auth_id",
                  'icon' => 'fa-plug',
                  'show' => $auth_id != 1 && $info->auth_settings),
            array('title' => $activation_title,
                  'url' => $activation_url,
                  'icon' => $activation_icon,
                  'show' => $auth_id == 1 || $info->auth_settings),
            array('title' => $langTransitionEnable,
                  'url' => "$_SERVER[SCRIPT_NAME]?transition=true",
                  'icon' => 'fa-bell',
                  'show' => ($auth_name == 'cas' && !get_config('sso_transition'))),
            array('title' => $langTransitionDisable,
                  'url' => "$_SERVER[SCRIPT_NAME]?transition=false",
                  'icon' => 'fa-bell-slash',
                  'show' => ($auth_name == 'cas' && !is_null(get_config('sso_transition')) && get_config('sso_transition'))),
            array('title' => $langTransitionExcludeReq,
                  'url' => "../auth/transition/admin_auth_transition.php",
                  'icon' => 'fa-exclamation',
                  'show' => ($auth_name == 'cas' && !is_null(get_config('sso_transition')) && get_config('sso_transition'))),
            ));
            $tool_content .= "</td><tr>";
    }
    unset($auth_disabled_ids[14]); // Remove LTI users auth
    $add_options = array_map(function ($auth_id) {
        return [
            'title' => get_auth_info($auth_id),
            'url' => "auth_process.php?auth=$auth_id",
            'icon' => 'fa-plus-circle'];
    }, array_keys($auth_disabled_ids));
    $tool_content .= "</table></div>
        <div class='row'>
            <div class='col-xs-12 text-right'>
                $langAddNewAuthMethod: " . action_button($add_options) . "
            </div>
        </div>";
}

draw($tool_content, 3);
