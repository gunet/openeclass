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


$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_help = TRUE;
$helpTopic = 'User';

include '../../include/baseTheme.php';

$nameTools = $langAddManyUsers;
$navigation[] = array ("url"=>"user.php?course=$code_cours", "name"=> $langAdminUsers);

mysql_select_db($mysqlMainDb);

if (isset($_POST['submit'])) {
        $ok = array();
        $not_found = array();
        $existing = array();
        $field = ($_POST['type'] == 'am')? 'am': 'username';
        $line = strtok($_POST['user_info'], "\n");
        while ($line !== false) {
                $userid = finduser(canonicalize_whitespace($line), $field);
                if (!$userid) {
                        $not_found[] = $line;
                } else {
                        if (adduser($userid, $cours_id)) {
                                $ok[] = $userid;
                        } else {
                                $existing[] = $userid;
                        }
                }
                $line = strtok("\n");
        }
        
        if (count($not_found)) {
            $tool_content .= "<p class='alert1'>$langUsersNotExist<br>";
            foreach ($not_found as $uname) {
                $tool_content .= q($uname) . '<br>';
            }
            $tool_content .= '</p>';
        }

        if (count($ok)) {
            $tool_content .= "<p class='success'>$langUsersRegistered<br>";
            foreach ($ok as $userid) {
                $tool_content .= display_user($userid) . '<br>';
            }
            $tool_content .= '</p>';
        }

        if (count($existing)) {
            $tool_content .= "<p class='noteit'>$langUsersAlreadyRegistered<br>";
            foreach ($existing as $userid) {
                $tool_content .= display_user($userid) . '<br>';
            }
            $tool_content .= '</p>';
        }
}

$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours'>
        <fieldset>
           <legend>$langUsersData</legend>
           <table width='100%' class='tbl'> 
               <tr>
                   <td><input type='radio' name='type' value='uname' checked>&nbsp;$langUsername<br>
                       <input type='radio' name='type' value='am'>&nbsp;$langAm
                   </td>
               </tr>
               <tr>
                   <td>
                       <textarea class='auth_input' name='user_info' rows='10' cols='30'></textarea>
                   </td>
               </tr>
               <tr>
                   <th class='right'>
                       <input type='submit' name='submit' value='$langAdd'>
                   </th>
               </tr>
           </table>
        </fieldset>
    </form>
    <p class='noteit'>$langAskManyUsers</p>";

draw($tool_content, 2);

function finduser($user, $field) {
	$result = db_query("SELECT user_id FROM user WHERE $field=".autoquote($user));
	if (!mysql_num_rows($result)) {
                return false;
        }
	list($userid) = mysql_fetch_array($result);
        return $userid;
}

// function for adding users

// returns false (error - user is already in the course)
// returns true (yes everything is ok )

function adduser($userid, $cid) {
	$result = db_query("SELECT * from cours_user WHERE user_id = $userid AND cours_id = $cid");
	if (mysql_num_rows($result) > 0) {
                return false;
        }

	$result = db_query("INSERT INTO cours_user (user_id, cours_id, statut, reg_date)
			VALUES ($userid, $cid, 5, CURDATE())");
	return true;
}
