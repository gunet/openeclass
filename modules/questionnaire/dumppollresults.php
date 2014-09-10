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
$require_editor = TRUE;
include '../../include/init.php';

if (!isset($_GET['pid'])) {
    header("Location: $urlServer");
} else {
    $pid = $_GET['pid'];
}

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
} else {
    $charset = 'UTF-8';
}
$crlf = "\r\n";

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=pollresults.csv");

$p = Database::get()->querySingle("SELECT pid FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
if(!$p){
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
echo csv_escape($langQuestions), $crlf, $crlf;
$q = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid=?d",$p->pid);
foreach ($q as $question) {
    if ($question->qtype == 'multiple') { // only for questions with mupliple answers
        echo $question->question_text;
        echo "$crlf";
        $a = Database::get()->queryArray("SELECT COUNT(aid) AS count, aid, poll_question_answer.answer_text AS answer
                                FROM poll_answer_record LEFT JOIN poll_question_answer
                                ON poll_answer_record.aid = poll_question_answer.pqaid
                                WHERE qid = ?d GROUP BY aid", $question->pqid);
        $answer_counts = array();
        $answer_text = array();
        $answer_total = 0;
        foreach ($a as $answer) {
            $answer_counts[] = $answer->count;
            $answer_total += $answer->count;
            if ($answer->aid < 0) {
                $answer_text[] = $langPollUnknown;
            } else {
                $answer_text[] = $answer->answer;
            }
        }
        echo csv_escape($langAnswers) . ";" . csv_escape($langResults) . " (%)", $crlf;
        foreach ($answer_counts as $i => $count) {
            $percentage = round(100 * ($count / $answer_total));
            $label = $answer_text[$i];
            echo csv_escape($label) .
            ";" . csv_escape($percentage) . "$crlf";
        }
        echo "$crlf";
    } else { // free text questions
            echo csv_escape($question->question_text), $crlf;
            $a = Database::get()->queryArray("SELECT answer_text, user_id FROM poll_answer_record
                                              WHERE qid = ?d", $question->pqid);            
            foreach ($a as $answer) {
                    echo csv_escape(uid_to_name($answer->user_id)), ';', csv_escape($answer->answer_text), $crlf;
            }
    }
}