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
$require_help = true;
$helpTopic = 'User';

require_once '../../include/baseTheme.php';
require_once 'include/log.php';

$nameTools = $langAddManyUsers;
$navigation[] = array ("url"=>"index.php?course=$course_code", "name"=> $langAdminUsers);

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
                        if (adduser($userid, $course_id)) {
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

$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
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

/**
 * Add users
 * @param type $userid
 * @param type $cid
 * @return boolean (false if user is already in the course and true if registration was succesful)
 */
function adduser($userid, $cid) {
                        
	$result = db_query("SELECT * FROM course_user WHERE user_id = $userid AND course_id = $cid");
	if (mysql_num_rows($result) > 0) {
                return false;
        }

	db_query("INSERT INTO course_user (user_id, course_id, statut, reg_date)
                                   VALUES ($userid, $cid, ".USER_STUDENT.", CURDATE())");
       
        Log::record($cid, MODULE_ID_USERS, LOG_INSERT, array('uid' => $userid,
                                                             'right' => '+5'));
                
	return true;
}
