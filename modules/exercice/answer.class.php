<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

if(!class_exists('Answer')):

		/*>>>>>>>>>>>>>>>>>>>> CLASS ANSWER <<<<<<<<<<<<<<<<<<<<*/

/**
 * This class allows to instantiate an object of type Answer
 *
 * 5 arrays are created to receive the attributes of each answer
 * belonging to a specified question
 *
 * @author - Olivier Brouckaert
 */
class Answer
{
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
	function Answer($questionId)
	{
		$this->questionId=$questionId;
		$this->answer=array();
		$this->correct=array();
		$this->comment=array();
		$this->weighting=array();
		$this->position=array();

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
	function cancel()
	{
		$this->new_answer=array();
		$this->new_correct=array();
		$this->new_comment=array();
		$this->new_weighting=array();
		$this->new_position=array();

		$this->new_nbrAnswers=0;
	}

	/**
	 * reads answer informations from the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function read()
	{
		global $TBL_REPONSES, $currentCourseID;
		
		mysql_select_db($currentCourseID);

		$questionId=$this->questionId;

		$sql="SELECT reponse,correct,comment,ponderation,r_position FROM `$TBL_REPONSES` WHERE question_id='$questionId' ORDER BY r_position";
		$result=mysql_query($sql) or die("Error : SELECT in file ".__FILE__." at line ".__LINE__);

		$i=1;

		// while a record is found
		while($object=mysql_fetch_object($result))
		{
			$this->answer[$i]=$object->reponse;
			$this->correct[$i]=$object->correct;
			$this->comment[$i]=$object->comment;
			$this->weighting[$i]=$object->ponderation;
			$this->position[$i]=$object->r_position;

			$i++;
		}

		$this->nbrAnswers=$i-1;
	}

	/**
	 * returns the number of answers in this question
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - number of answers
	 */
	function selectNbrAnswers()
	{
		return $this->nbrAnswers;
	}

	/**
	 * returns the question ID which the answers belong to
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - the question ID
	 */
	function selectQuestionId()
	{
		return $this->questionId;
	}

	/**
	 * returns the answer title
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - string - answer title
	 */
	function selectAnswer($id)
	{
		return @$this->answer[$id];
	}

	/**
	 * tells if answer is correct or not
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - 0 if bad answer, not 0 if good answer
	 */
	function isCorrect($id)
	{
		return $this->correct[$id];
	}

	/**
	 * returns answer comment
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - string - answer comment
	 */
	function selectComment($id)
	{
		return $this->comment[$id];
	}

	/**
	 * returns answer weighting
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - answer weighting
	 */
	function selectWeighting($id)
	{
		return $this->weighting[$id];
	}

	/**
	 * returns answer position
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - answer position
	 */
	function selectPosition($id)
	{
		return $this->position[$id];
	}

	/**
	 * creates a new answer
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $answer - answer title
	 * @param - integer $correct - 0 if bad answer, not 0 if good answer
	 * @param - string $comment - answer comment
	 * @param - integer $weighting - answer weighting
	 * @param - integer $position - answer position
	 */
	function createAnswer($answer,$correct,$comment,$weighting,$position)
	{
		$this->new_nbrAnswers++;

		$id=$this->new_nbrAnswers;

		$this->new_answer[$id]=$answer;
		$this->new_correct[$id]=$correct;
		$this->new_comment[$id]=$comment;
		$this->new_weighting[$id]=$weighting;
		$this->new_position[$id]=$position;
	}

	/**
	 * records answers into the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function save()
	{
		global $TBL_REPONSES, $currentCourseID;
		
		mysql_select_db($currentCourseID);

		$questionId=$this->questionId;

		// removes old answers before inserting of new ones
		$sql="DELETE FROM `$TBL_REPONSES` WHERE question_id='$questionId'";
		mysql_query($sql) or die("Error : DELETE in file ".__FILE__." at line ".__LINE__);

		// inserts new answers into data base
		$sql="INSERT INTO `$TBL_REPONSES`(id,question_id,reponse,correct,comment,ponderation,r_position) VALUES";

		for($i=1;$i <= $this->new_nbrAnswers;$i++)
		{
			$answer=addslashes($this->new_answer[$i]);
			$correct=$this->new_correct[$i];
			$comment=addslashes($this->new_comment[$i]);
			$weighting=$this->new_weighting[$i];
			$position=$this->new_position[$i];

			$sql.="('$i','$questionId','$answer','$correct','$comment','$weighting','$position'),";
		}

		$sql=substr($sql,0,-1);
		mysql_query($sql) or die("Error : INSERT in file ".__FILE__." at line ".__LINE__);

		// moves $new_* arrays
		$this->answer=$this->new_answer;
		$this->correct=$this->new_correct;
		$this->comment=$this->new_comment;
		$this->weighting=$this->new_weighting;
		$this->position=$this->new_position;

		$this->nbrAnswers=$this->new_nbrAnswers;

		// clears $new_* arrays
		$this->cancel();
	}

	/**
	 * duplicates answers by copying them into another question
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $newQuestionId - ID of the new question
	 */
	function duplicate($newQuestionId)
	{
		global $TBL_REPONSES;

		// if at least one answer
		if($this->nbrAnswers)
		{
			// inserts new answers into data base
			$sql="INSERT INTO `$TBL_REPONSES`(id,question_id,reponse,correct,comment,ponderation,r_position) VALUES";

			for($i=1;$i <= $this->nbrAnswers;$i++)
			{
				$answer=addslashes($this->answer[$i]);
				$correct=$this->correct[$i];
				$comment=addslashes($this->comment[$i]);
				$weighting=$this->weighting[$i];
				$position=$this->position[$i];

				$sql.="('$i','$newQuestionId','$answer','$correct','$comment','$weighting','$position'),";
			}

			$sql=substr($sql,0,-1);
			mysql_query($sql) or die("Error : INSERT in file ".__FILE__." at line ".__LINE__);
		}
	}
}

endif;
?>
