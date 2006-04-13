<?php 
 // $Id$
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

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE ADMINISTRATION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to manage (create, modify) an exercise and its questions
 *
 * Following scripts are includes for a best code understanding :
 *
 * - exercise.class.php : for the creation of an Exercise object
 * - question.class.php : for the creation of a Question object
 * - answer.class.php : for the creation of an Answer object
 *
 * - exercise.lib.php : functions used in the exercise tool
 *
 * - exercise_admin.inc.php : management of the exercise
 * - question_admin.inc.php : management of a question (statement & answers)
 * - statement_admin.inc.php : management of a statement
 * - answer_admin.inc.php : management of answers
 * - question_list_admin.inc.php : management of the question list
 *
 * Main variables used in this script :
 *
 * - $is_allowedToEdit : set to 1 if the user is allowed to manage the exercise
 *
 * - $objExercise : exercise object
 * - $objQuestion : question object
 * - $objAnswer : answer object
 *
 * - $aType : array with answer types
 * - $exerciseId : the exercise ID
 * - $picturePath : the path of question pictures
 *
 * - $newQuestion : ask to create a new question
 * - $modifyQuestion : ID of the question to modify
 * - $editQuestion : ID of the question to edit
 * - $submitQuestion : ask to save question modifications
 * - $cancelQuestion : ask to cancel question modifications
 * - $deleteQuestion : ID of the question to delete
 * - $moveUp : ID of the question to move up
 * - $moveDown : ID of the question to move down
 * - $modifyExercise : ID of the exercise to modify
 * - $submitExercise : ask to save exercise modifications
 * - $cancelExercise : ask to cancel exercise modifications
 * - $modifyAnswers : ID of the question which we want to modify answers for
 * - $cancelAnswers : ask to cancel answer modifications
 * - $buttonBack : ask to go back to the previous page in answers of type "Fill in blanks"
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');

$require_current_course = TRUE;
$langFiles='exercice';
include ('../../include/init.php');

$nameTools = $langExercices;
$navigation[]= array ("url"=>"exercice.php", "name"=> $langExercices);

// answer types
define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);

// allows script inclusions
define('ALLOWED_TO_INCLUDE',1);

$is_allowedToEdit=$is_adminOfCourse;

// picture path
$picturePath='../../courses/'.$currentCourseID.'/image';

// the 4 types of answers
$aType=array($langUniqueSelect,$langMultipleSelect,$langFillBlanks,$langMatching);

// tables used in the exercise tool
$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

if(!$is_allowedToEdit) {
	die($langNotAllowed);
}

/****************************/
/*  stripslashes POST data  */
/****************************/

if($REQUEST_METHOD == 'POST') {
	foreach($_POST as $key=>$val) {
		if(is_string($val)) {
			$_POST[$key]=stripslashes($val);
		}
		elseif(is_array($val))
		{
			foreach($val as $key2=>$val2) {
				$_POST[$key][$key2]=stripslashes($val2);
			}
		}
		$GLOBALS[$key]=$_POST[$key];
	}
}

// intializes the Exercise object
if(!is_object(@$objExercise)) {
	// construction of the Exercise object
	$objExercise=new Exercise();

	// creation of a new exercise if wrong or not specified exercise ID
	if(isset($exerciseId)) {
		$objExercise->read($exerciseId);
	}

	// saves the object into the session
	session_register('objExercise');
}

// doesn't select the exercise ID if we come from the question pool
if(!isset($fromExercise)) {
	// gets the right exercise ID, and if 0 creates a new exercise
	if(!$exerciseId=$objExercise->selectId()) {
		$modifyExercise='yes';
	}
}

$nbrQuestions=$objExercise->selectNbrQuestions();

// intializes the Question object
if(isset($editQuestion) || isset($newQuestion) || isset($modifyQuestion) || isset($modifyAnswers)) {
	if(isset($editQuestion) || isset($newQuestion)) {
		// construction of the Question object
		$objQuestion=new Question();

		// saves the object into the session
		session_register('objQuestion');

		// reads question data
		if(isset($editQuestion)) {
			// question not found
			if(!$objQuestion->read($editQuestion)) {
				die($langQuestionNotFound);
			}
		}
	}

	// checks if the object exists
	if(is_object($objQuestion)) {
		// gets the question ID
		$questionId=$objQuestion->selectId();
	}
	// question not found
	else
	{
		die($langQuestionNotFound);
	}
}

// if cancelling an exercise
if(isset($cancelExercise)) {
	// existing exercise
	if($exerciseId) {
		unset($modifyExercise);
	}
	// new exercise
	else {
		// goes back to the exercise list
		header('Location: exercice.php');
		exit();
	}
}

// if cancelling question creation/modification
if(isset($cancelQuestion)) {
	// if we are creating a new question from the question pool
	if(!$exerciseId && !$questionId) {
		// goes back to the question pool
		header('Location: question_pool.php');
		exit();
	} else {
		// goes back to the question viewing
		$editQuestion=$modifyQuestion;
		unset($newQuestion,$modifyQuestion);
	}
}

// if cancelling answer creation/modification
if(isset($cancelAnswers)) {
	// goes back to the question viewing
	$editQuestion=$modifyAnswers;
	unset($modifyAnswers);
}

// modifies the query string that is used in the link of tool name
if(isset($editQuestion) || isset($modifyQuestion) || isset($newQuestion) || isset($modifyAnswers)) {
	$nameTools=$langQuestionManagement;
	@$QUERY_STRING=$questionId?'editQuestion='.$questionId.'&fromExercise='.$fromExercise:'newQuestion=yes';
} else {
	$nameTools=$langExerciseManagement;
	$QUERY_STRING='';
}


// shows a link to go back to the question pool
if(!$exerciseId && $nameTools != $langExerciseManagement) {
	$navigation[]=@array("url" => "question_pool.php?fromExercise=$fromExercise","name" => $langQuestionPool);
}

// if the question is duplicated, disable the link of tool name
if(isset($modifyIn) && $modifyIn == 'thisExercise') {
	if($buttonBack) {
		$modifyIn='allExercises';
	}
	else
	{
		$noPHP_SELF=true;
	}
}

begin_page();

if(isset($newQuestion) || isset($modifyQuestion)) {
	// statement management
	include('statement_admin.inc.php');
}

if(isset($modifyAnswers)) {
	// answer management
	include('answer_admin.inc.php');
}

if(isset($editQuestion) || isset($usedInSeveralExercises)) {
	// question management
	include('question_admin.inc.php');
}

if(!isset($newQuestion) && !isset($modifyQuestion) && !isset($editQuestion) && !isset($modifyAnswers)) {
	// exercise management
	include('exercise_admin.inc.php');

	if(!isset($modifyExercise))
	{
		// question list management
		include('question_list_admin.inc.php');
	}
}

?>
