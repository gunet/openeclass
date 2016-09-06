<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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


$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

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
                    x: 'category',
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
                bindto: '#poll_chart'
            };
            c3.generate(options);
        }
}

</script>";


if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    redirect_to_home_page();
}
$pid = intval($_GET['pid']);
$thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
$PollType = $thePoll ->type;
if (!$is_editor && !$thePoll->show_results) {
    Session::Messages($langPollResultsAccess);
    redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);    
}
if(!$thePoll){
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $pid)->total;
if(!$total_participants) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
$export_box = "";

if ($is_editor) {
    $export_box .= "
        <div class='alert alert-info'>
            <b>$langDumpUserDurationToFile:</b>
            <ul>
              <li><a href='dumppollresults.php?course=$course_code&amp;pid=$pid'>$langPollPercentResults</a>
                  (<a href='dumppollresults.php?course=$course_code&amp;pid=$pid&amp;enc=UTF-8'>$langcsvenc2</a>)</li>
              <li><a href='dumppollresults.php?course=$course_code&amp;pid=$pid&amp;full=1'>$langPollFullResults</a>
                  (<a href='dumppollresults.php?course=$course_code&amp;pid=$pid&amp;full=1&amp;enc=UTF-8'>$langcsvenc2</a>)</li>
            </ul>
        </div>";
}

$tool_content .= action_bar(array(
            array(
                'title' => $langBack,
                'url' => "index.php?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            )
        ))."
$export_box
<div class='panel panel-primary'>
    <div class='panel-heading'>
        <h3 class='panel-title'>$langInfoPoll</h3>
    </div>
    <div class='panel-body'>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langTitle:</strong>
            </div>
            <div class='col-sm-9'>
                " . q($thePoll->name) . "
            </div>                
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollCreation:</strong>
            </div>
            <div class='col-sm-9'>
                " . nice_format(date("Y-m-d H:i", strtotime($thePoll->creation_date)), true) . "
            </div>                
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollStart:</strong>
            </div>
            <div class='col-sm-9'>
                " . nice_format(date("Y-m-d H:i", strtotime($thePoll->start_date)), true) . "
            </div>                
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollEnd:</strong>
            </div>
            <div class='col-sm-9'>
                " . nice_format(date("Y-m-d H:i", strtotime($thePoll->end_date)), true) . "
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
</div>

";

$w_ckw = array (-0.13,0,0.68,0.55,0.37,0.6,0.01,0.58,-0.25,0.14,0.39,-0.07,0.45,-0.18,0.63,0.07,-0.12,0.41,0.59,-0.22);
$w_skw = array (0.48,0.39,0.01,0.06,0.08,0.09,0.39,0.11,0.53,0.25,0.29,0.5,0.26,0.53,0.04,0.34,0.39,-0.4,0.02,0.54);
	
//$result = Database::get()->queryArray("SELECT t2.uid as st, t1.*, t1.answer_text,sum(t1.answer_text*t3.ckw) as tckw, sum(t1.answer_text*t3.skw) as tskw FROM poll_answer_record as t1 , poll_user_record as t2, poll_attls as t3 
									//	WHERE t1.poll_user_record_id=t2.id AND t2.pid = ?d AND t1.qid = t3.id group by t2.uid", $pid);

$result = Database::get()->queryArray("SELECT  t1.*, t2.uid as st FROM poll_answer_record as t1, poll_user_record as t2
                                                                WHERE t1.poll_user_record_id=t2.id AND t2.pid = ?d group by st", $pid);


$tool_content .= "<table class='table-default'>
            <tbody>
            <tr>
                <th>$langStudents</th>
                <th>$lang_Results</th> 
            </tr>";

$connected = 0;
$separated = 0;
$both_con_sep = 0;
foreach ($result as $theresult){

    $uid = $theresult->st;
    $p_user_id = $theresult->poll_user_record_id;

    $user = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $uid);
    $f_name = $user->givenname;
    $l_name = $user->surname;
    $answers = Database::get()->queryArray("SELECT t1.answer_text as ans, t2.* FROM poll_answer_record as t1, poll_user_record as t2 WHERE t1.poll_user_record_id=t2.id AND t2.uid = ?d AND t2.pid = ?d ", $uid, $pid);

    $ckw = 0;
    $skw = 0;
    $i = 0;
    foreach ($answers as $answer){
        $q_ans = $answer -> ans;
        $a = $q_ans * $w_ckw[$i];
        $b = $q_ans * $w_skw[$i];
        $i++;
        $ckw = $ckw + $a;
        $skw = $skw + $b;
    }

    $tool_content .="<tr><td>".$l_name. " ". $f_name."</td><td>";

    $dif_scores = $ckw-$skw;

    if($dif_scores>1){
        $connected ++;
        $tool_content .= $langCKW . "<b>". $lang_ckw . round($ckw,2) . $lang_skw . round($skw,2)."</b>) <a href='#' class='trigger_names' data-type='multiple' id='show'>$langViewShow</a>";
        $tool_content .= "</td><td class='hidden_names' style='display:none;'><table border='1' width='100%'>";
        $answers = Database::get()->queryArray("SELECT t1.question_text as qt, t2.answer_text as ant FROM poll_question as t1, poll_answer_record as t2
                                                                                        WHERE t1.pqid=t2.qid AND t2.poll_user_record_id = ?d AND t1.pid = ?d", $p_user_id , $pid); 
        foreach ($answers as $answer){
            $q = $answer -> qt;
            $a = $answer -> ant;
            if ($a == 1)
                $ans = $lang_rate1;
            elseif ($a == 2)
                $ans = $lang_rate2;
            elseif ($a == 3)
                $ans = $lang_rate3;
            elseif ($a == 4)
                $ans = $lang_rate4;
            elseif ($a == 5)
                $ans = $lang_rate5;
            $tool_content .= "<tr><td width='80%'>".$q."</td><td width='20%' align='center'>".$ans."</td></tr>";
        }
        $tool_content .="</table><a href='#' class='trigger_names' data-type='multiple' id='hide'>$langViewHide</a></td>  "; 
    }
    else if($dif_scores<-1) {
        $separated ++;
        $tool_content .= $langSKW ."<b>". $lang_ckw . round($ckw,2) . $lang_skw . round($skw,2)."</b>) <a href='#' class='trigger_names' data-type='multiple' id='show'>$langViewShow</a>";
        $tool_content .= "</td><td class='hidden_names' style='display:none;'><table border='1' width='100%'>";
        $answers = Database::get()->queryArray("SELECT t1.question_text as qt, t2.answer_text as ant FROM poll_question as t1, poll_answer_record as t2
                                                                                        WHERE t1.pqid=t2.qid AND t2.poll_user_record_id = ?d AND t1.pid = ?d", $p_user_id , $pid); 
        foreach ($answers as $answer){
            $q = $answer -> qt;
            $a = $answer -> ant;
            if ($a == 1)
                $ans = $lang_rate1;
            elseif ($a == 2)
                $ans = $lang_rate2;
            elseif ($a == 3)
                $ans = $lang_rate3;
            elseif ($a == 4)
                $ans = $lang_rate4;
            elseif ($a == 5)
                $ans = $lang_rate5;
            $tool_content .= "<tr><td width='80%'>".$q."</td><td width='20%' align='center'>".$ans."</td></tr>";
        }
        $tool_content .="</table><a href='#' class='trigger_names' data-type='multiple' id='hide'>$langViewHide</a></td> "; 
    } else {
        $both_con_sep++;
        $tool_content .= $langCKW_SKW ."<b>". $lang_ckw . round($ckw,2) . $lang_skw . round($skw,2)."</b>) <a href='#' class='trigger_names' data-type='multiple' id='show'>$langViewShow</a>";
        $tool_content .= "</td><td class='hidden_names' style='display:none;'><table border='1' width='100%'>";
        $answers = Database::get()->queryArray("SELECT t1.question_text as qt, t2.answer_text as ant FROM poll_question as t1, poll_answer_record as t2
                                                                                        WHERE t1.pqid=t2.qid AND t2.poll_user_record_id = ?d AND t1.pid = ?d", $p_user_id , $pid); 
        foreach ($answers as $answer) {
            $q = $answer -> qt;
            $a = $answer -> ant;
            if ($a == 1)
                $ans = $lang_rate1;
            elseif ($a == 2)
                $ans = $lang_rate2;
            elseif ($a == 3)
                $ans = $lang_rate3;
            elseif ($a == 4)
                $ans = $lang_rate4;
            elseif ($a == 5)
                $ans = $lang_rate5;
            $tool_content .= "<tr><td width='80%'>".$q."</td><td width='20%' align='center'>".$ans."</td></tr>";
        }
        $tool_content .="</table><a href='#' class='trigger_names' data-type='multiple' id='hide'>$langViewHide</a></td> "; 
    }
    $tool_content .="</td></tr>";	
                            }
    $tool_content .="</tbody></table>";


//default tables
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    redirect_to_home_page();
}
$pid = intval($_GET['pid']);
$thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
$PollType = $thePoll ->type;
if (!$is_editor && !$thePoll->show_results) {
    Session::Messages($langPollResultsAccess);
    redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);    
}
if(!$thePoll){
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $pid)->total;
if(!$total_participants) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

//Summarizing results: #students belong to category
$chart_data = array();                                             
$this_chart_data = array();

$tool_content .= "
<div class='panel panel-success'>
    <div class='panel-heading'>
        <h3 class='panel-title'>$lang_result_summary</h3>
    </div>
    <div class='panel-body'>
        <h4>$lang_ckw_skw_chart</h4>";

$this_chart_data['category'][] = $langConnected;
$this_chart_data['category'][] = $langSeparated;
$this_chart_data['category'][] = $langBothWays;
$total_partic = $connected+$separated+$both_con_sep;
$this_chart_data['percentage'][] = round(100*$connected/$total_partic,2);
$this_chart_data['percentage'][] = round(100*$separated/$total_partic,2);
$this_chart_data['percentage'][] = round(100*$both_con_sep/$total_partic,2);

/****   C3 plot   ****/
$chart_data[] = $this_chart_data;
$tool_content .= "<script type = 'text/javascript'>pollChartData.push(".json_encode($this_chart_data).");</script>";
$tool_content .= "<div class='row plotscontainer'>";
$tool_content .= "<div class='col-lg-12'>";
$tool_content .= plot_placeholder("poll_chart");
$tool_content .= "</div></div>";

$tool_content .= "</div></div>";

// display page
draw($tool_content, 2, null, $head_content);
