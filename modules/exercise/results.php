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

$pageName = $langResults;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langExercices);

if (isset($_GET['exerciseId'])) {
    $exerciseId = intval($_GET['exerciseId']);
}

// if the object is not in the session
if (!isset($_SESSION['objExercise'][$exerciseId])) {
    // construction of Exercise
    $objExercise = new Exercise();
    // if the specified exercise doesn't exist or is disabled
    if (!$objExercise->read($exerciseId) && (!$is_editor)) {
        $tool_content .= "<p>$langExerciseNotFound</p>";
        draw($tool_content, 2);
        exit();
    }
    if(!$objExercise->selectScore() &&  !$is_editor) {
        redirect_to_home_page("modules/exercise/index.php?course=$course_code");
    }
}

if (isset($_SESSION['objExercise'][$exerciseId])) {
    $objExercise = $_SESSION['objExercise'][$exerciseId];
}

if ($is_editor && isset($_GET['purgeAttempID'])) {
    $eurid = $_GET['purgeAttempID'];
    $objExercise->purgeAttempt($eurid);
    Session::Messages($langPurgeExerciseResultsSuccess);
    redirect_to_home_page("modules/exercise/results.php?course=$course_code&exerciseId=$exerciseId");  
}
$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
 
$tool_content .= "
<div class='table-responsive'>
    <table class='table-default'>
    <tr>
    <th>" . q_math($exerciseTitle) . "</th>
    </tr>";
if($exerciseDescription_temp) {
    $tool_content .= "
        <tr>
            <td>" . standard_text_escape($exerciseDescription_temp) . "</td>
        </tr>";
}
$tool_content .= "</table>
</div><br>";
$status = (isset($_GET['status'])) ? intval($_GET['status']) : 0; 
$tool_content .= "<select class='form-control' style='margin:0 0 12px 0;' id='status_filtering'>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId' ".(($status == 0)? 'selected' : '').">--- $langCurrentStatus ---</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_COMPLETED."' ".(($status == 1)? 'selected' : '').">$langAttemptCompleted</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_PENDING."' ".(($status == 2)? 'selected' : '').">$langAttemptPending</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_PAUSED."' ".(($status == 3)? 'selected' : '').">$langAttemptPaused</option>
        <option value='results.php?course=$course_code&exerciseId=$exerciseId&status=".ATTEMPT_CANCELED."' ".(($status == 4)? 'selected' : '').">$langAttemptCanceled</option>
        </select>";
//This part of the code could be improved
if ($is_editor) {
    $result = Database::get()->queryArray("SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid in (SELECT id FROM exercise WHERE course_id = ?d)", $course_id);
} else {
    $result[] = (object) array('uid' => $uid);
}
$extra_sql = ($status != 0 ) ? 'AND attempt_status = '.$status : '';

foreach ($result as $row) {
    $sid = $row->uid;
    $theStudent = Database::get()->querySingle("SELECT surname, givenname, am FROM user WHERE id = ?d", $sid);

    $result2 = Database::get()->queryArray("SELECT DATE_FORMAT(a.record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date, a.record_end_date,
                CASE b.time_constraint WHEN 0 THEN TIME_TO_SEC(TIMEDIFF(a.record_end_date, a.record_start_date))
                ELSE b.time_constraint*60-a.secs_remaining END AS time_duration, a.total_score, a.total_weighting, a.eurid, a.attempt_status
                FROM `exercise_user_record` a, exercise b WHERE a.uid = ?d AND a.eid = ?d AND a.eid = b.id $extra_sql", $sid, $exerciseId);
    if (count($result2) > 0) { // if users found
        $tool_content .= "<div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<tr><td colspan='".($is_editor ? 5 : 4)."'>";
        if (!$sid) {
            $tool_content .= "$langNoGroupStudents";
        } else {
            if ($theStudent->am == '')
                $studentam = '-';
            else
                $studentam = $theStudent->am;
            $tool_content .= "<b>$langUser:</b> " . q($theStudent->surname) . " " . q($theStudent->givenname) . "  <div class='smaller'>($langAm: " . q($studentam) . ")</div>";
        }
        $tool_content .= "</td>
                </tr>
                <tr>
                  <th class='text-center'>" . $langStart . "</td>
                  <th class='text-center'>" . $langExerciseDuration . "</td>
                  <th class='text-center'>" . $langYourTotalScore2 . "</td>
                  <th class='text-center'>" . $langCurrentStatus. "</th>
                  ". ($is_editor ? "<th class='text-center'>" . icon('fa-gears'). "</th>" : "") ."
                </tr>";

        $k = 0;
        foreach ($result2 as $row2) {
//            if ($is_editor && $row2->attempt_status == ATTEMPT_PENDING) {
//                $class .= ' highlight_row'; 
//            }
            $tool_content .= "<tr>";
            $tool_content .= "<td class='text-center'>" . q($row2->record_start_date) . "</td>";
            if ($row2->time_duration == '00:00:00' or empty($row2->time_duration)) { // for compatibility
                $tool_content .= "<td class='center'>$langNotRecorded</td>";
            } else {
                $tool_content .= "<td class='text-center'>" . format_time_duration($row2->time_duration) . "</td>";
            }
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                $results_link = "<a href='exercise_result.php?course=$course_code&amp;eurId=$row2->eurid'>" . q($row2->total_score) . "/" . q($row2->total_weighting) . "</a>";
            } else {
                if ($row2->attempt_status == ATTEMPT_PAUSED) {
                    $results_link = "-/-";
                } else {
                    $results_link = q($row2->total_score). "/" . q($row2->total_weighting);
                }
            }
            $tool_content .= "<td class='text-center'>$results_link</td>";
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                $status = $langAttemptCompleted;
            } elseif ($row2->attempt_status == ATTEMPT_PENDING) {
                $status = "<a href='exercise_result.php?course=$course_code&amp;eurId=$row2->eurid'>" .$langAttemptPending. "</a>";
            } elseif ($row2->attempt_status == ATTEMPT_PAUSED) {
                $status = $langAttemptPaused;
            } elseif ($row2->attempt_status == ATTEMPT_CANCELED) {
                $status = $langAttemptCanceled;
            }
            $tool_content .= "
                    <td class='text-center'>$status</td>";
            if ($is_editor) {
            $tool_content .= "
                    <td class='option-btn-cell'>" . action_button(array(
                        array(
                            'title' => $langDelete,
                            'url' => "results.php?course=$course_code&exerciseId=$exerciseId&purgeAttempID=$row2->eurid",
                            'icon' => "fa-times",
                            'confirm' => $langQuestionCatDelConfirrm,
                            'class' => 'delete'
                        )
                    )) . "</td>";
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
