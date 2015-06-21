<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

$pageName = $langExercicesView;
require_once 'include/lib/textLib.inc.php';

$picturePath = "courses/$course_code/image";
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
}

$tool_content_extra = "<br/>
	<table width='99%' class='Question'>
		<thead>
			<td class='alert alert-warning'>".q($error)."</td>
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
$tmp = Database::get()->querySingle("SELECT COUNT(*) AS cnt, record_start_date FROM `exercise_user_record` WHERE eid = ?d AND uid= ?d AND record_end_date is NULL", $exerciseId, $uid);
if ($tmp->cnt > 0) {	
    Database::get()->query("UPDATE `exercise_user_record` SET record_end_date = '".date('Y-m-d H:i:s', time())."' WHERE eid = ?d AND uid = ?d AND record_end_date is NULL", $exerciseId, $uid);
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = $objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));

$tool_content .= "<table class='Exercise' width='99%'>
<thead><tr>
  <td colspan='2'>
  <b>" . q_math($exerciseTitle) . "</b>
  <br/><br/>
  " . q($exerciseDescription_temp) . "
  </td>
</tr>
</thead></table>";

$tool_content .= $tool_content_extra;
draw($tool_content, 2);


