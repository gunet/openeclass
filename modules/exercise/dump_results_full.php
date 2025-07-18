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

$require_current_course = TRUE;
$require_course_reviewer = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/baseTheme.php';
require_once 'exercise.class.php';
require_once 'modules/exercise/question.class.php';
require_once 'modules/exercise/answer.class.php';

$exerciseId = getDirectReference($_GET['exerciseId']);
$objExercise = new Exercise();
$objExercise->read($exerciseId);

$stringValueBinder = new \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder();
$stringValueBinder->setNumericConversion(false);
\PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder($stringValueBinder);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle($langResults);
$sheet->getDefaultColumnDimension()->setWidth(30);
$filename = $course_code . '_' . $exerciseId . '_' . date('Y-m-d') . '.xlsx';
$course_title = course_id_to_title($course_id);

$out[] = [ $course_title ];

// exercise details
$exercise_details  = $objExercise->selectTitle();
$exercise_details .= " $langTotalScore: " . $objExercise->selectTotalWeighting();
if (!empty($objExercise->selectStartDate())) {
    $exercise_details .= " $langStart: " . format_locale_date(strtotime($objExercise->selectStartDate()), 'short');
}
if (!empty($objExercise->selectEndDate())) {
    $exercise_details .= " $langPollEnd: " . format_locale_date(strtotime($objExercise->selectEndDate()), 'short');
}

$out[] = [ $exercise_details ];
$out[] = [];

$possible_qids = []; // possible questions
$results = []; // output `grid`. Holds final results.
$headers = $output = [];

// get possible questions
$item = Database::get()->queryArray('SELECT question_id, exercise_id, random_criteria
            FROM exercise_with_questions
                WHERE exercise_id = ?d
            ORDER BY q_position', $exerciseId);
foreach ($item as $data) { // check for random questions with criteria
    if (!is_null($data->random_criteria)) {
        $random_criteria = unserialize($data->random_criteria);
        next($random_criteria);
        $number = key($random_criteria);
        $c = $random_criteria[$number];
        if ($random_criteria['criteria'] == 'difficulty') {
            $result = Database::get()->queryArray("SELECT id FROM `exercise_question`
                            WHERE difficulty = ?d AND course_id = ?d", $c, $course_id);
            foreach ($result as $d1) {
                $possible_qids[] = $d1->id;
            }
        } else if ($random_criteria['criteria'] == 'category') {
            $result = Database::get()->queryArray("SELECT id FROM `exercise_question`
                            WHERE category = ?d AND course_id = ?d", $c, $course_id);
            foreach ($result as $d2) {
                $possible_qids[] = $d2->id;
            }
        } else if ($random_criteria['criteria'] == 'difficultycategory') {
            $result = Database::get()->queryArray("SELECT id FROM `exercise_question`
                            WHERE difficulty = ?d AND category = ?d AND course_id = ?d", $c[0], $c[1], $course_id);
            foreach ($result as $d2) {
                $possible_qids[] = $d2->id;
            }
        }
    } else {
        $possible_qids[] = $data->question_id; // `normal` questions
    }
}

// ------------------------------
// headers and question titles
// ------------------------------
$headers[] = $langSurname;
$headers[] = $langName;
$headers[] = $langAm;
$headers[] = $langEmail;
$headers[] = $langGroup;

foreach ($possible_qids as $qid) {
    $question = new Question();
    $question->read($qid);
    $question_id = $question->selectId();
    $headers[] = $question->selectTitle() . " (id: " . $question_id . ")";
    $headers[] = "$langGradebookGrade ($langMax: ". $question->selectWeighting() . ")";
}
$headers[] = $langTotalScore;
$out[] = $headers;

// get exercise attempts (except `canceled` attempts)
$q = Database::get()->queryArray("(SELECT uid, eurid, surname, givenname, am, total_score, total_weighting
                                            FROM exercise_user_record
                                            JOIN user ON uid = id
                                            WHERE eid = ?d
                                            AND attempt_status != " . ATTEMPT_CANCELED . "
                                            )
                                        UNION
                                            (SELECT 0 as uid, eurid, '$langAnonymous' AS surname, '$langUser' AS givenname, '' as am, total_score, total_weighting
                                                FROM `exercise_user_record` WHERE eid = ?d
                                                AND attempt_status != " . ATTEMPT_CANCELED . "
                                                AND uid = 0)
                                            ORDER BY surname, givenname"
                                        , $exerciseId, $exerciseId);

$total_score = [];
foreach ($q as $d) { // for each attempt
    $eurid = $d->eurid; // exercise user record id
    $total_score[$eurid] = user_total_score($d); // Canonicalized user final score
    $qids_answered = []; // answered questions;
    // get user questions
    $s = Database::get()->queryArray("SELECT DISTINCT question_id, uid
                FROM exercise_answer_record, exercise_user_record
                 WHERE exercise_answer_record.eurid = exercise_user_record.eurid
                AND exercise_user_record.eid = ?d
                AND exercise_user_record.eurid = ?d", $exerciseId, $eurid);
    foreach ($s as $data) {
        $qids_answered[$data->uid][] = $data->question_id;
    }

    $results[0][0] = '';
    foreach ($possible_qids as $qid) { // for each possible question
        $results[0][] = $qid; // first `results` row holds question ids
    }

    $user = array_keys($qids_answered); // user id
    for ($i = 1; $i <= count($qids_answered); $i++) {
        $results[$i][0] = $user[$i - 1]; // first `results` column holds user id
        $u = $results[$i][0]; // user id
        for ($j = 1; $j <= count($possible_qids); $j++) {
            $results[$i][$j] = ''; // initialisation
        }
        foreach ($qids_answered[$u] as $answered_qid) {
            $found = array_search($answered_qid, $possible_qids, true);
            if ($found !== NULL) { // if question has been answered
                $results[$i][$found + 1] = $possible_qids[$found]; // get qid
            }
        }
    }

    // -----------------------------
    // question answers data
    // -----------------------------
    for ($i = 1; $i < count($results); $i++) {
        for ($j = 0; $j < count($results[$i]); $j++) {
            if ($j == 0) {
                $user = $results[$i][$j]; // user id
                // user details
                $output[] = uid_to_name($user, 'surname');
                $output[] = uid_to_name($user, 'givenname');
                $output[] = uid_to_am($user);
                $output[] = uid_to_email($user);
                $output[] = user_groups($course_id, $user, 'txt');
            } else {
                $question = $results[$i][$j]; // question id
                $output[] = details($question, $eurid); // question answer details
                $output[] = floatval(user_question_score($question, $eurid)); // question score
            }
        }
        $output[] = $total_score[$eurid];
        $out[] = $output;
        $output = array();
    }
}

$sheet->mergeCells("A1:G1");
$sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
$sheet->getCell('A2')->getStyle()->getFont()->setItalic(true);
for ($i = 1; $i <= 5; $i++) {
    $cells = [$i, 4];
    $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
}
// create spreadsheet
$sheet->fromArray($out, NULL);

// file output
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
set_content_disposition('attachment', $filename);
$writer->save("php://output");
exit;


/**
 * @brief question details
 * @param $qid
 * @param $eurid
 * @return string
 */
function details($qid, $eurid) {

    $content = '';
    if ($qid) {
        $sql2 = Database::get()->queryArray("SELECT question_id, SUM(weight) AS weight FROM exercise_answer_record
                                WHERE eurid = ?d AND question_id = ?d 
                                GROUP BY question_id", $eurid, $qid);
        foreach ($sql2 as $user_question) {
            $content = question_answer_details($eurid, $user_question->question_id); // question answer
        }
    }
    return $content;
}

/**
 * @brief user answers
 * @param $eurid
 * @param $qid
 * @return string
 */
function question_answer_details($eurid, $qid) {

    global $langChoice;

    $content = $temp_content = '';
    $temp_array_content = [];
    $q = Database::get()->queryArray("SELECT question_id, answer, answer_id, `type`
                       FROM exercise_answer_record
                            JOIN exercise_question
                        ON exercise_question.id = exercise_answer_record.question_id
                            WHERE eurid = ?d AND question_id = ?d", $eurid, $qid);

    foreach ($q as $data) {
            switch ($data->type) {
                case UNIQUE_ANSWER:
                case TRUE_FALSE:
                    $a = Database::get()->querySingle("SELECT answer FROM exercise_answer
                                        WHERE question_id = ?d
                                        AND r_position = ?d",
                                    $data->question_id, $data->answer_id);
                    if ($a) {
                        $content .= html2text($a->answer);
                    } else {
                        $content .= '';
                    }
                break;
                case MULTIPLE_ANSWER:
                    $a = Database::get()->querySingle("SELECT answer FROM exercise_answer
                                        WHERE question_id = ?d
                                        AND r_position = ?d",
                                    $data->question_id, $data->answer_id);
                    if ($a) {
                        $temp_content .= html2text($a->answer) . " -- ";
                    } else {
                        $temp_content .= '';
                    }
                    $content = rtrim(trim($temp_content), '-- '); // remove last `--`
                    break;
                case ORDERING:
                    $temp_array_content[] = $data->answer;
                    $temp_content = implode(' -> ', $temp_array_content);
                    $content = $temp_content;
                    break;
                case DRAG_AND_DROP_TEXT:
                case DRAG_AND_DROP_MARKERS:
                case CALCULATED:
                    $objAnswerTmp = new Answer($data->question_id);
                    if ($data->type == DRAG_AND_DROP_TEXT or $data->type == DRAG_AND_DROP_MARKERS) {
                        $definedAnswers = $objAnswerTmp->get_drag_and_drop_answer_text();
                        $correctAnswer = $definedAnswers[$data->answer_id-1];
                    } else {
                        $correctAnswer = $objAnswerTmp->get_correct_calculated_answer($data->question_id);
                        
                    }
                    $temp_array_content[] = "[" . $data->answer . "|" . $correctAnswer . "]";
                    $temp_content = implode(' -- ', $temp_array_content);
                    $content = $temp_content;
                    unset($objAnswerTmp);
                    break;
                case FILL_IN_BLANKS_TOLERANT:
                case FILL_IN_BLANKS:
                    $content .= "[" . $data->answer . "] ";
                break;
                case FILL_IN_FROM_PREDEFINED_ANSWERS:
                    if ($data->answer > 0) {
                        $content .= "[" . $langChoice . ": " . $data->answer . "] ";
                    } else {
                        $content .= " --- ";
                    }
                break;
                case MATCHING:
                    $col_a = Database::get()->querySingle("SELECT answer FROM exercise_answer
                                        WHERE question_id = ?d
                                        AND r_position = ?d",
                                    $data->question_id, $data->answer_id);
                    $col_b = Database::get()->querySingle("SELECT answer FROM exercise_answer
                                        WHERE question_id = ?d
                                        AND r_position = ?d",
                                    $data->question_id, $data->answer);
                    if ($col_a) {
                        $col_a_answer = $col_a->answer;
                    } else {
                        $col_a_answer = '';
                    }
                    if ($col_b) {
                        $col_b_answer = $col_b->answer;
                    } else {
                        $col_b_answer = '';
                    }
                    $temp_content .= html2text($col_b_answer) . " ---> " . html2text($col_a_answer) . ", ";
                    $content = rtrim(trim($temp_content), ','); // remove last `comma`
                break;
                case FREE_TEXT:
                    $content .= html2text($data->answer);
                break;
            }
    }
    return  $content;
}

/**
 * @brief user question score
 * @param $qid
 * @param $eurid
 * @return string
 */
function user_question_score($qid, $eurid) {

    $content = '';
    if ($qid) {
        $uq = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record
                                WHERE eurid = ?d AND question_id = ?d", $eurid, $qid);
        $content = $uq->weight; // /question weight
    }
    return "$content";
}


/**
 * @brief User attempt canonicalized final score
 * @param $user_record
 */
function user_total_score($user_record) {
    global $objExercise;

    return $objExercise->canonicalize_exercise_score($user_record->total_score, $user_record->total_weighting);
}
