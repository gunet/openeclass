<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

  ============================================================================
  @Description: Main script for the work tool
  ============================================================================
 */

function do_sync() {
    global $tool_content, $course_id, $siteName;

    if (!extension_loaded('curl')) {
        $tool_content .= "SYNC FAILED";
        return;
    }

    // gather all data
    $crow = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d", $course_id);
    $studs = Database::get()->queryArray("SELECT u.* FROM user u JOIN course_user cu ON (cu.user_id = u.id) AND cu.course_id = ?d AND cu.status = 5", $course_id);
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

function curl_custom_request($url, $post_data = null, $req = null, $set_headers = null) {
    $response = null;
    $http_code = null;
    $headers = array();

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    if ($req != null) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $req);
    }
    if ($post_data != null && is_array($post_data) && count($post_data) > 0) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($set_headers != null && is_array($set_headers) && count($set_headers) > 0) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $set_headers);
    }
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) { // ignore invalid headers
            return $len;
        }

        $name = strtolower(trim($header[0]));
        if (!array_key_exists($name, $headers)) {
            $headers[$name] = [trim($header[1])];
        } else {
            $headers[$name][] = trim($header[1]);
        }

        return $len;
    });

    $response = curl_exec($ch);
    if(!curl_errno($ch)) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }
    curl_close($ch);

    return array($response, $http_code, $headers);
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