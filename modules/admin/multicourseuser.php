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
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        $ok = array();
        $not_found = array();
        $course_not_found = array();
        $existing = array();
        $field = 'username';
        $courses_codes = preg_split('/\s+/ms', mb_strtoupper($_POST['courses_codes']));
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
        $warn_messages = [];
        if (count($not_found)) {
            $usernames = implode('<br>', $not_found);
            $warn_messages[] = "$langUsersNotExist<br> $usernames";
        }

        if (count($course_not_found)) {
            $courses = implode('<br>', $course_not_found);
            $warn_messages[] = "$langCourseNotExist<br> $courses";
        }
        //Session::Messages($warn_messages);
        Session::flash('message',$warn_messages); 
        Session::flash('alert-class', 'alert-warning');
        if (count($ok)) {
            $added_users = implode('<br>', array_map(function($userid) {
                return display_user($userid);
            }, $ok));
            $sucess_message = "$langUsersRegistered<br> $added_users";
            //Session::Messages($sucess_message, 'alert-success');
            Session::flash('message',$sucess_message); 
            Session::flash('alert-class', 'alert-success');
        }

        if (count($existing)) {
            $already_registered_users = implode('<br>', array_map(function($userid) {
                return display_user($userid);
            }, $existing));
            $info_message = "$langUsersAlreadyRegistered<br> $already_registered_users";
            //Session::Messages($info_message, 'alert-info');
            Session::flash('message',$info_message); 
            Session::flash('alert-class', 'alert-info');
        }
        redirect_to_home_page('modules/admin/multicourseuser.php');
}

$data['menuTypeID'] = 3;

view('admin.users.multicourseuser', $data);

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
        $result = Database::get()->query("INSERT INTO course_user (user_id, course_id, status, reg_date, document_timestamp)
                                            VALUES (?d, ?d, ".USER_STUDENT.",  " . DBHelper::timeAfter() . ", " . DBHelper::timeAfter(). ")", $userid, $cid);
        return true;
    }
}
