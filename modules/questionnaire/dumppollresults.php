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
require_once 'include/lib/csv.class.php';

if (!isset($_GET['pid'])) {
    redirect_to_home_page();
} else {
    $pid = intval($_GET['pid']);
}

$full = isset($_GET['full']) && $_GET['full'];

$csv = new CSV();
if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
    $csv->setEncoding('UTF-8');
}

$p = Database::get()->querySingle("SELECT pid, name, anonymized FROM poll
        WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
if (!$p) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

$csv->filename = $course_code . '_poll_results_' . ($full ? 'full_' : '') . $p->name . '.csv';

$anonymized = $p->anonymized;
$qlist = array();
$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total
    FROM poll_user_record WHERE pid = ?d AND
         (email_verification = 1 OR email_verification IS NULL)", $p->pid)->total;
$csv->outputRecord($langInfoPoll)
    ->outputRecord($langTitle, $p->name)
    ->outputRecord($langPollTotalAnswers, $total_participants)
    ->outputRecord();
if ($full) {
    if ($anonymized) {
        $heading = array($langName);
    } else {
        $heading = array($langSurname, $langName, $langAm, $langUsername, $langEmail);
    }
    $questions = Database::get()->queryArray('SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position', $pid);

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
        if ($q->qtype == QTYPE_LABEL) {
            $q->question_text = strip_tags($q->question_text);
        }
        $heading[] = $q->question_text;
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
    $k = 0;
    $csv->outputRecord($heading);
    foreach ($qlist as $user_identifier => $answers) {
        $k++;
        if ($anonymized) {
            $user_info = "$langStudent $k";
        } else {
            $user_info = get_user($user_identifier);
        }
        $csv->outputRecord($user_info, $answers);
    }
} else {
    $csv->outputRecord($langQuestions);
    $questions = Database::get()->queryArray('SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position',$p->pid);
    foreach ($questions as $q) {
        if ($q->qtype == QTYPE_LABEL) {
            $csv->outputRecord(strip_tags($q->question_text));
        } else {
            $csv->outputRecord($q->question_text);
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
            $csv->outputRecord($langAnswers, $langResults, $langResults . ' (%)');
            foreach ($answer_counts as $i => $count) {
                $percentage = round(100 * ($count / $answer_total));
                $label = $answer_text[$i];
                $csv->outputRecord($label, $count, $percentage);
            }
            $csv->outputRecord();
        }
    }
}

function get_user($uid) {
    global $langAnonymous;

    $info = Database::get()->querySingle('SELECT username, am, email, givenname, surname
        FROM user WHERE id = ?d', $uid);
    if ($info) {
        return array($info->surname, $info->givenname, $info->am,
            $info->username, $info->email);
    } else {
        return array($langAnonymous, '-', '-', '-', $uid);
    }
}

