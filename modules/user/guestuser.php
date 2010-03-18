<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Guest';
include '../../include/baseTheme.php';

$nameTools = $langAddGuest;
$navigation[] = array ("url" => "user.php", "name" => $langAdminUsers);

$default_guest_username = $langGuestUserName . $currentCourseID;

$tool_content = "";
// IF PROF ONLY
if ($is_adminOfCourse) {
        if (isset($_POST['submit'])) {
                $password = autounquote($_POST['guestpassword']);
                createguest($default_guest_username, $cours_id, $password);
                $tool_content .= "<p class='success_small'>$langGuestSuccess<br />" .
                                 "<a href='user.php'>$langBackUser</a></p>";
        } else {
                $guest_info = guestinfo($cours_id);
                if ($guest_info) {
                        $tool_content .= "
                                <p class='caution_small'>$langGuestExist<br />
                                <a href='user.php'>$langBackUser</a></p>";
                        $submit_label = $langModify;
                } else {
                        $guest_info = array('nom' => $langGuestSurname,
                                            'prenom' => $langGuestName,
                                            'username' => $default_guest_username);
                        $submit_label = $langAdd;
                }

                $tool_content .= "
                        <form method='post' action='$_SERVER[PHP_SELF]'>

                        <table class='FormData'>
                        <tbody>
                        <tr>
                        <th width='220'>&nbsp;</th>
                        <td><b>$langUserData</b></td>
                        <td>&nbsp;</td>
                        </tr>
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
                        <td align='right'><small>$langAskGuest</small></td>
                        </tr>
                        <tr>
                        <th>&nbsp;</th>
                        <td><input type='submit' name='submit' value='$submit_label' /></td>
                        <td>&nbsp;</td>
                        </tr>
                        </table>
                        </form>";
        }

        draw($tool_content, 2, 'user');
}

// Create guest account or update password if it already exists
function createguest($username, $cours_id, $password)
{
	global $langGuestName, $langGuestSurname, $mysqlMainDb;

	mysql_select_db($mysqlMainDb);
        $password = md5($password);

	$q = db_query("SELECT user_id from cours_user WHERE statut=10 AND cours_id = $cours_id");
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
        db_query("INSERT IGNORE INTO cours_user (cours_id, user_id, statut, reg_date)
                  VALUES ($cours_id, $guest_id, 10, CURDATE())")
                or die ($langGuestFail);
}

// Check if guest account exists and return account information
function guestinfo($cours_id) {
	global $mysqlMainDb;
	mysql_select_db($mysqlMainDb);
	$q = db_query("SELECT nom, prenom, username FROM user, cours_user
                       WHERE user.user_id = cours_user.user_id AND
                             cours_user.statut = 10 AND
                             cours_user.cours_id = $cours_id");
	if (mysql_num_rows($q) == 0) {
		return false;
	} else {
		return mysql_fetch_array($q);
	}
}
