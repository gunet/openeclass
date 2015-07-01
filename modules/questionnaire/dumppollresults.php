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

$require_current_course = true;
$require_editor = true;
require_once '../../include/baseTheme.php';
require_once 'modules/questionnaire/functions.php';

if (!isset($_GET['pid'])) {
    redirect_to_home_page();
} else {
    $pid = intval($_GET['pid']);
}

if (!$is_editor) {
    Session::Messages($langPollResultsAccess);
    redirect_to_home_page('modules/questionnaire/index.php?course=' . $course_code);
}

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
    $sendSep = true;
} else {
    $charset = 'UTF-8';
    $sendSep = false;
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

$p = Database::get()->querySingle("SELECT pid, anonymized FROM poll
        WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
if (!$p) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

if ($sendSep) {
    echo 'sep=;', $crlf;
}

$anonymized = $p->anonymized;
$qlist = array();
if ($full) {
    $begin = true;
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);

    $users = Database::get()->queryArray("SELECT user_id FROM poll_answer_record WHERE pid = ?d ORDER BY user_id", $pid);
    foreach ($questions as $q) {
        if ($begin) {
            echo csv_escape($langUser), ';user_id;';
            $begin = false;
        } else {
            echo ';';
        }
        if ($q->qtype == QTYPE_LABEL) {
            $q->question_text = strip_tags($q->question_text);
        }
        echo csv_escape($q->question_text);
        if ($q->qtype == QTYPE_LABEL) {
            foreach ($users as $user) {
                $qlist[$user->user_id][$q->pqid] = '-';
            }
        } elseif ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {
            $answers = Database::get()->queryArray("SELECT poll_question_answer.answer_text, aid, user_id
                                FROM poll_answer_record LEFT JOIN poll_question_answer
                                     ON poll_answer_record.aid = poll_question_answer.pqaid
                                WHERE qid = ?d
                                ORDER BY user_id", $q->pqid);
            foreach ($answers as $a) {
                $answer_text = ($a->aid < 0)? $langPollUnknown: $a->answer_text;
                if (isset($qlist[$a->user_id][$q->pqid])) {
                    $qlist[$a->user_id][$q->pqid] .= ', ' . $answer_text;
                } else {
                    $qlist[$a->user_id][$q->pqid] = $answer_text;
                }
            }
        } else { // free text questions
            $answers = Database::get()->queryArray("SELECT answer_text, user_id
                                FROM poll_answer_record
                                WHERE qid = ?d
                                ORDER BY user_id", $q->pqid);
            foreach ($answers as $a) {
                $qlist[$a->user_id][$q->pqid] = $a->answer_text;
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
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid=?d ORDER BY q_position",$p->pid);
    foreach ($questions as $q) {
        if ($q->qtype == QTYPE_LABEL) {
            echo csv_escape(strip_tags($q->question_text)), $crlf, $crlf;
        } elseif ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {
            $answers = Database::get()->queryArray("SELECT COUNT(aid) AS count, aid, poll_question_answer.answer_text AS answer
                                FROM poll_answer_record
                                    LEFT JOIN poll_question_answer
                                        ON poll_answer_record.aid = poll_question_answer.pqaid
                                WHERE qid = ?d GROUP BY aid", $q->pqid);
            $answer_counts = array();
            $answer_text = array();
            $answer_total = 0;
            foreach ($answers as $a) {
                $answer_counts[] = $a->count;
                $answer_total += $a->count;
                if ($a->aid < 0) {
                    $answer_text[] = $langPollUnknown;
                } else {
                    $answer_text[] = $a->answer;
                }
            }
            echo csv_escape($langAnswers), ';', csv_escape($langResults), ' (%)', $crlf;
            foreach ($answer_counts as $i => $count) {
                $percentage = round(100 * ($count / $answer_total));
                $label = $answer_text[$i];
                echo csv_escape($label), ';', csv_escape($count), ';', csv_escape($percentage), $crlf;
            }
            echo $crlf;
        } else { // free text questions
            echo csv_escape($q->question_text), $crlf;
            $answers = Database::get()->queryArray("SELECT answer_text, user_id FROM poll_answer_record
                                                           WHERE qid = ?d", $q->pqid);
            $k = 0;
            foreach ($answers as $a) {
                $k++;
                $student_name = $anonymized? "$langStudent $k": uid_to_name($a->user_id);
                echo csv_escape($student_name), ';', csv_escape($a->answer_text), $crlf;
            }
            echo $crlf;
        }
    }
}
