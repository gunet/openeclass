<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
require_once 'modules/graphics/plotter.php';

$nameTools = $langPollCharts;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);

$total_answers = 0;
$questions = array();
$answer_total = 0;

if (!$is_editor) {
    Session::set_flashdata($langPollResultsAccess, 'alert1');
    redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);    
}
load_js('jquery');

if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    header("Location: $urlServer");
}
$pid = intval($_GET['pid']);
$thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);

$tool_content .= "
<div class='info'>
<b>$langDumpUserDurationToFile: </b>1. <a href='dumppollresults.php?course=$course_code&amp;pid=$pid'>$langcsvenc2</a>
2. <a href='dumppollresults.php?course=$course_code&amp;enc=1253&amp;pid=$pid'>$langcsvenc1</a>          
</div>";

$tool_content .= "
<p class='sub_title1'>$langSurvey</p>
<table class='tbl_border'>
<tr>
        <th width='150'>$langTitle:</th>
        <td>" . $thePoll->name . "</td>
</tr>
<tr>
        <th>$langPollCreation:</th>
        <td>" . nice_format(date("Y-m-d H:i", strtotime($thePoll->creation_date)), true) . "</td>
</tr>
<tr>
        <th>$langPollStart:</th>
        <td>" . nice_format(date("Y-m-d H:i", strtotime($thePoll->start_date)), true) . "</td>
</tr>
<tr>
        <th>$langPollEnd:</th>
        <td>" . nice_format(date("Y-m-d H:i", strtotime($thePoll->end_date)), true) . "</td>
</tr>
</table>
<p class='sub_title1'>$langAnswers</p>";
$tool_content .= "<table class='tbl' width='100%'>";

$questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d", $pid);
foreach ($questions as $theQuestion) {
    $tool_content .= "
        <tr>
                <td width='50'><b>$langQuestion:</b></td>
                <td>$theQuestion->question_text</td>
        </tr>
        <tr>
            <td colspan='2'>";
    if ($theQuestion->qtype == 'multiple') {
        $answers = Database::get()->queryArray("SELECT COUNT(aid) AS count, aid, poll_question_answer.answer_text AS answer
                        FROM poll_answer_record LEFT JOIN poll_question_answer
                        ON poll_answer_record.aid = poll_question_answer.pqaid
                        WHERE qid = ?d GROUP BY aid", $theQuestion->pqid);
        $answer_counts = array();
        $answer_text = array();
        foreach ($answers as $theAnswer) {
            $answer_counts[$theAnswer->aid] = $theAnswer->count;
            $answer_total += $theAnswer->count;
            if ($theAnswer->aid < 0) {
                $answer_text[$theAnswer->aid] = $langPollUnknown;
            } else {
                $answer_text[$theAnswer->aid] = $theAnswer->answer;
            }
        }
        $chart = new Plotter(500, 300);
        $answers_table = "
            <table class='tbl_border' width='100%'>
                <tr>
                    <th width='30%'>$langAnswer</th>
                    <th width='30%'>$langSurveyTotalAnswers</th>".(($thePoll->anonymized==1)?'':'<th>'.$langStudents.'</th>')."</tr>";
        foreach ($answer_counts as $i => $count) {
            $percentage = round(100 * ($count / $answer_total),2);
            $chart->addPoint($answer_text[$i], $percentage);
            
            if ($thePoll->anonymized!=1) {
            $names = Database::get()->queryArray("SELECT CONCAT(b.givenname, ' ', b.surname) AS fullname FROM poll_answer_record AS a, user AS b WHERE a.aid = ?d AND a.user_id = b.id", $i);
            $names_str = implode(', ', array_map(function($n) {
                return $n->fullname;
            }, $names));            

            }
            $answers_table .= "
                <tr>
                        <td>$answer_text[$i]</th>
                        <td>$count</td>".(($thePoll->anonymized==1)?'':'<td>'.$names_str.'</td>')."</tr>";  
        }
        $answers_table .= "</table><br>";
        $chart->normalize();
        $tool_content .= $chart->plot();
        $tool_content .= $answers_table;
    } else {
        $answers = Database::get()->queryArray("SELECT answer_text, user_id FROM poll_answer_record
                                WHERE qid = ?d", $theQuestion->pqid);
        $answer_total = count($answers);
        $tool_content .= "<table class='tbl_border' width='100%'>
                <tbody>
                <tr>
                        <th width='20%'>$langUser</th>
                        <th width='80%'>$langAnswer</th>
                </tr>";       
        if ($thePoll->anonymized==1) {
            $i=1;
             foreach ($answers as $theAnswer) {     
                $tool_content .= "
                <tr>
                        <td>$langMetaLearner $i</th>
                        <td>$theAnswer->answer_text</td>
                </tr>";                
                $i++;    
            }           
        } else {
            foreach ($answers as $theAnswer) {
                $tool_content .= "
                <tr>
                        <td>" . q(uid_to_name($theAnswer->user_id)) ."</th>
                        <td>$theAnswer->answer_text</td>
                </tr>";                     
            }
        }
        $tool_content .= '</tbody></table><br>';
    }
    $tool_content .= "</td></tr>";
}
$tool_content .= "<tr><th colspan='2'>$langPollTotalAnswers: $answer_total</th></tr>
</table>";
// display page
draw($tool_content, 2, null, $head_content);