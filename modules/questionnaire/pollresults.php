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
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.4.10/c3.css' />";
load_js('d3/d3.min.js');
load_js('c3-0.4.10/c3.min.js');

$toolName = $langQuestionnaire;
$pageName = $langPollCharts;

// view statistics by a consultant
if (isset($_GET['from_session_view'])) {
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
            options = {
                data: {
                    json: pollChartData[i],
                    x: 'answer',
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

$sqlParticipants = '';
if (isset($_GET['from_session_view'])) {
    $sqlParticipants = "AND uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND session_id = $_GET[session]";
}
$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL) $sqlParticipants", $pid)->total;
if (!$total_participants) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
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
    $action_bar = action_bar(array(
                    array(
                        'title' => $langBack,
                        'url' => "$back_link",
                        'icon' => 'fa-reply',
                        'level' => 'primary',
                        'show' => isset($_REQUEST['unit_id'])
                    ),
                    array('title' => "$langPollPercentResults ($langDumpExcel)",
                          'url' => "dumppollresults.php?course=$course_code&amp;pid=$pid",
                          'icon' => 'fa-file-excel',
                          'level' => 'primary-label',
                          'show' => $is_course_reviewer),
                    array('title' => "$langPollPercentResults ($langDumpPDF)",
                        'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid&amp;format=poll_pdf",
                        'icon' => 'fa-file-pdf',
                        'level' => 'primary-label',
                        'show' => $is_course_reviewer),
                    array('title' => $langPollFullResults,
                          'url' => "dumppollresults.php?course=$course_code&amp;pid=$pid&amp;full=1",
                          'icon' => 'fa-download',
                          'level' => 'primary-label',
                          'show' => $is_course_reviewer)
                ));
}
$tool_content .= $action_bar;

$tool_content .= "<div class='col-12'>
<div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'>
        <h3>$langInfoPoll</h3>

    </div>
    <div class='card-body'>
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

            <li class='list-group-item element $hidden_elements'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$langPollTotalAnswers:</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        $total_participants
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
</div>";

$questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position ASC", $pid);
$j=1;
$chart_data = [];
$chart_counter = 0;

$all_participants = [];
if (isset($_GET['from_session_view'])) { //session view
    $all_participants = Database::get()->queryArray("SELECT user.id,user.givenname,user.surname,mod_session_users.participants FROM mod_session_users
                                                     LEFT JOIN user ON user.id=mod_session_users.participants
                                                     WHERE mod_session_users.session_id = $_GET[session] AND mod_session_users.is_accepted = 1
                                                     AND mod_session_users.participants IN (SELECT uid FROM poll_user_record WHERE pid = $pid AND session_id = $_GET[session])");
} else { // course view
    $all_users_participants = Database::get()->queryArray("SELECT user.id,user.givenname,user.surname,poll_user_record.uid FROM poll_user_record
                                                           LEFT JOIN user ON user.id=poll_user_record.uid
                                                           WHERE poll_user_record.pid = ?d AND poll_user_record.session_id = ?d", $pid, 0);
    foreach ($all_users_participants as $p) {
        $all_participants[] = (object) array("participants" => $p->uid, "givenname" => $p->givenname, "surname" => $p->surname);
    }
}

if ($PollType == POLL_NORMAL || $PollType == POLL_QUICK || $PollType == POLL_COURSE_EVALUATION) {
    $loopTmp = 0;
    $pollOptionsArr = [];
    $gradesArr = [];
    $MsgGradesArr = [];
    $minMsg = '';
    $isEnabledGrade = pollHasGrade($pid);
    if ($pollOptions != '') {
        $pollOptionsArr = unserialize($pollOptions);
    }
    if (count($pollOptionsArr) > 0) {
        foreach ($pollOptionsArr as $opt) {
            $gradesArr[] = $opt['grade'];
        }
        rsort($gradesArr);
        $minGrade = min($gradesArr);
        foreach ($gradesArr as $gr) {
            foreach ($pollOptionsArr as $opt) {
                if ($opt['grade'] == $gr) {
                    $MsgGradesArr[$gr] = $opt['message'];
                }
                if ($opt['grade'] == $minGrade) {
                    $minMsg = $opt['message'];
                }
            }
        }
    }
    foreach ($questions as $theQuestion) {
        $ansExists = Database::get()->querySingle("SELECT arid FROM poll_answer_record WHERE qid = ?d", $theQuestion->pqid);
        if (!$ansExists) {
            continue;
        }
        $this_chart_data = array();
        if ($theQuestion->qtype == QTYPE_LABEL) {
            $tool_content .= "<div class='col-12 mt-3'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$theQuestion->question_text</span></div></div>";
        } else {

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
            <div class='card panelCard card-default px-lg-4 py-lg-3'>
                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                    <h3>$langQuestion " . (isset($_GET['from_session_view']) ? $theQuestion->q_position : $j) . "</h3>
                </div>
                <div class='card-body'>";

            $j++;

            if ($theQuestion->qtype == QTYPE_MULTIPLE || $theQuestion->qtype == QTYPE_SINGLE) {

                $sql_participants_a = '';
                $sql_participants_b = '';
                $sql_participants_c = '';
                if (isset($_GET['from_session_view'])) {
                    $sql_participants_a = "AND c.uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND c.session_id = $_GET[session]";
                    $sql_participants_b = "AND uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND session_id = $_GET[session]";
                    $sql_participants_c = "AND poll_user_record.uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND poll_user_record.session_id = $_GET[session]";
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
                            GROUP BY a.aid ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid);
                $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record, poll_user_record
                                                                        WHERE poll_user_record_id = id
                                                                        AND (email_verification=1 OR email_verification IS NULL)
                                                                        $sql_participants_b
                                                                        AND qid= ?d", $theQuestion->pqid)->total;

                $answers_table = "
                    <div class='table-responsive'><table class='table-default'>
                        <thead><tr class='list-header'>
                            <th>$langAnswer</th>";  
                            if (($totalUserAnswer > 1 && isset($_GET['from_session_view'])) or (!isset($_GET['from_session_view']))) {
                                $answers_table .= "<th>$langSurveyTotalAnswers</th>";
                                $answers_table .= "<th>$langPercentage</th>";
                                if (!$thePoll->anonymized) {
                                    $answers_table .= "<th>$langStudents</th>";
                                }
                                if ($isEnabledGrade) {
                                 $answers_table .= "<th>$langMessage</th>";
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
                                                        $sql_participants_c)
                                                UNION
                                                    (SELECT poll_user_record.email AS fullname, submit_date AS s
                                                    FROM poll_user_record, poll_answer_record
                                                        WHERE poll_answer_record.qid = ?d
                                                        AND poll_answer_record.aid = ?d
                                                        AND poll_user_record.email IS NOT NULL
                                                        AND poll_user_record.email_verification = 1
                                                        AND poll_answer_record.poll_user_record_id = poll_user_record.id)
                                                    ORDER BY s DESC
                                                ", $theQuestion->pqid, $aid, $theQuestion->pqid, $aid);
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
                                    $answers_table .= "<td>$percentage%</td>";
                                    if (!$thePoll->anonymized) {
                                        $answers_table .= "<td>" . $ellipsized_names_str;
                                        if ($ellipsized_names_str != $names_str) {
                                            $answers_table .= ' <a href="#" class="trigger_names" data-type="multiple" id="show">' . $langViewShow . '</a>';
                                        }
                                        $answers_table .= "</td>";
                                    }
                                }

                    if (count($MsgGradesArr) > 0) {
                        foreach ($MsgGradesArr as $key_grade => $val_msg) {
                            if ($answer->wgt >= $key_grade) {
                                $dis_msg = $val_msg;
                                break;
                            }
                        }
                    }
                    if (!isset($dis_msg)) {
                        $dis_msg = $minMsg;
                    }

                    $answers_table .= "<td class='hidden_names' style='display:none;'><em>" . q($names_str ?? '') . "</em> <a href='#' class='trigger_names' data-type='multiple' id='hide'>$langViewHide</a></td>";
                    if ($isEnabledGrade) {
                        $answers_table .= "<td>$dis_msg</td>";
                    }
                    $answers_table .= "</tr>";
                    unset($names_array);
                    unset($dis_msg);
                }
                $answers_table .= "</table></div><br>";
                $tool_content .= "<script type = 'text/javascript'>pollChartData.push(".json_encode($this_chart_data).");</script>";
                /****   C3 plot   ****/
                $tool_content .= "<div class='row plotscontainer mb-4'>";
                $tool_content .= "<div class='col-lg-12'>";
                $tool_content .= plot_placeholder("poll_chart$chart_counter", q_math($theQuestion->question_text));
                $tool_content .= "</div></div>";
                $tool_content .= $answers_table;
                $chart_counter++;
            } elseif ($theQuestion->qtype == QTYPE_SCALE) {

                $answerScale = Database::get()->querySingle("SELECT answer_scale FROM poll_question WHERE pqid = ?d", $theQuestion->pqid)->answer_scale;
                $arrAnsScale = explode('|', $answerScale);

                $sql_participants_a = '';
                $sql_participants_b = '';
                $sql_participants_c = '';
                if (isset($_GET['from_session_view'])) {
                    $sql_participants_a = "AND b.uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND b.session_id = $_GET[session]";
                    $sql_participants_b = "AND uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND session_id = $_GET[session]";
                    $sql_participants_c = "AND poll_user_record.uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND poll_user_record.session_id = $_GET[session]";
                }

                $names_array = array();
                for ($i=1;$i<=$theQuestion->q_scale;$i++) {
                    $this_chart_data['answer'][] = "$i";
                    $this_chart_data['percentage'][] = 0;
                }

                $answers = Database::get()->queryArray("SELECT a.answer_text, count(a.answer_text) AS count
                        FROM poll_answer_record a, poll_user_record b
                        WHERE a.qid = ?d
                        AND a.poll_user_record_id = b.id
                        $sql_participants_a
                        AND (b.email_verification = 1 OR b.email_verification IS NULL)
                        GROUP BY a.answer_text ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid);
                $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record, poll_user_record
                                                                        WHERE poll_user_record_id = id
                                                                        AND (email_verification=1 OR email_verification IS NULL)
                                                                        $sql_participants_b
                                                                        AND qid= ?d", $theQuestion->pqid)->total;

                $answers_table = "
                    <div class='table-responsive'><table class='table-default'>
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
                                                        $sql_participants_c)
                                                UNION
                                                    (SELECT poll_user_record.email AS fullname, submit_date AS s
                                                    FROM poll_user_record, poll_answer_record
                                                        WHERE poll_answer_record.qid = ?d
                                                        AND poll_answer_record.answer_text = ?s
                                                        AND poll_user_record.email IS NOT NULL
                                                        AND poll_user_record.email_verification = 1
                                                        AND poll_answer_record.poll_user_record_id = poll_user_record.id)
                                                    ORDER BY s DESC
                                                ", $theQuestion->pqid, $answer->answer_text, $theQuestion->pqid, $answer->answer_text);
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
                                    <td>$percentage%</td>"
                                . (($thePoll->anonymized == 1) ?
                                    '' :
                                    '<td>'.$ellipsized_names_str.
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
                /****   C3 plot   ****/
                $chart_data[] = $this_chart_data;
                $tool_content .= "<script type = 'text/javascript'>pollChartData.push(".json_encode($this_chart_data).");</script>";
                $tool_content .= "<div class='row plotscontainer mb-4'>";
                $tool_content .= "<div class='col-lg-12'>";
                $tool_content .= plot_placeholder("poll_chart$chart_counter", q($theQuestion->question_text));
                $tool_content .= "</div></div>";
                if ($is_editor) {
                    $tool_content .= $answers_table;
                }
                $chart_counter++;
            } elseif ($theQuestion->qtype == QTYPE_FILL || $theQuestion->qtype == QTYPE_DATETIME || $theQuestion->qtype == QTYPE_SHORT) {

                $sql_participants_a = '';
                $sql_participants_c = '';
                if (isset($_GET['from_session_view'])) {
                    $sql_participants_a = "AND b.uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND b.session_id = $_GET[session]";
                    $sql_participants_c = "AND poll_user_record.uid IN (SELECT participants FROM mod_session_users WHERE session_id = $_GET[session] AND is_accepted = 1) AND poll_user_record.session_id = $_GET[session]";
                }

                $tool_content .= "<div class='panel-body'>";
                $tool_content .= "<div class='inner-heading'>$theQuestion->question_text</div>";
                $tool_content .= "</div>";
                $names_array = [];
                $answers = Database::get()->queryArray("SELECT COUNT(a.arid) AS count, a.answer_text
                                            FROM poll_answer_record a, poll_user_record b
                                            WHERE a.qid = ?d
                                            AND a.poll_user_record_id = b.id
                                            $sql_participants_a
                                            AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                            GROUP BY a.answer_text ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid);
                $tool_content .= "<div class='table-responsive'><table class='table-default'>
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
                                                        $sql_participants_c)
                                                UNION
                                                    (SELECT poll_user_record.email AS fullname, submit_date AS s
                                                    FROM poll_user_record, poll_answer_record
                                                        WHERE poll_answer_record.qid = ?d
                                                        AND poll_answer_record.answer_text = ?s
                                                        AND poll_user_record.email IS NOT NULL
                                                        AND poll_user_record.email_verification = 1
                                                        AND poll_answer_record.poll_user_record_id = poll_user_record.id)
                                                    ORDER BY s DESC
                                                ", $theQuestion->pqid, $answer->answer_text, $theQuestion->pqid, $answer->answer_text);
                        foreach($names as $name) {
                            $names_array[] = $name->fullname;
                        }
                        $names_str = implode(', ', $names_array);
                        $ellipsized_names_str = q(ellipsize($names_str, 60));
                    }
                    $row_class = ($k>3) ? 'class="hidden_row" style="display:none;"' : '';
                    $extra_column = (!$thePoll->anonymized and $is_editor)?
                        "<td>"
                        . $ellipsized_names_str
                        . (($ellipsized_names_str != $names_str) ? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$langViewShow.'</a>' : '').
                        "</td>
                            <td class='hidden_names' style='display:none;'><em>"
                        . q($names_str) .
                        "</em> <a href='#' class='trigger_names' data-type='multiple' id='hide'>".$langViewHide."</a>
                           </td>" : "";
                    $tool_content .= "
                        <tr $row_class>
                                <td>".q($answer->answer_text)."</td>
                                <td>$answer->count</td>
                                $extra_column
                        </tr>";
                    $k++;
                    if (!$thePoll->anonymized) {
                        unset($names_array);
                    }
                }
                if ($k>4) {
                    $tool_content .= "
                    <tr>
                        <td colspan='".($thePoll->anonymized ? 2 : 3)."'><a href='#' class='trigger_names' data-type='fill' id='show'>$langViewShow</a></td>
                    </tr>";
                }
                $tool_content .= '</tbody></table></div><br>';
            } elseif ($theQuestion->qtype == QTYPE_TABLE) {
                $tool_content .= "<div class='panel-body'>
                                    <div class='inner-heading'><strong>$theQuestion->question_text</strong></div>
                                </div>";

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
                                                                AND poll_user_record.session_id = ?d
                                                                AND poll_question_answer.pqid = ?d", $theQuestion->pqid, $pid, $s_id, $theQuestion->pqid);

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
                            $tool_content .="<div class='card panelCard card-default card-user-answers mb-4'>
                                                <div class='card-header'>";
                                                    if ($thePoll->anonymized or (!$is_editor)) {
                                                        $tool_content .= "$langAnswer";
                                                    } else {
                                                        $tool_content .= "<h3 style='margin-bottom:0px;'>$p->givenname&nbsp;$p->surname</h3>";
                                                    }
                            $tool_content .= "  </div>
                                                <div class='card-body'>";   
                                    $tool_content .= "  <table class='table-default'><tr>";
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
                                                $tool_content .= "<td><ul class='list-group list-group-flush w-100'>
                                                                        <h5 style='margin-bottom:0px; word-break: normal; overflow-wrap: break-word;'>$s->answer_text</h5>";
                                                                    foreach ($answers as $a) {
                                                                        if ($p->participants == $a->uid && $theQuestion->pqid == $a->qid && 
                                                                                $s->sub_question == $a->sub_question) {
                                                                                    $tool_content .= "<li class='list-group-item element px-0'>$a->answer_text</li>";
                                                                        }
                                                                    }
                                                $tool_content .= "</ul></td>";
                                                                }
                                                            }                           
                            $tool_content .= "          
                                                        </tr></table>    
                                                </div>
                                            </div>";
                        }
                    }
                } else {
                    $tool_content .= "<div class='alert alert-warning'>
                                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                            <span>$langNoAnswers</span>
                                        </div>";
                }
            }
            $tool_content .= "</div></div></div>";
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
            .ButtonsContent{ display: none; }
            .hidden_names{ display: none; }
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