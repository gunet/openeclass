<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'course_users';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

$toolName = $langAddManyUsers;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langUsers);

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $ok = array();
    $not_found = array();
    $existing = array();
    $field = ($_POST['type'] == 'am') ? 'am' : 'username';
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
        $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langUsersNotExist<br>";
        foreach ($not_found as $uname) {
            $tool_content .= q($uname) . '<br>';
        }
        $tool_content .= '</span></div></div>';
    }

    if (count($ok)) {
        $tool_content .= "<div class='col-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langUsersRegistered<br>";
        foreach ($ok as $userid) {
            $tool_content .= display_user($userid) . '<br>';
        }
        $tool_content .= '</span></div></div>';
    }

    if (count($existing)) {
        $tool_content .= "<div class='col-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langUsersAlreadyRegistered<br>";
        foreach ($existing as $userid) {
            $tool_content .= display_user($userid) . '<br>';
        }
        $tool_content .= '</span></div></div>';
    }
}


$tool_content .= "
        
<div class='d-lg-flex gap-4 mt-4'>
    <div class='flex-grow-1'>
    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langAskManyUsers</span></div>
<div class='form-wrapper form-edit rounded'>
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <fieldset>     
            <legend class='mb-0' aria-label='$langForm'></legend>
            <div class='form-group'>
                <div class='col-sm-12 radio mb-2'>
                    <label>
                        <input type='radio' name='type' value='uname' checked>
                        $langUsername
                    </label>
                </div>
                <div class='col-sm-12 radio'>
                    <label>
                        <input type='radio' name='type' value='am'>
                        $langAm
                    </label>
                </div>
            </div>
                 
            <div class='form-group mt-4'>
                <textarea aria-label='$langTypeOutMessage' class='auth_input w-100' name='user_info' rows='10'></textarea>
            </div>
            ".showSecondFactorChallenge()." 
               
            <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                <input class='btn submitAdminBtn' type='submit' name='submit' value='$langAdd'>
            </div>                       
        </fieldset>
        ". generate_csrf_token_form_field() ."  
        </form>
        </div></div><div class='d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
    </div>";

draw($tool_content, 2);

/**
 * @brief find if user exists according to criteria
 * @param type $user
 * @param type $field
 * @return boolean
 */
function finduser($user, $field) {

    $result = Database::get()->querySingle("SELECT id FROM user WHERE `$field` = ?s", $user);
    if ($result) {
        $userid = $result->id;
    } else {
        return false;
    }
    return $userid;
}

/**
 * Add users
 * @param type $userid
 * @param type $cid
 * @return boolean (false if user is already in the course and true if registration was succesful)
 */
function adduser($userid, $cid) {

    $result = Database::get()->querySingle("SELECT * FROM course_user WHERE user_id = ?d AND course_id = ?d", $userid, $cid);
    if ($result) {
        return false;
    } else {
        Database::get()->query("INSERT INTO course_user (user_id, course_id, status, reg_date, document_timestamp)
                                   VALUES (?d, ?d, " . USER_STUDENT . ", " . DBHelper::timeAfter() . ", " . DBHelper::timeAfter(). " )", $userid, $cid);

        $r = Database::get()->queryArray("SELECT id FROM course_user_request WHERE uid = ?d AND course_id = ?d", $userid, $cid);
        if ($r) { // close course user request (if any)
            foreach ($r as $req) {
                Database::get()->query("UPDATE course_user_request SET status = 2 WHERE id = ?d", $req->id);
            }
        }
        Log::record($cid, MODULE_ID_USERS, LOG_INSERT, array('uid' => $userid,
                                                             'right' => '+5'));
        return true;
    }

}
