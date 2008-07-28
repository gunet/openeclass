<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$require_current_course = TRUE;
include '../../include/init.php';

// IF PROF ONLY
if($is_adminOfCourse) {

header("Content-disposition: filename=".$currentCourse."_".$_GET['exerciseId']."_".date("Y-m-d").".xls");
header("Content-type: application/msexcel; charset=UTF-8");
header("Pragma: no-cache");
header("Expires: 0");

$crlf="\n";

// doing some DOS-CRLF magic...
$client=getenv("HTTP_USER_AGENT");
if (ereg('[^(]*\((.*)\)[^)]*',$client,$regs)) {
	$os = $regs[1];
	// this looks better under WinX
	if (eregi("Win",$os)) $crlf="\r\n";
	}
	echo "$langExerciseSurname\t$langExerciseName\t$langExerciseStart\t$langExerciseEnd\t$langYourTotalScore2\t$langQuestionWeighting";
	echo "$crlf";
	echo "$crlf";

mysql_select_db($currentCourseID);
$sql="SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid='".mysql_real_escape_string($_GET['exerciseId'])."'";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	$sid = $row['uid'];
	$StudentName = db_query("select nom,prenom from user where user_id='$sid'", $mysqlMainDb);
	$theStudent = mysql_fetch_array($StudentName);	
	$nom = $theStudent["nom"];
	$prenom = $theStudent["prenom"];	
	mysql_select_db($currentCourseID);
	$sql2="SELECT RecordStartDate, RecordEndDate, TotalScore, TotalWeighting 
		FROM `exercise_user_record` WHERE uid='$sid' AND eid='".mysql_real_escape_string($_GET['exerciseId'])."'";
	$result2 = mysql_query($sql2);
	while($row2=mysql_fetch_array($result2)) {
		echo "$crlf";
		echo("$prenom \t");
		echo("$nom \t");
		$RecordStartDate = $row2['RecordStartDate'];
		echo("$RecordStartDate\t");		
		$RecordEndDate = $row2['RecordEndDate'];
		if ($RecordEndDate != "0000-00-00") { 
			echo("$RecordEndDate\t");
		} else { // user termination or excercise time limit exceeded
			echo("$langResultsFailed\t");
		}		
		$TotalScore = $row2['TotalScore'];
		$TotalWeighting = $row2['TotalWeighting'];
		echo("$TotalScore\t");
		echo("$TotalWeighting\t");
	}
}
echo "$crlf";
}  // end of initial if
?>
