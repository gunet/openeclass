<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_current_course = true;
$require_help = true;
$helpTopic = 'course_users';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

$toolName = $langAddManyUsers;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langUsers);

$up = new Permissions();

if (!$up->has_course_users_permission()) {
    Session::Messages($langCheckCourseAdmin, 'alert-danger');
    redirect_to_home_page('courses/'. $course_code);
}

$results = '';

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
        $results .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langUsersNotExist<br>";
        foreach ($not_found as $uname) {
            $results .= q($uname) . '<br>';
        }
        $results .= '</span></div></div>';
    }

    if (count($ok)) {
        $results .= "<div class='col-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langUsersRegistered<br>";
        foreach ($ok as $userid) {
            $results .= display_user($userid) . '<br>';
        }
        $results .= '</span></div></div>';
    }

    if (count($existing)) {
        $results .= "<div class='col-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langUsersAlreadyRegistered<br>";
        foreach ($existing as $userid) {
            $results .= display_user($userid) . '<br>';
        }
        $results .= '</span></div></div>';
    }
}

$data['results'] = $results;

view('modules.user.muladduser', $data);

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
 * @return boolean (false if user is already in the course and true if registration was successful)
 */
function adduser($userid, $cid): bool
{

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
