<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);

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
$default_answer = $thePoll->default_answer;
if (!$is_course_reviewer && !$thePoll->show_results) {
    Session::Messages($langPollResultsAccess);
    redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
}

$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $pid)->total;
if (!$total_participants) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

if (isset($_REQUEST['unit_id'])) {
    $back_link = "../units/index.php?course=$course_code&amp;id=" . intval($_REQUEST['unit_id']);
} else {
    $back_link = "index.php?course=$course_code";
}

$tool_content .= action_bar(array(
    array('title' => $langPollPercentResults,
        'url' => "dumppollresults.php?course=$course_code&amp;pid=$pid",
        'icon' => 'fa-download',
        'level' => 'primary-label',
        'show' => $is_course_reviewer),
    array('title' => $langPollFullResults,
        'url' => "dumppollresults.php?course=$course_code&amp;pid=$pid&amp;full=1",
        'icon' => 'fa-download',
        'level' => 'primary-label',
        'show' => $is_course_reviewer),
    array(
        'title' => $langBack,
        'url' => $back_link,
        'icon' => 'fa-reply',
        'level' => 'primary-label'
    )
));

$tool_content .= "<div class='panel panel-primary'>
    <div class='panel-heading'>
        <h3 class='panel-title'>$langInfoPoll</h3>
    </div>
    <div class='panel-body'>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langTitle:</strong>
            </div>
            <div class='col-sm-9'>
                " . q_math($thePoll->name) . "
            </div>
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollCreation:</strong>
            </div>
            <div class='col-sm-9'>
                " . format_locale_date(strtotime($thePoll->creation_date)) . "
            </div>
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langStart:</strong>
            </div>
            <div class='col-sm-9'>
                " . format_locale_date(strtotime($thePoll->start_date)) . "
            </div>
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollEnd:</strong>
            </div>
            <div class='col-sm-9'>
                " . format_locale_date(strtotime($thePoll->end_date)) . "
            </div>
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollTotalAnswers:</strong>
            </div>
            <div class='col-sm-9'>
                $total_participants
            </div>
        </div>
    </div>
</div>";

$questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position ASC", $pid);
$j=1;
$chart_data = [];
$chart_counter = 0;

if ($PollType == POLL_NORMAL || $PollType == POLL_QUICK) {
    foreach ($questions as $theQuestion) {
        $this_chart_data = array();
        if ($theQuestion->qtype == QTYPE_LABEL) {
            $tool_content .= "<div class='alert alert-info'>$theQuestion->question_text</div>";
        } else {
            $tool_content .= "
            <div class='panel panel-success'>
                <div class='panel-heading'>
                    <h3 class='panel-title'>$langQuestion $j</h3>
                </div>
                <div class='panel-body'>";

            $j++;

            if ($theQuestion->qtype == QTYPE_MULTIPLE || $theQuestion->qtype == QTYPE_SINGLE) {
                $names_array = [];
                $all_answers = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d", $theQuestion->pqid);
                foreach ($all_answers as $row) {
                    $this_chart_data['answer'][] = q($row->answer_text);
                    $this_chart_data['percentage'][] = 0;
                }
                $set_default_answer = false;
                $answers = Database::get()->queryArray("SELECT a.aid AS aid, MAX(b.answer_text) AS answer_text, count(a.aid) AS count
                            FROM poll_user_record c, poll_answer_record a
                            LEFT JOIN poll_question_answer b
                            ON a.aid = b.pqaid
                            WHERE a.qid = ?d
                            AND a.poll_user_record_id = c.id
                            AND (c.email_verification = 1 OR c.email_verification IS NULL)
                            GROUP BY a.aid ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid);
                $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record, poll_user_record
                                                                        WHERE poll_user_record_id = id
                                                                        AND (email_verification=1 OR email_verification IS NULL)
                                                                        AND qid= ?d", $theQuestion->pqid)->total;

                $answers_table = "
                    <table class='table-default'>
                        <tr>
                            <th>$langAnswer</th>
                            <th>$langSurveyTotalAnswers</th>
                            <th>$langPercentage</th>".(($thePoll->anonymized) ? '' : '<th>' . $langStudents . '</th>')."</tr>";
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
                                                        AND user.id = poll_user_record.uid)
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
                                <td>$q_answer</td>
                                <td>$answer->count</td>
                                <td>$percentage%</td>" .
                        (($thePoll->anonymized == 1) ? '' :
                            '<td>' . $ellipsized_names_str .
                            (($ellipsized_names_str != $names_str)? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$langViewShow.'</a>' : '') .
                            '</td>
                                <td class="hidden_names" style="display:none;">'.q($names_str).' <a href="#" class="trigger_names" data-type="multiple" id="hide">'.$langViewHide.'</a></td>')."</tr>";
                    unset($names_array);
                }
                $answers_table .= "</table><br>";
                $tool_content .= "<script type = 'text/javascript'>pollChartData.push(".json_encode($this_chart_data).");</script>";
                /****   C3 plot   ****/
                $tool_content .= "<div class='row plotscontainer'>";
                $tool_content .= "<div class='col-lg-12'>";
                $tool_content .= plot_placeholder("poll_chart$chart_counter", q_math($theQuestion->question_text));
                $tool_content .= "</div></div>";
                $tool_content .= $answers_table;
                $chart_counter++;
            } elseif ($theQuestion->qtype == QTYPE_SCALE) {
                $names_array = array();
                for ($i=1;$i<=$theQuestion->q_scale;$i++) {
                    $this_chart_data['answer'][] = "$i";
                    $this_chart_data['percentage'][] = 0;
                }

                $answers = Database::get()->queryArray("SELECT a.answer_text, count(a.answer_text) AS count
                        FROM poll_answer_record a, poll_user_record b
                        WHERE a.qid = ?d
                        AND a.poll_user_record_id = b.id
                        AND (b.email_verification = 1 OR b.email_verification IS NULL)
                        GROUP BY a.answer_text ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid);
                $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record, poll_user_record
                                                                        WHERE poll_user_record_id = id
                                                                        AND (email_verification=1 OR email_verification IS NULL)
                                                                        AND qid= ?d", $theQuestion->pqid)->total;

                $answers_table = "
                    <table class='table-default'>
                        <tr>
                            <th>$langAnswer</th>
                            <th>$langSurveyTotalAnswers</th>
                            <th>$langPercentage</th>".(($thePoll->anonymized == 1)?'':'<th>'.$langStudents.'</th>')."</tr>";
                foreach ($answers as $answer) {
                    $percentage = round(100 * ($answer->count / $answer_total),2);
                    $this_chart_data['percentage'][array_search($answer->answer_text,$this_chart_data['answer'])] = $percentage;

                    if ($thePoll->anonymized != 1) {
                        $names_str = $ellipsized_names_str = '';
                        // Gets names for registered users and emails for unregistered
                        $names = Database::get()->queryArray("(SELECT CONCAT(user.surname, ' ', user.givenname) AS fullname,
                                                            submit_date AS s
                                                    FROM poll_user_record, poll_answer_record, user
                                                        WHERE poll_user_record.id = poll_answer_record.poll_user_record_id
                                                        AND poll_answer_record.qid = ?d
                                                        AND poll_answer_record.answer_text = ?s
                                                        AND user.id = poll_user_record.uid)
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
                            <td>".q($answer->answer_text)."</td>
                            <td>$answer->count</td>
                            <td>$percentage%</td>"
                        . (($thePoll->anonymized == 1) ?
                            '' :
                            '<td>'.$ellipsized_names_str.
                            (($ellipsized_names_str != $names_str)? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$langViewShow.'</a>' : '').
                            '</td>
                            <td class="hidden_names" style="display:none;">'
                            . q($names_str) .
                            ' <a href="#" class="trigger_names" data-type="multiple" id="hide">'.$langViewHide.'</a>
                            </td>').
                        "</tr>";
                    unset($names_array);
                }
                $answers_table .= "</table>";
                /****   C3 plot   ****/
                $chart_data[] = $this_chart_data;
                $tool_content .= "<script type = 'text/javascript'>pollChartData.push(".json_encode($this_chart_data).");</script>";
                $tool_content .= "<div class='row plotscontainer'>";
                $tool_content .= "<div class='col-lg-12'>";
                $tool_content .= plot_placeholder("poll_chart$chart_counter", q($theQuestion->question_text));
                $tool_content .= "</div></div>";
                $tool_content .= $answers_table;
                $chart_counter++;
            } elseif ($theQuestion->qtype == QTYPE_FILL) {
                $tool_content .= "<div class='panel-body'>";
                $tool_content .= "<div class='inner-heading'>$theQuestion->question_text</div>";
                $tool_content .= "</div>";
                $names_array = [];
                $answers = Database::get()->queryArray("SELECT COUNT(a.arid) AS count, a.answer_text
                                            FROM poll_answer_record a, poll_user_record b
                                            WHERE a.qid = ?d
                                            AND a.poll_user_record_id = b.id
                                            AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                            GROUP BY a.answer_text ORDER BY MIN(a.submit_date) DESC", $theQuestion->pqid);
                $tool_content .= "<table class='table-default'>
                        <tbody>
                        <tr>
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
                                                        AND user.id = poll_user_record.uid)
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
                    $extra_column = !$thePoll->anonymized ?
                        "<td>"
                        . $ellipsized_names_str
                        . (($ellipsized_names_str != $names_str) ? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$langViewShow.'</a>' : '').
                        "</td>
                            <td class='hidden_names' style='display:none;'>"
                        . q($names_str) .
                        " <a href='#' class='trigger_names' data-type='multiple' id='hide'>".$langViewHide."</a>
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
                $tool_content .= '</tbody></table><br>';
            }
            $tool_content .= "</div></div>";
        }
    }
} elseif ($PollType == POLL_COLLES) {
    redirect_to_home_page("modules/questionnaire/colles.php?course=$course_code&pid=$pid");
} elseif ($PollType == POLL_ATTLS) {
    redirect_to_home_page("modules/questionnaire/attls.php?course=$course_code&pid=$pid");
}

// display page
draw($tool_content, 2, null, $head_content);
