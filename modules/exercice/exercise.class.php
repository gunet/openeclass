<?php
// $Id$
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


if(!class_exists('Exercise')):
/*>>>>>>>>>>>>>>>>>>>> CLASS EXERCISE <<<<<<<<<<<<<<<<<<<<*/
/**
 * This class allows to instantiate an object of type Exercise
 *
 * @author - Olivier Brouckaert
 */
class Exercise
{
	var $id;
	var $exercise;
	var $description;
	var $type;
	var $StartDate;
	var $EndDate;	
	var $TimeConstrain;
	var $AttemptsAllowed;
	var $random;
	var $active;
	var $questionList;  // array with the list of this exercise's questions

	/**
	 * constructor of the class
	 *
	 * @author - Olivier Brouckaert
	 */
	function Exercise()
	{
		$this->id=0;
		$this->exercise='';
		$this->description='';
		$this->type=1;
		$this->StartDate=date("Y-m-d H:i");
		$this->EndDate='';
		$this->TimeConstrain=0;
		$this->AttemptsAllowed=0;
		$this->random=0;
		$this->active=1;
		$this->results=1;
		$this->score=1;
		$this->questionList=array();
	}

	/**
	 * reads exercise informations from the data base
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - exercise ID
	 * @return - boolean - true if exercise exists, otherwise false
	 */
	function read($id)
	{
		global $TBL_EXERCICES, $TBL_EXERCICE_QUESTION, $TBL_QUESTIONS, $currentCourseID;
		
                mysql_select_db($currentCourseID);
                
		$sql="SELECT titre, description, type, StartDate, EndDate, TimeConstrain, 
			AttemptsAllowed, random, active, results, score 
			FROM `$TBL_EXERCICES` WHERE id='$id'";
		$result=db_query($sql);
		// if the exercise has been found
		if($object=mysql_fetch_object($result))
		{
			$this->id=$id;
			$this->exercise=$object->titre;
			$this->description=$object->description;
			$this->type=$object->type;
			$this->StartDate=$object->StartDate;
			$this->EndDate=$object->EndDate;
			$this->TimeConstrain=$object->TimeConstrain;
			$this->AttemptsAllowed=$object->AttemptsAllowed;
			$this->random=$object->random;
			$this->active=$object->active;
			$this->results=$object->results;
			$this->score=$object->score;                                                
                                                
			$sql="SELECT question_id,q_position FROM `$TBL_EXERCICE_QUESTION`,`$TBL_QUESTIONS` 
				WHERE question_id=id AND exercice_id='$id' ORDER BY q_position";
			$result=db_query($sql);

			// fills the array with the question ID for this exercise
			// the key of the array is the question position
			while($object=mysql_fetch_object($result)) {
				// makes sure that the question position is unique
				while(isset($this->questionList[$object->q_position]))
				{
					$object->q_position++;
				}
				$this->questionList[$object->q_position]=$object->question_id;
			}
                        // get exercise total weight
                        $this->totalweight = db_query_get_single_value("SELECT SUM(questions.ponderation)
                                        FROM questions, exercice_question
                                        WHERE questions.id = exercice_question.question_id
                                        AND exercice_question.exercice_id = $id");
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
	function selectId()
	{
		return $this->id;
	}

	/**
	 * returns the exercise title
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - exercise title
	 */
	function selectTitle()
	{
		return $this->exercise;
	}
	
	/**
	* set title
	*
	* @author Sebastien Piraux <pir@cerdecam.be>
	* @param string $value
	*/
	function setTitle($value)
	{
	    $this->exercise = trim($value);
	}

	/**
	 * returns the exercise description
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - exercise description
	 */
	function selectDescription()
	{
		return $this->description;
	}

	/**
	* set description
	*
	* @author Sebastien Piraux <pir@cerdecam.be>
	* @param string $value
	*/
	function setDescription($value)
	{
	    $this->description = trim($value);
	}

        
        // return the total weighting of an exercise
        function selectTotalWeighting()
        {       
                return $this->totalweight;                
        }
        
	/**
	 * returns the exercise type
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - exercise type
	 */
	function selectType()
	{
		return $this->type;
	}
	function selectStartDate()
	{
		return $this->StartDate;
	}
	function selectEndDate()
	{
		return $this->EndDate;
	}
	function selectTimeConstrain()
	{
		return $this->TimeConstrain;
	}
	function selectAttemptsAllowed()
	{
		return $this->AttemptsAllowed;
	}
	function selectResults()
	{
		return $this->results;
	}
	function selectScore()
	{
		return $this->score;
	}
	/**
	 * tells if questions are selected randomly, and if so returns the draws
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - 0 if not random, otherwise the draws
	 */
	function isRandom()
	{
		return $this->random;
	}

	/**
	 * returns the exercise status (1 = enabled ; 0 = disabled)
	 *
	 * @author - Olivier Brouckaert
	 * @return - boolean - true if enabled, otherwise false
	 */
	function selectStatus()
	{
		return $this->active;
	}

	/**
	 * returns the array with the question ID list
	 *
	 * @author - Olivier Brouckaert
	 * @return - array - question ID list
	 */
	function selectQuestionList()
	{
		return $this->questionList;
	}

	/**
	 * returns the number of questions in this exercise
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - number of questions
	 */
	function selectNbrQuestions()
	{
		return sizeof($this->questionList);
	}

	/**
     * selects questions randomly in the question list
	 *
	 * @author - Olivier Brouckaert
	 * @return - array - if the exercise is not set to take questions randomly, returns the question list
	 *					 without randomizing, otherwise, returns the list with questions selected randomly
     */
	function selectRandomList()
	{
		// if the exercise is not a random exercise, or if there are not at least 2 questions
		if(!$this->random || $this->selectNbrQuestions() < 2 || $this->random <= 0)
		{
			return $this->questionList;
		}

		// takes all questions
		if($this->random > $this->selectNbrQuestions())
		{
			$draws=$this->selectNbrQuestions();
		}
		else
		{
			$draws=$this->random;
		}

		srand((double)microtime()*1000000);

		$randQuestionList=array();
		$alreadyChosed=array();

		// loop for the number of draws
		for($i=0;$i < $draws;$i++)
		{
			// selects a question randomly
			do
			{
				$rand=rand(0,$this->selectNbrQuestions()-1);
			}
			// if the question has already been selected, continues in the loop
			while(in_array($rand,$alreadyChosed));

			$alreadyChosed[]=$rand;
			$j=0;

			foreach($this->questionList as $key=>$val) {
				// if we have found the question chosed above
				if($j == $rand)
				{
					$randQuestionList[$key]=$val;
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
	function isInList($questionId)
	{
		return in_array($questionId,$this->questionList);
	}

	/**
	 * changes the exercise title
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $title - exercise title
	 */
	function updateTitle($title)
	{
		$this->exercise=$title;
	}

	/**
	 * changes the exercise description
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $description - exercise description
	 */
	function updateDescription($description)
	{
		$this->description=$description;
	}

	/**
	 * changes the exercise type
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $type - exercise type
	 */
	function updateType($type)
	{
		$this->type=$type;
	}
	
	function updateStartDate($StartDate)
	{
		$this->StartDate=$StartDate;
	}
	function updateEndDate($EndDate)
	{
		$this->EndDate=$EndDate;
	}
	function updateTimeConstrain($TimeConstrain)
	{
		$this->TimeConstrain=$TimeConstrain;
	}
	function updateAttemptsAllowed($AttemptsAllowed)
	{
		$this->AttemptsAllowed=$AttemptsAllowed;
	}
	function updateResults($results)
	{
		$this->results=$results;
	}
	function updateScore($score)
	{
		$this->score=$score;
	}
	/**
	 * sets to 0 if questions are not selected randomly
	 * if questions are selected randomly, sets the draws
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $random - 0 if not random, otherwise the draws
	 */
	function setRandom($random)
	{
		$this->random=$random;
	}

	/**
	 * enables the exercise
	 *
	 * @author - Olivier Brouckaert
	 */
	function enable()
	{
		$this->active=1;
	}

	/**
	 * disables the exercise
	 *
	 * @author - Olivier Brouckaert
	 */
	function disable()
	{
		$this->active=0;
	}

	/**
	 * updates the exercise in the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function save()
	{
		global $TBL_EXERCICES, $TBL_QUESTIONS, $currentCourseID;

		$id=$this->id;
		$exercise=addslashes($this->exercise);
		$description = autoquote(purify($this->description));
		$type=$this->type;
		$StartDate=$this->StartDate;
		$EndDate=$this->EndDate;
		$TimeConstrain=$this->TimeConstrain;
		$AttemptsAllowed=$this->AttemptsAllowed;
		$random=$this->random;
		$active=$this->active;
		$results=$this->results;
		$score=$this->score;

		// exercise already exists
		if($id)
		{
			mysql_select_db($currentCourseID);
			$sql = "UPDATE `$TBL_EXERCICES` 
				SET titre = '$exercise', description = $description, type = '$type', ".
				"StartDate = '$StartDate', EndDate = '$EndDate', TimeConstrain = '$TimeConstrain', ".
				"AttemptsAllowed = '$AttemptsAllowed',  random = '$random', 
				active = '$active',  results = '$results',  score = '$score' WHERE id = '$id'";
			db_query($sql) or die("Error : UPDATE in file ".__FILE__." at line ".__LINE__);
		}
		// creates a new exercise
		else
		{
			mysql_select_db($currentCourseID);
			$sql="INSERT INTO `$TBL_EXERCICES`
				VALUES (NULL,  '$exercise', $description, $type, '$StartDate', '$EndDate', 
					$TimeConstrain,  $AttemptsAllowed,  $random,  $active,  $results,  $score)";
			db_query($sql);
			$this->id=mysql_insert_id();
		}
		// updates the question position
		foreach($this->questionList as $position=>$questionId)
		{
			$sql="UPDATE `$TBL_QUESTIONS` SET q_position='$position' WHERE id='$questionId'";
			db_query($sql);
		}
	}

	/**
	* check if data are valide
	*
	* @author Sebastien Piraux <pir@cerdecam.be>
	* @return boolean
	*/
	function validate() {
		
	   $title = strip_tags($this->exercise);
	   if(empty($title)) {
	       $tool_content .= $langExerciseNoTitle;
	       return false;
	   } /*else {
	       if(!is_null($this->EndDate) && $this->EndDate <= $this->StartDate) {
			$tool_content .= $langExerciseWrongDates;
			return false;
	       }
	   }*/
	return true; 
	}

	/**
	 * moves a question up in the list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - question ID to move up
	 */
	function moveUp($id)
	{
		global $currentCourseID;
		
		list($pos) = mysql_fetch_array(db_query("SELECT q_position FROM questions 
							WHERE id='$id'", $currentCourseID));
	
		if ($pos > 1) {
			$temp = $this->questionList[$pos-1];
			$this->questionList[$pos-1] = $this->questionList[$pos];
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
	function moveDown($id)
	{
		global $currentCourseID;
		
		list($pos) = mysql_fetch_array(db_query("SELECT q_position FROM questions 
							WHERE id='$id'", $currentCourseID));
		
		if ($pos < count($this->questionList)) {
			$temp = $this->questionList[$pos+1];
			$this->questionList[$pos+1] = $this->questionList[$pos];
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
	function addToList($questionId)
	{
		// checks if the question ID is not in the list
		if(!$this->isInList($questionId))
		{
			// selects the max position
			if(!$this->selectNbrQuestions())
			{
				$pos=1;
			}
			else
			{
				$pos=max(array_keys($this->questionList))+1;
			}

			$this->questionList[$pos]=$questionId;

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
	function removeFromList($questionId)
	{
		// searches the position of the question ID in the list
		$pos=array_search($questionId,$this->questionList);

		// question not found
		if($pos === false)
		{
			return false;
		}
		else
		{
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
	function delete()
	{
		global $TBL_EXERCICE_QUESTION, $TBL_EXERCICES;

		$id=$this->id;

		$sql="DELETE FROM `$TBL_EXERCICE_QUESTION` WHERE exercice_id='$id'";
		db_query($sql) or die("Error : DELETE in file ".__FILE__." at line ".__LINE__);

		$sql="DELETE FROM `$TBL_EXERCICES` WHERE id='$id'";
		db_query($sql) or die("Error : DELETE in file ".__FILE__." at line ".__LINE__);
	}
}

endif;

