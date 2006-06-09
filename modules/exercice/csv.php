<?
$langFiles = 'exercice';
$require_current_course = TRUE;

include '../../include/init.php';

// IF PROF ONLY
if($is_adminOfCourse) {

header("Content-disposition: filename=".$currentCourse."_".$exerciseId."_".date("Y-m-d").".xls");
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


	echo "$langExerciseSurname $langExerciseName\t$langExerciseStart\t$langExerciseEnd\t$langYourTotalScore";
	echo "$crlf";
	echo "$crlf";
//	$sql = db_query("SELECT  user.nom, user.prenom, user.email, user.am, user.username, user_group.team
//                        FROM cours_user, user LEFT JOIN `$currentCourseID`.user_group ON `user`.user_id=user_group.user
//                         WHERE `user`.`user_id`=`cours_user`.`user_id` AND `cours_user`.`code_cours`='$currentCourseID'
//			ORDER BY user.nom", $mysqlMainDb);
//	$r=0;
//	while ($r < mysql_num_rows($sql)) {
//		$a=mysql_fetch_array($sql);
//		echo "$crlf";
//		$f=0;
//		while ($f < mysql_num_fields($sql)) {
//			echo("$a[$f]\t");
//			$f++;
//			}
//		$r++;
/////////////////////////////////////////////////////////////////////////////////
mysql_select_db($currentCourseID);
$sql="SELECT DISTINCT uid FROM `exercise_user_record`";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	echo "$crlf";
	$f=0;
	$sid = $row['uid'];
	$StudentName = db_query("select nom,prenom from user where user_id='$sid'", $mysqlMainDb);
	$theStudent = mysql_fetch_array($StudentName);
	
	$nom = $theStudent["nom"];
	$prenom = $theStudent["prenom"];
	
	echo("$prenom $nom[$f]\t");
	$f++;
	
	echo("$prenom $nom[$f]\t");
	$f++;
	
	mysql_select_db($currentCourseID);
	$sql="SELECT RecordStartDate,RecordEndDate,TotalScore,TotalWeighting  FROM `exercise_user_record`";
	$result = mysql_query($sql);
	while($row=mysql_fetch_array($result)) {

		$RecordStartDate = $row['RecordStartDate'];
		echo("$RecordStartDate[$f]\t");
		$f++;
		
		$RecordEndDate = $row['RecordEndDate'];
		if ($RecordEndDate != "0000-00-00 00:00:00") { 
			echo("$RecordEndDate[$f]\t");
			$f++;
		} else { // user termination or excercise time limit exceeded
			echo("$langResultsFailed[$f]\t");
			$f++;
		}
		
		$theScore = $row['TotalScore']."/".$row['TotalWeighting'];
		echo("$theScore[$f]\t");
		$f++;
	}
/////////////////////////////////////////////////////////////////////////////////
}
echo "$crlf";

}  // end of initial if
?>
