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
if (file_exists('../../include/log.php'))
    require_once '../../include/log.php';
if (file_exists('../../../include/log.php'))
    require_once '../../../include/log.php';

if (!class_exists('Exercise')):
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
        var $timeConstraint;
        var $attemptsAllowed;
        var $random;
        var $active;
        var $results;
        var $score;
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
            $this->startDate = date("Y-m-d H:i");
            $this->endDate = '';
            $this->timeConstraint = 0;
            $this->attemptsAllowed = 0;
            $this->random = 0;
            $this->active = 1;
            $this->public = 1;
            $this->results = 1;
            $this->score = 1;
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
            global $TBL_EXERCISE, $TBL_EXERCISE_QUESTION, $TBL_QUESTION, $course_id;

            $sql = "SELECT title, description, type, start_date, end_date, time_constraint,
			attempts_allowed, random, active, results, score
			FROM `$TBL_EXERCISE` WHERE course_id = $course_id AND id = $id";
            $result = db_query($sql);
            // if the exercise has been found
            if ($object = mysql_fetch_object($result)) {
                $this->id = $id;
                $this->exercise = $object->title;
                $this->description = $object->description;
                $this->type = $object->type;
                $this->startDate = $object->start_date;
                $this->endDate = $object->end_date;
                $this->timeConstraint = $object->time_constraint;
                $this->attemptsAllowed = $object->attempts_allowed;
                $this->random = $object->random;
                $this->active = $object->active;
                $this->results = $object->results;
                $this->score = $object->score;

                $sql = "SELECT question_id, q_position FROM `$TBL_EXERCISE_QUESTION`, `$TBL_QUESTION`
				WHERE course_id = $course_id AND question_id = id AND exercise_id = $id ORDER BY q_position";
                $result = db_query($sql);

                // fills the array with the question ID for this exercise
                // the key of the array is the question position
                while ($object = mysql_fetch_object($result)) {
                    // makes sure that the question position is unique
                    while (isset($this->questionList[$object->q_position])) {
                        $object->q_position++;
                    }
                    $this->questionList[$object->q_position] = $object->question_id;
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
            global $TBL_EXERCISE, $TBL_QUESTION, $course_id;

            $id = $this->id;
            $exercise = $this->exercise;
            $description = standard_text_escape($this->description);
            $type = $this->type;
            $startDate = $this->startDate;
            $endDate = $this->endDate;
            $timeConstraint = $this->timeConstraint;
            $attemptsAllowed = $this->attemptsAllowed;
            $random = $this->random;
            $active = $this->active;
            $public = $this->public;
            $results = $this->results;
            $score = $this->score;

            // exercise already exists
            if ($id) {
                $sql = "UPDATE `$TBL_EXERCISE`
				SET title = " . quote($exercise) . ", description = " . quote($description) . ", type = '$type'," .
                        "start_date = '$startDate', end_date = '$endDate', time_constraint = '$timeConstraint'," .
                        "attempts_allowed = '$attemptsAllowed', random = '$random',
				active = $active, public = $public, results = '$results', score = '$score' 
                                WHERE course_id = $course_id AND id = $id";
                db_query($sql);

                Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, array('id' => $id,
                    'title' => $exercise,
                    'description' => $description));
            }
            // creates a new exercise
            else {
                $sql = "INSERT INTO `$TBL_EXERCISE` (course_id, title, description, `type`, start_date, 
                                        end_date, time_constraint, attempts_allowed, random, active, results, score) 
				VALUES ($course_id, " . quote($exercise) . ", " . quote($description) . ", $type, '$startDate', '$endDate',
					$timeConstraint, $attemptsAllowed, $random, $active, $results, $score)";
                db_query($sql);
                $this->id = mysql_insert_id();

                Log::record($course_id, MODULE_ID_EXERCISE, LOG_INSERT, array('id' => $this->id,
                    'title' => $exercise,
                    'description' => $description));
            }
            // updates the question position
            foreach ($this->questionList as $position => $questionId) {
                $sql = "UPDATE `$TBL_QUESTION` SET q_position = '$position' 
                                WHERE course_id = $course_id AND id='$questionId'";
                db_query($sql);
            }
        }

        /**
         * moves a question up in the list
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - question ID to move up
         */
        function moveUp($id) {
            global $TBL_QUESTION, $course_id;

            list($pos) = mysql_fetch_array(db_query("SELECT q_position FROM `$TBL_QUESTION`
							WHERE course_id = $course_id AND id = '$id'"));

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
            global $TBL_QUESTION, $course_id;

            list($pos) = mysql_fetch_array(db_query("SELECT q_position FROM `$TBL_QUESTION`
							WHERE course_id = $course_id AND id = '$id'"));

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
            global $TBL_EXERCISE_QUESTION, $TBL_EXERCISE, $course_id;

            $id = $this->id;

            $sql = "DELETE FROM `$TBL_EXERCISE_QUESTION` WHERE exercise_id = '$id'";
            db_query($sql);

            $title = db_query_get_single_value("SELECT title FROM `$TBL_EXERCISE` 
                                                WHERE course_id = $course_id AND id = '$id'");

            $sql = "DELETE FROM `$TBL_EXERCISE` WHERE course_id = $course_id AND id = '$id'";
            db_query($sql);
            Log::record($course_id, MODULE_ID_EXERCISE, LOG_DELETE, array('title' => $title));
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
        function record_answers($choice, $exerciseResult) {
            global $is_editor;
       
            // if the user has answered at least one question
            if (is_array($choice)) {
                //if all questions on the same page
                if ($this->selectType() == 1) {
                    // $exerciseResult receives the content of the form.
                    // Each choice of the student is stored into the array $choice
                    $exerciseResult = $choice;
                    if (!$is_editor) { 
                        foreach ($exerciseResult as $key => $value) {
                            $this->insert_answer_records($key, $value);
                        }
                    }
                //else if one question per page
                } else {
                    // gets the question ID from $choice. It is the key of the array
                    list($key) = array_keys($choice);
                    // if the user didn't already answer this question
                    if (!isset($exerciseResult[$key])) {
                        // stores the user answer into the array
                        $value = $exerciseResult[$key] = $choice[$key];
                        if (!$is_editor) {
                            $this->insert_answer_records($key, $value);
                        }
                    }
                }
            }
            return $exerciseResult;
        }
        /**
         * Insert user answers
         */
        private function insert_answer_records($key, $value) {
                             // construction of the Question object
                            $objQuestionTmp = new Question(); 
                            // reads question informations
                            $objQuestionTmp->read($key);
                            $question_type = $objQuestionTmp->selectType();
                            $eurid = $_SESSION['exerciseUserRecordID'][$this->id];
                            if ($objQuestionTmp->selectType()==6) {
                                Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer)
                                        VALUES (?d, ?d, ?s)", $eurid, $key, $value);
                            } elseif ($objQuestionTmp->selectType()==3) {
                                $objAnswersTmp = new Answer($key);
                                $answer_field = $objAnswersTmp->selectAnswer(1);
                                //splits answer string from weighting string
                                list($answer, $answerWeighting) = explode('::', $answer_field);
                                // splits weightings that are joined with a comma
                                $rightAnswerWeighting = explode(',', $answerWeighting);
                                //getting all matched strings between [ and ] delimeters
                                preg_match_all('#\[(.*?)\]#', $answer, $match);
                                foreach ($value as $row_key => $row_choice) {
                                    //if user's choice is right assign rightAnswerWeight else 0
                                    
                                        $weight = ($row_choice == $match[1][$row_key]) ? $rightAnswerWeighting[$row_key] : 0;
                                        Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer, answer_id, weight)
                                                VALUES (?d, ?d, ?s, ?d, ?f)", $eurid, $key, $row_choice, $row_key, $weight);
                                    
                                }
                            } elseif ($objQuestionTmp->selectType()==2) {
                                if ($value == 0) {
                                    $row_key = 0;
                                    $answer_weight = 0;
                                    Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer_id, weight)
                                                    VALUES (?d, ?d, ?d, ?f)", $eurid, $key, $row_key, $answer_weight);    
                                } else {
                                    $objAnswersTmp = new Answer($key);
                                    foreach ($value as $row_key => $row_choice) {
                                        $answer_weight = $objAnswersTmp->selectWeighting($row_key);
                                        Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer_id, weight)
                                                VALUES (?d, ?d, ?d, ?f)", $eurid, $key, $row_key, $answer_weight);

                                        unset($answer_weight);
                                    }
                                    unset($objAnswersTmp);                                                                    
                                }
                            } elseif ($objQuestionTmp->selectType()==4) {
                                $objAnswersTmp = new Answer($key);
                                foreach ($value as $row_key => $row_choice) {
                                    $correct_match = $objAnswersTmp->isCorrect($row_key);
                                    if ($correct_match == $row_choice) {
                                        $answer_weight = $objAnswersTmp->selectWeighting($row_key);
                                    } else {
                                        $answer_weight = 0;
                                    }
                                    
                                    // In matching questions isCorrect() returns position of left column answers while $row_key returns right column position
                                    
                                    Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer, answer_id, weight)
                                            VALUES (?d, ?d, ?d, ?d, ?f)", $eurid, $key, $row_key, $row_choice, $answer_weight);

                                    unset($answer_weight);
                                }                                
                            } else {
                                if ($value!=0) {
                                    $objAnswersTmp = new Answer($key);
                                    $answer_weight = $objAnswersTmp->selectWeighting($value);
                                } else {
                                    $answer_weight = 0;
                                }
                                Database::get()->query("INSERT INTO exercise_answer_record (eurid, question_id, answer_id, weight)
                                        VALUES (?d, ?d, ?d, ?f)", $eurid, $key, $value, $answer_weight);
                            }
                            unset($objQuestionTmp);           
        }        
        
        /**
         * Purge exercise user results
         */
        function purge() {
            $id = $this->id;
            $eurids = Database::get()->queryArray("SELECT eurid FROM exercise_user_record WHERE eid = ?d",$id);           
            $sql_where_in = implode(', ', array_map(function($e) {
                return $e->eurid;
            }, $eurids));                    
            Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid IN (?s)", $sql_where_in);
            Database::get()->query("DELETE FROM exercise_user_record WHERE eid = ?d",$id);
        }

    }

    

    

endif;