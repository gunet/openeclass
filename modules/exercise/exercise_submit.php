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
$TBL_ANSWER = 'exercise_answer';
$TBL_RECORDS = 'exercise_user_record';

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
//Identifying ajax request
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && !$is_editor) {
        if ($_POST['action'] == 'endExerciseNoSubmit') {
            $exerciseId = $_POST['value'];
            $record_end_date = date('Y-m-d H:i:s', time());
            $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId];
            Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t
                    WHERE eurid = ?d", $record_end_date, $eurid);
            Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid = ?d", $eurid);
            unset($_SESSION['objExercise'][$exerciseId]);
            unset($_SESSION['exercise_begin_time']);
            unset($_SESSION['exercise_end_time']);
            exit();
        }
}

//Checks if an exercise ID exists in the URL
//and if so it gets the exercise object either by the session (if it exists there)
//or by initializing it using the exercise ID
if (isset($_REQUEST['exerciseId'])) {
    $exerciseId = intval($_REQUEST['exerciseId']);    
    //  Checks if exercise object exists in the Session
    if (isset($_SESSION['objExercise'][$exerciseId])) {
        $objExercise = $_SESSION['objExercise'][$exerciseId];
    } else {
        // construction of Exercise
        $objExercise = new Exercise();
        // if the specified exercise is disabled (this only applies to students)
        // or doesn't exist redirects and shows error 
        if (!$objExercise->read($exerciseId) || (!$is_editor && $objExercise->selectStatus($exerciseId)==0)) {
            //$langExerciseNotFound??
            session::set_flashdata('Η άσκηση δεν βρέθηκε', 'alert1');
            redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
        }
        // saves the object into the session
        $_SESSION['objExercise'][$exerciseId] = $objExercise;        
    }
} else {
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}
// if the user has clicked on the "Cancel" button
// ends the exercise and returns to the exercise list
if (isset($_POST['buttonCancel'])) {
        $record_end_date = date('Y-m-d H:i:s', time());
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId];
        Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t
                WHERE eurid = ?d", $record_end_date, $eurid);
	Session::set_flashdata('Η προσπάθεια ακυρώθηκε', 'alert1');
        unset($_SESSION['objExercise'][$exerciseId]);
        unset($_SESSION['exercise_begin_time']);
        unset($_SESSION['exercise_end_time']);        
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}
// if the user has clicked on the "Save & Exit" button
// keeps the exercise in a pending/uncompleted state and returns to the exercise list
if (isset($_POST['buttonSave'])) {
	Session::set_flashdata('Η προσπάθεια αποθηκεύτηκε προσωρινά. Ξαναμπείτε στην άσκηση για να συνεχίσετε.', 'alert1');        
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}
load_js('tools.js');
load_js('jquery');
$head_content .= "<script type='text/javascript'>";
	// If not editor, enable countdown mechanism
	if (!$is_editor) {            
	    $head_content .= "            
                $(window).bind('beforeunload', function(){
                    return 'ΠΡΟΣΟΧΗ! Με την έξοδο σας από την άσκηση η προσπάθεια σας καταγράφεται σαν να μην έχετε δώσει καμία απάντηση. Παρακαλώ ολοκληρώστε την άσκηση ή κάντε προσωρινή αποθήκευση';
                });
                $(window).bind('unload', function(){ 
                    $.ajax({
                      type: 'POST',
                      url: '',
                      data: { action: 'endExerciseNoSubmit', value: $exerciseId},
                      async:false
                    });
                });                  
    		$(document).ready(function(){
                    $('.exercise').submit(function(){
                            $(window).unbind('beforeunload');
                            $(window).unbind('unload');
                    });
                    
                    timer = $('#progresstime');                        
                    timer.time = timer.text();                        
                    timer.text(secondsToHms(timer.time--));
    		    countdown(timer, function() {
    		        alert('$langExerciseEndTime');
    		        $('.exercise').submit();
    		    });
    		});";
	}
$head_content .="$(exercise_enter_handler);</script>";


$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$randomQuestions = $objExercise->isRandom();
$exerciseType = $objExercise->selectType();
$exerciseTimeConstraint = $objExercise->selectTimeConstraint();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$eid_temp = $objExercise->selectId(); //is this needed?
$exercisetotalweight = $objExercise->selectTotalWeighting();

$temp_CurrentDate = $recordStartDate = time();
$exercise_StartDate = strtotime($objExercise->selectStartDate());
$exercise_EndDate = strtotime($objExercise->selectEndDate());

//If question list exists in the Session get it for there
//else get it using the apropriate object method and save it to the session
if (isset($_SESSION['questionList'][$exerciseId])) {
    $questionList = $_SESSION['questionList'][$exerciseId];
} else {
    // selects the list of question ID
    $questionList = $randomQuestions ? $objExercise->selectRandomList() : $objExercise->selectQuestionList();
    // saves the question list into the session
    $_SESSION['questionList'][$exerciseId] = $questionList;
}

$nbrQuestions = sizeof($questionList);

if (!add_units_navigation()) {
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);
}

// if the user has submitted the form
if (isset($_POST['formSent'])) {
    $exerciseId = isset($_POST['exerciseId']) ? intval($_POST['exerciseId']) : '';
    $exerciseType = isset($_POST['exerciseType']) ? $_POST['exerciseType'] : '';
    $questionNum = isset($_POST['questionNum']) ? $_POST['questionNum'] : '';
    $nbrQuestions = isset($_POST['nbrQuestions']) ? $_POST['nbrQuestions'] : '';
    $exercisetotalweight = isset($_POST['exercisetotalweight']) ? $_POST['exercisetotalweight'] : '';
    $exerciseTimeConstraint = intval($_POST['exerciseTimeConstraint']);
    $eid_temp = isset($_POST['eid_temp']) ? $_POST['eid_temp'] : '';
    $recordStartDate = isset($_POST['record_start_date']) ? $_POST['record_start_date'] : '';
    $choice = isset($_POST['choice']) ? $_POST['choice'] : '';
            
    //if there are answers in the session (one question per page) get them
    if (isset($_SESSION['exerciseResult'][$exerciseId])) {
        $exerciseResult = $_SESSION['exerciseResult'][$exerciseId];
    } else {
        $exerciseResult = array();
    }
    // checking if user's time is more than exercise's time constrain
    if (!$is_editor && isset($exerciseTimeConstraint) && $exerciseTimeConstraint != 0) {
        $_SESSION['exercise_begin_time'][$exerciseId] = Database::get()->querySingle("SELECT record_start_date FROM `$TBL_RECORDS` WHERE eid = ?d AND uid = ?d AND (record_end_date is NULL OR record_end_date = 0)", $exerciseId, $uid)->record_start_date;
        $nowTime = new DateTime();
        $startTime = new DateTime($_SESSION['exercise_begin_time'][$exerciseId]);
        $endTime = new DateTime($startTime->format('Y-m-d H:i:s'));
        $endTime->add(new DateInterval('PT1M'));
        if ($endTime < $nowTime) {
            $time_expired = TRUE;                     
        }

        unset($_SESSION['exercise_begin_time']);    
    }

    // records user's answers in the database and adds them in the $exerciseResult array which is returned
    $exerciseResult = $objExercise->record_answers($choice, $exerciseResult);

    // the script "exercise_result.php" will take the variable $exerciseResult from the session
    $_SESSION['exerciseResult'][$exerciseId] = $exerciseResult;
    
    // if it is a non-sequential exercice OR
    // if it is a sequnential exercise in the last question OR the time has expired
    if ($exerciseType == 1 || $exerciseType == 2 && ($questionNum >= $nbrQuestions || (isset($time_expired) && $time_expired))) {
        // goes to the script that will show the result of the exercise
        unset($_SESSION['exerciseUserRecordID'][$exerciseId]);
        unset($_SESSION['exercise_begin_time']);    
        unset($_SESSION['exercise_end_time']);    

        redirect_to_home_page('modules/exercise/exercise_result.php?course='.$course_code.'&exerciseId='.$exerciseId);
    }
} // end of submit



if (!$is_editor) {
	$error = FALSE;
	// determine begin time: 
	// either from a previews attempt meaning that user hasn't sumbited his answers    
	// 		and exerciseTimeConstrain hasn't yet passed,
	// either start a new attempt and count now() as begin time.
	$sql = "SELECT COUNT(*), record_start_date FROM `$TBL_RECORDS` WHERE eid='$exerciseId' AND uid='$uid' AND (record_end_date is NULL OR record_end_date = 0)";
	$tmp = mysql_fetch_row(db_query($sql));
	if ($tmp[0] > 0) {
		$recordStartDate = strtotime($tmp[1]);
		// if exerciseTimeConstrain has not passed yet calculate the remaining time               
		if ($recordStartDate + ($exerciseTimeConstraint*60) >= $temp_CurrentDate) {
                        $_SESSION['exercise_begin_time'][$exerciseId] = $recordStartDate;
			$timeleft = ($exerciseTimeConstraint*60) - ($temp_CurrentDate - $recordStartDate);
		}
		// what # of attempt is this?
		$sql = "SELECT COUNT(*) FROM `$TBL_RECORDS` WHERE eid='$exerciseId' AND uid='$uid'";
		$tmp = mysql_fetch_row(db_query($sql));
		$attempt = $tmp[0];
	}
	if (!isset($_SESSION['exercise_begin_time'][$exerciseId]) && $nbrQuestions > 0) {
		$_SESSION['exercise_begin_time'][$exerciseId] = $recordStartDate = $temp_CurrentDate;
		// save begin time in db
		$start = date('Y-m-d H:i:s', $_SESSION['exercise_begin_time'][$exerciseId]);
		$sql = "SELECT COUNT(*) FROM `$TBL_RECORDS` WHERE eid='$exerciseId' AND uid='$uid'";
		$tmp = mysql_fetch_row(db_query($sql));
		$attempt = $tmp[0] + 1;
		// count this as an attempt by saving it as an incomplete record, if there are any available attempts left
		if (($exerciseAllowedAttempts > 0 && $attempt <= $exerciseAllowedAttempts) || $exerciseAllowedAttempts == 0) {
                    $eurid = Database::get()->query("INSERT INTO exercise_user_record (eid, uid, record_start_date, total_score, total_weighting, attempt)
                        VALUES (?d, ?d, ?t, 0, 0, ?d)", $exerciseId, $uid, $start, $attempt)->lastInsertID;
                    
                    $_SESSION['exerciseUserRecordID'][$exerciseId] = $eurid;
                    
                    $timeleft = $exerciseTimeConstraint*60;
		}
	}
	// Checking everything is between correct boundaries:
	
	// Number of Attempts
    if ($exerciseAllowedAttempts > 0 && $attempt > $exerciseAllowedAttempts) {
    	$error = 'langExerciseMaxAttemptsReached';
    }
    // Remaining Time
    if ($exerciseTimeConstraint!=0 && ($recordStartDate + ($exerciseTimeConstraint*60) < $temp_CurrentDate)) {
    	$error = 'langExerciseExpiredTime';
    }
    // Exercise's Expiration
    if (($temp_CurrentDate < $exercise_StartDate) || ($temp_CurrentDate >= $exercise_EndDate)) { 
		$error = 'langExerciseExpired';
    }
    if ($error) {
    	unset($_SESSION['exercise_begin_time']);
    	unset($_SESSION['exercise_end_time']);
    	header('Location: exercise_redirect.php?course='.$course_code.'&exerciseId='.$exerciseId.'&error='.$error);
    	exit();
    }
}

// if questionNum comes from POST and not from GET
if (!isset($questionNum) || $_POST['questionNum']) {
    // only used for sequential exercises (see $exerciseType)
    if (!isset($questionNum)) {
        $questionNum = 1;
    } else {
        $questionNum++;
    }
}

if (isset($_POST['questionNum'])) {
    $QUERY_STRING = "questionNum = $questionNum";
}

$exerciseDescription_temp = standard_text_escape($exerciseDescription);
$tool_content .= "
 <table width='100%' class='tbl_border'>
  <tr class='odd'>
    <th colspan='2'>";
        if (!$is_editor && isset($timeleft) && $timeleft>0) {
            $tool_content .= "<div id='timedisplay'>$langRemainingTime: <span id='progresstime'>".($timeleft)."</span></div>";
        }
        $tool_content .= q($exerciseTitle). "</th></tr>
  <tr class='even'>
    <td colspan='2'>$exerciseDescription_temp</td>
  </tr>
  </table>

  <br />

  <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&exerciseId=$exerciseId' class='exercise' >
  <input type='hidden' name='formSent' value='1' />
  <input type='hidden' name='exerciseId' value='$exerciseId' />
  <input type='hidden' name='exerciseType' value='$exerciseType' />
  <input type='hidden' name='questionNum' value='$questionNum' />
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions' />
  <input type='hidden' name='exerciseTimeConstraint' value='$exerciseTimeConstraint' />
  <input type='hidden' name='eid_temp' value='$eid_temp' />
  <input type='hidden' name='exercisetotalweight' value='$exercisetotalweight' />
  <input type='hidden' name='record_start_date' value='$recordStartDate' />";

$i = 0;
foreach ($questionList as $questionId) {
    $i++;
    // for sequential exercises
    if ($exerciseType == 2) {
        // if it is not the right question, goes to the next loop iteration
        if ($questionNum != $i) {
            continue;
        } else {
            // if the user has already answered this question
            if (isset($exerciseResult[$questionId])) {
                // construction of the Question object
                $objQuestionTmp = new Question();
                // reads question informations
                $objQuestionTmp->read($questionId);
                $questionName = $objQuestionTmp->selectTitle();
                // destruction of the Question object
                unset($objQuestionTmp);
                $tool_content .= '<div class\"alert1\" ' . $langAlreadyAnswered . ' &quot;' . q($questionName) . '&quot;</div>';
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
                <td colspan='2'>" . $langQuestion . ": " . $i . "&nbsp;($questionWeight $message)";

    if ($exerciseType == 2) {
        $tool_content .= "/" . $nbrQuestions;
    }
    $tool_content .= "</td></tr>";
    unset($question);
    showQuestion($questionId);

    $tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr></table>";
    // for sequential exercises
    if ($exerciseType == 2) {
        // quits the loop
        break;
    }
} // end foreach()

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
        $tool_content .= $langNext . " &gt;" . "' />";
    }
    $tool_content .= "&nbsp;<input type='submit' name='buttonSave' value='Προσωρινή Αποθήκευση' />";
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
