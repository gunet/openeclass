<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';

$toolName = $langMultiRegCourseUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
if (isset($_POST['submit'])) {
        $ok = array();
        $not_found = array();
        $course_not_found = array();
        $existing = array();        
        $field = 'username';                        
        $courses_codes = preg_split('/\s+/', mb_strtoupper($_POST['courses_codes']), PREG_SPLIT_NO_EMPTY);        
        $line = strtok($_POST['user_info'], "\n\r");
        while ($line !== false) {
            $userid = finduser(canonicalize_whitespace($line), $field);
            if (!$userid) {
                $not_found[] = $line;
            } else {                
                foreach ($courses_codes as $codes) {                    
                    $cid = course_code_to_id($codes);                    
                    if ($cid) {
                        if (adduser($userid, $cid)) {
                            $ok[] = $userid;
                        } else {
                            $existing[] = $userid;
                        }
                    } else {
                        $course_not_found[] = $codes;
                    }
                }
            }
            $line = strtok("\n\r");
        }
        
        if (count($not_found)) {
            $tool_content .= "<div class='alert alert-warning'>$langUsersNotExist<br>";
            foreach ($not_found as $uname) {
                $tool_content .= q($uname) . '<br>';
            }
            $tool_content .= '</div>';
        }
        
        if (count($course_not_found)) { 
            $tool_content .= "<div class='alert alert-warning'>$langCourseNotExist<br>";
            foreach ($course_not_found as $course) {
                $tool_content .= q($course) . '<br>';
            }
            $tool_content .= '</div>';
        }

        if (count($ok)) {
            $tool_content .= "<div class='alert alert-success'>$langUsersRegistered<br>";
            foreach ($ok as $userid) {
                $tool_content .= display_user($userid) . '<br>';
            }
            $tool_content .= '</div>';
        }

        if (count($existing)) {
            $tool_content .= "<div class='alert alert-info'>$langUsersAlreadyRegistered<br>";
            foreach ($existing as $userid) {
                $tool_content .= display_user($userid) . '<br>';
            }
            $tool_content .= '</div>';
        }
}

$tool_content .= "<div class='alert alert-info'>$langAskManyUsersToCourses</div>";
$tool_content .= "<div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>
        <fieldset>
           <h4>$langUsersData</h4>
            <div class='form-group'>
                <div class='radio'>
                <label>
                    <input type='radio' name='type' value='uname' checked>$langUsername
                </label>
                </div>
            <div class='col-sm-7'>" . text_area('user_info', 10, 30, '') . "</div>
        </div>                                                
        </fieldset>
        <fieldset>
           <h4>$langCourseCodes</h4>
           <div class='form-group'>
                <div class='col-sm-7'>" . text_area('courses_codes', 10, 30, '') . "</div>
            </div>
            <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='submit' value='" . q($langRegistration) . "'>
            </div>
        </fieldset>
    </form>
    </div>";

/**
 * @brief check if user exist
 * @param type $user
 * @param type $field
 * @return boolean
 */
function finduser($user, $field) {
    
    $result = Database::get()->querySingle("SELECT id FROM user WHERE $field = ?s", $user);
    if ($result) {
        $userid = $result->id;
        return $userid;
    } else {
        return false;
    }	
}


/**
 * @brief add user to course
 * @param type $userid
 * @param type $cid
 * @return boolean
 */
function adduser($userid, $cid) {
        
    $result = Database::get()->querySingle("SELECT * FROM course_user WHERE user_id = ?d AND course_id = ?d", $userid, $cid);
    if ($result) {
            return false;
    } else {
        $result = Database::get()->query("INSERT INTO course_user (user_id, course_id, status, reg_date)
                                            VALUES (?d, ?d, ".USER_STUDENT.",  " . DBHelper::timeAfter() . ")", $userid, $cid);
        return true;
    }	
}

draw($tool_content, 3);

