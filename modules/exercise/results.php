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

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Exercise';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

$nameTools = $langResults;
$navigation[]=array('url' => "index.php?course=$course_code", 'name' => $langExercices);

if (isset($_GET['exerciseId'])) {
	$exerciseId = intval($_GET['exerciseId']);
}

// if the object is not in the session
if(!isset($_SESSION['objExercise'][$exerciseId])) {
	// construction of Exercise
	$objExercise = new Exercise();
	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_editor)) {
		$tool_content .= "<p>$langExerciseNotFound</p>";
		draw($tool_content, 2);
		exit();
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
    <th>". q($exerciseTitle) ."</th>
    </tr>
    <tr>
    <td>". standard_text_escape($exerciseDescription_temp) ."</td>
    </tr>
    </table>
    <br/>";

$sql = "SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid in (SELECT id FROM exercise WHERE course_id = $course_id)";
$result = db_query($sql);
while($row=mysql_fetch_array($result)) {
	$sid = $row['uid'];
	$StudentName = db_query("SELECT nom,prenom,am FROM user WHERE user_id='$sid'");
	$theStudent = mysql_fetch_array($StudentName);

	$sql2="SELECT DATE_FORMAT(record_start_date, '%Y-%m-%d / %H:%i') AS record_start_date, record_end_date,
		TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date))
		AS time_duration, total_score, total_weighting
		FROM `exercise_user_record` WHERE uid='$sid' AND eid='$exerciseId'";
	$result2 = db_query($sql2);
	if (mysql_num_rows($result2) > 0) { // if users found
		$tool_content .= "<table class='tbl_alt' width='100%'>";
		$tool_content .= "<tr><td colspan='3'>";
		if (!$sid) {
			$tool_content .= "$langNoGroupStudents";
		} else {
			if ($theStudent['am'] == '') $studentam = '-';
			else $studentam = $theStudent['am'];
			$tool_content .= "<b>$langUser:</b> $theStudent[nom] $theStudent[prenom]  <div class='smaller'>($langAm: $studentam)</div>";
		}
		$tool_content .= "</td>
                </tr>
                <tr>
                  <th width='150' class='center'>".$langExerciseStart."</td>
                  <th width='150' class='center'>".$langExerciseDuration."</td>
                  <th width='150' class='center'>".$langYourTotalScore2."</td>
                </tr>";

        $k=0;
        while($row2=mysql_fetch_array($result2)) {
                if ($k%2 == 0) {
                        $tool_content .= "<tr class='even'>";
                } else {
                        $tool_content .= "<tr class='odd'>";
                }
                $tool_content .= "<td class='center'>$row2[record_start_date]</td>";
			if ($row2['time_duration'] == '00:00:00' or empty($row2['time_duration'])) { // for compatibility
				$tool_content .= "<td class='center'>$langNotRecorded</td>";
			} else {
				$tool_content .= "<td class='center'>".format_time_duration($row2['time_duration'])."</td>";
			}
			$tool_content .= "<td class='center'>".$row2['total_score']. "/".$row2['total_weighting']."</td>
                        </tr>";
                        $k++;
		}
	$tool_content .= "</table><br/>";
	}
}
draw($tool_content, 2, null, $head_content);
