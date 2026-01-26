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


if (!class_exists('Answer')):

    /* >>>>>>>>>>>>>>>>>>>> CLASS ANSWER <<<<<<<<<<<<<<<<<<<< */

    /**
     * This class allows instantiating an object of type Answer
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
         * reads answer information from the database
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
         * @brief get answer question id
         * @return mixed
         */
        public function getQuestionId() {
            return $this->questionId;
        }

        /**
         * @brief get answer title
         * @param $id
         * @return mixed|string
         */
        public function getTitle($id) {
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
            if (!isset($this->correct[$id])) {
                $this->correct[$id] = 0;
            }
            return $this->correct[$id];
        }

        /**
         * @brief get comment
         * @param $id
         * @return mixed|string
         */
        public function getComment($id) {
            if (!isset($this->comment[$id])) {
                $this->comment[$id] = '';
            }
            return $this->comment[$id];
        }

        /**
         * @brief get answer weighting
         * @param $id
         * @return float|mixed
         */
        public function getWeighting($id) {
            if (!isset($this->weighting[$id])) {
                $this->weighting[$id] = 0.00;
            }
            return $this->weighting[$id];
        }

        /**
         * @brief get answer position
         * @param $id
         * @return mixed
         */
        public function getPosition($id) {
            if (!isset($this->weighting[$id])) {
                $this->weighting[$id] = 0;
            }
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
         * @brief save answers
         * @return void
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
                // inserts new answers
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
         * @brief get a question type (e.g., multiple choice, matching, etc)
         * @return type
         */
        private function getQuestionType() {
            $question_type = Database::get()->querySingle("SELECT `type` FROM exercise_question WHERE id = ?d", $this->getQuestionId())->type;
            return $question_type;
        }

        /**
         * @brief Retrieve the text that contains all the brackets with their markers.
         * @author - Nikos Mpalamoutis
         */
        public function get_drag_and_drop_text() {

            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $text = '';
            if ($answer) {
                $arr = unserialize($answer->answer);
                if (is_array($arr) && count($arr) > 0) {
                    $text = $arr[0]['pr_text'] ?? '';
                }
            }

            return $text;

        }

        /**
         * @brief Retrieve the answers for each bracket inside the text.
         * @author - Nikos Mpalamoutis
         */
        public function get_drag_and_drop_text_with_answers() {

            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $textWithAnswer = [];
            $predefinedAnswers = [];
            if ($answer) {
                $arr = unserialize($answer->answer);
                if (is_array($arr) && count($arr) > 0) {
                    $predefinedAnswers = unserialize($arr[0]['pr_answers'] ?? '');
                    if (is_array($predefinedAnswers) && count($predefinedAnswers) > 0) {
                        foreach ($predefinedAnswers as $p) {
                            $textWithAnswer[$p['index']+1] = $p['choice_answer'];
                        }
                    }
                }
            }

            return $textWithAnswer;

        }

        /**
         * @brief Retrieve the grades for each bracket inside the text.
         * @author - Nikos Mpalamoutis
         */
        public function get_drag_and_drop_text_with_grades() {

            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $markerWithGrade = [];
            $predefinedAnswers = [];
            if ($answer) {
                $arr = unserialize($answer->answer);
                if (is_array($arr) && count($arr) > 0) {
                    $predefinedAnswers = unserialize($arr[0]['pr_answers'] ?? '');
                    if (is_array($predefinedAnswers) && count($predefinedAnswers) > 0) {
                        foreach ($predefinedAnswers as $p) {
                            $markerWithGrade[$p['index']+1] = $p['choice_grade'];
                        }
                    }
                }
            }

            return $markerWithGrade;

        }

        /**
         * @brief Retrieve the answers for each marker inside the text.
         * @author - Nikos Mpalamoutis
         */
        public function get_drag_and_drop_markers_with_answers() {

            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $markerWithAnswer = [];
            $predefinedAnswers = [];
            if ($answer) {
                $arr = unserialize($answer->answer);
                if (is_array($arr) && count($arr) > 0) {
                    $predefinedAnswers = unserialize($arr[0]['pr_answers'] ?? '');
                    if (is_array($predefinedAnswers) && count($predefinedAnswers) > 0) {
                        foreach ($predefinedAnswers as $p) {
                            $markerWithAnswer[$p['index']] = $p['choice_answer'];
                        }
                    }
                }
            }

            return $markerWithAnswer;

        }

        /**
         * @brief Retrieve the grades for each marker inside the text.
         * @author - Nikos Mpalamoutis
         */
        public function get_drag_and_drop_markers_with_grades() {

            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $predefinedAnswers = [];
            $markerWithGrade = [];
            if ($answer) {
                $arr = unserialize($answer->answer);
                if (is_array($arr) && count($arr) > 0) {
                    $predefinedAnswers = unserialize($arr[0]['pr_answers'] ?? '');
                    if (is_array($predefinedAnswers) && count($predefinedAnswers) > 0) {
                        foreach ($predefinedAnswers as $p) {
                            $markerWithGrade[$p['index']] = $p['choice_grade'];
                        }
                    }
                }
            }

            return $markerWithGrade;
        }

         /**
         * @brief Getting the total number of predefined answers.
         * @author - Nikos Mpalamoutis
         */
        public function get_total_drag_and_drop_answers() {

            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            if ($answer) {
                $q = unserialize($answer->answer);
                if (is_array($q) && count($q) > 0) {
                    $res = unserialize($q[0]['pr_answers'] ?? '');
                    return count($res);
                }
            } else {
                return 2; // minimum answers
            }

        }

         /**
         * @brief Getting the total number of correct predefined answers.
         * @author - Nikos Mpalamoutis
         */
        public function get_total_correct_drag_and_drop_predefined_answers() {

            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $total = 0;
            if ($answer) {
                $q = unserialize($answer->answer);
                if (is_array($q) && count($q) > 0) {
                    foreach ($q as $r) {
                        $arr = unserialize($r['pr_answers']);
                        if (is_array($arr) && count($arr) > 0) {
                            foreach ($arr as $r) {
                                if ($r['choice_grade'] > 0) {
                                    $total++;
                                }
                            }
                        }
                    }
                }
            }

            return $total;

        }

        /**
        *
        * @author - Nikos Mpalamoutis
        */
        public function get_drag_and_drop_answer_text() {

            $finalArray = [];
            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            if ($answer) {
                $q = unserialize($answer->answer);
                if (is_array($q) && count($q) > 0) {
                    $reformattedItems = [];
                    $arr = unserialize($q[0]['pr_answers'] ?? '');
                    if (is_array($arr) && count($arr) > 0) {
                        foreach ($arr as $r) {
                            $reformattedItems[$r['index']] = $r['choice_answer'];
                        }
                    }
                    ksort($reformattedItems);
                    $finalArray = array_values($reformattedItems);
                }
            }

            return $finalArray;

        }

       /**
       *
       * @author - Nikos Mpalamoutis
       */
        public function get_drag_and_drop_answer_grade() {

            $resultArray = [];
            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $predefinedAnswers = [];
            if ($answer) {
                $arr = unserialize($answer->answer);
                if (is_array($arr) && count($arr) > 0) {
                    $predefinedAnswers = unserialize($arr[0]['pr_answers'] ?? '');
                    if (is_array($predefinedAnswers) && count($predefinedAnswers) > 0) {
                        foreach ($predefinedAnswers as $p) {
                            $resultArray[$p['index']] = $p['choice_grade'];
                        }
                    }
                }
            }
            return $resultArray;
        }

        /**
         *
         * @param $questionId
         * @return int|mixed
         * @author - Nikos Mpalamoutis
         */
        function get_total_drag_and_drop_marker_answers($questionId) {
            global $webDir, $course_code;

            $q = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
            $arrMarkers = [];
            if ($q) {
                $arrMarkers = explode('|', $q);
            }
            // minumun 2 answers
            $maxValueMarker = (count($arrMarkers) > 0) ? count($arrMarkers) : 2;
            return $maxValueMarker;
        }

        /**
         *
         * @param $questionId
         * @author - Nikos Mpalamoutis
         */
        function get_drag_and_drop_marker_answer_grade() {

            $resultArray = [];
            $questionId = $this->questionId;
            $answer = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $predefinedAnswers = [];

            if ($answer) {
                $arr = unserialize($answer->answer);
                if (is_array($arr) && count($arr) > 0) {
                    $predefinedAnswers = unserialize($arr[0]['pr_answers']);
                    if (is_array($predefinedAnswers) && count($predefinedAnswers) > 0) {
                        foreach ($predefinedAnswers as $p) {
                            $resultArray[$p['index']] = $p['choice_grade'];
                        }
                    }
                }
            }

            return $resultArray;

        }

        /**
         *
         * @param $questionId
         * @author - Nikos Mpalamoutis
         */
        function get_marker_ids($questionId) {
            global $webDir, $course_code;

            $q = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
            $arrMarkers = [];
            $markerIds = [];
            if ($q) {
                $arrMarkers = explode('|', $q);
                foreach ($arrMarkers as $m) {
                    if ($m) {
                        $markersData = json_decode($m, true);
                        $markerIds[] = $markersData['marker_id'];
                    }
                }
            }

            return $markerIds;
        }

        /**
         *
         * @param $questionId
         * @return string|mixed
         * @author - Nikos Mpalamoutis
         */
        function get_correct_calculated_answer($questionId) {

            $correctAnswer = '';
            $q = Database::get()->queryArray("SELECT answer,correct FROM exercise_answer WHERE question_id = ?d", $questionId);
            foreach ($q as $an) {
                if ($an->correct == 1) {
                    $arrCorrect = unserialize($an->answer);
                    if (count($arrCorrect) > 0) {
                        foreach ($arrCorrect as $r) {
                            $correctAnswer = $r['result'];
                        }
                    }
                }
            }

            return $correctAnswer;
        }

        /**
         *
         * @param $questionId
         * @return int|mixed
         * @author - Nikos Mpalamoutis
         */
        function get_correct_calculated_grade($questionId) {

            $correctAnswerGrade = 0;
            $q = Database::get()->queryArray("SELECT weight,correct FROM exercise_answer WHERE question_id = ?d", $questionId);
            foreach ($q as $an) {
                if ($an->correct == 1) {
                    $correctAnswerGrade = $an->weight;
                }
            }

            return $correctAnswerGrade;
        }

        /**
         * @return int|mixed
         * @author - Nikos Mpalamoutis
         */
        function get_total_calculated_predefined_answers() {

            $questionId = $this->questionId;
            $totalAnswers = Database::get()->queryArray("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);

            return count($totalAnswers);
        }

         /**
         *
         * @param $questionId
         * @param $hasAnswered
         * @return string|mixed
         * @author - Nikos Mpalamoutis
         */
        function get_user_calculated_answer($questionId, $eurid) {

            $userAnswer = Database::get()->querySingle("SELECT answer FROM exercise_answer_record WHERE eurid = ?d AND question_id = ?d", $eurid, $questionId)->answer;
            return $userAnswer ?? '';
        }

        /**
         *
         * @param $questionId
         * @param $hasAnswered
         * @return int|mixed
         * @author - Nikos Mpalamoutis
         */
        function get_user_answer_grade($questionId, $hasAnswered) {

            $grade = 0;
            $predefinedAnswer = Database::get()->queryArray("SELECT answer,weight FROM exercise_answer WHERE question_id = ?d", $questionId);
            foreach ($predefinedAnswer as $an) {
                $tmpArr = unserialize($an->answer);
                if (count($tmpArr) > 0) {
                    foreach ($tmpArr as $r) {
                        if ($hasAnswered == $r['result']) {
                            $grade = $an->weight;
                        }
                    }
                }
            }

            return $grade;
        }

        /**
         *
         * @param $eurid
         * @param $questionId
         * @return int|mixed
         * @author - Nikos Mpalamoutis
         */
        function get_user_grade_for_answered_calculated_question($eurid, $questionId, $answer_id) {
            $grade = Database::get()->querySingle("SELECT weight FROM exercise_answer_record WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d", $eurid, $questionId, $answer_id)->weight;
            $grade = $grade ?? 0;
            return $grade;
        }

        /**
         *
         * @param $questionId
         * @param $expression
         * @return string|mixed
         * @author - Nikos Mpalamoutis
         */
        function replaceItemsBracesWithWildCards($expression, $questionId) {

            $options = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
            if ($options) {
                // Decode JSON to array
                $dataItems = json_decode($options, true);

                // Create a key-value array for items
                $wildCards = [];
                foreach ($dataItems as $item) {
                    $wildCards[$item['item']] = $item['value'];
                }

                foreach ($wildCards as $key => $value) {
                    $expression = str_replace("{" . $key . "}", $value, $expression);
                }

            }

            return $expression;

        }

        /**
        *
        * @author - Nikos Mpalamoutis
        */
        public function get_ordering_answers() {

            $reformattedItems = [];
            $questionId = $this->questionId;
            $answer_temp = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            if ($answer_temp) {
                $arr_answer = unserialize($answer_temp->answer);
                if (is_array($arr_answer) and count($arr_answer) > 0) {
                    foreach ($arr_answer as $item) {
                        $index = $item['index'];
                        $value = $item['value'];
                        $reformattedItems[$index] = $value;
                    }
                }
            }

            return $reformattedItems;
        }

        /**
       *
       * @author - Nikos Mpalamoutis
       */
        public function get_ordering_answer_grade() {

            $resultArray = [];
            $questionId = $this->questionId;
            $answer_temp = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            if ($answer_temp) {
                $arr_answer = unserialize($answer_temp->answer);
                if (is_array($arr_answer) and count($arr_answer) > 0) {
                    foreach ($arr_answer as $item) {
                        $index = $item['index'];
                        $grade = $item['grade'];
                        $resultArray[$index] = $grade;
                    }
                }
            }

            return $resultArray;

        }

        /**
        *
        * @author - Nikos Mpalamoutis
        */
        public function get_total_ordering_answers() {

            $questionId = $this->questionId;
            $arr_answer = [];
            $answer_temp = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            if ($answer_temp) {
                $arr_answer = unserialize($answer_temp->answer);
            }

            return count($arr_answer);

        }

        /**
        *
        * @author - Nikos Mpalamoutis
        */
        public function get_ordering_answers_by_user($qid, $eurid) {
            $answers = Database::get()->queryArray("SELECT answer,answer_id,weight FROM exercise_answer_record WHERE question_id = ?d AND eurid = ?d", $qid, $eurid);
            return $answers;
        }

        /**
         * @brief Getting the total number of correct ordering predefined answers.
         * @author - Nikos Mpalamoutis
         */
        public function get_total_correct_ordering_predefined_answers() {

            $questionId = $this->questionId;
            $answer_temp = Database::get()->querySingle("SELECT answer FROM exercise_answer WHERE question_id = ?d", $questionId);
            $total = 0;
            if ($answer_temp) {
                $arr_answer = unserialize($answer_temp->answer);
                if (count($arr_answer) > 0) {
                    foreach ($arr_answer as $item) {
                        if ($item['grade'] > 0) {
                            $total++;
                        }
                    }
                }
            }
            return $total;
        }

        /**
         * @brief get user certainty answer choice
         * @param $qid
         * @param $eurid
         * @return mixed
         */
        public function get_user_certainty_answer_choice($qid, $eurid) {

            $q = Database::get()->querySingle("SELECT certainty FROM exercise_answer_record WHERE eurid = ?d AND question_id = ?d", $eurid, $qid);
            if ($q) {
                return $q->certainty;
            } else {
                return 0;
            }
        }

    }
endif;
