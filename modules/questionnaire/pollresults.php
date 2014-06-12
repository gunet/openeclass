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
require_once '../../include/libchart/libchart.php';

$nameTools = $langPollCharts;
$navigation[] = array("url"=>"questionnaire.php?course=$code_cours", "name"=> $langQuestionnaire);

$questions = array();
$answer_total = 0;

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
$tool_content .= "<table class='tbl'>";

$questions = db_query("SELECT * FROM poll_question WHERE pid=$pid ORDER BY qtype");
while ($theQuestion = mysql_fetch_array($questions)) {
        $fullResultsUrl = "fullresults.php?course=$code_cours&amp;pid=$pid&amp;qid=$theQuestion[pqid]";
        if ($theQuestion['qtype'] == 'multiple') {
            $tool_content .= "
            <tr>
            <th rowspan=2>$theQuestion[question_text]</th>
            <td>";
            $answers = db_query("SELECT COUNT(aid) AS count, aid, poll_question_answer.answer_text AS answer
                    FROM poll_answer_record LEFT JOIN poll_question_answer
                    ON poll_answer_record.aid = poll_question_answer.pqaid
                    WHERE qid = $theQuestion[pqid] GROUP BY aid", $currentCourseID);
            $answer_counts = array();
            $answer_text = array();
            while ($theAnswer = mysql_fetch_array($answers)) {
                    $answer_counts[] = $theAnswer['count'];
                    $answer_total += $theAnswer['count'];
                    if ($theAnswer['aid'] < 0) {
                            $answer_text[] = $langPollUnknown;
                    } else {
                            $answer_text[] = $theAnswer['answer'];
                    }
            }
            $chart = new PieChart(500, 300);
            $dataSet = new XYDataSet();
            $chart->setTitle('');
            foreach ($answer_counts as $i => $count) {
                    $percentage = 100 * ($count / $answer_total);
                    $label = $answer_text[$i];
                    $dataSet->addPoint(new Point($label, $percentage));
            }
            $chart->setDataSet($dataSet);
            $chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';
            $chart->render($webDir.$chart_path);
            $tool_content .= '<img src="'.$urlServer.$chart_path.'" />';
            $tool_content .= "</td></tr>
                <tr><td class=right><a href='$fullResultsUrl'>$langPollFullResults</a></tr>
                <tr><td colspan='2'>&nbsp;</td></tr>\n";
        } else {
                $tool_content .= "<tr><th colspan='2'>$theQuestion[question_text]</th></tr>";
                $answers = db_query_get_single_value("SELECT COUNT(*) FROM poll_answer_record
                                WHERE qid = $theQuestion[pqid] AND answer_text <> ''", $currentCourseID);
                $tool_content .= "<tr><td>$langPollTotalAnswers: $answers</td>
                                      <td class=right><a href='$fullResultsUrl'>$langPollFullResults</a></td></tr>
                                  <tr><td colspan='2'>&nbsp;</td></tr>\n";
        }
}

$total = mysql_num_rows(db_query("SELECT DISTINCT user_id FROM poll_answer_record WHERE pid = $pid", $currentCourseID));
$tool_content .= "
<tr>
        <th colspan='2'>$langPollTotalAnswers: $total</th>
</tr>
</table>";
draw($tool_content, 2);

