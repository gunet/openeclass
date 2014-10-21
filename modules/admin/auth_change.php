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
$nameTools = $langAuthChangeUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'auth.php', 'name' => $langUserAuthentication);

if (isset($_GET['auth'])) {
    $auth = $_GET['auth'];
    $_SESSION['auth_temp'] = $auth;
}

if (!isset($auth)) {
    $auth = $_SESSION['auth_temp'];
}

$auth_change = isset($_REQUEST['auth_change']) ? intval($_REQUEST['auth_change']) : false;
register_posted_variables(array('submit' => true));
$auth_methods = get_auth_active_methods();

foreach ($auth_methods as $key => $value) {
    // remove current auth method
    if ($auth == $value or $value == 1) { // cannot change to eclass native method
        unset($auth_methods[$key]);
    }
}

foreach ($auth_methods as $value) {
    $auth_methods_active[$value] = $auth_ids[$value];
}

$c = count_auth_users($auth);
$tool_content .= "<form name='authchange' method='post' action='$_SERVER[SCRIPT_NAME]'>
<fieldset>
<legend>" . get_auth_info($auth) . " ($langNbUsers: $c)</legend>
<table width='100%' class='tbl'><tr>
<th colspan='2'>
	<input type='hidden' name='auth' value='" . intval($auth) . "' />
</th>
</tr>";

if ($submit && $auth && $auth_change) {
    if (Database::get()->query("UPDATE user SET password=?s WHERE password=?s and user_id != 1", $auth_ids[$auth_change], $auth_ids[$auth])->affectedRows >= 1) {
        $tool_content .= "
				<td class='alert alert-success'>$langAuthChangeYes</td></tr></tbody></table><br /><br />";
        draw($tool_content, 3);
    }
}

if (count($auth_methods_active) == 0) {
    $tool_content .= "<div class='alert alert-warning'>$langAuthChangeno</div>";
} else {
    $tool_content .= "
		<tr>
			<th class='left'>$langAuthChangeto: </th>
			<td>";
    $tool_content .= selection($auth_methods_active, 'auth_change');
    $tool_content .= "</td></tr>";
    $tool_content .= "<tr><th>&nbsp;</th><td class='left'><input class='btn btn-primary' type='submit' name='submit' value='$langModify'></td></tr>";
}
$tool_content .= "</table></fieldset></form>";

draw($tool_content, 3);
