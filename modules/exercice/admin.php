<?php 
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
	work.php
	@last update: 17-4-2006 by Costas Tsibanis
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

error_reporting('E_ALL ^ E_NOTICE');
include('exercise.class.php');
include('question.class.php');
include('answer.class.php');

include('exercise.lib.php');

$require_current_course = TRUE;
$langFiles='exercice';

include '../../include/baseTheme.php';

// For using with th epop-up calendar
include 'jscalendar.inc.php';

$local_head .= "

<script language=\"JavaScript\">
function validate() {
	if (document.forms[0].intitule.value==\"\") {
   		alert(\"Παρακαλώ συμπληρώστε τον τίτλο του μαθήματος!\"); 
   		return false;
 	}
 	if (document.forms[0].titulaires.value==\"\") {
   		alert(\"Παρακαλώ συμπληρώστε τουλάχιστον έναν διαχειριστή για το μάθημα!\"); 
   		return false;
 	}
 	return true;
}
</script>

";

$tool_content = "";

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
if(!is_object($objExercise)) {
	// construction of the Exercise object
	$objExercise=new Exercise();

	// creation of a new exercise if wrong or not specified exercise ID
	if($exerciseId) {
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
if($editQuestion || $newQuestion || $modifyQuestion || $modifyAnswers) {

if($editQuestion || $newQuestion) {

		// construction of the Question object
		$objQuestion=new Question();

		// saves the object into the session
		session_register('objQuestion');

		// reads question data
		if($editQuestion) {
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
if($cancelExercise) {
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
if($cancelQuestion) {
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
if($cancelAnswers) {
	// goes back to the question viewing
	$editQuestion=$modifyAnswers;
	unset($modifyAnswers);
}

// modifies the query string that is used in the link of tool name
if($editQuestion || $modifyQuestion || $newQuestion || $modifyAnswers) {
	$nameTools=$langQuestionManagement;
	$QUERY_STRING=$questionId?'editQuestion='.$questionId.'&fromExercise='.$fromExercise:'newQuestion=yes';
} else {
	$nameTools=$langExerciseManagement;
	$QUERY_STRING='';
}


// shows a link to go back to the question pool
//if(((!$exerciseId)||(!isset($exerciseId))) && $nameTools != $langExerciseManagement) {
//	//$navigation[]=@array("url" => "question_pool.php?fromExercise=$fromExercise","name" => $langQuestionPool);
//	//$tool_content .= "<a href=\"question_pool.php?fromExercise=$fromExercise\">".$langQuestionPool."</a>";
//}

// if the question is duplicated, disable the link of tool name
if($modifyIn == 'thisExercise') {
	if ($buttonBack) {
		$modifyIn='allExercises';
	}
	else
	{
		$noPHP_SELF=true;
	}
}

if($newQuestion || $modifyQuestion) {

	// statement management
	include('statement_admin.inc.php');
}

if($modifyAnswers) {
	// answer management
	include('answer_admin.inc.php');
}

if($editQuestion || $usedInSeveralExercises) {
	// question management
	include('question_admin.inc.php');
}

if(!$newQuestion && !$modifyQuestion && !$editQuestion && !$modifyAnswers) {
	// exercise management
	include('exercise_admin.inc.php');

	if(!$modifyExercise)
	{
		// question list management
		include('question_list_admin.inc.php');
	}
}

draw($tool_content, 2, '', $local_head, '');
?>
