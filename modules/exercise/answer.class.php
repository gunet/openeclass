<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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


if (!class_exists('Answer')):

    /* >>>>>>>>>>>>>>>>>>>> CLASS ANSWER <<<<<<<<<<<<<<<<<<<< */

    /**
     * This class allows to instantiate an object of type Answer
     *
     * 5 arrays are created to receive the attributes of each answer
     * belonging to a specified question
     *
     * @author - Olivier Brouckaert
     */
    class Answer {

        var $questionId;        
        var $answer;
        var $correct;
        var $comment;
        var $weighting;
        var $position;
        var $nbrAnswers;        

        /**
         * constructor of the class
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - question ID that answers belong to
         */
        public function __construct($questionId) {
            $this->questionId = $questionId;            
            $this->nbrAnswers = 2;
            $this->answer = array();
            $this->correct = array();
            $this->comment = array();
            $this->weighting = array();
            $this->position = array();
            
            $this->read();
        }       

        /**
         * reads answer informations from the data base
         *
         * @author - Olivier Brouckaert
         */
        function read() {
            $questionId = $this->questionId;
            $result = Database::get()->queryArray("SELECT answer, correct, comment, weight, r_position
                            FROM exercise_answer WHERE question_id = ?d ORDER BY r_position", $questionId);
            if ($result) { // found result ?
                $i = 1;                
                foreach ($result as $object) {
                    $this->answer[$i] = $object->answer;
                    $this->correct[$i] = $object->correct;
                    $this->comment[$i] = $object->comment;
                    $this->weighting[$i] = $object->weight;
                    $this->position[$i] = $object->r_position;
                    $i++;
                }
                $this->nbrAnswers = $i - 1;
            } else { // new answer
                for ($i = 1; $i <= 2; $i++) {
                    $this->answer[$i] = '';
                    if ($this->getQuestionType() == MATCHING) {
                        $this->correct[$i] = 1;
                    } else {
                        $this->correct[$i] = 0;    
                    }                    
                    $this->comment[$i] = '';
                    $this->weighting[$i] = 0.00;
                    $this->position[$i] = 0;
                }                
            }
        }

                        
        /**
         * returns the number of answers in this question
         *
         * @author - Olivier Brouckaert
         * @return - integer - number of answers
         */
        public function selectNbrAnswers() {
            return $this->nbrAnswers;
        }

        /**
         * returns the question ID which the answers belong to
         *
         * @author - Olivier Brouckaert
         * @return - integer - the question ID
         */
        public function selectQuestionId() {
            return $this->questionId;
        }

        /**
         * returns the answer title
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - string - answer title
         */
        public function selectAnswer($id) {            
            if (isset($this->answer[$id])) {
                return $this->answer[$id];
            } else {
                return '';
            }
        }

        /**
         * tells if answer is correct or not
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - integer - 0 if bad answer, not 0 if good answer
         */
        public function isCorrect($id) {
            return $this->correct[$id];
        }

        /**
         * returns answer comment
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - string - answer comment
         */
        public function selectComment($id) {
            return $this->comment[$id];
        }

        /**
         * returns answer weighting
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - float - answer weighting
         */
        public function selectWeighting($id) {
            return $this->weighting[$id];
        }

        /**
         * returns answer position
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - integer - answer position
         */
        public function selectPosition($id) {
            return $this->position[$id];
        }

        
        public function getFirstMatchingPosition() {
            $pos = Database::get()->querySingle("SELECT r_position FROM exercise_answer "
                    . "WHERE question_id = ?d "
                    . "AND correct > 0 "
                    . "ORDER BY r_position ASC LIMIT 1", $this->questionId)->r_position;
            return $pos;
        }
        
        /**
         * creates a new answer
         *
         * @author - Olivier Brouckaert
         * @param - string $answer - answer title
         * @param - integer $correct - 0 if bad answer, not 0 if good answer
         * @param - string $comment - answer comment
         * @param - float $weighting - answer weighting
         * @param - integer $position - answer position
         */
        public function createAnswer($answer, $correct, $comment, $weighting, $position, $newEmptyAnswer = false) {
            
            if ($newEmptyAnswer) {
                $this->nbrAnswers = $this->nbrAnswers + 1;
                $id = $this->nbrAnswers;
                Database::get()->querySingle("INSERT INTO exercise_answer (question_id, answer, correct, comment, weight, r_position) 
                    VALUES (?d, ?s, ?d, ?s, ?f, ?d)", $this->questionId, $answer, $correct, $comment, $weighting, $position);
            } else {
                $this->nbrAnswers = $position;
                $id = $position;                
            }            
            $this->answer[$id] = $answer;
            $this->correct[$id] = $correct;
            $this->comment[$id] = $comment;
            $this->weighting[$id] = $weighting;
            $this->position[$id] = $position;                       
        }

        /**
         * save answers into the data base
         *
         * @author - Olivier Brouckaert
         */
        public function save() {

            $questionId = $this->questionId;
            // removes old answers before inserting of new ones
           Database::get()->query("DELETE FROM exercise_answer WHERE question_id = ?d", $questionId);            
            // inserts new answers into data base
            $sql = "INSERT INTO exercise_answer (question_id, answer, correct, comment, weight, r_position) VALUES ";           
            
            for ($i = 1; $i <= $this->nbrAnswers; $i++) {
                  $data_array[] = $questionId;
                  $data_array[] = $this->answer[$i];
                  $data_array[] = $this->correct[$i];
                  $data_array[] = $this->comment[$i];
                  $data_array[] = $this->weighting[$i];
                  $data_array[] = $this->position[$i];
                $sql .= "(?d, ?s, ?d, ?s, ?f, ?d),";
            }
            $sql = substr($sql, 0, -1);            
            Database::get()->query($sql, $data_array);
                       
        }

        /**
         * duplicates answers by copying them into another question
         *
         * @author - Olivier Brouckaert
         * @param - integer $newQuestionId - ID of the new question
         */
        public function duplicate($newQuestionId) {

            // if at least one answer
            if ($this->nbrAnswers) {
                // inserts new answers into data base
                $sql = "INSERT INTO exercise_answer (question_id, answer, correct, comment, weight, r_position) VALUES ";

                for ($i = 1; $i <= $this->nbrAnswers; $i++) {
                    $data_array[] = $newQuestionId;
                    $data_array[] = $this->answer[$i];
                    $data_array[] = $this->correct[$i];
                    $data_array[] = $this->comment[$i];
                    $data_array[] = $this->weighting[$i];
                    $data_array[] = $this->position[$i];  
                    
                    $sql .= "(?d, ?s, ?d, ?s, ?f, ?d),";
                }

                $sql = substr($sql, 0, -1);
                Database::get()->query($sql,$data_array);
            }
        }
        
        /**
         * @brief get question type (e.g. multiple choice, matching etc)
         * @return type
         */
        private function getQuestionType() {
            
            $question_type = Database::get()->querySingle("SELECT `type` FROM exercise_question WHERE id = ?d", $this->selectQuestionId())->type;            
            return $question_type;            
            
        }

    }
endif;
