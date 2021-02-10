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
$csv->debug = true;
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
        } else if ($random_criteria['criteria'] == 'difficulty') {
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

//print_a($results);

// exercise details
$exercise_details[] = $objExercise->selectTitle() . " " . $objExercise->selectStartDate() . " " . $objExercise->selectEndDate() . " " . $objExercise->selectRange();
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
    $headers[] = $langGradebookGrade;
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
    $output[] = user_total_score($user, $question_id, $exerciseId); // user total score
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

    global $langChoice, $langText, $langAnswer, $langOptions, $langCorrespondsTo;

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
                    $content .= "$langChoice: " . $data->answer_id;
                break;
                case MULTIPLE_ANSWER:
                    $temp_content .= $data->answer_id . ", ";
                    $content = "$langOptions: " . rtrim(trim($temp_content), ','); // remove last `comma`
                    break;
                case FILL_IN_BLANKS_TOLERANT:
                case FILL_IN_BLANKS:
                    $content .= "[" . $data->answer . "] ";
                break;
                case MATCHING:
                    $temp_content .= "$langCorrespondsTo: " . $data->answer_id . ", ";
                    $content = rtrim(trim($temp_content), ','); // remove last `comma`
                    break;
                case FREE_TEXT:
                    $content .= "$langText: " . $data->answer;
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
function user_total_score($uid, $qid, $eid) {

    global $objExercise;

    $content = '';
    $sql = Database::get()->queryArray("SELECT eurid, total_score, total_weighting FROM exercise_user_record 
            WHERE uid = ?d AND eid = ?d", $uid, $eid);
    foreach ($sql as $data) {
        $user_total_score = $objExercise->canonicalize_exercise_score($data->total_score, $data->total_weighting);
    }
    $content .= $user_total_score;

    return $content;
}


exit;

/*
$csv->outputRecord($langSurname, $langName, $langAm, $langGroup, $langStart,
    $langExerciseDuration, $langStudentTotalScore, $langTotalScore,
    $headings);

$result = Database::get()->queryArray("SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid = ?d", $exerciseId);

foreach ($result as $row) {
    $sid = $row->uid;
    $surname = uid_to_name($sid, 'surname');
    $name = uid_to_name($sid, 'givenname');
    $am = uid_to_am($sid);
    $ug = user_groups($course_id, $sid, 'txt');

    $result2 = Database::get()->queryArray("SELECT DATE_FORMAT(record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date,
        record_end_date, TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date)) AS time_duration,
        total_score, total_weighting, eurid, attempt_status
        FROM `exercise_user_record` WHERE uid = ?d AND eid = ?d  
        ORDER BY record_start_date DESC", $sid, $exerciseId);

    foreach ($result2 as $row2) {
        if ($row2->time_duration == '00:00:00' or empty($row2->time_duration)) { // for compatibility
            $duration = $langNotRecorded;
        } else {
            $duration = format_time_duration($row2->time_duration);
        }
        $exerciseRange = $objExercise->selectRange();
        $total_score = '';
        if ($exerciseRange > 0) {
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                $total_score = $objExercise->canonicalize_exercise_score($row2->total_score, $row2->total_weighting);
            }
            $total_weighting = $exerciseRange;
        } else {
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                $total_score = $row2->total_score;
            }
            $total_weighting = $row2->total_weighting;
        }

        if ($full) {
            // how many answers for each question have we encountered so far for this row
            // needed to track fill-in-blanks multiple answers per question_id
            $questionOffsetCount = array();
            foreach (array_keys($questionOffset) as $qid) {
                $questionOffsetCount[$qid] = 0;
            }

            // blank row template
            //$values = array_fill(0, count($headings), '');

            Database::get()->queryFunc('SELECT question_id, answer, answer_id, type
                FROM exercise_answer_record
                    JOIN exercise_question
                        ON exercise_question.id = exercise_answer_record.question_id
                WHERE eurid = ?d ORDER BY question_id, answer_record_id',
                function ($item) use (&$values, &$answerCache, &$questionOffsetCount, $questionOffset) {
                    $qid = $item->question_id;
                    $index = $questionOffset[$qid] + $questionOffsetCount[$qid];
                    if ($item->type == FREE_TEXT) {
                        $values[$index] = canonicalize_whitespace(html_entity_decode(strip_tags($item->answer)));
                    } elseif ($item->type == FILL_IN_BLANKS or $item->type == FILL_IN_BLANKS_TOLERANT) {
                        $values[$index] = $item->answer;
                    } else {
                        if (!isset($answerCache[$qid])) {
                            $answerObj = new Answer($qid);
                            for ($i = 1; $i <= $answerObj->selectNbrAnswers(); $i++) {
                                $answerCache[$qid][$i] = canonicalize_whitespace(
                                    html_entity_decode(strip_tags(
                                        $answerObj->selectAnswer($i))));
                            }
                        }
                        $answer_id = $item->answer_id;
                        if ($answer_id > 0) {
                            if (isset($answerCache[$qid][$answer_id])) {
                                $values[$index] = $answerCache[$qid][$answer_id];
                            } else {
                                // Unknown value - exercise changed?
                                $values[$index] = '## ? ##';
                            }
                        }
                    }
                    if (in_array($item->type,
                            array(MATCHING, FILL_IN_BLANKS, FILL_IN_BLANKS_TOLERANT))) {
                        $questionOffsetCount[$qid]++;
                    }
                }, $row2->eurid);
        } else {
            $values = array();
        }

        $sql = Database::get()->queryArray("SELECT exercise_answer.answer AS answer
                FROM exercise_answer_record, exercise_answer
                    WHERE eurid = ?d                             
                    AND exercise_answer_record.answer = exercise_answer.id  
                    ORDER BY exercise_answer.question_id, answer_record_id", $row2->eurid);
            print_a($sql);
            foreach ($sql as $data) {
                $values[] = $data->answer;
            }
        }

        $csv->outputRecord($surname, $name, $am, $ug, $row2->record_start_date,
            $duration, $total_score, $total_weighting,
            $values);
    }
}*/
