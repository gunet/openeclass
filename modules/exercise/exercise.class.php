<?php
/* ========================================================================
 * Open eClass 3.10
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2020  Greek Universities Network - GUnet
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
     * This class allows to instantiate an object of type Exercise
     *
     * @author - Olivier Brouckaert
     */
    class Exercise {

        private $id;
        private $exercise;
        private $description;
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
        private $questionList;  // array with the list of this exercise's questions

        /**
         * constructor of the class
         *
         * @author - Olivier Brouckaert
         */
        public function __construct() {
            $this->id = 0;
            $this->exercise = '';
            $this->description = '';
            $this->type = 1;
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
        }

        /**
         * read exercise information from database
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - exercise ID
         * @return - boolean - true if exercise exists, otherwise false
         */
        function read($id) {
            global $course_id;

            $object = Database::get()->querySingle("SELECT title, description, type, `range`, start_date, end_date, temp_save, time_constraint,
            attempts_allowed, random, shuffle, active, public, results, score, ip_lock, password_lock, assign_to_specific, continue_time_limit
            FROM `exercise` WHERE course_id = ?d AND id = ?d", $course_id, $id);

            // if the exercise has been found
            if ($object) {
                $this->id = $id;
                $this->exercise = $object->title;
                $this->description = $object->description;
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
                $this->continueTimeLimit = $object->continue_time_limit;

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
         * @author - Olivier Brouckaert
         * @return - integer - exercise ID
         */
        function selectId() {
            return $this->id;
        }

        /**
         * returns the exercise title
         *
         * @author - Olivier Brouckaert
         * @return - string - exercise title
         */
        function selectTitle() {
            return $this->exercise;
        }

        /**
         * set title
         *
         * @author Sebastien Piraux <pir@cerdecam.be>
         * @param string $value
         */
        function setTitle($value) {
            $this->exercise = trim($value);
        }

        /**
         * returns the exercise description
         *
         * @author - Olivier Brouckaert
         * @return - string - exercise description
         */
        function selectDescription() {
            return $this->description;
        }

        /**
         * set description
         *
         * @author Sebastien Piraux <pir@cerdecam.be>
         * @param string $value
         */
        function setDescription($value) {
            $this->description = trim($value);
        }

        /**
         *
         * @return the total weighting of an exercise
         */
        function selectTotalWeighting() {
            return $this->totalweight;
        }

        /**
         * returns the exercise type
         *
         * @author - Olivier Brouckaert
         * @return - integer - exercise type
         */
        function selectType() {
            return $this->type;
        }

        function selectRange() {
            return $this->range;
        }

        function selectStartDate() {
            return $this->startDate;
        }

        function selectEndDate() {
            return $this->endDate;
        }

        function selectTempSave() {
            return $this->tempSave;
        }

        function selectTimeConstraint() {
            return $this->timeConstraint;
        }

        function selectAttemptsAllowed() {
            return $this->attemptsAllowed;
        }

        function selectResults() {
            return $this->results;
        }

        function selectScore() {
            return $this->score;
        }

        function selectIPLock() {
            return $this->ip_lock;
        }

        function selectPasswordLock() {
            return $this->password_lock;
        }

        function selectAssignToSpecific() {
            return $this->assign_to_specific;
        }

        function continueTimeLimit() {
            return $this->continueTimeLimit;
        }
        
        function isRandom() {
            return $this->random;
        }
        
        function selectShuffle() {
            return $this->shuffle;
        }

        /**
         * returns the exercise status (1 = enabled ; 0 = disabled)
         *
         * @author - Olivier Brouckaert
         * @return - boolean - true if enabled, otherwise false
         */
        function selectStatus() {
            return $this->active;
        }

        /**
         * returns the array with the question ID list
         *
         * @author - Olivier Brouckaert
         * @return - array - question ID list
         */
        function selectQuestionList() {

            return $this->questionList;
        }


        /**
         * @brief get questions (with dynamic criteria or not)
         * @return - array with question is
         */
        function selectQuestions() {

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
        function selectQuestionListWithDifficulty($number, $difficulty) {

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
        function selectQuestionListWithCategory($number, $category) {

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
        function selectQuestionListWithDifficultyAndCategory($number, $difficulty, $category) {

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
         * @author - Olivier Brouckaert
         * @return - integer - number of questions
         */
        function selectNbrQuestions() {
            return sizeof($this->questionList);
        }

        /**
         * @brief shuffle questions
         * @return array
         */
        function selectShuffleQuestions() {

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
        function hasQuestionListWithRandomCriteria() {
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
         * @author - Olivier Brouckaert
         * @param - integer $questionId - question ID
         * @return - boolean - true if in the list, otherwise false
         */
        function isInList($questionId) {
            return in_array($questionId, $this->questionList);
        }

        /**
         * changes the exercise title
         *
         * @author - Olivier Brouckaert
         * @param - string $title - exercise title
         */
        function updateTitle($title) {
            $this->exercise = $title;
        }

        /**
         * changes the exercise description
         *
         * @author - Olivier Brouckaert
         * @param - string $description - exercise description
         */
        function updateDescription($description) {
            $this->description = $description;
        }

        /**
         * changes the exercise type
         *
         * @author - Olivier Brouckaert
         * @param - integer $type - exercise type
         */
        function updateType($type) {
            $this->type = $type;
        }

        function updateRange($range) {
            $this->range = $range;
        }

        function updateStartDate($startDate) {
            $this->startDate = $startDate;
        }

        function updateEndDate($endDate) {
            $this->endDate = $endDate;
        }

        function updateTempSave($tempSave) {
            $this->tempSave = $tempSave;
        }

        function updateTimeConstraint($timeConstraint) {
            $this->timeConstraint = $timeConstraint;
        }

        function updateAttemptsAllowed($attemptsAllowed) {
            $this->attemptsAllowed = $attemptsAllowed;
        }

        function updateResults($results) {
            $this->results = $results;
        }

        function updateScore($score) {
            $this->score = $score;
        }

        function updateContinueTimeLimit($minutes) {
            $this->continueTimeLimit = intval($minutes);
            if ($this->continueTimeLimit < 0) {
                $this->continueTimeLimit = 0;
            }
        }

        function updateIPLock($ips) {
            $this->ip_lock = (empty($ips)) ? null : $ips;
        }
        function updatePasswordLock($password) {
            $this->password_lock = (empty($password)) ? null : $password;
        }
        function updateAssignToSpecific($assign_to_specific) {
            $this->assign_to_specific = $assign_to_specific;
        }
        function assignTo($assignees) {
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


        function setRandom($random) {
            $this->random = $random;
        }
        
        function setShuffle($shuffle) {
            $this->shuffle = $shuffle;
        }

        /**
         * enables the exercise
         *
         * @author - Olivier Brouckaert
         */
        function enable() {
            $this->active = 1;
        }

        /**
         * disables the exercise
         *
         * @author - Olivier Brouckaert
         */
        function disable() {
            $this->active = 0;
        }

        /**
         * make exercise public
         */
        function makepublic() {
            $this->public = 1;
        }

        /**
         * make exercise limited
         */
        function makelimited() {
            $this->public = 0;
        }

        /**
         * updates the exercise in the data base
         *
         * @author - Olivier Brouckaert
         */
        function save() {
            global $course_id;

            $id = $this->id;
            $exercise = $this->exercise;
            $description = purify($this->description);
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
            // exercise already exists
            if ($id) {
                $affected_rows = Database::get()->query("UPDATE `exercise`
                    SET title = ?s, description = ?s, type = ?d, `range` = ?d,
                        start_date = ?t, end_date = ?t, temp_save = ?d, time_constraint = ?d,
                        attempts_allowed = ?d, random = ?d, shuffle = ?d, active = ?d, public = ?d,
                        results = ?d, score = ?d, ip_lock = ?s, password_lock = ?s,
                        assign_to_specific = ?d, continue_time_limit = ?d
                    WHERE course_id = ?d AND id = ?d",
                    $exercise, $description, $type, $range,
                    $startDate, $endDate, $tempSave, $timeConstraint,
                    $attemptsAllowed, $random, $shuffle, $active, $public,
                    $results, $score, $ip_lock, $password_lock,
                    $assign_to_specific, $this->continueTimeLimit,
                    $course_id, $id)->affectedRows;
                if ($affected_rows > 0) {
                    Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, array('id' => $id,
                        'title' => $exercise,
                        'description' => $description));
                }
            }
            // creates a new exercise
            else {
                $this->id = Database::get()->query("INSERT INTO `exercise`
                    (course_id, title, description, type, `range`, start_date, end_date,
                     temp_save, time_constraint, attempts_allowed,
                     random, shuffle, active, results, score, ip_lock, password_lock,
                     assign_to_specific, continue_time_limit)                    
                    VALUES (?d, ?s, ?s, ?d, ?d, ?t, ?t, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s, ?s, ?d, ?d)",
                        $course_id, $exercise, $description, $type, $range, $startDate, $endDate,
                        $tempSave, $timeConstraint, $attemptsAllowed,
                        $random, $shuffle, $active, $results, $score, $ip_lock, $password_lock,
                        $assign_to_specific, $this->continueTimeLimit)->lastInsertID;

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
         * adds a question into the question list
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - question ID
         * @return - boolean - true if the question has been added, otherwise false
         */
        function addToList($questionId) {
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
         * removes a question from the question list
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - question ID
         * @return - boolean - true if the question has been removed, otherwise false
         */
        function removeFromList($questionId) {
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
         * deletes the exercise from the database
         * Notice : leaves the question in the data base
         *
         * @author - Olivier Brouckaert
         */
        function delete() {
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
        function record_answers($choice, $exerciseResult, $record_type = 'insert') {

            $action = $record_type.'_answer_records';

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
        function getAnswerPosition($question_id) {
            $attempt_value = $_POST['attempt_value'];
            return 1 + array_search($question_id,
                array_values($_SESSION['questionList'][$this->id][$attempt_value]));
        }

        /**
         * keeps record of user answers
         */
        function get_attempt_results_array($eurid) {
            $exerciseResult = array();
            $results = Database::get()->queryArray("SELECT * FROM exercise_answer_record WHERE eurid = ?d AND is_answered <> 0 ORDER BY q_position", $eurid);
            foreach ($results as $row) {
                $objQuestionTmp = new Question();
                // reads question informations
                $objQuestionTmp->read($row->question_id);
                $question_type = $objQuestionTmp->selectType();
                if ($question_type == FREE_TEXT) {
                    $exerciseResult[$row->question_id] = $row->answer;
                } elseif ($question_type == MATCHING){
                    $exerciseResult[$row->question_id][$row->answer] = $row->answer_id;
                } elseif ($question_type == FILL_IN_BLANKS || $question_type == FILL_IN_BLANKS_TOLERANT){
                    $exerciseResult[$row->question_id][$row->answer_id] = $row->answer;
                } elseif ($question_type == MULTIPLE_ANSWER){
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
        function save_unanswered($as_answered = 0) {
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
                    } elseif ($question_type == FILL_IN_BLANKS || $question_type == FILL_IN_BLANKS_TOLERANT) {
                        // construction of the Answer object
                        $objAnswerTmp = new Answer($question_id);
                        $answer = $objAnswerTmp->selectAnswer(1);
                        // construction of the Answer object
                        list($answer, $answerWeighting) = explode('::', $answer);
                        $answerWeighting = explode(',', $answerWeighting);
                        $nbrAnswers = count($answerWeighting);
                        for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                            $value[$answerId] = '';
                        }
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
        private function insert_answer_records($key, $value, $as_answered, $q_position) {
            $objQuestionTmp = new Question();
            $objQuestionTmp->read($key);
            $question_type = $objQuestionTmp->selectType();
            $id = $this->id;
            $attempt_value = $_POST['attempt_value'];
            $eurid = $_SESSION['exerciseUserRecordID'][$id][$attempt_value];
            Database::get()->query("DELETE FROM exercise_answer_record
                            WHERE eurid = ?d AND question_id = ?d", $eurid, $key);
            if ($question_type == FREE_TEXT) {
                Database::get()->query("INSERT INTO exercise_answer_record
                   (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                   VALUES (?d, ?d, ?s, 0, NULL, ?d, ?d)",
                   $eurid, $key, $value, $as_answered, $q_position);
            } elseif ($question_type == FILL_IN_BLANKS || $question_type == FILL_IN_BLANKS_TOLERANT) {
                $objAnswersTmp = new Answer($key);
                $answer_field = $objAnswersTmp->selectAnswer(1);
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
                        remove_accents($blanks[$row_key-1]) : $blanks[$row_key-1];
                    $right_answers = array_map('canonicalize_whitespace', preg_split('/\s*\|\s*/', $canonical_match));
                    $weight = in_array($canonical_choice, $right_answers) ? $rightAnswerWeighting[$row_key-1] : 0;
                    Database::get()->query("INSERT INTO exercise_answer_record
                        (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                        VALUES (?d, ?d, ?s, ?d, ?f, ?d, ?d)",
                        $eurid, $key, $row_choice, $row_key, $weight, $as_answered, $q_position);
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
                        $answer_weight = $objAnswersTmp->selectWeighting($row_key);
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
                        $answer_weight = $objAnswersTmp->selectWeighting($row_key);
                    } else {
                        $answer_weight = 0;
                    }

                    Database::get()->query("INSERT INTO exercise_answer_record
                        (eurid, question_id, answer, answer_id, weight, is_answered, q_position)
                        VALUES (?d, ?d, ?d, ?d, ?f, ?d, ?d)",
                        $eurid, $key, $row_key, $row_choice, $answer_weight, $as_answered, $q_position);
                    unset($answer_weight);
                }
            } else {
                if ($value) {
                    $objAnswersTmp = new Answer($key);
                    $answer_weight = $objAnswersTmp->selectWeighting($value);
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
        private function update_answer_records($key, $value, $dummy1, $dummy2) {
            // construction of the Question object
            $objQuestionTmp = new Question();
            // reads question informations
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
                $answer_field = $objAnswersTmp->selectAnswer(1);
                list($answer, $answerWeighting) = Question::blanksSplitAnswer($answer_field);
                // splits weightings that are joined with a comma
                $rightAnswerWeighting = explode(',', $answerWeighting);
                $blanks = Question::getBlanks($answer);
                foreach ($value as $row_key => $row_choice) {
                    // if user's choice is right assign rightAnswerWeight else 0
                    $canonical_choice = canonicalize_whitespace($objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ? remove_accents($row_choice) : $row_choice);
                    $canonical_match = $objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ? remove_accents($blanks[$row_key-1]) : $blanks[$row_key-1];
                    $right_answers = array_map('canonicalize_whitespace', preg_split('/\s*\|\s*/', $canonical_match));
                    $weight = in_array($canonical_choice, $right_answers) ? $rightAnswerWeighting[$row_key-1] : 0;
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
                        $answer_weight = $objAnswersTmp->selectWeighting($row_key);
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
                        $answer_weight = $objAnswersTmp->selectWeighting($row_key);
                    } else {
                        $answer_weight = 0;
                    }
                    Database::get()->query("UPDATE exercise_answer_record SET answer_id = ?d, weight = ?f , is_answered = 1
                        WHERE eurid = ?d AND question_id = ?d AND answer = ?d", $row_choice, $answer_weight, $eurid, $key, $row_key);
                    unset($answer_weight);
                }
            } else {
                if ($value!=0) {
                    $objAnswersTmp = new Answer($key);
                    $answer_weight = $objAnswersTmp->selectWeighting($value);
                } else {
                    $answer_weight = 0;
                }
                Database::get()->query("UPDATE exercise_answer_record SET answer_id = ?d, weight = ?f , is_answered = 1
                    WHERE eurid = ?d AND question_id = ?d", $value, $answer_weight, $eurid, $key);
            }
            unset($objQuestionTmp);
        }

        /**
         * Purge exercise user results
         */
        function purge() {
            $id = $this->id;

            Database::get()->query("DELETE d FROM exercise_answer_record d, exercise_user_record s
                              WHERE d.eurid = s.eurid AND s.eid = ?d", $id);
            Database::get()->query("DELETE FROM exercise_user_record WHERE eid = ?d",$id);
        }
        /**
         * Purge exercise user attempt
         */
        function purgeAttempt($id, $eurid) {

            Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid = ?d", $eurid);
            Database::get()->query("DELETE FROM exercise_user_record WHERE eid = ?d AND eurid = ?d", $id, $eurid);
        }

        /**
         * @brief modify attempt status
         * @param $eurid
         */
        function modifyAttempt($eurid, $status) {
            Database::get()->query("UPDATE exercise_user_record SET attempt_status = ?d WHERE eurid = ?d", $status, $eurid);
        }

        /**
         * Clone an Exercise
         */
        function duplicate() {
            global $langCopy2, $course_id, $course_code;

            $clone_course_id = $_POST['clone_to_course_id'];
            if (!check_editor(null, $clone_course_id)) {
                forbidden();
            }
            $id = $this->id;
            $exercise = $this->exercise.(($clone_course_id == $course_id)? " ($langCopy2)" : '');
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
            $clone_id = Database::get()->query("INSERT INTO `exercise` (course_id, title, description, `type`, `range`, start_date,
                                    end_date, temp_save, time_constraint, attempts_allowed, random, active, results, score, ip_lock, password_lock, assign_to_specific)
                                    VALUES (?d, ?s, ?s, ?d, ?d, ?t, ?t, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s, ?s, ?d)",
                                    $clone_course_id, $exercise, $description, $type, $range, $startDate, $endDate, $tempSave,
                                    $timeConstraint, $attemptsAllowed, $random, $active, $results, $score, $ip_lock, $password_lock, $assign_to_specific)->lastInsertID;
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
                                (course_id, question, description, weight, type, difficulty, category)
                                SELECT ?d, question, description, weight, type, difficulty, 0
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
        function distribution($correction_output) {

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
                    Database::get()->query("UPDATE exercise_user_record SET assigned_to = ?d WHERE eurid = ?d" , $teacherId, $eurid);
                    //gia na min xrisimopooioume to i pou ksanaksekinaei apo to 0
                    $stopped++;
                }
            }
        }

        /**
         * @brief run UPDATE queries for each eurid
         */
        function cancelDistribution() {

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
        function canonicalize_exercise_score($user_score, $total_score) {

            if ($total_score == 0) {
                $score = 0;
            } else if ($this->range > 0) {
                $score = round(($user_score / $total_score) * $this->range, 2);
            } else {
                $score = round($user_score, 2);
            }
            return $score;
        }
    }

}
