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

ini_set('log_errors_max_len', 0);
set_time_limit(0);

error_log("=== received a tii outcomes tool placement request ===");

// receive POST JSON data
$data = json_decode(file_get_contents('php://input'), true);

if ( is_array($data) && count($data) > 0 && isset($data['lis_result_sourcedid']) && isset($data['paperid']) && isset($data['outcomes_tool_placement_url']) ) {

    require_once '../../include/baseTheme.php';
    require_once 'modules/lti_consumer/lti-functions.php';
    require_once 'modules/work/functions.php';
    require_once 'include/lib/fileUploadLib.inc.php';
    require_once 'modules/progress/AssignmentEvent.php';
    require_once 'modules/analytics/AssignmentAnalyticsEvent.php';
    require_once 'include/log.class.php';

    // validate outcomes_tool_placement_url
    $launch_url = strtok($data['outcomes_tool_placement_url'], "?");
//    $patternmatch = "https://api.turnitin.com/api/lti/1p0/outcome_tool_data/";
//    if (substr($launch_url, 0, strlen($patternmatch)) !== $patternmatch) {
//        error_log("invalid placement url detected, exiting...");
//        die();
//    }

    // extract sourcedid info
    $sourcedid = $data['lis_result_sourcedid'];
    list($assignment_id, $uid, $assignment, $lti, $user) = lti_verify_extract_sourcedid($sourcedid, 3600);
    $course_code = Database::get()->querySingle("SELECT code FROM course WHERE id = ?d", $assignment->course_id)->code;

    // POST to outcomes tool placement url
    $post_data = lti_prepare_oauth_only_data($launch_url, $lti->lti_provider_key, $lti->lti_provider_secret);
    list($response, $http_code, $response_headers) = tii_post_request($launch_url, $post_data);

    if ($http_code != null && intval($http_code) == 200) {
        $data1 = json_decode($response, true);


        if (array_key_exists("outcome_originalfile", $data1) && array_key_exists("launch_url", $data1['outcome_originalfile'])) {

            // validate outcome_originalfile url
            $orig_launch_url = strtok($data1['outcome_originalfile']['launch_url'], "?");
//            $patternmatch = "https://api.turnitin.com/api/lti/1p0/download/orig/";
//            if (substr($orig_launch_url, 0, strlen($patternmatch)) !== $patternmatch) {
//                error_log("invalid outcome_originalfile launch_url detected, exiting...");
//                die();
//            }

            // POST to outcome_originalfile url
            $lti_launch_data = lti_prepare_launch_data(
                $assignment->course_id,
                $course_code,
                $language,
                $uid,
                $lti->lti_provider_key,
                $assignment_id,
                "assignment",
                $assignment->title,
                $assignment->description,
                $assignment->launchcontainer,
                $assignment
            );
            $oauth_sig = lti_build_signature($orig_launch_url, $lti->lti_provider_secret, $lti_launch_data);
            $lti_launch_data['oauth_signature'] = $oauth_sig;
            $temp_filename = "submission-" . intval($data['paperid']) . ".out";
            $temp_file = $webDir . "/courses/" . $course_code . '/temp/' . $temp_filename;
            $max_tries = 100;
            $i = 0;
            do {
                list($file_response, $file_http_code, $file_response_headers) = tii_post_request($orig_launch_url, $lti_launch_data, true, $temp_file);
                $i++;
                if ($i > $max_tries) {
                    error_log("error: max retries exhausted, giving up...");
                    break;
                }
            } while (intval($file_http_code) != 200);

            if ($file_http_code != null && intval($file_http_code) == 200) {

                // try to assign a filename to the submission
                $original_filename = $temp_filename;
                if (is_array($file_response_headers) && array_key_exists("content-disposition", $file_response_headers) && is_array($file_response_headers['content-disposition'])) {
                    $cont_disp = $file_response_headers['content-disposition'][0];
                    $lasteq = strrpos($cont_disp, "=") + 1;
                    $original_filename = substr($cont_disp,$lasteq,strlen($cont_disp));
                }

                // submit work
                $student_name = trim(uid_to_name($uid));
                $local_name = !empty($student_name)? $student_name : uid_to_name($uid, 'username');
                $am = Database::get()->querySingle("SELECT am FROM user WHERE id = ?d", $uid)->am;
                $local_name .= (!empty($am)) ? $am : '';
                $local_name = greek_to_latin($local_name);
                $local_name = replace_dangerous_char($local_name);
                $workPath = $webDir . '/courses/' . $course_code . '/work';
                make_dir("$workPath/" . $assignment->secret_directory);
                $ext = get_file_extension($original_filename);
                $work_filename = $assignment->secret_directory . "/$local_name" . (empty($ext) ? '' : '.' . $ext);
                copy($temp_file, "$workPath/$work_filename");
                unlink($temp_file);

                // insert or update
                $exists = Database::get()->querySingle("SELECT COUNT(id) AS cnt FROM assignment_submit 
                                    WHERE uid = ?d AND assignment_id = ?d", $uid, $assignment_id)->cnt;
                if ($exists) {
                    Database::get()->query("UPDATE assignment_submit SET submission_date = " . DBHelper::timeAfter() . ", 
                                    submission_ip = ?s, file_path = ?s, file_name = ?s, comments = ''
                                    WHERE uid = ?d AND assignment_id = ?d", Log::get_client_ip(), $work_filename, $original_filename, $uid, $assignment_id);
                    triggerGame($assignment->course_id, $uid, $assignment_id);
                    triggerAssignmentAnalytics($assignment->course_id, $uid, $assignment_id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
                    triggerAssignmentAnalytics($assignment->course_id, $uid, $assignment_id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
                } else {
                    Database::get()->query("INSERT INTO assignment_submit
                                    (uid, assignment_id, submission_date, submission_ip, file_path, file_name, comments)
                                     VALUES (?d, ?d, " . DBHelper::timeAfter() . ", ?s, ?s, ?s, '')",
                        $uid, $assignment_id, Log::get_client_ip(), $work_filename, $original_filename);
                    triggerGame($assignment->course_id, $uid, $assignment_id);
                    triggerAssignmentAnalytics($assignment->course_id, $uid, $assignment_id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
                    triggerAssignmentAnalytics($assignment->course_id, $uid, $assignment_id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
                }
            }
        }
    }
}