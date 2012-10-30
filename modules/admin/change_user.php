<?php
/* ========================================================================
 * Open eClass 2.6
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


/*===========================================================================
	change_user.php
==============================================================================
	@Description: Allows platform admin to login as another user without
         asking for a password
==============================================================================
*/

$require_admin = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langChangeUser;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

if (isset($_REQUEST['username'])) {
	$sql = "SELECT user_id, nom, username, password, prenom, statut, email, iduser is_admin, perso, lang
					FROM user LEFT JOIN admin ON user.user_id = admin.iduser
					WHERE username ";
	if (get_config('case_insensitive_usernames')) {
		$sql .= "= " . quote($_REQUEST['username']);
	} else {
		$sql .= "COLLATE utf8_bin = " . quote($_REQUEST['username']);
	}

	$result = db_query($sql);
	if (mysql_num_rows($result) > 0) {
                $myrow = mysql_fetch_array($result);
                $_SESSION['uid'] = $myrow['user_id'];
                $_SESSION['nom'] = $myrow['nom'];
                $_SESSION['prenom'] = $myrow['prenom'];
                $_SESSION['statut'] = $myrow['statut'];
                $_SESSION['email'] = $myrow['email'];
                $_SESSION['is_admin'] = !(!($myrow['is_admin'])); // double 'not' to handle NULL
                $_SESSION['uname'] = $myrow['username'];
	        if ($myrow['perso'] == 'no' and $persoIsActive) {
        		$_SESSION['user_perso_active'] = true;
                } else {
        		$_SESSION['user_perso_active'] = false;
                }
                $_SESSION['langswitch'] = langcode_to_name($myrow['lang']);
                redirect_to_home_page();
        } else {
                $tool_content = "<div class='caution'>" . sprintf($langChangeUserNotFound, canonicalize_whitespace(q($_POST['username']))) . "</div>";
        }
} 

$tool_content .= "<legend><form action='$_SERVER[SCRIPT_NAME]' method='post'>$langUsername: <input type='text' name='username' /></form></legend>";
draw($tool_content, 3);
