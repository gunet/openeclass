<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$require_current_course = true;
$require_course_reviewer = true;
require_once '../../include/baseTheme.php';
require_once 'modules/questionnaire/functions.php';

if (!isset($_GET['pid'])) {
    redirect_to_home_page();
} else {
    $pid = intval($_GET['pid']);
}

$full = isset($_GET['full']) && $_GET['full'];

$p = Database::get()->querySingle("SELECT pid, name, anonymized FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
if (!$p) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langResults);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . '_poll_results_' . ($full ? 'full_' : '') . $p->name . '.xlsx';
$course_title = course_id_to_title($course_id);

$qlist = [];
$submit_date = [];
$anonymized = $p->anonymized;
$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total
    FROM poll_user_record WHERE pid = ?d AND
         (email_verification = 1 OR email_verification IS NULL)", $p->pid)->total;

$poll_title = $p->name . " (". $langPollTotalAnswers . ": " . $total_participants . ")";
$data[] = [ $poll_title ];
$data[] = [];

if ($full) { // user questions results
    if ($anonymized) {
        $heading = array($langName);
    } else {
        $heading = array($langSurname, $langName, $langAm, $langUsername, $langEmail);
    }
    $heading[] = $langDate;
    $questions = Database::get()->queryArray('SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position', $pid);
    $users = Database::get()->queryArray("SELECT uid AS user_identifier
                                            FROM poll_user_record
                                                WHERE pid = ?d
                                                AND uid != 0
                                            UNION
                                                SELECT email AS user_identifier
                                                FROM poll_user_record
                                                WHERE pid = ?d
                                                AND email_verification = 1",
                                        $pid, $pid);

    $q_counter = 0;
    foreach ($questions as $q) {
        if ($q->qtype == QTYPE_TABLE) {
            $sub_questions = Database::get()->queryArray("SELECT answer_text,sub_question FROM poll_question_answer WHERE pqid = ?d", $q->pqid);
            if (count($sub_questions) > 0) {
                foreach ($sub_questions as $sq) {
                    $questions[] = (object) array("pqid" => $q->pqid, 
                                                  "pid" => $q->pid, 
                                                  "question_text" => $sq->answer_text, 
                                                  "qtype" => $q->qtype, 
                                                  "q_position" => $q->q_position, 
                                                  "q_scale" => $q->q_scale, 
                                                  "description" => $q->description, 
                                                  "answer_scale" => $q->answer_scale,
                                                  "q_row" => $q->q_row,
                                                  "q_column" => $q->q_column,
                                                  "sub_question" => $sq->sub_question);
                }
            }
            unset($questions[$q_counter]);
        }
        $q_counter++;
    }

    $headingQ = [];
    foreach ($questions as $q) {
        if ($q->qtype == QTYPE_LABEL) {
            $q->question_text = strip_tags($q->question_text);
        }
        $heading[] = $q->question_text;
        $headingQ[] = (isset($q->sub_question)) ? $q->pqid . '_' . $q->sub_question : $q->pqid;
        if ($q->qtype == QTYPE_LABEL) {
            foreach ($users as $user) {
                $qlist[$user->user_identifier][$q->pqid] = '-';
            }
        } elseif ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {
            $answers = Database::get()->queryArray("SELECT c.answer_text, a.aid, b.uid, b.email, a.submit_date
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
                if (!isset($submit_date[$user_identifier])) {
                    $submit_date[$user_identifier] = format_locale_date(strtotime($a->submit_date), 'short');
                }
            }
        } elseif ($q->qtype == QTYPE_TABLE) {
            $answers = Database::get()->queryArray("SELECT a.answer_text, a.sub_qid,  b.uid, b.email, a.submit_date
                                                    FROM poll_answer_record a, poll_user_record b
                                                    WHERE a.qid = ?d
                                                    AND a.sub_qid = ?d
                                                    AND a.poll_user_record_id = b.id
                                                    AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                                    ORDER BY uid", $q->pqid, $q->sub_question);

            foreach ($answers as $a) {
                $answer_text = $a->answer_text;
                $user_identifier = $a->uid ?: $a->email;
                $subQuestion = $q->pqid . '_' .$a->sub_qid;
                if (isset($qlist[$user_identifier][$subQuestion])) {
                    $qlist[$user_identifier][$subQuestion] .= ', ' . $answer_text;
                } else {
                    $qlist[$user_identifier][$subQuestion] = $answer_text;
                }
                if (!isset($submit_date[$user_identifier])) {
                    $submit_date[$user_identifier] = format_locale_date(strtotime($a->submit_date), 'short');
                }
            }
        } else { // free text questions
            $answers = Database::get()->queryArray("SELECT a.answer_text, b.uid, b.email, a.submit_date
                                FROM poll_answer_record a, poll_user_record b
                                WHERE qid = ?d
                                AND a.poll_user_record_id = b.id
                                AND (b.email_verification = 1 OR b.email_verification IS NULL)
                                ORDER BY uid", $q->pqid);
            foreach ($answers as $a) {
                $user_identifier = $a->uid ?: $a->email;
                $qlist[$user_identifier][$q->pqid] = $a->answer_text;
                if (!isset($submit_date[$user_identifier])) {
                    $submit_date[$user_identifier] = format_locale_date(strtotime($a->submit_date), 'short');
                }
            }
        }
    }
    $k = 0;
    $data[] = $heading;
    foreach ($qlist as $user_identifier => $answers) {
        $answers_keys = array_keys($answers);
        $result = array_diff($headingQ,$answers_keys);
        if (count($result) > 0) {
            foreach ($result as $key => $a) {
                $value = array($a => '');
                $answers = array_merge(array_slice($answers, 0, $key), $value, array_slice($answers, $key));
            }
        }

        $k++;
        if ($anonymized) {
            $user_info = [ "$langStudent $k"];
        } else {
            $user_info = get_user($user_identifier);
        }
        $user_info[] = $submit_date[$user_identifier];
        $data[] = array_merge($user_info, $answers);
    }


    $sheet->mergeCells("A1:F1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    for ($i = 1; $i <= 6; $i++) {
        $cells = [$i, 3];
        $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
    }

} else { // percentage results
    $questions = Database::get()->queryArray('SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position', $p->pid);
    foreach ($questions as $q) {
        if ($q->qtype == QTYPE_LABEL) {
            $questions_text = strip_tags($q->question_text);
            $data[] = [ $questions_text ];
        } else {
            $questions_text = $q->question_text;
            $data[] = [ $questions_text ];
            if ($q->qtype == QTYPE_SINGLE or $q->qtype == QTYPE_MULTIPLE) {
                $answers = Database::get()->queryArray("SELECT COUNT(b.aid) AS count, b.aid, c.answer_text AS answer
                                    FROM poll_user_record a, poll_answer_record b
                                    LEFT JOIN poll_question_answer c
                                    ON b.aid = c.pqaid
                                    WHERE b.qid = ?d
                                    AND b.poll_user_record_id = a.id
                                    AND (a.email_verification = 1 OR a.email_verification IS NULL)
                                    GROUP BY b.aid, c.answer_text", $q->pqid);
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
            $data[] = [ '', $langResults, $langResults . ' (%)' ];
            foreach ($answer_counts as $i => $count) {
                $percentage = round(100 * ($count / $answer_total));
                $label = $answer_text[$i];
                $data[] = [ $label, $count, $percentage ];
            }
            $data[] = [];
        }
    }
    $sheet->mergeCells("A1:C1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    for ($j = 3; $j <= count($data); $j = $j+6) {
        for ($i = 1; $i <= 3; $i++) {
            $cells = [$i, $j];
            $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
        }
    }
}

// create spreadsheet
$sheet->fromArray($data, NULL);

// file output
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
set_content_disposition('attachment', $filename);
$writer->save("php://output");
exit;

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
