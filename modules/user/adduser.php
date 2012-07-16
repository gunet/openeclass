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


/* This script allows a course admin to add users to the course. */

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'User';

include '../../include/baseTheme.php';
include 'include/sendMail.inc.php';

$nameTools = $langAddUser;
$navigation[] = array ('url' => "user.php?course=$course_code", 'name' => $langAdminUsers);

if (isset($_GET['add'])) {
        $uid_to_add = intval($_GET['add']);
	mysql_select_db($mysqlMainDb);
	$result = db_query("INSERT IGNORE INTO course_user (user_id, course_id, statut, reg_date) ".
                           "VALUES ($uid_to_add, $course_id, 5, CURDATE())");
		// notify user via email
		$email = uid_to_email($uid_to_add);
		if (!empty($email) and email_seems_valid($email)) {
			$emailsubject = "$langYourReg " . course_id_to_title($course_id);
			$emailbody = "$langNotifyRegUser1 '".course_id_to_title($course_id). "' $langNotifyRegUser2 $langFormula \n$gunet";
			send_mail('', '', '', $email, $emailsubject, $emailbody, $charset);
		}
		$tool_content .= "";

	if ($result) {
		$tool_content .=  "<p class='success'>$langTheU $langAdded</p>";
	} else {
		$tool_content .=  "<p class='alert1'>$langAddError</p>";
	}
		$tool_content .= "<br /><p><a href='adduser.php?course=$course_code'>$langAddBack</a></p><br />\n";

} else {
	$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>";
        register_posted_variables(array('search_nom' => true,
                                        'search_prenom' => true,
                                        'search_username' => true,
                                        'search_am' => true), 'any');

        $tool_content .= "
        <fieldset>
        <legend>$langUserData</legend>
        <table class='tbl'>
        <tr><td colspan='2'>$langAskUser<br /><br /></td></tr>
        <tr><th class='left'>$langSurname:</th>
            <td><input type='text' name='search_nom' value='".q($search_nom)."' /></td></tr>
        <tr><th class='left'>$langName:</th>
            <td><input type='text' name='search_prenom' value='".q($search_prenom)."' /></td></tr>
        <tr><th class='left'>$langUsername:</th>
            <td><input type='text' name='search_username' value='".q($search_username)."' /></td></tr>
        <tr><th class='left'>$langAm:</th>
            <td><input type='text' name='search_am' value='".q($search_am)."' /></td></tr>
        <tr><th class='left'>&nbsp;</th>
            <td class='right'><input type='submit' name='search' value='$langSearch' /></td></tr>
        </table>
        </fieldset>
        </form>";

	mysql_select_db($mysqlMainDb);
	$search = array();
        foreach (array('nom', 'prenom', 'username', 'am') as $term) {
                $tvar = 'search_'.$term;
                if (!empty($GLOBALS[$tvar])) {
                        $search[] = "u.$term LIKE " . quote($GLOBALS[$tvar] . '%');
                }
        }
	$query = join(' AND ', $search);
	if (!empty($query)) {
                    db_query("CREATE TEMPORARY TABLE lala AS
                    SELECT user_id FROM course_user WHERE course_id = $course_id");
                    $result = db_query("SELECT u.user_id, u.nom, u.prenom, u.username, u.am FROM
                        user u LEFT JOIN lala c ON u.user_id = c.user_id WHERE
                        c.user_id IS NULL AND $query");
                    if (mysql_num_rows($result) == 0) {
                            $tool_content .= "<p class='caution'>$langNoUsersFound</p>\n";
                    } else {
                            $tool_content .= "<table width=100% class='tbl_alt'>
                                <tr>
                                  <th width='20'>$langID</th>
                                  <th width='150'>$langName</th>
                                  <th width='150'>$langSurname</th>
                                  <th>$langUsername</th>
                                  <th width='150'>$langAm</th>
                                  <th width='200'>$langActions</th>
                                </tr>";
                            $i = 1;
                            while ($myrow = mysql_fetch_array($result)) {
                                    if ($i % 2 == 0) {
                                            $tool_content .= "<tr class='even'>";
                                    } else {
                                            $tool_content .= "<tr class='odd'>";
                                    }
                                    $tool_content .= "<td class='right'>$i.</td><td>" . q($myrow['prenom']) . "</td><td>" .
                                            q($myrow['nom']) . "</td><td>" . q($myrow['username']) . "</td><td>" .
                                            q($myrow['am']) . "</td><td align='center'>" .
                                            "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=$myrow[user_id]'>$langRegister</a></td></tr>\n";
                                    $i++;
                            }
                            $tool_content .= "</table>";
                    }
                    db_query("DROP TABLE lala");
            }
}
draw($tool_content, 2);
