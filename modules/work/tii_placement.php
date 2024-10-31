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

ini_set('log_errors_max_len', 0);
set_time_limit(0);

error_log("=== received a tii outcomes tool placement request ===");

// receive POST JSON data
$data = json_decode(file_get_contents('php://input'), true);

if ( is_array($data) && count($data) > 0 && isset($data['lis_result_sourcedid']) && isset($data['paperid']) && isset($data['outcomes_tool_placement_url']) ) {

    require_once '../../include/baseTheme.php';
    require_once 'include/lib/curlutil.class.php';
    require_once 'modules/lti_consumer/lti-functions.php';
    require_once 'modules/work/functions.php';
    require_once 'include/lib/fileUploadLib.inc.php';
    require_once 'modules/progress/AssignmentEvent.php';
    require_once 'modules/analytics/AssignmentAnalyticsEvent.php';
    require_once 'include/log.class.php';
    require_once 'include/sendMail.inc.php';

    // validate outcomes_tool_placement_url
    $launch_url = strtok($data['outcomes_tool_placement_url'], "?");
//    $patternmatch = "https://api.turnitin.com/api/lti/1p0/outcome_tool_data/";
//    if (substr($launch_url, 0, strlen($patternmatch)) !== $patternmatch) {
//        error_log("invalid placement url detected, exiting...");
//        die();
//    }

    // extract sourcedid info
    $sourcedid = $data['lis_result_sourcedid'];
    list($assignment_id, $uid, $assignment, $lti, $user) = lti_verify_extract_sourcedid($sourcedid, PHP_INT_MAX);
    $course_code = Database::get()->querySingle("SELECT code FROM course WHERE id = ?d", $assignment->course_id)->code;

    // POST to outcomes tool placement url
    $post_data = lti_prepare_oauth_only_data($launch_url, $lti->lti_provider_key, $lti->lti_provider_secret);
    list($response, $http_code, $response_headers) = CurlUtil::httpPostRequest($launch_url, $post_data);

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
                list($file_response, $file_http_code, $file_response_headers) = CurlUtil::httpPostRequest($orig_launch_url, $lti_launch_data, true, $temp_file);
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
                $student_email = uid_to_email($uid);
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
                                    (uid, assignment_id, submission_date, submission_ip, file_path,
                                      file_name, comments, grade_comments, grade_comments_filename,
                                      grade_comments_filepath, grade_submission_ip)
                                     VALUES (?d, ?d, " . DBHelper::timeAfter() . ", ?s, ?s, ?s, '', '', '', '', '')",
                        $uid, $assignment_id, Log::get_client_ip(), $work_filename, $original_filename);
                    triggerGame($assignment->course_id, $uid, $assignment_id);
                    triggerAssignmentAnalytics($assignment->course_id, $uid, $assignment_id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
                    triggerAssignmentAnalytics($assignment->course_id, $uid, $assignment_id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
                }
                // notify course admin (if requested)
                if ($assignment->notification) {
                    $emailSubject = "$logo - $langAssignmentPublished";
                    $emailHeaderContent = "
                        <div id='mail-header'>
                            <br>
                            <div>
                                <div id='header-title'>$langHasAssignmentPublished $langTo $langsCourse <a href='{$urlServer}courses/$course_code/'>" . q(course_id_to_title($assignment->course_id)) . "</a>.</div>
                                <ul id='forum-category'>
                                    <li><span><b>$langSender:</b></span> <span class='left-space'>" . $student_name . "</span></li>
                                </ul>
                            </div>
                        </div>";
                    $emailBodyContent = "
                        <div id='mail-body'>
                            <br>
                            <div><b>$langAssignment:</b> <span class='left-space'>".q($assignment->title)."</span></div><br>
                        </div>";

                    $emailContent = $emailHeaderContent . $emailBodyContent;
                    $emailBody = html2text($emailContent);

                    $profs = Database::get()->queryArray("SELECT user.id AS prof_uid, user.email AS email,
                              user.surname, user.givenname
                           FROM course_user JOIN user ON user.id = course_user.user_id
                           WHERE course_id = ?d AND course_user.status = " . USER_TEACHER . "", $assignment->course_id);

                    foreach ($profs as $prof) {
                        if (!get_user_email_notification_from_courses($prof->prof_uid) or (!get_user_email_notification($prof->prof_uid, $assignment->course_id))) {
                            continue;
                        } else {
                            $to_name = $prof->givenname . " " . $prof->surname;
                            if (!send_mail_multipart($logo, $student_email, $to_name, $prof->email, $emailSubject, $emailBody, $emailContent)) {
                                continue;
                            }
                        }
                    }
                }
            }
        }
    }
}
