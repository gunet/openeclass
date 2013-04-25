<?php

/* ========================================================================
 * Open eClass 2.6
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

$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';

$nameTools = $langMultiCourseUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);

mysql_select_db($mysqlMainDb);
if (isset($_POST['submit'])) {
        $ok = array();
        $not_found = array();
        $course_not_found = array();
        $existing = array();        
        $field = 'username';        
        $courses_codes = explode("\n", trim(mb_strtoupper($_POST['courses_codes'])));
        $line = strtok($_POST['user_info'], "\n");        
        while ($line !== false) {
                $userid = finduser(canonicalize_whitespace($line), $field);
                if (!$userid) {
                        $not_found[] = $line;
                } else {
                        foreach ($courses_codes as $codes) {
                                $cours_id = course_code_to_id($codes);
                                if (!$cours_id) {
                                        $course_not_found[] = $codes;
                                } else {
                                        if (adduser($userid, $cours_id)) {
                                                $ok[] = $userid;
                                        } else {
                                                $existing[] = $userid;
                                        }
                                }
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
        
        if (count($course_not_found)) { 
            $tool_content .= "<p class='alert1'>$langCourseNotExist<br>";
            foreach ($course_not_found as $course) {
                $tool_content .= q($course) . '<br>';
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

$tool_content .= "<p class='noteit'>$langAskManyUsersToCourses</p>";
$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]'>
        <fieldset>
           <legend>$langUsersData</legend>
           <table width='100%' class='tbl'> 
               <tr>
                   <td><input type='radio' name='type' value='uname' checked>&nbsp;$langUsername<br>        
                   </td>
               </tr>
               <tr>
                   <td>
                       <textarea class='auth_input' name='user_info' rows='10' cols='30'></textarea>
                   </td>
               </tr>               
           </table>
        </fieldset>
        <fieldset>
           <legend>$langCourseCodes</legend>
           <table width='100%' class='tbl'>                
               <tr>
                   <td>
                       <textarea class='auth_input' name='courses_codes' rows='10' cols='30'></textarea>
                   </td>
               </tr>
               <tr>
                   <th class='right'>
                       <input type='submit' name='submit' value='".q($langRegistration)."'>
                   </th>
               </tr>
           </table>
        </fieldset>
    </form>";


function finduser($user, $field) {
	$result = db_query("SELECT user_id FROM user WHERE $field=".autoquote($user));
	if (!mysql_num_rows($result)) {
                return false;
        }
	list($userid) = mysql_fetch_array($result);
        return $userid;
}

function findcourse($cid) {
        $result = db_query("SELECT cours_id FROM cours WHERE cours_id = $cid");
	if (!mysql_num_rows($result)) {
                return false;
        }
	list($course_id) = mysql_fetch_array($result);
        return $course_id;
        
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

draw($tool_content, 3);

