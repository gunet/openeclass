<?php 
 // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                            |
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

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE SUBMISSION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script allows to run an exercise. According to the exercise type, questions
 * can be on an unique page, or one per page with a Next button.
 *
 * One exercise may contain different types of answers (unique or multiple selection,
 * matching and fill in blanks).
 *
 * Questions are selected randomly or not.
 *
 * When the user has answered all questions and clicks on the button "Ok",
 * it goes to exercise_result.php
 *
 * Notice : This script is also used to show a question before modifying it by
 * the administrator
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');
 
// answer types
define('UNIQUE_ANSWER',1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS',3);
define('MATCHING',4);

$require_current_course = TRUE;
$langFiles='exercice';
$require_help = TRUE;
$helpTopic = 'Exercise';

include('../../include/init.php');

$nameTools = $langExercice;

include('../../include/lib/textLib.inc.php');

$picturePath='../../'.$currentCourseID.'/image';

$is_allowedToEdit=$is_adminOfCourse;
$dbNameGlu=$currentCourseID;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

$navigation[]=array("url" => "exercice.php","name" => $langExercices);
begin_page($nameTools);

// if the object is not in the session
if(!session_is_registered('objExercise')) {
	// construction of Exercise
	$objExercise=new Exercise();

	// if the specified exercise doesn't exist or is disabled
	//if(!$objExercise->read($exerciseId) || (!$objExercise->selectStatus() && !$is_allowedToEdit))
	if(!$objExercise->read($exerciseId) && (!$is_allowedToEdit))
		{
		die($langExerciseNotFound);
	}

	// saves the object into the session
	session_register('objExercise');
}

$exerciseTitle=$objExercise->selectTitle();
//$exerciseDescription=$objExercise->selectDescription();
//$randomQuestions=$objExercise->isRandom();
//$exerciseType=$objExercise->selectType();

?>

<h3><?= $exerciseTitle; ?></h3>

<p><?= $langExerciseExpired ?><a href="exercice.php"><?= $langExerciseLis ?></a></p>

