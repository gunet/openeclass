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


/** 
 * @brief checks if user has chat access permissions
 * @global type $is_editor
 * @param type $uid
 * @param type $conference_id
 * @param type $conference_status
 * @return boolean
 */
function is_valid_chat_user($uid, $conference_id, $conference_status) {
    
    global $is_editor;
    
    if ($is_editor) {
        return TRUE;
    } else {
        if ($conference_status == 'inactive') {
            return FALSE;
        } else {
            $c_users = Database::get()->querySingle("SELECT user_id FROM conference WHERE conf_id = ?d", $conference_id)->user_id;
            if ($c_users == 0) { // all users
                return TRUE;
            } else { // check if we're in list of chat users
                $chat_users = explode(',', $c_users);
                return in_array($uid, $chat_users);
            }
        }
    }
}

function is_valid_activity_user($conference_activity, $conference_agent) {
    global $is_editor;

    if ($is_editor) {
        return TRUE;
    }

    if (!$conference_activity) {
        return TRUE;
    }

    if ($conference_activity && $conference_agent) {
        return TRUE;
    }

    return FALSE;
}

function colmooc_create() {
    global $tool_content, $course_id, $siteName;

    if (!extension_loaded('curl')) {
        $tool_content .= "SYNC FAILED";
        return;
    }

    // gather all data
    $crow = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $course_id);
    $ctitle = q($crow->title) . " (" . $crow->public_code . ")";
    $platform_id = 201;
    $base_url = "https://mklab.iti.gr";
    $accept_head = array("Accept: text/html; charset=UTF-8");
    $json_head = "Content-Type: application/json\r\n";

    // api delete current course
    $delete_course_url = $base_url . "/colmoocapi/api/course/delete?course_id=" . $course_id . "&platform_id=" . $platform_id;
    list($response, $http_code, $response_headers) = curl_custom_request($delete_course_url, null, "DELETE", $accept_head);
    $tool_content .= "delete course API call, status: " . $http_code . ", response: " . $response . "<br/><br/>";

    // api add current course
    $add_course_url = $base_url . "/colmoocapi/api/course/add";
    $add_data = json_encode(array(array(
        "platform_id" => $platform_id,
        "course_id" => $course_id,
        "title" => $ctitle,
        "platform" => $siteName
    )));
    $response = custom_request($add_course_url, $add_data, "POST", $json_head);
    $tool_content .= "add course API call response: " . $response . "<br/><br/>";

    // api get current course
    $get_course_url = $base_url . "/colmoocapi/api/course?course_id=". $course_id ."&platform_id=" . $platform_id;
    list($response, $http_code, $response_headers) = curl_custom_request($get_course_url, null, "GET", $accept_head);
    $tool_content .= "get course API call, status: " . $http_code . ", response: " . $response . "<br/><br/>";

    // finished
    $tool_content .= "SYNC SUCCESSFUL" . "<br/><br/>";
}

function colmooc_register_student() {
    global $tool_content, $course_id, $uid;

    // gather all data
    $studs = Database::get()->queryArray("SELECT u.* FROM user u JOIN course_user cu ON (cu.user_id = u.id) AND cu.course_id = ?d AND cu.status = 5 AND u.id = ?d", $course_id, $uid);
    $base_url = "https://mklab.iti.gr";
    $platform_id = 201;
    $accept_head = array("Accept: text/html; charset=UTF-8");
    $json_head = "Content-Type: application/json\r\n";

    // api add student
    $add_studs_url = $base_url . "/colmoocapi/api/student/add";
    $studs_array = array();
    foreach ($studs as $stud) {
        $stud_array = array(
            "platform_id" => $platform_id,
            "course_id" => $course_id,
            "student_id" => $stud->id,
            "first_name" => $stud->givenname,
            "last_name" => $stud->surname
        );
        $studs_array[] = $stud_array;
    }
    $studs_data = json_encode($studs_array);
    $response = custom_request($add_studs_url, $studs_data, "POST", $json_head);
    $tool_content .= "add students API call response: " . $response . "<br/><br/>";

    // api get current course students
    $get_students_url = $base_url . "/colmoocapi/api/students?course_id=" . $course_id . "&platform_id=" . $platform_id;
    list($response, $http_code, $response_headers) = curl_custom_request($get_students_url, null, "GET", $accept_head);
    $tool_content .= "get students API call, status: " . $http_code . ", response: " . $response . "<br/><br/>";

    // finished
    $tool_content .= "SYNC SUCCESSFUL" . "<br/><br/>";
}