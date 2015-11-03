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
$toolName = $langUserAuthentication;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "$_SERVER[PHP_SELF]",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ),false);

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
} else {
    $auth_active_ids = get_auth_active_methods();
    $tool_content .= "<div class='alert alert-info'><label>$langMethods</label><ul>";
    foreach ($auth_ids as $auth_id => $auth_name) {
        $auth_count = count_auth_users($auth_id);
        $auth_active = in_array($auth_id, $auth_active_ids);
        if ($auth_count > 0 or $auth_active) {
            $auth_search_link = ($auth_count == 0)? '0':
                "<a href='listusers.php?fname=&amp;lname=&amp;am=&amp;user_type=0&amp;auth_type=$auth_id&amp;reg_flag=1&amp;user_registered_at=&verified_mail=3&amp;email=&amp;uname=&amp;department=" . getIndirectReferences(0) . "'>$auth_count</a>";
            if ($auth_id != 1 and $auth_count > 0) {
                $auth_change_link = " - <a href='auth_change.php?auth=" . getIndirectReference($auth_id) . "'>$langAuthChangeUser</a>";
            } else {
                $auth_change_link = '';
            }
            if (!$auth_active) {
                $auth_warn = "<br><span class='label label-warning'>$langAuthWarnInactive</span>";
            } else {
                $auth_warn = '';
            }
            $tool_content .= "<li>" . get_auth_info($auth_id) . " ($langNbUsers: $auth_search_link$auth_change_link)$auth_warn</li>";
        }
    }
    $tool_content .= "</ul></div>";

    $authMethods = Database::get()->queryArray("SELECT * FROM auth ORDER BY auth_default DESC, auth_id");
    $tool_content .= "<div class='table-responsive'><table class='table-default'>";
    $tool_content .= "<th>$langAllAuthTypes</th><th class='text-right'>".icon('fa-gears', $langActions)."</th>";
    foreach ($authMethods as $info) {
        $auth_id = $info->auth_id;
        $auth_name = $info->auth_name;
        $active = $info->auth_default;
        $primary = $info->auth_default > 1;
        $primaryLabel = $primary? "&nbsp;&nbsp;<small><span class='label label-default'>$langPrimaryAuthType</span></small>": '';
        $visibility = $active? '': ' class=not_visible';
        $activation_url = "$_SERVER[PHP_SELF]?auth=" . getIndirectReference($auth_id) . "&amp;q=" . !$active;
        $activation_title = $active? $langDeactivate: $langActivate;
        $activation_icon = $active? 'fa-toggle-off': 'fa-toggle-on';
        $tool_content .= "<tr><td$visibility>" . strtoupper($auth_name) . "$primaryLabel</td><td class='option-btn-cell'>";
        $tool_content .= action_button(array(
            array('title' => $activation_title,
                  'url' => $activation_url,
                  'icon' => $activation_icon,
                  'show' => $auth_id == 1 or $info->auth_settings),
            array('title' => $langAuthSettings,
                  'url' => "auth_process.php?auth=" . getIndirectReference($auth_id),
                  'icon' => 'fa-gear'),
            array('title' => $langPrimaryAuthType,
                  'url' => "$_SERVER[PHP_SELF]?auth=" . getIndirectReference($auth_id) . "&amp;p=1",
                  'icon' => 'fa-flag',
                  'show' => $active and !$primary),
            array('title' => $langSecondaryAuthType,
                  'url' => "$_SERVER[PHP_SELF]?auth=" . getIndirectReference($auth_id) . "&amp;p=0",
                  'icon' => 'fa-circle-o',
                  'show' => $primary)));
            $tool_content .= "</td><tr>";
    }
    $tool_content .= "</table></div>";
}

draw($tool_content, 3);

