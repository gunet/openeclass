<?
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

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

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

$nameTools = $langExercicesView;
$picturePath='../../courses/'.$currentCourseID.'/image';
 
if (isset($_GET['exerciseId'])) {
	$exerciseId = $_GET['exerciseId'];
}

if (isset($exerciseId)) {
	// security check 
	$active = mysql_fetch_array(db_query("SELECT active FROM `$TBL_EXERCICES` 
		WHERE id='$exerciseId'", $currentCourseID));
	if (($active['active'] == 0) and (!$is_adminOfCourse)) {
		header('Location: exercice.php');
		exit();
	} 
}

if (!isset($_SESSION['exercise_begin_time'])) {
	$_SESSION['exercise_begin_time'] = time();
}

// if the user has clicked on the "Cancel" button
if(isset($_POST['buttonCancel'])) {
	// returns to the exercise list
	header('Location: exercice.php');
	exit();
}
	
// if the user has submitted the form
if (isset($_POST['formSent'])) {
	$exerciseType = isset($_POST['exerciseType'])?$_POST['exerciseType']:'';
	$questionNum  = isset($_POST['questionNum'])?$_POST['questionNum']:'';
	$nbrQuestions = isset($_POST['nbrQuestions'])?$_POST['nbrQuestions']:'';
	$exerciseTimeConstrain = isset($_POST['exerciseTimeConstrain'])?$_POST['exerciseTimeConstrain']:'';
	$eid_temp = isset($_POST['eid_temp'])?$_POST['eid_temp']:'';
	$RecordStartDate = isset($_POST['RecordStartDate'])?$_POST['RecordStartDate']:'';
	$choice = isset($_POST['choice'])?$_POST['choice']:'';
	if (isset($_SESSION['exerciseResult'])) {
		$exerciseResult = $_SESSION['exerciseResult'];
	} else {
		$exerciseResult = array();
	}
	
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
	
	// if the user has answered at least one question
	if(is_array($choice)) {
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
	print_r($_SESSION['exerciseResult']);
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

if (isset($_SESSION['objExercise'])) {
	$objExercise = $_SESSION['objExercise'];
}

// if the object is not in the session
if(!isset($_SESSION['objExercise'])) {
	// construction of Exercise
	$objExercise=new Exercise();
	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_adminOfCourse)) {
		$tool_content .= $langExerciseNotFound;
		draw($tool_content, 2);
		exit();
	}
	// saves the object into the session
	$_SESSION['objExercise'] = $objExercise;
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseType = $objExercise->selectType();
$exerciseTimeConstrain = $objExercise->selectTimeConstrain();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$eid_temp = $objExercise->selectId();
$RecordStartDate = date("Y-m-d H:i:s", time());

// check if exercise has expired 
$CurrentAttempt = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record 
		WHERE eid='$eid_temp' AND uid='$uid'", $currentCourseID));
++$CurrentAttempt[0];
if ($exerciseAllowedAttempts > 0 and $CurrentAttempt[0] > $exerciseAllowedAttempts) { 
	$tool_content .= "<br/><table width='99%' class='Question'>
	<thead><tr><td class='alert1'>$langExerciseExpired</td></tr>
	<tr>
	<td><br/><br/><br/><div align='center'><a href='exercice.php'>$langBack</a></div></td>
	</tr></thead></table>";
	draw($tool_content, 2);
	exit();
}

if (isset($_SESSION['questionList'])) {
	$questionList = $_SESSION['questionList'];
}
if (!isset($_SESSION['questionList'])) {
	// selects the list of question ID
	$questionList = $objExercise->selectQuestionList();
	// saves the question list into the session
	$_SESSION['questionList'] = $questionList;
}

$nbrQuestions = sizeof($questionList);

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

$exerciseDescription_temp = standard_text_escape($exerciseDescription);
$tool_content .= "<table width='99%' class='Exercise'>
<thead>
<tr>
  <td colspan=\"2\"><b>$exerciseTitle</b>
  <br/><br/>
  $exerciseDescription_temp</td>
</tr>
</thead>
</table>
<form method='post' action='$_SERVER[PHP_SELF]' autocomplete='off'>
<input type='hidden' name='formSent' value='1'>
<input type='hidden' name='exerciseType' value='$exerciseType'>	
<input type='hidden' name='questionNum' value='$questionNum'>
<input type='hidden' name='nbrQuestions' value='$nbrQuestions'>
<input type='hidden' name='exerciseTimeConstrain' value='$exerciseTimeConstrain'>
<input type='hidden' name='eid_temp' value='$eid_temp'>
<input type='hidden' name='RecordStartDate' value='$RecordStartDate'>";

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
		if ($exerciseType == 1 || $nbrQuestions == $questionNum) {
			$tool_content .= "$langCont\">&nbsp;";
		} else {
			$tool_content .= $langNext." &gt;"."\">";
		}
	$tool_content .= "<input type='submit' name='buttonCancel' value='$langCancel'></div>
	</td></tr></table>";
}	

$tool_content .= "</form>";
draw($tool_content, 2);
?>