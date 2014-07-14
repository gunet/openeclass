<?php
/* ========================================================================
 * Open eClass 2.10
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
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';
require_once 'functions.php';
require_once '../../include/libchart/libchart.php';

$nameTools = $langPollCharts;
$navigation[] = array("url"=>"questionnaire.php?course=$code_cours", "name"=> $langQuestionnaire);

$questions = array();
$answer_total = 0;
load_js('jquery');
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
                $(this).text('$shownone');
                $(this).attr('id', 'hide');
            }
        } else {
            if (field_type == 'multiple') {
                var hidden_field = $(this).parent();
                hidden_field.hide();
                hidden_field.prev().show();
            } else {
                $(this).closest('tr').siblings('.hidden_row').hide('slow');
                $(this).text('$showall');
                $(this).attr('id', 'show');            
            }
        }
      });
    });  
</script>";
if (!$is_editor) {
    $tool_content .= "<p class='alert1'>".$langPollResultsAccess."<br /><a href=\"questionnaire.php?course=$code_cours\">".$langBack."</a></p>";
    draw($tool_content, 2, null, $head_content);
    exit();
}

if(!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    redirect_to_home_page();
}
$pid = intval($_GET['pid']);
$current_poll = db_query("SELECT * FROM poll WHERE pid = $pid", $currentCourse);
if (!$current_poll or !mysql_num_rows($current_poll)) {
    redirect_to_home_page();
}
$thePoll = mysql_fetch_array($current_poll);

$tool_content .= "
<div class='info'>
<b>$langDumpUserDurationToFile:</b><br>
<b>$langPollPercentResults:</b> <a href='dumppollresults.php?course=$code_cours&amp;pid=$pid'>$langcsvenc2</a>,
   <a href='dumppollresults.php?course=$code_cours&amp;enc=1253&amp;pid=$pid'>$langcsvenc1</a><br>
<b>$langPollFullResults:</b> <a href='dumppollresults.php?course=$code_cours&amp;pid=$pid&amp;full=1'>$langcsvenc2</a>,
   <a href='dumppollresults.php?course=$code_cours&amp;enc=1253&amp;pid=$pid&amp;full=1'>$langcsvenc1</a>
</div>";

$tool_content .= "
<table class='tbl_border'>
<tr>
        <th width='150'>$langTitle:</th>
        <td>" . $thePoll["name"] . "</td>
</tr>
<tr>
        <th>$langPollCreation:</th>
        <td>".nice_format(date("Y-m-d H:i", strtotime($thePoll["creation_date"])), true)."</td>
</tr>
<tr>
        <th>$langPollStart:</th>
        <td>".nice_format(date("Y-m-d H:i", strtotime($thePoll["start_date"])), true)."</td>
</tr>
<tr>
        <th>$langPollEnd:</th>
        <td>".nice_format(date("Y-m-d H:i", strtotime($thePoll["end_date"])), true)."</td>
</tr>
</table>";
$tool_content .= "<p class='sub_title1'>$langAnswers</p>";
$tool_content .= "<table class='tbl' width='100%'>";

$questions = db_query("SELECT * FROM poll_question WHERE pid=$pid ORDER BY pqid");
$j=1;
while ($theQuestion = mysql_fetch_array($questions)) {
        if ($theQuestion['qtype'] != QTYPE_LABEL) {
            $tool_content .= "
            <tr>
                    <td width='80'><b>$langQuestion $j:</b></td>
                    <td>$theQuestion[question_text]</td>
            </tr>
            <tr>
            <td colspan='2'>";
            $j++;
        } else {
           $tool_content .= "<tr><td colspan='2'><br><div class='info'>$theQuestion[question_text]</div><br><hr></td></tr>"; 
        }
        if ($theQuestion['qtype'] == QTYPE_MULTIPLE || $theQuestion['qtype'] == QTYPE_SINGLE) {
            $answers = db_query("SELECT COUNT(aid) AS count, aid, poll_question_answer.answer_text AS answer
                    FROM poll_answer_record LEFT JOIN poll_question_answer
                    ON poll_answer_record.aid = poll_question_answer.pqaid
                    WHERE qid = $theQuestion[pqid] GROUP BY aid", $currentCourseID);
            $answer_counts = array();
            $answer_text = array();
            while ($theAnswer = mysql_fetch_array($answers)) {
                    $answer_counts[$theAnswer['aid']] = $theAnswer['count'];
                    $answer_total += $theAnswer['count'];
                    if ($theAnswer['aid'] < 0) {
                            $answer_text[$theAnswer['aid']] = $langPollUnknown;
                    } else {
                            $answer_text[$theAnswer['aid']] = $theAnswer['answer'];
                    }
            }
            $chart = new PieChart(500, 300);
            $dataSet = new XYDataSet();
            $chart->setTitle('');
            $answers_table = "
                <table class='tbl_border' width='100%'>
                    <tr>
                        <th width='30%'>$langAnswer</th>
                        <th width='30%'>$langSurveyTotalAnswers</th>".(($thePoll["anonymized"] == 1)?'':'<th>'.$langStudents.'</th>')."</tr>";            
            foreach ($answer_counts as $i => $count) {
                $percentage = round(100 * ($count / $answer_total),2);
                $label = $answer_text[$i];
                $dataSet->addPoint(new Point($label, $percentage));
                if ($thePoll["anonymized"] != 1) {
                    $names = db_query("SELECT CONCAT(b.prenom, ' ', b.nom) AS fullname FROM poll_answer_record AS a, $mysqlMainDb.user AS b WHERE a.aid = $i AND a.user_id = b.user_id");
                    while($fetched_names = mysql_fetch_array($names)) {
                      $names_array[] = $fetched_names[0];
                    }
                    $names_str = implode(', ', $names_array);  
                    $ellipsized_names_str = ellipsize($names_str, 60);
                }
                $answers_table .= "
                    <tr>
                            <td>$answer_text[$i]</th>
                            <td>$count</td>".(($thePoll["anonymized"] == 1)?'':'<td>'.$ellipsized_names_str.(($ellipsized_names_str != $names_str)? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$showall.'</a>' : '').'</td><td class="hidden_names" style="display:none;">'.$names_str.' <a href="#" class="trigger_names" data-type="multiple" id="hide">'.$shownone.'</a></td>')."</tr>";     
                unset($names_array);
            }
            $answers_table .= "</table><br>";
            $chart->setDataSet($dataSet);
            $chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';
            $chart->render($webDir.$chart_path);           
            $tool_content .= '<img src="'.$urlServer.$chart_path.'" />';
            $tool_content .= $answers_table;
        } elseif ($theQuestion['qtype'] == QTYPE_FILL) {
            $answers = db_query("SELECT answer_text, user_id FROM poll_answer_record
                                    WHERE qid = $theQuestion[pqid]");
            $tool_content .= "<table class='tbl_border' width='100%'>
                    <tbody>
                    <tr>
                            <th width='20%'>$langUser</th>
                            <th width='80%'>$langAnswer</th>
                    </tr>";  
            if ($thePoll["anonymized"]==1) {
                $k=1;
                while ($theAnswer = mysql_fetch_array($answers)) {     
                    $tool_content .= "
                    <tr>
                            <td>$langMetaLearner $i</th>
                            <td>$theAnswer[answer_text]</td>
                    </tr>";                
                    $k++;    
                }           
            } else {
                $k=1;
                while ($theAnswer = mysql_fetch_array($answers)) { 
                    $tool_content .= "
                    <tr ".(($k>3) ? 'class="hidden_row" style="display:none;"' : '').">
                            <td>" . q(uid_to_name($theAnswer['user_id'])) ."</th>
                            <td>$theAnswer[answer_text]</td>
                    </tr>";
                    $k++;
                }
                if ($k>3) {
                 $tool_content .= "
                    <tr>
                            <td colspan='2'><a href='#' class='trigger_names' data-type='fill' id='show'>$showall</a></th>
                    </tr>";                       
                }                
            }
            $tool_content .= '</tbody></table><br>';
        }
        $tool_content .= "<hr></td></tr>";
}

$total = mysql_num_rows(db_query("SELECT DISTINCT user_id FROM poll_answer_record WHERE pid = $pid", $currentCourseID));
$tool_content .= "
<tr>
        <th colspan='2'>$langPollTotalAnswers: $total</th>
</tr>
</table>";
draw($tool_content, 2, null, $head_content);

