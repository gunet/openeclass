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

if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
    $charset = 'Windows-1253';
    $sendSep = true;
} else {
    $charset = 'UTF-8';
    $sendSep = false;
}
$full = isset($_GET['full']) && $_GET['full'];
$crlf = "\r\n";

header("Content-Type: text/csv; charset=$charset");
header("Content-Disposition: attachment; filename=pollresults.csv");

if ($sendSep) {
    echo 'sep=;', $crlf;
}

$p = Database::get()->querySingle("SELECT pid, name, anonymized FROM poll
        WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
if (!$p) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

$anonymized = $p->anonymized;
$qlist = array();
$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d AND (email_verification = 1 OR email_verification IS NULL)", $p->pid)->total;
echo csv_escape($langInfoPoll), $crlf, $crlf;
echo csv_escape($langTitle), ';', csv_escape($p->name), $crlf;
echo csv_escape($langPollTotalAnswers), ';', csv_escape($total_participants), $crlf, $crlf, $crlf;
if ($full) {
    $begin = true;
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);

    $users = Database::get()->queryArray("SELECT uid AS user_identifier
                        FROM poll_user_record 
                        WHERE pid = ?d
                        AND uid != 0
                        UNION
                        SELECT email AS user_identifier
                        FROM poll_user_record
                        WHERE pid = ?d
                        AND email_verification = 1", $pid, $pid);
    foreach ($questions as $q) {
        if ($begin) {
            echo csv_escape($langUser), ';User ID / Email;';
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
                $qlist[$user->user_identifier][$q->pqid] = '-';
            }
        } elseif ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {  
            $answers = Database::get()->queryArray("SELECT c.answer_text, a.aid, b.uid, b.email
                                FROM poll_user_record b, poll_answer_record a 
                                LEFT JOIN poll_question_answer c
                                ON a.aid = c.pqaid
                                WHERE a.poll_user_record_id = b.id
                                AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                AND a.qid = ?d", $q->pqid);            
            foreach ($answers as $a) {
                $answer_text = ($a->aid < 0)? $langPollUnknown: $a->answer_text;
                $user_identifier = $a->uid ?: $a->email;
                if (isset($qlist[$user_identifier][$q->pqid])) {
                    $qlist[$user_identifier][$q->pqid] .= ', ' . $answer_text;
                } else {
                    $qlist[$user_identifier][$q->pqid] = $answer_text;
                }
            }
        } else { // free text questions
            $answers = Database::get()->queryArray("SELECT a.answer_text, b.uid, b.email
                                FROM poll_answer_record a, poll_user_record b
                                WHERE qid = ?d
                                AND a.poll_user_record_id = b.id
                                AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                ORDER BY uid", $q->pqid);
            foreach ($answers as $a) {
                $user_identifier = $a->uid ?: $a->email;
                $qlist[$user_identifier][$q->pqid] = $a->answer_text;
            }
        }
    }
    echo $crlf;
    $k = 0;
    foreach ($qlist as $user_identifier => $answers) {
        $k++;
        $student_name = $anonymized? "$langStudent $k": uid_to_name($user_identifier);
        if ($anonymized) {
            $user_identifier = ' - ';
        }
        echo csv_escape($student_name), ';', $user_identifier, ';',
             implode(';', array_map('csv_escape', $answers)), $crlf;
    }
} else {
    echo csv_escape($langQuestions), $crlf, $crlf;
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid=?d ORDER BY q_position",$p->pid);
    foreach ($questions as $q) {
        if ($q->qtype == QTYPE_LABEL) {
            echo csv_escape(strip_tags($q->question_text)), $crlf, $crlf;
        } else {
            echo csv_escape($q->question_text), $crlf;
            if ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {
                $answers = Database::get()->queryArray("SELECT COUNT(b.aid) AS count, b.aid, c.answer_text AS answer
                                    FROM poll_user_record a, poll_answer_record b
                                    LEFT JOIN poll_question_answer c
                                    ON b.aid = c.pqaid
                                    WHERE b.qid = ?d
                                    AND b.poll_user_record_id = a.id
                                    AND (a.email_verification = 1 OR a.email_verification IS NULL)
                                    GROUP BY b.aid", $q->pqid);
            } else {
                $answers = Database::get()->queryArray("SELECT COUNT(a.arid) AS count, a.answer_text 
                                                        FROM poll_answer_record a, poll_user_record b
                                                        WHERE a.qid = ?d 
                                                        AND a.poll_user_record_id = b.id
                                                        AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                                        GROUP BY a.answer_text", $q->pqid);                
            }
            $answer_counts = array();
            $answer_text = array();
            $answer_total = 0;
            foreach ($answers as $a) {
                $answer_counts[] = $a->count;
                $answer_total += $a->count;
                if ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {
                    if ($a->aid < 0) {
                        $answer_text[] = $langPollUnknown;
                    } else {
                        $answer_text[] = $a->answer;
                    }
                } else {
                    $answer_text[] = $a->answer_text;
                }
            }
            echo csv_escape($langAnswers), ';', csv_escape($langResults), ';', csv_escape($langResults), ' (%)', $crlf;
            foreach ($answer_counts as $i => $count) {
                $percentage = round(100 * ($count / $answer_total));
                $label = $answer_text[$i];
                echo csv_escape($label), ';', csv_escape($count), ';', csv_escape($percentage), $crlf;
            }
            echo $crlf;
        }
    }
}
