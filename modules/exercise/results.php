<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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


require_once 'exercise.class.php';

$require_current_course = true;
$require_help = true;
$helpTopic = 'Exercise';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();


if (isset($_GET['exerciseId'])) {
    $exerciseIdIndirect = $_GET['exerciseId'];
    $exerciseId = getDirectReference($exerciseIdIndirect);
    $objExercise = new Exercise();
    if (!$objExercise->read($exerciseId) && !$is_editor) {
        Session::Messages($langExerciseNotFound);
        redirect_to_home_page("modules/exercise/index.php?course=$course_code");
    }
    if (!$objExercise->selectScore() && !$is_editor) {
        redirect_to_home_page("modules/exercise/index.php?course=$course_code");
    }    
}
if ($is_editor && isset($_GET['purgeAttempID'])) {
    $eurid = $_GET['purgeAttempID'];
    $objExercise->purgeAttempt($eurid);
    Session::Messages($langPurgeExerciseResultsSuccess);
    redirect_to_home_page("modules/exercise/results.php?course=$course_code&exerciseId=$exerciseId");
}

$pageName = $langResults;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langExercices);

$extra_sql = $status !== ''  ? ' AND attempt_status = '.$status : '';
$user_attempts = [];
if ($is_editor) {
    $data['students'] = Database::get()->queryArray("SELECT DISTINCT record.uid id, user.surname surname, user.givenname givenname, user.am am "
            . "FROM `exercise_user_record` record, `user` user "
            . "WHERE user.id = record.uid AND eid in "
            . "(SELECT id FROM exercise WHERE course_id = ?d)", $course_id);
} else {
    $data['students'] = Database::get()->queryArray("SELECT id, surname, givenname, am FROM user WHERE id = ?d", $uid);
}
foreach ($data['students'] as $student) {
    $data['user_attempts'][$student->id] = Database::get()->queryArray("SELECT DATE_FORMAT(a.record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date, a.record_end_date,
                CASE b.time_constraint
                WHEN 0 THEN TIME_TO_SEC(TIMEDIFF(a.record_end_date, a.record_start_date))
                ELSE b.time_constraint*60-a.secs_remaining END AS time_duration, a.total_score, a.total_weighting, a.eurid, a.attempt_status
                FROM `exercise_user_record` a, exercise b WHERE a.uid = ?d AND a.eid = ?d AND a.eid = b.id$extra_sql 
                ORDER BY a.record_start_date DESC", $student->id, $exerciseId);    
}
$data['exercise'] = $objExercise;
$data['status'] = $status = (isset($_GET['status'])) ? intval($_GET['status']) : '';
view('modules.exercise.results', $data);
exit();

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectParsedDescription();
$exerciseTimeConstraint = $objExercise->selectTimeConstraint();
$displayScore = $objExercise->selectScore();
$exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
$userAttempts = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exerciseId, $uid)->count;
$cur_date = new DateTime("now");
$end_date = new DateTime($objExercise->selectEndDate());
$showScore = $displayScore == 1
            || $is_editor
            || $displayScore == 3 && $exerciseAttemptsAllowed == $userAttempts
            || $displayScore == 4 && $end_date < $cur_date;
$tool_content .= "
<div class='table-responsive'>
    <table class='table-default'>
    <tr>
    <th>" . q_math($exerciseTitle) . "</th>
    </tr>";
if($exerciseDescription) {
    $tool_content .= "
        <tr>
            <td>" . standard_text_escape($exerciseDescription) . "</td>
        </tr>";
}
$tool_content .= "</table>
</div><br>";
$status = (isset($_GET['status'])) ? intval($_GET['status']) : '';
$tool_content .= "<select class='form-control' style='margin:0 0 12px 0;' id='status_filtering'>
        <option value='results.php?course=$course_code&exerciseId=$exerciseIdIndirect'>--- $langCurrentStatus ---</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseIdIndirect&status=".ATTEMPT_ACTIVE."' ".(($status === 0)? 'selected' : '').">$langAttemptActive</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseIdIndirect&status=".ATTEMPT_COMPLETED."' ".(($status === 1)? 'selected' : '').">$langAttemptCompleted</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseIdIndirect&status=".ATTEMPT_PENDING."' ".(($status === 2)? 'selected' : '').">$langAttemptPending</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseIdIndirect&status=".ATTEMPT_PAUSED."' ".(($status === 3)? 'selected' : '').">$langAttemptPaused</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseIdIndirect&status=".ATTEMPT_CANCELED."' ".(($status === 4)? 'selected' : '').">$langAttemptCanceled</option>
        </select>";


$extra_sql = $status !== ''  ? ' AND attempt_status = '.$status : '';
$user_attempts = [];
if ($is_editor) {
    $students = Database::get()->queryArray("SELECT DISTINCT record.uid id, user.surname surname, user.givenname givenname, user.am am "
            . "FROM `exercise_user_record` record, `user` user "
            . "WHERE user.id = record.uid AND eid in "
            . "(SELECT id FROM exercise WHERE course_id = ?d)", $course_id);
} else {
    $students = Database::get()->queryArray("SELECT id, surname, givenname, am FROM user WHERE id = ?d", $uid);
}
foreach ($students as $student) {
    $user_attempts[$student->id] = Database::get()->queryArray("SELECT DATE_FORMAT(a.record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date, a.record_end_date,
                CASE b.time_constraint
                WHEN 0 THEN TIME_TO_SEC(TIMEDIFF(a.record_end_date, a.record_start_date))
                ELSE b.time_constraint*60-a.secs_remaining END AS time_duration, a.total_score, a.total_weighting, a.eurid, a.attempt_status
                FROM `exercise_user_record` a, exercise b WHERE a.uid = ?d AND a.eid = ?d AND a.eid = b.id$extra_sql 
                ORDER BY a.record_start_date DESC", $student->id, $exerciseId);    
}

foreach ($students as $student) {
    if (count($user_attempts[$student->id]) > 0) { // if user attempts found
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<tr><td colspan='".($is_editor ? 5 : 4)."'>";
        if (!$student->id) {
            $tool_content .= "$langNoGroupStudents";
        } else {
            if ($student->am == '')
                $studentam = '-';
            else
                $studentam = $student->am;
            $tool_content .= "<b>$langUser:</b> " . q($student->surname) . " " . q($student->givenname) . "  <div class='smaller'>($langAm: " . q($studentam) . ")</div>";
        }
        $tool_content .= "</td>
                </tr>
                <tr>
                  <th class='text-center'>" . $langStart . "</td>
                  <th class='text-center'>" . $langExerciseDuration . "</td>
                  <th class='text-center'>" . $langTotalScore . "</td>
                  <th class='text-center'>" . $langCurrentStatus. "</th>
                  ". ($is_editor ? "<th class='text-center'>" . icon('fa-gears'). "</th>" : "") ."
                </tr>";

        $k = 0;
        foreach ($user_attempts[$student->id] as $attempt) {
            $row_class = "";
            $action_btn_state = true;
            if ($attempt->attempt_status == ATTEMPT_COMPLETED) { // IF ATTEMPT COMPLETED
                $status = $langAttemptCompleted;
                if ($showScore) {
                    $answersCount = Database::get()->querySingle("SELECT count(*) AS answers_cnt FROM `exercise_answer_record` WHERE `eurid` = ?d", $attempt->eurid)->answers_cnt;
                    if ($answersCount) {
                        $results_link = "<a href='exercise_result.php?course=$course_code&amp;eurId=$attempt->eurid'>" . q($attempt->total_score) . "/" . q($attempt->total_weighting) . "</a>";
                    } else {
                        $results_link = q($attempt->total_score) . "/" . q($attempt->total_weighting);
                    }
                } else {
                    switch ($displayScore) {
                        case 2:
                            $results_link = $langScoreNotDisp;
                            break;
                        case 3:
                            $results_link = $langScoreDispLastAttempt;
                            break;
                        case 4:
                            $results_link = $langScoreDispEndDate;
                            break;
                    }
                }
            } else { // IF ATTEMPT ANYTHING BUT COMPLETED
                // IF ATTEMPT PAUSED OR ACTIVE
                if ($attempt->attempt_status == ATTEMPT_PAUSED || $attempt->attempt_status == ATTEMPT_ACTIVE) {
                    $results_link = "-/-";
                    if ($attempt->attempt_status == ATTEMPT_PAUSED) {
                        $status = $langAttemptPaused;
                    } else {
                        $status = $langAttemptActive;
                        $now = new DateTime('NOW');
                        $estimatedEndTime = DateTime::createFromFormat('Y-m-d / H:i', $attempt->record_start_date);
                        // in an active exercise if a time constaint passes the exercise can safely be deleted
                        // if not it can be deleted after a day
                        if ($exerciseTimeConstraint) {
                            $estimatedEndTime->add(new DateInterval('PT' . $exerciseTimeConstraint . 'M'));
                        } else {
                            $estimatedEndTime->add(new DateInterval('P1D'));
                        }
                        if ($now > $estimatedEndTime) {
                            $row_class = " class='warning' data-toggle='tooltip' title='$langAttemptActiveButDeadMsg'";
                        } else {
                            if ($exerciseTimeConstraint) {
                                $action_btn_state = false;
                            }
                            $row_class = " class='success' data-toggle='tooltip' title='$langAttemptActiveMsg'";
                        }
                    }
                // IF ATTEMPT PENDING OR CANCELED
                } else {
                    $results_link = q($attempt->total_score). "/" . q($attempt->total_weighting);
                    if ($attempt->attempt_status == ATTEMPT_PENDING) {
                        $status = "<a href='exercise_result.php?course=$course_code&amp;eurId=$attempt->eurid'>" .$langAttemptPending. "</a>";
                    } else {
                        $status = $langAttemptCanceled;
                    }
                }
            }
            $tool_content .= "
                        <tr$row_class>
                            <td class='text-center'>" . q($attempt->record_start_date) . "</td>";
            if ($attempt->time_duration == '00:00:00' || empty($attempt->time_duration) || $attempt->attempt_status == ATTEMPT_ACTIVE) { // for compatibility
                $tool_content .= "<td class='text-center'>$langNotRecorded</td>";
            } else {
                $tool_content .= "<td class='text-center'>" . format_time_duration($attempt->time_duration) . "</td>";
            }
            $tool_content .= "
                    <td class='text-center'>$results_link</td>
                    <td class='text-center'>$status</td>";
            if ($is_editor) {
            $tool_content .= "
                    <td class='option-btn-cell'>" . ($action_btn_state ? action_button(array(
                        array(
                            'title' => $langDelete,
                            'url' => "results.php?course=$course_code&exerciseId=$exerciseId&purgeAttempID=$attempt->eurid",
                            'icon' => "fa-times",
                            'confirm' => $langQuestionCatDelConfirrm,
                            'class' => 'delete'
                        )
                    )) : "") . "</td>";
            }
            $tool_content .= "
                </tr>";
            $k++;
        }
        $tool_content .= "</table></div><br>";
    }
}
$head_content .= "
    <script type='text/javascript'>
            $(function(){
              // bind change event to select
              $('#status_filtering').bind('change', function () {
                  var url = $(this).val(); // get selected value
                  if (url) { // require a URL
                      window.location = url; // redirect
                  }
                  return false;
              });
            });
    </script>
        ";
draw($tool_content, 2, null, $head_content);
