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



include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('exercise.lib.php');

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Exercise';

include '../../include/baseTheme.php';

$nameTools = $langExercicesView;
require_once 'include/lib/textLib.inc.php';

$picturePath = "courses/$course_code/image";

$TBL_EXERCISE_QUESTION = 'exercise_with_questions';
$TBL_EXERCISE = 'exercise';
$TBL_QUESTION = 'exercise_question';
$TBL_ANSWER = 'exercise_answer';
$TBL_RECORDS = 'exercise_user_record';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

if (isset($_GET['exerciseId'])) {
    $exerciseId = intval($_GET['exerciseId']);
}

if (isset($_SESSION['objExercise'][$exerciseId])) {
    $objExercise = $_SESSION['objExercise'][$exerciseId];
}

if (isset($_GET['error'])) {
    $error = $_GET['error'];
    unset($_SESSION['exercise_begin_time']);
    unset($_SESSION['exercise_end_time']);
}

$tool_content_extra = "<br/>
	<table width='99%' class='Question'>
		<thead>
			<td class='alert1'>".${$error}."</td>
		</tr><tr>
			<td><br/><br/><br/>
				<div align='center'><a href='index.php?course=$course_code'>$langBack</a></div>
			</td>
	</tr></thead></table>"; 

// if the object is not in the session
if (!isset($_SESSION['objExercise'][$exerciseId])) {
    // construction of Exercise
    $objExercise = new Exercise();
    // if the specified exercise doesn't exist or is disabled
    if (@(!$objExercise->read($exerciseId) && (!$is_editor))) {
        $error = 'langExerciseNotFound';
		draw($tool_content_extra, 2);
        exit();
    }
    // saves the object into the session
    $_SESSION['objExercise'][$exerciseId] = $objExercise;
}

// if there is an active attempt and it's time passed. Complete the record to finish attempt
$sql = "SELECT COUNT(*), record_start_date FROM `$TBL_RECORDS` WHERE eid='$exerciseId' AND uid='$uid' AND record_end_date is NULL";
$tmp = mysql_fetch_row(db_query($sql));
if ($tmp[0] > 0) {
	$sql = "UPDATE `$TBL_RECORDS` SET record_end_date = '".date('Y-m-d H:i:s', time())."' WHERE eid = '$exerciseId' AND uid = '$uid' AND record_end_date is NULL";
	db_query($sql);
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));

$tool_content .= "<table class='Exercise' width='99%'>
<thead><tr>
  <td colspan='2'>
  <b>" . stripslashes($exerciseTitle) . "</b>
  <br/><br/>
  " . stripslashes($exerciseDescription_temp) . "
  </td>
</tr>
</thead></table>";

$tool_content .= $tool_content_extra;
draw($tool_content, 2);


