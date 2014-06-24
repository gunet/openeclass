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


$TBL_EXERCISE_QUESTION = 'exercise_with_questions';
$TBL_EXERCISE = 'exercise';
$TBL_QUESTION = 'exercise_question';
$TBL_ANSWER = 'exercise_answer';

require_once 'exercise.class.php';

$require_current_course = true;
$require_help = true;
$helpTopic = 'Exercise';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

$nameTools = $langResults;
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

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));

$tool_content .= "
    <table class='tbl_border' width='100%'>
    <tr>
    <th>" . q($exerciseTitle) . "</th>
    </tr>
    <tr>
    <td>" . standard_text_escape($exerciseDescription_temp) . "</td>
    </tr>
    </table>
    <br/>";

//This part of the code could be improved
if ($is_editor) {
    $result = Database::get()->queryArray("SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid in (SELECT id FROM exercise WHERE course_id = ?d)", $course_id);
} else {
    $result[] = (object) array('uid' => $uid);
}

foreach ($result as $row) {
    $sid = $row->uid;
    $theStudent = Database::get()->querySingle("SELECT surname, givenname, am FROM user WHERE id = ?d", $sid);

    $result2 = Database::get()->queryArray("SELECT DATE_FORMAT(record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date, record_end_date,
                TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date))
                AS time_duration, total_score, total_weighting, eurid, attempt_status
                FROM `exercise_user_record` WHERE uid = ?d AND eid = ?d", $sid, $exerciseId);
    if (count($result2) > 0) { // if users found
        $tool_content .= "<table class='tbl_alt' width='100%'>";
        $tool_content .= "<tr><td colspan='4'>";
        if (!$sid) {
            $tool_content .= "$langNoGroupStudents";
        } else {
            if ($theStudent->am == '')
                $studentam = '-';
            else
                $studentam = $theStudent->am;
            $tool_content .= "<b>$langUser:</b> $theStudent->surname $theStudent->givenname  <div class='smaller'>($langAm: $studentam)</div>";
        }
        $tool_content .= "</td>
                </tr>
                <tr>
                  <th width='150' class='center'>" . $langExerciseStart . "</td>
                  <th width='150' class='center'>" . $langExerciseDuration . "</td>
                  <th width='150' class='center'>" . $langYourTotalScore2 . "</td>
                  <th class='center'>" . $langCurrentStatus. "</th>
                </tr>";

        $k = 0;
        foreach ($result2 as $row2) {
            if ($k % 2 == 0) {
                $tool_content .= "<tr class='even'>";
            } else {
                $tool_content .= "<tr class='odd'>";
            }
            $tool_content .= "<td class='center'>$row2->record_start_date</td>";
            if ($row2->time_duration == '00:00:00' or empty($row2->time_duration)) { // for compatibility
                $tool_content .= "<td class='center'>$langNotRecorded</td>";
            } else {
                $tool_content .= "<td class='center'>" . format_time_duration($row2->time_duration) . "</td>";
            }
            $nbr_answer_records = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_answer_record WHERE eurid = ?d",$row2->eurid)->count;
            if ($nbr_answer_records > 0) {
                $results_link = "<a href='exercise_result.php?course=$course_code&amp;eurId=$row2->eurid'>" . $row2->total_score . "/" . $row2->total_weighting . "</a>";
            } else {
                $results_link = $row2->total_score . "/" . $row2->total_weighting;
            }
            $tool_content .= "<td class='center'>$results_link</td>";
            if ($row2->attempt_status == ATTEMPT_COMPLETED) {
                $status = $langAttemptCompleted;
            } elseif ($row2->attempt_status == ATTEMPT_PENDING) {
                $status = $langAttemptPending;
            } elseif ($row2->attempt_status == ATTEMPT_PAUSED) {
                $status = $langAttemptPaused;
            } elseif ($row2->attempt_status == ATTEMPT_CANCELED) {
                $status = $langAttemptCanceled;
            }
            $tool_content .= "<td class='center'>$status</td></tr>";            
            $k++;
        }
        $tool_content .= "</table><br/>";
    }
}
draw($tool_content, 2, null, $head_content);
