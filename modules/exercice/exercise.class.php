<?php // $Id$
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	exercise.class.php
	@last update: 01-06-2006 by Dionysios G. Synodinos
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the work tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights

 	The user can : - navigate through files and directories.
                       - upload a file
                       - delete, copy a file or a directory
                       - edit properties & content (name, comments, 
			 html content)

 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
   	2) Define the directory to display
  	3) Read files and directories from the directory defined in part 2
  	4) Display all of that on an HTML page
 
  	@TODO: eliminate code duplication between document/document.php, scormdocument.php
==============================================================================
*/
//include '../../include/baseTheme.php';

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
	var $StartDate; 				// added
	var $EndDate;						//	
	var $TimeConstrain;			//
	var $AttemptsAllowed; 	// end of added
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
		$this->StartDate=date("Y-m-d H:i:s");
		$this->EndDate='';
		$this->TimeConstrain=0;
		$this->AttemptsAllowed=0;
		$this->random=0;
		$this->active=1;

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
		$sql="SELECT titre,description,type,StartDate,EndDate,TimeConstrain,AttemptsAllowed,random,active FROM `$TBL_EXERCICES` WHERE id='$id'";
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

			$sql="SELECT question_id,q_position FROM `$TBL_EXERCICE_QUESTION`,`$TBL_QUESTIONS` WHERE question_id=id AND exercice_id='$id' ORDER BY q_position";
			$result=db_query($sql);

			// fills the array with the question ID for this exercise
			// the key of the array is the question position
			while($object=mysql_fetch_object($result))
			{
				// makes sure that the question position is unique
				while(isset($this->questionList[$object->q_position]))
				{
					$object->q_position++;
				}

				$this->questionList[$object->q_position]=$object->question_id;
			}

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
		if(!$this->random || $this->selectNbrQuestions() < 2)
		{
			return $this->questionList;
		}

		// takes all questions
		if($this->random == -1 || $this->random > $this->selectNbrQuestions())
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

			foreach($this->questionList as $key=>$val)
			{
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
		$description=addslashes($this->description);
		$type=$this->type;
		$StartDate=$this->StartDate; 							// added
		$EndDate=$this->EndDate;									//	
		$TimeConstrain=$this->TimeConstrain;			//
		$AttemptsAllowed=$this->AttemptsAllowed; 	// end of added
		$random=$this->random;
		$active=$this->active;

		// exercise already exists
		if($id)
		{
			mysql_select_db($currentCourseID);
			$sql="UPDATE `$TBL_EXERCICES` SET titre='$exercise',description='$description',type='$type',".
				"StartDate='$StartDate',EndDate='$EndDate',TimeConstrain='$TimeConstrain',".
				"AttemptsAllowed='$AttemptsAllowed',random='$random',active='$active' WHERE id='$id'";
			mysql_query($sql) or die("Error : UPDATE in file ".__FILE__." at line ".__LINE__);
		}
		// creates a new exercise
		else
		{
			mysql_select_db($currentCourseID);

			$sql="INSERT INTO `$TBL_EXERCICES` VALUES(".
				"0,'$exercise','$description',$type,'$StartDate','$EndDate',$TimeConstrain,$AttemptsAllowed,".
				"$random,$active)";
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
	 * moves a question up in the list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - question ID to move up
	 */
	function moveUp($id)
	{
		foreach($this->questionList as $position=>$questionId)
		{
			// if question ID found
			if($questionId == $id)
			{
				// position of question in the array
				$pos1=$position;

				prev($this->questionList);

				// position of previous question in the array
				$pos2=key($this->questionList);

				// error, can't move question
				if(!$pos2)
				{
					return;
				}

				$id2=$this->questionList[$pos2];

				// exits foreach()
				break;
			}

			// goes to next question
			next($this->questionList);
		}

		// permutes questions in the array
		$temp=$this->questionList[$pos2];
		$this->questionList[$pos2]=$this->questionList[$pos1];
		$this->questionList[$pos1]=$temp;
	}

	/**
	 * moves a question down in the list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - question ID to move down
	 */
	function moveDown($id)
	{
		foreach($this->questionList as $position=>$questionId)
		{
			// if question ID found
			if($questionId == $id)
			{
				// position of question in the array
				$pos1=$position;

				next($this->questionList);

				// position of next question in the array
				$pos2=key($this->questionList);

				// error, can't move question
				if(!$pos2)
				{
					return;
				}

				$id2=$this->questionList[$pos2];

				// exits foreach()
				break;
			}

			// goes to next question
			next($this->questionList);
		}

		// permutes questions in the array
		$temp=$this->questionList[$pos2];
		$this->questionList[$pos2]=$this->questionList[$pos1];
		$this->questionList[$pos1]=$temp;
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
		mysql_query($sql) or die("Error : DELETE in file ".__FILE__." at line ".__LINE__);

		$sql="DELETE FROM `$TBL_EXERCICES` WHERE id='$id'";
		mysql_query($sql) or die("Error : DELETE in file ".__FILE__." at line ".__LINE__);
	}
}

endif;


?>
