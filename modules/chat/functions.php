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

require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/colmoocapp.php';

//define('COLMOOC_CHAT_URL', "https://mklab.iti.gr/colmooc-chat");
define('COLMOOC_JSON_HEAD', "Content-Type: application/json\r\n");

$colmoocapp = ExtAppManager::getApp(strtolower(ColmoocApp::NAME));

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

function colmooc_create_activity($activityId, $activityTitle) {
    global $colmoocapp, $uid, $course_id;

    if (!ini_get('allow_url_fopen')) {
        return false;
    }

    // api add activity
    $add_activity_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/api/activity/add";
    $add_data = json_encode(array(array(
        "platform_id" => $colmoocapp->getParam(ColmoocApp::PLATFORM_ID)->value(),
        "activity_id" => $activityId,
        "activity_title" => $activityTitle,
        "teacher_id" => $uid,
        "course_id" => $course_id
    )));
    $responseStr = custom_request($add_activity_url, $add_data, "POST", COLMOOC_JSON_HEAD);
    // error_log("add activity API call response: " . $responseStr);
    $responseData = json_decode($responseStr, true);
    if (is_array($responseData) && count($responseData) > 0 && isset($responseData['success']) && isset($responseData['success']['colmooc_id'])) {
        // error_log("colmooc id: " . $responseData['success']['colmooc_id']);
        return $responseData['success']['colmooc_id'];
    }
    return false;
}

function colmooc_update_activity($activityId, $activityTitle, $agentId) {
    global $colmoocapp, $uid, $course_id;

    if (!ini_get('allow_url_fopen')) {
        return false;
    }

    // api update activity
    $update_activity_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/api/activity/update";
    $update_data = json_encode(array(array(
        "platform_id" => $colmoocapp->getParam(ColmoocApp::PLATFORM_ID)->value(),
        "activity_id" => $activityId,
        "teacher_id" => $uid,
        "course_id" => $course_id,
        "activity_title" => $activityTitle,
        "agent_id" => $agentId
    )));
    $responseStr = custom_request($update_activity_url, $update_data, "POST", COLMOOC_JSON_HEAD);
    // error_log("update activity API call response: " . $responseStr);
    $responseData = json_decode($responseStr, true);
    if (is_array($responseData) && count($responseData) > 0 && isset($responseData['success'])) {
        return true;
    }
    return false;
}

function colmooc_create_agent($conferenceId) {
    global $colmoocapp, $uid, $urlServer;

    if (!ini_get('allow_url_fopen')) {
        return false;
    }

    // api add agent
    $add_agent_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/api/agent/add";
    $add_data = json_encode(array(array(
        "platform_id" => $colmoocapp->getParam(ColmoocApp::PLATFORM_ID)->value(),
        "teacher_id" => $uid,
        "callback_data" => $urlServer . "modules/chat/agentcb.php?id=" . $conferenceId
    )));

    $responseStr = custom_request($add_agent_url, $add_data, "POST", COLMOOC_JSON_HEAD);
    // error_log("add agent API call response: " . $responseStr);
    $responseData = json_decode($responseStr, true);
    if (is_array($responseData) && count($responseData) > 0 && isset($responseData['success']) && isset($responseData['success']['agent_id'])) {
        // error_log("agent id: " . $responseData['success']['agent_id']);
        return $responseData['success']['agent_id'];
    }
    return false;
}

function colmooc_register_student($conferenceId) {
    global $colmoocapp, $uid, $urlServer, $language;

    if (!ini_get('allow_url_fopen')) {
        return array(false, false);
    }

    $u = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $uid);
    $conf = Database::get()->querySingle("SELECT * FROM conference WHERE conf_id = ?d", $conferenceId);

    // colstudent id
    $colstudentId = null;
    $colmoocUser = Database::get()->querySingle("SELECT * FROM colmooc_user WHERE user_id = ?d", $uid);

    if ($colmoocUser && $colmoocUser->colmooc_id) {

        $colstudentId = $colmoocUser->colmooc_id;

        // api update student
        $update_student_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/api/student/update";
        $update_student_data = json_encode(array(array(
            "colstudent_id" => $colstudentId,
            "student_id" => $uid,
            "first_name" => $u->givenname,
            "last_name" => $u->surname,
            "gender" => "male",
            "lang" => $language,
            "language" => $language,
            "nationality" => "gr",
            "timezone" => "+2"
        )));

        custom_request($update_student_url, $update_student_data, "POST", COLMOOC_JSON_HEAD);
    } else {
        // api add student
        $add_student_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/api/student/add";
        $add_student_data = json_encode(array(array(
            "platform_id" => $colmoocapp->getParam(ColmoocApp::PLATFORM_ID)->value(),
            "student_id" => $uid,
            "first_name" => $u->givenname,
            "last_name" => $u->surname,
            "gender" => "male",
            "lang" => $language,
            "language" => $language,
            "nationality" => "gr",
            "timezone" => "+2"
        )));

        $responseStr = custom_request($add_student_url, $add_student_data, "POST", COLMOOC_JSON_HEAD);
        // error_log("add student API call response: " . $responseStr);
        $responseData = json_decode($responseStr, true);
        if (is_array($responseData) && count($responseData) > 0 && isset($responseData['success']) && isset($responseData['success']['colstudent_id'])) {
            // error_log("colstudent id: " . $responseData['success']['colstudent_id']);
            $colstudentId = $responseData['success']['colstudent_id'];
            // INSERT OR UPDATE
            if ($colmoocUser) {
                Database::get()->query("UPDATE colmooc_user SET colmooc_id = ?d WHERE user_id = ?d", $colstudentId, $uid);
            } else {
                Database::get()->query("INSERT INTO colmooc_user (user_id, colmooc_id) VALUES (?d, ?d)", $uid, $colstudentId);
            }
        } else {
            return array(false, false);
        }
    }

    // user session
    $colmoocUserSession = Database::get()->querySingle("SELECT * FROM colmooc_user_session WHERE user_id = ?d AND activity_id = ?d", $uid, $conf->chat_activity_id);

//    if ($colmoocUserSession && $colmoocUserSession->session_id && $colmoocUserSession->session_token) {
//        $sessionId = $colmoocUserSession->session_id;
//        $sessionToken = $colmoocUserSession->session_token;
//    } else {
    // api create session
    $add_session_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/api/session/add";
    $add_session_data = json_encode(array(array(
        "colmooc_id" => $conf->chat_activity_id,
        "colstudent_id" => $colstudentId,
        "callback_data" => $urlServer . "modules/chat/sessioncb.php?activity_id=" . $conf->chat_activity_id
    )));

    $responseStr = custom_request($add_session_url, $add_session_data, "POST", COLMOOC_JSON_HEAD);
    // error_log("add session API call response: " . $responseStr);
    $responseData = json_decode($responseStr, true);
    if (is_array($responseData) && count($responseData) > 0 && isset($responseData['success']) && isset($responseData['success']['session_id']) && isset($responseData['success']['session_token'])) {
        // error_log("session id: " . $responseData['success']['session_id']);
        // error_log("session token: " . $responseData['success']['session_token']);
        $sessionId = $responseData['success']['session_id'];
        $sessionToken = $responseData['success']['session_token'];
        // INSERT OR UPDATE
        if ($colmoocUserSession) {
            Database::get()->query("UPDATE colmooc_user_session SET session_id = ?s, session_token = ?s WHERE user_id = ?d AND activity_id = ?d", $sessionId, $sessionToken, $uid, $conf->chat_activity_id);
        } else {
            Database::get()->query("INSERT INTO colmooc_user_session (user_id, activity_id, session_id, session_token) VALUES (?d, ?d, ?s, ?s)", $uid, $conf->chat_activity_id, $sessionId, $sessionToken);
        }
    } else {
        return array(false, false);
    }
//    }

    return array($sessionId, $sessionToken);
}

function custom_request($url, $post_data, $method, $header) {

    $context = stream_context_create(array(
        'http' => array(
            'method' => $method,
            'header' => $header,
            'content' => $post_data
        )
    ));

    $response = file_get_contents($url, FALSE, $context);

    return $response;
}


// ATTIC

//function colmooc_create() {
//    global $tool_content, $course_id, $siteName;
//
//    if (!extension_loaded('curl')) {
//        $tool_content .= "SYNC FAILED";
//        return;
//    }
//
//    // gather all data
//    $crow = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $course_id);
//    $ctitle = q($crow->title) . " (" . $crow->public_code . ")";
//    $platform_id = 201;
//    $base_url = "https://mklab.iti.gr";
//    $accept_head = array("Accept: text/html; charset=UTF-8");
//    $json_head = "Content-Type: application/json\r\n";
//
//    // api delete current course
//    $delete_course_url = $base_url . "/colmoocapi/api/course/delete?course_id=" . $course_id . "&platform_id=" . $platform_id;
//    list($response, $http_code, $response_headers) = curl_custom_request($delete_course_url, null, "DELETE", $accept_head);
//    $tool_content .= "delete course API call, status: " . $http_code . ", response: " . $response . "<br/><br/>";
//
//    // api add current course
//    $add_course_url = $base_url . "/colmoocapi/api/course/add";
//    $add_data = json_encode(array(array(
//        "platform_id" => $platform_id,
//        "course_id" => $course_id,
//        "title" => $ctitle,
//        "platform" => $siteName
//    )));
//    $response = custom_request($add_course_url, $add_data, "POST", $json_head);
//    $tool_content .= "add course API call response: " . $response . "<br/><br/>";
//
//    // api get current course
//    $get_course_url = $base_url . "/colmoocapi/api/course?course_id=". $course_id ."&platform_id=" . $platform_id;
//    list($response, $http_code, $response_headers) = curl_custom_request($get_course_url, null, "GET", $accept_head);
//    $tool_content .= "get course API call, status: " . $http_code . ", response: " . $response . "<br/><br/>";
//
//    // finished
//    $tool_content .= "SYNC SUCCESSFUL" . "<br/><br/>";
//}

//function curl_custom_request($url, $post_data = null, $req = null, $set_headers = null) {
//    $response = null;
//    $http_code = null;
//    $headers = array();
//
//    $ch = curl_init();
//
//    curl_setopt($ch, CURLOPT_URL, $url);
//    if ($req != null) {
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $req);
//    }
//    if ($post_data != null && is_array($post_data) && count($post_data) > 0) {
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
//    }
//    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    if ($set_headers != null && is_array($set_headers) && count($set_headers) > 0) {
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $set_headers);
//    }
//    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
//        $len = strlen($header);
//        $header = explode(':', $header, 2);
//        if (count($header) < 2) { // ignore invalid headers
//            return $len;
//        }
//
//        $name = strtolower(trim($header[0]));
//        if (!array_key_exists($name, $headers)) {
//            $headers[$name] = [trim($header[1])];
//        } else {
//            $headers[$name][] = trim($header[1]);
//        }
//
//        return $len;
//    });
//
//    $response = curl_exec($ch);
//    if(!curl_errno($ch)) {
//        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//    }
//    curl_close($ch);
//
//    return array($response, $http_code, $headers);
//}
