<?php 
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".

       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/


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
$nameTools = $langExercice;

include('../../include/lib/textLib.inc.php');
$picturePath='../../courses/'.$currentCourseID.'/image';

$is_allowedToEdit=$is_adminOfCourse;
$dbNameGlu=$currentCourseID;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';
$TBL_REPONSES='reponses';

$navigation[]=array("url" => "exercice.php","name" => $langExercices);

// if the object is not in the session
if(!session_is_registered('objExercise')) {
	// construction of Exercise
	$objExercise=new Exercise();
	// if the specified exercise doesn't exist or is disabled
	if(!$objExercise->read($exerciseId) && (!$is_allowedToEdit)) {
		$tool_content .= "<p>$langExerciseNotFound</p>";	
		draw($tool_content, 2, 'exercice');
		exit();
	}
	// saves the object into the session
	session_register('objExercise');
}

$exerciseTitle=$objExercise->selectTitle();
$tool_content .= "<table class=\"ExerciseSum\" width=\"99%\">
    	<thead><tr><td><b>$exerciseTitle</b></td></tr></thead>
    	</table><br/>";

mysql_select_db($currentCourseID);
$sql="SELECT DISTINCT uid FROM `exercise_user_record`";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	$sid = $row['uid'];
	$StudentName = db_query("select nom,prenom from user where user_id='$sid'", $mysqlMainDb);
	$theStudent = mysql_fetch_array($StudentName);
	$tool_content .= "<table class=\"Exercise\" width=\"99%\"><tr>
      	<td colspan=\"3\">$langUser&nbsp;:&nbsp;&nbsp;<b>".$theStudent["nom"]." ".$theStudent["prenom"]."</b></td></tr>";
	$tool_content .= "<tr><td><b>".$langExerciseStart."</b></td>";
	$tool_content .= "<td><b>".$langExerciseEnd."</b></td>";
	$tool_content .= "<td><b>".$langYourTotalScore2."</b></td></tr>";
	
	mysql_select_db($currentCourseID);
	$sql2="SELECT RecordStartDate, RecordEndDate, TotalScore, TotalWeighting 
		FROM `exercise_user_record` WHERE uid='$sid' AND eid='$exerciseId'";
	$result2 = mysql_query($sql2);
	while($row2=mysql_fetch_array($result2)) {
		$RecordEndDate = $row2['RecordEndDate'];
		$tool_content .= "<tr><td>".greek_format($row2['RecordStartDate'])."</td>";
		if ($RecordEndDate != "0000-00-00") { 
			$tool_content .= "<td>".greek_format($RecordEndDate)."</td>";
		} else { // user termination or excercise time limit exceeded
			$tool_content .= "<td>".$langResultsFailed."</td>";
		}	
		$tool_content .= "<td>".$row2['TotalScore']. "/".$row2['TotalWeighting']."</td></tr>";
	}
$tool_content .= "</table><br/><br/>";
}
draw($tool_content, 2, 'exercice');
?>	
