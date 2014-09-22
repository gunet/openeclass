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
require_once '../../include/baseTheme.php';
require_once 'functions.php';

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
} else {
    $charset = 'UTF-8';
}
$full = isset($_GET['full']) && $_GET['full'];
$crlf = "\r\n";

if (!isset($_GET['pid'])) {
    redirect_to_home_page();
} else {
    $pid = intval($_GET['pid']);
}

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=pollresults.csv");

$anonymized = db_query_get_single_value("SELECT anonymized FROM poll WHERE pid = $pid", $currentCourseID);

if ($full) {
    $begin = true;
    $questions = db_query("SELECT * FROM poll_question WHERE pid = $pid ORDER BY pqid", $currentCourseID);
    $qlist = array();

    $user_ids = array();
    $r = db_query("SELECT user_id FROM poll_answer_record WHERE pid = $pid ORDER BY user_id");
    while (list($user_id) = mysql_fetch_row($r)) {
        $user_ids[] = $user_id;
    }
    while ($q = mysql_fetch_assoc($questions)) {
        $pqid = $q['pqid'];
        $qtype = $q['qtype'];
        if ($begin) {
            echo csv_escape($langUser), ';user_id;';
            $begin = false;
        } else {
            echo ';';
        }
        if ($qtype == QTYPE_LABEL) {
            $q['question_text'] = strip_tags($q['question_text']);
        }
        echo csv_escape($q['question_text']);
        if ($qtype == QTYPE_LABEL) {
            foreach ($user_ids as $user_id) {
                $qlist[$user_id][$pqid] = '-';
            }
        } elseif ($qtype == QTYPE_SINGLE or $qtype == QTYPE_MULTIPLE) {
            $r = db_query("SELECT poll_question_answer.answer_text, aid, user_id
                                FROM poll_answer_record LEFT JOIN poll_question_answer
                                     ON poll_answer_record.aid = poll_question_answer.pqaid
                                WHERE qid = $pqid
                                ORDER BY user_id");
            while ($a = mysql_fetch_assoc($r)) {
                $answer_text = ($a['aid'] < 0)? $langPollUnknown: $a['answer_text'];
                if (isset($qlist[$a['user_id']][$pqid])) {
                    $qlist[$a['user_id']][$pqid] .= ', ' . $answer_text;
                } else {
                    $qlist[$a['user_id']][$pqid] = $answer_text;
                }
            }
        } else { // free text questions
            $r = db_query("SELECT answer_text, user_id
                                FROM poll_answer_record
                                WHERE qid = $pqid
                                ORDER BY user_id");
            while ($a = mysql_fetch_assoc($r)) {
                $qlist[$a['user_id']][$pqid] = $a['answer_text'];
            }
        }
    }
    echo $crlf;
    $k = 0;
    foreach ($qlist as $user_id => $answers) {
        $k++;
        $student_name = $anonymized? "$langStudent $k": uid_to_name($user_id);
        if ($anonymized) {
            $user_id = $k;
        }
        echo csv_escape($student_name), ';', $user_id, ';',
            implode(';', array_map('csv_escape', $answers)), $crlf;
    }
} else {
    echo csv_escape($langQuestions), $crlf, $crlf;
    $questions = db_query("SELECT * FROM poll_question WHERE pid=$pid ORDER BY pqid", $currentCourseID);
    while ($theQuestion = mysql_fetch_array($questions)) {
        $qtype = $theQuestion['qtype'];
        if ($qtype == QTYPE_LABEL) {
            echo csv_escape(strip_tags($theQuestion['question_text'])), $crlf, $crlf;
        } elseif ($qtype == QTYPE_SINGLE or $qtype == QTYPE_MULTIPLE) {
            echo csv_escape($theQuestion['question_text']), $crlf;
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
            echo csv_escape($langAnswers).";".csv_escape($langResults).";".csv_escape($langResults)." (%)", $crlf;
            foreach ($answer_counts as $i => $count) {
                $percentage = round(100 * ($count / $answer_total));
                $label = $answer_text[$i];                        
                echo csv_escape($label), ';', csv_escape($count), ';', csv_escape($percentage), $crlf;
            }        
            echo $crlf;
        } else { // free text questions
            echo csv_escape($theQuestion['question_text']), $crlf;
            $answers = db_query("SELECT answer_text, user_id FROM poll_answer_record
                                    WHERE qid = $theQuestion[pqid]", $currentCourseID);                
            $k = 0;
            while ($theAnswer = mysql_fetch_array($answers)) {
                $k++;
                $student_name = $anonymized? "$langStudent $k": uid_to_name($theAnswer['user_id']);
                echo csv_escape($student_name), ';', csv_escape($theAnswer['answer_text']), $crlf;
            }
            echo $crlf;
        }
    }
}
