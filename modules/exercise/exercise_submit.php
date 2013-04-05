<?php
/* ========================================================================
 * Open eClass 3.0
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


$TBL_EXERCISE_QUESTION = 'exercise_with_questions';
$TBL_EXERCISE = 'exercise';
$TBL_QUESTION = 'exercise_question';
$TBL_ANSWER  = 'exercise_answer';

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('exercise.lib.php');

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Exercise';
$guest_allowed = true;

include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$nameTools = $langExercicesView;
$picturePath = "courses/$course_code/image";

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

if (isset($_REQUEST['exerciseId'])) {
	$exerciseId = intval($_REQUEST['exerciseId']);
}

if (isset($exerciseId)) {
	// security check
	$active = mysql_fetch_array(db_query("SELECT active FROM `$TBL_EXERCISE`
                                                WHERE course_id = $course_id AND id = '$exerciseId'"));
	if (($active['active'] == 0) and (!$is_editor)) {
		header('Location: index.php?course='.$course_code);
		exit();
	}
}

if (!isset($_SESSION['exercise_begin_time'][$exerciseId])) {
	$_SESSION['exercise_begin_time'][$exerciseId] = time();
}

// if the user has clicked on the "Cancel" button
if(isset($_POST['buttonCancel'])) {
	// returns to the exercise list
	header('Location: index.php?course='.$course_code);
	exit();
}

// if the user has submitted the form
if (isset($_POST['formSent'])) {
	$exerciseId   = isset($_POST['exerciseId'])   ? intval($_POST['exerciseId']) : '';
	$exerciseType = isset($_POST['exerciseType']) ? $_POST['exerciseType'] : '';
	$questionNum  = isset($_POST['questionNum'])  ? $_POST['questionNum']  : '';
	$nbrQuestions = isset($_POST['nbrQuestions']) ? $_POST['nbrQuestions'] : '';
        $exercisetotalweight = isset($_POST['exercisetotalweight'])?$_POST['exercisetotalweight']:'';
	$exerciseTimeConstraint = isset($_POST['exerciseTimeConstraint']) ? $_POST['exerciseTimeConstraint'] : '';
	$eid_temp        = isset($_POST['eid_temp'])          ? $_POST['eid_temp']          : '';
	$recordStartDate = isset($_POST['record_start_date']) ? $_POST['record_start_date'] : '';
	$choice          = isset($_POST['choice'])            ? $_POST['choice']            : '';
	if (isset($_SESSION['exerciseResult'][$exerciseId])) {
		$exerciseResult = $_SESSION['exerciseResult'][$exerciseId];
	} else {
		$exerciseResult = array();
	}

	if (isset($exerciseTimeConstraint) and $exerciseTimeConstraint != 0) {
		$exerciseTimeConstraint = $exerciseTimeConstraint * 60;
		$exerciseTimeConstraintSecs = time() - $exerciseTimeConstraint;
		$_SESSION['exercise_end_time'][$exerciseId] = $exerciseTimeConstraintSecs;

		if ($_SESSION['exercise_end_time'][$exerciseId] - $_SESSION['exercise_begin_time'][$exerciseId] > $exerciseTimeConstraint) {
			unset($_SESSION['exercise_begin_time']);
			unset($_SESSION['exercise_end_time']);
			header('Location: exercise_redirect.php?course='.$course_code.'&exerciseId='.$exerciseId);
			exit();
		}
	}
	$recordEndDate = date("Y-m-d H:i:s", time());
	if (($exerciseType == 1) or (($exerciseType == 2) and ($nbrQuestions == $questionNum))) { // record
		mysql_select_db($mysqlMainDb);
		$sql = "INSERT INTO exercise_user_record (eid, uid, record_start_date, record_end_date, total_score, total_weighting, attempt)
			VALUES ('$eid_temp', '$uid', '$recordStartDate', '$recordEndDate', 0, $exercisetotalweight, 1)";
		$result = db_query($sql);
	}

	// if the user has answered at least one question
	if(is_array($choice)) {
		if($exerciseType == 1) {
			// $exerciseResult receives the content of the form.
			// Each choice of the student is stored into the array $choice
			$exerciseResult = $choice;
		} else {
			// gets the question ID from $choice. It is the key of the array
			list($key) = array_keys($choice);
			// if the user didn't already answer this question
			if(!isset($exerciseResult[$key])) {
				// stores the user answer into the array
				$exerciseResult[$key] = $choice[$key];
			}
		}
	}
	// the script "exercise_result.php" will take the variable $exerciseResult from the session
	$_SESSION['exerciseResult'][$exerciseId] = $exerciseResult;
	// if it is the last question (only for a sequential exercise)
	if($exerciseType == 1 || $questionNum >= $nbrQuestions) {
		// goes to the script that will show the result of the exercise
		header('Location: exercise_result.php?course='.$course_code.'&exerciseId='.$exerciseId);
		exit();
	}
} // end of submit

if (!add_units_navigation()) {
	$navigation[] = array("url" => "index.php?course=$course_code","name" => $langExercices);
}

if (isset($_SESSION['objExercise'][$exerciseId])) {
	$objExercise = $_SESSION['objExercise'][$exerciseId];
}

// if the object is not in the session
if(!isset($_SESSION['objExercise'][$exerciseId])) {
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

$exerciseTitle           = $objExercise->selectTitle();
$exerciseDescription     = $objExercise->selectDescription();
$randomQuestions         = $objExercise->isRandom();
$exerciseType            = $objExercise->selectType();
$exerciseTimeConstraint  = $objExercise->selectTimeConstraint();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$eid_temp                = $objExercise->selectId();
$exercisetotalweight     = $objExercise->selectTotalWeighting();
$recordStartDate         = date("Y-m-d H:i:s", time());

$temp_CurrentDate = date("Y-m-d H:i");
$temp_StartDate = $objExercise->selectStartDate();
$temp_EndDate = $objExercise->selectEndDate();
$temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
$temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
$temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));

if (!$is_editor) {
        $error = FALSE;
        // check if exercise has expired or is active
        $currentAttempt = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record
                        WHERE eid = '$eid_temp' AND uid = '$uid'", $course_code));
        ++$currentAttempt[0];
        if ($exerciseAllowedAttempts > 0 and $currentAttempt[0] > $exerciseAllowedAttempts) {
                $message = $langExerciseMaxAttemptsReached;
                $error = TRUE;
        }
        if (($temp_CurrentDate < $temp_StartDate) || ($temp_CurrentDate >= $temp_EndDate)) {
                $message = $langExerciseExpired;
                $error = TRUE;
        }
        if ($error == TRUE) {
                $tool_content .= "
                  <br/>
                    <table width='100%' class='tbl'>
                    <tr>
                      <td class='alert1'>$message</td>
                    </tr>
                    <tr>
                      <td><br/><br/><br/><div align='center'>
                        <a href='index.php?course=$course_code'>$langBack</a></div></td>
                    </tr>
                    </table>";
                draw($tool_content, 2);
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
if(!isset($questionNum) || $_POST['questionNum']) {
	// only used for sequential exercises (see $exerciseType)
	if(!isset($questionNum)) {
		$questionNum = 1;
	} else {
		$questionNum++;
	}
}

if(@$_POST['questionNum']) {
	$QUERY_STRING = "questionNum = $questionNum";
}

$exerciseDescription_temp = standard_text_escape($exerciseDescription);
$tool_content .= "
 <table width='100%' class='tbl_border'>
  <tr class='odd'>
    <th colspan='2'>". q($exerciseTitle) ."</th>
  </tr>
  <tr class='even'>
    <td colspan='2'>$exerciseDescription_temp</td>
  </tr>
  </table>

  <br />

  <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
  <input type='hidden' name='formSent' value='1' />
  <input type='hidden' name='exerciseId' value='$exerciseId' />
  <input type='hidden' name='exerciseType' value='$exerciseType' />
  <input type='hidden' name='questionNum' value='$questionNum' />
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions' />
  <input type='hidden' name='exerciseTimeConstraint' value='$exerciseTimeConstraint' />
  <input type='hidden' name='eid_temp' value='$eid_temp' />
  <input type='hidden' name='exercisetotalweight' value='$exercisetotalweight' />
  <input type='hidden' name='record_start_date' value='$recordStartDate' />";

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
				$objQuestionTmp = new Question();
				// reads question informations
				$objQuestionTmp->read($questionId);
				$questionName = $objQuestionTmp->selectTitle();
				// destruction of the Question object
				unset($objQuestionTmp);
				$tool_content .= '<div class\"alert1\" '.$langAlreadyAnswered.' &quot;'. q($questionName) .'&quot;</div>';
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
                <td colspan='2'>".$langQuestion.": ".$i."&nbsp;($questionWeight $message)";

	if($exerciseType == 2) {
		$tool_content .= "/".$nbrQuestions;
	}
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
                $tool_content .= "$langCont' />";
        } else {
                $tool_content .= $langNext." &gt;"."' />";
        }
	$tool_content .= "&nbsp;<input type='submit' name='buttonCancel' value='$langCancel' /></div>
        </td>
        </tr>
        <tr>
        <td colspan='2'>&nbsp;</td>
        </tr>
        </table>";
}
$tool_content .= "</form>";
draw($tool_content, 2, null, $head_content);
