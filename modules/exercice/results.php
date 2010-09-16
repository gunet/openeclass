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

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses'; 


$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Exercise';

include '../../include/baseTheme.php';
include('exercise.class.php');
include('../../include/lib/textLib.inc.php');

$nameTools = $langResults;
$navigation[]=array("url" => "exercice.php","name" => $langExercices);

if (isset($_GET['exerciseId'])) {
	$exerciseId = $_GET['exerciseId'];
}

// if the object is not in the session
if(!isset($_SESSION['objExercise'])) {
	// construction of Exercise
	$objExercise = new Exercise();
	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_adminOfCourse)) {
		$tool_content .= "<p>$langExerciseNotFound</p>";	
		draw($tool_content, 2);
		exit();
	}
}

if (isset($_SESSION['objExercise'])) {
	$objExercise = $_SESSION['objExercise'];
}

$exerciseTitle=$objExercise->selectTitle();
$exerciseDescription=$objExercise->selectDescription();
$exerciseDescription_temp = nl2br(make_clickable($exerciseDescription));
	
$tool_content .= "
    <table class=\"tbl_border\" width=\"99%\">
    <tr class='odd'>
      <td><b>$exerciseTitle</b><br/><br/>${exerciseDescription_temp}</td>
    </tr>
    </table>
    <br/>";

mysql_select_db($currentCourseID);
$sql="SELECT DISTINCT uid FROM `exercise_user_record`";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	$sid = $row['uid'];
	$StudentName = db_query("SELECT nom,prenom,am FROM user WHERE user_id='$sid'", $mysqlMainDb);
	$theStudent = mysql_fetch_array($StudentName);
	
	mysql_select_db($currentCourseID);
	$sql2="SELECT DATE_FORMAT(RecordStartDate, '%Y-%m-%d / %H:%i') AS RecordStartDate, RecordEndDate,
		TIME_TO_SEC(TIMEDIFF(RecordEndDate,RecordStartDate))
		AS TimeDuration, TotalScore, TotalWeighting 
		FROM `exercise_user_record` WHERE uid='$sid' AND eid='$exerciseId'";
	$result2 = db_query($sql2);
	if (mysql_num_rows($result2) > 0) { // if users found
		$tool_content .= "
    <table class='tbl' width='99%'>";
		$tool_content .= "
    <tr>
      <th colspan='3'>";
		if (!$sid) {
			$tool_content .= "$langNoGroupStudents";
		} else {
			if ($theStudent['am'] == '') $studentam = '-';
			else $studentam = $theStudent['am'];
			$tool_content .= "$langUser: <b>$theStudent[nom] $theStudent[prenom] </b> ($langAm: $studentam)";
		}
		$tool_content .= "</th>
    </tr>
    <tr>
      <th width='150' class='center'>".$langExerciseStart."</td>
      <th width='150' class='center'>".$langExerciseDuration."</td>
      <th width='150' class='center'>".$langYourTotalScore2."</td>
    </tr>";
 	
                $k=0;
		while($row2=mysql_fetch_array($result2)) {
        if ($k%2 == 0) {
                $tool_content .= "    <tr class='even'>\n";
        } else {
                $tool_content .= "    <tr class='odd'>\n";
        }

			$tool_content .= "
      <td class='center'>$row2[RecordStartDate]</td>";
			if ($row2['TimeDuration'] == '00:00:00' or empty($row2['TimeDuration'])) { // for compatibility 
				$tool_content .= "
      <td class='center'>$langNotRecorded</td>";
			} else {
				$tool_content .= "
      <td class='center'>".format_time_duration($row2['TimeDuration'])."</td>";
			}
			$tool_content .= "
      <td class='center'>".$row2['TotalScore']. "/".$row2['TotalWeighting']."</td>
    </tr>";
    $k++;
		}
	$tool_content .= "
    </table>
    <br/>";
	}
}
draw($tool_content, 2);
?>	
