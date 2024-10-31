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

require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/colmoocapp.php';

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

    return array($sessionId, $sessionToken);
}

function colmooc_add_teacher_lasession() {
    global $colmoocapp, $uid, $course_id;

    if (!ini_get('allow_url_fopen')) {
        return array(false, false);
    }

    // api add la session
    $add_lasession_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/api/lasession/add";
    $add_lasession_data = json_encode(array(array(
        "platform_id" => $colmoocapp->getParam(ColmoocApp::PLATFORM_ID)->value(),
        "course_id" => $course_id,
        "teacher_id" => $uid,
        "colstudent_id" => 0
    )));

    $responseStr = custom_request($add_lasession_url, $add_lasession_data, "POST", COLMOOC_JSON_HEAD);
    // error_log("add lasession API call response: " . $responseStr);
    $responseData = json_decode($responseStr, true);
    if (is_array($responseData) && count($responseData) > 0 && isset($responseData['success']) && isset($responseData['success']['lasession_id']) && isset($responseData['success']['lasession_token'])) {
        // error_log("lasession id: " . $responseData['success']['lasession_id']);
        // error_log("session token: " . $responseData['success']['lasession_token']);
        $laSessionId = $responseData['success']['lasession_id'];
        $laSessionToken = $responseData['success']['lasession_token'];
    } else {
        return array(false, false);
    }

    return array($laSessionId, $laSessionToken);
}

function colmooc_add_student_lasession() {
    global $colmoocapp, $uid, $course_id;

    if (!ini_get('allow_url_fopen')) {
        return array(false, false);
    }

    // colstudent id
    $colstudentId = null;
    $colmoocUser = Database::get()->querySingle("SELECT * FROM colmooc_user WHERE user_id = ?d", $uid);
    if ($colmoocUser && $colmoocUser->colmooc_id) {
        $colstudentId = $colmoocUser->colmooc_id;
    }

    if ($colstudentId == null) {
        return array(false, false);
    }

    // api add la session
    $add_lasession_url = $colmoocapp->getParam(ColmoocApp::BASE_URL)->value() . "/colmoocapi/api/lasession/add";
    $add_lasession_data = json_encode(array(array(
        "platform_id" => $colmoocapp->getParam(ColmoocApp::PLATFORM_ID)->value(),
        "course_id" => $course_id,
        "teacher_id" => 0,
        "colstudent_id" => $colstudentId
    )));

    $responseStr = custom_request($add_lasession_url, $add_lasession_data, "POST", COLMOOC_JSON_HEAD);
    // error_log("add lasession API call response: " . $responseStr);
    $responseData = json_decode($responseStr, true);
    if (is_array($responseData) && count($responseData) > 0 && isset($responseData['success']) && isset($responseData['success']['lasession_id']) && isset($responseData['success']['lasession_token'])) {
        // error_log("lasession id: " . $responseData['success']['lasession_id']);
        // error_log("session token: " . $responseData['success']['lasession_token']);
        $laSessionId = $responseData['success']['lasession_id'];
        $laSessionToken = $responseData['success']['lasession_token'];
    } else {
        return array(false, false);
    }

    return array($laSessionId, $laSessionToken);
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

function display_session_status($status) {
    global $langColMoocSessionStatusNoPair, $langColMoocSessionStatusFinished, $langColMoocSessionStatusNoFinalAnswer;

    switch ($status) {
        case 0:
            return $langColMoocSessionStatusNoPair;
            break;
        case 1:
            return $langColMoocSessionStatusFinished;
            break;
        case 2:
            return $langColMoocSessionStatusNoFinalAnswer;
            break;
        default:
            return $langColMoocSessionStatusNoPair;
            break;
    }
}

function display_finished_count($user_id) {
    $mod_cnt = Database::get()->querySingle("SELECT COUNT(id) AS cnt FROM colmooc_pair_log WHERE moderator_id = ?d AND session_status = 1", $user_id)->cnt;
    $par_cnt = Database::get()->querySingle("SELECT COUNT(id) AS cnt FROM colmooc_pair_log WHERE partner_id = ?d AND session_status = 1", $user_id)->cnt;
    $cnt = $mod_cnt + $par_cnt;
    return $cnt;
}
