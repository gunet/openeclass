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
	exercice_submit.php
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

//include('../../include/init.php');

include '../../include/baseTheme.php';

$tool_content = "";

$nameTools = $langExercice;

include('../../include/lib/textLib.inc.php');

$picturePath='../../'.$currentCourseID.'/image';

$is_allowedToEdit=$is_adminOfCourse;
$dbNameGlu=$currentCourseID;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

// if the user has clicked on the "Cancel" button
if(isset($buttonCancel)) {
	// returns to the exercise list
	header('Location: exercice.php');
	exit();
}



// if the user has submitted the form
if(isset($formSent)) {
	// initializing
	if(!is_array(@$exerciseResult)) {
		$exerciseResult=array();
	}

	// if the user has answered at least one question
	if(@is_array($choice))
	{
		if($exerciseType == 1)
		{
			// $exerciseResult receives the content of the form.
			// Each choice of the student is stored into the array $choice
			$exerciseResult=$choice;
		}
		else
		{
			// gets the question ID from $choice. It is the key of the array
			list($key)=array_keys($choice);

			// if the user didn't already answer this question
			if(!isset($exerciseResult[$key]))
			{
				// stores the user answer into the array
				$exerciseResult[$key]=$choice[$key];
			}
		}
	}

	// the script "exercise_result.php" will take the variable $exerciseResult from the session
	session_register('exerciseResult');

	// if it is the last question (only for a sequential exercise)
	if($exerciseType == 1 || $questionNum >= $nbrQuestions) {
		// goes to the script that will show the result of the exercise
		header('Location: exercise_result.php');
		exit();
	}
}

$navigation[]=array("url" => "exercice.php","name" => $langExercices);
//begin_page($nameTools);

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
$exerciseDescription=$objExercise->selectDescription();
$randomQuestions=$objExercise->isRandom();
$exerciseType=$objExercise->selectType();
$exerciseTimeConstrain=$objExercise->selectTimeConstrain();
$exerciseAllowedAttemtps=$objExercise->selectAttemptsAllowed();

$eid_temp = $objExercise->selectId();
$exerciseTimeConstrainSecs = time() + ($exerciseTimeConstrain*60);
$RecordStartDate = date("Y-m-d H:i:s",time());


if ((!$is_adminOfCourse)&&(isset($uid))) { //if registered student
$CurrentAttempt = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record WHERE eid='$eid_temp' AND uid='$uid'", $currentCourseID));
++$CurrentAttempt[0];
	if (!isset($_COOKIE['marvelous_cookie'])) { // either expired or begin again
		if ((!$exerciseAllowedAttemtps)||($CurrentAttempt[0] <= $exerciseAllowedAttemtps)) { // if it is allowed begin again
			if (!$exerciseTimeConstrainSecs)
				$exerciseTimeConstrainSecs = 9999999;
				$CookieLife=time()+$exerciseTimeConstrainSecs;
			if (!setcookie("marvelous_cookie", $eid_temp, $CookieLife, "/")) {
				header('Location: exercise_redirect.php');
				exit();
			}
			// record start of exercise
			mysql_select_db($currentCourseID);
			$sql="INSERT INTO `exercise_user_record` (eurid,eid,uid,RecordStartDate,RecordEndDate,".
				"TotalScore,TotalWeighting,attempt) VALUES".
				"(0,'$eid_temp','$uid','$RecordStartDate','','','',1)";
			$result=mysql_query($sql) or die("Error : SELECT in file ".__FILE__." at line ".__LINE__);		
		} else {  // not allowed begin again
			header('Location: exercise_redirect.php');
			exit();
		}
	}
}

if(!session_is_registered('questionList')) {
	// selects the list of question ID
	$questionList=$randomQuestions?$objExercise->selectRandomList():$objExercise->selectQuestionList();

	// saves the question list into the session
	session_register('questionList');
}

$nbrQuestions=sizeof($questionList);

// if questionNum comes from POST and not from GET
if(!isset($questionNum) || $_POST['questionNum']) {
	// only used for sequential exercises (see $exerciseType)
	if(!isset($questionNum)) {
		$questionNum=1;
	} else {
		$questionNum++;
	}
}

if(@$_POST['questionNum']) {
	$QUERY_STRING="questionNum=$questionNum";
}

	$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
	$tool_content .= <<<cData
		<h3>${exerciseTitle}</h3>

		<p>${exerciseDescription_temp}</p>
		
		<table width="100%" border="0" cellpadding="1" cellspacing="0">
		<form method="post" action="${_SERVER['PHP_SELF']}" autocomplete="off">
		<input type="hidden" name="formSent" value="1">
		<input type="hidden" name="exerciseType" value="${exerciseType}">
		<input type="hidden" name="questionNum" value="${questionNum}">
		<input type="hidden" name="nbrQuestions" value="${nbrQuestions}">
		<tr>
		<td>
		<table width="100%" cellpadding="4" cellspacing="2" border="0">
cData;


$i=0;
foreach($questionList as $questionId) {
	$i++;
	//$tool_content .= "<h1>".$i."</h1>";

	// for sequential exercises
	if($exerciseType == 2)
	{
		//$tool_content .= "<h1>"."type=2"."</h1>";
		// if it is not the right question, goes to the next loop iteration
		if($questionNum != $i)
		{
			continue;
		}
		else
		{
			// if the user has already answered this question
			if(isset($exerciseResult[$questionId])) {
				// construction of the Question object
				$objQuestionTmp=new Question();

				// reads question informations
				$objQuestionTmp->read($questionId);

				$questionName=$objQuestionTmp->selectTitle();

				// destruction of the Question object
				unset($objQuestionTmp);

				$tool_content .= '<tr><td>'.$langAlreadyAnswered.' &quot;'.$questionName.'&quot;</td></tr>';
				break;
			}
		}
	}
	$tool_content .= "<tr bgcolor=\"#E6E6E6\"><td valign=\"top\" colspan=\"2\">".$langQuestion." ".$i; 
	
	if($exerciseType == 2) 
		$tool_content .= " / ".$nbrQuestions;
	$tool_content .= "</td></tr>";

	// shows the question and its answers
	showQuestion($questionId);

	// for sequential exercises
	if($exerciseType == 2)
	{
		// quits the loop
		break;
	}
}	// end foreach()
/////////////////////////////////////////////////////////////////////////////////
	$tool_content .= "</table></td></tr><tr><td align=\"center\"><br><input type=\"submit\" value=\"";
	if ($exerciseType == 1 || $nbrQuestions == $questionNum)
		$tool_content .= $langOk." &gt;"."\">";
	else	
		$tool_content .= $langNext." &gt;"."\">";

 	$tool_content .= " <input type=\"submit\" name=\"buttonCancel\" value=\"${langCancel}\">";
	$tool_content .= "</td></tr></form></table>";
/////////////////////////////////////////////////////////////////////////////////
draw($tool_content, 2);
?>