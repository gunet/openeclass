<?php
/* ========================================================================
 * Open eClass 2.6
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


define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS', 3);
define('MATCHING', 4);
define('TRUE_FALSE', 5);

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';
$TBL_RECORDS='exercise_user_record';

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

require_once '../../include/lib/modalboxhelper.class.php';
require_once '../../include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

load_js('tools.js');
$head_content .= "<script type='text/javascript'>";
	// If not editor, enable countdown mechanism
	if (!$is_editor) {            
	$head_content .= "
		$(document).ready(function(){
			timer = $('#progresstime');                        
			timer.time = timer.text();                        
                        timer.text(secondsToHms(timer.time--));
		    countdown(timer, function() {
		        alert('$langExerciseEndTimeResults');
		        $('.exercise').submit();
		    });
		});";
	}
$head_content .="$(exercise_enter_handler);</script>";

if (isset($_GET['exerciseId'])) {
	$exerciseId = intval($_GET['exerciseId']);
}

if (isset($exerciseId)) {
	// security check 
	$active = mysql_fetch_array(db_query("SELECT active FROM `$TBL_EXERCICES` 
		WHERE id='$exerciseId'", $currentCourseID));
	if (($active['active'] == 0) and (!$is_editor)) {
		$error = 'langExerciseExpired';
		header('Location: exercise_redirect.php?course='.$code_cours.'&exerciseId='.$exerciseId.'&error='.$error);
		exit();
	}
}

// if the user has clicked on the "Cancel" button
if (isset($_POST['buttonCancel'])) {
	// returns to the exercise list
	$error = 'langResultsFailed';
	$exerciseId = isset($_POST['exerciseId']) ? $_POST['exerciseId'] : "";
	unset($_SESSION['exercise_begin_time']);
	unset($_SESSION['exercise_end_time']);
	header('Location: exercise_redirect.php?course='.$code_cours.'&exerciseId='.$exerciseId.'&error='.$error);
	exit();
}
	
// if the user has submitted the form
if (isset($_POST['formSent'])) {
	$exerciseId = isset($_POST['exerciseId'])?intval($_POST['exerciseId']):'';
	$exerciseType = isset($_POST['exerciseType'])?$_POST['exerciseType']:'';
	$questionNum  = isset($_POST['questionNum'])?$_POST['questionNum']:'';
	$nbrQuestions = isset($_POST['nbrQuestions'])?$_POST['nbrQuestions']:'';
	$exercisetotalweight = isset($_POST['exercisetotalweight'])?$_POST['exercisetotalweight']:'';
	$exerciseTimeConstrain = isset($_POST['exerciseTimeConstrain'])?$_POST['exerciseTimeConstrain']:'';
	$eid_temp = isset($_POST['eid_temp'])?$_POST['eid_temp']:'';
	$RecordStartDate = isset($_POST['RecordStartDate'])?$_POST['RecordStartDate']:'';
	$choice = isset($_POST['choice'])?$_POST['choice']:'';
	if (isset($_SESSION['exerciseResult'][$exerciseId])) {
		$exerciseResult = $_SESSION['exerciseResult'][$exerciseId];
	} else {
		$exerciseResult = array();
	}
	// checking if user's time is more than exercise's time constrain
	if (isset($exerciseTimeConstrain) and $exerciseTimeConstrain != 0) {            		
                $exerciseTimeConstrain = $exerciseTimeConstrain*60;
		$exerciseTimeConstrainSecs = time() - $exerciseTimeConstrain;
		$_SESSION['exercise_end_time'][$exerciseId] = $exerciseTimeConstrainSecs;                
                                
		if (!$is_editor) {
                        if ($_SESSION['exercise_end_time'][$exerciseId] > $_SESSION['exercise_begin_time'][$exerciseId]) {
				$error = 'langExerciseExpiredTime';
				header('Location: exercise_redirect.php?course='.$code_cours.'&exerciseId='.$exerciseId.'&error='.$error);
			}
			unset($_SESSION['exercise_begin_time']);
			unset($_SESSION['exercise_end_time']);
		} 
	}
	
	// if the user has answered at least one question
	if (is_array($choice)) {
		if ($exerciseType == 1) {
			// $exerciseResult receives the content of the form.
			// Each choice of the student is stored into the array $choice
			$exerciseResult = $choice;
		} else {
			// gets the question ID from $choice. It is the key of the array
			list($key) = array_keys($choice);
			// if the user didn't already answer this question
			if (!isset($exerciseResult[$key])) {
				// stores the user answer into the array
				$exerciseResult[$key] = $choice[$key];
			}
		}
	}
	// the script "exercise_result.php" will take the variable $exerciseResult from the session
	$_SESSION['exerciseResult'][$exerciseId] = $exerciseResult;
	// if it is the last question (only for a sequential exercise)
	if($exerciseType == 1 or $questionNum >= $nbrQuestions 
                or ($exerciseType == 2 and (isset($error) and $error == 'langExerciseExpiredTime'))) {
		// goes to the script that will show the result of the exercise
		header('Location: exercise_result.php?course='.$code_cours.'&exerciseId='.$exerciseId);
		exit();
	}
} // end of submit

if (!add_units_navigation()) {
	$navigation[]=array("url" => "exercice.php?course=$code_cours","name" => $langExercices);
}

if (isset($_SESSION['objExercise'][$exerciseId])) {
	$objExercise = $_SESSION['objExercise'][$exerciseId];
}

// if the object is not in the session
if (!isset($_SESSION['objExercise'][$exerciseId])) {
	// construction of Exercise
	$objExercise = new Exercise();
	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_editor)) {
		$tool_content .= $langExerciseNotFound;
		draw($tool_content, 2);
		exit();
	}
	// saves the object into the session
	$_SESSION['objExercise'][$exerciseId] = $objExercise;
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$randomQuestions=$objExercise->isRandom();
$exerciseType = $objExercise->selectType();
$exerciseTimeConstrain = $objExercise->selectTimeConstrain();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$eid_temp = $objExercise->selectId();
$exercisetotalweight = $objExercise->selectTotalWeighting();

$temp_CurrentDate = $RecordStartDate = time();
$exercise_StartDate = strtotime($objExercise->selectStartDate());
$exercise_EndDate = strtotime($objExercise->selectEndDate());

if (!$is_editor) {
	$error = FALSE;
	// determine begin time: 
	// either from a previews attempt meaning that user hasn't sumbited his answers    
	// 		and exerciseTimeConstrain hasn't yet passed,
	// either start a new attempt and count now() as begin time.
	$sql = "SELECT COUNT(*), RecordStartDate FROM `$TBL_RECORDS` WHERE eid='$exerciseId' AND uid='$uid' AND (RecordEndDate is NULL OR RecordEndDate = 0)";
	$tmp = mysql_fetch_row(db_query($sql, $currentCourseID));
	if ($tmp[0] > 0) {
		$RecordStartDate = strtotime($tmp[1]);
		// if exerciseTimeConstrain has not passed yet calculate the remaining time
		if ($RecordStartDate + ($exerciseTimeConstrain*60) >= $temp_CurrentDate) {
			$_SESSION['exercise_begin_time'][$exerciseId] = $RecordStartDate;
			$timeleft = ($exerciseTimeConstrain*60) - ($temp_CurrentDate - $RecordStartDate);
		}
		// what # of attempt is this?
		$sql = "SELECT COUNT(*) FROM `$TBL_RECORDS` WHERE eid='$exerciseId' AND uid='$uid'";
		$tmp = mysql_fetch_row(db_query($sql, $currentCourseID));
		$attempt = $tmp[0];
	}
	if (!isset($_SESSION['exercise_begin_time'][$exerciseId])) {
		$_SESSION['exercise_begin_time'][$exerciseId] = $RecordStartDate = $temp_CurrentDate;
		// save begin time in db
		$start = date('Y-m-d H:i:s', $_SESSION['exercise_begin_time'][$exerciseId]);
		$sql = "SELECT COUNT(*) FROM `$TBL_RECORDS` WHERE eid='$exerciseId' AND uid='$uid'";
		$tmp = mysql_fetch_row(db_query($sql, $currentCourseID));
		$attempt = $tmp[0] + 1;
		// count this as an attempt by saving it as an incomplete record, if there are any available attempts left
		if (($exerciseAllowedAttempts > 0 && $attempt <= $exerciseAllowedAttempts) || $exerciseAllowedAttempts == 0) {			
			$sql = "INSERT INTO `$TBL_RECORDS` (eid, uid, RecordStartDate, TotalScore, TotalWeighting, attempt)
		                                VALUES ('$exerciseId','$uid','$start', 0, 0, $attempt)";
			$result = db_query($sql);
			$timeleft = $exerciseTimeConstrain*60;
		}
	}
	// Checking everything is between correct boundaries:
	
	// Number of Attempts
    if ($exerciseAllowedAttempts > 0 && $attempt > $exerciseAllowedAttempts) {
    	$error = 'langExerciseMaxAttemptsReached';
    }
    // Remaining Time
    if ($exerciseTimeConstrain > 0) {
        if ($RecordStartDate + ($exerciseTimeConstrain*60) < $temp_CurrentDate) {
            $error = 'langExerciseExpiredTime';
        }
    }
    // Exercise's Expiration
    if (($temp_CurrentDate < $exercise_StartDate) || ($temp_CurrentDate >= $exercise_EndDate)) { 
		$error = 'langExerciseExpired';
    }
    if ($error) {
    	unset($_SESSION['exercise_begin_time']);
    	unset($_SESSION['exercise_end_time']);
    	header('Location: exercise_redirect.php?course='.$code_cours.'&exerciseId='.$exerciseId.'&error='.$error);
    	exit();
    }
}
if (isset($_SESSION['questionList'][$exerciseId])) {
	$questionList = $_SESSION['questionList'][$exerciseId];
}
if (!isset($_SESSION['questionList'][$exerciseId])) {
	// selects the list of question ID
    $questionList= $randomQuestions ? $objExercise->selectRandomList() : $objExercise->selectQuestionList();
	// saves the question list into the session
	$_SESSION['questionList'][$exerciseId] = $questionList;
}

$nbrQuestions = sizeof($questionList);

// if questionNum comes from POST and not from GET
if (!isset($questionNum) || $_POST['questionNum']) {
	// only used for sequential exercises (see $exerciseType)
	if(!isset($questionNum)) {
		$questionNum=1;
	} else {
		$questionNum++;
	}
}

if (isset($_POST['questionNum'])) {
	$QUERY_STRING="questionNum=$questionNum";
}

$exerciseDescription_temp = standard_text_escape($exerciseDescription);
$tool_content .= "
 <table width='100%' class='tbl_border'>
  <tr class='odd'>
    <th colspan='2'>";
    if (!$is_editor and isset($timeleft) and $timeleft > 0) {
    	$tool_content .= "<div id='timedisplay'>$langRemainingTime: <span id='progresstime'>".($timeleft)."</span></div>";
    }
  $tool_content .= q($exerciseTitle). "</th></tr>
  <tr class='even'>
    <td colspan='2'>$exerciseDescription_temp</td>
  </tr>
  </table>

  <br />

  <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours' class='exercise'>
  <input type='hidden' name='formSent' value='1' />
  <input type='hidden' name='exerciseId' value='$exerciseId' />	
  <input type='hidden' name='exerciseType' value='$exerciseType' />	
  <input type='hidden' name='questionNum' value='$questionNum' />
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions' />
  <input type='hidden' name='exerciseTimeConstrain' value='$exerciseTimeConstrain' />
  <input type='hidden' name='eid_temp' value='$eid_temp' />
  <input type='hidden' name='exercisetotalweight' value='$exercisetotalweight' />
  <input type='hidden' name='RecordStartDate' value='$RecordStartDate' />";

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
				$tool_content .= "<div class='alert1'>".$langAlreadyAnswered." &quot;". q($questionName) ."&quot;</div>";
				break;
			}
		}
	}
    // shows the question and its answers
    $question = new Question();
    $question->read($questionId);
    $questionWeight = $question->selectWeighting();        
    $message = $langInfoGrades;             
	if (intval($questionWeight) == $questionWeight) {        
		$questionWeight = intval($questionWeight);
	}
	if ($questionWeight == 1) {
		$message = $langInfoGrade;
	} 
	$tool_content .= "<table width='100%' class='tbl'>
                <tr class='sub_title1'>
                <td colspan='2'>".$langQuestion.": ".$i.""; 
	
	if($exerciseType == 2) { 
		$tool_content .= "/".$nbrQuestions;
	}
        $tool_content .= "&nbsp;&nbsp;($questionWeight $message)";
	$tool_content .= "</td></tr>";
        unset($question);        	
	showQuestion($questionId);
        
	$tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr></table>";
	// for sequential exercises
	if($exerciseType == 2) {
		// quits the loop
		break;
	}
}	// end foreach()

if (!$questionList) {
	$tool_content .= "
          <table width='100%'>
          <tr>
            <td colspan='2'>
              <p class='caution'>$langNoAnswer</p>
            </td>
          </tr>
          </table>";	 
} else {
	$tool_content .= "
          <br/>
          <table width='100%' class='tbl'>
          <tr>
            <td><div class='right'><input type='submit' value='";
        if ($exerciseType == 1 || $nbrQuestions == $questionNum) {
                $tool_content .= "".q($langCont)."' />&nbsp;";
        } else {
                $tool_content .= "".q($langNext)." &gt;"."' />&nbsp;";
        }
	$tool_content .= "<input type='submit' name='buttonCancel' value='".q($langCancel)."' /></div>
            </td>
          </tr>
          <tr>
            <td colspan='2'>&nbsp;</td>
          </tr>
          </table>";
}
$tool_content .= "</form>";
draw($tool_content, 2, null, $head_content);
