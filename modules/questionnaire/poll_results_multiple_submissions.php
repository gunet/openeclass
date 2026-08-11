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


$require_current_course = true;
$require_help = true;
$helpTopic = 'questionnaire';

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'functions.php';
require_once 'modules/usage/usage.lib.php';
require_once 'modules/session/functions.php';

$toolName = $langQuestionnaire;
$pageName = $langPollCharts;

$sID = 0;
if (isset($_GET['from_session_view'])) {
    $sID = $_GET['session'] ?? 0;
    if (isset($_GET['session'])) {
        check_user_belongs_in_session($_GET['session']);
    }
    if ($is_consultant) {
        $is_course_reviewer = true;
    }
    $session_title = Database::get()->querySingle("SELECT title FROM mod_session WHERE id = ?d",$_GET['session'])->title;
    $navigation[] = array('url' => $urlServer . '/modules/session/index.php?course=' . $course_code, 'name' => $langSession);
    $navigation[] = array('url' => $urlServer . '/modules/session/session_space.php?course=' . $course_code . "&session=" . $_GET['session'] , 'name' => $session_title);
} else {
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);
}

// Poll
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    redirect_to_home_page();
} else {
    $pid = intval($_GET['pid']);
}
$thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
if (!$thePoll) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

if (!$is_course_reviewer && !$thePoll->show_results) {
    Session::flash('message',$langPollResultsAccess);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
}

$arrParticipants = poll_participation();
$Participated = $arrParticipants['total_participants'];
$NoParticipated = $arrParticipants['total_users'] - $arrParticipants['total_participants'];

$head_content .= "
<script src='{$urlAppend}js/chart/chart.js'></script>
<script type = 'text/javascript'>
    $(document).ready(function(){
        // Get context of the canvas element
        const ctx = document.getElementById('PollPieChart').getContext('2d');

        // Create the pie chart
        const myPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
                labels: ['$langPollUsersParticipation', '$langPollNoUsersParticipation'],
                datasets: [{
                    data: [$Participated, $NoParticipated],
                    backgroundColor: [
                        'rgba(61, 183, 126, 0.6)', // Color for Option A
                        'rgba(255, 99, 132, 0.6)' // Color for Option B
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>";

if (isset($_REQUEST['unit_id'])) {
    $back_link = "../units/index.php?course=$course_code&amp;id=" . intval($_REQUEST['unit_id']);
} else {
    $back_link = '';
}

if (isset($_GET['from_session_view'])) {
    $action_bar = action_bar(array(
                    array(
                        'title' => $langBack,
                        'url' => "$back_link",
                        'icon' => 'fa-reply',
                        'level' => 'primary',
                        'show' => isset($_REQUEST['unit_id'])
                    ),
                    array('title' => $langDumpPDF,
                          'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid&amp;session=$_GET[session]&amp;from_session_view=true&amp;format=poll_pdf",
                          'icon' => 'fa-solid fa-file-pdf',
                          'level' => 'primary-label',
                          'link-attrs' => "target='_blank'",
                          'show' => $is_course_reviewer),
                    array('title' => $langPollFullResults,
                          'url' => "dumppollresults_multiple_submissions.php?course=$course_code&amp;pid=$pid&amp;full=1&amp;dumppoll_session=true&amp;session=$_GET[session]",
                          'icon' => 'fa-download',
                          'level' => 'primary-label',
                          'show' => $is_course_reviewer)
                ));
} else {
    $action_bar = action_bar(array(
                    array(
                        'title' => $langBack,
                        'url' => "$back_link",
                        'icon' => 'fa-reply',
                        'level' => 'primary',
                        'show' => isset($_REQUEST['unit_id'])),
                    array('title' => "$langPollPercentResults ($langDumpPDF)",
                        'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid&amp;format=poll_pdf",
                        'icon' => 'fa-file-pdf',
                        'level' => 'primary-label',
                        'link-attrs' => "target='_blank'",
                        'show' => $is_course_reviewer),
                    array('title' => $langPollFullResults,
                          'url' => "dumppollresults_multiple_submissions.php?course=$course_code&amp;pid=$pid&amp;full=1",
                          'icon' => 'fa-download',
                          'level' => 'primary-label',
                          'show' => $is_course_reviewer)
                ));
}
$tool_content .= $action_bar;

$tool_content .= "<div class='col-12'>
<div class='card panelCard px-lg-4 py-lg-3'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
        <h2 class='text-heading-h3'>$langInfoPoll</h2>
    </div>
    <div class='card-body'>
        <div class='col-12 d-flex justify-content-center justify-content-md-start align-items-start gap-3 flex-wrap'>
            <div class='PollPieChart_div'>
                <canvas width='250' height='250' id='PollPieChart'></canvas>
            </div>
            <div class='flex-fill'>
                <ul class='list-group list-group-flush'>
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langTitle</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                " . q_math($thePoll->name) . "
                            </div>
                        </div>
                    </li>
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langPollCreation</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                " . format_locale_date(strtotime($thePoll->creation_date)) . "
                            </div>
                        </div>
                    </li>
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langStart</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                " . format_locale_date(strtotime($thePoll->start_date)) . "
                            </div>
                        </div>
                    </li>
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langPollEnd</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                " . format_locale_date(strtotime($thePoll->end_date)) . "
                            </div>
                        </div>
                    </li>
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langPollTotalAnswers:</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                $Participated
                            </div>
                        </div>
                    </li>";
                    if ($sID > 0) {
                        $tool_content .= "
                            <li class='list-group-item element'>
                                <div class='row row-cols-1 row-cols-md-2 g-1'>
                                    <div class='col-md-3 col-12'>
                                        <div class='title-default'>$langSSession:</div>
                                    </div>
                                    <div class='col-md-9 col-12 title-default-line-height'>
                                        $session_title
                                    </div>
                                </div>
                            </li>";
                    }
        $tool_content .= "
                </ul>
            </div>
        </div>
    </div>
</div>
</div>";

// Questions
$questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d AND qtype != ?d ORDER BY q_position ASC", $pid, 0);
$newQuestions = [];
$j = 1;
foreach ($questions as $question) {
    if ($question->has_sub_question == -1) {
        continue;
    }

    $newQuestions[] = $question; // Add current question
    
    if ($question->has_sub_question == 1) {
        // Fetch sub-question
        $sub_qid = Database::get()->querySingle("SELECT sub_qid FROM poll_question_answer WHERE pqid = ?d AND sub_qid > ?d", $question->pqid, 0)->sub_qid;

        // Find sub-question in original array
        $subQuestion = null;
        foreach ($questions as $key_sub => $qt) {
            if ($qt->pqid == $sub_qid) {
                $qt->qnumber = $j . '.1';
                $subQuestion = $qt;
                break;
            }
        }

        // Insert sub-question after parent
        if ($subQuestion !== null) {
            $newQuestions[] = $subQuestion;
        }
    }
    $question->qnumber = $j;
    $j++;
}
$questions = $newQuestions;

$formUsers = Database::get()->queryArray("SELECT DISTINCT pur.uid, u.givenname, u.surname FROM poll_user_record pur
                                          JOIN user u ON pur.uid=u.id
                                          WHERE pur.pid = ?d AND pur.session_id = ?d", $pid, $sID);

// Show results per question or per user
$PollType = $thePoll->type;
if ($PollType == POLL_NORMAL || $PollType == POLL_QUICK || $PollType == POLL_COURSE_EVALUATION) {

    $fromSessionView = '';
    $sessionArg = '';
    if (isset($_GET['from_session_view'])) {
        $fromSessionView = "&from_session_view=true";
        $sessionArg = "&session=$sID";
    }

    $qform = 0;
    $qform_user = 0;
    if (isset($_POST['qform'])) {
        $qform = intval($_POST['qform']);
    }
    if (isset($_POST['qformUser'])) {
        $qform_user = intval($_POST['qformUser']);;
    }

    $tool_content .= "
    <div class='col-12 mt-4'>
        <form class='d-flex justify-content-start align-items-center gap-3 mb-4' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&pid=$pid$fromSessionView$sessionArg'>
            <div class='w-50'>
                <label class='form-label' for='question_id'>$langPollPerQuestion</label>
                <select class='form-select' name='qform' onchange='this.form.submit();'>";
                    $tool_content .= "<option value='0'>$langPollAllQuestions</option>";
                    foreach ($questions as $q) {
                        if ($q->has_sub_question != -1) {
                            $tool_content .= "<option value='{$q->pqid}' " . ($q->pqid==$qform ? 'selected' : '') . ">$q->qnumber) $q->question_text</option>";
                        }
                    }
    $tool_content .= "</select>
           </div>";
        if (($is_editor or $is_consultant) && !$thePoll->anonymized) {
    $tool_content .= "  <div class='w-50'>
                <label class='form-label' for='question_id'>$langPollPerUser</label>
                <select class='form-select' name='qformUser' onchange='this.form.submit();'>";
                    $tool_content .= "<option value='0'>$langPollAllUsers</option>";
                    foreach ($formUsers as $u) {
                        $tool_content .= "<option value='{$u->uid}' " . ($u->uid==$qform_user ? 'selected' : '') . ">$u->givenname&nbsp;$u->surname</option>";
                    }
    $tool_content .= "</select>
           </div>";
        }
    $tool_content .= "
        </form>
    ";

    poll_results_per_question_or_user($pid, $questions, $qform, $qform_user, $sID, $thePoll->anonymized);
}

draw($tool_content, 2, null, $head_content);

// Results per question
function poll_results_per_question_or_user($pid, $questions, $form_question = 0, $form_user = 0, $session_id = 0, $pollAnonymized = 0) {
    global $course_code, $course_id, $uid, $is_editor, $tool_content, $langStudent, $langAnswers,
           $webDir, $urlServer, $langPollUnknown, $is_consultant, $langPollUsersResponded;

    if (!$is_editor && !$is_consultant && isset($_GET['from_session_view'])) { // session
        $sqlForUser = "AND pur.uid = ?d";
        $sqlForUserArgs = [$uid];
    } else {
        $sqlForUser = "";
        $sqlForUserArgs = [];
    }
    if ($form_user > 0) {
        $sqlForUser = "AND pur.uid = ?d";
        $sqlForUserArgs = [$form_user];
    }
    
    $tool_content .= "<div class='col-12'>";
    foreach ($questions as $q) {
        if ($q->has_sub_question == -1) { // dont show the sub-question as default question
            continue;
        }
        if ($form_question > 0 && $q->pqid != $form_question) {
            continue;
        }

        $questionText = Database::get()->querySingle("SELECT question_text FROM poll_question WHERE pqid = ?d", $q->pqid)->question_text;
        if ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {
            $hasSubQuestion = Database::get()->querySingle("SELECT has_sub_question FROM poll_question WHERE pqid = ?d", $q->pqid)->has_sub_question;

            $tool_content .= "<div class='card panelCard card-default card-poll-results poll-border-left border-0 px-lg-4 py-lg-3 mb-4'>";
            $tool_content .= "<div class='card-header'><h2 class='text-heading-h3'>$q->qnumber) $questionText</h2></div>";
            $answers = Database::get()->queryArray("SELECT pur.uid, par.aid, par.submit_date FROM poll_answer_record par
                                                    JOIN poll_user_record pur ON pur.id=par.poll_user_record_id
                                                    WHERE par.qid = ?d
                                                    AND pur.session_id = ?d
                                                    $sqlForUser
                                                    ORDER BY par.submit_date DESC", $q->pqid, $session_id, $sqlForUserArgs);
            
            $result = [];
            foreach ($answers as $an) {
                $result[$an->aid][] = ['user_id' => $an->uid, 'submit_date' => $an->submit_date];
            }

            $tool_content .= "<div class='card-body'>";
            $tool_content .= "<table class='table-default'>";
            $tool_content .= "<thead><tr><th>$langPollUsersResponded</th><th></th></tr></thead>";
            foreach ($result as $aid => $arr) {
                if ($aid == -1) {
                    $answerText = "$langPollUnknown";
                } else {
                    $answerText = Database::get()->querySingle("SELECT answer_text FROM poll_question_answer WHERE pqaid = ?d", $aid)->answer_text; 
                }

                $showGrade = '';
                if ($q->require_grade && $q->has_sub_question != -1) {
                    $grade_val = Database::get()->querySingle("SELECT `weight` FROM poll_question_answer WHERE pqaid = ?d", $aid)->weight;
                    $showGrade .= "&nbsp;<span class='level-badge'><i class='fa fa-star' style='color:#f59e0b;'></i>$grade_val</span>";
                }
                
                $tool_content .= "<tr>";
                $tool_content .= "<td>$answerText</td>";
                $tool_content .= "<td class='text-end'><div class='dropdown'><a class='linkColor TextBold text-nowrap' href='#dropdown_menu_{$aid}' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                    " . count($arr) . "&nbsp; $langAnswers
                                </a><ul class='dropdown-menu' id='dropdown_menu_{$aid}'>";
                foreach ($arr as $val) {
                    $tool_content .= "<li class='dropdown-item'>" . ((!$is_editor && !$is_consultant && $pollAnonymized) ? "$langStudent" : uid_to_name($val['user_id'])) . "$showGrade&nbsp;($val[submit_date])</li>";
                }
                $tool_content .= "</ul></div></td>";
                $tool_content .= "</tr>";
            }
            $tool_content .= "</table></div>";

            if ($hasSubQuestion == 1) {
                $subQid = Database::get()->querySingle("SELECT sub_qid FROM poll_question_answer WHERE pqid = ?d AND sub_qid > ?d", $q->pqid, 0)->sub_qid;
                $subQuestionInfo = Database::get()->querySingle("SELECT question_text,qtype FROM poll_question WHERE pqid = ?d", $subQid);
                if ($subQuestionInfo && ($subQuestionInfo->qtype == QTYPE_SINGLE or $subQuestionInfo->qtype == QTYPE_MULTIPLE)) {
                    $tool_content .= "<div class='card-header mt-4'><h2 class='text-heading-h3'>$q->qnumber.1) $subQuestionInfo->question_text</h2></div>";
                    $answers = Database::get()->queryArray("SELECT pur.uid, par.aid, par.submit_date FROM poll_answer_record par
                                                            JOIN poll_user_record pur ON pur.id=par.poll_user_record_id
                                                            WHERE par.qid = ?d
                                                            AND pur.session_id = ?d
                                                            $sqlForUser
                                                            ORDER BY par.submit_date DESC", $subQid, $session_id, $sqlForUserArgs);

                    $result = [];
                    foreach ($answers as $an) {
                        $result[$an->aid][] = ['user_id' => $an->uid, 'submit_date' => $an->submit_date];
                    }

                    $tool_content .= "<div class='card-body'>";
                    $tool_content .= "<table class='table-default'>";
                    $tool_content .= "<thead><tr><th>$langPollUsersResponded</th><th></th></tr></thead>";
                    foreach ($result as $aid => $arr) {
                        $answerText = Database::get()->querySingle("SELECT answer_text FROM poll_question_answer WHERE pqaid = ?d", $aid)->answer_text; 
                        $tool_content .= "<tr>";
                        $tool_content .= "<td>$answerText</td>";
                        $tool_content .= "<td class='text-end'><div class='dropdown'><a class='linkColor TextBold text-nowrap' href='#dropdown_menu_{$aid}' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                            " . count($arr) . "&nbsp; $langAnswers
                                        </a><ul class='dropdown-menu' id='dropdown_menu_{$aid}'>";
                        foreach ($arr as $val) {
                            $tool_content .= "<li class='dropdown-item'>" . ((!$is_editor && !$is_consultant && $pollAnonymized) ? "$langStudent" : uid_to_name($val['user_id'])) . "&nbsp;($val[submit_date])</li>";
                        }
                        $tool_content .= "</ul></div></td>";
                        $tool_content .= "</tr>";
                    }
                    $tool_content .= "</table></div>";
                } elseif ($subQuestionInfo && $subQuestionInfo->qtype == QTYPE_FILL) {
                    $tool_content .= "<div class='card-header mt-4'><h2 class='text-heading-h3'>$q->qnumber.1) $subQuestionInfo->question_text</h2></div>";
                    $answers = Database::get()->queryArray("SELECT pur.uid, par.answer_text, par.submit_date FROM poll_answer_record par
                                                            JOIN poll_user_record pur ON pur.id=par.poll_user_record_id
                                                            WHERE par.qid = ?d
                                                            $sqlForUser
                                                            ORDER BY par.submit_date DESC", $subQid, $sqlForUserArgs);

                    $tool_content .= "<div class='card-body'>";
                    $tool_content .= "<table class='table-default'>";
                    $tool_content .= "<thead><tr><th>$langPollUsersResponded</th><th></th></tr></thead>";
                    foreach ($answers as $answer) {
                        $tool_content .= "<tr>";
                        $tool_content .= "<td>$answer->answer_text</td>";
                        $tool_content .= "<td class='text-end'>";
                            $tool_content .= "<p>" . ((!$is_editor && !$is_consultant && $pollAnonymized) ? "$langStudent" : uid_to_name($answer->uid)) . "&nbsp;($answer->submit_date)</p>";
                        $tool_content .= "</td>";
                        $tool_content .= "</tr>";
                    }
                    $tool_content .= "</table></div>";
                }
            }
            $tool_content .= "</div>"; // end card
        } elseif ($q->qtype == QTYPE_FILL || $q->qtype == QTYPE_DATETIME || $q->qtype == QTYPE_SHORT || $q->qtype == QTYPE_FILE || $q->qtype == QTYPE_DATE) {
            $tool_content .= "<div class='card panelCard card-default card-poll-results poll-border-left border-0 px-lg-4 py-lg-3 mb-4'>";
            $tool_content .= "<div class='card-header'><h2 class='text-heading-h3'>$q->qnumber) $questionText</h2></div>";
            $answers = Database::get()->queryArray("SELECT pur.uid, par.answer_text, par.submit_date FROM poll_answer_record par
                                                    JOIN poll_user_record pur ON pur.id=par.poll_user_record_id
                                                    WHERE par.qid = ?d
                                                    AND pur.session_id = ?d
                                                    $sqlForUser
                                                    ORDER BY par.submit_date DESC", $q->pqid, $session_id, $sqlForUserArgs);

            $tool_content .= "<div class='card-body'>";
            $tool_content .= "<table class='table-default'>";
            $tool_content .= "<thead><tr><th>$langPollUsersResponded</th><th></th></tr></thead>";
            foreach ($answers as $answer) {
                if ($q->qtype == QTYPE_FILE) {
                    $arrFile = unserialize($answer->answer_text, ['allowed_classes' => false]);
                    if (is_array($arrFile) && isset($arrFile['filename'], $arrFile['filepath']) 
                        && is_string($arrFile['filename']) && is_string($arrFile['filepath'])) {
                        $filename = basename(trim($arrFile['filename']));
                        $filepath = trim($arrFile['filepath']);
                        $userID = ($is_editor or $is_consultant) ? $answer->uid : $uid;
                        if (!file_exists("$webDir/courses/$course_code/poll_$pid/$userID/$q->pqid/$session_id$filepath")) {
                            $answerText = "<p class='text-decoration-line-through text-danger'>$filename</p>";
                        } else {
                            $answerText = "<a target='_blank' href='{$urlServer}courses/$course_code/poll_$pid/$userID/$q->pqid/$session_id$filepath'>$filename</a>";
                        }
                    }
                } else {
                    $answerText = $answer->answer_text;
                }
                $tool_content .= "<tr>";
                $tool_content .= "<td>$answerText</td>";
                $tool_content .= "<td class='text-end'>";
                    $tool_content .= "<p>" . ((!$is_editor && !$is_consultant && $pollAnonymized) ? "$langStudent" : uid_to_name($answer->uid)) . "&nbsp;($answer->submit_date)</p>";
                $tool_content .= "</td>";
                $tool_content .= "</tr>";
            }
            $tool_content .= "</table></div></div>";
        } elseif ($q->qtype == QTYPE_TABLE) {
            $q_dimension = Database::get()->querySingle("SELECT q_row, q_column FROM poll_question WHERE pqid = ?d", $q->pqid);
            $table_questions = Database::get()->queryArray("SELECT answer_text, sub_question FROM poll_question_answer WHERE pqid = ?d ORDER BY sub_question ASC", $q->pqid);
            $answers = Database::get()->queryArray("SELECT pur.uid, par.answer_text, par.sub_qid, par.sub_qid_row, par.submit_date FROM poll_answer_record par
                                                    JOIN poll_user_record pur ON pur.id=par.poll_user_record_id
                                                    WHERE par.qid = ?d
                                                    AND pur.session_id = ?d
                                                    $sqlForUser
                                                    ORDER BY par.submit_date DESC", $q->pqid, $session_id, $sqlForUserArgs);
            
            $userAnswers = [];
            $userAnswersHtml = '';
            foreach ($answers as $an) {
                if (isset($userAnswers[$an->uid][$an->submit_date][$an->sub_qid_row][$an->sub_qid])) {
                    $userAnswers[$an->uid][$an->submit_date][$an->sub_qid_row][$an->sub_qid] .= '<br>' . $an->answer_text;
                } else {
                    $userAnswers[$an->uid][$an->submit_date][$an->sub_qid_row][$an->sub_qid] = $an->answer_text;
                }
            }

            $tool_content .= "<div class='card panelCard card-default card-poll-results poll-border-left border-0 px-lg-4 py-lg-3 mb-4'>";
            $tool_content .= "<div class='card-header'><h2 class='text-heading-h3'>$q->qnumber) $questionText</h2></div>";
            $tool_content .= "<div class='card-body'>";
            
            foreach ($userAnswers as $userId => $answer) {
                $tool_content .= "<div class='w-100 mb-5'>";
                $tool_content .= "<h2 class='text-heading-h3 mb-0'><i class='fa-solid fa-user me-2'></i>" . ((!$is_editor && !$is_consultant && $pollAnonymized) ? "$langStudent" : uid_to_name($userId)) . "</h2>";
                $tool_content .= "<div class='table-responsive mt-0'><table class='table-default mt-1'><thead><tr>";
                foreach ($table_questions as $tq) {
                    $tool_content .= "<th>$tq->answer_text</th>";
                }
                $tool_content .= "</thead></tr><tbody>";
                foreach ($answer as $key_date => $submission) {
                    $tool_content .= "<tr>";
                    for ($column = 1; $column <= $q_dimension->q_column; $column++) {
                        $tool_content .= "<td>";
                        $tool_content .= "<ul class='m-0 p-0 list-unstyled'>";
                        for ($row = 1; $row <= $q_dimension->q_row; $row++) {
                            if (isset($submission[$row][$column])) {
                                $tool_content .= "<li>" . $submission[$row][$column] . '<br>' ?? '' . "</li>";
                            } else {
                                $tool_content .= "<li><br></li>";
                            }
                        }
                        $tool_content .= "</ul>";
                        $tool_content .= "</td>";
                    }
                    $tool_content .= "</tr>";
                    $tool_content .= "<tr><td colspan='$q_dimension->q_column'><strong><i class='fa-solid fa-clock me-2'></i>$key_date</strong></td></tr>";
                }
                $tool_content .= "</tbody></table></div></div>";
            }
            $tool_content .= "</div></div>";
        } elseif ($q->qtype == QTYPE_SCALE) {
            $answerScale = Database::get()->querySingle("SELECT answer_scale FROM poll_question WHERE pqid = ?d", $q->pqid)->answer_scale;
            if ($answerScale) {
                $arrAnsScale = explode('|', $answerScale);
                $arrAnsScale = array_combine(
                    range(1, count($arrAnsScale)),
                    $arrAnsScale
                );

                $tool_content .= "<div class='card panelCard card-default card-poll-results poll-border-left border-0 px-lg-4 py-lg-3 mb-4'>";
                $tool_content .= "<div class='card-header'><h2 class='text-heading-h3'>$q->qnumber) $questionText</h2></div>";
                $answers = Database::get()->queryArray("SELECT pur.uid, par.answer_text, par.submit_date FROM poll_answer_record par
                                                        JOIN poll_user_record pur ON pur.id=par.poll_user_record_id
                                                        WHERE par.qid = ?d
                                                        AND pur.session_id = ?d
                                                        $sqlForUser
                                                        ORDER BY par.submit_date DESC", $q->pqid, $session_id, $sqlForUserArgs);
                $result = [];
                foreach ($answers as $an) {
                    $result[$an->answer_text][] = ['user_id' => $an->uid, 'submit_date' => $an->submit_date];
                }

                $tool_content .= "<div class='card-body'>";
                $tool_content .= "<table class='table-default'>";
                $tool_content .= "<thead><tr><th>$langPollUsersResponded</th><th></th></tr></thead>";
                foreach ($result as $scaleId => $arr) {
                    $tool_content .= "<tr>";
                    $tool_content .= "<td>$arrAnsScale[$scaleId]</td>";
                    $tool_content .= "<td class='text-end'><div class='dropdown'><a class='linkColor TextBold text-nowrap' href='#dropdown_menu_{$scaleId}' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                        " . count($arr) . "&nbsp; $langAnswers
                                    </a><ul class='dropdown-menu' id='dropdown_menu_{$scaleId}'>";
                    foreach ($arr as $val) {
                        $tool_content .= "<li class='dropdown-item'>" . ((!$is_editor && !$is_consultant && $pollAnonymized) ? "$langStudent" : uid_to_name($val['user_id'])) . "&nbsp;($val[submit_date])</li>";
                    }
                    $tool_content .= "</ul></div></td>";
                    $tool_content .= "</tr>";
                }
                $tool_content .= "</table></div></div>";
            }
        }
    }
    $tool_content .= "</div>"; // end col-12

}


function poll_participation() {
    global $course_id;

    $poll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $_GET['pid']);
    $allUsers = [];

    $sid = $_GET['session'] ?? 0;

    if ($poll->assign_to_specific) {
        $assign = Database::get()->queryArray("SELECT * FROM poll_to_specific WHERE poll_id = ?d", $poll->pid);
        foreach ($assign as $item) {
            if ($item->user_id) {
                $allUsers[] = $item->user_id;
            } elseif ($item->group_id) {
                $group_members = Database::get()->queryArray("SELECT user_id FROM group_members WHERE is_tutor = 0 AND group_id = ?d", $item->group_id);
                foreach ($group_members as $member) {
                    $allUsers[] = $member->user_id;
                }
            }
        }
    } else {
        $allUsers = Database::get()->queryArray('SELECT user_id FROM course_user WHERE course_id = ?d AND editor = 0 AND status = ' . USER_STUDENT, $course_id);
        $allUsers = array_map(function ($user) {
            return $user->user_id;
        }, $allUsers);
    }

    $polledUsers = Database::get()->queryArray("
                        SELECT
                            MAX(id) AS id,
                            uid,
                            MAX(email) AS email,
                            MAX(email_verification) AS email_verification
                        FROM poll_user_record
                        WHERE pid = ?d
                        AND session_id = ?d
                        GROUP BY uid
                    ", $poll->pid, $sid);

    $okUsers = [];
    $emailUsers = [];
    $timestamp = [];
    foreach ($polledUsers as $user) {
        $ts = Database::get()->querySingle("SELECT submit_date FROM poll_answer_record WHERE poll_user_record_id = ?d LIMIT 1", $user->id)->submit_date;
        if ($user->uid) {
            $okUsers[] = $user->uid;
            $timestamp[$user->uid] = $ts;
        } elseif ($user->email_verification) {
            $emailUsers[] = $user->email;
            $timestamp[$user->email] = $ts;
        }
    }

    $allUsers = array_unique(array_merge($allUsers, $okUsers));

    if (isset($_GET['from_session_view'])) {
        $totalSessionParticipants = Database::get()->querySingle("SELECT COUNT(*) as total FROM mod_session_users
                                                                    WHERE session_id = ?d AND is_accepted = ?d", $sid, 1)->total;
    }

    return $arr = ['total_users' => $totalSessionParticipants ?? count($allUsers), 'total_participants' => count($polledUsers)];
}