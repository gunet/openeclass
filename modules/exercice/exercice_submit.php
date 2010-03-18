<?php 
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('exercise.lib.php');
 
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Exercise';
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
// support for math symbols
include('../../include/phpmathpublisher/mathpublisher.php');
$tool_content = "";

$nameTools = $langExercicesView;
$picturePath='../../courses/'.$currentCourseID.'/image';
$is_allowedToEdit=$is_adminOfCourse;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';
 
if (isset($exerciseId)) {
	// security check 
	$active = mysql_fetch_array(db_query("SELECT active FROM `$TBL_EXERCICES` 
		WHERE id='$exerciseId'", $currentCourseID));
	if (($active['active'] == 0) and (!$is_allowedToEdit)) {
		header('Location: exercice.php');
		exit();
	} 
}

if (!isset($_SESSION['exercise_begin_time'])) {
	$_SESSION['exercise_begin_time'] = time();
}

// if the user has clicked on the "Cancel" button
if(isset($buttonCancel)) {
	// returns to the exercise list
	header('Location: exercice.php');
	exit();
}

// if the user has submitted the form
if (isset($formSent)) {
	$CurrentAttempt = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record 
		WHERE eid='$eid_temp' AND uid='$uid'", $currentCourseID));
	++$CurrentAttempt[0];
	if (($exerciseAllowedAttemtps == 0) or ($CurrentAttempt[0] <= $exerciseAllowedAttemtps)) { // if it is allowed
		if (isset($exerciseTimeConstrain) and $exerciseTimeConstrain != 0) { 
			$exerciseTimeConstrain = $exerciseTimeConstrain*60;
			$exerciseTimeConstrainSecs = time() - $exerciseTimeConstrain;
			$_SESSION['exercise_end_time'] = $exerciseTimeConstrainSecs;
			if ($_SESSION['exercise_end_time'] - $_SESSION['exercise_begin_time'] > $exerciseTimeConstrain) {
				unset($_SESSION['exercise_begin_time']);
				unset($_SESSION['exercise_end_time']);
				header('Location: exercise_redirect.php');
				exit();
			} 
		}
		$RecordEndDate = date("Y-m-d H:i:s", time());
		if (($exerciseType == 1) or (($exerciseType == 2) and ($nbrQuestions == $questionNum))) { // record
			mysql_select_db($currentCourseID); 
			$sql="INSERT INTO exercise_user_record(eid, uid, RecordStartDate, RecordEndDate, attempt)
				VALUES ('$eid_temp','$uid','$RecordStartDate','$RecordEndDate', 1)";
			$result=db_query($sql);
		}	
	} else {
		$tool_content .= "<br/><table width='99%' class='Question'>
		<thead><tr><td class='alert1'>$langExerciseExpired</td></tr>
		<tr>
		<td><br/><br/><br/><div align='center'><a href='exercice.php'>$langBack</a></div></td>
		</tr></thead></table>";
		draw($tool_content, 2, 'exercice');
		exit();
	}
	
	// initializing
	if(!is_array(@$exerciseResult)) {
		$exerciseResult=array();
	}

	// if the user has answered at least one question
	if(@is_array($choice)) {
		if($exerciseType == 1) {
			// $exerciseResult receives the content of the form.
			// Each choice of the student is stored into the array $choice
			$exerciseResult=$choice;
		} else {
			// gets the question ID from $choice. It is the key of the array
			list($key)=array_keys($choice);
			// if the user didn't already answer this question
			if(!isset($exerciseResult[$key])) {
				// stores the user answer into the array
				$exerciseResult[$key]=$choice[$key];
			}
		}
	}

	// the script "exercise_result.php" will take the variable $exerciseResult from the session
	$_SESSION['exerciseResult'] = $exerciseResult;

	// if it is the last question (only for a sequential exercise)
	if($exerciseType == 1 || $questionNum >= $nbrQuestions) {
		// goes to the script that will show the result of the exercise
		header('Location: exercise_result.php');
		exit();
	}
} // end of submit
if (!add_units_navigation()) {
	$navigation[]=array("url" => "exercice.php","name" => $langExercices);
}

// if the object is not in the session
if(!isset($_SESSION['objExercise'])) {
	// construction of Exercise
	$objExercise=new Exercise();
	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_allowedToEdit)) {
		$tool_content .= $langExerciseNotFound;
		draw($tool_content, 2, 'exercice');
		exit();
	}
	// saves the object into the session
	$_SESSION['objExercise'] = $objExercise;
}

$exerciseTitle=$objExercise->selectTitle();
$exerciseDescription=$objExercise->selectDescription();
$randomQuestions=$objExercise->isRandom();
$exerciseType=$objExercise->selectType();
$exerciseTimeConstrain=$objExercise->selectTimeConstrain();
$exerciseAllowedAttemtps=$objExercise->selectAttemptsAllowed();
$eid_temp = $objExercise->selectId();

$RecordStartDate = date("Y-m-d H:i:s", time());
if (!isset($_SESSION['questionList'])) {
	// selects the list of question ID
	$questionList=$randomQuestions?$objExercise->selectRandomList():$objExercise->selectQuestionList();
	// saves the question list into the session
	$_SESSION['questionList'] = $questionList;
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
	$exerciseDescription_temp = mathfilter($exerciseDescription_temp, 12, "../../courses/mathimg/");
	$tool_content .= <<<cData
      <table width="99%" class="Exercise">
      <thead>
      <tr>
        <td colspan=\"2\"><b>${exerciseTitle}</b>
        <br/><br/>
        ${exerciseDescription_temp}</td>
      </tr>
      </thead>
      </table>
      <form method="post" action="$_SERVER[PHP_SELF]" autocomplete="off">
      <input type="hidden" name="formSent" value="1">
      <input type="hidden" name="exerciseType" value="$exerciseType">
      <input type="hidden" name="questionNum" value="$questionNum">
      <input type="hidden" name="nbrQuestions" value="$nbrQuestions">
      <input type="hidden" name="exerciseTimeConstrain" value="$exerciseTimeConstrain">
      <input type="hidden" name="eid_temp" value="$eid_temp">
      <input type="hidden" name="RecordStartDate" value="$RecordStartDate">
      <input type="hidden" name="exerciseAllowedAttemtps" value="$exerciseAllowedAttemtps">

cData;

$i=0;
foreach($questionList as $questionId) {
	$i++;

	// for sequential exercises
	if($exerciseType == 2) {
		// if it is not the right question, goes to the next loop iteration
		if($questionNum != $i) {
			continue;
		} else {
			// if the user has already answered this question
			if(isset($exerciseResult[$questionId])) {
				// construction of the Question object
				$objQuestionTmp=new Question();
				// reads question informations
				$objQuestionTmp->read($questionId);
				$questionName=$objQuestionTmp->selectTitle();
				// destruction of the Question object
				unset($objQuestionTmp);
				$tool_content .= '<div class\"alert1\" '.$langAlreadyAnswered.' &quot;'.$questionName.'&quot;</div>';
				break;
			}
		}
	}
	// shows the question and its answers
	$tool_content .= "<br/><table width=\"99%\" class=\"Question\"><thead>
	<tr><td colspan=\"2\"><b><u>".$langQuestion."</u>: ".$i; 
	
	if($exerciseType == 2) { 
		$tool_content .= "/".$nbrQuestions;
	}
	$tool_content .= "</b></td></tr>";
	showQuestion($questionId);
	$tool_content .= "</thead></table>";

	// for sequential exercises
	if($exerciseType == 2) {
		// quits the loop
		break;
	}
}	// end foreach()

if (!$questionList) {
	$tool_content .= "<table width=\"99%\" class=\"Question\">
	<thead>
	<tr><td colspan='2'><font color='red'>$langNoAnswer</font></td></tr>
	</thead>
	</table>";	 
} else {
	$tool_content .= "<br/>	<table width=\"99%\" class=\"Exercise\"><tr>
	<td><div align=\"center\"><input type=\"submit\" value=\"";
		if ($exerciseType == 1 || $nbrQuestions == $questionNum)
			$tool_content .= "$langCont\">&nbsp;";
		else	
			$tool_content .= $langNext." &gt;"."\">";
		$tool_content .= "<input type=\"submit\" name=\"buttonCancel\" value=\"$langCancel\"></div>
	</td></tr></table>";
}	

$tool_content .= "</form>";
draw($tool_content, 2, 'exercice');
?>
