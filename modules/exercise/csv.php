<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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
require_once '../../include/init.php';
require_once 'include/lib/csv.class.php';
require_once 'modules/exercise/question.class.php';
require_once 'modules/exercise/answer.class.php';

if ($is_editor) {
    $full = isset($_GET['full']) and $_GET['full'];
    $exerciseId = getDirectReference($_GET['exerciseId']);
    $csv = new CSV();
    $csv->filename = $course_code . '_' . $exerciseId . '_' . date('Y-m-d') . '.csv';

    // where to put each question - needed for randomized exercises
    // contains a mapping of question_id => offset
    $questionOffset = array();

    // cache of answers to questions
    $answerCache = array();

    $headings = array();
    if ($full) {
        Database::get()->queryFunc('SELECT question_id, question, type
            FROM exercise_question, exercise_with_questions
            WHERE exercise_with_questions.question_id = exercise_question.id AND
                  exercise_id = ?d
            ORDER BY q_position', function ($item) use (&$headings, &$questionOffset, &$answerCache) {
                $qid = $item->question_id;
                $questionOffset[$qid] = count($headings);
                if ($item->type == FILL_IN_BLANKS or $item->type == FILL_IN_BLANKS_TOLERANT) {
                    list($answer) = Question::blanksSplitAnswer(Database::get()
                        ->querySingle('SELECT answer
                            FROM exercise_answer WHERE question_id = ?d', $qid)->answer);
                    $headings = array_merge($headings, Question::getBlanks($answer));
                } elseif ($item->type == MATCHING) {
                    $answerObj = new Answer($qid);
                    for ($i = 1; $i <= $answerObj->selectNbrAnswers(); $i++) {
                        $text = canonicalize_whitespace(html_entity_decode(
                            strip_tags($answerObj->selectAnswer($i))));
                        if ($answerObj->isCorrect($i)) {
                            // matching option from column A
                            $headings[] = $text;
                        } else {
                            // matching option from column B
                            $answerCache[$qid][$i] = $text;
                        }
                    }
                } else {
                    $headings[] = $item->question;
                }
            }, $exerciseId);
    }

    $csv->outputRecord($langSurname, $langName, $langAm, $langStart,
        $langExerciseDuration, $langStudentTotalScore, $langTotalScore,
        $headings);

    $result = Database::get()->queryArray("SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid = ?d", $exerciseId);

    foreach ($result as $row) {
        $sid = $row->uid;
        $surname = uid_to_name($sid, 'surname');
        $name = uid_to_name($sid, 'givenname');
        $am = uid_to_am($sid);

        $result2 = Database::get()->queryArray("SELECT DATE_FORMAT(record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date,
			record_end_date, TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date)) AS time_duration,
			total_score, total_weighting, eurid
			FROM `exercise_user_record` WHERE uid = ?d AND eid = ?d", $sid, $exerciseId);
        
        foreach ($result2 as $row2) {
            if ($row2->time_duration == '00:00:00' or empty($row2->time_duration)) { // for compatibility
                $duration = $langNotRecorded;
            } else {
                $duration = format_time_duration($row2->time_duration);
            }

            if ($full) {
                // how many answers for each question have we encountered so far for this row
                // needed to track fill-in-blanks multiple answers per question_id
                $questionOffsetCount = array();
                foreach (array_keys($questionOffset) as $qid) {
                    $questionOffsetCount[$qid] = 0;
                }

                // blank row template
                $values = array_fill(0, count($headings), '');

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

            $csv->outputRecord($surname, $name, $am, $row2->record_start_date,
                $duration, $row2->total_score, $row2->total_weighting,
                $values);
        }
    }
}

