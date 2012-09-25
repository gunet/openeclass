<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


$require_current_course = TRUE;
include '../../include/init.php';

if ($is_editor) {
	header("Content-disposition: filename=".$currentCourse."_".$_GET['exerciseId']."_".date("Y-m-d").".xls");
	header("Content-type: text/csv; charset=UTF-16");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	$bom = "\357\273\277";
	
	$crlf="\r\n";
	$output =  "$bom$langSurname\t$langName\t$langExerciseStart\t$langExerciseDuration\t$langYourTotalScore2$crlf";
	$output .=  "$crlf";
	
	mysql_select_db($currentCourseID);
	$sql = "SELECT DISTINCT uid FROM `exercise_user_record` WHERE eid=".intval($_GET['exerciseId']);
	$result = db_query($sql);
	while ($row=mysql_fetch_array($result)) {
		$sid = $row['uid'];
		$StudentName = db_query("select nom, prenom from user where user_id='$sid'", $mysqlMainDb);
		$theStudent = mysql_fetch_array($StudentName);	
		$nom = $theStudent["nom"];
		$prenom = $theStudent["prenom"];	
		mysql_select_db($currentCourseID);
		$sql2="SELECT DATE_FORMAT(RecordStartDate, '%Y-%m-%d / %H:%i') AS RecordStartDate, 
			RecordEndDate, TIME_TO_SEC(TIMEDIFF(RecordEndDate,RecordStartDate)) AS TimeDuration, 
			TotalScore, TotalWeighting 
			FROM `exercise_user_record` WHERE uid='$sid' AND eid=".intval($_GET['exerciseId']);
		$result2 = db_query($sql2);
		while ($row2=mysql_fetch_array($result2)) {
			$output .= csv_escape($prenom) ."\t";
			$output .= csv_escape($nom) ."\t";
			$RecordStartDate = $row2['RecordStartDate'];
			$output .= csv_escape($RecordStartDate) ."\t";
			if ($row2['TimeDuration'] == '00:00:00' or empty($row2['TimeDuration'])) { // for compatibility 
				$output .= csv_escape($langNotRecorded) ."\t";
			} else {
				$output .= csv_escape(format_time_duration($row2['TimeDuration']))."\t";
			}		
			$TotalScore = $row2['TotalScore'];
			$TotalWeighting = $row2['TotalWeighting'];
			$output .= csv_escape("( $TotalScore/$TotalWeighting )"). "\t";
			$output .=  "$crlf";
		}
	}
	echo iconv('UTF-8', 'UTF-16LE', $output);
}  // end of initial if

