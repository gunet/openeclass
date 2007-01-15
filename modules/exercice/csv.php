<?
$langFiles = 'exercice';
$require_current_course = TRUE;

include '../../include/init.php';

// IF PROF ONLY
if($is_adminOfCourse) {

header("Content-disposition: filename=".$currentCourse."_".$_GET['exerciseId']."_".date("Y-m-d").".xls");
header("Content-type: application/msexcel; charset=iso-8859-7");
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
	//echo "$crlf";
	$sid = $row['uid'];
	$StudentName = db_query("select nom,prenom from user where user_id='$sid'", $mysqlMainDb);
	$theStudent = mysql_fetch_array($StudentName);
	
	$nom = $theStudent["nom"];
	$prenom = $theStudent["prenom"];
	
	mysql_select_db($currentCourseID);
	$sql2="SELECT RecordStartDate,RecordEndDate,TotalScore,TotalWeighting  FROM `exercise_user_record` WHERE uid='$sid' AND eid='".mysql_real_escape_string($_GET['exerciseId'])."'";
	$result2 = mysql_query($sql2);
	while($row2=mysql_fetch_array($result2)) {
		echo "$crlf";
		echo("$prenom \t");
		echo("$nom \t");

		$RecordStartDate = $row2['RecordStartDate'];
		echo("$RecordStartDate\t");
		
		$RecordEndDate = $row2['RecordEndDate'];
		if ($RecordEndDate != "0000-00-00 00:00:00") { 
			echo("$RecordEndDate\t");
		} else { // user termination or excercise time limit exceeded
			echo("$langResultsFailed\t");
		}
		
		$TotalScore = $row2['TotalScore'];
		$TotalWeighting = $row2['TotalWeighting'];
		echo("$TotalScore\t");
		echo("$TotalWeighting\t");
	}
/////////////////////////////////////////////////////////////////////////////////
}
echo "$crlf";

}  // end of initial if
?>
