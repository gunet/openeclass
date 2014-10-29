<?php

// $Id$
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


if (!class_exists('Question')):

    /* >>>>>>>>>>>>>>>>>>>> CLASS QUESTION <<<<<<<<<<<<<<<<<<<< */

    /**
     * This class allows to instantiate an object of type Question
     */
    class Question {

        var $id;
        var $question;
        var $description;
        var $weighting;
        var $position;
        var $type;
        var $difficulty;
        var $category;
        var $exerciseList;  // array with the list of exercises which this question is in

        /**
         * constructor of the class
         *
         * @author - Olivier Brouckaert
         */

        function Question() {
            $this->id = 0;
            $this->question = '';
            $this->description = '';
            $this->weighting = 0;
            $this->position = 1;
            $this->type = 1;
            $this->difficulty = 0;
            $this->category = 0;
            $this->exerciseList = array();
        }

        /**
         * reads question informations from the data base
         *
         * @author - Olivier Brouckaert
         * @param - integer $id - question ID
         * @return - boolean - true if question exists, otherwise false
         */
        function read($id) {
            global $course_id;

            $object = Database::get()->querySingle("SELECT question, description, weight, q_position, type, difficulty, category 
                        FROM `exercise_question` WHERE course_id = ?d AND id = ?d", $course_id, $id);
            // if the question has been found
            if ($object) {
                $this->id = $id;
                $this->question = $object->question;
                $this->description = $object->description;
                $this->weighting = $object->weight;
                $this->position = $object->q_position;
                $this->type = $object->type;
                $this->difficulty = $object->difficulty;
                $this->category = $object->category;

                $result = Database::get()->queryArray("SELECT exercise_id FROM `exercise_with_questions` WHERE question_id = ?d", $id);
                // fills the array with the exercises which this question is in
                foreach ($result as $row) {
                    $this->exerciseList[] = $row->exercise_id;
                }

                return true;
            }

            // question not found
            return false;
        }

        /**
         * returns the question ID
         *
         * @author - Olivier Brouckaert
         * @return - integer - question ID
         */
        function selectId() {
            return $this->id;
        }

        /**
         * returns the question title
         *
         * @author - Olivier Brouckaert
         * @return - string - question title
         */
        function selectTitle() {
            return $this->question;
        }

        /**
         * returns the question description
         *
         * @author - Olivier Brouckaert
         * @return - string - question description
         */
        function selectDescription() {
            return $this->description;
        }

        /**
         * returns the question weighting
         *
         * @author - Olivier Brouckaert
         * @return - float - question weighting
         */
        function selectWeighting() {
            return $this->weighting;
        }

        /**
         * returns the question position
         *
         * @author - Olivier Brouckaert
         * @return - integer - question position
         */
        function selectPosition() {
            return $this->position;
        }

        /**
         * returns the answer type
         *
         * @author - Olivier Brouckaert
         * @return - integer - answer type
         */
        function selectType() {
            return $this->type;
        }      
        /**
         * returns the question difficulty
         */
        function selectDifficulty() {
            return $this->difficulty;
        }
        /**
         * returns the question category
         */
        function selectCategory() {
            return $this->category;
        }            
        /**
         * returns the relative verbal answer type
         */
        function selectTypeWord($answerTypeId) {
            global $langUniqueSelect, $langMultipleSelect, $langFillBlanks, 
                   $langMatching, $langTrueFalse, $langFreeText;
            switch ($answerTypeId) {
                case UNIQUE_ANSWER:
                    return $langUniqueSelect;
                case MULTIPLE_ANSWER:
                    return $langMultipleSelect;
                case FILL_IN_BLANKS:
                    return $langFillBlanks;
                case MATCHING:
                    return $langMatching;
                case TRUE_FALSE:    
                    return $langTrueFalse;
                case FREE_TEXT:    
                    return $langFreeText;
            }            
        }
        /**
         * returns the array with the exercise ID list
         *
         * @author - Olivier Brouckaert
         * @return - array - list of exercise ID which the question is in
         */
        function selectExerciseList() {
            return $this->exerciseList;
        }

        /**
         * returns the number of exercises which this question is in
         *
         * @author - Olivier Brouckaert
         * @return - integer - number of exercises
         */
        function selectNbrExercises() {
            if (is_array($this->exerciseList)) {
                return sizeof($this->exerciseList);
            }
        }

        /**
         * changes the question title
         *
         * @author - Olivier Brouckaert
         * @param - string $title - question title
         */
        function updateTitle($title) {
            $this->question = $title;
        }
        /**
         * changes the question description
         *
         * @author - Olivier Brouckaert
         * @param - string $description - question description
         */
        function updateDescription($description) {
            $this->description = $description;
        }
        /**
         * changes the question weighting
         *
         * @author - Olivier Brouckaert
         * @param - float $weighting - question weighting
         */
        function updateWeighting($weighting) {
            $this->weighting = $weighting;
        }

        /**
         * changes the question position
         *
         * @author - Olivier Brouckaert
         * @param - integer $position - question position
         */
        function updatePosition($position) {
            $this->position = $position;
        }

        /**
         * changes the answer type. If the user changes the type from "unique answer" to "multiple answers"
         * (or conversely) answers are not deleted, otherwise yes
         *
         * @author - Olivier Brouckaert
         * @param - integer $type - answer type
         */
        function updateType($type) {
            // if we really change the type
            if ($type != $this->type) {
                // if we don't change from "unique answer" to "multiple answers" (or conversely)
                if (!in_array($this->type, array(UNIQUE_ANSWER, MULTIPLE_ANSWER)) || !in_array($type, array(UNIQUE_ANSWER, MULTIPLE_ANSWER))) {
                    // removes old answers
                    Database::get()->query("DELETE FROM `exercise_answer` WHERE question_id = ?d", $this->id);
                }

                $this->type = $type;
            }
        }
        
        /**
         * changes the question difficulty
         */
        function updateDifficulty($difficulty) {
            $this->difficulty = $difficulty;
        }
        /**
         * changes the question category
         */
        function updateCategory($category_id) {
            $this->category = $category_id;
        }        
        /**
         * adds a picture to the question
         *
         * @author - Olivier Brouckaert
         * @param - string $Picture - temporary path of the picture to upload
         * @return - boolean - true if uploaded, otherwise false
         */
        function uploadPicture($picture, $type) {
            global $picturePath;

            if ($this->id) {
                $filename_final = $picturePath . '/quiz-' . $this->id;
                if (!copy_resized_image($picture, $type, 760, 512, $filename_final)) {
                    return false;
                } else {
                    return true;
                }
            }
            return false;
        }

        /**
         * deletes the picture
         *
         * @author - Olivier Brouckaert
         * @return - boolean - true if removed, otherwise false
         */
        function removePicture() {
            global $picturePath;

            // if the question has got an ID and if the picture exists
            if ($this->id && file_exists($picturePath . '/quiz-' . $this->id)) {
                return unlink($picturePath . '/quiz-' . $this->id) ? true : false;
            }

            return false;
        }

        /**
         * imports a picture from another question
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - ID of the original question
         * @return - boolean - true if copied, otherwise false
         */
        function importPicture($questionId) {
            global $picturePath;

            // if the question has got an ID and if the picture exists
            if ($this->id && file_exists($picturePath . '/quiz-' . $questionId)) {
                return @copy($picturePath . '/quiz-' . $questionId, $picturePath . '/quiz-' . $this->id) ? true : false;
            }

            return false;
        }

        /**
         * exports a picture to another question
         *
         * @author - Olivier Brouckaert
         * @param - integer $questionId - ID of the target question
         * @return - boolean - true if copied, otherwise false
         */
        function exportPicture($questionId) {
            global $picturePath;

            // if the question has got an ID and if the picture exists
            if ($this->id && file_exists($picturePath . '/quiz-' . $this->id)) {
                return @copy($picturePath . '/quiz-' . $this->id, $picturePath . '/quiz-' . $questionId) ? true : false;
            }

            return false;
        }

        /**
         * updates the question in the data base
         * if an exercise ID is provided, we add that exercise ID into the exercise list
         *
         * @author - Olivier Brouckaert
         * @param - integer $exerciseId - exercise ID if saving in an exercise
         */
        function save($exerciseId = 0) {
            global $course_id;

            $id = $this->id;
            $question = $this->question;
            $description = $this->description;
            $weighting = $this->weighting;
            $position = $this->position;
            $type = $this->type;
            $difficulty = $this->difficulty;
            $category = $this->category;

            // question already exists
            if ($id) {
                Database::get()->query("UPDATE `exercise_question` SET question = ?s, description = ?s,
					weight = ?f, q_position = ?d, type = ?d, difficulty = ?d, category = ?d
					WHERE course_id = $course_id AND id='$id'", $question, $description, $weighting, $position, $type, $difficulty, $category);
            }
            // creates a new question
            else {
                $this->id = Database::get()->query("INSERT INTO `exercise_question` (course_id, question, description, weight, q_position, type, difficulty, category)
				VALUES (?d, ?s, ?s, ?f, ?d, ?d, ?d, ?d)", $course_id, $question, $description, $weighting, $position, $type, $difficulty, $category)->lastInsertID;
            }

            // if the question is created in an exercise
            if ($exerciseId) {
                // adds the exercise into the exercise list of this question
                $this->addToList($exerciseId);
            }
        }

        /**
         * adds an exercise into the exercise list
         *
         * @author - Olivier Brouckaert
         * @param - integer $exerciseId - exercise ID
         */
        function addToList($exerciseId) {
            $id = $this->id;

            // checks if the exercise ID is not in the list
            if (!in_array($exerciseId, $this->exerciseList)) {
                $this->exerciseList[] = $exerciseId;
                Database::get()->query("INSERT INTO `exercise_with_questions` (question_id, exercise_id) VALUES (?d, ?d)", $id, $exerciseId);
            }
        }

        /**
         * removes an exercise from the exercise list
         *
         * @author - Olivier Brouckaert
         * @param - integer $exerciseId - exercise ID
         * @return - boolean - true if removed, otherwise false
         */
        function removeFromList($exerciseId) {
            $id = $this->id;

            // searches the position of the exercise ID in the list
            $pos = array_search($exerciseId, $this->exerciseList);

            // exercise not found
            if ($pos === false) {
                return false;
            } else {
                // deletes the position in the array containing the wanted exercise ID
                unset($this->exerciseList[$pos]);
                Database::get()->query("DELETE FROM `exercise_with_questions` WHERE question_id = ?d AND exercise_id = ?d", $id, $exerciseId);
                return true;
            }
        }

        /**
         * deletes a question from the database
         * the parameter tells if the question is removed from all exercises (value = 0),
         * or just from one exercise (value = exercise ID)
         *
         * @author - Olivier Brouckaert
         * @param - integer $deleteFromEx - exercise ID if the question is only removed from one exercise
         */
        function delete($deleteFromEx = 0) {
            global $course_id;

            $id = $this->id;

            // if the question must be removed from all exercises
            //if($deleteFromEx === 0)
            if (!$deleteFromEx) {
                Database::get()->query("DELETE FROM `exercise_with_questions` WHERE question_id = ?d", $id);
                Database::get()->query("DELETE FROM `exercise_question` WHERE course_id = ?d AND id = ?d", $course_id, $id);
                Database::get()->query("DELETE FROM `exercise_answer` WHERE question_id = ?d", $id);
                $this->removePicture();
                // resets the object
                $this->Question();
            }
            // just removes the exercise from the list
            else {
                $this->removeFromList($deleteFromEx);
            }
        }
        /**
         * Getting exercise answers
         */
        function get_answers_record($eurid) {
            $type = $this->type;
            $question_id = $this->id;
            $answers = Database::get()->queryArray("SELECT * FROM exercise_answer_record WHERE eurid = ?d AND question_id = ?d", $eurid, $question_id);    
            $i = 1;
            foreach ($answers as $row) {
                if ($type == 1 || $type == 5) {
                    $choice = $row->answer_id;
                } elseif ($type == 2) {
                    $choice[$row->answer_id] = 1;
                } elseif ($type == 6) {
                    $choice = $row->answer;
                } elseif ($type == 3) {
                    $choice[$row->answer_id] = $row->answer;
                } elseif ($type == 4) {
                    $choice[$row->answer] = $row->answer_id;
                }
               
                $i++;
            }
            return $choice;
        }
        /**
         * duplicates the question
         *
         * @author - Olivier Brouckaert
         * @return - integer - ID of the new question
         */
        function duplicate() {
            global $course_id;

            $question = $this->question;
            $description = $this->description;
            $weighting = $this->weighting;
            $position = $this->position;
            $type = $this->type;
            $difficulty = $this->difficulty;
            $category = $this->category;

            $id = Database::get()->query("INSERT INTO `exercise_question` (course_id, question, description, weight, q_position, type, difficulty, category)
						VALUES (?d, ?s, ?s, ?f, ?d, ?d, ?d, ?d)", $course_id, $question, $description, $weighting, $position, $type, $difficulty, $category)->lastInsertID;

            // duplicates the picture
            $this->exportPicture($id);

            return $id;
        }

    }

    

    
endif;