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
function unset_exercise_var($exerciseId){
            unset($_SESSION['exerciseUserRecordID'][$exerciseId]);
            unset($_SESSION['objExercise'][$exerciseId]);
            unset($_SESSION['exerciseResult'][$exerciseId]);
            unset($_SESSION['questionList'][$exerciseId]);
            unset($_SESSION['exercise_begin_time'][$exerciseId]);    
            unset($_SESSION['exercise_end_time']);
}
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($_POST['action'] == 'endExerciseNoSubmit') {             
            $exerciseId = $_POST['eid'];             
            $record_end_date = date('Y-m-d H:i:s', time());
            $eurid = $_POST['eurid'];
            Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, attempt_status = ?d
                    WHERE eurid = ?d", $record_end_date, ATTEMPT_CANCELED, $eurid);
            Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid = ?d", $eurid);
            unset_exercise_var($exerciseId);
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
            session::set_flashdata($langExerciseNotFound, 'alert1');
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
        Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, attempt_status = ?d
                WHERE eurid = ?d", $record_end_date, ATTEMPT_CANCELED, $eurid);
        Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid = ?d", $eurid);
	Session::set_flashdata($landAttemptCanceled, 'alert1');
        unset($_SESSION['exerciseUserRecordID'][$exerciseId]);
        unset($_SESSION['exercise_begin_time'][$exerciseId]);    
        unset($_SESSION['exercise_begin_time'][$exerciseId]); 
        unset($_SESSION['objExercise'][$exerciseId]);
        unset($_SESSION['exerciseResult'][$exerciseId]);        
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}
// if the user has clicked on the "Save & Exit" button
// keeps the exercise in a pending/uncompleted state and returns to the exercise list
if (isset($_POST['buttonSave'])) {
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId];
        Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, attempt_status = ?d
                WHERE eurid = ?d", $record_end_date, ATTEMPT_PAUSED, $eurid);    
	Session::set_flashdata($langTemporarySaveSuccess, 'alert1');        
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}
// setting a cookie in OnBeforeUnload event in order to redirect user to the exercises page in case of refresh
// as the synchronous ajax call in onUnload event doen't work the same in all browsers in case of refresh 
// (It is executed after page load in Chrome and Mozilla and before page load in IE).  
// In current functionality if user leaves the exercise for another module the cookie will expire anyway in 30 seconds
// or it will be unset by the exercises page (index.php). If user who left an exercise for another module 
// visits through a direct link a specific execise page before the 30 seconds time frame
// he will be redirected to the exercises page (index.php)
if (isset($_COOKIE['inExercise'])) {
    setcookie("inExercise", "", time() - 3600);
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}
load_js('tools.js');
load_js('jquery');

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$randomQuestions = $objExercise->isRandom();
$exerciseType = $objExercise->selectType();
$exerciseTimeConstraint = (int) $objExercise->selectTimeConstraint();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
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

$nbrQuestions = count($questionList);

if (!add_units_navigation()) {
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);
}

$error = FALSE;
// determine begin time: 
// either from a previews attempt meaning that user hasn't sumbited his answers    
// 		and exerciseTimeConstrain hasn't yet passed,
// either start a new attempt and count now() as begin time.
if (isset($_SESSION['exerciseUserRecordID'][$exerciseId])) {
    $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId];
    $recordStartDate = Database::get()->querySingle("SELECT record_start_date FROM exercise_user_record WHERE eurid = ?d", $eurid)->record_start_date;
    $recordStartDate = strtotime($recordStartDate);
    //duplicate line
    $attempt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exerciseId, $uid)->count;
    $_SESSION['exercise_begin_time'][$exerciseId] = $recordStartDate;
    // if exerciseTimeConstrain has not passed yet calculate the remaining time               
    if ($exerciseTimeConstraint>0) {
        $timeleft = ($exerciseTimeConstraint*60) - ($temp_CurrentDate - $recordStartDate);
    }
} elseif (!isset($_SESSION['exercise_begin_time'][$exerciseId]) && $nbrQuestions > 0) {
    $_SESSION['exercise_begin_time'][$exerciseId] = $recordStartDate = $temp_CurrentDate;
    $start = date('Y-m-d H:i:s', $_SESSION['exercise_begin_time'][$exerciseId]);
    //duplicate line
    $attempt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exerciseId, $uid)->count;
    $attempt++;
    // count this as an attempt by saving it as an incomplete record, if there are any available attempts left
    if (($exerciseAllowedAttempts > 0 && $attempt <= $exerciseAllowedAttempts) || $exerciseAllowedAttempts == 0) {
        $eurid = Database::get()->query("INSERT INTO exercise_user_record (eid, uid, record_start_date, total_score, total_weighting, attempt, attempt_status)
                        VALUES (?d, ?d, ?t, 0, 0, ?d, 0)", $exerciseId, $uid, $start, $attempt)->lastInsertID;            
        $_SESSION['exerciseUserRecordID'][$exerciseId] = $eurid;
        $timeleft = $exerciseTimeConstraint*60;
    }    
}

//if there are answers in the session get them
if (isset($_SESSION['exerciseResult'][$exerciseId])) {
        $exerciseResult = $_SESSION['exerciseResult'][$exerciseId];
} else {
        $exerciseResult = array();
}

$questionNum = count($exerciseResult)+1;
// if the user has submitted the form
if (isset($_POST['formSent'])) {

    $choice = isset($_POST['choice']) ? $_POST['choice'] : '';
            
    // checking if user's time is more than exercise's time constrain
    if (isset($exerciseTimeConstraint) && $exerciseTimeConstraint != 0) {
        $nowTime = new DateTime();
        $startTime = new DateTime();
        $startTime->setTimestamp($_SESSION['exercise_begin_time'][$exerciseId]);
        $endTime = new DateTime($startTime->format('Y-m-d H:i:s'));
        $interval = 'PT'.$exerciseTimeConstraint.'M';
        $endTime->add(new DateInterval($interval)); // THIS MUST CHANGE TO WORK WITH OTHER THAN 1 MINUTE CONSTRAINTS
        if ($endTime < $nowTime) {
            $time_expired = TRUE;                     
        }
    }

    // records user's answers in the database and adds them in the $exerciseResult array which is returned
    $exerciseResult = $objExercise->record_answers($choice, $exerciseResult);

    // the script "exercise_result.php" will take the variable $exerciseResult from the session
    $_SESSION['exerciseResult'][$exerciseId] = $exerciseResult;
    
    // if it is a non-sequential exercice OR
    // if it is a sequnential exercise in the last question OR the time has expired
    if ($exerciseType == 1 || $exerciseType == 2 && ($questionNum >= $nbrQuestions || (isset($time_expired) && $time_expired))) {
        // goes to the script that will show the result of the exercise
        var_dump('mpika2');
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId];
        $record_end_date = date('Y-m-d H:i:s', time());
        $totalScore = Database::get()->querySingle("SELECT SUM(weight) FROM exercise_answer_record WHERE eurid = ?d", $eurid);
        $totalWeighting = $objExercise->selectTotalWeighting();
        //If time expired in sequential exercise we must add to the DB the non-given answers
        // to the questions the student didn't had the time to answer
        if (isset($time_expired) && $time_expired && $exerciseType == 2) {
            $objExercise->finalize_answers();
        }
        $unmarked_free_text_nbr = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_answer_record WHERE weight IS NULL AND eurid = ?d", $eurid)->count;
        $attempt_status = ($unmarked_free_text_nbr > 0) ? ATTEMPT_PENDING : ATTEMPT_COMPLETED;
        // record results of exercise
        Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, total_score = ?f, attempt_status = ?d,
                                total_weighting = ?f WHERE eurid = ?d", $record_end_date, $totalScore, $attempt_status, $totalWeighting, $eurid);
        
        unset($objExercise);
        unset($_SESSION['exerciseUserRecordID'][$exerciseId]);
        unset($_SESSION['exercise_begin_time'][$exerciseId]);    
        unset($_SESSION['exercise_end_time']);  
        unset($_SESSION['objExercise'][$exerciseId]);
        unset($_SESSION['exerciseResult'][$exerciseId]);
        // Remaining Time
        if ($exerciseTimeConstraint!=0 && ($recordStartDate + ($exerciseTimeConstraint*60) < $temp_CurrentDate)) {
            Session::set_flashdata($langExerciseExpiredTime, 'alert1');
        }
        redirect_to_home_page('modules/exercise/exercise_result.php?course='.$course_code.'&eurId='.$eurid);
    }
    redirect_to_home_page('modules/exercise/exercise_submit.php?course='.$course_code.'&exerciseId='.$exerciseId);
} // end of submit

    // Number of Attempts
    if ($exerciseAllowedAttempts > 0 && $attempt > $exerciseAllowedAttempts) {
    	$error = 'langExerciseMaxAttemptsReached';
    }
    // Exercise's Expiration
    if (($temp_CurrentDate < $exercise_StartDate) || ($temp_CurrentDate >= $exercise_EndDate)) { 
		$error = 'langExerciseExpired';
    }
    if ($error) {
    	unset($_SESSION['exercise_begin_time'][$exerciseId]);
    	unset($_SESSION['exercise_end_time']);
    	header('Location: exercise_redirect.php?course='.$course_code.'&exerciseId='.$exerciseId.'&error='.$error);
    	exit();
    }

$exerciseDescription_temp = standard_text_escape($exerciseDescription);
$tool_content .= "
 <table width='100%' class='tbl_border'>
  <tr class='odd'>
    <th colspan='2'>";
        if (isset($timeleft) && $timeleft>0) {
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
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions' />";

$i = 0;
foreach ($questionList as $questionId) {
    $i++;
    // for sequential exercises
    if ($exerciseType == 2) {
        // if it is not the right question, goes to the next loop iteration
        if (isset($exerciseResult)) {
            $answered_question_ids = array_keys($exerciseResult);
        } else {
            $answered_question_ids = array();
        }      
        if (in_array($questionId, $answered_question_ids)) {
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

    $tool_content .= "&nbsp;<input type='submit' name='buttonSave' value='$langTemporarySave' />";   

    $tool_content .= "&nbsp;<input type='submit' name='buttonCancel' value='$langCancel' /></div>
        </td>
        </tr>
        <tr>
        <td colspan='2'>&nbsp;</td>
        </tr>
        </table>";
}
$tool_content .= "</form>";

$eurid = $_SESSION['exerciseUserRecordID'][$exerciseId];
$head_content .= "<script type='text/javascript'>            
                $(window).bind('beforeunload', function(){
                    var date = new Date();
                    date.setTime(date.getTime()+(30*1000));
                    var expires = '; expires='+date.toGMTString();                
                    document.cookie = 'inExercise=$exerciseId'+expires;
                    return '$langLeaveExerciseWarning';
                });
                $(window).bind('unload', function(){ 
                    $.ajax({
                      type: 'POST',
                      url: '',
                      data: { action: 'endExerciseNoSubmit', eid: $exerciseId, eurid: $eurid},
                      async: false
                    });
                });                  
    		$(document).ready(function(){
                    $('.exercise').submit(function(){
                            $(window).unbind('beforeunload');
                            $(window).unbind('unload');
                            document.cookie = 'inExercise=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';                            
                    });
                    
                    timer = $('#progresstime');                        
                    timer.time = timer.text();                        
                    timer.text(secondsToHms(timer.time--));
    		    countdown(timer, function() {
    		        $('.exercise').submit();
    		    });
    		});
                $(exercise_enter_handler);</script>";
draw($tool_content, 2, null, $head_content);
