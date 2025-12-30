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

/**
 * @file pollparticipate.php
 */

$require_current_course = TRUE;
$require_user_registration = true;
$require_help = TRUE;
$helpTopic = 'questionnaire';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'functions.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/progress/ViewingEvent.php';
require_once 'modules/lti_consumer/lti-functions.php';

load_js('bootstrap-slider');

$toolName = $langQuestionnaire;

if (isset($_GET['from_session_view'])) {
    if ($is_consultant) {
        $is_course_reviewer = true;
    }
    $session_title = Database::get()->querySingle("SELECT title FROM mod_session WHERE id = ?d",$_GET['session'])->title;
    $navigation[] = array('url' => $urlServer . '/modules/session/index.php?course=' . $course_code, 'name' => $langSession);
    $navigation[] = array('url' => $urlServer . '/modules/session/session_space.php?course=' . $course_code . "&session=" . $_GET['session'] , 'name' => $session_title);
} else {
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langQuestionnaire);
}
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
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
$pid = intval($_REQUEST['pid']);
if (!$pid) {
    forbidden();
}

$query = "SELECT pid FROM poll WHERE course_id = ?d AND pid = ?d";
$query_params[] = $course_id;
$query_params[] = $pid;
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

if($is_consultant){
    $is_editor = true;
}
if (!$p) { // check poll access
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
// check poll type (for limesurvey)
$pollObj = Database::get()->querySingle("SELECT * FROM poll WHERE pid = ?d", $pid);
$pollIsLime = false;
if ($pollObj && $pollObj->type == POLL_LIMESURVEY) {
    $pollIsLime = true;
}
// check poll validity
if(!hasPollQuestions($_REQUEST['pid']) && !$pollIsLime) {
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
 */
function printPollForm() {
    global $course_id, $course_code, $tool_content, $langSelect, $langDescription,
    $langSubmit, $langPollInactive, $langPollUnknown, $uid, $langAnswer,
    $langPollAlreadyParticipated, $is_editor, $is_course_reviewer, $langBack, $langQuestion,
    $langCancel, $head_content, $langPollParticipantInfo, $langCollesLegend,
    $pageName, $lang_rate1, $lang_rate5, $langForm, $pid, $langTypeOutMessage,
    $langPreviousQuestion, $langNextQuestion, $langCleanup, $langForbidden,
    $is_consultant, $is_coordinator, $langSubmissionOnBehalfOfUser, $urlAppend, $langProcessForFiliInTool;

    $unit_id = isset($_REQUEST['unit_id'])? intval($_REQUEST['unit_id']): null;
    $refresh_time = 300000; // Refresh PHP session every 5 min. (in ms)
    $head_content .= "
    <style>
        .slider.slider-horizontal{
            width: 500px;
        }
        .slider-tick-label {
            font-size: 12px;
            white-space: normal;
        }
    </style>
    <script>
        $(function() {
            $('.grade_bar').each(function () {
                var max = parseInt($(this).attr('data-slider-max'));
                var countAns = parseInt($(this).attr('data-answers'));
                if (max > 10) {
                    $(this).slider({ ticks: [1, max] });
                } else {
                    var ticks = Array.from(Array(max).keys());
                    // If the value of countAns equals with 0 then there aren't new texts for the scale.
                    if(countAns == 0) {
                        if (max == 5) {
                            $(this).slider({
                                ticks: ticks.map(function (i) { return i + 1 }),
                                ticks_labels: ['" . js_escape($lang_rate1) . "', '', '', '', '" . js_escape($lang_rate5) . "']
                            });
                        } else {
                            $(this).slider({
                                ticks: ticks.map(function (i) { return i + 1 }),
                                ticks_labels: ticks.map(function (i) { return i + 1 })
                            });
                        }
                    } else {
                        var texts_ans = $(this).attr('data-txt-answers');
                        separatedArray = texts_ans.split(',');
                        $(this).slider({
                            ticks: ticks.map(function (i) { return i + 1 }),
                            ticks_labels: separatedArray
                        });
                    }
                }
            })
            setInterval(function() {
                $.ajax({
                  type: 'POST',
                  data: { action: 'refreshSession'}
                });
            }, $refresh_time);
        });
    </script>

    <script>
        $(function() {

            var total_pages = $('#total_pages').val();
            var current_page = $('#current_page').val();
            var paginate_answers = $('#paginate_answers').val();

            if(paginate_answers > 0){
                for(var i=2; i<=total_pages; i++){
                    $('#q_page_'+i).css('display','none');
                }
            }

            if(current_page >= 2 && current_page<total_pages){
                $('#prevBtn').css('display','block');
                $('#nextBtn').css('display','block');
            }else if(current_page <= 1 && total_pages>1){
                $('#prevBtn').css('display','none');
                $('#nextBtn').css('display','block');
            }else if(current_page >= 2 && current_page >= total_pages){
                $('#prevBtn').css('display','block');
                $('#nextBtn').css('display','none');
            }else if(current_page <= 1 && total_pages==1){
                $('#prevBtn').css('display','none');
                $('#nextBtn').css('display','none');
            }

            $('#prevBtn').on('click', function(e){
                e.preventDefault();
                current_page--;
                $('#current_page').val(current_page);
            });
            $('#nextBtn').on('click', function(e){
                e.preventDefault();
                current_page++;
                $('#current_page').val(current_page);
            });

            $('#prevBtn,#nextBtn').on('click', function(e){
                e.preventDefault();
                if(current_page >= 2 && current_page<total_pages){
                    $('#prevBtn').css('display','block');
                    $('#nextBtn').css('display','block');
                }else if(current_page <= 1 && total_pages>1){
                    $('#prevBtn').css('display','none');
                    $('#nextBtn').css('display','block');
                }else if(current_page >= 2 && current_page >= total_pages){
                    $('#prevBtn').css('display','block');
                    $('#nextBtn').css('display','none');
                }else if(current_page <= 1 && total_pages==1){
                    $('#prevBtn').css('display','none');
                    $('#nextBtn').css('display','none');
                }

                for(var i=1; i<=total_pages; i++){
                    if(i==current_page){
                        $('#q_page_'+i).css('display','block');
                    }else{
                        $('#q_page_'+i).css('display','none');
                    }
                }
            });

        });
    </script>

    <script>
        $(function() {
            $('#onBehalfOfSelection').change(function(e) {
                e.preventDefault();
                var selectedValue = $(this).val();
                $('#onBehalfOfUserId').val(selectedValue);

                $('#onBehalfOfSelectionForm').submit();
            });
        });
    </script>

    <script>
        $(function() {
            $('.clearUpBtn').on('click', function(e){
                e.preventDefault();
                var arr = this.id.split('_');
                var typeQ = arr[0];
                var numberQ = arr[1];
                if(typeQ == 1) {
                    var classQ = '.QuestionType_'+typeQ+'.QuestionNumber_'+numberQ+' input[type=radio]';
                    $(classQ).prop('checked', false);
                } else if(typeQ == 2) {
                    var classQ = '.QuestionType_'+typeQ+'.QuestionNumber_'+numberQ+' textarea';
                    $(classQ).val('');
                } else if(typeQ == 3) {
                    var classQ = '.QuestionType_'+typeQ+'.QuestionNumber_'+numberQ+' input[type=checkbox]';
                    $(classQ).prop('checked', false);
                } else if(typeQ == 6) {
                    var classQ = '.QuestionType_'+typeQ+'.QuestionNumber_'+numberQ+' textarea';
                    $(classQ).val('');
                }
            });
        });
    </script>";

    // If a consultant is on behalf of user mode for submitting poll.
    $userDefault = $uid;
    if (isset($_GET['printPollForUser'])) {
        $userDefault = intval($_POST['userSelected']);
    }

    $pid = $_REQUEST['pid'];
    //      Get poll data
    $thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid",$course_id, $pid);
    $multiple_submissions = $thePoll->multiple_submissions;
    $default_answer = $thePoll->default_answer;

    if ($thePoll->require_answer) {
        $head_content .= "
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const form = document.getElementById('poll');
                        form.querySelectorAll('input, textarea').forEach(el => {
                            if (['radio', 'textarea'].includes(el.type) || el.tagName === 'TEXTAREA') {
                                if (el.tagName === 'TEXTAREA' && el.classList.contains('textarea-qtable')) {
                                    // Skip adding required to textarea with class 'textarea-qtable'
                                    return;
                                }
                                el.required = true;
                            }
                        });
                });

                document.addEventListener('DOMContentLoaded', () => {
                  const form = document.getElementById('poll');
                  if (!form) return;

                  form.addEventListener('submit', e => {
                    let valid = true;
                    const checkboxGroups = {};
                    form.querySelectorAll('input[type=\\\"checkbox\\\"]').forEach(cb => {
                      const name = cb.name;
                      if (!checkboxGroups[name]) checkboxGroups[name] = [];
                      checkboxGroups[name].push(cb);
                    });

                    for (const boxes of Object.values(checkboxGroups)) {
                      if (!boxes.some(cb => cb.checked)) {
                        valid = false;
                        boxes.forEach(cb => cb.classList.add('invalid-checkbox'));
                      } else {
                        boxes.forEach(cb => cb.classList.remove('invalid-checkbox'));
                      }
                    }

                    if (!valid) {
                        e.preventDefault();
                    }
                  });

                  form.querySelectorAll('input[type=\\\"checkbox\\\"]').forEach(cb => {
                    cb.addEventListener('change', () => {
                      const group = form.querySelectorAll('input[name=\\\"' + cb.name + '\\\"]');
                      if ([...group].some(g => g.checked)) {
                        group.forEach(g => g.classList.remove('invalid-checkbox'));
                      }
                    });
                  });
                });
                </script>

                <style>
                    .invalid-checkbox {
                      outline: 1px solid #B70A0A!important;
                    }
                </style>

            ";

    }

    // check if user has participated
    $has_participated = Database::get()->querySingle("SELECT COUNT(*) AS count FROM poll_user_record WHERE uid = ?d AND pid = ?d", $userDefault, $pid)->count;
    if (($userDefault && $has_participated > 0 && !$is_editor && !$multiple_submissions) or
        ($userDefault && $has_participated > 0 && isset($_GET['onBehalfOfUser']) && !$multiple_submissions)) {
        Session::flash('message',$langPollAlreadyParticipated);
        Session::flash('alert-class', 'alert-warning');
        if (isset($_REQUEST['unit_id'])) {
            redirect_to_home_page('modules/units/index.php?course='.$course_code.'&id='.$_REQUEST['unit_id']);
        } else if (isset($_REQUEST['res_type'])) {
            if (isset($_GET['from_session_view'])) {
                redirect_to_home_page('modules/session/session_space.php?course='.$course_code.'&session='.$_GET['session']);
            }else {
               redirect_to_home_page('modules/wall/index.php?course=' . $course_code);
            }
        } else {
            redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
        }
    }

    $temp_CurrentDate = date("Y-m-d H:i");
    $temp_StartDate = $thePoll->start_date;
    $temp_EndDate = $thePoll->end_date;
    $temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
    $temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
    $temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));
    $temp_IsLime = $thePoll->type == POLL_LIMESURVEY;

    if ($is_editor || ($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {

        $pageName = $thePoll->name;
        if (isset($_REQUEST['unit_id'])) {
            $back_link = "../units/index.php?course=$course_code&amp;id=$_REQUEST[unit_id]";
        } else if (isset($_REQUEST['res_type'])) {
            $back_link = "../wall/index.php?course=$course_code";
        } else {
            $back_link = "index.php?course=$course_code";
        }

        $onBehalfOfUser = '';
        $forSession = '';
        if (isset($_GET['from_session_view']) && isset($_GET['onBehalfOfUser'])) {
            // Before moving on, check if the session belongs to the logged consultant.
            $consultantId = 0;
            if ($is_consultant && !$is_coordinator) {
                $consultantId = $uid;
            }
            $ch = Database::get()->querySingle("SELECT id FROM mod_session WHERE id = ?d
                                                AND course_id = ?d AND creator = ?d", $_GET['session'], $course_id, $consultantId); // only for logged consultant
            if ((!$ch && $is_consultant && !$is_coordinator) or !$is_consultant) {
                Session::flash('message', $langForbidden);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page('modules/session/index.php?course=' . $course_code);
            }

            $onBehalfOfUser = "<input type='hidden' name='onBehalfOfUserId' id='onBehalfOfUserId' value='{$userDefault}'>";
            $forSession = "<input type='hidden' name='forSession' value='$_GET[session]'>";
            $session_participants = Database::get()->queryArray("SELECT mod_session_users.participants,user.givenname,user.surname FROM mod_session_users
                                                                 JOIN user ON mod_session_users.participants = user.id
                                                                 WHERE mod_session_users.session_id = ?d
                                                                 AND mod_session_users.is_accepted = ?d", $_GET['session'], 1);

            $actionPoll = $urlAppend . "modules/units/view.php?course=$course_code&res_type=questionnaire&pid=$pid&UseCase=1&session=$_GET[session]&from_session_view=true&onBehalfOfUser=true&printPollForUser=true";
            $tool_content .= "
                <div class='col-12 mb-4'>
                    <div class='card panelCard card-default px-lg-4 py-lg-3 mb-4'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>$langSubmissionOnBehalfOfUser</h3>
                        </div>
                        <div class='card-body'>
                            <form id='onBehalfOfSelectionForm' method='post' action='$actionPoll'>
                                <select id='onBehalfOfSelection' class='form-select' name='userSelected'>";
                                    $tool_content .= "<option value='0' selected>$langSelect</option>";
                                    foreach ($session_participants as $p) {
                                        $tool_content .= "<option value='{$p->participants}' " . ($userDefault == $p->participants ? 'selected' : '') . ">{$p->givenname}&nbsp;{$p->surname}</option>";
                                    }
            $tool_content .= " </select>
                            </form>";
                            if (isset($_GET['printPollForUser']) && $userDefault > 0) {
                                $tool_content .= "
                                <div class='d-flex justify-content-start align-items-center gap-3 mt-3 flex-wrap'>
                                    $langProcessForFiliInTool
                                    <div>
                                        <div class='spinner-grow text-primary' role='status' style='width:15px; height:15px;'>
                                            <span class='visually-hidden'></span>
                                        </div>
                                        <div class='spinner-grow text-secondary' role='status' style='width:15px; height:15px;'>
                                            <span class='visually-hidden'>.</span>
                                        </div>
                                        <div class='spinner-grow text-success' role='statu' style='width:15px; height:15px;'>
                                            <span class='visually-hidden'></span>
                                        </div>
                                        <div class='spinner-grow text-danger' role='status' style='width:15px; height:15px;'>
                                            <span class='visually-hidden'></span>
                                        </div>
                                    </div>
                                </div>";
                            }
      $tool_content .= "</div>
                    </div>
                </div>";
        }

        if ($thePoll->description) {
            $tool_content .= "<div class='col-12 mb-4'>
                                <div class='card panelCard card-default px-lg-4 py-lg-3 mb-4'>
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
        } else if (isset($_REQUEST['res_type'])) {
            $session_view = '';
            if (isset($_GET['from_session_view'])) {
                $session_view = "&amp;session=$_GET[session]&amp;from_session_view=true";
            }
            $form_link = "../units/view.php?course=$course_code&amp;res_type=questionnaire$session_view";
        } else {
            $form_link = "$_SERVER[SCRIPT_NAME]?course=$course_code";
        }
        if (!$temp_IsLime) {
            $tool_content .= "
            <form class='form-horizontal' role='form' action='$form_link' id='poll' method='post'>
                <fieldset>
                <legend class='mb-0' aria-label='$langForm'></legend>
                $onBehalfOfUser
                $forSession
                <input type='hidden' value='2' name='UseCase'>
                <input type='hidden' value='$pid' name='pid'>";
                if (isset($_REQUEST['unit_id'])) {
                    $tool_content .= "<input type='hidden' value='$_REQUEST[unit_id]' name='unit_id'>";
                }
        }

        //*****************************************************************************
        //      Get answers + questions
        //******************************************************************************/
        $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position ASC", $pid);
        if (!$userDefault && !isset($_GET['printPollForUser'])) {
            $email = Session::has('participantEmail') ? Session::get('participantEmail') : '';
            $email_error = Session::getError('participantEmail') ? " has-error" : "";
            $tool_content .= "
                <div class='card panelCard card-default px-lg-4 py-lg-3 mb-4'>
                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                        <h3>$langPollParticipantInfo</h3>
                    </div>
                    <div class='card-body'>
                        <div class='form-group$email_error'>
                            <label for='participantEmail' class='col-12  control-label-notes'>Email <span class='asterisk Accent-200-cl'>(*)</span></label>
                            <div class='col-12'>
                                <input type='text' name='participantEmail' id='participantEmail' class='form-control' value='$email'>
                                ".(Session::getError('participantEmail') ? "<span class='help-block Accent-200-cl'>" . Session::getError('participantEmail') . "</span>" : "")."
                            </div>
                        </div>
                    </div>
                </div>";
        }

        $pollType = Database::get()->querySingle("SELECT `type` FROM poll WHERE pid = ?d", $pid)->type;
        $pollPagination = Database::get()->querySingle("SELECT `pagination` FROM poll WHERE pid = ?d", $pid)->pagination;
        if (isset($_SESSION["poll_answers_$pid"])) {
            $incomplete_resubmission = isset($_SESSION["poll_answers_$pid"]);
            $incomplete_answers = $_SESSION["poll_answers_$pid"];
            unset($_SESSION["poll_answers_$pid"]);
        }

        $i = 1;
        $page = 1;
        $tool_content .= "<input id='current_page' type='hidden' value='$page'>";
        $tool_content .= "<input id='total_pages' type='hidden' value='".count($questions)."'>";
        $tool_content .= "<input id='paginate_answers' type='hidden' value='$pollPagination'>";
        // Session process
        $user_answers_session = false;
        $sql_an = '';
        $s_id = $_GET['session'] ?? 0;
        if (isset($s_id) && $s_id > 0 && isset($_GET['from_session_view'])) {
            $user_answers_session = true;
        }
        //if ($user_answers_session) {
            $sql_an = "AND b.session_id = $s_id";
        //}

        foreach ($questions as $theQuestion) {
            if ($temp_IsLime) {
                break;
            }
            $pqid = $theQuestion->pqid;
            $qtype = $theQuestion->qtype;
            $q_description = $theQuestion->description;
            if (isset($incomplete_resubmission) and !isset($incomplete_answers[$pqid])) {
                $incomplete_answers[$pqid] = [];
            }
            $user_answers = null;
            if ($qtype == QTYPE_LABEL) {
                $tool_content .= "
                <div class='col-12'>
                   <div class='alert alert-info m-0 TextBold text-center mb-4'>" . standard_text_escape($theQuestion->question_text) . "</div>
                </div>";
            } else {
                $page_number = (isset($pollPagination) && $pollPagination == 1) ? "id=q_page_$page" : "";
                $tool_content .= "
                <div $page_number class='col-12' style='display: block;'>
                    <div class='card panelCard px-lg-4 py-lg-3 h-100 panelCard-questionnaire poll-panel mb-4'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>$langQuestion $i</h3>
                        </div>
                        <div class='card-body'>";
                            $tool_content .= "<p class='TextMedium Neutral-900-cl mb-2'>".q_math($theQuestion->question_text)."</p>";
                                            if(!empty($q_description)){
                                                $tool_content .= "<div class='col-12 my-4'>$q_description</div>";
                                            }
                            $tool_content .= "<input type='hidden' name='question[$pqid]' value='$qtype'>";
                            if ($qtype == QTYPE_SINGLE || $qtype == QTYPE_MULTIPLE) {
                                if ($multiple_submissions) { // get user answers (if any)
                                    $user_answers = Database::get()->queryArray("SELECT a.aid
                                            FROM poll_user_record b, poll_answer_record a
                                            LEFT JOIN poll_question_answer c
                                                ON a.aid = c.pqaid
                                            WHERE a.poll_user_record_id = b.id
                                                AND a.qid = ?d
                                                AND b.uid = ?d
                                                $sql_an", $pqid, $userDefault);
                                } elseif (isset($incomplete_resubmission)) {
                                    $user_answers = $incomplete_answers[$pqid];
                                }
                                $answers = Database::get()->queryArray("SELECT * FROM poll_question_answer
                                            WHERE pqid = ?d ORDER BY pqaid", $pqid);
                                $name_ext = ($qtype == QTYPE_SINGLE)? '': '[]';
                                $type_attr = ($qtype == QTYPE_SINGLE)? "radio": "checkbox";
                                $class_type_attr = ($qtype == QTYPE_SINGLE)? "radio-label": "label-container";
                                $checkMark_class = ($qtype == QTYPE_SINGLE)? "": "<span class='checkmark'></span>";
                                if ($qtype == QTYPE_MULTIPLE) {
                                    $tool_content .= "<input type='hidden' name='answer[$pqid]' value='-1'>";
                                }
                                foreach ($answers as $theAnswer) {
                                        $checked = '';
                                    if ($user_answers) {
                                        if (count($user_answers) > 1) { // multiple answers
                                            foreach ($user_answers as $ua) {
                                                if ($ua->aid == $theAnswer->pqaid) {
                                                    $checked = 'checked';
                                                }
                                            }
                                        } else {
                                            if (count($user_answers) == 1) { // single answer
                                                if ($user_answers[0]->aid == $theAnswer->pqaid) {
                                                    $checked = 'checked';
                                                }
                                            }
                                        }
                                    }
                                    $tool_content .= "
                                        <div class='form-group'>
                                            <div class='col-sm-offset-1 col-sm-11'>
                                                <div class='$type_attr QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                                    <label class='$class_type_attr' aria-label='$langSelect'>
                                                        <input type='$type_attr' name='answer[$pqid]$name_ext' value='$theAnswer->pqaid' $checked>
                                                        $checkMark_class
                                                        ".q_math($theAnswer->answer_text)."
                                                    </label>
                                                </div>
                                            </div>
                                        </div>";
                                }
                                if ($qtype == QTYPE_SINGLE && $default_answer) {
                                    $tool_content .= "
                                        <div class='form-group'>
                                            <div class='col-sm-offset-1 col-sm-11'>
                                                <div class='$type_attr QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                                    <label class='$class_type_attr'>
                                                        <input type='$type_attr' name='answer[$pqid]' value='-1'>
                                                        $checkMark_class
                                                        $langPollUnknown
                                                    </label>
                                                </div>
                                            </div>
                                        </div>";
                                }

                                $tool_content .= "<div class='col-12 d-flex justify-content-end align-items-center mt-4'>
                                                    <a id='{$qtype}_{$pqid}' class='btn deleteAdminBtn clearUpBtn gap-1'><i class='fa-regular fa-trash-can'></i>$langCleanup</a>
                                                  </div>";
                            } elseif ($qtype == QTYPE_SCALE) {
                                $slider_value = 0;
                                if ($multiple_submissions) { // get user answers (if any)
                                    $user_answers = Database::get()->querySingle("SELECT a.answer_text
                                                        FROM poll_answer_record a, poll_user_record b
                                                    WHERE qid = ?d
                                                        AND a.poll_user_record_id = b.id
                                                        AND b.uid = ?d
                                                        $sql_an", $pqid, $userDefault);
                                    if ($user_answers) {
                                        $slider_value = $user_answers->answer_text;
                                    }
                                } elseif (isset($incomplete_resubmission) and $incomplete_answers[$pqid]) {
                                    $slider_value = $incomplete_answers[$pqid][0]->answer_text;
                                }
                                if (($pollType == POLL_COLLES) or ($pollType == POLL_ATTLS)) {
                                    $tool_content .= "<div style='margin-bottom: 0.5em;'><small>".q($langCollesLegend)."</small></div>";
                                }


                                // For scaling labels
                                $countAnsScales = 0;
                                $ansAsString = "";
                                if (!empty($theQuestion->answer_scale)) {
                                    $arrAnswerScale = explode('|', $theQuestion->answer_scale);
                                    $countAnsScales = count($arrAnswerScale);
                                    $ansAsString = implode(",",$arrAnswerScale);
                                }

                                $tool_content .= "
                                     <div class='form-group px-4 mb-5'>
                                        <input aria-label='$langAnswer' name='answer[$pqid]' class='grade_bar' data-answers='$countAnsScales' data-txt-answers='$ansAsString' data-slider-id='ex1Slider' type='text' data-slider-min='1' data-slider-max='$theQuestion->q_scale' data-slider-step='1' data-slider-value='$slider_value'>
                                    </div>";
                            } elseif ($qtype == QTYPE_FILL) {
                                $text = '';
                                if ($multiple_submissions) { // get user answers (if any)
                                    $user_answers = Database::get()->querySingle("SELECT a.answer_text
                                                        FROM poll_answer_record a, poll_user_record b
                                                    WHERE qid = ?d
                                                        AND a.poll_user_record_id = b.id
                                                        AND b.uid = ?d
                                                        $sql_an", $pqid, $userDefault);
                                    if ($user_answers) {
                                        $text = $user_answers->answer_text;
                                    }
                                } elseif (isset($incomplete_resubmission) and $incomplete_answers[$pqid]) {
                                    $text = $incomplete_answers[$pqid][0]->answer_text;
                                }
                                $tool_content .= "
                                    <div class='form-group margin-bottom-fat'>
                                        <div class='col-sm-12 margin-top-thin QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                            <textarea class='form-control' name='answer[$pqid]' aria-label='$langTypeOutMessage'>$text</textarea>
                                        </div>
                                    </div>";

                                    $tool_content .= "<div class='col-12 d-flex justify-content-end align-items-center mt-4'>
                                                            <a id='{$qtype}_{$pqid}' class='btn deleteAdminBtn clearUpBtn gap-1'><i class='fa-regular fa-trash-can'></i>$langCleanup</a>
                                                        </div>";
                            } elseif ($qtype == QTYPE_TABLE) {

                                $q_rows = Database::get()->querySingle("SELECT q_row FROM poll_question WHERE pqid = ?d", $pqid)->q_row;
                                if ($q_rows > 0) {
                                    $user_questions = Database::get()->queryArray("SELECT answer_text,sub_question FROM poll_question_answer
                                                                                    WHERE pqid = ?d", $pqid);


                                    if (count($user_questions)>0) {
                                        $sub_question_arr = [];
                                        foreach ($user_questions as $uq) {
                                            $sub_question_arr[] = $uq->sub_question;
                                        }
                                        $tool_content .= "
                                            <div class='table-responsive'>
                                                <table class='table-default QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                                    <thead>
                                                        <tr>";
                                                            foreach ($user_questions as $q) {
                                                                $tool_content .= "<th style='min-width:250px;'><p>" . q($q->answer_text) . "</p></th>";
                                                            }
                                    $tool_content .= "</tr>
                                                    </thead>
                                                    <tbody>";
                                                        $ansCounter = 0;
                                                        for ($r=0; $r<$q_rows; $r++) {
                                                            $tool_content .= "<tr>";
                                                                for ($t=0; $t<count($user_questions); $t++) {
                                                                    $val_row = $r+1;
                                                                    $val_col = $sub_question_arr[$t];
                                                                    $ansCounter = $ansCounter+1;
                                                                    $text = '';
                                                                    if ($multiple_submissions) { // get user answers (if any)
                                                                        $user_q = Database::get()->querySingle("SELECT a.answer_text
                                                                                                                FROM poll_answer_record a, poll_user_record b
                                                                                                                WHERE qid = ?d
                                                                                                                AND a.poll_user_record_id = b.id
                                                                                                                AND b.uid = ?d
                                                                                                                AND a.sub_qid = ?d
                                                                                                                AND a.sub_qid_row = ?d
                                                                                                                $sql_an", $pqid, $userDefault, $val_col, $val_row);
                                                                        if ($user_q) {
                                                                            $text = $user_q->answer_text;
                                                                        }
                                                                    }
                                                                    $tool_content .= "<td>
                                                                                        <input type='hidden' name='q_row_col[]' value='$pqid,$val_row,$val_col,$ansCounter'>
                                                                                        <textarea class='form-control textarea-qtable' name='answer[$pqid][$ansCounter]' aria-label='$langTypeOutMessage'>$text</textarea>
                                                                                    </td>";
                                                                }
                                                            $tool_content .= "</tr>";
                                                        }
                                $tool_content .= "</tbody>
                                                </table>
                                            </div>
                                        ";
                                    }

                                    $tool_content .= "<div class='col-12 d-flex justify-content-end align-items-center mt-4'>
                                                            <a id='{$qtype}_{$pqid}' class='btn deleteAdminBtn clearUpBtn gap-1'><i class='fa-regular fa-trash-can'></i>$langCleanup</a>
                                                        </div>";
                                }
                            }
                $tool_content .= "
                        </div>
                    </div>
                </div>";
                $i++;
            }

            $page++;
        }


        // next - prev btn
        if (!$temp_IsLime && isset($pollPagination) && $pollPagination == 1) {
        $tool_content .= "<div class='col-12 d-flex justify-content-between align-items-center gap-3'>
                            <button id='prevBtn' class='btn submitAdminBtn'><i class='fa-solid fa-chevron-left'></i>$langPreviousQuestion</button>
                            <button id='nextBtn' class='btn submitAdminBtn'>$langNextQuestion<i class='fa-solid fa-chevron-right'></i></button>
                          </div>";
        }


        if ($temp_IsLime) {
            show_limesurvey_integration($thePoll);
        }
        if ($multiple_submissions) {
            $tool_content .= "<input type='hidden' value='1' name='update'>";
        }

        $tool_content .= "<div class='col-12 d-flex justify-content-center mt-5'>";
        if ($is_editor && !isset($_GET['onBehalfOfUser'])) {
            if (isset($_GET['from_session_view'])) {
                $tool_content .= "<a class='btn cancelAdminBtn' href='../session/session_space.php?course=$course_code&amp;session=$_GET[session]'>" . q($langBack) . "</a>";
            } else {
                $tool_content .= "<a class='btn cancelAdminBtn' href='index.php?course=$course_code'>" . q($langBack) . "</a>";
            }
        } else if ($is_course_reviewer) {
            // is poll assigned to course_reviewer?
            $query = "SELECT * FROM poll WHERE pid = ?d";
            $query_params[] = $pid;
            $gids = user_group_info($uid, $course_id);
            if (!empty($gids)) {
                $gids_sql_ready = implode(',',array_keys($gids));
            } else {
                $gids_sql_ready = "''";
            }
            $query .= " AND (assign_to_specific != '0' AND pid IN
                   (SELECT poll_id FROM poll_to_specific WHERE user_id = ?d UNION SELECT poll_id FROM poll_to_specific WHERE group_id IN ($gids_sql_ready))
                )";
            $query_params[] = $uid;
            $result = Database::get()->queryArray($query, $query_params);
            if (count($result) > 0) {
                $tool_content .= "<input class='btn submitAdminBtn' name='submit' type='submit' value='" . q($langSubmit) . "'>";
            } else {
                $tool_content .= "<a class='btn cancelAdminBtn' href='index.php?course=$course_code'>" . q($langBack) . "</a>";
            }
        } else {
            if (!$temp_IsLime) {
                $tool_content .= "<input class='btn submitAdminBtn' name='submit' type='submit' value='" . q($langSubmit) . "'>";
                if (isset($_REQUEST['unit_id'])) {
                    $tool_content .= "<a class='btn cancelAdminBtn ms-3' href='../units/index.php?course=$course_code&amp;id=$_REQUEST[unit_id]'>" . q($langCancel) . "</a>";
                } else {
                    if (isset($_GET['from_session_view'])) {
                        $tool_content .= "<a class='btn cancelAdminBtn ms-3' href='../session/session_space.php?course=$course_code&amp;session=$_GET[session]'>" . q($langCancel). "</a>";
                    } else {
                        $tool_content .= "<a class='btn cancelAdminBtn ms-3' href='index.php?course=$course_code'>" . q($langCancel) . "</a>";
                    }
                }
            }
        }
        $tool_content .= "</div>";
        if (!$temp_IsLime) {
            $tool_content .= "</fieldset>
                </form>
                <script>
                    $(function () {
                        $('#poll').on('submit', function () {
                            $(this).find('input[type=submit]').prop('disabled', true);
                            return true;
                        });
                    });
                </script>";
        }
    } else {
        Session::flash('message',$langPollInactive);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
}

/**
 * @brief limesurvey integration
 * @param $thePoll
 */
function show_limesurvey_integration($thePoll) {
    global $tool_content, $course_id, $course_code, $langLimesurveyIntegration, $urlAppend, $uid;

    $lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d", $thePoll->lti_template);
    $_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$thePoll->pid.'_COURSE_ID'] = $course_id;
    $_SESSION['POLL_POST_LAUNCH_'.$uid.'_'.$thePoll->pid.'_COURSE_CODE'] = $course_code;

    if ($thePoll->launchcontainer == LTI_LAUNCHCONTAINER_EMBED) {
        $tool_content .= '<iframe id="contentframe"
            src="' . $urlAppend . "modules/questionnaire/post_launch.php?course=" . $course_code . "&amp;pid=" . $thePoll->pid . '"
            webkitallowfullscreen=""
            mozallowfullscreen=""
            allowfullscreen=""
            width="100%"
            height="800px"
            style="border: 1px solid #ddd; border-radius: 4px;"></iframe>';
    } else {
        $joinLink = create_join_button(
            $lti->lti_provider_url,
            $lti->lti_provider_key,
            $lti->lti_provider_secret,
            $thePoll->pid,
            RESOURCE_LINK_TYPE_POLL,
            $thePoll->name,
            $thePoll->description,
            $thePoll->launchcontainer,
            $langLimesurveyIntegration . ":&nbsp;&nbsp;"
        );

        $tool_content .= "<div class='form-wrapper'>" . $joinLink . "</div>";
    }
}

/**
 * @brief submit poll
 */
function submitPoll() {
    global $tool_content, $course_code, $uid, $langPollSubmitted, $langBack, $langQFillInAllQs,
           $langUsage, $langTheField, $langFormErrors, $urlServer, $langPollParticipateConfirm,
           $langPollEmailUsed, $langPollParticipateConfirmation, $course_id, $pid, $langChooseAUser;

    $unit_id = isset($_REQUEST['unit_id'])? intval($_REQUEST['unit_id']): null;
    $poll = Database::get()->querySingle("SELECT * FROM poll WHERE pid = ?d", $pid);
    $default_answer = $poll->default_answer;
    $is_complete = true;
    $v = new Valitron\Validator($_POST);
    $atleast_one_answer = false;
    // Session process
    $sql_u = '';
    $s_id = $_GET['session'] ?? 0;
    //if (isset($_GET['from_session_view'])) {
        $sql_u = "AND session_id = $s_id";
    //}
    if ($poll->require_answer) {
        $atleast_one_answer = true;
    }

    if (isset($_POST['onBehalfOfUserId'])) {
        $isAllowed = true;
        $users_arr = [];
        $users_p = Database::get()->queryArray("SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = ?d", $_POST['forSession'], 1);
        foreach ($users_p as $p) {
            $users_arr[] = $p->participants;
         }
        if ($_POST['onBehalfOfUserId'] < 1 or !in_array($_POST['onBehalfOfUserId'], $users_arr)) {
            $isAllowed = false;
        }
        if (!$isAllowed) {
            Session::flash('message', $langChooseAUser);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/questionnaire/pollparticipate.php?course=$course_code&UseCase=1&pid=$pid&session=$_POST[forSession]&from_session_view=true&onBehalfOfUser=true");
        }
    }

    if (!$uid) {
        $v->addRule('unique', function($field, $value, array $params) use ($pid){
            return !Database::get()->querySingle("SELECT COUNT(*) AS count FROM poll_user_record WHERE email = ?s AND pid = ?d", $value, $pid)->count;
        }, $langPollEmailUsed);
        $v->rule('required', array('participantEmail'));
        $v->rule('email', array('participantEmail'));
        $v->rule('unique', array('participantEmail'));
        $v->labels(array('participantEmail' => "$langTheField Email"));
    }
    if ($v->validate()) {
        // first populate poll_answer
        $CreationDate = date("Y-m-d H:i");
        $answer = isset($_POST['answer'])? $_POST['answer']: array();
        $userDefault = $_POST['onBehalfOfUserId'] ?? $uid;

        if ($userDefault) {
            $eventData = new stdClass();
            $eventData->courseId = $course_id;
            $eventData->uid = $userDefault;
            $eventData->activityType = ViewingEvent::QUESTIONNAIRE_ACTIVITY;
            $eventData->module = MODULE_ID_QUESTIONNAIRE;
            $eventData->resource = intval($pid);
            ViewingEvent::trigger(ViewingEvent::NEWVIEW, $eventData);

            if (isset($_REQUEST['update'])) { // if poll has enabled multiple submissions first delete the previous answers
                Database::get()->query("DELETE FROM poll_answer_record WHERE poll_user_record_id IN (SELECT id FROM poll_user_record WHERE uid = ?d AND pid = ?d $sql_u)", $userDefault, $pid);
                Database::get()->query("DELETE FROM poll_user_record WHERE uid = ?d AND pid = ?d $sql_u", $userDefault, $pid);
            }
            $user_record_id = Database::get()->query("INSERT INTO poll_user_record (pid, uid, session_id) VALUES (?d, ?d, ?d)", $pid, $userDefault, $s_id)->lastInsertID;
        } else {
            require_once 'include/sendMail.inc.php';
            $participantEmail = $_POST['participantEmail'];
            $verification_code = randomkeys(255);
            $user_record_id = Database::get()->query("INSERT INTO poll_user_record (pid, uid, email, email_verification, verification_code, session_id) VALUES (?d, ?d, ?s, ?d, ?s, ?d)", $pid, $userDefault, $participantEmail, 0, $verification_code, $s_id)->lastInsertID;
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
                if (is_array($answer[$pqid])){
                    foreach ($answer[$pqid] as $aid) {
                        $aid = intval($aid);
                        Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date)
                            VALUES (?d, ?d, ?d, '', NOW())", $user_record_id, $pqid, $aid);
                    }
                } else {
                    if (!$atleast_one_answer) {
                        continue;
                    }
                    $aid = -1;
                    Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date)
                        VALUES (?d, ?d, ?d, '', NOW())", $user_record_id, $pqid, $aid);
                }
                continue;
            } elseif ($qtype == QTYPE_SCALE) {
                $aid = 0;
                $answer_text = $answer[$pqid];
            } elseif ($qtype == QTYPE_SINGLE) {
                if (isset($answer[$pqid])) {
                    $aid = intval($answer[$pqid]);
                } else {
                    if (!$default_answer && !$atleast_one_answer) {
                        continue;
                    }
                    if (!$default_answer && $atleast_one_answer) {
                        $is_complete = false;
                    }
                    $aid = -1;
                }
                $answer_text = '';
            } elseif ($qtype == QTYPE_FILL) {
                $_SESSION['q_answer'] = $answer;
                $answer_text = trim($answer[$pqid]);
                if ($answer_text === '' and !$default_answer and !$atleast_one_answer) {
                    continue;
                }
                if ($answer_text === '' and !$default_answer and $atleast_one_answer) {
                    $is_complete = false;
                }
                $aid = 0;
            } elseif ($qtype == QTYPE_TABLE) {
                $aid = 0;
                $arr_answers = $_POST['answer'];
                $empty_answers = 0;
                foreach ($_POST['q_row_col'] as $q_an) {
                    $arr_tmp = explode(',',$q_an);
                    $pqid_tmp = $arr_tmp[0];
                    $q_row = $arr_tmp[1];
                    $q_col = $arr_tmp[2];
                    $answerCounter = $arr_tmp[3];
                    if (!empty($arr_answers[$pqid][$answerCounter]) && $pqid == $pqid_tmp) {
                        $empty_answers++;
                        $answer_text = $arr_answers[$pqid][$answerCounter];
                        Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date, sub_qid, sub_qid_row)
                                                VALUES (?d, ?d, ?d, ?s, ?t, ?d, ?d)", $user_record_id, $pqid_tmp, $aid, $answer_text, $CreationDate, $q_col, $q_row);
                    }
                }
                if($atleast_one_answer && $empty_answers == 0){
                    $is_complete = false;
                }
            } else {
                continue;
            }

            if ($qtype != QTYPE_TABLE) {
                Database::get()->query("INSERT INTO poll_answer_record (poll_user_record_id, qid, aid, answer_text, submit_date)
                            VALUES (?d, ?d, ?d, ?s, ?t)", $user_record_id, $pqid, $aid, $answer_text, $CreationDate);
            }
        }

        if (!$is_complete) {
            $user_answers = Database::get()->queryArray('SELECT * FROM poll_answer_record
                WHERE poll_user_record_id = ?d', $user_record_id);
            $session_answers = [];
            foreach ($user_answers as $answer) {
                if (isset($session_answers[$answer->qid])) {
                    $session_answers[$answer->qid][] = $answer;
                } else {
                    $session_answers[$answer->qid] = [$answer];
                }
            }
            $_SESSION["poll_answers_$pid"] = $session_answers;
//            Database::get()->query('DELETE FROM poll_answer_record WHERE poll_user_record_id = ?d', $user_record_id);
//            Database::get()->query('DELETE FROM poll_user_record WHERE id = ?d', $user_record_id);
            Session::flash('message', $langQFillInAllQs);
            Session::flash('alert-class', 'alert-warning');
            if ($unit_id) {
                redirect_to_home_page("modules/units/view.php?course=$course_code&res_type=questionnaire&UseCase=1&pid=$pid");
            } elseif (isset($_GET['from_session_view'])) {
                redirect_to_home_page("modules/units/view.php?course=$course_code&res_type=questionnaire&UseCase=1&pid=$pid&session=$_GET[session]&from_session_view=true");
            } else {
                redirect_to_home_page("modules/questionnaire/pollparticipate.php?course=$course_code&UseCase=1&pid=$pid");
            }
        }

        Log::record($course_id, MODULE_ID_QUESTIONNAIRE, LOG_INSERT,
            array('legend' => 'submit_answers',
                'title' => $poll->name
            )
        );
        $end_message = Database::get()->querySingle("SELECT end_message FROM poll WHERE pid = ?d", $pid)->end_message;
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>".$langPollSubmitted."</span></div></div>";
        if ($poll->end_message) {
            $tool_content .=  $end_message;
        }
        $tool_content .= "<br><div class='d-flex text-center'>";
        if (isset($_REQUEST['unit_id'])) {
            $tool_content .= "<a class='btn cancelAdminBtn' href='../units/index.php?course=$course_code&amp;id=$_REQUEST[unit_id]'>$langBack</a>";
        } else if (isset($_REQUEST['res_type'])) {
            if (isset($_GET['from_session_view'])) {
                $tool_content .= "<a class='btn btn-primary' href='../session/session_space.php?course=$course_code&amp;session=$_GET[session]'>$langBack</a>";
            } else {
                $tool_content .= "<a class='btn btn-primary' href='../wall/index.php?course=$course_code'>$langBack</a>";
            }
        } else {
            $tool_content .= "<a class='btn cancelAdminBtn' href='index.php?course=$course_code'>$langBack</a>";
        }
        if ($poll->show_results) {
            if (isset($_REQUEST['unit_id'])) {
                $tool_content .= "<a class='btn submitAdminBtn ms-3' href='../units/view.php?course=$course_code&amp;res_type=questionnaire_results&amp;unit_id=$_REQUEST[unit_id]&amp;pid=$pid'>$langUsage</a>";
            } else if (isset($_REQUEST['res_type'])) {
                if (isset($_GET['from_session_view'])) {
                    $tool_content .= "<a class='btn btn-primary ms-3' href='../questionnaire/pollresults.php?course=$course_code&session=$_GET[session]&pid=$pid&from_session_view=true'>$langUsage<a>";
                } else {
                    $tool_content .= "<a class='btn btn-primary' href='../wall/index.php?course=$course_code'>$langUsage</a>";
                }
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
