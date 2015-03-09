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
 * @brief define authentication methods and settings
 * @file auth.php
 */


$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
$pageName = $langUserAuthentication;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$auth = isset($_GET['auth']) ? $_GET['auth'] : '';
$active = isset($_GET['active']) ? $_GET['active'] : '';

if (!empty($auth) and ! empty($active)) {
    $s = get_auth_settings($auth);        
    $settings = $s['auth_settings'];
    switch ($active) {
        case 'yes': if ($auth == 1) { // eclass method has no settings
                        $q = 1;
                    } else {
                        $q = empty($settings) ? 0 : 1;
                    }
            break;
        case 'no': $q = 0;
            break;
        default: $q = 0;
            break;
    }
    Database::get()->query("UPDATE auth SET auth_default = ?d WHERE auth_id = ?d", $q, $auth);
}

$auth_methods = get_auth_active_methods();

$tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "index.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ),false);

if (empty($auth)) {
    $tool_content .= "<div class='alert alert-info'><label>$langMethods</label>";
    if ($auth_methods) {
        $tool_content .= "<ul>";
        foreach ($auth_methods as $k => $v) {
            $c = count_auth_users($v);
            if ($c != 0) {                
                $lc = "<a href='listusers.php?fname=&amp;lname=&amp;am=&amp;user_type=0&amp;auth_type=$v&amp;reg_flag=1&amp;user_registered_at=&verified_mail=3&amp;email=&amp;uname=&amp;department=0'>$c</a>";
                if ($v != 1) {
                    $l = " - <a href='auth_change.php?auth=$v'>$langAuthChangeUser</a>";
                } else {
                    $l = "";
                }
            } else {
                $lc = 0;
                $l = "";
            }
            $tool_content .= "<li>" . get_auth_info($v) . " ($langNbUsers: $lc$l)</li>";
        }
        $tool_content .= "</ul>";
    }
    $tool_content .= "</div>";
} else {
    if (empty($settings) and $auth != 1) {
        $tool_content .= "<div class='alert alert-danger'>$langErrActiv $langActFailure</div>";
    } else {
        if ($active == 'yes') {
            $tool_content .= "<div class='alert alert-success'>";
            $tool_content .= "$langActSuccess" . get_auth_info($auth);
            $tool_content .= "</div>";
        } else {
            $tool_content .= "<div class='alert alert-success'>";
            $tool_content .= "$langDeactSuccess" . get_auth_info($auth);
            $tool_content .= "</div>";
        }
    }
}

$tool_content .= "<div class='table-responsive'><table class='table-default'>";
$tool_content .= "<th>$langAllAuthTypes</th><th class='text-center'>".icon('fa-gears', $langActions)."</th>";
foreach ($auth_ids as $auth_id => $auth_name) {
        $tool_content .= "<tr><td>".  strtoupper($auth_name).":</td><td class='option-btn-cell'>";
        if (in_array($auth_id, $auth_methods)) {
                $activation_url = "auth.php?auth=$auth_id&amp;active=no";
                $activation_title = $langDeactivate;
                $activation_icon = "fa-toggle-off";
        } else {
                $activation_url = "auth.php?auth=$auth_id&amp;active=yes";
                $activation_title = $langActivate;
                $activation_icon = "fa-toggle-on";
        }
        $tool_content .= action_button(array(
                array('title' => $activation_title,
                      'url' => $activation_url,
                      'icon' => $activation_icon),
                array('title' => $langAuthSettings,
                      'url' => "auth_process.php?auth=$auth_id",
                      'icon' => 'fa-gear',
                      'show' => $auth_id != 1)));
        $tool_content .= "</td><tr>";
}
$tool_content .= "</table></div>";

draw($tool_content, 3);
