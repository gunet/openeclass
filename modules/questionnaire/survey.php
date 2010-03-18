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

/*===========================================================================
	survey.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the survey tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights - to create surveys.

 	The user can : - navigate through files and directories.
                       - upload a file
                       - delete, copy a file or a directory
                       - edit properties & content (name, comments, 
			 html content)

 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
   	2) Define the directory to display
  	3) Read files and directories from the directory defined in part 2
  	4) Display all of that on an HTML page
==============================================================================
*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';
$tool_content = "";

/*****************************************************************************
		Check for surveys
******************************************************************************/
$survey_check = 0;
$result = mysql_list_tables($currentCourse);
while ($row = mysql_fetch_row($result)) {
	if ($row[0] == 'survey') {
 		//$tool_content .= $row[0] . "<br><br>";
 		$result = db_query("select * from survey", $currentCourse);
		$num_rows = mysql_num_rows($result);
		if ($num_rows > 0)
 			++$survey_check;
	}
}
if (!$survey_check) {
	$tool_content .= $langSurveyNone . "<br><br>";
	if ($is_adminOfCourse) 
		$tool_content .= '<b><a href="addsurvey.php?UseCase=0">'.$langSurveyCreate.'</a></b><br><br>  ';
	}
else {

	if ($is_adminOfCourse) 
		$tool_content .= '<b><a href="addsurvey.php?UseCase=0">'.$langSurveyCreate.'</a></b><br><br>  ';
	
	/*****************************************************************************
			Print active surveys
	******************************************************************************/
	$tool_content .= <<<cData
		<b>$langSurveysActive</b>
		<table border="1"><tr>
		<td>$langyName</td>
		<td>$langSurveyCreator</td>
		<td>$langCreate</td>
		<td>$langSurveyStart</td>
		<td>$langSurveyEnd</td>
		<td>$langType</td>
		<td>$langSurveyOperations</td>
		</tr>
cData;
	
	$active_surveys = db_query("
		select * from survey 
		where active=1", $currentCourse);
		
	while ($theSurvey = mysql_fetch_array($active_surveys)) {	
		
		$creator_id = $theSurvey["creator_id"];
		
		$survey_creator = db_query("
		select nom,prenom from user 
		where user_id='$creator_id'", $mysqlMainDb);
		$theCreator = mysql_fetch_array($survey_creator);
		
		$sid = $theSurvey["sid"];
		$answers = db_query("
		select * from survey_answer 
		where sid='$sid'", $currentCourse);
		$countAnswers = mysql_num_rows($answers);
		
		if ($is_adminOfCourse) { 
			$tool_content .= "<tr><td><a href=\"surveyresults.php?sid=". 
			$sid ."&type=" . $theSurvey["type"]."\">" . $theSurvey["name"] .
			"</a></td>";
		} else {
			$tool_content .= "<tr><td>" . $theSurvey["name"] . "</td>";
		}	
			
		$tool_content .= "<td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
		$tool_content .= "<td>" . $theSurvey["creation_date"] . "</td>";
		$tool_content .= "<td>" . $theSurvey["start_date"] . "</td>";
		$tool_content .= "<td>" . $theSurvey["end_date"] . "</td>";
		
		if ($theSurvey["type"] == 1) {
			$tool_content .= "<td>" . $langSurveyMC . "</td>";
		} else {
			$tool_content .= "<td>" . $langSurveyFillText . "</td>";
		}
		if ($is_adminOfCourse) 
			$tool_content .= "<td><!--<a href='editsurvey.php?sid={$sid}'>".$langEdit."</a> | -->".
				"<a href='deletesurvey.php?sid={$sid}'>".$langDelete."</a> | ".
				"<a href='deactivatesurvey.php?sid={$sid}'>".$langDeactivate."</a> | ".
				"</td></tr>";
		else
			$tool_content .= "<td><a href='surveyparticipate.php?UseCase=1&sid=". $sid ."'>".$langSurveyParticipate."</a></td></tr>";
	}
	$tool_content .= "</table><br>";
	
	///*****************************************************************************
	//		Print inactive surveys
	//******************************************************************************/
	if ($is_adminOfCourse) {
		
		$tool_content .= <<<cData
			<b>$langSurveysInactive</b>
			<table border="1"><tr>
			<td>$langTitle</td>
			<td>$langSurveyCreator</td>
			<td>$langCreate</td>
			<td>$langSurveyStart</td>
			<td>$langSurveyEnd</td>
			<td>$langType</td>
			<td>$langSurveyOperations</td>
			</tr>
cData;
		
		$inactive_surveys = db_query("
			select * from survey 
			where active=0", $currentCourse);
			
		while ($theSurvey = mysql_fetch_array($inactive_surveys)) {	
			
			$creator_id = $theSurvey["creator_id"];
			
			$survey_creator = db_query("
			select nom,prenom from user 
			where user_id='$creator_id'", $mysqlMainDb);
			$theCreator = mysql_fetch_array($survey_creator);
			
			$sid = $theSurvey["sid"];
			$answers = db_query("
			select * from survey_answer 
			where sid='$sid'", $currentCourse);
			$countAnswers = mysql_num_rows($answers);
			
			if ($is_adminOfCourse) { 
				$tool_content .= "<tr><td><a href=\"surveyresults.php?sid=". 
				$sid ."&type=" . $theSurvey["type"]."\">" . $theSurvey["name"] .
			"</a></td>";
			} else {
				$tool_content .= "<tr><td>" . $theSurvey["name"] . "</td>";
			}
			$tool_content .= "<td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
			$tool_content .= "<td>" . $theSurvey["creation_date"] . "</td>";
			$tool_content .= "<td>" . $theSurvey["start_date"] . "</td>";
			$tool_content .= "<td>" . $theSurvey["end_date"] . "</td>";
			
			if ($theSurvey["type"] == 1) {
				$tool_content .= "<td>" . $langSurveyMC . "</td>";
			} else {
				$tool_content .= "<td>" . $langSurveyFillText . "</td>";
			}
			$tool_content .= "<td><!--<a href='editsurvey.php?sid={$sid}'>".$langEdit."</a> | -->".
			"<a href='deletesurvey.php?sid={$sid}'>".$langDelete."</a> | ".
			"<a href='activatesurvey.php?sid={$sid}'>".$langActivate."</a> | ".
			"</td></tr>";
		}
		$tool_content .= "</table><br>";
	}
}
/*****************************************************************************
		Print the page
******************************************************************************/
draw($tool_content, 2); 
?>
