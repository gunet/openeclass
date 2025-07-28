<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

include 'exercise.class.php';
include 'question.class.php';
include 'answer.class.php';
include 'exercise.lib.php';

$require_current_course = true;
$guest_allowed = true;

include '../../include/baseTheme.php';
require_once 'modules/gradebook/functions.php';
require_once 'modules/attendance/functions.php';
require_once 'modules/group/group_functions.php';
require_once 'game.php';
require_once 'analytics.php';
require_once 'include/log.class.php';

$unit = $unit ?? null;
$back_url = $unit?
    "modules/units/index.php?course=$course_code&id=$unit":
    "modules/exercise/index.php?course=$course_code";

$picturePath = "courses/$course_code/image";

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

load_js('jquery-ui');
load_js('jquery-touch');

if (!add_units_navigation()) {
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);
}

function unset_exercise_var($exerciseId) {
    global $attempt_value;
    unset($_SESSION['objExercise'][$exerciseId]);
    unset($_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value]);
    unset($_SESSION['exerciseResult'][$exerciseId][$attempt_value]);
    unset($_SESSION['questionList'][$exerciseId][$attempt_value]);
    unset($_SESSION['password'][$exerciseId][$attempt_value]);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    if (isset($_POST['delete-recording'])) {
        $courseCode = $_GET['course'];
        $delPath = Database::get()->querySingle("SELECT id,`path` FROM document WHERE course_id = ?d AND subsystem = ?d 
                                                    AND subsystem_id = ?d AND lock_user_id = ?d", $course_id, ORAL_QUESTION, $_POST['delete-recording'], $uid);
        unlink("$webDir/courses/$courseCode/image" . $delPath->path);
        Database::get()->query("DELETE FROM document WHERE id = ?d", $delPath->id);
    }
     /* save audio recorded data */
    if (isset($_FILES['audio-blob'])) {
        $courseCode = $_GET['course'];
        $questionId = $_POST['questionId'];
        $file_path = '/' . safe_filename('mp3');
        $filename = 'recording-file.mp3';
        $oldFile = Database::get()->querySingle("SELECT id,`path` FROM document WHERE course_id = ?d AND subsystem = ?d 
                                                    AND subsystem_id = ?d AND lock_user_id = ?d", $course_id, ORAL_QUESTION, $questionId, $uid);

        if ($oldFile && file_exists("$webDir/courses/$courseCode/image" . $oldFile->path)) {
            unlink("$webDir/courses/$courseCode/image" . $oldFile->path);
            Database::get()->query("DELETE FROM document WHERE id = ?d", $oldFile->id);
        }
        if (move_uploaded_file($_FILES['audio-blob']['tmp_name'], "$webDir/courses/$courseCode/image/$file_path")) {
            $file_creator = "$_SESSION[givenname] $_SESSION[surname]";
            $file_date = date('Y-m-d G:i:s');
            $file_format = 'mp3';
            $q = Database::get()->query("INSERT INTO document SET
                course_id = ?d,
                subsystem = ?d,
                subsystem_id = ?d,
                path = ?s,
                extra_path = '',
                filename = ?s,
                visible = 1,
                comment = '',
                category = 0,
                title = ?s,
                creator = ?s,
                date = ?s,
                date_modified = ?s,
                subject = '',
                description = '',
                author = ?s,
                format = ?s,
                language = ?s,
                copyrighted = 0,
                editable = 0,
                lock_user_id = ?d",
                $course_id, ORAL_QUESTION, $questionId, $file_path,
                $filename, $filename, $file_creator,
                $file_date, $file_date, $file_creator, $file_format,
                $language, $uid);

            if ($q) { 
                $newFilePath = Database::get()->querySingle("SELECT `path` FROM document WHERE id = ?d", $q->lastInsertID)->path;
                $fPath = $urlServer . "courses/$course_code/image" . $newFilePath;
                echo json_encode(['newFilePath' => $fPath]); 
            }
        }
    }
    exit;
}

// Does nothing, just refreshes the session
if (isset($_POST['action']) and $_POST['action'] == 'refreshSession') {
    exit();
}

// POST request that cancels an active attempt (sent via onBeforeUnload)
if (isset($_POST['action']) and $_POST['action'] == 'endExerciseNoSubmit') {
    $exerciseId = $_POST['eid'];
    $record_end_date = date('Y-m-d H:i:s', time());
    $eurid = $_POST['eurid'];
    Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, attempt_status = ?d, secs_remaining = ?d
        WHERE eurid = ?d", $record_end_date, ATTEMPT_CANCELED, 0, $eurid);
    Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, [
        'title' => $_SESSION['objExercise'][$exerciseId]->selectTitle(),
        'legend' => $langCancel,
        'eurid' => $eurid ]);
    unset_exercise_var($exerciseId);
    exit();
}
//$_SESSION['objExercise'][$exerciseId]->selectTitle()
// setting a cookie in onBeforeUnload event in order to redirect user to the exercises page in case of refresh
// as the synchronous ajax call in onUnload event doen't work the same in all browsers in case of refresh
// (It is executed after page load in Chrome and Mozilla and before page load in IE).
// In current functionality if user leaves the exercise for another module the cookie will expire anyway in 30 seconds,
// or it will be unset by the exercises page (index.php). If user who left an exercise for another module
// visits through a direct link a specific execise page before the 30 seconds time frame
// he will be redirected to the exercises page (index.php)
if (isset($_COOKIE['inExercise'])) {
    setcookie("inExercise", "", time() - 3600);
    redirect_to_home_page($back_url);
}

// Check if an exercise ID exists in the URL
// and if so it gets the exercise object either by the session (if it exists there)
// or by initializing it using the exercise ID
if (isset($_REQUEST['exerciseId'])) {
    $exerciseId = intval($_REQUEST['exerciseId']);
    // Check if exercise object exists in session
    if (isset($_SESSION['objExercise'][$exerciseId])) {
        $objExercise = $_SESSION['objExercise'][$exerciseId];
    } else {
        // construction of Exercise
        $objExercise = new Exercise();
        // if the specified exercise is disabled (this only applies to students)
        // or doesn't exist, redirect and show error
        if (!$objExercise->read($exerciseId) || (!$is_editor && $objExercise->selectStatus($exerciseId) == 0)) {
            Session::flash('message', $langExerciseNotFound);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page($back_url);
        }
        // saves the object into the session
        $_SESSION['objExercise'][$exerciseId] = $objExercise;
    }
} else {
    redirect_to_home_page($back_url);
}

// check if exercise is `exam` type
if ($objExercise->isExam()) {
    if (!($is_admin or $is_editor or user_is_registered_to_course($uid, $course_id))) {
        Session::flash('message', $langExerciseRequireLogin);
        Session::flash('alert-class', 'alert-warning');
        $next = str_replace($urlAppend, '/', $_SERVER['REQUEST_URI']);
        header("Location:" . $urlServer . "main/login_form.php?next=" . urlencode($next));
    }
}

$pageName = $objExercise->selectTitle();
// If the exercise is assigned to specific users / groups
if ($objExercise->selectAssignToSpecific() and !$is_editor) {
    $assignees = Database::get()->queryArray('SELECT user_id, group_id
        FROM exercise_to_specific WHERE exercise_id = ?d', $exerciseId);
    $accessible = false;
    if ($uid > 0) { // we are logged in
        foreach ($assignees as $item) {
            if ($item->user_id == $uid) {
                $accessible = true;
                break;
            } elseif ($item->group_id) {
                if (!isset($groups)) {
                    $groups = user_group_info($uid, $course_id);
                }
                if (isset($groups[$item->group_id])) {
                    $accessible = true;
                    break;
                }
            }
        }
    }
    if (!$accessible) {
        Session::flash('message',$langNoAccessPrivilages);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page($back_url);
    }
}

// Initialize attempts timestamp
if (isset($_POST['attempt_value']) && !isset($_GET['eurId'])) {
    $attempt_value = $_POST['attempt_value'];
} elseif (isset($_GET['eurId'])) { // reinitialize paused attempt
    // If there is a paused or recent incomplete attempt get it
    $eurid = $_GET['eurId'];
    $paused_attempt = Database::get()->querySingle("SELECT eurid, record_start_date, secs_remaining
        FROM exercise_user_record
        WHERE eurid = ?d AND
              eid = ?d AND
              attempt_status = ?d OR
                  (attempt_status = ?d AND TIME_TO_SEC(TIMEDIFF(" . DBHelper::timeAfter() . ", record_end_date)) < ?d) AND
              uid = ?d", $eurid, $exerciseId, ATTEMPT_PAUSED, ATTEMPT_ACTIVE, 300, $uid);
    if ($paused_attempt) {
        $objDateTime = new DateTime($paused_attempt->record_start_date);
        $attempt_value = $objDateTime->getTimestamp();
        // Regenerate eurid so that attempt can't be restarted from other browser
        unset($_SESSION['exerciseResult'][$exerciseId][$attempt_value]);
        Database::get()->transaction(function () {
            global $eurid, $paused_attempt;
            $new_eurid = Database::get()->query('INSERT INTO exercise_user_record
                (eid, uid, record_start_date, record_end_date, total_score,
                 total_weighting, attempt, attempt_status, secs_remaining,
                 assigned_to)
                SELECT eid, uid, record_start_date, ' . DBHelper::timeAfter() . ', total_score,
                       total_weighting, attempt, ?d, secs_remaining,
                       assigned_to FROM exercise_user_record WHERE eurid = ?d',
                ATTEMPT_ACTIVE, $eurid)->lastInsertID;
            if ($new_eurid) {
                Database::get()->query('UPDATE exercise_answer_record
                    SET eurid = ?d WHERE eurid = ?d', $new_eurid, $eurid);
                Database::get()->query('DELETE FROM exercise_user_record
                    WHERE eurid = ?d', $eurid);
                $paused_attempt->eurid = $eurid = $new_eurid;
            }
        });
        $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value] = $eurid;
    } else {
        redirect_to_home_page($back_url);
    }
    Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, [
                            'title' => $objExercise->selectTitle(),
                            'legend' => $langContinueAttempt,
                            'eurid' => $eurid ]);
} else {
    $objDateTime = new DateTime('NOW');
    $attempt_value = $objDateTime->getTimestamp();
}



if (!isset($_POST['acceptAttempt']) and (!isset($_POST['formSent']))) {
    // If the exercise is password protected
    $password = $objExercise->selectPasswordLock();
    if ($password && !$is_editor) {
        if(!isset($_SESSION['password'][$exerciseId][$attempt_value])) {
            if (isset($_POST['password']) && $password === $_POST['password']) {
                $_SESSION['password'][$exerciseId][$attempt_value] = 1;
            } else {
                Session::flash('message',$langWrongPassword);
                Session::flash('alert-class', 'alert-warning');
                redirect_to_home_page($back_url);
            }
        }
    }
}


// If the exercise is IP protected
$ips = $objExercise->selectIPLock();
if ($ips && !$is_editor){
    $user_ip = Log::get_client_ip();
    if(!match_ip_to_ip_or_cidr($user_ip, explode(',', $ips))){
        Session::flash('message',$langIPHasNoAccess);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page($back_url);
    }
}

// If the user has clicked on the "Cancel" button,
// end the exercise and return to the exercise list
if (isset($_POST['buttonCancel'])) {


    $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
    Database::get()->query("UPDATE exercise_user_record
        SET record_end_date = " . DBHelper::timeAfter() . ", attempt_status = ?d, total_score = 0, total_weighting = 0
        WHERE eurid = ?d", ATTEMPT_CANCELED, $eurid);

    Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, [
        'title' => $objExercise->selectTitle(),
        'legend' => $langCancel,
        'eurid' => $eurid ]);
    unset_session_variables_of_questions($eurid, 'cancel_exercise');
    unset_exercise_var($exerciseId);
    Session::flash('message',$langAttemptWasCanceled);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page($back_url);
}

load_js('tools.js');

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$shuffleQuestions = $objExercise->selectShuffle();
$randomQuestions = $objExercise->isRandom();
$exerciseType = $objExercise->selectType();
$exerciseTempSave = $objExercise->selectTempSave();
$exerciseTimeConstraint = $objExercise->selectTimeConstraint();
$exerciseAllowedAttempts = $objExercise->selectAttemptsAllowed();
$exercisetotalweight = $objExercise->selectTotalWeighting();

// In this php block we have been saving the subset of predefined answers in ordering question
// when the exercise has MULTIPLE_PAGE_TYPE type or user press the save button.
if (($exerciseType == MULTIPLE_PAGE_TYPE || isset($_POST['buttonSave'])) && isset($_POST['choice'])) {
    // $arrKey = question Id
    $arrayKeys = array_keys($_POST['choice']);
    if (count($arrayKeys) > 0) {
        foreach ($arrayKeys as $arrKey) {
            $CurrentQuestion = new Question();
            $CurrentQuestion->read($arrKey);
            if ($CurrentQuestion->selectType() == ORDERING) {
                if (!empty($_POST['subsetKeys'][$arrKey])) {
                    $arr2 = json_decode($_POST['subsetKeys'][$arrKey], true);
                    $_SESSION['OrderingSubsetKeys'][$uid][$arrKey] = $arr2;
                }
            }
        }
    }
}

$questionOptions = [];
$shuffle_answers = $objExercise->getOption('ShuffleAnswers')? 1: 0;
if ($shuffle_answers) {
    $questionOptions[] = 'shuffle_answers';
}
$exercisePreventCopy = $objExercise->getOption('jsPreventCopy')? 1: 0;
if ($exercisePreventCopy) {
    $questionOptions[] = 'prevent_copy_paste';
}

$is_exam = $objExercise->isExam();
if ($is_exam) { // disallow links outside exercise frame. disallow button quick note
    $head_content .= "
        <script type='text/javascript'>
            $(function() {
                $('.btn-quick-note').remove();
                $('a:not(#exercise_frame a)').css('cursor', 'not-allowed');
                $('div:not(#exercise_frame)').css('cursor', 'not-allowed');
                $('a:not(#exercise_frame a)').on('click', function (e) {
                    e.preventDefault();
                    return false;
                });
            });
    </script>";
}

$temp_CurrentDate = $recordStartDate = time();
$exercise_StartDate = new DateTime($objExercise->selectStartDate());
$exercise_EndDate = $objExercise->selectEndDate();
$exercise_EndDate = isset($exercise_EndDate) ? new DateTime($objExercise->selectEndDate()) : $exercise_EndDate;
$choice = $_POST['choice'] ?? '';

// If there are answers in the session get them
if (isset($_SESSION['exerciseResult'][$exerciseId][$attempt_value])) {
    $exerciseResult = $_SESSION['exerciseResult'][$exerciseId][$attempt_value];
} else {
    if (isset($paused_attempt)) {
        $exerciseResult = $_SESSION['exerciseResult'][$exerciseId][$attempt_value] = $objExercise->get_attempt_results_array($eurid);
    } else {
        $exerciseResult = array();
    }
}

// exercise has ended or hasn't been enabled yet due to declared dates or was submitted automatically due to expiring time
$autoSubmit = isset($_POST['autoSubmit']) && $_POST['autoSubmit'] == 'true';
if ($temp_CurrentDate < $exercise_StartDate->getTimestamp()
    or (isset($exercise_EndDate) && ($temp_CurrentDate >= $exercise_EndDate->getTimestamp()))
    or $autoSubmit) {
    if ($is_editor) {
        // Allow editors to test expired or not yet started exercises, but warn them
        if (!isset($_POST['buttonFinish']) and !$autoSubmit) {
            Session::flash('message',$langExerciseExpired);
            Session::flash('alert-class', 'alert-warning');
        }
    } else {
        // if that happens during an active attempt
        if (isset($_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value])) {
            $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
            $record_end_date = date('Y-m-d H:i:s', time());
            $objExercise->save_unanswered();
            $objExercise->record_answers($choice, $exerciseResult, 'update');
            $totalScore = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE eurid = ?d", $eurid)->weight;
            $totalWeighting = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_question WHERE id IN (
                                          SELECT question_id FROM exercise_answer_record WHERE eurid = ?d)", $eurid)->weight;
            $unmarked_free_text_nbr = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_answer_record WHERE weight IS NULL AND eurid = ?d", $eurid)->count;
            $attempt_status = ($unmarked_free_text_nbr > 0) ? ATTEMPT_PENDING : ATTEMPT_COMPLETED;
            $totalWeighting = is_null($totalWeighting)? 0: $totalWeighting;
            $totalScore = is_null($totalScore)? 0: $totalScore;
            Database::get()->query("UPDATE exercise_user_record SET record_end_date = ?t, total_score = ?f, attempt_status = ?d,
                            total_weighting = ?f WHERE eurid = ?d", $record_end_date, $totalScore, $attempt_status, $totalWeighting, $eurid);
            // update attendance book
            update_attendance_book($uid, $objExercise->selectId(), GRADEBOOK_ACTIVITY_EXERCISE);
            // update gradebook
            if (is_null($totalWeighting) or $totalWeighting == 0) {
                update_gradebook_book($uid, $objExercise->selectId(), 0, GRADEBOOK_ACTIVITY_EXERCISE);
            } else {
                update_gradebook_book($uid, $objExercise->selectId(), $totalScore/$totalWeighting, GRADEBOOK_ACTIVITY_EXERCISE);
            }
            // update user progress
            triggerGame($course_id, $uid, $objExercise->selectId());
            triggerExerciseAnalytics($course_id, $uid, $objExercise->selectId());
            Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, [
                'title' => $objExercise->selectTitle(),
                'legend' => $langSubmit,
                'eurid' => $eurid ]);
            unset_exercise_var($exerciseId);
            Session::flash('message',$langExerciseExpiredTime);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page($back_url);
        } else {
            unset_exercise_var($exerciseId);
            Session::flash('message',$langExerciseExpired);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page($back_url);
        }
    }
}


// If question list exists in the Session get it for there
// else get it using the appropriate object method and save it to the session
if (isset($_SESSION['questionList'][$exerciseId][$attempt_value])) {
    $questionList = $_SESSION['questionList'][$exerciseId][$attempt_value];
} else {
    if (isset($paused_attempt)) {
        $record_question_ids = Database::get()->queryArray("SELECT question_id, q_position, is_answered
            FROM exercise_answer_record WHERE eurid = ?d GROUP BY question_id, q_position, is_answered ORDER BY q_position", $paused_attempt->eurid);
        foreach ($record_question_ids as $row) {
            $questionList[$row->q_position] = $row->question_id;
            if ($row->is_answered == 2 or !isset($pausedQuestionNumber)) {
                $pausedQuestionNumber = $row->q_position;
            }
        }
    } else {
        // selects the list of question ID
        $questionList = [];
        if ($shuffleQuestions or (intval($randomQuestions) > 0)) {
            $qList = $objExercise->selectShuffleQuestions();
        } else {
            $qList = $objExercise->selectQuestions();
        }
        $qList = array_unique($qList); // avoid duplicates (if any)
        $i = 1;
        foreach ($qList as $data) { // just make sure that array key / values are ok
            $questionList[$i] = $data;
            $i++;
        }
    }
    // saves the question list into the session if there are questions
    if (count($questionList) > 0) {
        $_SESSION['questionList'][$exerciseId][$attempt_value] = $questionList;
    } else {
        unset_exercise_var($exerciseId);
    }
}

$nbrQuestions = count($questionList);

// determine begin time:
// either from a previews attempt meaning that user hasn't submitted his answers permanently
// and exerciseTimeConstrain hasn't yet passed,
// either start a new attempt and count now() as begin time.

if (isset($_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value]) || isset($paused_attempt)) {
    $eurid = isset($paused_attempt) ? $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value] = $paused_attempt->eurid : $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
    $record = Database::get()->querySingle("SELECT record_start_date, record_end_date, secs_remaining FROM exercise_user_record WHERE eurid = ?d", $eurid);
    $recordStartDate = strtotime($record->record_start_date);
    if (isset($paused_attempt) and $paused_attempt->secs_remaining) {
        // resume paused attempt with same time left
        $timeleft = $paused_attempt->secs_remaining;
    } elseif ($record->record_end_date and $record->secs_remaining) {
        // navigation within exercise: subtract time from last submission timestamp
        $recordEndDate = strtotime($record->record_end_date);
        $timeleft = $record->secs_remaining - ($temp_CurrentDate - $recordEndDate);
    } elseif ($exerciseTimeConstraint > 0) {
        // if exerciseTimeConstrain has not passed yet calculate the remaining time
        $timeleft = $exerciseTimeConstraint * 60 - ($temp_CurrentDate - $recordStartDate);
    }
} elseif (!isset($_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value]) && $nbrQuestions > 0) {
    $attempt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exerciseId, $uid)->count;

    // Check if allowed number of attempts exceeded and if so redirect
    if ($exerciseAllowedAttempts > 0 && $attempt >= $exerciseAllowedAttempts) {
        unset_exercise_var($exerciseId);
        Session::flash('message',$langExerciseMaxAttemptsReached);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page($back_url);
    } else {
        if ($exerciseAllowedAttempts > 0 && !isset($_POST['acceptAttempt'])) {
            $left_attempts = $exerciseAllowedAttempts - $attempt;
            if ($unit) {
                $form_next_link = "{$urlAppend}modules/units/view.php?course=$course_code&res_type=exercise&exerciseId=$exerciseId&unit=$unit";
                $form_cancel_link = "{$urlAppend}modules/units/index.php?course=$course_code&id=$unit";
            } else {
                $form_next_link = "{$urlAppend}modules/exercise/exercise_submit.php?course=$course_code&exerciseId=$exerciseId";
                $form_cancel_link = "{$urlAppend}modules/exercise/index.php?course=$course_code";
            }
            $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>" .
                ($left_attempts == 1 ? $langExerciseAttemptLeft : sprintf($langExerciseAttemptsLeft, $left_attempts)) .
                ' ' . $langExerciseAttemptContinue . "</span></div>
                <form action='$form_next_link' method='post'>
                    <div class='col-12 d-flex justify-content-center align-items-center gap-2 flex-wrap' style='margin-top:70px;'>
                            <input class='btn successAdminBtn' style='float: right;' id='submit' type='submit' name='acceptAttempt' value='$langContinue'>
                            <a href='$form_cancel_link' class='btn cancelAdminBtn'>$langCancel</a>
                    </div>
                </form>";
            unset_exercise_var($exerciseId);
            draw($tool_content, 2, null, $head_content);
            exit;
        }
        // count this as an attempt by saving it as an incomplete record, if there are any available attempts left
        $start = date('Y-m-d H:i:s', $attempt_value);
        if ($exerciseTimeConstraint) {
            $timeleft = $exerciseTimeConstraint * 60;
        }
        $eurid = Database::get()->query("INSERT INTO exercise_user_record
        (eid, uid, record_start_date, total_score, total_weighting, attempt, attempt_status, secs_remaining)
        VALUES (?d, ?d, ?t, 0, 0, ?d, 0, ?d)",
            $exerciseId, $uid, $start, $attempt + 1, isset($timeleft) ? $timeleft : 0)->lastInsertID;
        $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value] = $eurid;
        Log::record($course_id, MODULE_ID_EXERCISE, LOG_INSERT, [
            'title' => $objExercise->selectTitle(),
            'legend' => $langStart,
            'eurid' => $eurid ]);
        if ($exerciseType == ONE_WAY_TYPE) {
            Session::flash('message',$langWarnOneWayExercise);
            Session::flash('alert-class', 'alert-warning');
        }
    }
}

if ($exercise_EndDate) {
    $exerciseTimeLeft = $exercise_EndDate->getTimestamp() - $temp_CurrentDate;
    if ($exerciseTimeLeft) {
        if ($exerciseTimeLeft < 0 and $is_editor) {
            // Give editors unlimited time to test expired exercises
            unset($timeleft);
        } elseif ($exerciseTimeLeft < 3 * 3600 and (!isset($timeleft) or $exerciseTimeLeft < $timeleft)) {
            // Display countdown of exercise remaining time if less than
            // user's remaining time or less than 3 hours away
            $timeleft = $exerciseTimeLeft;
        }
    }
}

$questionNum = count($exerciseResult) + 1;
// if the user has submitted the form
if (isset($_POST['formSent'])) {
    $time_expired = false;
    // check if user's time expired
    if (isset($timeleft) and $timeleft <= 0) {
        $time_expired = true;
    }

    // insert answers in the database and add them in the $exerciseResult array which is returned
    $action = isset($paused_attempt) ? 'update' : 'insert';
    $exerciseResult = $objExercise->record_answers($choice, $exerciseResult, $action);
    $questionNum = count($exerciseResult) + 1;
    Database::get()->query('UPDATE exercise_user_record
        SET record_end_date = ' . DBHelper::timeAfter() . ', secs_remaining = ?d
        WHERE eurid = ?d', isset($timeleft)? $timeleft: 0, $eurid);

    $_SESSION['exerciseResult'][$exerciseId][$attempt_value] = $exerciseResult;

    // if the user has made a final submission or the time has expired
    if (isset($_POST['buttonFinish']) or $time_expired) {


        if (isset($_POST['secsRemaining'])) {
            $secs_remaining = $_POST['secsRemaining'];
        } else {
            $secs_remaining = 0;
        }
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
        $totalScore = $objExercise->calculate_total_score($eurid);
        $exerciseFeedback = $objExercise->selectFeedback();

        if ($objExercise->isRandom() or $objExercise->hasQuestionListWithRandomCriteria()) {
            $totalWeighting = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_question WHERE id IN (
                                          SELECT question_id FROM exercise_answer_record WHERE eurid = ?d)", $eurid)->weight;
        } else {
            $totalWeighting = $objExercise->selectTotalWeighting();
        }
        $unmarked_free_text_nbr = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_answer_record WHERE weight IS NULL AND eurid = ?d", $eurid)->count;
        $attempt_status = ($unmarked_free_text_nbr > 0) ? ATTEMPT_PENDING : ATTEMPT_COMPLETED;
        // record results of exercise
        Database::get()->query("UPDATE exercise_user_record
            SET total_score = ?f, attempt_status = ?d, total_weighting = ?f
            WHERE eurid = ?d", $totalScore, $attempt_status, $totalWeighting, $eurid);

        if ($attempt_status == ATTEMPT_COMPLETED) {
            // update attendance book
            update_attendance_book($uid, $exerciseId, GRADEBOOK_ACTIVITY_EXERCISE);
            // update gradebook
            if (is_null($totalWeighting) or $totalWeighting == 0) {
                update_gradebook_book($uid, $objExercise->selectId(), 0, GRADEBOOK_ACTIVITY_EXERCISE);
            } else {
                update_gradebook_book($uid, $exerciseId, $totalScore/$totalWeighting, GRADEBOOK_ACTIVITY_EXERCISE);
            }
            // update user progress
            triggerGame($course_id, $uid, $exerciseId);
            triggerExerciseAnalytics($course_id, $uid, $exerciseId);
            Log::record($course_id, MODULE_ID_EXERCISE, LOG_MODIFY, [
                'title' => $objExercise->selectTitle(),
                'legend' => $langSubmit,
                'eurid' => $eurid ]);
        }
        unset_session_variables_of_questions($eurid);
        unset($objExercise);
        unset_exercise_var($exerciseId);
        // if time expired set flashdata
        if ($time_expired) {
            Session::flash('message',$langExerciseExpiredTime);
            Session::flash('alert-class', 'alert-warning');
        } else {
            if (!empty($exerciseFeedback)) {
                Session::flash('message', $exerciseFeedback);
                Session::flash('alert-class', 'alert-success');
            } else{
                Session::flash('message', $langExerciseCompleted);
                Session::flash('alert-class', 'alert-success');
            }
        }
        if ($unit) {
            redirect_to_home_page("modules/units/view.php?course=$course_code&eurId=$eurid&res_type=exercise_results&unit=$unit");
        } else {
            redirect_to_home_page("modules/exercise/exercise_result.php?course=$course_code&eurId=$eurid");
        }
    }
    // if the user has clicked on the "Save & Exit" button
    // keeps the exercise in a pending/uncompleted state and returns to the exercise list
    if (isset($_POST['buttonSave']) && $exerciseTempSave) {
        $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
        $secs_remaining = isset($timeleft) ? $timeleft : 0;
        $totalScore = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE eurid = ?d", $eurid)->weight;
        if ($objExercise->isRandom()) {
            $totalWeighting = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_question WHERE id IN (
                                          SELECT question_id FROM exercise_answer_record WHERE eurid = ?d)", $eurid)->weight;
        } else {
            $totalWeighting = $objExercise->selectTotalWeighting();
        }
        // If we are currently in a previously paused attempt (so this is not
        // the first pause), unanswered are already saved in the DB and they
        // only need an update
        if (!isset($paused_attempt)) {
            $objExercise->save_unanswered();
        }

        Database::get()->query("UPDATE exercise_user_record SET record_end_date = " . DBHelper::timeAfter() . ", total_score = ?f, total_weighting = ?f, attempt_status = ?d, secs_remaining = ?d
                WHERE eurid = ?d", floatval($totalScore), floatval($totalWeighting), ATTEMPT_PAUSED, $secs_remaining, $eurid);
        if (($exerciseType == MULTIPLE_PAGE_TYPE or $exerciseType == ONE_WAY_TYPE) and isset($_POST['choice']) and is_array($_POST['choice'])) {
            // for sequential exercises, return to current question
            // by setting is_answered to a special value
            $qid = array_keys($_POST['choice']);
            Database::get()->query('UPDATE exercise_answer_record SET is_answered = 2
                WHERE eurid = ?d AND question_id = ?d', $eurid, $qid);
        }
        unset_exercise_var($exerciseId);
        redirect_to_home_page($back_url);
    }
}

if (isset($timeleft)) { // time remaining
    if ($timeleft <= 1) {
        $timeleft = 1;
    }
    $tool_content .= "<div class='alert alert-warning time-remaining-warning'><i class='fa-solid fa-triangle-exclamation fa-lg pt-1'></i><span>";
    $tool_content .= "<div class='col-sm-12'><h4 class='d-flex align-items-center gap-2 mb-0'>$langRemainingTime: <span id='progresstime'>$timeleft</span></h4></div>";
    $tool_content .= "</span></div>";
}

if (!empty($exerciseDescription)) { // description
    $tool_content .= "<div class='col-sm-12 mb-4'><div class='card panelCard card-default px-lg-4 py-lg-3'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'><h3>$langDescription</h3></div>";
    $tool_content .= "<div class='card-body'><em>" . standard_text_escape($exerciseDescription) . "</em></div>";
    $tool_content .= "</div></div>";
}

if ($unit) {
    $form_action_link = "{$urlServer}modules/units/view.php?res_type=exercise&amp;unit=$_REQUEST[unit]&amp;course=$course_code&amp;exerciseId=$exerciseId";
} else if (isset($_REQUEST['res_type'])) {
    $form_action_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;res_type=exercise&amp;exerciseId=$exerciseId";
} else {
    $form_action_link = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId";
}

$tool_content .= "<div id='exercise_frame'>
  <form class='form-horizontal exercise exercise-prevent-copy' role='form' method='post' action='$form_action_link' autocomplete='off'>
  <input type='hidden' name='formSent' value='1'>
  <input type='hidden' name='attempt_value' value='$attempt_value'>
  <input type='hidden' name='nbrQuestions' value='$nbrQuestions'>";

if (isset($timeleft)) {
    $tool_content .= "<input type='hidden' name='secsRemaining' id='secsRemaining' value='$timeleft'>";
}

$unansweredIds = $answeredIds = array();

if ($exerciseType == MULTIPLE_PAGE_TYPE or $exerciseType == ONE_WAY_TYPE) {
    $eurid = $_SESSION['exerciseUserRecordID'][$exerciseId][$attempt_value];
}

$questions = [];
// check for unanswered questions
foreach ($questionList as $k => $q_id) {
    $answered = false;
    $q_id = $questionList[$k];
    $t_question = new Question();
    $t_question->read($q_id);
    $questions[$q_id] = $t_question;
    if (($t_question->selectType() == UNIQUE_ANSWER or $t_question->selectType() == MULTIPLE_ANSWER or $t_question->selectType() == TRUE_FALSE or $t_question->selectType() == ORDERING)
        and array_key_exists($q_id, $exerciseResult) and $exerciseResult[$q_id] != 0) {
        $answered = true;
    } elseif (($t_question->selectType() == FILL_IN_BLANKS or $t_question->selectType() == FILL_IN_BLANKS_TOLERANT)
        and array_key_exists($q_id, $exerciseResult)) {
        if (is_array($exerciseResult[$q_id])) {
            $answered = true;
            foreach ($exerciseResult[$q_id] as $key => $value) {
                if (trim($value) == '') {  // check if we have filled all blanks
                    $answered = false;
                    break;
                }
            }
        }
    } elseif ($t_question->selectType() == FREE_TEXT
        and array_key_exists($q_id, $exerciseResult) and trim($exerciseResult[$q_id]) !== '') { // button color is `blue` if we have type anything
        $answered = true;
    } elseif (($t_question->selectType() == MATCHING or $t_question->selectType() == FILL_IN_FROM_PREDEFINED_ANSWERS) and array_key_exists($q_id, $exerciseResult)) {
        if (is_array($exerciseResult[$q_id])) {
            $answered = true;
            foreach ($exerciseResult[$q_id] as $key => $value) {
                if ($value == 0) {  // check if we have done all matches
                    $answered = false;
                    break;
                }
            }
        }
    } elseif (($t_question->selectType() == DRAG_AND_DROP_TEXT or $t_question->selectType() == DRAG_AND_DROP_MARKERS)
        and array_key_exists($q_id, $exerciseResult)) {
        $answered = true;
        if (empty($exerciseResult[$q_id])) {
            $answered = false;
        } elseif (is_string($exerciseResult[$q_id]) && !empty($exerciseResult[$q_id])) {
            $arrAn = json_decode($exerciseResult[$q_id], true);
            foreach ($arrAn as $index => $value) {
                if (empty($value['dataWord'])) {
                    $answered = false;
                    break;
                }
            }
        } elseif (is_array($exerciseResult[$q_id])) {
            foreach ($exerciseResult[$q_id] as $val) {
                if ($val == '') {
                    $answered = false;
                    break;
                }
            }
        } 
    } elseif ($t_question->selectType() == CALCULATED and array_key_exists($q_id, $exerciseResult) 
        and (strpos($exerciseResult[$q_id], '|') !== false)) { // This question is calculated type with unique answer as text and the user has not answered it.
        $answered = true;
    }
    if ($answered) {
        $answeredIds[] = $q_id;
    } else {
        $unansweredIds[] = $q_id;
    }
}

if ($questionList) {
    if ($exerciseType == SINGLE_PAGE_TYPE) {
        foreach ($questionList as $questionNumber => $questionId) {
            // show the question and its answers
            showQuestion($questions[$questionId], $questionNumber, $exerciseResult, options: $questionOptions);
        }
    } else {
        if (isset($pausedQuestionNumber)) { // restarting paused attempt
            $questionNumber = $pausedQuestionNumber;
            Database::get()->query('UPDATE exercise_answer_record SET is_answered = 1
                WHERE eurid = ?d AND is_answered = 2', $eurid);
        } elseif ($exerciseType == MULTIPLE_PAGE_TYPE and isset($_REQUEST['q_id'])) { // we come from pagination buttons
            $questionNumber = intval($_REQUEST['q_id']); // only number
        } elseif (isset($_REQUEST['questionId'])) { // we come from prev / next buttons
            if ($exerciseType == MULTIPLE_PAGE_TYPE and isset($_REQUEST['prev'])) { // previous
                $questionNumber = array_search($_REQUEST['questionId'], $questionList) - 1;
            } else { // next
                $questionNumber = array_search($_REQUEST['questionId'], $questionList) + 1;
            }
        } else { // starting multipage exercise from first question
            $questionNumber = 1;
        }
        $questionId = $questionList[$questionNumber];

        if ($exerciseType == MULTIPLE_PAGE_TYPE) {
            // display question numbering buttons
            $tool_content .= "<div class='card panelCard card-transparent p-0 border-0'>";
            $tool_content .= "<div class='card-body p-0 border-0'>";
            $tool_content .= "<div class='d-flex justify-content-center p-0 flex-wrap gap-2 border-0'>";
            foreach ($questionList as $k => $q_id) {
                $answered = in_array($q_id, $answeredIds);
                if ($answered) {
                    $class = 'submitAdminBtn';
                    $title = q($langHasAnswered);
                } else {
                    $class = 'submitAdminBtnClassic';
                    $title = q($langPendingAnswered);
                }
                if ($questionNumber == $k) { // we are in the current question
                    $extra_style = "style='outline: 2px solid #3584e4; outline-offset: 2px;'";
                } else {
                    $extra_style = '';
                }
                $tool_content .= "
                    <div class='p-2' style='display: inline-block; margin-right: 10px;'>
                        <input class='btn $class' $extra_style type='submit' name='q_id' id='q_num$k' value='$k' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title='$title'>
                    </div>";
            }
            $tool_content .= "</div></div></div>";
        }

        $question = $questions[$questionList[$questionNumber]];
        showQuestion($question, $questionNumber, $exerciseResult, options: $questionOptions);
    }
} else {
    $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoQuestion</span></div></div>";
    $backlink = $unit?
        "index.php?course=$course_code&amp;id=$unit":
        "index.php?course=$course_code";
    $tool_content .= "<div class='float-end'><a href='$backlink' class='btn cancelAdminBtn'>$langBack</a></div>";
}

// "Temporary save" button
if ($uid and $exerciseTempSave) {
    $tempSaveButton = "<input class='btn submitAdminBtn blockUI' type='submit' name='buttonSave' value='$langTemporarySave'>";
} else {
    $tempSaveButton = '';
}

// Navigation buttons (previous / next)
$isFinalQuestion = 'false';
if ($exerciseType != SINGLE_PAGE_TYPE) {
    $head_content .= "<style>
            @media only screen and (max-width: 680px) {
                .exercise-nav-buttons { width: 100%; }
                .exercise-action-buttons { text-align: center; }
            }
            @media only screen and (max-width: 460px) {
                .exercise-nav-buttons { text-align: center !important; }
                .exercise-action-buttons { width: 100%; }
            }
            .exercise-action-buttons { margin-top: 60px; }
            .exercise-action-buttons { float: right; }
            .exercise-action-buttons .btn { margin: 0 5px; }
        </style>";

    $tool_content .= "<div class='exercise-nav-buttons col-12' style='margin-top: 20px;'>
                        <div class='row row-cols-2 g-4'>";

    $prevLabel = '&lt; ' . $langPrevious;
    $nextLabel = $langNext . ' &gt';
    if ($exerciseType == MULTIPLE_PAGE_TYPE and $questionId != $questionList[1]) { // `prev` button
        $tool_content .= "<div class='col'><input class='btn blockUI navbutton btn-exercise-nav w-100' type='submit' name='prev' value='$prevLabel'></div>";
    }
    if ($questionId != end($questionList)) { // `next` button
        if (($exerciseType == ONE_WAY_TYPE) or ($exerciseType == MULTIPLE_PAGE_TYPE and $questionId == $questionList[1])) {
            $tool_content .= "<div class='col'></div>";
        }
        $tool_content .= "<div class='col'><input class='btn blockUI navbutton btn-exercise-nav w-100' type='submit' value='$nextLabel'></div>";
    } else {
        $isFinalQuestion = 'true';
    }
    $tool_content .= "</div></div>";
} else {
    $head_content .= "<style>
            @media only screen and (max-width: 460px) {
                .exercise-action-buttons { text-align: center; width: 100%; }
            }
            .exercise-action-buttons { margin-top: 15px; }
            .exercise-action-buttons .btn { margin: 0 5px; }
            .exercise-action-buttons { float: right; }
        </style>";
}

$tool_content .= "<div class='col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap' style='margin-top:100px;'>";

// "Cancel" button
$tool_content .= "<input class='btn cancelAdminBtn' type='submit' name='buttonCancel' id='cancelButton' value='$langCancel'>";

// "Submit" button
$tool_content .= "<input class='btn successAdminBtn blockUI' type='submit' name='buttonFinish' value='$langExerciseFinalSubmit'>";
if ($exerciseType != SINGLE_PAGE_TYPE) {
    $tool_content .= "<input type='hidden' name='questionId' value='$questionId'>";
}

$tool_content .= $tempSaveButton . "</div>";

$tool_content .= "</form>";
$tool_content .= "</div>";

// In sequential exercise we save all questions in the DB
// to avoid mixing up their order if user navigates non-sequentially
if ($exerciseType == MULTIPLE_PAGE_TYPE or $exerciseType == ONE_WAY_TYPE) {
    $_POST['attempt_value'] = $attempt_value;
    $objExercise->save_unanswered();
}

// If the attempt has disappeared or isn't in a valid state in the DB, redirect user to exercise home
$attempt = Database::get()->querySingle('SELECT eurid FROM exercise_user_record
        WHERE eurid = ?d AND attempt_status = ?d', $eurid, ATTEMPT_ACTIVE);
if (!$attempt && !$is_editor) {
    Session::flash('message',$langExerciseAttemptGone);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page($back_url);
}

if ($questionList) {
    $refresh_time = 300000; // Refresh PHP session every 5 min. (in ms)
    // Enable check for unanswered questions when displaying more than one question
    if ($exerciseType == ONE_WAY_TYPE) {
        $checkSinglePage = 'true';
        $unansweredIds = [];
        $oneUnanswered = js_escape($langUnansweredQuestionsWarningThisOne);
        $questionPrompt = js_escape($langUnansweredQuestionsNoTurnBack);
        $submitPrompt = js_escape(($isFinalQuestion == 'true')? $langExerciseFinalSubmit: $langNextQuestion);
    } else {
        $checkSinglePage = 'false';
        $oneUnanswered = js_escape($langUnansweredQuestionsWarningOne);
        $questionPrompt = js_escape($langUnansweredQuestionsQuestion);
        $submitPrompt = js_escape($langExerciseFinalSubmit);
    }
    $head_content .= "<script type='text/javascript'>
    var langHasAnswered = '". js_escape($langHasAnswered) ."';
    $(function () {
        exercise_init_countdown({
            checkSinglePage: $checkSinglePage,
            isFinalQuestion: $isFinalQuestion,
            warning: '". js_escape($langLeaveExerciseWarning) ."',
            unansweredQuestions: '". js_escape($langUnansweredQuestions) ."',
            unseenQuestions: '". js_escape($langUnansweredQuestionsWarningUnseen) ."',
            oneUnanswered: '$oneUnanswered',
            manyUnanswered: '". js_escape($langUnansweredQuestionsWarningMany) ."',
            finalSubmit: '". js_escape($langExerciseFinalSubmit) ."',
            finalSubmitWarn: '". js_escape($langExerciseFinalSubmitWarn) ."',
            question: '$questionPrompt',
            submit: '$submitPrompt',
            goBack: '". js_escape($langGoBackToEx) ."',
            cancelMessage: '". js_escape($langCancelExConfirmation) ."',
            cancelAttempt: '". js_escape($langCancelAttempt) ."',
            refreshTime: $refresh_time,
            exerciseId: $exerciseId,
            answeredIds: ". json_encode($answeredIds) .",
            unansweredIds: ". json_encode($unansweredIds) .",
            attemptsAllowed: $exerciseAllowedAttempts,
            eurid: $eurid
        });
        $('.qNavButton').click(function (e) {
            e.preventDefault();
            var panel = $($(this).attr('href'));
            $('.qPanel').removeClass('panelCard').addClass('panel-default');
            panel.removeClass('panel-default').addClass('panelCard');
            $('html').animate({ scrollTop: ($(panel).offset().top - 20) + 'px' });
        });
        if ($exercisePreventCopy) {
            document.addEventListener('contextmenu', e => e.preventDefault(), false);
            document.addEventListener('keydown', e => {
                if (e.ctrlKey || e.code == 85 || e.code == 123) {
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
            $('.exercise-prevent-copy input').on('select', function () {
                this.selectionStart = this.selectionEnd;
            });
        }
    });
</script>" . ($exercisePreventCopy? "
<style>
.exercise-prevent-copy { user-select: none; }
.exercise-prevent-copy input[type=text]::selection { background-color:transparent }
</style>": '');
}

draw($tool_content, 2, null, $head_content);

function unset_session_variables_of_questions($eurid, $type = '') {
    global $course_id, $uid, $webDir, $course_code;

    $question_ids = [];
    $qids = Database::get()->queryArray("SELECT question_id FROM exercise_answer_record WHERE eurid = ?d", $eurid);
    foreach ($qids as $q) {
        $question_ids[] = $q->question_id;
    }

    // Remove sessions of ordering and oral questions
    if (count($question_ids) > 0) {
        foreach ($question_ids as $qid) {
            // About ordering questions
            if (isset($_SESSION['OrderingSubsetKeys'][$uid][$qid])) {
                unset($_SESSION['OrderingSubsetKeys'][$uid][$qid]);
            }
            // About oral questions
            if ($type == 'cancel_exercise') {
                $fFile = Database::get()->querySingle("SELECT id,`path` FROM document WHERE course_id = ?d
                                                        AND subsystem = ?d AND subsystem_id = ?d
                                                        AND lock_user_id = ?d", $course_id, ORAL_QUESTION, $qid, $uid);
                if (file_exists("$webDir/courses/$course_code/image" . $fFile->path)) {
                    unlink("$webDir/courses/$course_code/image" . $fFile->path);
                }
                Database::get()->query("DELETE FROM document WHERE id = ?d", $fFile->id);
            }
        }
    }

    // Remove sessions of calculated question
    if (isset($_SESSION['QuestionDisplayed'][$uid]) && count($question_ids) > 0) {
        foreach ($_SESSION['QuestionDisplayed'][$uid] as $index => $q) {
            if (in_array($q, $question_ids)) {
                unset($_SESSION['QuestionDisplayed'][$uid][$index]);
            }
        }
    }
}
