<?php 
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/


include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
include('exercise.lib.php');

// answer types
define('UNIQUE_ANSWER',1);
define('MULTIPLE_ANSWER',2);
define('FILL_IN_BLANKS',3);
define('MATCHING',4);

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Exercise';

include '../../include/baseTheme.php';
$tool_content = "";
$nameTools = $langResults;

include('../../include/lib/textLib.inc.php');
$picturePath='../../courses/'.$currentCourseID.'/image';
$is_allowedToEdit=$is_adminOfCourse;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

$navigation[]=array("url" => "exercice.php","name" => $langExercices);

// if the object is not in the session
if(!isset($_SESSION['objExercise'])) {
	// construction of Exercise
	$objExercise=new Exercise();
	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_allowedToEdit)) {
		$tool_content .= "<p>$langExerciseNotFound</p>";	
		draw($tool_content, 2, 'exercice');
		exit();
	}
	// saves the object into the session
	$_SESSION['objExercise'] = $objExercise;
}

$exerciseTitle=$objExercise->selectTitle();
$exerciseDescription=$objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
	
$tool_content .= "<table class=\"Exercise\" width=\"99%\"><thead><tr>
	<td><b>$exerciseTitle</b><br/><br/>${exerciseDescription_temp}
	</td></tr></thead></table><br/>";

mysql_select_db($currentCourseID);
$sql="SELECT DISTINCT uid FROM `exercise_user_record`";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	$sid = $row['uid'];
	$StudentName = db_query("SELECT nom,prenom,am FROM user WHERE user_id='$sid'", $mysqlMainDb);
	$theStudent = mysql_fetch_array($StudentName);
	
	mysql_select_db($currentCourseID);
	$sql2="SELECT DATE_FORMAT(RecordStartDate, '%Y-%m-%d / %H:%i') AS RecordStartDate, RecordEndDate, TIME_TO_SEC(TIMEDIFF(RecordEndDate,RecordStartDate)) AS TimeDuration, TotalScore, TotalWeighting 
	FROM `exercise_user_record` WHERE uid='$sid' AND eid='$exerciseId'";
	$result2 = db_query($sql2);
	if (mysql_num_rows($result2) > 0) { // if users found
		$tool_content .= "<table class='Question'>";
		$tool_content .= "<tr><th colspan='3' class='left'>";
		if (!$sid) {
			$tool_content .= "$langNoGroupStudents";
		} else {
			if ($theStudent['am'] == '') $studentam = '-';
			else $studentam = $theStudent['am'];
			$tool_content .= "$langUser: <b>$theStudent[nom] $theStudent[prenom] </b> ($langAm: $studentam)";
		}
		$tool_content .= "</th></tr>";
		$tool_content .= "<tr><td width='150' align='center'><b>".$langExerciseStart."</b></td>";
		$tool_content .= "<td width='150' align='center'><b>".$langExerciseDuration."</b></td>";
		$tool_content .= "<td width='150' align='right'><b>".$langYourTotalScore2."</b></td></tr>";
	
		while($row2=mysql_fetch_array($result2)) {
			$tool_content .= "<tr><td align='center'>$row2[RecordStartDate]</td>";
			if ($row2['TimeDuration'] == '00:00:00' or empty($row2['TimeDuration'])) { // for compatibility 
				$tool_content .= "<td align='center'>$langNotRecorded</td>";
			} else {
				$tool_content .= "<td align='center'>".format_time_duration($row2['TimeDuration'])."</td>";
			}
			$tool_content .= "<td align='center'>".$row2['TotalScore']. "/".$row2['TotalWeighting']."</td></tr>";
		}
	$tool_content .= "</table><br/>";
	}
}
draw($tool_content, 2, 'exercice');
?>	