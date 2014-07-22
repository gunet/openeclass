<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
  auth.php
  @last update: 27-06-2006 by Stratos Karatzidis
  @authors list: Karatzidis Stratos <kstratos@uom.gr>
  Vagelis Pitsioygas <vagpits@uom.gr>
  ==============================================================================
  @Description: Platform Authentication Methods and their settings

  This script displays the alternative methods of authentication
  and their settings.

  The admin can: - choose a method and define its settings

  ==============================================================================
 */

//$require_power_user = true;
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
$nameTools = $langUserAuthentication;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$auth = isset($_GET['auth']) ? $_GET['auth'] : '';
$active = isset($_GET['active']) ? $_GET['active'] : '';

if (!empty($auth) and ! empty($active)) {
    $s = get_auth_settings($auth);
    $settings = $s['auth_settings'];

    switch ($active) {
        case 'yes': $q = empty($settings) ? 0 : 1;
            break;
        case 'no': $q = 0;
            break;
        default: $q = 0;
            break;
    }
    Database::get()->query("UPDATE auth SET auth_default = ?d WHERE auth_id = ?d", $q, $auth);
}

$auth_methods = get_auth_active_methods();

if (empty($auth)) {
    $tool_content .= '<p>' . $langMethods . '</p>';
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
} else {
    if (empty($settings)) {
        $tool_content .= "<p class='caution'>$langErrActiv $langActFailure</p>";
    } else {
        if ($active == 'yes') {
            $tool_content .= "<p class='success'>";
            $tool_content .= "$langActSuccess" . get_auth_info($auth);
            $tool_content .= "</p>";
        } else {
            $tool_content .= "<p class='success'>";
            $tool_content .= "$langDeactSuccess" . get_auth_info($auth);
            $tool_content .= "</p>";
        }
    }
}

$tool_content .= "<table width='100%' class='tbl_alt'>
<tr>
<th colspan='3'>$langChooseAuthMethod</th>
</tr><tr><td width='90'><b>POP3:</b></td><td width='90'>[";

$tool_content .= in_array("2", $auth_methods) ? "<a class='add' href='auth.php?auth=2&amp;active=no'>" . $langDeactivate . "</a>]" : "<a class='revoke'  href=\"auth.php?auth=2&amp;active=yes\">" . $langActivate . "</a>]";

$tool_content .= "</td><td><div align='right'>";

$tool_content .= "&nbsp;&nbsp;<a href='auth_process.php?auth=2'>$langAuthSettings</a>";
$tool_content .= "</div></td></tr>
<tr class='odd'><td><b>IMAP:</b></td><td>[";

$tool_content .= in_array("3", $auth_methods) ? "<a class='add' href='auth.php?auth=3&amp;active=no'>" . $langDeactivate . "</a>]" : "<a class='revoke' href=\"auth.php?auth=3&amp;active=yes\">" . $langActivate . "</a>]";
$tool_content .= "</td><td><div align='right'>";

$tool_content .= "&nbsp;&nbsp;<a href='auth_process.php?auth=3'>$langAuthSettings</a>";
$tool_content .= "</div></td></tr><tr><td><b>LDAP:</b></td><td>[";

$tool_content .= in_array("4", $auth_methods) ? "<a class='add' href='auth.php?auth=4&amp;active=no'>" . $langDeactivate . "</a>]" : "<a class='revoke' href=\"auth.php?auth=4&amp;active=yes\">" . $langActivate . "</a>]";
$tool_content .= "</td><td><div align='right'>";

$tool_content .= "&nbsp;&nbsp;<a href=\"auth_process.php?auth=4\">$langAuthSettings</a>";
$tool_content .= "</div></td></tr><tr class='odd'><td><b>External DB:</b></td><td>[";

$tool_content .= in_array("5", $auth_methods) ? "<a class='add' href=\"auth.php?auth=5&amp;active=no\">" . $langDeactivate . "</a>]" : "<a class='revoke' href=\"auth.php?auth=5&amp;active=yes\">" . $langActivate . "</a>]";
$tool_content .= "</td><td><div align='right'>";

$tool_content .= "<a href=\"auth_process.php?auth=5\">$langAuthSettings</a>";

$tool_content .= "</div></td></tr><tr><td><b>Shibboleth:</b></td><td>[";

$tool_content .= in_array("6", $auth_methods) ? "<a class='add' href=\"auth.php?auth=6&amp;active=no\">" . $langDeactivate . "</a>]" : "<a class='revoke' href=\"auth.php?auth=6&amp;active=yes\">" . $langActivate . "</a>]";
$tool_content .= "</td><td><div align='right'>";

$tool_content .= "<a href=\"auth_process.php?auth=6\">$langAuthSettings</a>";
$tool_content .= "</div></td></tr><tr class='odd'><td><b>CAS:</b></td><td>[";

$tool_content .= in_array("7", $auth_methods) ? "<a class='add' href=\"auth.php?auth=7&amp;active=no\">" . $langDeactivate . "</a>]" : "<a class='revoke' href=\"auth.php?auth=7&amp;active=yes\">" . $langActivate . "</a>]";
$tool_content .= "</td><td><div align='right'>";

$tool_content .= "<a href=\"auth_process.php?auth=7\">$langAuthSettings</a>";
$tool_content .= "</div></td></tr></table>";

draw($tool_content, 3);
