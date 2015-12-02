<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

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
require_once 'modules/gradebook/functions.php';
require_once 'modules/attendance/functions.php';
require_once 'game.php';

$pageName = $langExercicesView;
$picturePath = "courses/$course_code/image";

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

if (!add_units_navigation()) {
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);
}

function unset_exercise_var($exerciseId){
    global $attempt_value;
    unset($_SESSION['objExercise'][$exerciseId]);
    unset($_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value]);
    unset($_SESSION['exerciseResult'][$exerciseId][$attempt_value]);
    unset($_SESSION['questionList'][$exerciseId][$attempt_value]);
    unset($_SESSION['password'][$exerciseId][$attempt_value]);
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

//Identifying ajax request that cancels an active attempt
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($_POST['action'] == 'endExerciseNoSubmit') {   
            
            $exerciseId = $_POST['eid'];             
            $record_end_date = date('Y-m-d H:i:s', time());
            $eurid = $_POST['eurid'];
            Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, attempt_status = ?d, secs_remaining = ?d
                    WHERE eurid = ?d", $record_end_date, ATTEMPT_CANCELED, 0, $eurid);
            triggerGame($course_id, $uid, $exerciseId);
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
            session::Messages($langExerciseNotFound);
            redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
        }
        // saves the object into the session
        $_SESSION['objExercise'][$exerciseId] = $objExercise;        
    }
} else {
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

//Initialize attempts timestamp
if(isset($_POST['attempt_value']) && !isset($_GET['eurId'])){
    $attempt_value = $_POST['attempt_value'];
}elseif (isset($_GET['eurId'])) { //reinitialize paused attempt
    //If there is a paused attempt get it
    $paused_attempt = Database::get()->querySingle("SELECT eurid, record_start_date, secs_remaining FROM exercise_user_record WHERE eurid = ?d AND eid = ?d AND attempt_status = ?d AND uid = ?d", $_GET['eurId'], $exerciseId, ATTEMPT_PAUSED, $uid);
    if ($paused_attempt) {
        $objDateTime = new DateTime($paused_attempt->record_start_date);
        $attempt_value = $objDateTime->getTimestamp();    
    } else {
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
} else {
    $objDateTime = new DateTime('NOW');
    $attempt_value = $objDateTime->getTimestamp();
}
//If the exercise is password protected
$password = $objExercise->selectPasswordLock();
if ($password && !$is_editor) {
    if(!isset($_SESSION['password'][$exerciseId][$attempt_value])) {
        if (isset($_POST['password']) && $password === $_POST['password']) {
            $_SESSION['password'][$exerciseId][$attempt_value] = 1;
        } else {
            Session::Messages($langCaptchaWrong);
            redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
        }
    }
}
//If the exercise is IP protected
$ips = $objExercise->selectIPLock();
if ($ips && !$is_editor){
    $user_ip = $_SERVER["REMOTE_ADDR"];
    if(!match_ip_to_ip_or_cidr($user_ip, explode(',', $ips))){
        Session::Messages($langIPHasNoAccess);
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);                
    }           
}
// if the user has clicked on the "Cancel" button
// ends the exercise and returns to the exercise list
if (isset($_POST['buttonCancel'])) {
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value]; 
        $exercisetotalweight = $objExercise->selectTotalWeighting();
        Database::get()->query("UPDATE exercise_user_record SET record_end_date = NOW(), attempt_status = ?d, total_score = 0, total_weighting = ?d
                WHERE eurid = ?d", ATTEMPT_CANCELED, $exercisetotalweight, $eurid);
        triggerGame($course_id, $uid, $exerciseId);
        Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid = ?d", $eurid);
        unset_exercise_var($exerciseId);
        Session::Messages($langAttemptWasCanceled);
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

load_js('tools.js');

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$randomQuestions = $objExercise->isRandom();
$exerciseType = $objExercise->selectType();
$exerciseTempSave = $objExercise->selectTempSave(); 
$exerciseTimeConstraint = (int) $objExercise->selectTimeConstraint();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$exercisetotalweight = $objExercise->selectTotalWeighting();

$temp_CurrentDate = $recordStartDate = time();
$exercise_StartDate = new DateTime($objExercise->selectStartDate());
$exercise_EndDate = $objExercise->selectEndDate();
$exercise_EndDate = isset($exercise_EndDate) ? new DateTime($objExercise->selectEndDate()) : $exercise_EndDate;

//exercise has ended or hasn't been enabled yet due to declared dates
if (($temp_CurrentDate < $exercise_StartDate->getTimestamp()) || isset($exercise_EndDate) && ($temp_CurrentDate >= $exercise_EndDate->getTimestamp())) {
    //if that happens during an active attempt
    if(isset($_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value])) {
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
        $record_end_date = date('Y-m-d H:i:s', time());
        $totalScore = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE eurid = ?d", $eurid)->weight;
        if ($objExercise->isRandom()) {
            $totalWeighting = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_question WHERE id IN (
                                          SELECT question_id FROM exercise_answer_record WHERE eurid = ?d)", $eurid)->weight;
        } else {
            $totalWeighting = $objExercise->selectTotalWeighting();
        }      
        $objExercise->save_unanswered();
        $unmarked_free_text_nbr = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_answer_record WHERE weight IS NULL AND eurid = ?d", $eurid)->count;
        $attempt_status = ($unmarked_free_text_nbr > 0) ? ATTEMPT_PENDING : ATTEMPT_COMPLETED;        
        Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, total_score = ?f, attempt_status = ?d,
                        total_weighting = ?f WHERE eurid = ?d", $record_end_date, $totalScore, $attempt_status, $totalWeighting, $eurid);
        triggerGame($course_id, $uid, $exerciseId);
        unset_exercise_var($exerciseId);
        Session::Messages($langExerciseExpiredTime);
        redirect_to_home_page('modules/exercise/exercise_result.php?course='.$course_code.'&eurId='.$eurid);
    } else {
        unset_exercise_var($exerciseId);
        Session::Messages($langExerciseExpired);
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
}

//If question list exists in the Session get it for there
//else get it using the apropriate object method and save it to the session
if (isset($_SESSION['questionList'][$exerciseId][$attempt_value])) {
    $questionList = $_SESSION['questionList'][$exerciseId][$attempt_value];
} else {
    if (isset($paused_attempt)) {
        $record_question_ids = Database::get()->queryArray("SELECT DISTINCT question_id FROM exercise_answer_record WHERE eurid = ?d ORDER BY answer_record_id ASC", $paused_attempt->eurid);
        $i=1;
        foreach ($record_question_ids as $row) {
            $questionList[$i] = $row->question_id;
            $i++;
        }        
    } else {
        // selects the list of question ID
        $questionList = $randomQuestions ? $objExercise->selectRandomList() : $objExercise->selectQuestionList(); 
    }
    // saves the question list into the session if there are questions
    if (count($questionList)) {
        $_SESSION['questionList'][$exerciseId][$attempt_value] = $questionList;
    } else {
        unset_exercise_var($exerciseId);
    }
}

$nbrQuestions = count($questionList);


// determine begin time: 
// either from a previews attempt meaning that user hasn't sumbited his answers permanantly  
// 		and exerciseTimeConstrain hasn't yet passed,
// either start a new attempt and count now() as begin time.

if (isset($_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value]) || isset($paused_attempt)) {
    
    $eurid = isset($paused_attempt) ? $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value] = $paused_attempt->eurid : $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
    $recordStartDate = Database::get()->querySingle("SELECT record_start_date FROM exercise_user_record WHERE eurid = ?d", $eurid)->record_start_date;
    $recordStartDate = strtotime($recordStartDate); 
    // if exerciseTimeConstrain has not passed yet calculate the remaining time 
    if ($exerciseTimeConstraint>0) {
        $timeleft = isset($paused_attempt) ? $paused_attempt->secs_remaining : ($exerciseTimeConstraint*60) - ($temp_CurrentDate - $recordStartDate);
    }
} elseif (!isset($_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value]) && $nbrQuestions > 0) {
    $attempt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exerciseId, $uid)->count;

    // Check if allowed number of attempts surpassed and if so redirect 
   if ($exerciseAllowedAttempts > 0 && $attempt >= $exerciseAllowedAttempts) {
        unset_exercise_var($exerciseId);
        Session::Messages($langExerciseMaxAttemptsReached);
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
   } else {
        if ($exerciseAllowedAttempts > 0 && !isset($_POST['acceptAttempt'])) {
            $left_attempts = $exerciseAllowedAttempts - $attempt;
            $tool_content .= "<div class='alert alert-warning text-center'>" .
                ($left_attempts == 1? $langExerciseAttemptLeft: sprintf($langExerciseAttemptsLeft, $left_attempts)) .
                ' ' . $langExerciseAttemptContinue . "</div>
                <div class='text-center'>
                    <form action='{$urlServer}modules/exercise/exercise_submit.php?course=$course_code&exerciseId=$exerciseId' method='post'>
                        <input class='btn btn-primary' id='submit' type='submit' name='acceptAttempt' value='$langContinue'>
                        <a href='{$urlServer}modules/exercise/index.php?course=$course_code' class='btn btn-default'>$langCancel</a>
                    </form>
                </div>";
            unset_exercise_var($exerciseId);
            draw($tool_content, 2, null, $head_content);
            exit;
         }
        // count this as an attempt by saving it as an incomplete record, if there are any available attempts left
        $start = date('Y-m-d H:i:s', $attempt_value);
        $eurid = Database::get()->query("INSERT INTO exercise_user_record (eid, uid, record_start_date, total_score, total_weighting, attempt, attempt_status)
                        VALUES (?d, ?d, ?t, 0, 0, ?d, 0)", $exerciseId, $uid, $start, $attempt+1)->lastInsertID;            
        triggerGame($course_id, $uid, $exerciseId);
        $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value] = $eurid;
        $timeleft = $exerciseTimeConstraint*60;            
   }
}

//if there are answers in the session get them
    if (isset($_SESSION['exerciseResult'][$exerciseId][$attempt_value])) {
            $exerciseResult = $_SESSION['exerciseResult'][$exerciseId][$attempt_value];
    } else {
        if (isset($paused_attempt)) {
            $exerciseResult = $_SESSION['exerciseResult'][$exerciseId][$attempt_value]= $objExercise->get_attempt_results_array($eurid);
        } else {
            $exerciseResult = array();
        }
    }

$questionNum = count($exerciseResult)+1;
// if the user has submitted the form
if (isset($_POST['formSent'])) {
    $choice = isset($_POST['choice']) ? $_POST['choice'] : '';
            
    // checking if user's time is more than exercise's time constrain
    if (isset($exerciseTimeConstraint) && $exerciseTimeConstraint != 0) {
        $nowTime = new DateTime();
        $startTime = new DateTime();
        if (isset($paused_attempt)) {
            $startTime->setTimestamp($exercise_EndDate);
        } else {
            $startTime->setTimestamp($recordStartDate);
        }
        $endTime = new DateTime($startTime->format('Y-m-d H:i:s'));       
        $interval = isset($paused_attempt) ? 'PT'.$paused_attempt->secs_remaining.'S' :'PT'.$exerciseTimeConstraint.'M';
        $endTime->add(new DateInterval($interval));
        if ($endTime < $nowTime) {
            $time_expired = TRUE;                     
        }
    }

    // inserts user's answers in the database and adds them in the $exerciseResult array which is returned

    $action = isset($paused_attempt) ? 'update' : 'insert';

    $exerciseResult = $objExercise->record_answers($choice, $exerciseResult, $action);

    $_SESSION['exerciseResult'][$exerciseId][$attempt_value] = $exerciseResult;
    
    // if it is a non-sequential exercice OR
    // if it is a sequnential exercise in the last question OR the time has expired
    if ($exerciseType == 1 && !isset($_POST['buttonSave']) || $exerciseType == 2 && ($questionNum >= $nbrQuestions || (isset($time_expired) && $time_expired))) {        
        if (isset($_POST['secsRemaining'])) {
            $secs_remaining = $_POST['secsRemaining'];
        } else { 
            $secs_remaining = 0;
        }              
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
        $record_end_date = date('Y-m-d H:i:s', time());
        $totalScore = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE eurid = ?d", $eurid)->weight;
        if ($objExercise->isRandom()) {
            $totalWeighting = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_question WHERE id IN (
                                          SELECT question_id FROM exercise_answer_record WHERE eurid = ?d)", $eurid)->weight;
        } else {
            $totalWeighting = $objExercise->selectTotalWeighting();
        }        

        //If time expired in sequential exercise we must add to the DB the non-given answers
        // to the questions the student didn't had the time to answer
        if (isset($time_expired) && $time_expired && $exerciseType == 2) {
            $objExercise->save_unanswered();
        }
        $unmarked_free_text_nbr = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_answer_record WHERE weight IS NULL AND eurid = ?d", $eurid)->count;
        $attempt_status = ($unmarked_free_text_nbr > 0) ? ATTEMPT_PENDING : ATTEMPT_COMPLETED;
        // record results of exercise
        Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, total_score = ?f, attempt_status = ?d,
                                total_weighting = ?f, secs_remaining = ?d WHERE eurid = ?d", $record_end_date, $totalScore, $attempt_status, $totalWeighting, $secs_remaining, $eurid);
        triggerGame($course_id, $uid, $exerciseId);
        
        if ($attempt_status == ATTEMPT_COMPLETED) {
            // update attendance book
            update_attendance_book($uid, $exerciseId, GRADEBOOK_ACTIVITY_EXERCISE);
            // update gradebook            
            update_gradebook_book($uid, $exerciseId, $totalScore/$totalWeighting, GRADEBOOK_ACTIVITY_EXERCISE);
        }
        unset($objExercise);
        unset_exercise_var($exerciseId);
        // if time expired set flashdata
        if (isset($time_expired) && $time_expired) {
            Session::Messages($langExerciseExpiredTime);
        } else {
            Session::Messages($langExerciseCompleted, 'alert-success');
        }
        redirect_to_home_page('modules/exercise/exercise_result.php?course='.$course_code.'&eurId='.$eurid);
    }
    // if the user has clicked on the "Save & Exit" button
    // keeps the exercise in a pending/uncompleted state and returns to the exercise list    
    if (isset($_POST['buttonSave']) && $exerciseTempSave) {
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
        $secs_remaining = isset($_POST['secsRemaining']) ? $_POST['secsRemaining'] : 0;
        $totalScore = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE eurid = ?d", $eurid)->weight;
        if ($objExercise->isRandom()) {
            $totalWeighting = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_question WHERE id IN (
                                          SELECT question_id FROM exercise_answer_record WHERE eurid = ?d)", $eurid)->weight;
        } else {
            $totalWeighting = $objExercise->selectTotalWeighting();
        }      
        //if we are currently in a previously paused attempt (so this is not the first pause), unanswered are already saved in the DB and they onky need an update
        if (!isset($paused_attempt)) {
            $objExercise->save_unanswered(0); //passing 0 to save like unanswered
        }
        Database::get()->query("UPDATE exercise_user_record SET record_end_date = NOW(), total_score = ?d, total_weighting = ?d, attempt_status = ?d, secs_remaining = ?d
                WHERE eurid = ?d", $totalScore, $totalWeighting, ATTEMPT_PAUSED, $secs_remaining, $eurid);
        triggerGame($course_id, $uid, $exerciseId);
        unset_exercise_var($exerciseId);      
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);        
    } 
//    else {
//        redirect_to_home_page("modules/exercise/exercise_submit.php?course=$course_code&exerciseId=$exerciseId");
//    }
} // end of submit


$exerciseDescription_temp = standard_text_escape($exerciseDescription);
$tool_content .= "<div class='panel panel-primary'>
  <div class='panel-heading'>
    <h3 class='panel-title'>" .
      (isset($timeleft) && $timeleft>0 ?
        "<div class='pull-right'>$langRemainingTime: <span id='progresstime'>" . $timeleft . "</span></div>" : '') .
      q_math($exerciseTitle) . "</h3>
  </div>";
if (!empty($exerciseDescription_temp)) {
    $tool_content .= "<div class='panel-body'>
        $exerciseDescription_temp
      </div>";
}
$tool_content .= "</div><br>";

  
$tool_content .= "
  <form class='form-horizontal exercise' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId".(isset($paused_attempt) ? "&amp;eurId=$eurid" : "")."'>
  <input type='hidden' name='formSent' value='1'>
  <input type='hidden' name='attempt_value' value='$attempt_value'>
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions'>";
        
if (isset($timeleft) && $timeleft>0) {        
  $tool_content .= "<input type='hidden' name='secsRemaining' id='secsRemaining' value='$timeleft' />";
}
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
        } 
    }

    // shows the question and its answers
    $question = new Question();
    $question->read($questionId);
 
    showQuestion($question, $exerciseResult);

    $tool_content .= "<br>";
    // for sequential exercises
    if ($exerciseType == 2) {
        // quits the loop
        break;
    }
} // end foreach()

if (!$questionList) {
    $tool_content .= "
            <div class='alert alert-warning'>$langNoQuestion</div>
            <div class='pull-right'>
                <a href='index.php?course=$course_code' class='btn btn-default'>$langBack</a>
            </div>";
} else {
    $tool_content .= "
        <br>
        <div class='pull-right'><input class='btn btn-default' type='submit' name='buttonCancel' value='$langCancel'>&nbsp;<input class='btn btn-primary' type='submit' value='";
    if ($exerciseType == 1 || $nbrQuestions == $questionNum) {
        $tool_content .= "$langCont' />";
    } else {
        $tool_content .= $langNext . " &gt;" . "' />";
    }
    if ($exerciseTempSave && !($exerciseType == 2 && ($questionNum == $nbrQuestions))) {
        $tool_content .= "&nbsp;<input class='btn btn-primary' type='submit' name='buttonSave' value='$langTemporarySave' />";   
    }
    $tool_content .= "</div>";
}
$tool_content .= "</form>";
if ($questionList) {
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
                    timer = $('#progresstime');                        
                    timer.time = timer.text();                        
                    timer.text(secondsToHms(timer.time--));
                    hidden_timer = $('#secsRemaining');
                    hidden_timer.time = timer.time;                    
                    setInterval(function() {
                        hidden_timer.val(hidden_timer.time--);
                        if (hidden_timer.time + 1 == 0) {
                            clearInterval();
                        }
                    }, 1000);
    		    countdown(timer, function() {
    		        $('.exercise').submit();
    		    });               
                    $('.exercise').submit(function(){
                            $(window).unbind('beforeunload');
                            $(window).unbind('unload');
                            document.cookie = 'inExercise=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';                            
                    });
                    
    		});
                $(exercise_enter_handler);</script>";
}
draw($tool_content, 2, null, $head_content);
