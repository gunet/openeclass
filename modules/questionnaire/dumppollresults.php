<?php

/* ========================================================================
 * Open eClass 2.6
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
$require_editor = TRUE;
include '../../include/init.php';

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
        $charset = 'Windows-1253';
} else {
        $charset = 'UTF-8';
}
$crlf="\r\n";
$pid = $_GET['pid'];

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=pollresults.csv");

echo csv_escape($langQuestions), $crlf, $crlf;
$questions = db_query("SELECT * FROM poll_question WHERE pid=$pid", $currentCourseID);
while ($theQuestion = mysql_fetch_array($questions)) {
        if ($theQuestion['qtype'] == 'multiple') { // only for questions with mupliple answers
                echo $theQuestion['question_text'];
                echo "$crlf";                
                $answers = db_query("SELECT COUNT(aid) AS count, aid, poll_question_answer.answer_text AS answer
                                FROM poll_answer_record LEFT JOIN poll_question_answer
                                ON poll_answer_record.aid = poll_question_answer.pqaid
                                WHERE qid = $theQuestion[pqid] GROUP BY aid", $currentCourseID);
                $answer_counts = array();
                $answer_text = array();
                $answer_total = 0;
                while ($theAnswer = mysql_fetch_array($answers)) {
                        $answer_counts[] = $theAnswer['count'];
                        $answer_total += $theAnswer['count'];
                        if ($theAnswer['aid'] < 0) {
                                $answer_text[] = $langPollUnknown;
                        } else {
                                $answer_text[] = $theAnswer['answer'];
                        }
                }
                echo csv_escape($langAnswers).";".csv_escape($langResults)." (%)", $crlf;
                foreach ($answer_counts as $i => $count) {
                        $percentage = round(100 * ($count / $answer_total));
                        $label = $answer_text[$i];                        
                        echo csv_escape($label).
                        ";".csv_escape($percentage)."$crlf";
                }        
                echo "$crlf";
        }
}
