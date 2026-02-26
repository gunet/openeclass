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
require_once 'functions.php';
require_once 'modules/usage/usage.lib.php';

$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.7.20/c3.css' />";
load_js('d3/d3.min.js');
load_js('c3-0.7.20/c3.min.js');

$toolName = $langQuestionnaire;
$pageName = $langPollCharts;

// view statistics by a consultant
$sID = 0;
if (isset($_GET['from_session_view'])) {
    $sID = $_GET['session'] ?? 0;
    if ($is_consultant) {
        $is_course_reviewer = true;
    }
    $session_title = Database::get()->querySingle("SELECT title FROM mod_session WHERE id = ?d",$_GET['session'])->title;
    $navigation[] = array('url' => $urlServer . '/modules/session/index.php?course=' . $course_code, 'name' => $langSession);
    $navigation[] = array('url' => $urlServer . '/modules/session/session_space.php?course=' . $course_code . "&session=" . $_GET['session'] , 'name' => $session_title);
} else {
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);
}

$head_content .= "<script type = 'text/javascript'>
    $(document).ready(function(){
      $('a.trigger_names').click(function(e){
        e.preventDefault();
        var action = $(this).attr('id');
        var field_type = $(this).data('type');
        if (action == 'show') {
            if (field_type == 'multiple') {
                var hidden_field = $(this).parent().next();
                $(this).parent().hide();
                hidden_field.show();
            } else {
                $(this).closest('tr').siblings('.hidden_row').show('slow');
                $(this).text('$langViewHide');
                $(this).attr('id', 'hide');
            }
        } else {
            if (field_type == 'multiple') {
                var hidden_field = $(this).parent();
                hidden_field.hide();
                hidden_field.prev().show();
            } else {
                $(this).closest('tr').siblings('.hidden_row').hide('slow');
                $(this).text('$langViewShow');
                $(this).attr('id', 'show');
            }
        }
      });
    });
</script>";

$head_content .= "<script type='text/javascript'>
        pollChartData = new Array();

        $(document).ready(function(){
            draw_plots();
        });

    function draw_plots(){
        var options = null;
        for(var i=0;i<pollChartData.length;i++){
            var x_row = 'answer';
            if (pollChartData[i]['answer_text']) {
                x_row = 'answer_text';
            }
            options = {
                data: {
                    json: pollChartData[i],
                    x: x_row,
                    types:{
                        percentage: 'bar'
                    },
                    axes: {percentage: 'y'},
                    names:{percentage:'%'},
                    colors:{percentage:'#e9d460'}
                },
                legend:{show:false},
                bar:{width:{ratio:0.8}},
                axis:{ x: {type:'category'}, y:{max: 100, padding:{top:0, bottom:0}}},
                bindto: '#poll_chart'+i
            };
            c3.generate(options);
        }
}

</script>";


if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    redirect_to_home_page();
} else {
    $pid = intval($_GET['pid']);
}
$thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
if (!$thePoll) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
$PollType = $thePoll->type;
$pollOptions = !is_null($thePoll->options) ? $thePoll->options : '';
$default_answer = $thePoll->default_answer;

if (!$is_course_reviewer && !$thePoll->show_results) {
    Session::flash('message',$langPollResultsAccess);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
}

if (isset($_GET['from_session_view'])) {
    $total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d 
                                                            AND (email_verification = 1 OR email_verification IS NULL) 
                                                            AND uid IN
                                                             (
                                                                SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1
                                                             ) 
                                                            AND session_id = ?d", $pid, $_GET['session'], $_GET['session'])->total;
} else {
    $total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $pid)->total;
}

if (!$total_participants) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
if (isset($_GET['res_per_u'])) {
    $total_participants = 1;
}

if (isset($_REQUEST['unit_id'])) {
    $back_link = "../units/index.php?course=$course_code&amp;id=" . intval($_REQUEST['unit_id']);
} else {
    $back_link = '';
}

$from_session = '';
$export_pdf = '';
$hidden_elements = '';
$from_session_view_type = '';
if (isset($_GET['from_session_view'])) {
    $from_session = "&amp;dumppoll_session=true&amp;session=$_GET[session]";
    $export_pdf = "&amp;session=$_GET[session]&amp;format=pdf";
    $hidden_elements = 'hidden-element';
    $action_bar = action_bar(array(
                    array(
                        'title' => $langBack,
                        'url' => "$back_link",
                        'icon' => 'fa-reply',
                        'level' => 'primary',
                        'show' => isset($_REQUEST['unit_id'])
                    ),
                    array('title' => $langDumpPDF,
                          'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid$export_pdf&amp;from_session_view=true",
                          'icon' => 'fa-solid fa-file-pdf',
                          'level' => 'primary-label',
                          'show' => ($is_course_reviewer && isset($_GET['from_session_view']))),
                    array('title' => $langPollPercentResults,
                          'url' => "dumppollresults.php?course=$course_code&amp;pid=$pid$from_session",
                          'icon' => 'fa-download',
                          'level' => 'primary-label',
                          'show' => $is_course_reviewer),
                    array('title' => $langPollFullResults,
                          'url' => "dumppollresults.php?course=$course_code&amp;pid=$pid&amp;full=1$from_session",
                          'icon' => 'fa-download',
                          'level' => 'primary-label',
                          'show' => $is_course_reviewer)
                ));
} else {
    $res_per_user = '';
    if (isset($_GET['res_per_u'])) {
        $res_per_user = "&amp;res_per_u=$_GET[res_per_u]";
    }
    $action_bar = action_bar(array(
                    array(
                        'title' => $langBack,
                        'url' => "$back_link",
                        'icon' => 'fa-reply',
                        'level' => 'primary',
                        'show' => isset($_REQUEST['unit_id'])),
                    array(
                        'title' => $langBack,
                        'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid",
                        'icon' => 'fa-reply',
                        'level' => 'primary',
                        'show' => isset($_GET['chart'])),
                    array(
                        'title' => $langBack,
                        'url' => "pollresults_per_user.php?course=$course_code&amp;pid=$pid",
                        'icon' => 'fa-reply',
                        'level' => 'primary',
                        'show' => isset($_GET['res_per_u']) && !$thePoll->anonymized),
                    array('title' => "$langCharts",
                          'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid&amp;chart=true",
                          'icon' => 'fa-solid fa-chart-area',
                          'level' => 'primary-label',
                          'show' => !isset($_GET['chart']) && !isset($_GET['res_per_u'])),
                    array('title' => "$langIndividuals",
                          'url' => "pollresults_per_user.php?course=$course_code&amp;pid=$pid",
                          'icon' => 'fa-address-card',
                          'level' => 'primary-label',
                          'show' => !isset($_GET['chart']) && !isset($_GET['res_per_u']) && !$thePoll->anonymized && $is_editor),
                    array('title' => "$langPollPercentResults ($langDumpExcel)",
                          'url' => "dumppollresults.php?course=$course_code&amp;pid=$pid$res_per_user",
                          'icon' => 'fa-file-excel',
                          'level' => 'primary-label',
                          'show' => $is_course_reviewer && !isset($_GET['chart'])),
                    array('title' => "$langPollPercentResults ($langDumpPDF)",
                        'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid&amp;format=poll_pdf$res_per_user",
                        'icon' => 'fa-file-pdf',
                        'level' => 'primary-label',
                        'show' => $is_course_reviewer && !isset($_GET['chart'])),
                    array('title' => $langPollFullResults,
                          'url' => "dumppollresults.php?course=$course_code&amp;pid=$pid&amp;full=1$res_per_user",
                          'icon' => 'fa-download',
                          'level' => 'primary-label',
                          'show' => $is_course_reviewer && !isset($_GET['chart']))
                ));
}
$tool_content .= $action_bar;

$arrParticipants = poll_user_participation();
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

$tool_content .= "<div class='col-12'>
<div class='card panelCard px-lg-4 py-lg-3'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
        <h3>$langInfoPoll</h3>
    </div>
    <div class='card-body'>
        <div class='col-12 d-flex justify-content-center justify-content-md-start align-items-start gap-3 flex-wrap'>
            <div>
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
                    <li class='list-group-item element $hidden_elements'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langPollCreation</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                " . format_locale_date(strtotime($thePoll->creation_date)) . "
                            </div>
                        </div>
                    </li>
                    <li class='list-group-item element $hidden_elements'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langStart</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                " . format_locale_date(strtotime($thePoll->start_date)) . "
                            </div>
                        </div>
                    </li>
                    <li class='list-group-item element $hidden_elements'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langPollEnd</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                " . format_locale_date(strtotime($thePoll->end_date)) . "
                            </div>
                        </div>
                    </li>
                    <li class='list-group-item element pollTotalAnswers $hidden_elements'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langPollTotalAnswers:</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                $total_participants
                            </div>
                        </div>
                    </li>";
                    if ($sID > 0) {
                        $tool_content .= "
                            <li class='list-group-item element $hidden_elements'>
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

$chart_data = [];
$chart_counter = 0;

$all_participants = [];
if (isset($_GET['from_session_view'])) { //session view
    $all_participants = Database::get()->queryArray("SELECT user.id,user.givenname,user.surname,mod_session_users.participants FROM mod_session_users
                                                     LEFT JOIN user ON user.id=mod_session_users.participants
                                                     WHERE mod_session_users.session_id = ?d AND mod_session_users.is_accepted = 1
                                                     AND mod_session_users.participants IN (SELECT uid FROM poll_user_record WHERE pid = ?d AND session_id = ?d)", $_GET['session'], $pid, $_GET['session']);
} else { // course view
    if (isset($_GET['res_per_u'])) {
        $uName = Database::get()->querySingle("SELECT givenname,surname FROM user WHERE id = ?d", intval($_GET['res_per_u']));
        $all_participants[] = (object) array("participants" => intval($_GET['res_per_u']), "givenname" => $uName->givenname, "surname" => $uName->surname);
    } else {
        $all_users_participants = Database::get()->queryArray("SELECT user.id,user.givenname,user.surname,poll_user_record.uid FROM poll_user_record
                                                           LEFT JOIN user ON user.id=poll_user_record.uid
                                                           WHERE poll_user_record.pid = ?d AND poll_user_record.session_id = ?d", $pid, 0);
        foreach ($all_users_participants as $p) {
            $all_participants[] = (object) array("participants" => $p->uid, "givenname" => $p->givenname, "surname" => $p->surname);
        }
    }
}

// If the poll has enabled the grade option, display the user's grades.
// $isEnabledGrade = pollHasGrade($pid);
// if ($isEnabledGrade && $is_editor && !isset($_GET['res_per_u']) && !isset($_GET['chart'])) {
//     $sSession = $_GET['session'] ?? 0;
//     $grade_answers = Database::get()->queryArray("SELECT a.aid AS aid, b.weight AS wgt, a.poll_user_record_id AS poll_user_id
//                                 FROM poll_user_record c, poll_answer_record a
//                                 LEFT JOIN poll_question_answer b
//                                 ON a.aid = b.pqaid
//                                 WHERE a.qid IN (SELECT pqid FROM poll_question WHERE pid = ?d AND qtype = 1 OR QTYPE = 3)
//                                 AND a.poll_user_record_id = c.id
//                                 AND (c.email_verification = 1 OR c.email_verification IS NULL)
//                                 AND c.session_id = ?d", $pid, $sSession);

//     if (count($grade_answers) > 0) {
//         $userGrades = [];
//         $totalUserGrade = 0;
//         $user_ids_arr = [];
//         $pollOptionsArr = [];
//         $gradesArr = [];
//         $MsgGradesArr = [];
//         $minMsg = '';
//         foreach ($grade_answers as $gr) {
//             $userId = Database::get()->querySingle("SELECT `uid` FROM poll_user_record WHERE id = ?d", $gr->poll_user_id)->uid;
//             if (!in_array($userId, $user_ids_arr)) {
//                 $totalUserGrade = 0;
//             }
//             $totalUserGrade = $totalUserGrade + $gr->wgt;
//             $userGrades[$userId] = $totalUserGrade;
//             $user_ids_arr[] = $userId;
//         }
//         if ($pollOptions != '') {
//             $pollOptionsArr = unserialize($pollOptions);
//         }
//         if (count($pollOptionsArr) > 0) {
//             foreach ($pollOptionsArr as $opt) {
//                 $gradesArr[] = $opt['grade'];
//             }
//             rsort($gradesArr);
//             $minGrade = min($gradesArr);
//             foreach ($gradesArr as $gr) {
//                 foreach ($pollOptionsArr as $opt) {
//                     if ($opt['grade'] == $gr) {
//                         $MsgGradesArr[$gr] = $opt['message'];
//                     }
//                     if ($opt['grade'] == $minGrade) {
//                         $minMsg = $opt['message'];
//                     }
//                 }
//             }
//         }
//         if (count($MsgGradesArr) > 0 && count($userGrades) > 0) {
//             $tool_content .= "<div class='col-12 mt-4'>";
//             $tool_content .= "<div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>";
//             $tool_content .= "<div class='card-body'>";
//             $tool_content .= "<div class='panel'><div class='panel-group group-section' id='accordion' role='tablist' aria-multiselectable='true'>
//                             <ul class='list-group list-group-flush'>
//                             <li class='list-group-item px-0 bg-transparent'>
//                             <a class='accordion-btn d-flex justify-content-start align-items-start fs-6' role='button' data-bs-toggle='collapse' href='#ugrade' aria-expanded='false' aria-controls='#'>
//                                 <span class='fa-solid fa-chevron-down'></span>
//                                 $langUserGradesPoll
//                             </a>
//                             <div id='ugrade' class='panel-collapse accordion-collapse collapse border-0 rounded-0' role='tabpanel' data-bs-parent='#accordion'>
//                             <div class='panel-body bg-transparent Neutral-900-cl px-4'>
//                             <ul class='list-group list-group-flush'>";
//             $list_counter = 0;
//             foreach ($userGrades as $user_key => $ugr) {
//                 $lowGrade = 0;
//                 $userFullName = Database::get()->querySingle("SELECT CONCAT(user.surname, ' ', user.givenname) AS fullname FROM user WHERE id = ?d", $user_key)->fullname;
//                 if ($thePoll->anonymized == 1) {
//                     $userFullName = $langUser;
//                 }
//                 foreach ($MsgGradesArr as $key_grade => $val_msg) {
//                     if ($ugr >= $key_grade) {
//                         $tool_content .= "
//                         <li class='list-group-item element'>
//                             <div class='row row-cols-1 row-cols-md-2 g-1'>
//                                 <div class='col-md-3 col-12'>
//                                     <div class='title-default'><strong>$userFullName</strong></div>
//                                 </div>
//                                 <div class='col-md-9 col-12 title-default-line-height'>
//                                     $val_msg
//                                 </div>
//                             </div>
//                         </li>";
//                         $lowGrade++;
//                         break;
//                     }
//                 }
//                 if ($lowGrade == 0) {
//                     $tool_content .= "
//                     <li class='list-group-item element'>
//                         <div class='row row-cols-1 row-cols-md-2 g-1'>
//                             <div class='col-md-3 col-12'>
//                                 <div class='title-default'><strong>$userFullName</strong></div>
//                             </div>
//                             <div class='col-md-9 col-12 title-default-line-height'>
//                                 $minMsg
//                             </div>
//                         </div>
//                     </li>";
//                 }
//             }
//             $tool_content .= "</ul></div></div></li></ul></div></div></div></div></div>";
//         }
//     }
// }

if ($PollType == POLL_NORMAL || $PollType == POLL_QUICK || $PollType == POLL_COURSE_EVALUATION) {
    $loopTmp = 0;
    foreach ($questions as $theQuestion) {
        $ansExists = Database::get()->querySingle("SELECT arid FROM poll_answer_record WHERE qid = ?d", $theQuestion->pqid);
        if (!$ansExists) {
            continue;
        }
        $this_chart_data = array();
        if ($theQuestion->qtype == QTYPE_LABEL) {
            $tool_content .= "<div class='col-12 mt-3'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$theQuestion->question_text</span></div></div>";
        } else {

            if (isset($_GET['chart']) && 
                ($theQuestion->qtype == QTYPE_FILL || $theQuestion->qtype == QTYPE_DATETIME || $theQuestion->qtype == QTYPE_SHORT || $theQuestion->qtype == QTYPE_TABLE || $theQuestion->qtype == QTYPE_FILE || $theQuestion->qtype == QTYPE_DATE)) {
                continue;
            }

            $totalUserAnswer = total_number_of_users_answer_per_question($theQuestion->pqid);

            if ($totalUserAnswer == 1 && isset($_GET['from_session_view']) && isset($_GET['session']) && $loopTmp == 0) {
                $uInfo = Database::get()->querySingle("SELECT poll_user_record.uid,user.id,user.givenname,user.surname FROM poll_user_record
                                                        LEFT JOIN user ON poll_user_record.uid=user.id
                                                        WHERE poll_user_record.pid=?d AND poll_user_record.session_id=?d", $pid, $_GET['session']);
                $tool_content .= "<div class='card panelCard card-default my-4 px-lg-4'><div class='card-body'><h3 class='mb-0'>$langUser: <span>$uInfo->givenname $uInfo->surname</span></h3></div></div>";
                $loopTmp++;
            }

            $tool_content .= "
            <div class='col-12 mt-4'>
                <div class='card panelCard card-default card-poll-results poll-border-left border-0 px-lg-4 py-lg-3'>
                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                        <h3 class='d-flex justify-content-start align-items-start gap-2'>
                            <strong class='fs-6 text-nowrap'>$theQuestion->qnumber)</strong>
                            <strong class='fs-6'>$theQuestion->question_text</strong>
                        </h3>
                    </div>
                    <div class='card-body'>";
                        if ($theQuestion->qtype == QTYPE_MULTIPLE || $theQuestion->qtype == QTYPE_SINGLE) {

                            $sql_participants_a = '';
                            $sql_participants_b = '';
                            $sql_participants_c = '';
                            if (isset($_GET['from_session_view'])) {
                                $sql_participants_a = "AND c.uid IN (SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1) AND c.session_id = ?d";
                                $sql_participants_b = "AND uid IN (SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1) AND session_id = ?d";
                                $sql_participants_c = "AND poll_user_record.uid IN (SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1) AND poll_user_record.session_id = ?d";
                                $args_array = [$_GET['session'], $_GET['session']];
                            } else {
                                $args_array = [];
                            }

                            $sql_participants_d = '';
                            $sql_participants_e = '';
                            if (isset($_GET['res_per_u'])) {
                                $sql_participants_d = "AND c.uid=?d";
                                $sql_participants_e = "AND poll_user_record.uid=?d";
                                $args_array_d = [$_GET['res_per_u']];
                                $args_array_e = [$_GET['res_per_u']];
                            } else {
                                $args_array_d = [];
                                $args_array_e = [];
                            }

                            $names_array = [];
                            $all_answers = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d", $theQuestion->pqid);
                            foreach ($all_answers as $row) {
                                $this_chart_data['answer'][] = q($row->answer_text);
                                $this_chart_data['percentage'][] = 0;
                            }
                            $set_default_answer = false;
                            $answers = Database::get()->queryArray("SELECT a.aid AS aid, MAX(b.answer_text) AS answer_text, count(a.aid) AS count, b.weight AS wgt
                                        FROM poll_user_record c, poll_answer_record a
                                        LEFT JOIN poll_question_answer b
                                        ON a.aid = b.pqaid
                                        WHERE a.qid = ?d
                                        AND a.poll_user_record_id = c.id
                                        AND (c.email_verification = 1 OR c.email_verification IS NULL)
                                        $sql_participants_a
                                        $sql_participants_d
                                        GROUP BY a.aid ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid, $args_array, $args_array_d);
                            $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record, poll_user_record
                                                                                    WHERE poll_user_record_id = id
                                                                                    AND (email_verification=1 OR email_verification IS NULL)
                                                                                    $sql_participants_b
                                                                                    AND qid = ?d",  $args_array, $theQuestion->pqid)->total;

                            $answers_table = "
                                <div class='table-responsive mt-0'><table class='table-default table-poll-results'>
                                    <thead><tr class='list-header'>
                                        <th>$langAnswer</th>";  
                                        if (($totalUserAnswer > 1 && isset($_GET['from_session_view'])) or (!isset($_GET['from_session_view']))) {
                                            $answers_table .= "<th>$langSurveyTotalAnswers</th>";
                                            $answers_table .= "<th>$langPercentage</th>";
                                            if ($theQuestion->require_grade && $theQuestion->has_sub_question != -1) {
                                                $answers_table .= "<th>$langScore</th>";
                                            }
                                            if (!$thePoll->anonymized) {
                                                $answers_table .= "<th>$langStudents</th>";
                                            }
                                        }
                                        $answers_table .= "</tr></thead>";
                            foreach ($answers as $answer) {
                                $percentage = round(100 * ($answer->count / $answer_total),2);
                                if (isset($answer->answer_text)) {
                                    $q_answer = q_math($answer->answer_text);
                                    $aid = $answer->aid;
                                } else {
                                    $q_answer = $langPollUnknown;
                                    $aid = -1;
                                }
                                if (!$set_default_answer and (($theQuestion->qtype == QTYPE_SINGLE && $default_answer) or $aid == -1)) {
                                    $this_chart_data['answer'][] = $langPollUnknown;
                                    $this_chart_data['percentage'][] = 0;
                                }

                                if (isset($this_chart_data['answer'])) { // skip answers that don't exist
                                    $this_chart_data['percentage'][array_search($q_answer,$this_chart_data['answer'])] = $percentage;
                                }

                                if ($thePoll->anonymized != 1) {
                                    $names_str = $ellipsized_names_str = '';
                                    $names = Database::get()->queryArray("(SELECT CONCAT(user.surname, ' ', user.givenname) AS fullname,
                                                                        submit_date AS s
                                                                FROM poll_user_record, poll_answer_record, user
                                                                    WHERE poll_user_record.id = poll_answer_record.poll_user_record_id
                                                                    AND poll_answer_record.qid = ?d
                                                                    AND poll_answer_record.aid = ?d
                                                                    AND user.id = poll_user_record.uid
                                                                    $sql_participants_c
                                                                    $sql_participants_e)
                                                            UNION
                                                                (SELECT poll_user_record.email AS fullname, submit_date AS s
                                                                FROM poll_user_record, poll_answer_record
                                                                    WHERE poll_answer_record.qid = ?d
                                                                    AND poll_answer_record.aid = ?d
                                                                    AND poll_user_record.email IS NOT NULL
                                                                    AND poll_user_record.email_verification = 1
                                                                    AND poll_answer_record.poll_user_record_id = poll_user_record.id)
                                                                ORDER BY s DESC
                                                            ", $theQuestion->pqid, $aid,  $args_array, $args_array_e, $theQuestion->pqid, $aid);
                                    if (count($names) > 0) {
                                        foreach($names as $name) {
                                            $names_array[] = $name->fullname;
                                        }
                                        $names_str = implode(', ', $names_array);
                                        $ellipsized_names_str = q(ellipsize($names_str, 60));
                                    }
                                }

                                $answers_table .= "
                                    <tr>
                                            <td>$q_answer</td>";
                                            if (($totalUserAnswer > 1 && isset($_GET['from_session_view'])) or (!isset($_GET['from_session_view']))) {
                                                $answers_table .= "<td>$answer->count</td>";
                                                $answers_table .= "
                                                <td style='width:25%;'>
                                                    <div class='progress'>
                                                        <div class='progress-bar progress-bar-striped progress-bar-poll-results' role='progressbar' style='width: $percentage%;' aria-valuenow='$percentage' aria-valuemin='0' aria-valuemax='100'>
                                                        $percentage%
                                                        </div>
                                                    </div>
                                                </td>
                                                ";
                                                if ($theQuestion->require_grade && $theQuestion->has_sub_question != -1) {
                                                    $answers_table .= "<td><span class='level-badge'><i class='fa fa-star' style='color:#f59e0b;'></i>$answer->wgt</span></td>";
                                                }
                                                if (!$thePoll->anonymized) {
                                                    $answers_table .= "<td>" . ((isset($_GET['format']) && $_GET['format'] == 'poll_pdf') ? $names_str : $ellipsized_names_str);
                                                    if ($ellipsized_names_str != $names_str) {
                                                        $answers_table .= ' <a href="#" class="trigger_names" data-type="multiple" id="show">' . $langViewShow . '</a>';
                                                    }
                                                    $answers_table .= "</td>";
                                                }
                                            }
                                $answers_table .= "<td class='hidden_names' style='display:none;'><em>" . q($names_str ?? '') . "</em> <a href='#' class='trigger_names' data-type='multiple' id='hide'>$langViewHide</a></td>";
                                $answers_table .= "</tr>";
                                unset($names_array);
                                unset($dis_msg);
                            }
                            $answers_table .= "</table></div>";
                            /****   C3 plot   ****/
                            if (isset($_GET['chart'])) {
                                $tool_content .= "<script type = 'text/javascript'>pollChartData.push(".json_encode($this_chart_data).");</script>";
                                $tool_content .= "<div class='row plotscontainer mb-4'>";
                                $tool_content .= "<div class='col-lg-12'>";
                                $tool_content .= plot_placeholder("poll_chart$chart_counter", '');
                                $tool_content .= "</div></div>";
                                $chart_counter++;
                            } else {
                                $tool_content .= $answers_table;
                            }

                        } elseif ($theQuestion->qtype == QTYPE_SCALE) {

                            $answerScale = Database::get()->querySingle("SELECT answer_scale FROM poll_question WHERE pqid = ?d", $theQuestion->pqid)->answer_scale;
                            $arrAnsScale = explode('|', $answerScale);

                            $sql_participants_a = '';
                            $sql_participants_b = '';
                            $sql_participants_c = '';
                            if (isset($_GET['from_session_view'])) {
                                $sql_participants_a = "AND b.uid IN (SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1) AND b.session_id = ?d";
                                $sql_participants_b = "AND uid IN (SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1) AND session_id = ?d";
                                $sql_participants_c = "AND poll_user_record.uid IN (SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1) AND poll_user_record.session_id = ?d";
                                $args_array = [$_GET['session'], $_GET['session']];
                            } else {
                                $args_array = [];
                            }

                            $sql_participants_d = '';
                            $sql_participants_e = '';
                            if (isset($_GET['res_per_u'])) {
                                $sql_participants_d = "AND b.uid=?d";
                                $sql_participants_e = "AND poll_user_record.uid=?d";
                                $args_array_d = [$_GET['res_per_u']];
                                $args_array_e = [$_GET['res_per_u']];
                            } else {
                                $args_array_d = [];
                                $args_array_e = [];
                            }

                            $names_array = array();
                            $ans_scale = explode('|', $theQuestion->answer_scale);
                            foreach ($ans_scale as $an_text) {
                                $this_chart_data['answer_text'][] = "$an_text";
                            }
                            for ($i=1;$i<=$theQuestion->q_scale;$i++) {
                                $this_chart_data['answer'][] = "$i";
                                $this_chart_data['percentage'][] = 0;
                            }

                            $answers = Database::get()->queryArray("SELECT a.answer_text, count(a.answer_text) AS count
                                    FROM poll_answer_record a, poll_user_record b
                                    WHERE a.qid = ?d
                                    AND a.poll_user_record_id = b.id
                                    $sql_participants_a
                                    $sql_participants_d
                                    AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                    GROUP BY a.answer_text ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid, $args_array, $args_array_d);
                            $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record, poll_user_record
                                                                                    WHERE poll_user_record_id = id
                                                                                    AND (email_verification=1 OR email_verification IS NULL)
                                                                                    $sql_participants_b
                                                                                    AND qid = ?d", $args_array, $theQuestion->pqid)->total;

                            $answers_table = "
                                <div class='table-responsive mt-0'><table class='table-default table-poll-results'>
                                        <thead>
                                            <tr class='list-header'>
                                                <th>$langAnswer</th>";
                                        if (($totalUserAnswer > 1 && isset($_GET['from_session_view'])) or (!isset($_GET['from_session_view']))) {
                            $answers_table .= " <th>$langSurveyTotalAnswers</th>
                                                <th>$langPercentage</th>
                                            " . (($thePoll->anonymized == 1) ? '' : '<th>' . $langStudents . '</th>') . "";
                                        }
                        $answers_table .= "</tr>
                                        </thead>";
                            foreach ($answers as $answer) {
                                $percentage = round(100 * ($answer->count / $answer_total),2);
                                if (!is_null($this_chart_data['answer'])) {
                                    $this_chart_data['percentage'][array_search($answer->answer_text, $this_chart_data['answer'])] = $percentage;
                                }
                                if ($thePoll->anonymized != 1) {
                                    $names_str = $ellipsized_names_str = '';
                                    // Gets names for registered users and emails for unregistered
                                    $names = Database::get()->queryArray("(SELECT CONCAT(user.surname, ' ', user.givenname) AS fullname,
                                                                        submit_date AS s
                                                                FROM poll_user_record, poll_answer_record, user
                                                                    WHERE poll_user_record.id = poll_answer_record.poll_user_record_id
                                                                    AND poll_answer_record.qid = ?d
                                                                    AND poll_answer_record.answer_text = ?s
                                                                    AND user.id = poll_user_record.uid
                                                                    $sql_participants_c
                                                                    $sql_participants_e)
                                                            UNION
                                                                (SELECT poll_user_record.email AS fullname, submit_date AS s
                                                                FROM poll_user_record, poll_answer_record
                                                                    WHERE poll_answer_record.qid = ?d
                                                                    AND poll_answer_record.answer_text = ?s
                                                                    AND poll_user_record.email IS NOT NULL
                                                                    AND poll_user_record.email_verification = 1
                                                                    AND poll_answer_record.poll_user_record_id = poll_user_record.id)
                                                                ORDER BY s DESC
                                                            ", $theQuestion->pqid, $answer->answer_text, $args_array, $args_array_e, $theQuestion->pqid, $answer->answer_text);
                                    if (count($names) > 0) {
                                        foreach ($names as $name) {
                                            $names_array[] = $name->fullname;
                                        }
                                        $names_str = implode(', ', $names_array);
                                        $ellipsized_names_str = q(ellipsize($names_str, 60));
                                    }
                                }
                                $answers_table .= "
                                            <tr>
                                                <td>" . $arrAnsScale[$answer->answer_text-1] ?? q($answer->answer_text) . "</td>";
                                        if (($totalUserAnswer > 1 && isset($_GET['from_session_view'])) or (!isset($_GET['from_session_view']))) {
                            $answers_table .= " <td>$answer->count</td>
                                                <td style='width:25%;'>
                                                    <div class='progress'>
                                                        <div class='progress-bar progress-bar-striped progress-bar-poll-results' role='progressbar' style='width: $percentage%;' aria-valuenow='$percentage' aria-valuemin='0' aria-valuemax='100'>
                                                        $percentage%
                                                        </div>
                                                    </div>
                                                </td>"
                                            . (($thePoll->anonymized == 1) ?
                                                '' :
                                                '<td>'.((isset($_GET['format']) && $_GET['format'] == 'poll_pdf') ? $names_str : $ellipsized_names_str).
                                                (($ellipsized_names_str != $names_str)? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$langViewShow.'</a>' : '').
                                                '</td>
                                                <td class="hidden_names" style="display:none;"><em>'
                                                . q($names_str) .
                                                '</em> <a href="#" class="trigger_names" data-type="multiple" id="hide">'.$langViewHide.'</a>
                                                </td>').
                                            "</tr>";
                                        }
                                unset($names_array);
                            }
                            $answers_table .= "</table></div>";
                            if (isset($_GET['chart'])) {
                                /****   C3 plot   ****/
                                $chart_data[] = $this_chart_data;
                                $tool_content .= "<script type = 'text/javascript'>pollChartData.push(".json_encode($this_chart_data).");</script>";
                                $tool_content .= "<div class='row plotscontainer mb-4'>";
                                $tool_content .= "<div class='col-lg-12'>";
                                $tool_content .= plot_placeholder("poll_chart$chart_counter", '');
                                $tool_content .= "</div></div>";
                                $chart_counter++;
                            } else {
                                $tool_content .= $answers_table;
                            }

                        } elseif ($theQuestion->qtype == QTYPE_FILL || $theQuestion->qtype == QTYPE_DATETIME || $theQuestion->qtype == QTYPE_SHORT || $theQuestion->qtype == QTYPE_FILE || $theQuestion->qtype == QTYPE_DATE) {

                            $sql_participants_a = '';
                            $sql_participants_c = '';
                            if (isset($_GET['from_session_view'])) {
                                $sql_participants_a = "AND b.uid IN (SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1) AND b.session_id = ?d";
                                $sql_participants_c = "AND poll_user_record.uid IN (SELECT participants FROM mod_session_users WHERE session_id = ?d AND is_accepted = 1) AND poll_user_record.session_id = ?d";
                                $args_array = [$_GET['session'], $_GET['session']];
                            } else {
                                $args_array = [];
                            }

                            $sql_participants_d = '';
                            $sql_participants_e = '';
                            if (isset($_GET['res_per_u'])) {
                                $sql_participants_d = "AND b.uid=?d";
                                $sql_participants_e = "AND poll_user_record.uid=?d";
                                $args_array_d = [$_GET['res_per_u']];
                                $args_array_e = [$_GET['res_per_u']];
                            } else {
                                $args_array_d = [];
                                $args_array_e = [];
                            }

                            // $tool_content .= "<div class='panel-body'>";
                            // $tool_content .= "<div class='inner-heading'>$theQuestion->question_text</div>";
                            // $tool_content .= "</div>";
                            $names_array = [];
                            $answers = Database::get()->queryArray("SELECT COUNT(a.arid) AS count, a.answer_text, b.uid
                                                        FROM poll_answer_record a, poll_user_record b
                                                        WHERE a.qid = ?d
                                                        AND a.poll_user_record_id = b.id
                                                        $sql_participants_a
                                                        $sql_participants_d
                                                        AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                                        GROUP BY a.answer_text, b.uid ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid, $args_array, $args_array_d);
                            $answers_table = "<div class='table-responsive mt-0'><table class='table-default table-poll-results'>
                                    <tbody>
                                    <tr class='list-header'>
                                            <th>$langAnswer</th>
                                            <th>$langSurveyTotalAnswers</th>
                                            ".(($thePoll->anonymized == 1)?'':'<th>'.$langStudents.'</th>')."
                                    </tr>";
                            $k=1;
                            foreach ($answers as $answer) {
                                if (!$thePoll->anonymized) {
                                    // Gets names for registered users and emails for unregistered

                                    $names = Database::get()->queryArray("(SELECT CONCAT(user.surname, ' ', user.givenname) AS fullname,
                                                                        submit_date AS s
                                                                FROM poll_user_record, poll_answer_record, user
                                                                    WHERE poll_user_record.id = poll_answer_record.poll_user_record_id
                                                                    AND poll_answer_record.qid = ?d
                                                                    AND poll_answer_record.answer_text = ?s
                                                                    AND user.id = poll_user_record.uid
                                                                    $sql_participants_c
                                                                    $sql_participants_e)
                                                            UNION
                                                                (SELECT poll_user_record.email AS fullname, submit_date AS s
                                                                FROM poll_user_record, poll_answer_record
                                                                    WHERE poll_answer_record.qid = ?d
                                                                    AND poll_answer_record.answer_text = ?s
                                                                    AND poll_user_record.email IS NOT NULL
                                                                    AND poll_user_record.email_verification = 1
                                                                    AND poll_answer_record.poll_user_record_id = poll_user_record.id)
                                                                ORDER BY s DESC
                                                            ", $theQuestion->pqid, $answer->answer_text, $args_array, $args_array_e, $theQuestion->pqid, $answer->answer_text);
                                    foreach($names as $name) {
                                        $names_array[] = $name->fullname;
                                    }
                                    $names_str = implode(', ', $names_array);
                                    $ellipsized_names_str = q(ellipsize($names_str, 60));
                                }
                                $row_class = ($k>3) ? 'class="hidden_row" style="display:none;"' : '';
                                $extra_column = (!$thePoll->anonymized)?
                                    "<td>"
                                    . ((isset($_GET['format']) && $_GET['format'] == 'poll_pdf') ? $names_str : $ellipsized_names_str)
                                    . (($ellipsized_names_str != $names_str) ? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$langViewShow.'</a>' : '').
                                    "</td>
                                        <td class='hidden_names' style='display:none;'><em>"
                                    . q($names_str) .
                                    "</em> <a href='#' class='trigger_names' data-type='multiple' id='hide'>".$langViewHide."</a>
                                    </td>" : "";
                                $uAnswerText = q($answer->answer_text);
                                if ($theQuestion->qtype == QTYPE_FILE) {
                                    $arrFile = unserialize($answer->answer_text);
                                    $filename = $arrFile['filename'];
                                    $filepath = $arrFile['filepath'];
                                    $userID = $uid;
                                    if ($is_editor or $is_consultant) {
                                        $userID = $answer->uid;
                                    }
                                    $Qid = $theQuestion->pqid;
                                    if ($is_editor or $is_consultant) {
                                        if (!file_exists("$webDir/courses/$course_code/poll_$pid/$userID/$Qid/$sID$filepath")) {
                                            $uAnswerText = "<p class='text-decoration-line-through text-danger'>$filename</p>";
                                        } else {
                                            $uAnswerText = "<a target='_blank' href='{$urlServer}courses/$course_code/poll_$pid/$userID/$Qid/$sID$filepath'>$filename</a>";
                                        }
                                    } else {
                                        if (!file_exists("$webDir/courses/$course_code/poll_$pid/$userID/$Qid/$sID$filepath")) {
                                            $uAnswerText = "<p>$filename</p>";
                                        } else {
                                            $uAnswerText = "<a target='_blank' href='{$urlServer}courses/$course_code/poll_$pid/$userID/$Qid/$sID$filepath'>$filename</a>";
                                        }
                                    }
                                }
                                $answers_table .= "
                                    <tr $row_class>
                                            <td>$uAnswerText</td>
                                            <td>$answer->count</td>
                                            $extra_column
                                    </tr>";
                                $k++;
                                if (!$thePoll->anonymized) {
                                    unset($names_array);
                                }
                            }
                            if ($k>4) {
                                $answers_table .= "
                                <tr>
                                    <td colspan='".($thePoll->anonymized ? 2 : 3)."'><a href='#' class='trigger_names' data-type='fill' id='show'>$langViewShow</a></td>
                                </tr>";
                            }
                            $answers_table .= '</tbody></table></div>';
                            if (isset($_GET['chart'])) {

                            } else {
                                $tool_content .= $answers_table;
                            }
                        } elseif ($theQuestion->qtype == QTYPE_TABLE) {
                            // $tool_content .= "<div class='panel-body'>
                            //                     <div class='inner-heading'><strong>$theQuestion->question_text</strong></div>
                            //                 </div>";

                            $sql_participants_d = '';
                            if (isset($_GET['res_per_u'])) {
                                $sql_participants_d = "AND poll_user_record.uid=?d";
                                $args_array_d = [$_GET['res_per_u']];
                            } else {
                                $args_array_d = [];
                            }

                            $s_id = $_GET['session'] ?? 0;
                            $answers = Database::get()->queryArray("SELECT poll_answer_record.poll_user_record_id,
                                                                        poll_answer_record.qid,
                                                                        poll_answer_record.answer_text,
                                                                        poll_answer_record.sub_qid,
                                                                        poll_answer_record.sub_qid_row,
                                                                        poll_user_record.id,
                                                                        poll_user_record.uid,
                                                                        poll_user_record.session_id,
                                                                        poll_question_answer.answer_text as sub_question_text,
                                                                        poll_question_answer.sub_question FROM poll_answer_record
                                                                            INNER JOIN poll_user_record ON poll_answer_record.poll_user_record_id=poll_user_record.id
                                                                            INNER JOIN poll_question_answer ON poll_answer_record.sub_qid=poll_question_answer.sub_question
                                                                            WHERE poll_answer_record.qid = ?d 
                                                                            AND poll_user_record.pid = ?d
                                                                            $sql_participants_d
                                                                            AND poll_user_record.session_id = ?d
                                                                            AND poll_question_answer.pqid = ?d", $theQuestion->pqid, $pid, $args_array_d, $s_id, $theQuestion->pqid);

                            if (count($all_participants) > 0 && count($answers) > 0) {
                                $sub_questions = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d",$theQuestion->pqid);
                                foreach ($all_participants as $p) {
                                    $pollUserR = Database::get()->querySingle("SELECT id FROM poll_user_record WHERE pid = ?d AND session_id = ?d AND uid = ?d", $pid, $s_id, $p->participants);
                                    $displayUser = false;
                                    foreach ($sub_questions as $s) {
                                        if ($pollUserR) {
                                            $check = Database::get()->queryArray("SELECT * FROM poll_answer_record
                                                                                    WHERE poll_user_record_id = ?d
                                                                                    AND qid = ?d
                                                                                    AND sub_qid = ?d", $pollUserR->id, $s->pqid, $s->sub_question);

                                            if (count($check) > 0) {
                                                $displayUser = true;
                                            }
                                        }
                                    }
                                    if ($displayUser) {
                                        $answers_table ="<div class='card panelCard card-default card-user-answers mb-4'>";
                                        if (!$thePoll->anonymized) {
                                            $answers_table .= "<div class='card-header'>
                                                                <h3 style='margin-bottom:0px;'>$p->givenname&nbsp;$p->surname</h3>
                                                            </div>";
                                        }       
                                        $answers_table .= " <div class='card-body'>";   
                                                $answers_table .= "  <table class='table-default'><tr>";
                                                                        foreach ($sub_questions as $s) {
                                                                            $displayItem = false;
                                                                            if ($pollUserR) {
                                                                                $check = Database::get()->queryArray("SELECT * FROM poll_answer_record
                                                                                                                        WHERE poll_user_record_id = ?d
                                                                                                                        AND qid = ?d
                                                                                                                        AND sub_qid = ?d", $pollUserR->id, $s->pqid, $s->sub_question);

                                                                                if (count($check) > 0) {
                                                                                    $displayItem = true;
                                                                                }
                                                                            }
                                                                            if ($displayItem) {
                                                            $answers_table .= "<td><ul class='list-group list-group-flush w-100'>
                                                                                    <h5 style='margin-bottom:0px; word-break: normal; overflow-wrap: break-word;'>$s->answer_text</h5>";
                                                                                foreach ($answers as $a) {
                                                                                    if ($p->participants == $a->uid && $theQuestion->pqid == $a->qid && 
                                                                                            $s->sub_question == $a->sub_question) {
                                                                                                $answers_table .= "<li class='list-group-item element px-0'>$a->answer_text</li>";
                                                                                    }
                                                                                }
                                                            $answers_table .= "</ul></td>";
                                                                            }
                                                                        }                           
                                        $answers_table .= "          
                                                                    </tr></table>    
                                                            </div>
                                                        </div>";

                                        if (isset($_GET['chart'])) {

                                        } else {
                                            $tool_content .= $answers_table;
                                        }
                                    }
                                }
                            } else {
                                $tool_content .= "<div class='alert alert-warning'>
                                                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                                        <span>$langNoAnswers</span>
                                                    </div>";
                            }
                        }
            $tool_content .= "</div></div></div>";// col-12
        }
    }
} elseif ($PollType == POLL_COLLES) {
    redirect_to_home_page("modules/questionnaire/colles.php?course=$course_code&pid=$pid");
} elseif ($PollType == POLL_ATTLS) {
    redirect_to_home_page("modules/questionnaire/attls.php?course=$course_code&pid=$pid");
}

if (isset($_GET['format']) and $_GET['format'] == 'pdf') { // pdf format
    $sid = $_GET['session'];
    pdf_session_poll_output($sid);
} elseif (isset($_GET['format']) and $_GET['format'] == 'poll_pdf') {
    pdf_poll_output();
} else{
    // display page
    draw($tool_content, 2, null, $head_content);
}




/**
 * @brief output to pdf file
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_poll_output() {
    global $tool_content, $currentCourseName, $webDir, $course_id, $course_code;

    $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName") . "</title>
          <style>
            * { font-family: 'opensans'; }
            body { font-family: 'opensans'; font-size: 10pt; }
            small, .small { font-size: 8pt; }
            h1, h2, h3, h4 { font-family: 'roboto'; margin: .8em 0 0; }
            h1 { font-size: 16pt; }
            h2 { font-size: 12pt; border-bottom: 1px solid black; }
            h3 { font-size: 10pt; color: #158; border-bottom: 1px solid #158; }
            th { text-align: left; border-bottom: 1px solid #999; }
            td { text-align: left; }
            .ButtonsContent{ display: none; }
            .hidden_names{ display: none; }
            .trigger_names{display: none;}
            #hide{ display: none; }
            em{ display: none; }
            .hidden-element { display: none; }
            td ul { list-style: none !important; padding-left: 0 !important; margin-left: 0 !important; }
            ul { list-style: none !important; padding-left: 0 !important; margin-left: 0 !important; }
            li { list-style: none !important; }
            .card-user-answers { background-color: #eeeeee; padding: 0px 25px 20px 25px; margin-top: 15px; margin-bottom: 10px;}
          </style>
        </head>
        <body>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>";

    $pdf_content .= $tool_content;

    $pdf_content .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'margin_top' => 63,     // approx 200px
        'margin_bottom' => 63,  // approx 200px
        'tempDir' => _MPDF_TEMP_PATH,
        'fontDir' => array_merge($fontDirs, [ $webDir . '/template/modern/fonts' ]),
        'fontdata' => $fontData + [
                'opensans' => [
                    'R' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-regular.ttf',
                    'B' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700.ttf',
                    'I' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-italic.ttf',
                    'BI' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700italic.ttf'
                ],
                'roboto' => [
                    'R' => 'roboto-v15-latin_greek_cyrillic_greek-ext-regular.ttf',
                    'I' => 'roboto-v15-latin_greek_cyrillic_greek-ext-italic.ttf',
                ]
            ]
    ]);

    $mpdf->SetHTMLHeader(get_platform_logo());
    $footerHtml = '
    <div>
        <table width="100%" style="border: none;">
            <tr>
                <td style="text-align: left;">{DATE j-n-Y}</td>
                <td style="text-align: right;">{PAGENO} / {nb}</td>
            </tr>
        </table>
    </div>
    ' . get_platform_logo('','footer') . '';
    $mpdf->SetHTMLFooter($footerHtml);
    $mpdf->SetCreator(course_id_to_prof($course_id));
    $mpdf->SetAuthor(course_id_to_prof($course_id));
    $mpdf->WriteHTML($pdf_content);
    $mpdf->Output("$course_code poll_results.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}

/**
 * @brief output to pdf file
 * @return void
 * @throws \Mpdf\MpdfException
 */
function pdf_session_poll_output($sid) {
    global $tool_content, $currentCourseName, $webDir, $course_id, $course_code, $language;

    $sessionTitle = Database::get()->querySingle("SELECT title FROM mod_session WHERE id = ?d AND course_id = ?d", $sid, $course_id)->title;

    $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName") . "</title>
          <style>
            * { font-family: 'opensans'; }
            body { font-family: 'opensans'; font-size: 10pt; }
            small, .small { font-size: 8pt; }
            h1, h2, h3, h4 { font-family: 'roboto'; margin: .8em 0 0; }
            h1 { font-size: 16pt; }
            h2 { font-size: 12pt; border-bottom: 1px solid black; }
            h3 { font-size: 10pt; color: #158; border-bottom: 1px solid #158; }
            th { text-align: left; border-bottom: 1px solid #999; }
            td { text-align: left; }
            .card-poll-results {border-left: 4px solid rgb(255, 255, 255) !important;}
            table {min-width:100% !important;}
            .ButtonsContent{ display: none; }
            .hidden_names{ display: none; }
            #hide{ display: none; }
            em{ display: none; }
            .hidden-element { display: none; }
            .pollTotalAnswers{ display: block;}
            td ul { list-style: none !important; padding-left: 0 !important; margin-left: 0 !important; }
            ul { list-style: none !important; padding-left: 0 !important; margin-left: 0 !important; }
            li { list-style: none !important; }
            .card-user-answers { background-color: #eeeeee; padding: 0px 25px 20px 25px; margin-top: 15px; margin-bottom: 10px;}
          </style>
        </head>
        <body>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($sessionTitle) . "</h2>";

    $pdf_content .= $tool_content;

    $pdf_content .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'margin_top' => 63,     // approx 200px
        'margin_bottom' => 63,  // approx 200px
        'tempDir' => _MPDF_TEMP_PATH,
        'fontDir' => array_merge($fontDirs, [ $webDir . '/template/modern/fonts' ]),
        'fontdata' => $fontData + [
                'opensans' => [
                    'R' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-regular.ttf',
                    'B' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700.ttf',
                    'I' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-italic.ttf',
                    'BI' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700italic.ttf'
                ],
                'roboto' => [
                    'R' => 'roboto-v15-latin_greek_cyrillic_greek-ext-regular.ttf',
                    'I' => 'roboto-v15-latin_greek_cyrillic_greek-ext-italic.ttf',
                ]
            ]
    ]);

    $mpdf->SetHTMLHeader(get_platform_logo());
    $footerHtml = '
    <div>
        <table width="100%" style="border: none;">
            <tr>
                <td style="text-align: left;">{DATE j-n-Y}</td>
                <td style="text-align: right;">{PAGENO} / {nb}</td>
            </tr>
        </table>
    </div>
    ' . get_platform_logo('','footer') . '';
    $mpdf->SetHTMLFooter($footerHtml);
    $mpdf->SetCreator(course_id_to_prof($course_id));
    $mpdf->SetAuthor(course_id_to_prof($course_id));
    $mpdf->WriteHTML($pdf_content);
    $mpdf->Output("$course_code poll_results.pdf", 'I'); // 'D' or 'I' for download / inline display
    exit;
}


/**
 * @brief Return the total number of user's answers per session.
 */
function total_number_of_users_answer_per_question($qid) {

    $total = 0;
    $sid = $_GET['session'] ?? 0;
    $pid = $_GET['pid'] ?? 0;

    if ($sid > 0 && $pid > 0 && isset($_GET['from_session_view'])) {
        $poll_user_record_ids = Database::get()->queryArray("SELECT id FROM poll_user_record WHERE pid = ?d AND session_id = ?d", $pid, $sid);
        if (count($poll_user_record_ids) > 0) {
            foreach ($poll_user_record_ids as $ur) {
                $checker = Database::get()->querySingle("SELECT arid FROM poll_answer_record WHERE poll_user_record_id = ?d AND qid = ?d", $ur->id, $qid);
                if ($checker) {
                    $total++;
                }
            }
        }
    }

    return $total;
}

function poll_user_participation() {
    global $course_id;

    $poll = Database::get()->querySingle('SELECT * FROM poll WHERE course_id = ?d AND pid = ?d', $course_id, $_GET['pid']);
    $allUsers = [];

    $sid = $_GET['session'] ?? 0;

    if ($poll->assign_to_specific) {
        $assign = Database::get()->queryArray('SELECT * FROM poll_to_specific
            WHERE poll_id = ?d', $poll->pid);
        foreach ($assign as $item) {
            if ($item->user_id) {
                $allUsers[] = $item->user_id;
            } elseif ($item->group_id) {
                $group_members = Database::get()->queryArray('SELECT user_id
                    FROM group_members WHERE is_tutor = 0 AND group_id = ?d',
                    $item->group_id);
                foreach ($group_members as $member) {
                    $allUsers[] = $member->user_id;
                }
            }
        }
    } else {
        $allUsers = Database::get()->queryArray('SELECT user_id FROM course_user
            WHERE course_id = ?d AND editor = 0 AND status = ' . USER_STUDENT,
            $course_id);
        $allUsers = array_map(function ($user) {
            return $user->user_id;
        }, $allUsers);
    }

    $polledUsers = Database::get()->queryArray('SELECT id, uid, email, email_verification FROM poll_user_record WHERE pid = ?d AND session_id = ?d', $poll->pid, $sid);
    $okUsers = [];
    $emailUsers = [];
    $timestamp = [];
    foreach ($polledUsers as $user) {
        $ts = Database::get()->querySingle('SELECT submit_date
                FROM poll_answer_record WHERE poll_user_record_id = ?d LIMIT 1',
                $user->id)->submit_date;
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