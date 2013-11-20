<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

require_once '../../include/baseTheme.php';
require_once 'include/phpass/PasswordHash.php';

$nameTools = $langAddGuest;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langAdminUsers);

// javascript
load_js('jquery');
load_js('pwstrength.js');
$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

    var lang = {
hContent;
$head_content .= "pwStrengthTooShort: '". js_escape($langPwStrengthTooShort) ."', ";
$head_content .= "pwStrengthWeak: '". js_escape($langPwStrengthWeak) ."', ";
$head_content .= "pwStrengthGood: '". js_escape($langPwStrengthGood) ."', ";
$head_content .= "pwStrengthStrong: '". js_escape($langPwStrengthStrong) ."'";
$head_content .= <<<hContent
    };

    $(document).ready(function() {
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });
    });

/* ]]> */
</script>
hContent;

$default_guest_username = $langGuestUserName . $course_code;

if (isset($_POST['submit'])) {
        $password = autounquote($_POST['guestpassword']);
        createguest($default_guest_username, $course_id, $password);
        $tool_content .= "<p class='success'>$langGuestSuccess</p>" .
                         "<a href='index.php?course=$course_code'>$langBack</a>";
} else {
        $guest_info = guestinfo($course_id);
        if ($guest_info) {
                $tool_content .= "<p class='caution'>$langGuestExist</p>";
                $submit_label = $langModify;
        } else {
                $guest_info = array('surname' => $langGuestSurname,
                                    'givenname' => $langGuestName,
                                    'username' => $default_guest_username);
                $submit_label = $langAdd;
        }
        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <fieldset>
            <legend>$langUserData</legend>
        <table width='100%' class='tbl'>
        <tr>
        <th class='left'>$langName:</th>
        <td>$guest_info[givenname]</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <th class='left'>$langSurname:</th>
        <td>$guest_info[surname]</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <th class='left'>$langUsername:</th>
        <td>$guest_info[username]</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <th class='left'>$langPass:</th>
        <td><input type='text' name='guestpassword' value='' class='FormData_InputText' id='password' /></td>
        <td class='smaller'>$langAskGuest</td>
        </tr>
        <tr>
        <th class='left'></th>
        <td colspan='2'><span id='result'></span></td>
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
draw($tool_content, 2, null, $head_content);



/**
 * @brief create guest account or update password if it already exists
 * @global type $langGuestName
 * @global type $langGuestSurname
 * @param type $username
 * @param type $course_id
 * @param type $password
 * @return none
 */
function createguest($username, $course_id, $password)
{
	global $langGuestName, $langGuestSurname, $langGuestFail;
	
	$hasher = new PasswordHash(8, false);
	$password = $hasher->HashPassword($password);

	$q = db_query("SELECT user_id from course_user WHERE status=".USER_GUEST." AND course_id = $course_id");
	if (mysql_num_rows($q) > 0) {
		list($guest_id) = mysql_fetch_array($q);
		db_query("UPDATE user SET password = '$password' WHERE user_id = $guest_id");
	} else {
                $regtime = time();
                $exptime = 126144000 + $regtime;
                db_query("INSERT INTO user (surname, givenname, username, password, status, registered_at, expires_at, whitelist)
                             VALUES ('$langGuestSurname', '$langGuestName', '$username', '$password', ".USER_GUEST.", $regtime, $exptime, '')");
                $guest_id = mysql_insert_id();
	}
        db_query("INSERT IGNORE INTO course_user (course_id, user_id, status, reg_date)
                  VALUES ($course_id, $guest_id, ".USER_GUEST.", CURDATE())")
                or die ($langGuestFail);
        
        return;
}


/**
 * @brief check if guest account exists and return account information
 * @param type $course_id
 * @return boolean
 */
function guestinfo($course_id) {

	$q = db_query("SELECT surname, givenname, username FROM user, course_user
                       WHERE user.id = course_user.user_id AND
                             course_user.status = ".USER_GUEST." AND
                             course_user.course_id = $course_id");
	if (mysql_num_rows($q) == 0) {
		return false;
	} else {
		return mysql_fetch_array($q);
	}
}
