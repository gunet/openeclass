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
require_once 'include/lib/fileUploadLib.inc.php';

load_js('bootstrap-slider');
load_js('bootstrap-datetimepicker');
load_js('bootstrap-datepicker');

$toolName = $langQuestionnaire;

$head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('.datetimeAnswer').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
            $('.dateAnswer').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>
";

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
        if (isset($_POST['action']) && $_POST['action'] == 'refreshSession') {
            // Does nothing just refreshes the session
            exit();
        }

        // Delete user answer from session data for the specific question
        if (isset($_POST['clean_question_id']) or isset($_POST['clean_sub_question_id'])) {
            $question_id = intval($_POST['clean_question_id']);
            $squestion_id = intval($_POST['clean_sub_question_id']);
            unset($_SESSION['data_answers'][$question_id]);
            unset($_SESSION['data_answers'][$squestion_id]);
            exit();
        }

        // Store user answers into session data for all questions
        if (isset($_POST['data_answers'])) {
            foreach ($_POST['data_answers'] as $question_key => $data) {
                unset($_SESSION['data_answers'][$question_key]);
                $_SESSION['data_answers'][$question_key] = $data;
            }
            exit();
        }

        // File has been uploaded from uppy
        if (isset($_POST['file_uploaded'])) {
            header('Content-Type: application/json');
            $questionID = $_POST['question_id'];
            $docInfo = ['filename' => $_POST['file_name'], 'filepath' => $_POST['file_path']];
            $_SESSION['data_answers'][$questionID] = serialize($docInfo);
            $_SESSION['data_file_answer'][$questionID] = serialize($docInfo);
            echo json_encode(['upload_success' => true]);
            exit();
        }

        // File has been removed from uppy
        if (isset($_POST['file_removed'])) {
            if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();

            $questionID = $_POST['question_id'];
            unset($_SESSION['data_answers'][$questionID]);
            unset($_SESSION['data_file_answer'][$questionID]);
            
            $c = $_GET['course'];
            $s = $_GET['session'];
            $currentUser = $_POST['current_user'];
            $pollId = intval($_GET['pid']);
            $fpath = $_POST['fPath'];
            $file = "$webDir/courses/$c/poll_$pollId/$currentUser/$questionID/$s$fpath";
            if (file_exists($file)) {
                unlink($file);
                Database::get()->query("DELETE poll_answer_record FROM poll_answer_record
                                        INNER JOIN poll_user_record ON poll_user_record.id=poll_answer_record.poll_user_record_id
                                        WHERE poll_answer_record.qid = ?d
                                        AND poll_user_record.uid = ?d
                                        AND poll_user_record.pid = ?d
                                        AND poll_user_record.session_id = ?d", $questionID, $currentUser, $pollId, $s);
            } 
            exit();
        }
}

// Save uploaded file from uppy - only for users
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['answer'])) {
    if (!isset($_GET['token']) || !validate_csrf_token($_GET['token'])) csrf_token_error();

    // Na mhn anevasei o editor diko tou file . Mono ean einai ek merous tou xrhsth apo sunedria
    if (isset($_GET['behalf_of_user_mode']) && $_GET['behalf_of_user_mode'] == 0 && ($is_editor or $is_consultant)) {
        forbidden();
    }
    // Na mhn anevasei o editor diko tou file an den exei epilexei xrhsth
    if (isset($_GET['behalf_of_user_mode']) && $_GET['behalf_of_user_mode'] == 1 && isset($_GET['u']) && $_GET['u'] == 0) {
        forbidden();
    }

    header('Content-Type: application/json');
    
    $pid = intval($_GET['pid']);
    $qid = intval($_GET['qid']);
    $sid = intval($_GET['session']);
    $currentUser = intval($_GET['u']);
    $filename = $_FILES['answer']['name'];
    validateUploadedFile($filename); // check file type
    $filename = add_ext_on_mime($filename);
    // File name used in file system and path field
    $safe_filename = safe_filename(get_file_extension($filename));
    $dir = "$webDir/courses/$course_code/poll_$pid/$currentUser/$qid/$sid/";
    if (!file_exists($dir)) {
        mkdir("$webDir/courses/$course_code/poll_$pid/$currentUser/$qid/$sid/", 0755, true);
    } else {// delete prev file
        $folder = "$webDir/courses/$course_code/poll_$pid/$currentUser/$qid/$sid";
        if (is_dir($folder)) {
            $files = scandir($folder);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $folder . DIRECTORY_SEPARATOR . $file;
                    if (is_file($filePath)) {
                        unlink($filePath); // Delete the file
                    }
                }
            }
        }
    }
    $pathfile = "$webDir/courses/$course_code/poll_$pid/$currentUser/$qid/$sid/$safe_filename";
    if (move_uploaded_file($_FILES['answer']['tmp_name'], $pathfile)) {
        @chmod($pathfile, 0644);
        $real_filename = $_FILES['answer']['name'];
        $filepath = '/' . $safe_filename;
        $info_file = pathinfo($filename);
        echo json_encode(['success' => true, 'fileInfo' => $info_file, 'filePath' => $filepath]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
    }
    exit();
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
if (!$p && !$is_consultant) { // check poll access
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
    $is_consultant, $is_coordinator, $langSubmissionOnBehalfOfUser, $urlAppend, $langProcessForFiliInTool,
    $urlServer, $langRequireAnswer;

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
                var sSubq = $(this).attr('data-sub-question');
                if(typeQ == 1) {
                    var classQ = '.QuestionType_'+typeQ+'.QuestionNumber_'+numberQ+' input[type=radio]';
                    $(classQ).prop('checked', false);
                    if (sSubq > 0) { // clear the subquestion
                        // single type
                        var classSubQ = '.QuestionType_1.QuestionNumber_'+sSubq+' input[type=radio]';
                        $(classSubQ).prop('checked', false);

                        // multiple type
                        var classSubQMultiple = '.QuestionType_3.QuestionNumber_'+sSubq+' input[type=checkbox]';
                        $(classSubQMultiple).prop('checked', false);

                        // free text type
                        var classSubQFrText = '.QuestionType_2.QuestionNumber_'+sSubq+' textarea';
                        $(classSubQFrText).val('');
                    }
                } else if(typeQ == 2) {
                    var classQ = '.QuestionType_'+typeQ+'.QuestionNumber_'+numberQ+' textarea';
                    $(classQ).val('');
                } else if(typeQ == 3) {
                    var classQ = '.QuestionType_'+typeQ+'.QuestionNumber_'+numberQ+' input[type=checkbox]';
                    $(classQ).prop('checked', false);
                } else if(typeQ == 6) {
                    var classQ = '.QuestionType_'+typeQ+'.QuestionNumber_'+numberQ+' textarea';
                    $(classQ).val('');
                } else if(typeQ == 7) {
                    $('#dateTimeAnswer_'+numberQ).val('');
                } else if (typeQ == 8) {
                    $('#shortAnswer_'+numberQ).val('');
                } else if(typeQ == 10) {
                    $('#dateAnswer_'+numberQ).val('');
                }
                // Clean question from session data
                var qQuestion = $(this).attr('data-question-clean');
                $.ajax({
                    url: '$_SERVER[SCRIPT_NAME]?course=$course_code&UseCase=1&pid=$pid',
                    method: 'POST',
                    data: { clean_question_id: qQuestion, clean_sub_question_id: sSubq },
                });
            });
        });
    </script>
    
    <script>
        $(function() {
            function save_data(callback) {
                var obj = {};
                var checkBoxesVal = [];
                document.querySelectorAll('[data-question-type]').forEach(function(elem) {
                    if (elem) {
                        var qType = $(elem).attr('data-question-type');
                        var name = elem.name;
                        const foundNumbers = name.match(/\d+/g);
                        if (qType == 1 || qType == 2 || qType == 5 || qType == 7 || qType == 8 || qType == 10) { // radio or single text or scale or datetime or short answer
                            if (foundNumbers.length == 1 && elem.value != '') {
                                var qRow = foundNumbers[0];
                                if (!obj[qRow]) {
                                    obj[qRow] = {};
                                }
                                if (qType == 1 && elem.checked) {
                                    obj[qRow] = elem.value;
                                } else if (qType == 2 || qType == 5 || qType == 7 || qType == 8 || qType == 10) {
                                    obj[qRow] = elem.value;
                                }
                            }
                        } else if (qType == 3) { // checkboxes multiple answer
                            if (foundNumbers.length == 1 && elem.checked) {
                                var qRow = foundNumbers[0];
                                if (!obj[qRow]) {
                                    obj[qRow] = {};
                                }
                                checkBoxesVal.push(elem.value);
                                const result = checkBoxesVal.join(',');
                                obj[qRow] = result;
                            }
                        } else if (qType == 6) { // table
                            if (foundNumbers.length == 2 && elem.value != '') {
                                var qRow = foundNumbers[0];
                                var qCol = foundNumbers[1];
                                if (!obj[qRow]) {
                                    obj[qRow] = {};
                                }
                                obj[qRow][qCol] = elem.value;
                            }
                        }
                    }
                });
                // Send data via AJAX
                $.ajax({
                    url: '$_SERVER[SCRIPT_NAME]?course=$course_code&UseCase=1&pid=$pid',
                    method: 'POST',
                    data: { data_answers: obj },
                    success: function(response) {
                        if (callback) callback();
                    },
                    error: function() {
                        // handle error if needed
                        if (callback) callback();
                    }
                });
            }

            $('#prevBtn').on('click', function(e) {
                e.preventDefault();
                var prevLink = $('#linkPrevPage').val();
                save_data(function() {
                    window.location.href = prevLink;
                });
            });
            $('#nextBtn').on('click', function(e) {
                e.preventDefault();
                var nextLink = $('#linkNextPage').val();
                save_data(function() {
                    window.location.href = nextLink;
                });
            });
        });
    </script>
    
    
    <script>
        $(function() {
            $('.single_type_answer').change(function() {
                var mainQ = $(this).attr('data-main-question');
                var valId = $(this).val();
                $('.sub_question_temp_'+mainQ).removeClass('d-block').addClass('d-none');
                $('.sub_question_'+valId).removeClass('d-none').addClass('d-block');
            });
        });
    </script>
    
    
    ";

    // If a consultant is on behalf of user mode for submitting poll.
    if (isset($_POST['userSelected'])) {
        $_SESSION['onBehalfOfUserId'] = intval($_POST['userSelected']);
        unset($_SESSION['loop_init_answers_session']);
    }
    $userDefault = $_SESSION['onBehalfOfUserId'] ?? $uid;

    $pid = $_REQUEST['pid'];
    //      Get poll data
    $thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid",$course_id, $pid);
    $multiple_submissions = $thePoll->multiple_submissions;
    $default_answer = $thePoll->default_answer;

    // if ($thePoll->require_answer) {
    //     $head_content .= "            
    //         <script>
    //             document.addEventListener('DOMContentLoaded', () => {
    //                 const form = document.getElementById('poll');
    //                     form.querySelectorAll('input, textarea').forEach(el => {
    //                         if (['radio', 'textarea'].includes(el.type) || el.tagName === 'TEXTAREA') {
    //                             if (el.tagName === 'TEXTAREA' && el.classList.contains('textarea-qtable')) {
    //                                 // Skip adding required to textarea with class 'textarea-qtable'
    //                                 return;
    //                             }
    //                             el.required = true;
    //                         }
    //                     });
    //             });
                
    //             document.addEventListener('DOMContentLoaded', () => {
    //               const form = document.getElementById('poll');
    //               if (!form) return;
                
    //               form.addEventListener('submit', e => {                      
    //                 let valid = true;
    //                 const checkboxGroups = {};
    //                 form.querySelectorAll('input[type=\\\"checkbox\\\"]').forEach(cb => {
    //                   const name = cb.name;
    //                   if (!checkboxGroups[name]) checkboxGroups[name] = [];
    //                   checkboxGroups[name].push(cb);
    //                 });
                
    //                 for (const boxes of Object.values(checkboxGroups)) {
    //                   if (!boxes.some(cb => cb.checked)) {
    //                     valid = false;
    //                     boxes.forEach(cb => cb.classList.add('invalid-checkbox'));
    //                   } else {
    //                     boxes.forEach(cb => cb.classList.remove('invalid-checkbox'));
    //                   }
    //                 }
                    
    //                 if (!valid) {
    //                     e.preventDefault();
    //                 }
    //               });
                
    //               form.querySelectorAll('input[type=\\\"checkbox\\\"]').forEach(cb => {
    //                 cb.addEventListener('change', () => {
    //                   const group = form.querySelectorAll('input[name=\\\"' + cb.name + '\\\"]');
    //                   if ([...group].some(g => g.checked)) {
    //                     group.forEach(g => g.classList.remove('invalid-checkbox'));
    //                   }
    //                 });
    //               });
    //             });
    //             </script>
                
    //             <style>
    //                 .invalid-checkbox {
    //                   outline: 1px solid #B70A0A!important;
    //                 }
    //             </style>
            
    //         ";

    // }

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

            $actionPoll = $urlAppend . "modules/units/view.php?course=$course_code&res_type=questionnaire&pid=$pid&UseCase=1&session=$_GET[session]&from_session_view=true&onBehalfOfUser=true";
            $disabledUserSelection = '';
            if (isset($_GET['emptyQ'])) {
                $disabledUserSelection = 'disabled';
            }
            $tool_content .= "
                <div class='col-12 mb-4'>
                    <div class='card panelCard card-default px-lg-4 py-lg-3 mb-4'>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>$langSubmissionOnBehalfOfUser</h3>
                        </div>
                        <div class='card-body'>
                            <form id='onBehalfOfSelectionForm' method='post' action='$actionPoll'>
                                <select id='onBehalfOfSelection' class='form-select' name='userSelected' $disabledUserSelection>";
                                    $tool_content .= "<option value='0' selected>$langSelect</option>";
                                    foreach ($session_participants as $p) {
                                        $tool_content .= "<option value='{$p->participants}' " . ($userDefault == $p->participants ? 'selected' : '') . ">{$p->givenname}&nbsp;{$p->surname}</option>";
                                    }
            $tool_content .= " </select>
                            </form>";
                            if ($userDefault > 0) {
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
            $form_link = "../units/view.php?course=$course_code&amp;res_type=questionnaire&amp;id=$_REQUEST[unit_id]&amp;from_poll=true";
        } else if (isset($_REQUEST['res_type'])) {
            $session_view = '';
            if (isset($_GET['from_session_view'])) {
                $session_view = "&amp;session=$_GET[session]&amp;from_session_view=true";
            }
            $onBehalfOfUserView = '';
            if (isset($_GET['onBehalfOfUser'])) {
                $onBehalfOfUserView = "&amp;onBehalfOfUser=true";
            }
            $form_link = "../units/view.php?course=$course_code&amp;from_poll=true&amp;res_type=questionnaire$session_view$onBehalfOfUserView";
        } else {
            $form_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&from_poll=true";
        }
        if (!$temp_IsLime) {
            $tool_content .= "
            <form class='form-horizontal' role='form' action='$form_link' id='poll' method='post' enctype='multipart/form-data'>
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
        $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d AND qtype != ?d ORDER BY q_position,`page` ASC", $pid, 0);
        $pageBreakExists = checkPageBreakOn($pid);
        $maxPage = Database::get()->querySingle("SELECT MAX(`page`) AS max FROM poll_question WHERE pid = ?d AND qtype != ?d", $pid, 0)->max;
        if (isset($_SESSION['current_page'])) {
            unset($_SESSION['current_page']);
        }
        $_SESSION['current_page'] = $pageBreakExists ? 1 : '';
        if ($pageBreakExists && isset($_SESSION['data_file_answer']) && !isset($_GET['onBehalfOfUser'])) {
            unset($_SESSION['data_file_answer']);
        }
        if ($pageBreakExists && isset($_GET['page'])) {
            $_SESSION['current_page'] = intval($_GET['page']);
        } elseif (!$pageBreakExists) {
            unset($_SESSION['current_page']);
            unset($_SESSION['data_answers']);
            // When page break is off and the user has uploaded a file from uppy
            if (isset($_SESSION['data_file_answer'])) {
                foreach ($_SESSION['data_file_answer'] as $questionID => $val) {
                    $_SESSION['data_answers'][$questionID] = $val;
                }
            }
            unset($_SESSION['question_ids']);
            unset($_SESSION['q_row_columns']);
            unset($_SESSION['loop_init_answers']);
            unset($_SESSION['loop_init_answers_session']);
        }
        if (!$userDefault && !isset($_GET['onBehalfOfUser'])) {
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
        if (isset($_SESSION["poll_answers_$pid"])) {
            $incomplete_resubmission = isset($_SESSION["poll_answers_$pid"]);
            $incomplete_answers = $_SESSION["poll_answers_$pid"];
            unset($_SESSION["poll_answers_$pid"]);
        }

        if (!isset($_GET['page']) or (isset($_GET['page']) && intval($_GET['page']) == 1)) {
            $_SESSION['q_counter'] = 1;
        } else {
            $totalQuestionsInPrevPages = Database::get()->querySingle("SELECT COUNT(*) as total FROM poll_question 
                                                                       WHERE pid = ?d AND `page` < ?d AND `page` > ?d", $pid, intval($_GET['page']), 0)->total;
            $_SESSION['q_counter'] = $totalQuestionsInPrevPages + 1;
        }
        // Session process
        $sql_an = '';
        $s_id = $_GET['session'] ?? 0;
        $sql_an = "AND b.session_id = $s_id";

        // Initialize the user answers from db
        user_answers_from_db($questions, $sql_an, $userDefault, $pageBreakExists); 
        // If the user has left an empty required question
        if (isset($_GET['emptyQ']) && isset($_SESSION['temp_data_answers'])) {
            foreach ($_SESSION['temp_data_answers'] as $Qid => $val) {
                $typeQ = Database::get()->querySingle("SELECT qtype FROM poll_question WHERE pqid = ?d", $Qid)->qtype;
                if ($typeQ == QTYPE_MULTIPLE && is_array($val)) {
                    $multipleData = [];
                    foreach ($val as $v) {
                        $multipleData[] = $v; 
                    }
                    $str_multipleData = implode(',', $multipleData);
                    $_SESSION['data_answers'][$Qid] = $str_multipleData;
                } else {
                    $_SESSION['data_answers'][$Qid] = $val;
                }
            }
            if (isset($_SESSION['unanswered_required_qids'])) {
                foreach ($_SESSION['unanswered_required_qids'] as $q) {
                    $_SESSION['emptyQuestions'][] = $q;
                    unset($_SESSION['data_answers'][$q]);
                }
            }
            unset($_SESSION['temp_data_answers']);
            unset($_SESSION['unanswered_required_qids']);
        }
        

        foreach ($questions as $theQuestion) {
            if ($temp_IsLime) {
                break;
            }
            $pqid = $theQuestion->pqid;
            $qtype = $theQuestion->qtype;
            $q_description = $theQuestion->description;
            $qHasSubQ = $theQuestion->has_sub_question;
            if (isset($incomplete_resubmission) and !isset($incomplete_answers[$pqid])) {
                $incomplete_answers[$pqid] = [];
            }
            $user_answers = null;
            if ($qtype == QTYPE_LABEL) {
                $tool_content .= "
                <div class='col-12 mb-4'>
                    <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                        <div class='card-body'>" . standard_text_escape($theQuestion->question_text) . "";
                        if (!empty($theQuestion->description)) {
                            $tool_content .= standard_text_escape($theQuestion->description);
                        }
      $tool_content .= "</div></div>
                </div>";
            } else {
                // Store all questions into session variables.
                $_SESSION['question_ids'][$pqid] = $qtype;

                // Do not display the subquestion as main question
                if ($theQuestion->has_sub_question == -1) {
                    continue;
                }

                $sSubQ = 0;
                if ($qHasSubQ) {
                    $sSubQ = Database::get()->querySingle("SELECT sub_qid FROM poll_question_answer WHERE pqid = ?d AND sub_qid > ?d", $pqid, 0)->sub_qid;
                }

                // Ignore questions that appear on a new page.
                if (isset($_SESSION['current_page']) && $theQuestion->page != $_SESSION['current_page']) {
                    continue;
                }
                $RequiredQuestionHtml = '';
                if ($theQuestion->require_response) {
                    $RequiredQuestionHtml = "&nbsp; <span data-bs-toggle='tooltip' data-bs-placement='top' title='$langRequireAnswer'>(<i class='fa-solid fa-asterisk fa-lg text-danger'></i>)</span>";
                }

                // Highlight to the card question only if is empty.
                $emptyQuestionStyle = '';
                if (isset($_SESSION['emptyQuestions']) && is_array($_SESSION['emptyQuestions']) && in_array($pqid, $_SESSION['emptyQuestions'])) {
                    $emptyQuestionStyle = "style='border: solid 2px red !important;'";
                }
                $tool_content .= "
                <div class='col-12'>
                    <div class='card panelCard px-lg-4 py-lg-3 h-100 panelCard-questionnaire poll-panel mb-4' $emptyQuestionStyle>
                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>$langQuestion $_SESSION[q_counter] $RequiredQuestionHtml</h3>
                        </div>
                        <div class='card-body'>";
                            $tool_content .= "<p class='TextMedium Neutral-900-cl mb-2'>".q_math($theQuestion->question_text)."</p>";
                                            if(!empty($q_description)){
                                                $tool_content .= "<div class='col-12 my-4'>$q_description</div>";
                                            }
                            $tool_content .= "<input type='hidden' name='question[$pqid]' value='$qtype'>";
                            if ($qtype == QTYPE_SINGLE || $qtype == QTYPE_MULTIPLE) {
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
                                    if ($qtype == QTYPE_SINGLE && isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$pqid]) && $_SESSION['data_answers'][$pqid] == $theAnswer->pqaid) {
                                        $checked = 'checked';
                                    } elseif ($qtype == QTYPE_MULTIPLE && isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$pqid])) {
                                        $arrTemporaryUserAnswers = explode(',', $_SESSION['data_answers'][$pqid]);
                                        if (in_array($theAnswer->pqaid, $arrTemporaryUserAnswers)) {
                                            $checked = 'checked';
                                        }
                                    }
                                    $tool_content .= "
                                        <div class='form-group'>
                                            <div class='col-sm-offset-1 col-sm-11'>
                                                <div class='$type_attr QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                                    <label class='$class_type_attr' aria-label='$langSelect'>
                                                        <input class='single_type_answer' type='$type_attr' name='answer[$pqid]$name_ext' value='$theAnswer->pqaid' $checked data-question-type='$qtype' data-main-question='$pqid'>
                                                        $checkMark_class
                                                        ".q_math($theAnswer->answer_text)."
                                                    </label>
                                                </div>
                                            </div>
                                        </div>";

                                    /*****************************************************************/
                                    /*****************************************************************/
                                    /*****************************************************************/
                                    if ($theAnswer->sub_qid) {// the answer contains the sub-question
                                        $subQDisplay = 'd-none';
                                        if (isset($_SESSION['data_answers'][$pqid])) {
                                            $theCheckedSubQ = Database::get()->querySingle("SELECT sub_qid FROM poll_question_answer WHERE pqaid = ?d", $_SESSION['data_answers'][$pqid])->sub_qid;
                                            if ($theCheckedSubQ > 0) {
                                                $subQDisplay = "d-block";
                                            }
                                        }
                                        $qTypeSubQuestion = Database::get()->querySingle("SELECT qtype FROM poll_question WHERE pqid = ?d", $theAnswer->sub_qid)->qtype;
                                        $SubQuestionText = Database::get()->querySingle("SELECT question_text FROM poll_question WHERE pqid = ?d", $theAnswer->sub_qid)->question_text;
                                        $tool_content .= "<div class='col-12 sub_question_temp_{$pqid} sub_question_{$theAnswer->pqaid} $subQDisplay' style='border-top: solid 1px rgb(30, 43, 52) !important; padding-top: 25px; margin-top: 25px;'>";
                                        if ($qTypeSubQuestion == QTYPE_SINGLE) {
                                            $resSubQAnswers = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d", $theAnswer->sub_qid);
                                            $tool_content .= "<p class='mb-2'>$SubQuestionText</p>";
                                            foreach ($resSubQAnswers as $an) {
                                                $checkedSubQ = '';
                                                if (isset($_SESSION['data_answers'][$an->pqid]) && $_SESSION['data_answers'][$an->pqid] == $an->pqaid) {
                                                    $checkedSubQ = 'checked';
                                                }
                                                $tool_content .= "
                                                <div class='form-group'>
                                                    <div class='col-sm-offset-1 col-sm-11'>
                                                        <div class='radio QuestionType_{$qTypeSubQuestion} QuestionNumber_{$an->pqid}'>
                                                            <label class='radio-label' aria-label='$langSelect'>
                                                                <input type='radio' name='answer[$an->pqid]' value='$an->pqaid' $checkedSubQ data-question-type='$qTypeSubQuestion'>
                                                                ".q_math($an->answer_text)."
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>";
                                            }
                                        } elseif ($qTypeSubQuestion == QTYPE_MULTIPLE) {
                                            $qTypeSubQuestion = Database::get()->querySingle("SELECT qtype FROM poll_question WHERE pqid = ?d", $theAnswer->sub_qid)->qtype;
                                            $resSubQAnswers = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d", $theAnswer->sub_qid);
                                            $tool_content .= "<input type='hidden' name='question[$theAnswer->sub_qid]' value='$qTypeSubQuestion'>
                                                              <input type='hidden' name='answer[$theAnswer->sub_qid]' value='-1'>";
                                            $tool_content .= "<p class='mb-2'>$SubQuestionText</p>";
                                            foreach ($resSubQAnswers as $an) {
                                                $checkedSubQ = '';
                                                if (isset($_SESSION['data_answers'][$an->pqid])) {
                                                    $arrAnSubQ = explode(',', $_SESSION['data_answers'][$an->pqid]);
                                                    if (in_array($an->pqaid, $arrAnSubQ)) {
                                                        $checkedSubQ = 'checked';
                                                    }
                                                }
                                                $tool_content .= "
                                                <div class='form-group'>
                                                    <div class='col-sm-offset-1 col-sm-11'>
                                                        <div class='checkbox QuestionType_{$qTypeSubQuestion} QuestionNumber_{$an->pqid}'>
                                                            <label class='label-container' aria-label='$langSelect'>
                                                                <input class='single_type_answer' type='checkbox' name='answer[$an->pqid][]' value='$an->pqaid' $checkedSubQ data-question-type='$qTypeSubQuestion'>
                                                                <span class='checkmark'></span>
                                                                ".q_math($an->answer_text)."
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>";
                                            }
                                        } elseif ($qTypeSubQuestion == QTYPE_FILL) {
                                            $text = '';
                                            $QText = Database::get()->querySingle("SELECT question_text FROM poll_question WHERE pqid = ?d", $theAnswer->sub_qid)->question_text;
                                            if (isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$theAnswer->sub_qid])) {
                                                $text = $_SESSION['data_answers'][$theAnswer->sub_qid];
                                            }
                                            $tool_content .= "
                                            <p class='TextMedium Neutral-900-cl mb-2'>$QText</p>
                                            <div class='form-group margin-bottom-fat'>
                                                <div class='col-sm-12 margin-top-thin QuestionType_{$qTypeSubQuestion} QuestionNumber_{$theAnswer->sub_qid}'>
                                                    <textarea class='form-control' name='answer[$theAnswer->sub_qid]' aria-label='$langTypeOutMessage' data-question-type='$qTypeSubQuestion'>$text</textarea>
                                                </div>
                                            </div>";
                                        }

                                        $tool_content .= "</div>";
                                    }
                                    /*****************************************************************/
                                    /*****************************************************************/
                                    /*****************************************************************/
                                }
                                if ($qtype == QTYPE_SINGLE && $default_answer) {
                                    $checked = '';
                                    if (isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$pqid]) && $_SESSION['data_answers'][$pqid] == -1) {
                                        $checked = 'checked';
                                    }
                                    $tool_content .= "
                                        <div class='form-group'>
                                            <div class='col-sm-offset-1 col-sm-11'>
                                                <div class='$type_attr QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                                    <label class='$class_type_attr'>
                                                        <input type='$type_attr' name='answer[$pqid]' value='-1' data-question-type='$qtype' $checked>
                                                        $checkMark_class
                                                        $langPollUnknown
                                                    </label>
                                                </div>
                                            </div>
                                        </div>";
                                }

                                $tool_content .= "<div class='col-12 d-flex justify-content-end align-items-center mt-4'>
                                                    <a id='{$qtype}_{$pqid}' class='btn deleteAdminBtn clearUpBtn gap-1' data-question-clean='$pqid' data-sub-question='$sSubQ'><i class='fa-regular fa-trash-can'></i>$langCleanup</a>
                                                  </div>";
                            } elseif ($qtype == QTYPE_SCALE) {
                                $slider_value = 0;
                                if (isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$pqid])) {
                                    $slider_value = $_SESSION['data_answers'][$pqid];
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
                                        <input aria-label='$langAnswer' name='answer[$pqid]' class='grade_bar' data-answers='$countAnsScales' data-txt-answers='$ansAsString' data-slider-id='ex1Slider' type='text' data-slider-min='1' data-slider-max='$theQuestion->q_scale' data-slider-step='1' data-slider-value='$slider_value' data-question-type='$qtype'>
                                    </div>";
                            } elseif ($qtype == QTYPE_FILL) {
                                $text = '';
                                if (isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$pqid])) {
                                    $text = $_SESSION['data_answers'][$pqid];
                                }
                                $tool_content .= "
                                    <div class='form-group margin-bottom-fat'>
                                        <div class='col-sm-12 margin-top-thin QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                            <textarea class='form-control' name='answer[$pqid]' aria-label='$langTypeOutMessage' data-question-type='$qtype'>$text</textarea>
                                        </div>
                                    </div>";

                                    $tool_content .= "<div class='col-12 d-flex justify-content-end align-items-center mt-4'>
                                                            <a id='{$qtype}_{$pqid}' class='btn deleteAdminBtn clearUpBtn gap-1' data-question-clean='$pqid'><i class='fa-regular fa-trash-can'></i>$langCleanup</a>
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
                                        sort($sub_question_arr);
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
                                                        if (isset($_SESSION['q_row_columns'][$pqid])) {
                                                            unset($_SESSION['q_row_columns'][$pqid]);
                                                        }
                                                        for ($r=0; $r<$q_rows; $r++) {
                                                            $val_row = $r+1;
                                                            $tool_content .= "<tr>";
                                                                for ($t=0; $t<count($user_questions); $t++) {
                                                                    $val_col = $sub_question_arr[$t];
                                                                    $ansCounter = $ansCounter+1;
                                                                    $text = '';
                                                                    if (isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$theQuestion->pqid][$ansCounter])) {
                                                                        $text = $_SESSION['data_answers'][$theQuestion->pqid][$ansCounter];
                                                                    }
                                                                    if ($pageBreakExists) {
                                                                        $_SESSION['q_row_columns'][$pqid][] = "$pqid,$val_row,$val_col,$ansCounter";
                                                                    }
                                                                    $tool_content .= "<td>
                                                                                        <input type='hidden' name='q_row_col[]' value='$pqid,$val_row,$val_col,$ansCounter'>
                                                                                        <textarea class='form-control textarea-qtable' name='answer[$pqid][$ansCounter]' aria-label='$langTypeOutMessage' data-question-type='$qtype'>$text</textarea>
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
                                                            <a id='{$qtype}_{$pqid}' class='btn deleteAdminBtn clearUpBtn gap-1' data-question-clean='$pqid'><i class='fa-regular fa-trash-can'></i>$langCleanup</a>
                                                        </div>";
                                }
                            } elseif ($qtype == QTYPE_DATETIME || $qtype == QTYPE_SHORT || $qtype == QTYPE_DATE) {
                                $text = '';
                                if (isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$pqid])) {
                                    $text = $_SESSION['data_answers'][$pqid];
                                }

                                if ($qtype == QTYPE_DATETIME) {
                                    $tool_content .= "
                                    <div class='form-group margin-bottom-fat'>
                                        <div class='col-sm-12 margin-top-thin QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                            <div class='input-group'>
                                                <span class='add-on1 input-group-text h-40px input-border-color border-end-0'>
                                                    <i class='fa-regular fa-calendar Neutral-600-cl'></i>
                                                </span>
                                                <input id='dateTimeAnswer_$pqid' class='datetimeAnswer form-control mt-0 border-start-0' name='answer[$pqid]' type='text' data-question-type='$qtype' value='$text'>
                                            </div>
                                        </div>
                                    </div>";
                                } elseif ($qtype == QTYPE_DATE) {
                                    $tool_content .= "
                                    <div class='form-group margin-bottom-fat'>
                                        <div class='col-sm-12 margin-top-thin QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                            <div class='input-group'>
                                                <span class='add-on1 input-group-text h-40px input-border-color border-end-0'>
                                                    <i class='fa-regular fa-calendar Neutral-600-cl'></i>
                                                </span>
                                                <input id='dateAnswer_$pqid' class='dateAnswer form-control mt-0 border-start-0' name='answer[$pqid]' type='text' data-question-type='$qtype' value='$text'>
                                            </div>
                                        </div>
                                    </div>";
                                } else {
                                    $tool_content .= "
                                    <div class='form-group margin-bottom-fat'>
                                        <div class='col-sm-12 margin-top-thin QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                                            <input id='shortAnswer_$pqid' class='form-control' name='answer[$pqid]' type='text' data-question-type='$qtype' value='$text'>
                                        </div>
                                    </div>";
                                }
                               

                                    $tool_content .= "<div class='col-12 d-flex justify-content-end align-items-center mt-4'>
                                                            <a id='{$qtype}_{$pqid}' class='btn deleteAdminBtn clearUpBtn gap-1' data-question-clean='$pqid'><i class='fa-regular fa-trash-can'></i>$langCleanup</a>
                                                        </div>";
                            } elseif ($qtype == QTYPE_FILE) {
                                poll_upload_file($pid, $form_link, $qtype, $pqid, $userDefault);
                            }
                $tool_content .= "
                        </div>
                    </div>
                </div>";
                $_SESSION['q_counter'] = $_SESSION['q_counter'] + 1;
            }
        }


        // next - prev btn
        if (!$temp_IsLime && $pageBreakExists) {
            $session_mode = '';
            $onBehalfOfUserMode = '';
            $emptyQMode = '';
            if (isset($_GET['from_session_view']) && isset($_GET['session']) && isset($_GET['res_type'])) {
                $session_mode = "&res_type=$_GET[res_type]&session=$_GET[session]&from_session_view=true";
            }
            if (isset($_GET['onBehalfOfUser'])) {
                $onBehalfOfUserMode = "&onBehalfOfUser=true";
            }
            if (isset($_GET['emptyQ'])) {
                $emptyQMode = "&emptyQ=1";
            }
            $prev_page = $_SESSION['current_page'] - 1;
            $linkPrev = "{$urlServer}modules/questionnaire/pollparticipate.php?course=$course_code&UseCase=1&pid=$pid&page=$prev_page$session_mode$onBehalfOfUserMode$emptyQMode";
            $tool_content .= "<input id='linkPrevPage' type='hidden' value='$linkPrev'>";
            $next_page = $_SESSION['current_page'] + 1;
            $linkNext = "{$urlServer}modules/questionnaire/pollparticipate.php?course=$course_code&UseCase=1&pid=$pid&page=$next_page$session_mode$onBehalfOfUserMode$emptyQMode";
            $tool_content .= "<input id='linkNextPage' type='hidden' value='$linkNext'>";
            $tool_content .= "<div class='col-12 d-flex justify-content-between align-items-center gap-3'>";
            $tool_content .= "<div class='flex-fill d-flex justify-content-start'>";
                            if (isset($_GET['page']) && intval($_GET['page']) > 1) {
                                $tool_content .= "
                                    <button type='submit' id='prevBtn' class='btn submitAdminBtn w-100 gap-2'>
                                        <i class='fa-solid fa-chevron-left'></i>$langPreviousQuestion
                                    </button>";
                            }
            $tool_content .= "</div>";
            $tool_content .= "<div class='flex-fill d-flex justify-content-end'>";
                            if (!isset($_GET['page']) or (isset($_GET['page']) && intval($_GET['page']) < $maxPage)) {
                                $tool_content .= "
                                    <button type='submit' id='nextBtn' class='btn submitAdminBtn w-100 gap-2'>
                                        $langNextQuestion<i class='fa-solid fa-chevron-right'></i>
                                    </button>";
                            }
            $tool_content .= "</div>";
            $tool_content .= "</div>";
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
                $tool_content .= "<a class='btn cancelAdminBtn' href='../session/session_space.php?course=$course_code&amp;cancelPoll=true&amp;session=$_GET[session]'>" . q($langBack) . "</a>";
            } else {
                $tool_content .= "<a class='btn cancelAdminBtn' href='index.php?course=$course_code&cancelPoll=true'>" . q($langBack) . "</a>";
            }
        } else if ($is_course_reviewer && !isset($_GET['onBehalfOfUser'])) {
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
                $tool_content .= "<a class='btn cancelAdminBtn' href='index.php?course=$course_code&cancelPoll=true'>" . q($langBack) . "</a>";
            }
        } else {
            if (!$temp_IsLime) {
                $tool_content .= "<input class='btn submitAdminBtn' name='submit' type='submit' value='" . q($langSubmit) . "'>";
                if (isset($_REQUEST['unit_id'])) {
                    $tool_content .= "<a class='btn cancelAdminBtn ms-3' href='../units/index.php?course=$course_code&amp;id=$_REQUEST[unit_id]'>" . q($langCancel) . "</a>";
                } else {
                    if (isset($_GET['from_session_view'])) {
                        $tool_content .= "<a class='btn cancelAdminBtn ms-3' href='../session/session_space.php?course=$course_code&amp;cancelPoll=true&amp;session=$_GET[session]'>" . q($langCancel). "</a>";
                    } else {
                        $tool_content .= "<a class='btn cancelAdminBtn ms-3' href='index.php?course=$course_code&cancelPoll=true'>" . q($langCancel) . "</a>";
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
           $langPollEmailUsed, $langPollParticipateConfirmation, $course_id, $pid, $langChooseAUser,
           $langQuestionsRequireAnswers;

    $unit_id = isset($_REQUEST['unit_id'])? intval($_REQUEST['unit_id']): null;
    $poll = Database::get()->querySingle("SELECT * FROM poll WHERE pid = ?d", $pid);
    $default_answer = $poll->default_answer;
    $is_complete = true;
    $v = new Valitron\Validator($_POST);
    $atleast_one_answer = false;
    // Session process
    $s_id = $_GET['session'] ?? 0;
    $sql_u = "AND session_id = $s_id";

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
        $pageBreakExists = checkPageBreakOn($pid);
        $answer = update_submission($pid);
        $answeq_ids = [];
        foreach ($answer as $q => $an) {
            $answeq_ids[] = $q;
        }
        $userDefault = $_POST['onBehalfOfUserId'] ?? $uid;

        // Check if exists require answer to the specific question
        $require_an = false;
        $allQuestions = Database::get()->queryArray("SELECT pqid,qtype,require_response,has_sub_question FROM poll_question WHERE pid = ?d", $pid);
        foreach ($allQuestions as $q) {
            if ($q->require_response && !isset($answer[$q->pqid])) {
                $require_an = true;
                $_SESSION['unanswered_required_qids'][] = $q->pqid;
                // Add the unanswered sub-question
                if ($q->has_sub_question == 1 && $q->qtype == QTYPE_SINGLE) {
                    $_SESSION['unanswered_required_qids'][] = Database::get()->querySingle("SELECT sub_qid FROM poll_question_answer WHERE pqid = ?d AND sub_qid > ?d", $q->pqid, 0)->sub_qid;
                }
            } elseif ($q->require_response && isset($answer[$q->pqid]) && $q->qtype == QTYPE_TABLE) {
                if (count($answer[$q->pqid]) === count(array_filter($answer[$q->pqid], function($value) {// all elements are empty strings
                    return $value === "";
                }))) {
                    $require_an = true;
                    $_SESSION['unanswered_required_qids'][] = $q->pqid;
                }
            } elseif ($q->require_response && isset($answer[$q->pqid]) && empty($answer[$q->pqid]) && ($q->qtype == QTYPE_DATETIME or $q->qtype == QTYPE_SHORT or $q->qtype == QTYPE_FILE or $q->qtype == QTYPE_DATE)) {
                $require_an = true;
                $_SESSION['unanswered_required_qids'][] = $q->pqid;
            }
        }
        if ($require_an) {
            $fromSessionView = '';
            if (isset($_GET['from_session_view'])) {
                $fromSessionView = "&res_type=questionnaire&session=$_GET[session]&from_session_view=true";
            }
            $onBehalfOfUserView = '';
            if (isset($_GET['onBehalfOfUser'])) {
                $onBehalfOfUserView = "&onBehalfOfUser=true";
            }
            $_SESSION['temp_data_answers'] = $answer;
            Session::flash('message', $langQuestionsRequireAnswers);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/questionnaire/pollparticipate.php?course=$course_code&UseCase=1&pid=$pid&su=$userDefault&emptyQ=1$fromSessionView$onBehalfOfUserView");
        }

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

        
        if (isset($_SESSION['question_ids'])) {
            foreach ($_SESSION['question_ids'] as $question_id => $qtype) {
                if (!in_array($question_id, $answeq_ids)) {
                    unset($_SESSION['question_ids'][$question_id]);
                }
            }
            $question = $_SESSION['question_ids'];
        } else {
            $question = isset($_POST['question'])? $_POST['question']: array();
        }

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
            } elseif ($qtype == QTYPE_FILL || $qtype == QTYPE_DATETIME || $qtype == QTYPE_SHORT || $qtype == QTYPE_FILE || $qtype == QTYPE_DATE) {
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
                $arr_answers = $answer;
                $empty_answers = 0;
                $arrRowCols = (isset($pageBreakExists) && isset($_SESSION['q_row_columns'][$pqid])) ? $_SESSION['q_row_columns'][$pqid] : $_POST['q_row_col'];
                if (!isset($arrRowCols)) {
                    $row_cols = Database::get()->querySingle("SELECT q_row,q_column FROM poll_question WHERE pqid = ?d", $pqid);
                    $total_row = $row_cols->q_row;
                    $total_cols = $row_cols->q_column;
                    $div = 0;
                    for ($i = 1; $i <= $total_row; $i++) {
                        for ($j = 1; $j <= $total_cols; $j++) {
                            $div++;
                            $arrRowCols[] = "$pqid,$i,$j,$div";
                        }
                    }
                }
                foreach ($arrRowCols as $q_an) {
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

            if (!isset($answer_text)) {
                $answer_text = '';
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
            if(isset($_GET['from_session_view'])){
                redirect_to_home_page("modules/units/view.php?course=$course_code&res_type=questionnaire&UseCase=1&pid=$pid&session=$_GET[session]&from_session_view=true");
            }else{
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

function checkPageBreakOn($PID) {
    $check = false;
    $res = Database::get()->querySingle("SELECT pqid FROM poll_question WHERE pid = ?d AND qtype = ?d AND `page` = ?d", $PID, 0, 0);
    if ($res) {
        $check = true;
    }
    return $check;
}

function user_answers_from_db($questions, $sql_an, $userDefault, $pageBreakExists) {
    if (!isset($_SESSION['loop_init_answers'])) {
        $_SESSION['loop_init_answers'] = 1;
    }
    if (!isset($_SESSION['loop_init_answers_session']) && isset($_GET['onBehalfOfUser']) && isset($_SESSION['onBehalfOfUserId'])) {
        $_SESSION['loop_init_answers'] = $_SESSION['loop_init_answers_session'] = 1;
    }
    if (isset($_SESSION['loop_init_answers']) && $_SESSION['loop_init_answers'] == 1) {
        foreach ($questions as $theQuestion) {
            $pqid = $theQuestion->pqid;
            $qtype = $theQuestion->qtype;
            $user_answers = null;
            if (isset($_GET['onBehalfOfUser']) && isset($_SESSION['onBehalfOfUserId']) && $userDefault == 0) {
                unset($_SESSION['data_answers'][$pqid]);
            }
            if (($qtype == QTYPE_SINGLE || $qtype == QTYPE_MULTIPLE)) {       
                $user_answers = Database::get()->queryArray("SELECT a.aid
                        FROM poll_user_record b, poll_answer_record a
                        LEFT JOIN poll_question_answer c
                            ON a.aid = c.pqaid
                        WHERE a.poll_user_record_id = b.id
                            AND a.qid = ?d
                            AND b.uid = ?d
                            $sql_an", $pqid, $userDefault);          
                if ($user_answers) {
                    $storeData = [];
                    foreach ($user_answers as $ua) {
                        $storeData[] = $ua->aid;
                    }
                    if ($qtype == QTYPE_MULTIPLE) {
                        $_SESSION['data_answers'][$pqid] = implode(',', $storeData);
                    } elseif ($qtype == QTYPE_SINGLE) {
                        foreach ($storeData as $sd) {
                            $_SESSION['data_answers'][$pqid] = $sd;
                        }
                    }
                }
            } elseif ($qtype == QTYPE_SCALE) {
                $user_answers = Database::get()->querySingle("SELECT a.answer_text
                                    FROM poll_answer_record a, poll_user_record b
                                WHERE qid = ?d
                                    AND a.poll_user_record_id = b.id
                                    AND b.uid = ?d
                                    $sql_an", $pqid, $userDefault);
                if ($user_answers) {
                    $slider_value = $user_answers->answer_text;
                    $_SESSION['data_answers'][$pqid] = $slider_value;
                }
            } elseif ($qtype == QTYPE_FILL or $qtype == QTYPE_DATETIME or $qtype == QTYPE_SHORT or $qtype == QTYPE_FILE or $qtype == QTYPE_DATE) {
                $user_answers = Database::get()->querySingle("SELECT a.answer_text
                                    FROM poll_answer_record a, poll_user_record b
                                WHERE qid = ?d
                                    AND a.poll_user_record_id = b.id
                                    AND b.uid = ?d
                                    $sql_an", $pqid, $userDefault);
                if ($user_answers) {
                    $text = $user_answers->answer_text;
                    $_SESSION['data_answers'][$pqid] = $text;
                }
                if ($qtype == QTYPE_FILE && !$pageBreakExists && isset($_SESSION['data_file_answer'][$pqid])) {
                    $_SESSION['data_answers'][$pqid] = $_SESSION['data_file_answer'][$pqid];
                }
                if ($qtype == QTYPE_FILE && $pageBreakExists && isset($_SESSION['data_file_answer'][$pqid]) && isset($_GET['onBehalfOfUser'])) {
                    $_SESSION['data_answers'][$pqid] = $_SESSION['data_file_answer'][$pqid];
                }
            } elseif ($qtype == QTYPE_TABLE) {
                $s_data = [];
                $q_res = Database::get()->querySingle("SELECT q_row,q_column FROM poll_question WHERE pqid = ?d", $pqid);
                $length = 1;
                for ($i = 1; $i <= $q_res->q_row; $i++) {
                    for ($j = 1; $j <= $q_res->q_column; $j++) {
                        $user_answers = Database::get()->querySingle("SELECT DISTINCT a.sub_qid, a.sub_qid_row, a.answer_text
                                        FROM poll_answer_record a, poll_user_record b
                                        WHERE qid = ?d
                                        AND a.poll_user_record_id = b.id
                                        AND b.uid = ?d
                                        AND a.sub_qid = ?d
                                        AND a.sub_qid_row = ?d
                                        $sql_an", $pqid, $userDefault, $j, $i);
                        
                        if ($user_answers) {
                            $s_data[$length] = $user_answers->answer_text;
                        }
                        $length++;
                    }
                }
                if (count($s_data) > 0) {
                    $_SESSION['data_answers'][$pqid] = $s_data;
                } 
            }
        }
        
        $_SESSION['loop_init_answers'] = $_SESSION['loop_init_answers'] + 1;
    }
}

function update_submission($pid) {
    if (isset($_SESSION['data_answers'])) {
        $final_answers = [];
        foreach ($_SESSION['data_answers'] as $question_key => $value) {
            if (isset($final_answers[$question_key])) {
                unset($final_answers[$question_key]);
            }
            $questionType = Database::get()->querySingle("SELECT qtype FROM poll_question WHERE pqid = ?d", $question_key)->qtype;
            if ($questionType == QTYPE_MULTIPLE) {
                $multiple_values_arr = explode(',', $value);
                $final_answers[$question_key] = $multiple_values_arr;
            } elseif ($questionType == QTYPE_SINGLE || $questionType == QTYPE_FILL || $questionType == QTYPE_SCALE
                        || $questionType == QTYPE_DATETIME || $questionType == QTYPE_SHORT || $questionType == QTYPE_FILE || $questionType == QTYPE_DATE) {
                $final_answers[$question_key] = $value;
            } elseif ($questionType == QTYPE_TABLE) {
                $resDimension = Database::get()->querySingle("SELECT q_row,q_column FROM poll_question WHERE pqid = ?d", $question_key);
                $length = $resDimension->q_row*$resDimension->q_column;
                $q_table_arr = [];
                for ($i = 1; $i <= $length; $i++) {
                    if (isset($_SESSION['data_answers'][$question_key][$i])) {
                        $q_table_arr[$i] = $_SESSION['data_answers'][$question_key][$i];
                    } else {
                        $q_table_arr[$i] = '';
                    }
                }
                $final_answers[$question_key] = $q_table_arr;
            }
        }
        if (isset($_POST['answer'])) {
            foreach ($_POST['answer'] as $q_key => $val) {
                if (isset($final_answers[$q_key])) {
                    unset($final_answers[$q_key]);
                }
                $final_answers[$q_key] = $val; // na parei thn kainouria timh
            }
        }
        $answer = $final_answers;
        unset($_SESSION['data_answers']);
        unset($_SESSION['loop_init_answers']);
        unset($_SESSION['loop_init_answers_session']);
        unset($_SESSION['onBehalfOfUserId']);
    } else {
        $answer = isset($_POST['answer'])? $_POST['answer']: array();
    }

    if (isset($_SESSION['emptyQuestions'])) {
        unset($_SESSION['emptyQuestions']);
    }

    return $answer;
}

function poll_upload_file($pid, $form_link, $qtype, $pqid, $currentUser) {
    global $tool_content, $head_content, $course_code, $urlAppend, $langPleaseWait, 
           $language, $langFileName, $langDelete, $langConfirmDeletePermantly, $urlServer, 
           $uid, $webDir, $langInfoPollUploadedFile;

    $token = $_SESSION['csrf_token'];
    $is_onBehalfOfUser_mode = isset($_GET['onBehalfOfUser']) ? 1 : 0;
    $sessionID = $_GET['session'] ?? 0;

    $del_file = '';
    $filename = '';
    $filepath = '';
    if (isset($_SESSION['data_answers']) && !empty($_SESSION['data_answers'][$pqid])) {
        $arrFile = unserialize($_SESSION['data_answers'][$pqid]);
        $filename = $arrFile['filename'];
        $filepath = $arrFile['filepath'];
    }

    if (!empty($filename) && file_exists("$webDir/courses/$course_code/poll_$pid/$currentUser/$pqid/$sessionID$filepath")) {
        $del_file .= "
        <div class='d-flex align-items-center gap-2 mb-1'>
            <p id='fileName_{$pqid}' class='TextBold'>$langFileName: <a class='TextBold' target='_blank' href='{$urlServer}courses/$course_code/poll_$pid/$currentUser/$pqid/$sessionID$filepath'>$filename</a></p>
            <a id='del_file_{$pqid}' class='btn deleteAdminBtn' data-bs-toggle='tooltip' data-bs-placement='top' title='$langDelete' style='width: 25px; height: 25px;'><i class='fa-solid fa-trash'></i></a>
        </div>
        <div id='info_uploaded_file_{$pqid}' class='mb-4 mt-2'>$langInfoPollUploadedFile</div>
        ";
    } else {
        // If the user has uploaded a file and the user has canceled the poll, 
        // remove the uploaded file for the current question.
        $folderPath = "$webDir/courses/$course_code/poll_$pid/$currentUser/$pqid/$sessionID";
        if (is_dir($folderPath)) {
            $files = scandir($folderPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $fileNPath = $folderPath . DIRECTORY_SEPARATOR . $file;
                // Delete files
                if (is_file($fileNPath)) {
                    unlink($fileNPath);
                    unset($_SESSION['data_answers'][$pqid]);
                    Database::get()->query("DELETE poll_answer_record FROM poll_answer_record
                                            INNER JOIN poll_user_record ON poll_user_record.id=poll_answer_record.poll_user_record_id
                                            WHERE poll_answer_record.qid = ?d
                                            AND poll_user_record.uid = ?d
                                            AND poll_user_record.pid = ?d
                                            AND poll_user_record.session_id = ?d", $pqid, $currentUser, $pid, $sessionID);
                }
            }
        }
    }

    $head_content .= "<link href='{$urlAppend}js/bundle/uppy.min.css' rel='stylesheet'>";
    $tool_content .= "<div class='form-group margin-bottom-fat'>
                        <div class='col-sm-12 margin-top-thin QuestionType_{$qtype} QuestionNumber_{$pqid}'>
                            $del_file
                            <div id='uppy_{$pqid}'></div>
                        </div>
                      </div>";

    $head_content .= "
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let isUppyLoaded = false;

            async function loadUppy() {
                try {
                    console.log('Uppy loaded');
                    const { Uppy, Dashboard, XHRUpload, English, French, German, Italian, Spanish, Greek } = await import('{$urlAppend}js/bundle/uppy.js');

                    const locale_map = {
                        'de': German,
                        'el': Greek,
                        'en': English,
                        'es': Spanish,
                        'fr': French,
                        'it': Italian,
                    }

                    const uppy = new Uppy({
                        autoProceed: true,
                        restrictions: {
                            maxFileSize: '" . parseSize(ini_get('upload_max_filesize')) . "',
                            maxNumberOfFiles: 1,
                        }
                    })

                    uppy.use(Dashboard, {
                        target: '#uppy_{$pqid}',
                        inline: true,
                        showProgressDetails: true,
                        proudlyDisplayPoweredByUppy: false,
                        height: 500,
                        thumbnailWidth: 100,
                        locale: locale_map['{$language}'] || English,
                        hideUploadButton: true
                    });

                    uppy.use(XHRUpload, {
                        endpoint: '{$urlAppend}modules/questionnaire/pollparticipate.php?course={$course_code}&pid={$pid}&session={$sessionID}&qid={$pqid}&u={$currentUser}&behalf_of_user_mode={$is_onBehalfOfUser_mode}&token={$token}',
                        fieldName: 'answer',
                        formData: true,
                        getResponseData: (responseText, response) => {
                            try {
                                const data = JSON.parse(responseText.responseText);
                                if (data.success) {
                                    $.ajax({
                                        url: '{$urlAppend}modules/questionnaire/pollparticipate.php?course={$course_code}&UseCase=1&pid={$pid}&behalf_of_user_mode={$is_onBehalfOfUser_mode}',
                                        method: 'POST',
                                        data: { file_uploaded: 1, file_name: data.fileInfo.basename, file_path: data.filePath, question_id: $pqid },
                                        success: function(res) {
                                            if (res.upload_success) {
                                                setInterval(() => {
                                                    window.location.reload();
                                                }, 500);
                                            }
                                        }
                                    });
                                }
                                return { url: '' };
                            } catch(e) {
                                console.error('Failed to parse response:', e); 
                                return { url: '' };
                            }
                        }
                    });

                    isUppyLoaded = true;

                } catch (error) {
                    console.log('Uppy not loaded', error);
                    isUppyLoaded = false;
                }
            }

            loadUppy();

            $('#del_file_{$pqid}').on('click', function (e) {
                e.preventDefault();
                if (confirm('$langConfirmDeletePermantly')) {
                    $.ajax({
                        url: '{$urlAppend}modules/questionnaire/pollparticipate.php?course=$course_code&UseCase=1&pid=$pid&session={$sessionID}&token={$token}',
                        method: 'POST',
                        data: { 
                            file_removed: 1,
                            fPath: '{$filepath}',
                            question_id: '{$pqid}',
                            current_user: '{$currentUser}'
                        },
                        success: function(response) {
                            $('#fileName_{$pqid}').remove();
                            $('#del_file_{$pqid}').remove();
                            $('#info_uploaded_file_{$pqid}').remove();
                        },
                    });
                }
            });
        });
    </script>";
    
}
