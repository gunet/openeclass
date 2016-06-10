<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
        // these are arrays
        var $answer;
        var $correct;
        var $comment;
        var $weighting;
        var $position;
        // these arrays are used to save temporarily new answers
        // then they are moved into the arrays above or deleted in the event of cancellation
        var $new_answer;
        var $new_correct;
        var $new_comment;
        var $new_weighting;
        var $new_position;
        var $nbrAnswers;
        var $new_nbrAnswers;

        /**
         * constructor of the class
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - question ID that answers belong to
         */
        public function __construct($questionId) {
            $this->questionId = $questionId;
            $this->answer = array();
            $this->correct = array();
            $this->comment = array();
            $this->weighting = array();
            $this->position = array();

            // clears $new_* arrays
            $this->cancel();

            // fills arrays
            $this->read();            
        }        

        /**
         * clears $new_* arrays
         *
         * @author - Olivier Brouckaert
         */
        function cancel() {
            $this->new_answer = array();
            $this->new_correct = array();
            $this->new_comment = array();
            $this->new_weighting = array();
            $this->new_position = array();

            $this->new_nbrAnswers = 0;
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
            $i = 1;
            // foreach record found
            foreach ($result as $object) {
                $this->answer[$i] = $object->answer;
                $this->correct[$i] = $object->correct;
                $this->comment[$i] = $object->comment;
                $this->weighting[$i] = $object->weight;
                $this->position[$i] = $object->r_position;

                $i++;
            }
            $this->nbrAnswers = $i - 1;
        }

        /**
         * returns the number of answers in this question
         *
         * @author - Olivier Brouckaert
         * @return - integer - number of answers
         */
        function selectNbrAnswers() {
            return $this->nbrAnswers;
        }

        /**
         * returns the question ID which the answers belong to
         *
         * @author - Olivier Brouckaert
         * @return - integer - the question ID
         */
        function selectQuestionId() {
            return $this->questionId;
        }

        /**
         * returns the answer title
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - string - answer title
         */
        function selectAnswer($id) {
            if (isset($this->answer[$id])) {
                return $this->answer[$id];
            } else {
                return '::0';
            }
        }
             
        function selectAnswers() {
            return $this->answer;
        }
        /**
         * tells if answer is correct or not
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - integer - 0 if bad answer, not 0 if good answer
         */
        function isCorrect($id) {
            return $this->correct[$id];
        }

        /**
         * returns answer comment
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - string - answer comment
         */
        function selectComment($id) {
            return $this->comment[$id];
        }

        /**
         * returns answer weighting
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - float - answer weighting
         */
        function selectWeighting($id) {
            return $this->weighting[$id];
        }

        /**
         * returns answer position
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - answer ID
         * @return - integer - answer position
         */
        function selectPosition($id) {
            return $this->position[$id];
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
        function createAnswer($answer, $correct, $comment, $weighting, $position) {
            $this->new_nbrAnswers++;

            $id = $this->new_nbrAnswers;

            $this->new_answer[$id] = $answer;
            $this->new_correct[$id] = $correct;
            $this->new_comment[$id] = $comment;
            $this->new_weighting[$id] = $weighting;
            $this->new_position[$id] = $position;
        }

        /**
         * records answers into the data base
         *
         * @author - Olivier Brouckaert
         */
        function save() {

            $questionId = intval($this->questionId);
            // removes old answers before inserting of new ones
            Database::get()->query("DELETE FROM exercise_answer WHERE question_id = ?d", $questionId);
            // inserts new answers into data base
            $sql = "INSERT INTO exercise_answer (question_id, answer, correct, comment, weight, r_position) VALUES ";

            for ($i = 1; $i <= $this->new_nbrAnswers; $i++) {
                  $data_array[] = $questionId;
                  $data_array[] = $this->new_answer[$i];
                  $data_array[] = $this->new_correct[$i];
                  $data_array[] = $this->new_comment[$i];
                  $data_array[] = $this->new_weighting[$i];
                  $data_array[] = $this->new_position[$i];
                $sql .= "(?d, ?s, ?d, ?s, ?f, ?d),";
            }
            $sql = substr($sql, 0, -1);
            Database::get()->query($sql,$data_array);

            // moves $new_* arrays
            $this->answer = $this->new_answer;
            $this->correct = $this->new_correct;
            $this->comment = $this->new_comment;
            $this->weighting = $this->new_weighting;
            $this->position = $this->new_position;

            $this->nbrAnswers = $this->new_nbrAnswers;

            // clears $new_* arrays
            $this->cancel();
        }

        /**
         * duplicates answers by copying them into another question
         *
         * @author - Olivier Brouckaert
         * @param - integer $newQuestionId - ID of the new question
         */
        function duplicate($newQuestionId) {

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

    }

    

    
endif;