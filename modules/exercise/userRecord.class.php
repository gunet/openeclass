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

    require_once 'question.class.php';
    require_once 'answer.class.php';
    class userRecord {

        public $id;
        public $eid;
        public $uid;
        public $record_start_date;
        public $record_end_date;
        public $total_score;
        public $total_weighting;
        public $questionsList;


        /**
         * constructor of the class
         *
         * @author - Olivier Brouckaert
         */
        public function __construct() {
            $this->id = 0;
            $this->eid = 0;
            $this->uid = 0;
            $this->record_start_date = (new DateTime('NOW'))->format('Y-m-d H:i:s');
            $this->record_end_date = null;
            $this->total_score = 0;
            $this->total_weighting = 0;
            $this->questionsList = [];
        }

        /**
         * reads question informations from the data base
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - question ID
         * @return - boolean - true if question exists, otherwise false
         */
        function find($eurid) {

            $eur = Database::get()->querySingle("SELECT *
                        FROM `exercise_user_record` 
                        WHERE eurid = ?d", $eurid);
            // if the question has been found
            if ($eur) {
                $this->id = $eur->eurid;
                $this->eid = $eur->eid;
                $this->uid = $eur->uid;
                $this->record_start_date = DateTime::createFromFormat('Y-m-d H:i:s', $eur->record_start_date);
                $this->record_end_date = $eur->record_end_date ? DateTime::createFromFormat('Y-m-d H:i:s', $eur->record_end_date) : DateTime::createFromFormat('Y-m-d H:i:s', $eur->record_start_date);           
                $this->attempt_status  = $eur->attempt_status;
                $this->total_score = $eur->total_score;
                $this->total_weighting = $eur->total_weighting;
                $this->secs_remaining = $eur->secs_remaining;
                $this->exercise = $this->findExercise();
                $start_date_obj = $this->record_start_date;
                $this->time_duration = $this->exercise->timeConstraint 
                        ? $this->exercise->timeConstraint * 60 - $this->secs_remaining
                        : $this->record_end_date->getTimestamp() - $this->record_start_date->getTimestamp();
                $this->max_attempt_end_date = $this->exercise->selectTimeConstraint() 
                        ? $start_date_obj->add(new DateInterval('PT' . $this->exercise->selectTimeConstraint() . 'M'))
                        : $start_date_obj->add(new DateInterval('P1D'));
                $this->answers_cnt = Database::get()->querySingle("SELECT count(*) AS answers_cnt FROM `exercise_answer_record` WHERE `eurid` = ?d", $this->id)->answers_cnt;
                return true;
            }

            // question not found
            return false;
        }
    
        function questions() {
            $questionsList = [];           
            $question_ids = [];
            // The only way to get the exact list of questions the moment the exercise was run is though exercise_answer_record
            Database::get()->queryFunc("SELECT DISTINCT question_id "
                    . "FROM exercise_answer_record "
                    . "WHERE eurid = ?d ORDER BY question_id", 
                    function($exercise_answer_record) use (&$question_ids) {
                        array_push($question_ids, $exercise_answer_record->question_id);
                    }, $this->id);
            foreach ($question_ids as $question_id) {
                $question = new Question();
                if ($question->read($question_id))  {
                    $question->user_score = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE question_id = ?d AND eurid =?d", $question_id, $this->id)->weight;
                    $question->user_choice = $question->get_answers_record_new($this->id);
                    $question->is_deleted = false;
                    $question->answers = new Answer($question->id);
                } else {
                    $question->user_score = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE question_id = ?d AND eurid =?d", $question_id, $this->id)->weight;
                    $question->is_deleted = true;
                }
                array_push($questionsList, $question);
            }

            return $questionsList;
        }
        
        function findExercise() {
            $exercise_obj = new Exercise();
            if($exercise_obj->read($this->eid)) {
                return $exercise_obj;
            }
            return false;
        }        
    }
