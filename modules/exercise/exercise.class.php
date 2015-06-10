<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

require_once 'question.class.php';
require_once 'answer.class.php';
if (file_exists('../../include/log.php')) {
    require_once '../../include/log.php';
}
if (file_exists('../../../include/log.php')) {
    require_once '../../../include/log.php';
}

if (!class_exists('Exercise')) {

    /* >>>>>>>>>>>>>>>>>>>> CLASS EXERCISE <<<<<<<<<<<<<<<<<<<< */

    /**
     * This class allows to instantiate an object of type Exercise
     *
     * @author - Olivier Brouckaert
     */
    class Exercise {

        var $id;
        var $exercise;
        var $description;
        var $type;
        var $startDate;
        var $endDate;
        var $tempSave;
        var $timeConstraint;
        var $attemptsAllowed;
        var $random;
        var $active;
        var $results;
        var $score;
        var $ip_lock;
        var $password_lock;
        var $questionList;  // array with the list of this exercise's questions

        /**
         * constructor of the class
         *
         * @author - Olivier Brouckaert
         */

        function Exercise() {
            $this->id = 0;
            $this->exercise = '';
            $this->description = '';
            $this->type = 1;
            $this->startDate = date("Y-m-d H:i:s");
            $this->endDate = null;
            $this->tempSave = 0;
            $this->timeConstraint = 0;
            $this->attemptsAllowed = 0;
            $this->random = 0;
            $this->active = 1;
            $this->public = 1;
            $this->results = 1;
            $this->score = 1;
            $this->ip_lock = null;
            $this->password_lock = null;
            $this->questionList = array();
        }

        /**
         * reads exercise informations from the data base
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - exercise ID
         * @return - boolean - true if exercise exists, otherwise false
         */
        function read($id) {
            global $course_id;

            $object = Database::get()->querySingle("SELECT title, description, type, start_date, end_date, temp_save, time_constraint,
			attempts_allowed, random, active, public, results, score, ip_lock, password_lock
			FROM `exercise` WHERE course_id = ?d AND id = ?d", $course_id, $id);

            // if the exercise has been found
            if ($object) {
                $this->id = $id;
                $this->exercise = $object->title;
                $this->description = $object->description;
                $this->type = $object->type;
                $this->startDate = $object->start_date;
                $this->endDate = $object->end_date;
                $this->tempSave = $object->temp_save;
                $this->timeConstraint = $object->time_constraint;
                $this->attemptsAllowed = $object->attempts_allowed;
                $this->random = $object->random;
                $this->active = $object->active;
                $this->public = $object->public;
                $this->results = $object->results;
                $this->score = $object->score;
                $this->ip_lock = $object->ip_lock;
                $this->password_lock = $object->password_lock;

                $result = Database::get()->queryArray("SELECT question_id, q_position FROM `exercise_with_questions`, `exercise_question`
				WHERE course_id = ?d AND question_id = id AND exercise_id = ?d ORDER BY q_position", $course_id, $id);
                
                // fills the array with the question ID for this exercise
                // the key of the array is the question position
                foreach ($result as $row) {
                    // makes sure that the question position is unique
                    while (isset($this->questionList[$row->q_position])) {
                        $row->q_position++;
                    }
                    $this->questionList[$row->q_position] = $row->question_id;
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
        /**
         * tells if questions are selected randomly, and if so returns the draws
         *
         * @author - Olivier Brouckaert
         * @return - integer - 0 if not random, otherwise the draws
         */
        function isRandom() {
            return $this->random;
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
         * returns the number of questions in this exercise
         *
         * @author - Olivier Brouckaert
         * @return - integer - number of questions
         */
        function selectNbrQuestions() {
            return sizeof($this->questionList);
        }

        /**
         * selects questions randomly in the question list
         *
         * @author - Olivier Brouckaert
         * @return - array - if the exercise is not set to take questions randomly, returns the question list
         * 					 without randomizing, otherwise, returns the list with questions selected randomly
         */
        function selectRandomList() {
            // if the exercise is not a random exercise, or if there are not at least 2 questions
            if (!$this->random || $this->selectNbrQuestions() < 2 || $this->random <= 0) {
                return $this->questionList;
            }

            // takes all questions
            if ($this->random > $this->selectNbrQuestions()) {
                $draws = $this->selectNbrQuestions();
            } else {
                $draws = $this->random;
            }

            $randQuestionList = array();
            $alreadyChosed = array();

            // loop for the number of draws
            for ($i = 0; $i < $draws; $i++) {
                // selects a question randomly
                do {
                    $rand = crypto_rand_secure(0, $this->selectNbrQuestions() - 1);
                }
                // if the question has already been selected, continues in the loop
                while (in_array($rand, $alreadyChosed));

                $alreadyChosed[] = $rand;
                $j = 0;

                foreach ($this->questionList as $key => $val) {
                    // if we have found the question chosed above
                    if ($j == $rand) {
                        $randQuestionList[$key] = $val;
                        break;
                    }
                    $j++;
                }
            }
            return $randQuestionList;
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
        function updateIPLock($ips) {
            $this->ip_lock = (empty($ips)) ? null : $ips;
        }
        function updatePasswordLock($password) {
            $this->password_lock = (empty($password)) ? null : $password;
        }        
        /**
         * sets to 0 if questions are not selected randomly
         * if questions are selected randomly, sets the draws
         *
         * @author - Olivier Brouckaert
         * @param - integer $random - 0 if not random, otherwise the draws
         */
        function setRandom($random) {
            $this->random = $random;
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
            $description = standard_text_escape($this->description);
            $type = $this->type;
            $startDate = $this->startDate;
            $endDate = $this->endDate;
            $tempSave = $this->tempSave;
            $timeConstraint = $this->timeConstraint;
            $attemptsAllowed = $this->attemptsAllowed;
            $random = $this->random;
            $active = $this->active;
            $public = $this->public;
            $results = $this->results;
            $score = $this->score;
            $ip_lock = $this->ip_lock;
            $password_lock = $this->password_lock; 
            // exercise already exists
            if ($id) {
                $affected_rows = Database::get()->query("UPDATE `exercise`
				SET title = ?s, description = ?s, type = ?d," .
                        "start_date = ?t, end_date = ?t, temp_save = ?d, time_constraint = ?d," .
                        "attempts_allowed = ?d, random = ?d, active = ?d, public = ?d, results = ?d, score = ?d, ip_lock = ?s, password_lock = ?s
                        WHERE course_id = ?d AND id = ?d", 
                        $exercise, $description, $type, $startDate, $endDate, $tempSave, $timeConstraint, $attemptsAllowed, $random, $active, $public, $results, $score, $ip_lock, $password_lock, $course_id, $id)->affectedRows;
                if ($affected_rows > 0) {
                    Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, array('id' => $id,
                        'title' => $exercise,
                        'description' => $description));
                }
            }
            // creates a new exercise
            else {
                $this->id = Database::get()->query("INSERT INTO `exercise` (course_id, title, description, type, start_date, 
                        end_date, temp_save, time_constraint, attempts_allowed, random, active, results, score, ip_lock, password_lock) 
			VALUES (?d, ?s, ?s, ?d, ?t, ?t, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s, ?s)", 
                        $course_id, $exercise, $description, $type, $startDate, $endDate, $tempSave, 
                        $timeConstraint, $attemptsAllowed, $random, $active, $results, $score, $ip_lock, $password_lock)->lastInsertID;

                Log::record($course_id, MODULE_ID_EXERCISE, LOG_INSERT, array('id' => $this->id,
                    'title' => $exercise,
                    'description' => $description));
            }
            // updates the question position
            foreach ($this->questionList as $position => $questionId) {
                Database::get()->query("UPDATE `exercise_question` SET q_position = ?d 
                                WHERE course_id = ?d AND id = ?d", $position, $course_id, $questionId);
            }
        }

        /**
         * moves a question up in the list
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - question ID to move up
         */
        function moveUp($id) {
            global $course_id;

            $pos = Database::get()->querySingle("SELECT q_position FROM `exercise_question`
				  WHERE course_id = ?d AND id = ?d", $course_id, $id)->q_position;
            if ($pos > 1) {
                $temp = $this->questionList[$pos - 1];
                $this->questionList[$pos - 1] = $this->questionList[$pos];
                $this->questionList[$pos] = $temp;
            }
            return;
        }

        /**
         * moves a question down in the list
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - question ID to move down
         */
        function moveDown($id) {
            global $course_id;

            $pos = Database::get()->querySingle("SELECT q_position FROM `exercise_question`
				  WHERE course_id = ?d AND id = ?d", $course_id, $id)->q_position;
            if ($pos < count($this->questionList)) {
                $temp = $this->questionList[$pos + 1];
                $this->questionList[$pos + 1] = $this->questionList[$pos];
                $this->questionList[$pos] = $temp;
            }
            return;
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
            $deleted_rows = Database::get()->query("DELETE FROM `exercise` WHERE course_id = ?d AND id = ?d", $course_id, $id)->affectedRows;
            if ($deleted_rows > 0) {
                Log::record($course_id, MODULE_ID_EXERCISE, LOG_DELETE, array('title' => $title));
            }
        }
         /**
         * checks if exercise time has expired
         */
        function has_time_expired($choice, $exerciseResult) {
            global $is_editor;
       

        }       
        /**
         * keeps record of user answers
         */
        function record_answers($choice, $exerciseResult, $record_type = 'insert') {
            global $is_editor;
            $action = $record_type.'_answer_records'; 
            // if the user has answered at least one question
            if (is_array($choice)) {
                //if all questions on the same page
                if ($this->selectType() == 1) {
                    // $exerciseResult receives the content of the form.
                    // Each choice of the student is stored into the array $choice
                    $exerciseResult = $choice;
                    foreach ($exerciseResult as $key => $value) {
                        $this->$action($key, $value);
                    }
                //else if one question per page
                } else {
                    // gets the question ID from $choice. It is the key of the array
                    list($key) = array_keys($choice);
                    // if the user didn't already answer this question
                    if (!isset($exerciseResult[$key])) {
                        // stores the user answer into the array
                        $value = $exerciseResult[$key] = $choice[$key];                       
                        $this->$action($key, $value);                    
                    }
                }
            }
            return $exerciseResult;
        }
        /**
         * keeps record of user answers
         */
        function get_attempt_results_array($eurid) {
            $exerciseResult = array();
            $results = Database::get()->queryArray("SELECT * FROM exercise_answer_record WHERE eurid = ?d AND is_answered = 1", $eurid);
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
         * Save User Unanswered Questions either as answered (default behaviour)
         * or as unanswered by passing parameter 0 to the function
         * (Used for sequential exercises on time expiration 
         * and when student wants to temporary save his answers)
         */
        function save_unanswered($as_answered = 1) {
            $id = $this->id;
            $attempt_value = $_POST['attempt_value'];
            $eurid = $_SESSION['exerciseUserRecordID'][$id][$attempt_value];
            $question_ids = Database::get()->queryArray('SELECT DISTINCT question_id FROM exercise_answer_record WHERE eurid = ?d AND is_answered = 1', $eurid);
            if (count($question_ids) > 0) {
                foreach ($question_ids as $row) {
                    $answered_question_ids[] = $row->question_id;
                }
            } else {
                $answered_question_ids = array();
            }
            $questionList = $_SESSION['questionList'][$id][$attempt_value];
            $unanswered_questions = array_diff($questionList, $answered_question_ids);
            foreach ($unanswered_questions as $question_id) {
                // construction of the Question object
                
                $objQuestionTmp = new Question();
                // reads question informations
                $objQuestionTmp->read($question_id);               
                $question_type = $objQuestionTmp->selectType();
                if ($question_type == MATCHING) {
                    // construction of the Answer object
                    $objAnswerTmp = new Answer($question_id);
                    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();
                    for ($answerId = 1; $answerId <= $nbrAnswers; $answerId++) {
                        //must get answer id ONLY where correct value existS
                        $answerCorrect = $objAnswerTmp->isCorrect($answerId);
                        if ($answerCorrect) {
                            $value[$answerId] = 0;
                        }
                    }
                    unset($objAnswerTmp);
                } else if ($question_type == FILL_IN_BLANKS || $question_type == FILL_IN_BLANKS_TOLERANT) {  
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
                $this->insert_answer_records($question_id, $value, $as_answered);
                unset($value);
            }
        }        
        /**
         * Insert user answers
         */
        private function insert_answer_records($key, $value, $as_answered = 1) {
            // construction of the Question object
           $objQuestionTmp = new Question(); 
           // reads question informations
           $objQuestionTmp->read($key);
           $question_type = $objQuestionTmp->selectType();
           $id = $this->id;
           $attempt_value = $_POST['attempt_value'];
           $eurid = $_SESSION['exerciseUserRecordID'][$id][$attempt_value];
           if ($objQuestionTmp->selectType() == FREE_TEXT) {
               if (!empty($value)) {
                   Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer, answer_id, is_answered)
                           VALUES (?d, ?d, ?s, ?d, ?d)", $eurid, $key, $value, 1, $as_answered);
               } else {
                   $weight = ($as_answered == 1) ? 0 : NULL; // if the question is unswered give 0 weight else give NULL
                   Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer, answer_id, weight, is_answered)
                           VALUES (?d, ?d, ?s, ?d, ?d, ?d)", $eurid, $key, $value, 0, $weight, $as_answered);                                    
               }                              
           } elseif ($objQuestionTmp->selectType() == FILL_IN_BLANKS || $objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT) {
               $objAnswersTmp = new Answer($key);
               $answer_field = $objAnswersTmp->selectAnswer(1);
               //splits answer string from weighting string
               list($answer, $answerWeighting) = explode('::', $answer_field);
               // splits weightings that are joined with a comma
               $rightAnswerWeighting = explode(',', $answerWeighting);
               //getting all matched strings between [ and ] delimeters
               preg_match_all('#(?<=\[)(?!/?m)[^\]]+#', $answer, $match);
               foreach ($value as $row_key => $row_choice) {
                   //if user's choice is right assign rightAnswerWeight else 0
                   //Some more coding should be done if blank can have multiple answers
                       $canonical_choice = $objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ? strtr(mb_strtoupper($row_choice, 'UTF-8'), "ΆΈΉΊΌΎΏ", "ΑΕΗΙΟΥΩ") : $row_choice;
                       $canonical_match = $objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ? strtr(mb_strtoupper($match[0][$row_key-1], 'UTF-8'), "ΆΈΉΊΌΎΏ", "ΑΕΗΙΟΥΩ") : $match[0][$row_key-1];
                       $right_answers = preg_split('/\s*,\s*/', $canonical_match);
                       $weight = in_array($canonical_choice, $right_answers) ? $rightAnswerWeighting[$row_key-1] : 0;
                       Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer, answer_id, weight, is_answered)
                               VALUES (?d, ?d, ?s, ?d, ?f, ?d)", $eurid, $key, $row_choice, $row_key, $weight, $as_answered);

               }
           } elseif ($objQuestionTmp->selectType() == MULTIPLE_ANSWER) {
               if ($value == 0) {
                   $row_key = 0;
                   $answer_weight = 0;
                   Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer_id, weight, is_answered)
                                   VALUES (?d, ?d, ?d, ?f, ?d)", $eurid, $key, $row_key, $answer_weight, $as_answered);    
               } else {
                   $objAnswersTmp = new Answer($key);
                   foreach ($value as $row_key => $row_choice) {
                       $answer_weight = $objAnswersTmp->selectWeighting($row_key);
                       Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer_id, weight, is_answered)
                               VALUES (?d, ?d, ?d, ?f, ?d)", $eurid, $key, $row_key, $answer_weight, $as_answered);

                       unset($answer_weight);
                   }
                   unset($objAnswersTmp);                                                                    
               }
           } elseif ($objQuestionTmp->selectType() == MATCHING) {
               $objAnswersTmp = new Answer($key);
               foreach ($value as $row_key => $row_choice) {
                   // In matching questions isCorrect() returns position of left column answers while $row_key returns right column position 
                   $correct_match = $objAnswersTmp->isCorrect($row_key);                              
                   if ($correct_match == $row_choice) {
                       $answer_weight = $objAnswersTmp->selectWeighting($row_key);
                   } else {
                       $answer_weight = 0;
                   }

                   Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer, answer_id, weight, is_answered)
                           VALUES (?d, ?d, ?d, ?d, ?f, ?d)", $eurid, $key, $row_key, $row_choice, $answer_weight, $as_answered);
                   unset($answer_weight);
               }                                
           } else {
               if ($value!=0) {
                   $objAnswersTmp = new Answer($key);
                   $answer_weight = $objAnswersTmp->selectWeighting($value);
               } else {
                   $answer_weight = 0;
               }
               Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer_id, weight, is_answered)
                       VALUES (?d, ?d, ?d, ?f, ?d)", $eurid, $key, $value, $answer_weight, $as_answered);
           }
           unset($objQuestionTmp);           
        }        
        /**
         * Update user answers
         */
        private function update_answer_records($key, $value) {
            
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
               //splits answer string from weighting string
               list($answer, $answerWeighting) = explode('::', $answer_field);
               // splits weightings that are joined with a comma
               $rightAnswerWeighting = explode(',', $answerWeighting);
               //getting all matched strings between [ and ] delimeters
               //preg_match_all('#\[(?!/?m)(.*?)\]#', $answer, $match);
               preg_match_all('#(?<=\[)(?!/?m)[^\]]+#', $answer, $match);
               foreach ($value as $row_key => $row_choice) {
                   //if user's choice is right assign rightAnswerWeight else 0
                       $canonical_choice = $objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ? strtr(mb_strtoupper($row_choice, 'UTF-8'), "ΆΈΉΊΌΎΏ", "ΑΕΗΙΟΥΩ") : $row_choice;
                       $canonical_match = $objQuestionTmp->selectType() == FILL_IN_BLANKS_TOLERANT ? strtr(mb_strtoupper($match[0][$row_key-1], 'UTF-8'), "ΆΈΉΊΌΎΏ", "ΑΕΗΙΟΥΩ") : $match[0][$row_key-1]; 
                       $right_answers = preg_split('/\s*,\s*/', $canonical_match);
                       $weight = in_array($canonical_choice, $right_answers) ? $rightAnswerWeighting[$row_key-1] : 0;
                       Database::get()->query("UPDATE exercise_answer_record SET answer = ?s, weight = ?f, is_answered = 1 
                                              WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d", $row_choice, $weight, $eurid, $key, $row_key);
               }
           } elseif ($question_type == MULTIPLE_ANSWER) {
               if ($value == 0) {
                   $row_key = 0;
                   $answer_weight = 0;
                   Database::get()->query("UPDATE exercise_answer_record SET is_answered= 1 WHERE eurid = ?d AND question_id = ?d", $eurid, $key);
               } else {
                   $objAnswersTmp = new Answer($key);
                   $i=1;
                   // the first time in the loop we should update in order to keep question position in the DB
                   // and then insert a new record if there are more than one answers
                   foreach ($value as $row_key => $row_choice) {
                       $answer_weight = $objAnswersTmp->selectWeighting($row_key);
                       if ($i==1) {
                           Database::get()->query("UPDATE exercise_answer_record SET answer_id = ?d, weight = ?f , is_answered = 1 WHERE eurid = ?d AND question_id = ?d", $row_key, $answer_weight, $eurid, $key);
                       } else {
                           Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer_id, weight, is_answered)
                                    VALUES (?d, ?d, ?d, ?f, 1)", $eurid, $key, $row_key, $answer_weight);
                       }
                       unset($answer_weight);
                       $i++;
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
            
            Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid IN ("
                    . "SELECT eurid FROM exercise_user_record WHERE eid = ?d"
                    . ")", $id);
            Database::get()->query("DELETE FROM exercise_user_record WHERE eid = ?d",$id);
        }
        /**
         * Purge exercise user attempt
         */
        function purgeAttempt($eurid) {
            $id = $this->id;
            
            Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid = ?d", $eurid);
            Database::get()->query("DELETE FROM exercise_user_record WHERE eid = ?d AND eurid = ?d", $id, $eurid);
        }        
        /**
         * Clone an Exercise
         */
        function duplicate() {
            global $langCopy2, $course_id;

            $clone_course_id = $_POST['clone_to_course_id'];
            if (!check_editor(null, $clone_course_id)) {
                forbidden();
            }
            $id = $this->id;
            $exercise = $this->exercise.(($clone_course_id == $course_id)? " ($langCopy2)" : '');
            $description = standard_text_escape($this->description);
            $type = $this->type;
            $startDate = $this->startDate;
            $endDate = $this->endDate;
            $tempSave = $this->tempSave;
            $timeConstraint = $this->timeConstraint;
            $attemptsAllowed = $this->attemptsAllowed;
            $random = $this->random;
            $active = $this->active;
            $public = $this->public;
            $results = $this->results;
            $score = $this->score;
            $ip_lock = $this->ip_lock;
            $password_lock = $this->password_lock;
            $clone_id = Database::get()->query("INSERT INTO `exercise` (course_id, title, description, type, start_date, 
                                    end_date, temp_save, time_constraint, attempts_allowed, random, active, results, score, ip_lock, password_lock) 
                                    VALUES (?d, ?s, ?s, ?d, ?t, ?t, ?d, ?d, ?d, ?d, ?d, ?d, ?d, ?s, ?s)", 
                                    $clone_course_id, $exercise, $description, $type, $startDate, $endDate, $tempSave, 
                                    $timeConstraint, $attemptsAllowed, $random, $active, $results, $score, $ip_lock, $password_lock)->lastInsertID;        
            if ($clone_course_id != $course_id) {
                // copy questions and answers to new course question pool
                Database::get()->queryFunc("SELECT question_id AS id FROM exercise_with_questions
                        WHERE exercise_id = ?d",
                    function ($question) use ($clone_id, $clone_course_id) {
                        $question_clone_id = Database::get()->query("INSERT INTO exercise_question
                            (course_id, question, description, weight, q_position, type, difficulty, category)
                            SELECT ?d, question, description, weight, q_position, type, difficulty, 0
                                FROM `exercise_question` WHERE id = ?d", $clone_course_id, $question->id)->lastInsertID;
                        Database::get()->query("INSERT INTO exercise_with_questions
                            (question_id, exercise_id) VALUES (?d, ?d)", $question_clone_id, $clone_id);
                        Database::get()->query("INSERT INTO exercise_answer
                            (question_id, answer, correct, comment, weight, r_position)
                            SELECT ?d, answer, correct, comment, weight, r_position FROM exercise_answer
                                WHERE question_id = ?d",
                            $question_clone_id, $question->id);
                    },
                    $id);
            } else {
                // add question to new exercise
                Database::get()->query("INSERT INTO `exercise_with_questions`
                        (question_id, exercise_id)
                        SELECT question_id, ?d FROM `exercise_with_questions`
                            WHERE exercise_id = ?d", $clone_id, $id);
            }
        }
    }

}
