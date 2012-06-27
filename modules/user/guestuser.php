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

$require_current_course = true;
$require_course_admin = true;
$require_help = TRUE;
$helpTopic = 'Guest';
include '../../include/baseTheme.php';

$nameTools = $langAddGuest;
$navigation[] = array('url' => "user.php?course=$course_code", 'name' => $langAdminUsers);

$default_guest_username = $langGuestUserName . $course_code;

if (isset($_POST['submit'])) {
        $password = autounquote($_POST['guestpassword']);
        createguest($default_guest_username, $course_id, $password);
        $tool_content .= "<p class='success'>$langGuestSuccess</p>" .
                         "<a href='user.php?course=$course_code'>$langBackUser</a>";
} else {
        $guest_info = guestinfo($course_id);
        if ($guest_info) {
                $tool_content .= "
                        <p class='caution'>$langGuestExist<br />
                        <a href='user.php?course=$course_code'>$langBackUser</a></p>";
                $submit_label = $langModify;
        } else {
                $guest_info = array('nom' => $langGuestSurname,
                                    'prenom' => $langGuestName,
                                    'username' => $default_guest_username);
                $submit_label = $langAdd;
        }
        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <fieldset>
            <legend>$langUserData</legend>
        <table width='100%' class='tbl'>
        <tr>
        <th class='left'>$langName:</th>
        <td>$guest_info[prenom]</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <th class='left'>$langSurname:</th>
        <td>$guest_info[nom]</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <th class='left'>$langUsername:</th>
        <td>$guest_info[username]</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <th class='left'>$langPass:</th>
        <td><input type='text' name='guestpassword' value='' class='FormData_InputText' /></td>
        <td class='smaller'>$langAskGuest</td>
        </tr>
        <tr>
        <th>&nbsp;</th>
        <td class='right'>&nbsp;</td>
        <td class='right'>
          <input type='submit' name='submit' value='$submit_label' />
        </td>
        </tr>
        </table>
        </fieldset>
        </form>";
}
draw($tool_content, 2);


// Create guest account or update password if it already exists
function createguest($username, $course_id, $password)
{
	global $langGuestName, $langGuestSurname, $mysqlMainDb;

	mysql_select_db($mysqlMainDb);
        $password = md5($password);

	$q = db_query("SELECT user_id from course_user WHERE statut=10 AND course_id = $course_id");
	if (mysql_num_rows($q) > 0) {
		list($guest_id) = mysql_fetch_array($q);
		db_query("UPDATE user SET password = '$password' WHERE user_id = $guest_id");
	} else {
                $regtime = time();
                $exptime = 126144000 + $regtime;
                db_query("INSERT INTO user (nom, prenom, username, password, statut, registered_at, expires_at)
                             VALUES ('$langGuestSurname', '$langGuestName', '$username', '$password', 10, $regtime, $exptime)");
                $guest_id = mysql_insert_id();
	}
        db_query("INSERT IGNORE INTO course_user (course_id, user_id, statut, reg_date)
                  VALUES ($course_id, $guest_id, 10, CURDATE())")
                or die ($langGuestFail);
}

// Check if guest account exists and return account information

function guestinfo($course_id) {
	
	$q = db_query("SELECT nom, prenom, username FROM user, course_user
                       WHERE user.user_id = course_user.user_id AND
                             course_user.statut = ".USER_GUEST." AND
                             course_user.course_id = $course_id");
	if (mysql_num_rows($q) == 0) {
		return false;
	} else {
		return mysql_fetch_array($q);
	}
}
