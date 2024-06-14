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
 * ======================================================================== */

/**
 * @file pollparticipate.php
 */

$require_current_course = TRUE;
$require_user_registration = true;
$require_help = TRUE;
$helpTopic = 'questionnaire';

require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/progress/ViewingEvent.php';

load_js('bootstrap-slider');

$toolName = $langQuestionnaire;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langQuestionnaire);
//Identifying ajax request that cancels an active attempt
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($_POST['action'] == 'refreshSession') {
            // Does nothing just refreshes the session
            exit();
        }
}
if (!isset($_REQUEST['UseCase'])) {
    $_REQUEST['UseCase'] = "";
}
if (!isset($_REQUEST['pid'])) {
    die();
}

$query = "SELECT pid FROM poll WHERE course_id = ?d AND pid = ?d";
$query_params[] = $course_id;
$query_params[] = $_REQUEST['pid'];
if (!$is_course_reviewer) {
    $gids = user_group_info($uid, $course_id);
    if (!empty($gids)) {
        $gids_sql_ready = implode(',',array_keys($gids));
    } else {
        $gids_sql_ready = "''";
    }
    $query .= " AND
                    (assign_to_specific = '0' OR assign_to_specific != '0' AND pid IN
                       (SELECT poll_id FROM poll_to_specific WHERE user_id = ?d UNION SELECT poll_id FROM poll_to_specific WHERE group_id IN ($gids_sql_ready))
                    )";
    $query_params[] = $uid;
}
$p = Database::get()->querySingle($query, $query_params);

if (!$p) { // check poll access
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
// check poll validity
$pq = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d", $_REQUEST['pid']);
if(!$pq) {
    //Session::messages($langPollNoQuestions);
    Session::flash('message',$langPollNoQuestions);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

switch ($_REQUEST['UseCase']) {
    case 1:
        printPollForm();
        break;
    case 2:
        submitPoll();
        break;
    default:
        printPollForm();
}

draw($tool_content, 2, null, $head_content);

/**
 * @brief display poll form
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langSubmit
 * @global type $langPollInactive
 * @global type $langPollUnknown
 * @global type $uid
 * @global type $langPollAlreadyParticipated
 * @global type $is_editor
 * @global type $langBack
 * @global type $langQuestion
 * @global type $langCancel
 * @global type $langCollesLegend
 * @global type $head_content
 * @global type $pageName
 * @global type $langPollParticipantInfo
 */
function printPollForm() {
    global $course_id, $course_code, $tool_content,
    $langSubmit, $langPollInactive, $langPollUnknown, $uid,
    $langPollAlreadyParticipated, $is_editor, $langBack, $langQuestion,
    $langCancel, $head_content, $langPollParticipantInfo, $langCollesLegend,
    $pageName, $lang_rate1, $lang_rate5, $langDescription;

    $refresh_time = 300000; // Refresh PHP session every 5 min. (in ms)
    $head_content .= "
    <style>.slider-tick-label { font-size: 12px; white-space: normal; }</style>
    <script>
        $(function() {
            $('.grade_bar').slider({
               ticks: [1, 2, 3, 4, 5],
               ticks_labels: ['$lang_rate1', '', '', '', '$lang_rate5']
            });
            setInterval(function() {
                $.ajax({
                  type: 'POST',
                  data: { action: 'refreshSession'}
                });
            }, $refresh_time);
        });
    </script>";

    $pid = $_REQUEST['pid'];

    // check if user has participated
    $has_participated = Database::get()->querySingle("SELECT COUNT(*) AS count FROM poll_user_record WHERE uid = ?d AND pid = ?d", $uid, $pid)->count;
    if ($uid && $has_participated > 0 && !$is_editor){
       // Session::Messages($langPollAlreadyParticipated);
        Session::flash('message',$langPollAlreadyParticipated);
        Session::flash('alert-class', 'alert-warning');
        if (isset($_REQUEST['unit_id'])) {
            redirect_to_home_page('modules/units/index.php?course='.$course_code.'&id='.$_REQUEST['unit_id']);
        } else {
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }
    }
    // *****************************************************************************
    //      Get poll data
    //******************************************************************************/

    $thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d "
            . "ORDER BY pid",$course_id, $pid);
    $temp_CurrentDate = date("Y-m-d H:i");
    $temp_StartDate = $thePoll->start_date;
    $temp_EndDate = $thePoll->end_date;
    $temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
    $temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
    $temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));

    if ($is_editor || ($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {

        $pageName = $thePoll->name;
        if (isset($_REQUEST['unit_id'])) {
            $back_link = "../units/index.php?course=$course_code&amp;id=$_REQUEST[unit_id]";
        } else {
            $back_link = "index.php?course=$course_code";
        }

        if ($thePoll->description) {
            $tool_content .= "<div class='col-12 mb-4'>
                                <div class='card panelCard px-lg-4 py-lg-3 mb-4'>
                                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                        <h3>$langDescription</h3>
                                    </div>
                                    <div class='card-body'>
                                        " . standard_text_escape($thePoll->description) . "
                                    </div>
                                </div>
                             </div>";
        }
        if (isset($_REQUEST['unit_id'])) {
            $form_link = "../units/view.php?course=$course_code&amp;res_type=questionnaire&amp;id=$_REQUEST[unit_id]";
        } else {
            $form_link = "$_SERVER[SCRIPT_NAME]?course=$course_code";
        }
        $tool_content .= "

            <form class='form-horizontal' role='form' action='$form_link' id='poll' method='post'>
            <input type='hidden' value='2' name='UseCase'>
            <input type='hidden' value='$pid' name='pid'>";
        if (isset($_REQUEST['unit_id'])) {
            $tool_content .= "<input type='hidden' value='$_REQUEST[unit_id]' name='unit_id'>";
        }

        //*****************************************************************************
        //      Get answers + questions
        //******************************************************************************/
        $questions = Database::get()->queryArray("SELECT * FROM poll_question
            WHERE pid = ?d ORDER BY q_position ASC", $pid);
        if (!$uid) {
            $email = Session::has('participantEmail') ? Session::get('participantEmail') : '';
            $email_error = Session::getError('participantEmail') ? " has-error" : "";
            $tool_content .= "
                <div class='card panelCard px-lg-4 py-lg-3 mb-4'>
                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                        <h3>$langPollParticipantInfo</h3>
                    </div>
                    <div class='card-body'>
                        <div class='form-group$email_error'>
                            <label for='participantEmail' class='col-12  control-label-notes'>Email:</label>
                            <div class='col-12'>
                                <input type='text' name='participantEmail' id='participantEmail' class='form-control' value='$email'>
                                ".(Session::getError('participantEmail') ? "<span class='help-block Accent-200-cl'>" . Session::getError('participantEmail') . "</span>" : "")."
                            </div>
                        </div>
                    </div>
                </div>";
        }
        $pollType = Database::get()->querySingle("SELECT `type` FROM poll WHERE pid = ?d", $pid)->type;
        $incomplete_resubmission = isset($_SESSION["poll_answers_$pid"]);
        $incomplete_answers = $_SESSION["poll_answers_$pid"];
        unset($_SESSION["poll_answers_$pid"]);
        $i=1;
        $tool_content .= " <div class='row row-cols-1 row-cols-md-2 g-4'>";
        foreach ($questions as $theQuestion) {
            $pqid = $theQuestion->pqid;
            $qtype = $theQuestion->qtype;
            if($qtype==QTYPE_LABEL) {
                $tool_content .= "
                <div class='col-12'>
                   <div class='alert alert-info m-0 TextBold text-uppercase text-center'>" . standard_text_escape($theQuestion->question_text) . "</div>
                </div>";
            } else {
                $columnPanel = 'col';
                if($qtype==QTYPE_LABEL){
                    $columnPanel = 'col-12';
                }
                $tool_content .= "
               
                <div class='$columnPanel'>
                    <div class='card panelCard px-lg-4 py-lg-3 h-100 panelCard-questionnaire poll-panel'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>$langQuestion $i</h3>
                        </div>
                        <div class='card-body'>
                            <p class='TextMedium Neutral-900-cl mb-2'>".q_math($theQuestion->question_text)."</p>
                            <input type='hidden' name='question[$pqid]' value='$qtype'>";
                if ($qtype == QTYPE_SINGLE || $qtype == QTYPE_MULTIPLE) {
                    $name_ext = ($qtype == QTYPE_SINGLE)? '': '[]';
                    $type_attr = ($qtype == QTYPE_SINGLE)? "radio": "checkbox";
                    $class_type_attr = ($qtype == QTYPE_SINGLE)? "radio-label": "label-container";
                    $checkMark_class = ($qtype == QTYPE_SINGLE)? "": "<span class='checkmark'></span>";
                    $answers = Database::get()->queryArray("SELECT * FROM poll_question_answer
                                WHERE pqid = ?d ORDER BY pqaid", $pqid);
                    if ($qtype == QTYPE_MULTIPLE) $tool_content .= "<input type='hidden' name='answer[$pqid]' value='-1'>";
                    foreach ($answers as $theAnswer) {
                        $tool_content .= "
                        <div class='form-group'>
                            <div class='col-sm-offset-1 col-sm-11'>
                                <div class='$type_attr'>
                                    <label class='$class_type_attr'>
                                        <input type='$type_attr' name='answer[$pqid]$name_ext' value='$theAnswer->pqaid'>
                                        $checkMark_class
                                        ".q_math($theAnswer->answer_text)."
                                       
                                    </label>
                                </div>
                            </div>
                        </div>";
                    }
                    if ($qtype == QTYPE_SINGLE) {
                        $tool_content .= "
                        <div class='form-group'>
                            <div class='col-sm-offset-1 col-sm-11'>
                                <div class='$type_attr'>
                                    <label class='$class_type_attr'>
                                        <input type='$type_attr' name='answer[$pqid]' value='-1' checked>
                                        $checkMark_class
                                        $langPollUnknown
                                        
                                    </label>
                                </div>
                            </div>
                        </div>";

                    }
                } elseif ($qtype == QTYPE_SCALE) {
                    if (($pollType == 1) or ($pollType == 2)) {
                        $tool_content .= "<div style='margin-bottom: 0.5em;'><small>".q($langCollesLegend)."</small></div>";
                    }
                    $tool_content .= "<div class='form-group d-flex justify-content-center mb-5'>
                        <div class='col-sm-offset-2 col-sm-10' style='padding-top:15px;'>
                            <input name='answer[$pqid]' class='grade_bar' data-slider-id='ex1Slider' type='text' data-slider-min='1' data-slider-max='$theQuestion->q_scale' data-slider-step='1' data-slider-value='1'>
                        </div>
                    </div>";
                } elseif ($qtype == QTYPE_FILL) {
                    $tool_content .= "
                        <div class='form-group margin-bottom-fat'>
                            <div class='col-sm-12 margin-top-thin'>
                                <textarea class='form-control' name='answer[$pqid]'></textarea>
                            </div>
                        </div>";
                }
                $tool_content .= "
                        </div>
                    </div></div>
                ";
                $i++;
            }
        }
        $tool_content .= "</div>";
        $tool_content .= "<div class='d-flex justify-content-center mt-5'>";
        if ($is_editor) {
            $tool_content .= "<a class='btn cancelAdminBtn' href='index.php?course=$course_code'>" . q($langBack). "</a>";
        } else {
            $tool_content .= "<input class='btn submitAdminBtn blockUI' name='submit' type='submit' value='".q($langSubmit)."'>";
            if (isset($_REQUEST['unit_id'])) {
                $tool_content .= "<a class='btn cancelAdminBtn ms-3' href='../units/index.php?course=$course_code&amp;id=$_REQUEST[unit_id]'>" . q($langCancel) . "</a>";
            } else {
                $tool_content .= "<a class='btn cancelAdminBtn ms-3' href='index.php?course=$course_code'>" . q($langCancel) . "</a>";
            }
        }
        $tool_content .= "</div></form>";
    } else {
        //Session::Messages($langPollInactive);
        Session::flash('message',$langPollInactive);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
}

/**
 * @brief submit poll
 */
function submitPoll() {
    global $tool_content, $course_code, $uid, $langPollSubmitted, $langBack,
           $langUsage, $langTheField, $langFormErrors, $urlServer, $langPollParticipateConfirm,
           $langPollEmailUsed, $langPollParticipateConfirmation, $course_id;

    $pid = intval($_POST['pid']);
    $poll = Database::get()->querySingle("SELECT * FROM poll WHERE pid = ?d", $pid);
    $v = new Valitron\Validator($_POST);
    if (!$uid) {
        $v->addRule('unique', function($field, $value, array $params) use ($pid){
            return !Database::get()->querySingle("SELECT count(*) AS count FROM poll_user_record WHERE email = ?s AND pid = ?d", $value, $pid)->count;
        }, $langPollEmailUsed);
        $v->rule('required', array('participantEmail'));
        $v->rule('email', array('participantEmail'));
        $v->rule('unique', array('participantEmail'));
        $v->labels(array('participantEmail' => "$langTheField Email"));
    }
    if($v->validate()) {
        // first populate poll_answer
        $CreationDate = date("Y-m-d H:i");
        $answer = isset($_POST['answer'])? $_POST['answer']: array();
        if ($uid) {
            $eventData = new stdClass();
            $eventData->courseId = $course_id;
            $eventData->uid = $uid;
            $eventData->activityType = ViewingEvent::QUESTIONNAIRE_ACTIVITY;
            $eventData->module = MODULE_ID_QUESTIONNAIRE;
            $eventData->resource = intval($pid);
            ViewingEvent::trigger(ViewingEvent::NEWVIEW, $eventData);

            $user_record_id = Database::get()->query("INSERT INTO poll_user_record (pid, uid) VALUES (?d, ?d)", $pid, $uid)->lastInsertID;
        } else {
            require_once 'include/sendMail.inc.php';
            $participantEmail = $_POST['participantEmail'];
            $verification_code = randomkeys(255);
            $user_record_id = Database::get()->query("INSERT INTO poll_user_record (pid, uid, email, email_verification, verification_code) VALUES (?d, ?d, ?s, ?d, ?s)", $pid, $uid, $participantEmail, 0, $verification_code)->lastInsertID;
            $subject = $langPollParticipateConfirmation;
            $body_html = "
             <!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <div id='header-title'>$subject</div>
                </div>
            </div>
            <!-- Body Section -->
            <div id='mail-body'>
                <br>
                <div id='mail-body-inner'>$langPollParticipateConfirm:<br><br>
                    <a href='{$urlServer}modules/questionnaire/index.php?course=$course_code&amp;verification_code=$verification_code'>test</a>
                </div>
            </div>";
            $body_plain = html2text($body_html);
            send_mail_multipart('', '', '', $participantEmail, $subject, $body_plain, $body_html);
        }

        $question = isset($_POST['question'])? $_POST['question']: array();
        foreach ($question as $pqid => $qtype) {
            $pqid = intval($pqid);
            if ($qtype == QTYPE_MULTIPLE) {
                if(is_array($answer[$pqid])){
                    foreach ($answer[$pqid] as $aid) {
                        $aid = intval($aid);
                        Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date)
                            VALUES (?d, ?d, ?d, '', NOW())", $user_record_id, $pqid, $aid);
                    }
                } else {
                    $aid = -1;
                    Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date)
                        VALUES (?d, ?d, ?d, '', NOW())", $user_record_id, $pqid, $aid);
                }
                continue;
            } elseif ($qtype == QTYPE_SCALE) {
                $aid = 0;
                $answer_text = $answer[$pqid];
            } elseif ($qtype == QTYPE_SINGLE) {
                $aid = intval($answer[$pqid]);
                $answer_text = '';
            } elseif ($qtype == QTYPE_FILL) {
                $answer_text = $answer[$pqid];
                $aid = 0;
            } else {
                continue;
            }
            Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date)
                            VALUES (?d, ?d, ?d, ?s, ?t)", $user_record_id, $pqid, $aid, $answer_text, $CreationDate);
        }
        $end_message = Database::get()->querySingle("SELECT end_message FROM poll WHERE pid = ?d", $pid)->end_message;
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>".$langPollSubmitted."</span></div></div>";
        if (!empty($end_message)) {
            $tool_content .=  $end_message;
        }
        $tool_content .= "<br><div class='d-flex text-center'>";
        if (isset($_REQUEST['unit_id'])) {
            $tool_content .= "<a class='btn cancelAdminBtn' href='../units/index.php?course=$course_code&amp;id=$_REQUEST[unit_id]'>$langBack</a>";
        } else {
            $tool_content .= "<a class='btn cancelAdminBtn' href='index.php?course=$course_code'>$langBack</a>";
        }
        if ($poll->show_results) {
            if (isset($_REQUEST['unit_id'])) {
                $tool_content .= "<a class='btn submitAdminBtn ms-3' href='../units/view.php?course=$course_code&amp;res_type=questionnaire_results&amp;unit_id=$_REQUEST[unit_id]&amp;pid=$pid'>$langUsage</a>";
            } else {
                $tool_content .= "<a class='btn submitAdminBtn ms-3' href='pollresults.php?course=$course_code&amp;pid=$pid'>$langUsage</a>";
            }
        }
        $tool_content .= "</div>";
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page("modules/questionnaire/pollparticipate.php?course=$course_code&UseCase=1&pid=$pid");
    }
}
