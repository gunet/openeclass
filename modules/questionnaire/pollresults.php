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

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';
require_once 'modules/graphics/plotter.php';
load_js('jquery');

$nameTools = $langPollCharts;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);

$questions = array();
$answer_total = 0;

if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    header("Location: $urlServer");
} else {
    $pid = $_GET['pid'];
}

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
$tool_content .= "<table class='tbl'>";

$q = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY qtype", $pid);
foreach ($q as $questions) {
    if ($questions->qtype == 'multiple') {
        $tool_content .= "
        <tr>
        <th>$questions->question_text</th>
        <td>";        
        $a = Database::get()->queryArray("SELECT COUNT(aid) AS count, aid, poll_question_answer.answer_text AS answer
                        FROM poll_answer_record LEFT JOIN poll_question_answer
                        ON poll_answer_record.aid = poll_question_answer.pqaid
                        WHERE qid = ?d GROUP BY aid", $questions->pqid);        
        $answer_counts = array();
        $answer_text = array();        
        foreach ($a as $answer) {
            $answer_counts[] = $answer->count;
            $answer_total += $answer->count;
            if ($answer->aid < 0) {
                $answer_text[] = $langPollUnknown;
            } else {
                $answer_text[] = $answer->answer;
            }
        }
        $chart = new Plotter(500, 300);
        foreach ($answer_counts as $i => $count) {
            $percentage = 100 * ($count / $answer_total);
            $chart->addPoint($answer_text[$i], $percentage);
        }
        $chart->normalize();
        $tool_content .= $chart->plot();
        $tool_content .= "</td></tr>";
    } else {        
        $tool_content .= "<tr><th colspan='2'>$questions->question_text</th></tr>";        
        $a = Database::get()->queryArray("SELECT answer_text, user_id FROM poll_answer_record
                                WHERE qid = ?d", $questions->pqid);        
        foreach ($a as $answer) {
            $tool_content .= "<tr><td>" . display_user($answer->user_id) . "</td>"
                                        . "<td>$answer->answer_text</td></tr>";
        }
        $tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";
    }
}

$t = Database::get()->querySingle("SELECT COUNT(DISTINCT user_id) AS total FROM poll_answer_record WHERE pid = ?d", $pid);
$tool_content .= "
<tr>
        <th colspan='2'>$langPollTotalAnswers: $t->total</th>
</tr>
</table>";
draw($tool_content, 2, null, $head_content);
