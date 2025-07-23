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
/*
 * @brief class Exercise
 *
 */

require_once 'question.class.php';
require_once 'answer.class.php';

if (file_exists('include/log.class.php')) {
    require_once 'include/log.class.php';
} elseif (file_exists('../../include/log.class.php')) {
    require_once '../../include/log.class.php';
} elseif (file_exists('../../../include/log.class.php')) {
    require_once '../../../include/log.class.php';
}

if (!class_exists('Exercise')) {

    /**
     * @brief This class allows to instantiate an object of type Exercise
     */
    class Exercise
    {

        private $id;
        private $exercise;
        private $description;
        private $general_feedback;
        private $type;
        private $range;
        private $startDate;
        private $endDate;
        private $tempSave;
        private $timeConstraint;
        private $attemptsAllowed;
        private $random;
        private $shuffle;
        private $active;
        private $results;
        private $score;
        private $ip_lock;
        private $password_lock;
        private $assign_to_specific;
        private $calc_grade_method;
        private $questionList;  // array with the list of this exercise's questions
        private $public;
        private $continueTimeLimit;
        private $totalweight;
        private $options;
        private $is_exam;

        /**
         * constructor of the class
         *
         * @author - Olivier Brouckaert
         */
        public function __construct()
        {
            $this->id = 0;
            $this->exercise = '';
            $this->description = '';
            $this->general_feedback = '';
            $this->type = MULTIPLE_PAGE_TYPE;
            $this->range = 0;
            $this->startDate = date("Y-m-d H:i:s");
            $this->endDate = null;
            $this->tempSave = 0;
            $this->timeConstraint = 0;
            $this->attemptsAllowed = 0;
            $this->random = 0;
            $this->shuffle = 0;
            $this->active = 1;
            $this->public = 1;
            $this->results = 1;
            $this->score = 1;
            $this->ip_lock = null;
            $this->assign_to_specific = 0;
            $this->password_lock = null;
            $this->questionList = array();
            $this->continueTimeLimit = 5; // minutes
            $this->calc_grade_method = 1;
            $this->options = [];
            $this->is_exam = 0;
        }

        /**
         * read exercise information from database
         *
         * @param - integer $id - exercise ID
         * @return - boolean - true if exercise exists, otherwise false
         * @author - Olivier Brouckaert
         */
        public function read($id)
        {
            global $course_id;

            $object = Database::get()->querySingle("SELECT title, description, general_feedback, type, `range`, start_date, end_date, temp_save, time_constraint,
                                                    attempts_allowed, random, shuffle, active, public, results, score, ip_lock, password_lock,
                                                    assign_to_specific, calc_grade_method, continue_time_limit, options, is_exam
                                                FROM `exercise` WHERE course_id = ?d AND id = ?d", $course_id, $id);

            // if the exercise has been found
            if ($object) {
                $this->id = $id;
                $this->exercise = $object->title;
                $this->description = $object->description;
                $this->general_feedback = $object->general_feedback;
                $this->type = $object->type;
                $this->range = $object->range;
                $this->startDate = $object->start_date;
                $this->endDate = $object->end_date;
                $this->tempSave = $object->temp_save;
                $this->timeConstraint = $object->time_constraint;
                $this->attemptsAllowed = $object->attempts_allowed;
                $this->random = $object->random;
                $this->shuffle = $object->shuffle;
                $this->active = $object->active;
                $this->public = $object->public;
                $this->results = $object->results;
                $this->score = $object->score;
                $this->ip_lock = $object->ip_lock;
                $this->password_lock = $object->password_lock;
                $this->assign_to_specific = $object->assign_to_specific;
                $this->calc_grade_method = $object->calc_grade_method;
                $this->continueTimeLimit = $object->continue_time_limit;
                $this->options = $object->options? json_decode($object->options, true): [];
                $this->is_exam = $object->is_exam;

                $result = Database::get()->queryArray("SELECT question_id, q_position, random_criteria
                    FROM `exercise_with_questions`
                    WHERE exercise_id = ?d
                    ORDER BY q_position, question_id", $id);

                // fills the array with the question ID for this exercise
                $k = 1;
                foreach ($result as $row) {
                    if (is_null($row->question_id)) {
                        $this->questionList[$k] = unserialize($row->random_criteria);
                    } else {
                        $this->questionList[$k] = $row->question_id;
                    }
                    $k++;
                }
                // find the total weighting of an exercise
                $this->totalweight = Database::get()->querySingle("SELECT SUM(exercise_question.weight) AS totalweight
                                                FROM exercise_question, exercise_with_questions
                                                WHERE exercise_question.course_id = ?d
                                                AND exercise_question.id = exercise_with_questions.question_id
                                                AND exercise_with_questions.exercise_id = ?d", $course_id, $id)->totalweight;
                return true;
            }
            // exercise not found
            return false;
        }

        /**
         * returns the exercise ID
         *
         * @return - integer - exercise ID
         * @author - Olivier Brouckaert
         */
        public function selectId()
        {
            return $this->id;
        }

        /**
         * returns the exercise title
         *
         * @return - string - exercise title
         * @author - Olivier Brouckaert
         */
        public function selectTitle()
        {
            return $this->exercise;
        }

        /**
         * set title
         *
         * @param string $value
         * @author Sebastien Piraux <pir@cerdecam.be>
         */
        public function setTitle($value)
        {
            $this->exercise = trim($value);
        }

        /**
         * @brief returns the exercise description
         * @author - Olivier Brouckaert
         */
        public function selectDescription()
        {
            return $this->description;
        }

        /**
         * @brief returns exercise feedback
         * @return mixed
         */
        public function selectFeedback()
        {
            return $this->general_feedback;
        }

        /**
         * set description
         *
         * @param string $value
         * @author Sebastien Piraux <pir@cerdecam.be>
         */
        public function setDescription($value)
        {
            $this->description = trim($value);
        }

        /**
         *
         * @return total weighting of an exercise
         */
        public function selectTotalWeighting()
        {
            return $this->totalweight;
        }

        /**
         * returns the exercise type
         *
         * @return - integer - exercise type
         * @author - Olivier Brouckaert
         */
        public function selectType()
        {
            return $this->type;
        }

        public function selectRange()
        {
            return $this->range;
        }

        public function selectStartDate()
        {
            return $this->startDate;
        }

        public function selectEndDate()
        {
            return $this->endDate;
        }

        public function selectTempSave()
        {
            return $this->tempSave;
        }

        public function selectTimeConstraint()
        {
            return $this->timeConstraint;
        }

        public function selectAttemptsAllowed()
        {
            return $this->attemptsAllowed;
        }

        public function selectResults()
        {
            return $this->results;
        }

        public function selectScore()
        {
            return $this->score;
        }

        public function selectIPLock()
        {
            return $this->ip_lock;
        }

        public function selectPasswordLock()
        {
            return $this->password_lock;
        }

        public function selectAssignToSpecific()
        {
            return $this->assign_to_specific;
        }

        public function getCalcGradeMethod()
        {
            return $this->calc_grade_method;
        }
        public function continueTimeLimit()
        {
            return $this->continueTimeLimit;
        }

        public function isRandom()
        {
            return $this->random;
        }

        public function selectShuffle()
        {
            return $this->shuffle;
        }

        /**
         * returns the exercise status (1 = enabled ; 0 = disabled)
         *
         * @return - boolean - true if enabled, otherwise false
         * @author - Olivier Brouckaert
         */
        public function selectStatus()
        {
            return $this->active;
        }

        /**
         * returns the array with the question ID list
         *
         * @return - array - question ID list
         * @author - Olivier Brouckaert
         */
        public function selectQuestionList()
        {
            return $this->questionList;
        }

        public function isExam()
        {
            return $this->is_exam;
        }

        /**
         * @brief get questions (with dynamic criteria or not)
         * @return - array with question is
         */
        public function selectQuestions()
        {

            $q = array(); // temp array
            foreach ($this->questionList as $id => $qid) {
                if (is_array($qid)) { // check for random questions
                    if ($qid['criteria'] == 'difficulty') { // random difficulty questions
                        next($qid);
                        $number = key($qid);
                        $difficulty = $qid[$number];
                        $random_questions = $this->selectQuestionListWithDifficulty($number, $difficulty);
                        if (count($random_questions) > 0) { // found?
                            $q = array_merge($q, $random_questions);
                        }
                    } else if ($qid['criteria'] == 'category') { // random category questions
                        next($qid);
                        $number = key($qid);
                        $category = $qid[$number];
                        $random_questions = $this->selectQuestionListWithCategory($number, $category);
                        if (count($random_questions) > 0) { // found?
                            $q = array_merge($q, $random_questions);
                        }
                    } else if ($qid['criteria'] == 'difficultycategory') {
                        next($qid);
                        $number = key($qid);
                        $difficulty = $qid[$number][0];
                        $category = $qid[$number][1];
                        $random_questions = $this->selectQuestionListWithDifficultyAndCategory($number, $difficulty, $category);
                        if (count($random_questions) > 0) { // found?
                            $q = array_merge($q, $random_questions);
                        }
                    }
                } else { // `normal` questions
                    $q[] = $this->questionList[$id];
                }
            }
            // make array start from 1
            array_unshift($q, null);
            unset($q[0]);

            $this->questionList = $q; // new question List

            return $this->questionList;
        }

        /**
         * @brief get random questions with difficulty
         * $param $number
         * @param $difficulty
         * @return array
         */
        public function selectQuestionListWithDifficulty($number, $difficulty)
        {

            global $course_id;
            $questions = array();

            $result = Database::get()->queryArray("SELECT id
                            FROM `exercise_question`
                                WHERE difficulty = ?d
                                AND course_id = ?d",
                $difficulty, $course_id);

            if (count($result) > 0) { // if questions found
                foreach ($result as $row) {
                    $questions[] = $row->id;
                }
                shuffle($questions);
                $questions = array_slice($questions, 0, $number);
            }
            return $questions;
        }

        /**
         * @brief get random questions with category
         * @param $number
         * @param $category
         * @return array
         */
        public function selectQuestionListWithCategory($number, $category)
        {

            global $course_id;
            $questions = array();

            $result = Database::get()->queryArray("SELECT id
                            FROM `exercise_question`
                                WHERE category = ?d
                                AND course_id = ?d",
                $category, $course_id);

            if (count($result) > 0) { // if questions found
                foreach ($result as $row) {
                    $questions[] = $row->id;
                }
                shuffle($questions);
                $questions = array_slice($questions, 0, $number);
            }
            return $questions;
        }


        /**
         * $brieg get random questions with difficulty and category
         * @param $number
         * @param $difficulty
         * @param $category
         * @return array
         */
        public function selectQuestionListWithDifficultyAndCategory($number, $difficulty, $category)
        {

            global $course_id;
            $questions = array();

            $result = Database::get()->queryArray("SELECT id
                            FROM `exercise_question`
                                WHERE category = ?d
                                AND difficulty = ?d
                                AND course_id = ?d",
                $category, $difficulty, $course_id);

            if (count($result) > 0) { // if questions found
                foreach ($result as $row) {
                    $questions[] = $row->id;
                }
                shuffle($questions);
                $questions = array_slice($questions, 0, $number);
            }
            return $questions;
        }


        /**
         * returns the number of questions in this exercise
         *
         * @return - integer - number of questions
         * @author - Olivier Brouckaert
         */
        public function selectNbrQuestions()
        {
            return sizeof($this->questionList);
        }

        /**
         * @brief shuffle questions
         * @return array
         */
        public function selectShuffleQuestions()
        {

            $questions = $this->questionList;
            shuffle($questions); // shuffle all questions
            if ($this->random > 0) {
                $questions = array_slice($questions, 0, $this->random);  // shuffle $this->random of them
            }

            // make array keys start from 1
            array_unshift($questions, null);
            unset($questions[0]);

            return $questions;
        }

        /**
         * @brief checks if exercise has questions with random criteria
         * @return bool
         */
        public function hasQuestionListWithRandomCriteria()
        {
            $result = Database::get()->queryArray("SELECT random_criteria FROM exercise_with_questions
                                            WHERE exercise_id = ?d", $this->id);
            foreach ($result as $data) {
                if (!is_null($data->random_criteria)) {
                    return true;
                    break;
                }
            }
            return false;
        }

        /**
         * returns 'true' if the question ID is in the question list
         *
         * @param - integer $questionId - question ID
         * @return - boolean - true if in the list, otherwise false
         * @author - Olivier Brouckaert
         */
        public function isInList($questionId)
        {
            return in_array($questionId, $this->questionList);
        }

        /**
         * changes the exercise title
         *
         * @param - string $title - exercise title
         * @author - Olivier Brouckaert
         */
        public function updateTitle($title)
        {
            $this->exercise = $title;
        }

        /**
         * changes the exercise description
         *
         * @param - string $description - exercise description
         * @author - Olivier Brouckaert
         */
        public function updateDescription($description)
        {
            $this->description = $description;
        }

        public function updateFeedback($feedback)
        {
            $this->general_feedback = $feedback;
        }
        /**
         * changes the exercise type
         *
         * @param - integer $type - exercise type
         * @author - Olivier Brouckaert
         */
        public function updateType($type)
        {
            $this->type = $type;
        }

        public function updateRange($range)
        {
            $this->range = $range;
        }

        public function updateStartDate($startDate)
        {
            $this->startDate = $startDate;
        }

        public function updateEndDate($endDate)
        {
            $this->endDate = $endDate;
        }

        public function updateTempSave($tempSave)
        {
            $this->tempSave = $tempSave;
        }

        public function updateTimeConstraint($timeConstraint)
        {
            $this->timeConstraint = $timeConstraint;
        }

        public function updateAttemptsAllowed($attemptsAllowed)
        {
            $this->attemptsAllowed = $attemptsAllowed;
        }

        public function updateResults($results)
        {
            $this->results = $results;
        }

        public function updateScore($score)
        {
            $this->score = $score;
        }

        public function updateContinueTimeLimit($minutes)
        {
            $this->continueTimeLimit = intval($minutes);
            if ($this->continueTimeLimit < 0) {
                $this->continueTimeLimit = 0;
            }
        }

        public function updateIPLock($ips)
        {
            $this->ip_lock = (empty($ips)) ? null : $ips;
        }

        public function updatePasswordLock($password)
        {
            $this->password_lock = (empty($password)) ? null : $password;
        }

        public function setCalcGradeMethod()
        {
            $this->calc_grade_method = 1;
        }

        public function updateAssignToSpecific($assign_to_specific)
        {
            $this->assign_to_specific = $assign_to_specific;
        }

        public function assignTo($assignees)
        {
            Database::get()->query("DELETE FROM exercise_to_specific WHERE exercise_id = ?d", $this->id);
            if ($this->assign_to_specific && !empty($assignees)) {
                if ($this->assign_to_specific == 1) {
                    foreach ($assignees as $assignee_id) {
                        Database::get()->query("INSERT INTO exercise_to_specific (user_id, exercise_id) VALUES (?d, ?d)", $assignee_id, $this->id);
                    }
                } else {
                    foreach ($assignees as $group_id) {
                        Database::get()->query("INSERT INTO exercise_to_specific (group_id, exercise_id) VALUES (?d, ?d)", $group_id, $this->id);
                    }
                }
            }
        }


        public function setRandom($random)
        {
            $this->random = $random;
        }

        public function setShuffle($shuffle)
        {
            $this->shuffle = $shuffle;
        }

        /**
         * @brief enables the exercise
         * @author - Olivier Brouckaert
         */
        public function enable()
        {
            $this->active = 1;
        }

        /**
         * @brief disables the exercise
         * @author - Olivier Brouckaert
         */
        public function disable()
        {
            $this->active = 0;
        }

        /**
         * make exercise public
         */
        public function makepublic()
        {
            $this->public = 1;
        }

        /**
         * make exercise limited
         */
        public function makelimited()
        {
            $this->public = 0;
        }

        public function getOption($option)
        {
            if (isset($this->options[$option])) {
                return $this->options[$option];
            } else {
                return null;
            }
        }
        public function setOption($option, $value)
        {
            if ($value) {
                $this->options[$option] = $value;
            } else {
                unset($this->options[$option]);
            }
        }

        public function setisExam($is_exam)
        {
            $this->is_exam = $is_exam;
        }

        /**
         * @brief updates the exercise in the database
         *
         * @author - Olivier Brouckaert
         */
        public function save()
        {
            global $course_id;

            $id = $this->id;
            $exercise = $this->exercise;
            if (!is_null($this->description)) {
                $description = purify($this->description);
            } else {
                $description = $this->description;
            }
            $general_feedback = purify($this->general_feedback);
            $type = $this->type;
            $range = $this->range;
            $startDate = $this->startDate;
            $endDate = $this->endDate;
            $tempSave = $this->tempSave;
            $timeConstraint = $this->timeConstraint;
            $attemptsAllowed = $this->attemptsAllowed;
            $random = $this->random;
            $shuffle = $this->shuffle;
            $active = $this->active;
            $public = $this->public;
            $results = $this->results;
            $score = $this->score;
            $ip_lock = $this->ip_lock;
            $password_lock = $this->password_lock;
            $assign_to_specific = $this->assign_to_specific;
            $calc_grade_method = $this->calc_grade_method;
            $options = $this->options? json_encode($this->options): '';
            $is_exam = $this->is_exam;
            // exercise already exists
            if ($id) {
                $q = Database::get()->query("UPDATE `exercise`
                    SET title = ?s, description = ?s, type = ?d, `range` = ?d,
                        start_date = ?t, end_date = ?t, temp_save = ?d, time_constraint = ?d,
                        attempts_allowed = ?d, random = ?d, shuffle = ?d, active = ?d, public = ?d,
                        results = ?d, score = ?d, ip_lock = ?s, password_lock = ?s,
                        assign_to_specific = ?d, continue_time_limit = ?d, calc_grade_method = ?d,
                        general_feedback = ?s, options = ?s, is_exam = ?d
                    WHERE course_id = ?d AND id = ?d",
                    $exercise, $description, $type, $range,
                    $startDate, $endDate, $tempSave, $timeConstraint,
                    $attemptsAllowed, $random, $shuffle, $active, $public,
                    $results, $score, $ip_lock, $password_lock,
                    $assign_to_specific, $this->continueTimeLimit,
                    $calc_grade_method, $general_feedback, $options, $is_exam,
                    $course_id, $id)->affectedRows;
                    Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY,
                        array('id' => $id,
                              'title' => $exercise,
                              'description' => $description));
            } else { // creates a new exercise
                $this->id = Database::get()->query("INSERT INTO `exercise`
                    (course_id, title, description, type, `range`, start_date, end_date,
                     temp_save, time_constraint, attempts_allowed,
                     random, shuffle, active, results, score, ip_lock, password_lock,
                     assign_to_specific, continue_time_limit, calc_grade_method, general_feedback, options, is_exam)
                    VALUES (?d, ?s, ?s, ?d, ?d, ?t, ?t, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s, ?s, ?d, ?d, ?d, ?s, ?s, ?d)",
                    $course_id, $exercise, $description, $type, $range, $startDate, $endDate,
                    $tempSave, $timeConstraint, $attemptsAllowed,
                    $random, $shuffle, $active, $results, $score, $ip_lock, $password_lock,
                    $assign_to_specific, $this->continueTimeLimit, $calc_grade_method,
                    $general_feedback, $options, $is_exam)->lastInsertID;

                Log::record($course_id, MODULE_ID_EXERCISE, LOG_INSERT, array('id' => $this->id,
                                                                              'title' => $exercise,
                                                                              'description' => $description));
            }

            // updates question list
            Database::get()->query("DELETE FROM exercise_with_questions WHERE exercise_id = ?d", $this->id);
            foreach ($this->questionList as $position => $questionId) {
                if (is_array($questionId)) {
                    Database::get()->query('INSERT INTO exercise_with_questions
                                (exercise_id, question_id, q_position, random_criteria) VALUES (?d, NULL, ?d, ?s)',
                        $this->id, $position, serialize($questionId));
                } else {
                    Database::get()->query('INSERT INTO exercise_with_questions
                              (exercise_id, question_id, q_position) VALUES (?d, ?d, ?d)',
                        $this->id, $questionId, $position);
                }
            }

        }


        /**
         * @brief adds a question into the question list
         * @param - integer $questionId - question ID
         * @return - boolean - true if the question has been added, otherwise false
         * @author - Olivier Brouckaert
         */
        public function addToList($questionId)
        {
            // checks if the question ID is not in the list
            if (!$this->isInList($questionId)) {
                // selects the max position
                if (!$this->selectNbrQuestions()) {
                    $pos = 1;
                } else {
                    $pos = max(array_keys($this->questionList)) + 1;
                }
                $this->questionList[$pos] = $questionId;

                return true;
            }
            return false;
        }

        /**
         * @brief removes a question from the question list
         * @param - integer $questionId - question ID
         * @return - boolean - true if the question has been removed, otherwise false
         * @author - Olivier Brouckaert
         */
        public function removeFromList($questionId)
        {
            // searches the position of the question ID in the list
            $pos = array_search($questionId, $this->questionList);

            // question not found
            if ($pos === false) {
                return false;
            } else {
                // deletes the position from the array containing the wanted question ID
                unset($this->questionList[$pos]);

                return true;
            }
        }

        /**
         * @brief  deletes the exercise from the database
         * Notice: leaves the question in the database
         *
         * @author - Olivier Brouckaert
         */
        public function delete()
        {
            global $course_id;

            $id = $this->id;
            Database::get()->query("DELETE FROM `exercise_with_questions` WHERE exercise_id = ?d", $id);
            $title = Database::get()->querySingle("SELECT title FROM `exercise`
                                                WHERE course_id = ?d AND id = ?d", $course_id, $id);
            Database::get()->query("DELETE FROM `exercise_to_specific` WHERE exercise_id = ?d", $id);
            $deleted_rows = Database::get()->query("DELETE FROM `exercise` WHERE course_id = ?d AND id = ?d", $course_id, $id)->affectedRows;
            if ($deleted_rows > 0) {
                Log::record($course_id, MODULE_ID_EXERCISE, LOG_DELETE, array('title' => $title));
            }
        }

        /**
         * @brief keeps record of user answers
         */
        public function record_answers($choice, $exerciseResult, $record_type = 'insert')
        {

            $action = $record_type . '_answer_records';

            // if the user has answered at least one question
            if (is_array($choice)) {
                // if all questions on the same page
                if ($this->selectType() == SINGLE_PAGE_TYPE) {
                    // $exerciseResult receives the content of the form.
                    // Each choice of the student is stored into the array $choice
                    $exerciseResult = $choice;
                    $q_position = 1;
                    foreach ($exerciseResult as $key => $value) {
                        $this->$action($key, $value, 1, $q_position);
                        $q_position++;
                    }
                    // else if one question per page
                } else {
                    // gets the question ID from $choice. It is the key of the array
                    list($key) = array_keys($choice);
                    // if the user didn't already answer this question
                    if (!isset($exerciseResult[$key]) or $exerciseResult[$key] != $choice[$key]) {
                        // stores the user answer into the array
                        $value = $exerciseResult[$key] = $choice[$key];
                        $this->$action($key, $value, 1, $this->getAnswerPosition($key));
                    }
                }
            }

            return $exerciseResult;
        }

        /**
         * @brief Get the position of the current question for question-per-page exercise
         *
         * @param integer $question_id
         */
        private function getAnswerPosition($question_id)
        {
            $attempt_value = $_POST['attempt_value'];
            return 1 + array_search($question_id,
                    array_values($_SESSION['questionList'][$this->id][$attempt_value]));
        }

        /**
         * @brief fetch user answers from DB
         * @param $eurid
         * @return array
         */
        public function get_attempt_results_array($eurid)
        {
            $exerciseResult = array();
            $results = Database::get()->queryArray("SELECT * FROM exercise_answer_record WHERE eurid = ?d AND is_answered <> 0 ORDER BY q_position", $eurid);
            foreach ($results as $row) {
                $objQuestionTmp = new Question();
                // reads question information
                $objQuestionTmp->read($row->question_id);
                $question_type = $objQuestionTmp->selectType();
                if ($question_type == FREE_TEXT) {
                    $exerciseResult[$row->question_id] = $row->answer;
                } elseif ($question_type == MATCHING) {
                    $exerciseResult[$row->question_id][$row->answer] = $row->answer_id;
                } elseif ($question_type == FILL_IN_BLANKS || $question_type == FILL_IN_BLANKS_TOLERANT || $question_type == FILL_IN_FROM_PREDEFINED_ANSWERS
                            || $question_type == DRAG_AND_DROP_TEXT || $question_type == DRAG_AND_DROP_MARKERS || $question_type == ORDERING) {
                    $exerciseResult[$row->question_id][$row->answer_id] = $row->answer;
                } elseif ($question_type == MULTIPLE_ANSWER) {
                    $exerciseResult[$row->question_id][$row->answer_id] = 1;
                } else {
                    $exerciseResult[$row->question_id] = $row->answer_id;
                }
            }
            return $exerciseResult;
        }

        /**
         * Save User Unanswered Questions either as unanswered (default behaviour)
         * or as answered by passing parameter 1 to the function
         * (Used for sequential exercises on time expiration
         * and when student wants to temporary save his answers)
         */
        public function save_unanswered($as_answered = 0)
        {
            $id = $this->id;
            $attempt_value = $_POST['attempt_value'];
            $eurid = $_SESSION['exerciseUserRecordID'][$id][$attempt_value];
            $question_ids = Database::get()->queryArray('SELECT DISTINCT question_id
                FROM exercise_answer_record WHERE eurid = ?d', $eurid);
            if (count($question_ids) > 0) {
                foreach ($question_ids as $row) {
                    $answered_question_ids[] = $row->question_id;
                }
            } else {
                $answered_question_ids = array();
            }
            $questionList = $_SESSION['questionList'][$id][$attempt_value];
            $q_position = 1;
            foreach ($questionList as $question_id) {
                if (!in_array($question_id, $answered_question_ids)) {
                    $objQuestionTmp = new Question();
                    $objQuestionTmp->read($question_id);
                    $question_type = $objQuestionTmp->selectType();
                    if ($question_type == MATCHING) {
                        // construction of the Answer object
                        $objAnswerTmp = new Answer($question_id);
                        $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                            // must get answer id ONLY where correct value exists
                            $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                            if ($answerCorrect) {
                                $value[$answerId] = 0;
                            }
                        }
                        unset($objAnswerTmp);
                    } elseif ($question_type == FILL_IN_BLANKS || $question_type == FILL_IN_BLANKS_TOLERANT || $question_type == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                        // construction of the Answer object
                        $objAnswerTmp = new Answer($question_id);
                        $answer = $objAnswerTmp->getTitle(1);
                        // construction of the Answer object
                        if ($question_type == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                            $value = [];
                            foreach (unserialize($answer)[1] as $answer_key => $correct_answer) {
                                $value[$answer_key] = 0; // mark all blanks as unanswered
                            }
                        } else {
                            list($answer, $answerWeighting) = explode('::', $answer);
                            $answerWeighting = explode(',', $answerWeighting);
                            $nbrAnswers = count($answerWeighting);
                            for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                                $value[$answerId] = '';
                            }
                        }
                    } elseif ($question_type == DRAG_AND_DROP_TEXT || $question_type == DRAG_AND_DROP_MARKERS) {
                        $objAnswer = new Answer($question_id);
                        $totalPredefinedAnswers = $objAnswer->get_total_correct_drag_and_drop_predefined_answers();
                        for ($i = 1; $i <= $totalPredefinedAnswers; $i++) {
                            $value[$i] = '';
                        }
                        unset($objAnswer);
                    } elseif ($question_type == CALCULATED) {
                        $objAnswer = new Answer($question_id);
                        $totalPredefinedAnswers = $objAnswer->get_total_calculated_predefined_answers();
                        if ($totalPredefinedAnswers > 1) { // multiple predefined answers
                            for ($answerId = 1; $answerId <= $totalPredefinedAnswers; $answerId++) {
                                $value[$answerId] = 0;
                            }
                        } elseif ($totalPredefinedAnswers == 1) { // unique predefined answer as text
                            $value = '';
                        }
                        unset($objAnswer);
                    } elseif ($question_type == ORDERING) {
                        $objAnswer = new Answer($question_id);
                        $totalPredefinedAnswers = $objAnswer->get_total_correct_ordering_predefined_answers();
                        for ($i = 1; $i <= $totalPredefinedAnswers; $i++) {
                            $value[$i] = '';
                        }
                        unset($objAnswer);
                    } elseif ($question_type == FREE_TEXT) {
                        $value = '';
                    } else {
                        $value = 0;
                    }
                    $this->insert_answer_records($question_id, $value, $as_answered, $q_position);
                    unset($value);
                }
                $q_position++;
            }
        }

        /**
         * Insert user answers
         */
        private function insert_answer_records($key, $value, $as_answered, $q_position)
        {
            $objQuestionTmp = new Question();
            $objQuestionTmp->read($key);
            $question_type = $objQuestionTmp->selectType();
            $id = $this->id;
            $attempt_value = $_POST['attempt_value'];
            $eurid = $_SESSION['exerciseUserRecordID'][$id][$attempt_value];
            Database::get()->query("DELETE FROM exercise_answer_record
                            WHERE eurid = ?d AND question_id = ?d", $eurid, $key);
            if ($question_type == FREE_TEXT) {
                $extra_value = '';
                if (isset($_POST['choice_recording'][$key]) && !empty($_POST['choice_recording'][$key])) {
                    $extra_value = '::' . $_POST['choice_recording'][$key];
                }
                $value = $value . $extra_value;
                Database::get()->query("INSERT INTO exercise_answer_record
                   (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                   VALUES (?d, ?d, ?s, 0, NULL, ?d, ?d)",
                    $eurid, $key, $value, $as_answered, $q_position);
            } elseif ($question_type == FILL_IN_BLANKS || $question_type == FILL_IN_BLANKS_TOLERANT) {
                $objAnswersTmp = new Answer($key);
                $answer_field = $objAnswersTmp->getTitle(1);
                list($answer, $answerWeighting) = Question::blanksSplitAnswer($answer_field);
                // split weightings that are joined with a comma
                $rightAnswerWeighting = explode(',', $answerWeighting);
                $blanks = Question::getBlanks($answer);
                foreach ($value as $row_key => $row_choice) {
                    // if user's choice is right assign rightAnswerWeight else 0
                    // Some more coding should be done if blank can have multiple answers
                    $canonical_choice = canonicalize_whitespace($objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ?
                        remove_accents($row_choice) : $row_choice);
                    $canonical_match = $objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ?
                        remove_accents($blanks[$row_key - 1]) : $blanks[$row_key - 1];
                    $right_answers = array_map('canonicalize_whitespace', preg_split('/\s*\|\s*/', $canonical_match));
                    $weight = in_array($canonical_choice, $right_answers) ? $rightAnswerWeighting[$row_key - 1] : 0;
                    Database::get()->query("INSERT INTO exercise_answer_record
                        (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                        VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                        $eurid, $key, $row_choice, $row_key, $weight, $as_answered, $q_position);
                }
            } elseif ($question_type == FILL_IN_FROM_PREDEFINED_ANSWERS) {
                $objAnswersTmp = new Answer($key);
                $answer = $objAnswersTmp->getTitle(1);
                $answer_string = unserialize($answer);
                $right_string = $answer_string[1]; // right answers
                $weight_string = $answer_string[2]; // weight
                foreach ($value as $choice_key => $choice) {
                    if (isset($right_string[$choice_key-1]) && $choice == $right_string[$choice_key-1]) {
                        $weight = $weight_string[$choice_key-1];
                    } else {
                        $weight = 0;
                    }
                    Database::get()->query("INSERT INTO exercise_answer_record
                        (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                        VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                        $eurid, $key, $choice, $choice_key, $weight, $as_answered, $q_position);
                }
            } elseif ($question_type == MULTIPLE_ANSWER) {
                if ($value == 0) {
                    $row_key = 0;
                    $answer_weight = 0;
                    Database::get()->query("INSERT INTO exercise_answer_record
                        (eurid, question_id, answer_id, weight, is_answered, q_position)
                        VALUES (?d, ?d, ?d, ?f, ?d, ?d)",
                        $eurid, $key, $row_key, $answer_weight, $as_answered, $q_position);
                } else {
                    $objAnswersTmp = new Answer($key);
                    foreach ($value as $row_key => $row_choice) {
                        $answer_weight = $objAnswersTmp->getWeighting($row_key);
                        Database::get()->query("INSERT INTO exercise_answer_record
                            (eurid, question_id, answer_id, weight, is_answered, q_position)
                            VALUES (?d, ?d, ?d, ?f, ?d, ?d)",
                            $eurid, $key, $row_key, $answer_weight, $as_answered, $q_position);
                        unset($answer_weight);
                    }
                    unset($objAnswersTmp);
                }
            } elseif ($question_type == MATCHING) {
                $objAnswersTmp = new Answer($key);
                foreach ($value as $row_key => $row_choice) {
                    // In matching questions isCorrect() returns position of left column answers while $row_key returns right column position
                    $correct_match = $objAnswersTmp->isCorrect($row_key);
                    if ($correct_match == $row_choice) {
                        $answer_weight = $objAnswersTmp->getWeighting($row_key);
                    } else {
                        $answer_weight = 0;
                    }

                    Database::get()->query("INSERT INTO exercise_answer_record
                        (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                        VALUES (?d, ?d, ?d, ?d, ?f, ?d, ?d)",
                        $eurid, $key, $row_key, $row_choice, $answer_weight, $as_answered, $q_position);
                    unset($answer_weight);
                }
            } elseif ($question_type == DRAG_AND_DROP_TEXT || $question_type == DRAG_AND_DROP_MARKERS) {

                $objAnswersTmp = new Answer($key);
                $questionWords = $objAnswersTmp->get_drag_and_drop_answer_text();
                $questionGrades = $objAnswersTmp->get_drag_and_drop_answer_grade();
                if (isset($_POST['choice']) && !empty($_POST['choice'][$key])) {
                    $userAnswersAsJSON = $_POST['choice'][$key];
                }

                // Change indexes to start from 0.
                if ($question_type == DRAG_AND_DROP_MARKERS) {
                    $arrTmp = [];
                    foreach ($questionGrades as $index => $value) {
                        if ($index > 0) {
                            $index = $index - 1;
                            $arrTmp[$index] = $value;
                        }
                    }
                    $questionGrades = $arrTmp;
                }

                if (!isset($userAnswersAsJSON)) { // User has not filled in the blanks of the question.
                    $blank = 1;
                    foreach ($questionWords as $word) {
                        $weight = 0;
                        $word = '';
                        //$as_answered = 0;
                        Database::get()->query("INSERT INTO exercise_answer_record
                            (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                            VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                            $eurid, $key, $word, $blank, $weight, $as_answered, $q_position);
                        $blank++;
                    }

                } else { // User has filled in at least one blank of the question.
                    $data = json_decode($userAnswersAsJSON, true);
                    $resultAns = [];
                    foreach ($data as $item) {
                        $blank = (int)$item['dataAnswer'];
                        $value = isset($item['dataWord']) && $item['dataWord'] !== null ? $item['dataWord'] : 0;
                        $resultAns[$blank] = $value;
                    }
                    foreach ($resultAns as $blank => $value) {
                        if ($value == 0) {// User has no filled in to the specific blank.
                            $weight = 0;
                            $value = '';
                        } else {
                            if ($question_type == DRAG_AND_DROP_TEXT || $question_type == DRAG_AND_DROP_MARKERS) {
                                $weight = ($blank > 0 && !empty($value) && $questionWords[$blank-1] == $value) ? $questionGrades[$blank-1] : 0;
                            }
                        }
                        Database::get()->query("INSERT INTO exercise_answer_record
                            (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                            VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                            $eurid, $key, $value, $blank, $weight, $as_answered, $q_position);
                    }
                }

            } elseif ($question_type == CALCULATED) {

                // $key = $question_id
                $objAnswersTmp = new Answer($key);
                $totalAnswers = $objAnswersTmp->get_total_calculated_predefined_answers();

                if (is_array($value)) { // unaswered question from multiple predefined answers
                    for ($i = 1; $i <= count($value); $i++) {
                        Database::get()->query("INSERT INTO exercise_answer_record
                                (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                                VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                                $eurid, $key, '', 0, 0, $as_answered, $q_position);
                    }
                } elseif (is_string($value) && empty($value) && $value != 0) { // unaswered question from text predefined answer
                    Database::get()->query("INSERT INTO exercise_answer_record
                                (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                                VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                                $eurid, $key, '', 1, 0, $as_answered, $q_position);
                } elseif (is_string($value) && !empty($value)) { // answered question
                    $arrAnswer = explode(',', $value);
                    if (count($arrAnswer) == 2) { // multiple predefined answers
                        $user_answer = $arrAnswer[0];
                        $answer_id = $arrAnswer[1];
                        $user_got_grade = $objAnswersTmp->get_user_answer_grade($key, $user_answer);
                    } else { // unique answer as text
                        $user_answer = $value;
                        if ($value == 0) {
                            $user_answer = 0;
                        }
                        $answer_id = $_POST['answer_id_choice'][$key] ?? 0;
                        $user_got_grade = $objAnswersTmp->get_user_answer_grade($key, $user_answer);
                    }

                    Database::get()->query("INSERT INTO exercise_answer_record
                                (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                                VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                                $eurid, $key, $user_answer, $answer_id, $user_got_grade, $as_answered, $q_position);
                } elseif (is_numeric($value) && $value == 0) { // unaswered question from multiple predefined answers
                    Database::get()->query("INSERT INTO exercise_answer_record
                                (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                                VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                                $eurid, $key, '', 0, 0, $as_answered, $q_position);
                }
                
                unset($objAnswersTmp);

            } elseif ($question_type == ORDERING) {

                $objAnswersTmp = new Answer($key);
                $ordering_answer = $objAnswersTmp->get_ordering_answers();
                $ordering_answer_grade = $objAnswersTmp->get_ordering_answer_grade();

                if (!is_string($value)) { // unaswered question
                    for ($i = 1; $i <= count($ordering_answer); $i++) {
                        Database::get()->query("INSERT INTO exercise_answer_record
                                    (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                                    VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                                    $eurid, $key, '', $i, 0, $as_answered, $q_position);
                    }
                } else { // $value contains json value as string
                    $userAnswers = json_decode($value, true);
                    if (count($userAnswers) > 0) {
                        for ($i = 1; $i <= count($userAnswers)-1; $i++) { // Index 0 is null.
                            $position = $i;
                            $weight = 0;
                            $val = $userAnswers[$i];
                            if ($userAnswers[$i] == $ordering_answer[$i]) {
                                $weight = $ordering_answer_grade[$i];
                            }
                            Database::get()->query("INSERT INTO exercise_answer_record
                                (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                                VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                                $eurid, $key, $val, $position, $weight, $as_answered, $q_position);
                        }
                    }
                }

                unset($objAnswersTmp);

            } else {
                if ($value) {
                    $objAnswersTmp = new Answer($key);
                    $answer_weight = $objAnswersTmp->getWeighting($value);
                } else {
                    $answer_weight = 0;
                }
                Database::get()->query("INSERT INTO exercise_answer_record
                    (eurid, question_id, answer_id, weight, is_answered, q_position)
                    VALUES (?d, ?d, ?d, ?f, ?d, ?d)",
                    $eurid, $key, $value, $answer_weight, $as_answered, $q_position);
            }
            unset($objQuestionTmp);
        }

        /**
         * Update user answers
         */
        private function update_answer_records($key, $value, $dummy1, $dummy2)
        {
            // construction of the Question object
            $objQuestionTmp = new Question();
            // reads question information
            $objQuestionTmp->read($key);
            $question_type = $objQuestionTmp->selectType();
            $id = $this->id;
            $attempt_value = $_POST['attempt_value'];
            $eurid = $_SESSION['exerciseUserRecordID'][$id][$attempt_value];
            if ($question_type == FREE_TEXT) {
                if (!empty($value)) {
                    Database::get()->query("UPDATE exercise_answer_record SET answer = ?s, answer_id = 1, weight = NULL,
                                          is_answered = 1 WHERE eurid = ?d AND question_id = ?d", $value, $eurid, $key);
                } else {
                    Database::get()->query("UPDATE exercise_answer_record SET answer = ?s,
                                          answer_id = 0, weight = 0, is_answered = 1 WHERE eurid = ?d AND question_id = ?d", $value, $eurid, $key);
                }
            } elseif ($question_type == FILL_IN_BLANKS || $question_type == FILL_IN_BLANKS_TOLERANT) {
                $objAnswersTmp = new Answer($key);
                $answer_field = $objAnswersTmp->getTitle(1);
                list($answer, $answerWeighting) = Question::blanksSplitAnswer($answer_field);
                // splits weightings that are joined with a comma
                $rightAnswerWeighting = explode(',', $answerWeighting);
                $blanks = Question::getBlanks($answer);
                foreach ($value as $row_key => $row_choice) {
                    // if user's choice is right assign rightAnswerWeight else 0
                    $canonical_choice = canonicalize_whitespace($objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ? remove_accents($row_choice) : $row_choice);
                    $canonical_match = $objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ? remove_accents($blanks[$row_key - 1]) : $blanks[$row_key - 1];
                    $right_answers = array_map('canonicalize_whitespace', preg_split('/\s*\|\s*/', $canonical_match));
                    $weight = in_array($canonical_choice, $right_answers) ? $rightAnswerWeighting[$row_key - 1] : 0;
                    Database::get()->query("UPDATE exercise_answer_record SET answer = ?s, weight = ?f, is_answered = 1
                                              WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d", $row_choice, $weight, $eurid, $key, $row_key);
                }
            } elseif ($question_type == MULTIPLE_ANSWER) {
                if ($value == 0) {
                    $row_key = 0;
                    $answer_weight = 0;
                    Database::get()->query("UPDATE exercise_answer_record SET is_answered = 1 WHERE eurid = ?d AND question_id = ?d", $eurid, $key);
                } else {
                    $objAnswersTmp = new Answer($key);
                    Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid = ?d AND question_id = ?d", $eurid, $key);
                    foreach ($value as $row_key => $row_choice) {
                        $answer_weight = $objAnswersTmp->getWeighting($row_key);
                        Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer_id, weight, is_answered)
                            VALUES (?d, ?d, ?d, ?f, 1)", $eurid, $key, $row_key, $answer_weight);
                        unset($answer_weight);
                    }
                    unset($objAnswersTmp);
                }
            } elseif ($question_type == MATCHING) {
                $objAnswersTmp = new Answer($key);
                foreach ($value as $row_key => $row_choice) {
                    // In matching questions isCorrect() returns position of left column answers while $row_key returns right column position
                    $correct_match = $objAnswersTmp->isCorrect($row_key);
                    if ($correct_match == $row_choice) {
                        $answer_weight = $objAnswersTmp->getWeighting($row_key);
                    } else {
                        $answer_weight = 0;
                    }
                    Database::get()->query("UPDATE exercise_answer_record SET answer_id = ?d, weight = ?f , is_answered = 1
                        WHERE eurid = ?d AND question_id = ?d AND answer = ?d", $row_choice, $answer_weight, $eurid, $key, $row_key);
                    unset($answer_weight);
                }
            } else {
                if ($value != 0) {
                    $objAnswersTmp = new Answer($key);
                    $answer_weight = $objAnswersTmp->getWeighting($value);
                } else {
                    $answer_weight = 0;
                }
                Database::get()->query("UPDATE exercise_answer_record SET answer_id = ?d, weight = ?f , is_answered = 1
                    WHERE eurid = ?d AND question_id = ?d", $value, $answer_weight, $eurid, $key);
            }
            unset($objQuestionTmp);
        }

        /**
         * @brief Purge exercise user results
         */
        public function purge()
        {
            global $course_id, $webDir, $course_code;
            $id = $this->id;

            // Remove oral answers from document table and courses folder
            $userRecords = Database::get()->queryArray("SELECT exercise_user_record.eurid,exercise_user_record.`uid`,exercise_answer_record.question_id 
                                                        FROM exercise_user_record
                                                        JOIN exercise_answer_record ON exercise_user_record.eurid = exercise_answer_record.eurid
                                                        WHERE exercise_user_record.eid = ?d", $id);
                                                        
            foreach ($userRecords as $rec) {
                $file = Database::get()->querySingle("SELECT id,`path` FROM document WHERE course_id = ?d
                                                      AND subsystem = ?d AND subsystem_id = ?d
                                                      AND lock_user_id = ?d", $course_id, ORAL_QUESTION, $rec->question_id, $rec->uid);
                if ($file && file_exists("$webDir/courses/$course_code/image" . $file->path)) {
                    unlink("$webDir/courses/$course_code/image" . $file->path);
                    Database::get()->query("DELETE FROM document WHERE id = ?d", $file->id);
                } 
            }
            

            Database::get()->query("DELETE d FROM exercise_answer_record d, exercise_user_record s
                              WHERE d.eurid = s.eurid AND s.eid = ?d", $id);
            Database::get()->query("DELETE FROM exercise_user_record WHERE eid = ?d", $id);
            $this->setCalcGradeMethod();

            $exercise_title = Database::get()->querySingle("SELECT title FROM exercise WHERE id = ?d", $id)->title;

            Log::record($course_id, MODULE_ID_EXERCISE, LOG_DELETE, array('title' => $exercise_title,
                                                                                              'purge_results' => 1));
        }

        /**
         * @brief Purge exercise user attempt
         * @param $id
         * @param $eurid
         * @return void
         */
        public function purgeAttempt($id, $eurid)
        {
            global $course_id;

            $exercise_title = Database::get()->querySingle("SELECT title FROM exercise WHERE id = ?d", $id)->title;
            $eurid_uid = Database::get()->querySingle("SELECT uid FROM exercise_user_record WHERE eid = ?d AND eurid = ?d", $id, $eurid)->uid;

            Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid = ?d", $eurid);
            Database::get()->query("DELETE FROM exercise_user_record WHERE eid = ?d AND eurid = ?d", $id, $eurid);

            Log::record($course_id, MODULE_ID_EXERCISE, LOG_DELETE, array('title' => $exercise_title,
                                                                                              'del_eurid_uid' => $eurid_uid));
        }

        /**
         * @brief modify attempt status
         * @param $eurid
         */
        public function modifyAttempt($eurid, $status)
        {
            global $course_id;

            Database::get()->query("UPDATE exercise_user_record SET attempt_status = ?d WHERE eurid = ?d", $status, $eurid);

            $q = Database::get()->querySingle("SELECT title, uid, eid FROM exercise_user_record, exercise WHERE eurid = ?d AND exercise_user_record.eid = exercise.id", $eurid);
            $eurid_uid = $q->uid;
            $exercise_title = $q->title;

            Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, array('title' => $exercise_title,
                                                                                               'mod_eurid_uid' => $eurid_uid,
                                                                                               'new_eurid_status' => $status));
        }

        /**
         * Clone an Exercise
         */
        public function duplicate()
        {
            global $langCopy2, $course_id, $course_code;

            $clone_course_id = $_POST['clone_to_course_id'];
            if (!check_editor(null, $clone_course_id)) {
                forbidden();
            }
            $id = $this->id;
            $exercise = $this->exercise . (($clone_course_id == $course_id) ? " ($langCopy2)" : '');
            $description = $this->description;
            $type = $this->type;
            $startDate = $this->startDate;
            $endDate = $this->endDate;
            $tempSave = $this->tempSave;
            $timeConstraint = $this->timeConstraint;
            $attemptsAllowed = $this->attemptsAllowed;
            $random = $this->random;
            $active = $this->active;
            $results = $this->results;
            $score = $this->score;
            $ip_lock = $this->ip_lock;
            $password_lock = $this->password_lock;
            $assign_to_specific = $this->assign_to_specific;
            $range = $this->range;
            $calc_grade_method = 1;
            $is_exam = $this->is_exam;
            $clone_id = Database::get()->query("INSERT INTO `exercise` (course_id, title, description, `type`, `range`, start_date,
                                    end_date, temp_save, time_constraint, attempts_allowed, random, active, results, score, ip_lock, password_lock,
                                    assign_to_specific, calc_grade_method, is_exam)
                                    VALUES (?d, ?s, ?s, ?d, ?d, ?t, ?t, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s, ?s, ?d, ?d, ?d)",
                $clone_course_id, $exercise, $description, $type, $range, $startDate, $endDate, $tempSave,
                $timeConstraint, $attemptsAllowed, $random, $active, $results, $score, $ip_lock, $password_lock, $assign_to_specific, $calc_grade_method, $is_exam)->lastInsertID;
            if ($assign_to_specific) {
                Database::get()->query("INSERT INTO `exercise_to_specific` (user_id, group_id, exercise_id)
                                        SELECT user_id, group_id, ?d FROM `exercise_to_specific`
                                        WHERE exercise_id = ?d", $clone_id, $id)->lastInsertID;
            }
            if ($clone_course_id != $course_id) {
                // copy questions and answers to new course question pool
                $old_path = "courses/$course_code/image/quiz-";
                $new_path = 'courses/' . course_id_to_code($clone_course_id) . '/image/quiz-';
                Database::get()->queryFunc("SELECT question_id AS id, q_position, random_criteria FROM exercise_with_questions
                        WHERE exercise_id = ?d",
                    function ($question) use ($clone_id, $clone_course_id, $old_path, $new_path) {
                        if (is_null($question->random_criteria)) {
                            $question_clone_id = Database::get()->query("INSERT INTO exercise_question
                                (course_id, question, description, weight, type, difficulty, category, options)
                                SELECT ?d, question, description, weight, type, difficulty, 0, options
                                    FROM `exercise_question` WHERE id = ?d", $clone_course_id, $question->id)->lastInsertID;
                            Database::get()->query("INSERT INTO exercise_with_questions
                                (question_id, exercise_id, q_position, random_criteria) VALUES (?d, ?d, ?d, NULL)", $question_clone_id, $clone_id, $question->q_position);
                            Database::get()->query("INSERT INTO exercise_answer
                                (question_id, answer, correct, comment, weight, r_position)
                                SELECT ?d, answer, correct, comment, weight, r_position FROM exercise_answer
                                    WHERE question_id = ?d",
                                $question_clone_id, $question->id);
                        } else {
                            Database::get()->query("INSERT INTO exercise_with_questions
                                (question_id, exercise_id, q_position, random_criteria) VALUES (NULL, ?d, ?d, ?s)", $clone_id, $question->q_position, $question->random_criteria);
                        }
                        $old_image_path = $old_path . $question->id;
                        if (file_exists($old_image_path)) {
                            copy($old_image_path, $new_path . $question_clone_id);
                        }
                    },
                    $id);
            } else {
                // add question to new exercise
                Database::get()->query("INSERT INTO `exercise_with_questions`
                        (question_id, exercise_id, q_position, random_criteria)
                        SELECT question_id, ?d, q_position, random_criteria FROM `exercise_with_questions`
                            WHERE exercise_id = ?d", $clone_id, $id);
            }
        }

        /**
         * @brief run UPDATE queries for each item of the output
         * @param type $correction_output
         */
        public function distribution($correction_output)
        {

            $id = $this->id;
            $stopped = 0;
            $courses = json_decode($correction_output);
            $TotalExercises = Database::get()->queryArray("SELECT eurid
                    FROM exercise_user_record WHERE eid = ?d AND attempt_status = " . ATTEMPT_PENDING . "", $id);

            foreach ($courses as $row) {
                $teacherId = $row->teacher;
                $disnumber = $row->grade;
                for ($i = 0; $i < $disnumber; $i++) {
                    $eurid = $TotalExercises[$stopped]->eurid;
                    Database::get()->query("UPDATE exercise_user_record SET assigned_to = ?d WHERE eurid = ?d", $teacherId, $eurid);
                    //gia na min xrisimopooioume to i pou ksanaksekinaei apo to 0
                    $stopped++;
                }
            }
        }

        /**
         * @brief run UPDATE queries for each eurid
         */
        public function cancelDistribution()
        {

            $TotalExercises = Database::get()->queryArray("SELECT eurid
                        FROM exercise_user_record WHERE  eid = ?d AND attempt_status = " . ATTEMPT_PENDING . "", $this->id);
            foreach ($TotalExercises as $row) {
                Database::get()->query("UPDATE exercise_user_record SET assigned_to = NULL WHERE eurid = ?d", $row->eurid);
            }
        }

        /**
         * @brief canonicalize user grade (if defined)
         * @param $user_score
         * @param $total_score
         * @return float
         */
        public function canonicalize_exercise_score($user_score, $total_score)
        {
            if ($this->range > 0 && $total_score > 0) {
                $score = round(($user_score / $total_score) * $this->range, 2);
            } else {
                $score = round($user_score, 2);
            }
            return $score;
        }

        /**
         * @brief calculate total exercise score per user attempt
         * @param $eurid
         * @return float|int
         */
        public function calculate_total_score($eurid)
        {

            $total_score1 = $total_score2 = 0;
            $temp_total_score1 = [];

            // disallow negative score in each question if question has multiple answers
            $score1 = Database::get()->queryArray("SELECT SUM(exercise_answer_record.weight) AS temp_total_score
                                                    FROM exercise_answer_record
                                                    JOIN exercise_question
                                                    ON question_id = id
                                                        AND exercise_question.type = " . MULTIPLE_ANSWER . "
                                                        AND eurid = ?d
                                                    GROUP BY question_id", $eurid);
            if (count($score1) > 0) {
                foreach ($score1 as $data) {
                    if ($data->temp_total_score < 0) {
                        $temp_total_score1[] = 0;
                    } else {
                        $temp_total_score1[] = $data->temp_total_score;
                    }
                }
                $total_score1 = array_sum($temp_total_score1); // total score for questions with multiple answers
            }
            // calculate total score for other question types
            $temp_total_score2 = Database::get()->querySingle("SELECT SUM(temp_sum) AS total_sum FROM
                                               (SELECT SUM(exercise_answer_record.weight) AS temp_sum
                                                FROM exercise_answer_record
                                                JOIN exercise_question
                                                ON question_id = id
                                                    AND exercise_question.type <> " . MULTIPLE_ANSWER . "
                                                    AND eurid = ?d
                                             GROUP BY question_id) AS total_weight", $eurid);

            if ($temp_total_score2) {
                $total_score2 = $temp_total_score2->total_sum;
            }

            $totalScore = $total_score1 + $total_score2;

            if ($totalScore < 0) { // disallow negative total score
                $totalScore = 0.00;
            }

            return $totalScore;
        }
    }
}
