<?php

/* ========================================================================
 * Open eClass 3.10
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
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

require_once 'exercise.class.php';

$require_current_course = TRUE;
$require_editor = TRUE;

require_once '../../include/init.php';
require_once 'include/baseTheme.php';
require_once 'include/lib/csv.class.php';
require_once 'modules/exercise/question.class.php';
require_once 'modules/exercise/answer.class.php';

$exerciseId = getDirectReference($_GET['exerciseId']);
$objExercise = new Exercise();
$objExercise->read($exerciseId);
$csv = new CSV();
//$csv->debug = true;
$csv->filename = $course_code . '_' . $exerciseId . '_' . date('Y-m-d') . '.csv';

$possible_qids = array(); // possible questions
$qids_answered = array(); // answered questions
$results = array(); // `grid`. Holds final results
$headers = $output = array();

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
        }
    } else {
        $possible_qids[] = $data->question_id; // `normal` questions
    }
}

// get user questions
$s = Database::get()->queryArray("SELECT DISTINCT question_id, uid 
                FROM exercise_answer_record, exercise_user_record 
                 WHERE exercise_answer_record.eurid = exercise_user_record.eurid
                AND exercise_user_record.eid = ?d", $exerciseId);
foreach ($s as $data) {
    $qids_answered[$data->uid][] = $data->question_id;
}

$results[0][0] = '';
foreach ($possible_qids as $qid) { // for each possible question
    $results[0][] = $qid; // first `results` row holds question ids
}

$users = array_keys($qids_answered); // array of user ids
for ($i=1; $i<=count($qids_answered); $i++) {
    $results[$i][0] = $users[$i-1]; // first `results` column holds user ids
    $u = $results[$i][0]; // user id
    for ($j=1; $j<=count($possible_qids); $j++) {
        $results[$i][$j] = ''; // initialisation
    }
    foreach ($qids_answered[$u] as $answered_qid) {
        $found = array_search($answered_qid, $possible_qids, true);
        if ($found !== NULL) { // if question has been answered
            $results[$i][$found+1] = $possible_qids[$found]; // get qid
        }
    }
}

// exercise details
$exercise_details[] = $objExercise->selectTitle();
$exercise_details[] = "$langTotalScore: " . $objExercise->selectTotalWeighting();
if (!empty($objExercise->selectStartDate())) {
    $exercise_details[] = "$langPollStart: " . greek_format($objExercise->selectStartDate(), true);
}
if (!empty($objExercise->selectEndDate())) {
    $exercise_details[] = "$langPollEnd: " . greek_format($objExercise->selectEndDate(), true);
}

$csv->outputRecord($exercise_details);

// ------------------------------
// headers and question titles
// ------------------------------
$headers[] = $langSurname;
$headers[] = $langName;
$headers[] = $langAm;
$headers[] = $langGroup;

for ($j = 1; $j<count($results[0]); $j++) {
    $qid = $results[0][$j];
    $question = new Question();
    $question->read($qid);
    $question_id = $question->selectId();
    $headers[] = $question->selectTitle();
    $headers[] = "$langGradebookGrade ($langMax: ". $question->selectWeighting() . ")";
}
$headers[] = $langTotalScore;
$csv->outputRecord($headers);

// -----------------------------
// question answers data
// -----------------------------
for ($i = 1; $i<count($results); $i++) {
    for ($j = 0; $j<count($results[$i]); $j++) {
        if ($j == 0) {
            $user = $results[$i][$j]; // user id
            // user details
            $output[] = uid_to_name($user, 'surname');
            $output[] = uid_to_name($user, 'givenname');
            $output[] = uid_to_am($user);
            $output[] = user_groups($course_id, $user, 'txt');
        } else {
            $question = $results[$i][$j]; // question id
            $output[] = details($user, $question, $exerciseId); // question answer details
            $output[] = user_question_score($user, $question, $exerciseId); // question score
        }
    }
    $output[] = user_total_score($user, $exerciseId); // user total score
    $csv->outputRecord($output);
    $output = array();
}

/**
 * @brief question details
 * @param $uid
 * @param $qid
 * @param $eid
 * @return string
 */
function details($uid, $qid, $eid) {

    global $objExercise, $output;

    $content = '';
    if ($qid) {
        $sql = Database::get()->queryArray("SELECT eurid FROM exercise_user_record 
            WHERE uid = ?d AND eid = ?d", $uid, $eid);
        foreach ($sql as $data) {
            $sql2 = Database::get()->queryArray("SELECT question_id, SUM(weight) AS weight FROM exercise_answer_record 
                                    WHERE eurid = ?d AND question_id = ?d", $data->eurid, $qid);
            foreach ($sql2 as $user_question) {
                $content = question_answer_details($uid, $data->eurid, $user_question->question_id, $eid); // question answer
            }
        }
    }
    return $content;
}

/**
 * @brief user answers
 * @param $uid
 * @param $eurid
 * @param $qid
 * @param $eid
 * @return string
 */
function question_answer_details($uid, $eurid, $qid, $eid) {
    
    $content = $temp_content = '';
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
                case FILL_IN_BLANKS_TOLERANT:
                case FILL_IN_BLANKS:
                    $content .= "[" . $data->answer . "] ";
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
 * @param $uid
 * @param $qid
 * @param $eid
 * @return string
 */
function user_question_score($uid, $qid, $eid) {

    global $objExercise, $output;
    $content = '';
    if ($qid) {
        $sql = Database::get()->queryArray("SELECT eurid FROM exercise_user_record 
            WHERE uid = ?d AND eid = ?d", $uid, $eid);
        foreach ($sql as $data) {
            $sql2 = Database::get()->queryArray("SELECT question_id, SUM(weight) AS weight FROM exercise_answer_record 
                                    WHERE eurid = ?d AND question_id = ?d", $data->eurid, $qid);
            foreach ($sql2 as $user_question) {
                $content = $user_question->weight; // /question weight
            }
        }
    }
    return $content;
}


/**
 * @brief user question total score
 * @param $uid
 * @param $qid
 * @param $eid
 */
function user_total_score($uid, $eid) {

    global $objExercise;

    $data = Database::get()->querySingle("SELECT eurid, total_score, total_weighting FROM exercise_user_record 
            WHERE uid = ?d AND eid = ?d", $uid, $eid);
    $user_total_score = $objExercise->canonicalize_exercise_score($data->total_score, $data->total_weighting);
    return $user_total_score;
}