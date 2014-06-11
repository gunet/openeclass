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

require_once '../../include/baseTheme.php';
load_js('jquery');
load_js('datatables');
$head_content .= "<script type='text/javascript'>$(function () {
    $('table').DataTable();
});</script>";

if (!($is_editor and
      isset($_GET['pid']) and is_numeric($_GET['pid']) and 
      isset($_GET['qid']) and is_numeric($_GET['qid']))) {
          redirect_to_home_page();
}
$pid = intval($_GET['pid']);
$qid = intval($_GET['qid']);

$pollTitle = db_query_get_single_value("SELECT name FROM poll WHERE pid = $pid", $dbname);
if (!$pollTitle) {
    redirect_to_home_page();
}

$question = db_query_get_single_row("SELECT * FROM poll_question WHERE pid = $pid and pqid = $qid");
if (!$question) {
    redirect_to_home_page();
}

$navigation[] = array('url' => "questionnaire.php?course=$code_cours", 'name' => $langQuestionnaire);
$navigation[] = array('url' => "pollresults.php?course=$code_cours&amp;pid=$pid", 'name' => q($pollTitle));
$nameTools = $langPollCharts;

$tool_content .= "<h1>" . q($question['question_text']) . "</h1>
    <p class='sub_title1'>$langAnswers</p>
    <table>
        <thead><tr><th>$langUser</th><th>$langAnswer</th><th>$langDate</th></tr></thead><tbody>\n";
if ($question['qtype'] == 'multiple') {
    $answers = db_query("SELECT poll_question_answer.answer_text, aid,
                                user_id, submit_date
                        FROM poll_answer_record LEFT JOIN poll_question_answer
                             ON poll_answer_record.aid = poll_question_answer.pqaid
                        WHERE qid = $qid");
    while ($theAnswer = mysql_fetch_assoc($answers)) {
        $answer = ($theAnswer['aid'] < 0)? $langPollUnknown: $theAnswer['answer_text'];
        $tool_content .= "<tr><th>" . display_user($theAnswer['user_id'], false, false) . "</th>
            <td>" . q($answer) . "</td><td>" .
            q($theAnswer['submit_date']) . "</td></tr>\n";
    }
} else {
    $answers = db_query("SELECT answer_text, user_id, submit_date
                            FROM poll_answer_record
                            WHERE qid = $qid");
    while ($theAnswer = mysql_fetch_assoc($answers)) {
        $tool_content .= "<tr><th>" . display_user($theAnswer['user_id'], false, false) . "</th>
            <td>" . q($theAnswer['answer_text']) . "</td><td>" .
            q($theAnswer['submit_date']) . "</td></tr>\n";
    }
}

$tool_content .= "</tbody></table>\n
    <p class=right style='clear: both; padding-top: 1em;'><a href='pollresults.php?course=$code_cours&amp;pid=$pid'>$langBack</a></p>";

draw($tool_content, 2, null, $head_content);

