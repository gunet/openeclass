<?php
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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

/*===========================================================================
	questionnaire.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the questionnaire tool

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
$langFiles = 'questionnaire';

$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';
$nameTools = $langQuestionnaire;
$tool_content = "";

//if ((isset($questionnaire_type))&&($questionnaire_type == 1)) {
//	// It is a SURVEY
//	header("Location: survey.php");
//	exit();
//} elseif ((isset($questionnaire_type))&&($questionnaire_type == 2)) {
//	// It is a POLL
//	header("Location: poll.php");
//	exit();
//} else {
//	// Just entered page
//	$tool_content .= "<b>" . $langQPref ."</b><br><br>";
//	
//	$tool_content .= "<a href=\"?questionnaire_type=1\">" . $langQPrefSurvey ."</a><br>";
//	$tool_content .= "<a href=\"?questionnaire_type=2\">" . $langQPrefPoll ."</a><br>";
// }
	
$tool_content .= "<table width=\"90%\"><thead>$langNamesSurvey</thead><tbody><tr><td>";
	printSurveys();
	$tool_content .= "</td></tr></tbody></table>";
	
	$tool_content .= "<table width=\"90%\"><thead>$langNamesPoll</thead><tbody><tr><td>";
	printPolls();
	$tool_content .= "</td></tr></tbody></table>";
	
	draw($tool_content, 2);
	
	
 /***************************************************************************************************
 * printSurveys()
 ****************************************************************************************************/
	function printSurveys() {
 		global $tool_content, $currentCourse, $langSurveyNone, $langSurveyNone,
 			$langSurveyCreate, $langSurveyCreate, $langSurveyName, $langSurveyCreator, 
 			$langSurveyCreation, $langSurveyStart, $langSurveyEnd, $langSurveyType, 
 			$langSurveyOperations, $is_adminOfCourse, $langSurveysActive, $mysqlMainDb, 
 			$langSurveyMC, $langSurveyEdit, $langSurveyRemove, $langSurveyDeactivate,
 			$langSurveysInactive, $langSurveyActivate, $langSurveyParticipate;

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
			//$tool_content .= $num_rows . " " . $survey_check;
		
			if ($is_adminOfCourse) 
				$tool_content .= '<b><a href="addsurvey.php?UseCase=0">'.$langSurveyCreate.'</a></b><br><br>  ';
			
			// Print active surveys //////////////////////////////////////////
			$tool_content .= <<<cData
				<b>$langSurveysActive</b>
				<table border="1" width="95%"><tr>
				<td>$langSurveyName</td>
				<td>$langSurveyCreator</td>
				<td>$langSurveyCreation</td>
				<td>$langSurveyStart</td>
				<td>$langSurveyEnd</td>
				<td>$langSurveyType</td>
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
					$tool_content .= "<td><!--<a href='editsurvey.php?sid={$sid}'>".$langSurveyEdit."</a> | -->".
						"<a href='deletesurvey.php?sid={$sid}'>".$langSurveyRemove."</a> | ".
						"<a href='deactivatesurvey.php?sid={$sid}'>".$langSurveyDeactivate."</a>  ".
						"</td></tr>";
				else
					$tool_content .= "<td><a href='surveyparticipate.php?UseCase=1&sid=". $sid ."'>".$langSurveyParticipate."</a></td></tr>";
			}
			$tool_content .= "</table><br>";
			
			//		Print inactive surveys ///////////////////////////////
			if ($is_adminOfCourse) {
				
				$tool_content .= <<<cData
					<b>$langSurveysInactive</b>
					<table border="1" width="95%"><tr>
					<td>$langSurveyName</td>
					<td>$langSurveyCreator</td>
					<td>$langSurveyCreation</td>
					<td>$langSurveyStart</td>
					<td>$langSurveyEnd</td>
					<td>$langSurveyType</td>
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
					
					//$tool_content .= "<tr><td>" . $theSurvey["name"] . "</td>";
					if ($is_adminOfCourse) { 
						$tool_content .= "<tr><td><a href=\"surveyresults.php?sid=". 
						$sid ."&type=" . $theSurvey["type"]."\">" . $theSurvey["name"] .
					"</a></td>";
					} else {
						$tool_content .= "<tr><td>" . $theSurvey["name"] . "</td>";
					}
					//$tool_content .= "<td>" . $countAnswers . "</td>";
					$tool_content .= "<td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["creation_date"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["start_date"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["end_date"] . "</td>";
					
					if ($theSurvey["type"] == 1) {
						$tool_content .= "<td>" . $langSurveyMC . "</td>";
					} else {
						$tool_content .= "<td>" . $langSurveyFillText . "</td>";
					}
					$tool_content .= "<td><!--<a href='editsurvey.php?sid={$sid}'>".$langSurveyEdit."</a> | -->".
					"<a href='deletesurvey.php?sid={$sid}'>".$langSurveyRemove."</a> | ".
					"<a href='activatesurvey.php?sid={$sid}'>".$langSurveyActivate."</a>  ".
					"</td></tr>";
				}
				$tool_content .= "</table><br>";
			}
		}

	}
	
 /***************************************************************************************************
 * printPolls()
 ****************************************************************************************************/
	function printPolls() {
		global $tool_content, $currentCourse, $langPollCreate, $langPollsActive, 
			$langPollName, $langPollCreator, $langPollCreation, $langPollStart, 
			$langPollEnd, $langPollOperations, $langPollNone, $is_adminOfCourse, $langNamesPoll,
			$langNamesSurvey, $mysqlMainDb, $langPollEdit, $langPollRemove, 
			$langPollDeactivate, $langPollsInactive, $langPollActivate, $langPollParticipate, 
			$user_id, $langPollHasParticipated, $uid;
		
		$poll_check = 0;
		$result = mysql_list_tables($currentCourse);
		while ($row = mysql_fetch_row($result)) {
			if ($row[0] == 'poll') {
		 		$result = db_query("select * from poll", $currentCourse);
				$num_rows = mysql_num_rows($result);
				if ($num_rows > 0)
		 			++$poll_check;
			}
		}
		if (!$poll_check) {
			$tool_content .= $langPollNone . "<br><br>";
			if ($is_adminOfCourse) 
				$tool_content .= '<b><a href="addpoll.php?UseCase=0">'.$langPollCreate.'</a></b><br><br>  ';
			}
		else {
		
			if ($is_adminOfCourse) 
				$tool_content .= '<b><a href="addpoll.php?UseCase=0">'.$langPollCreate.'</a></b><br><br>  ';
			
			// Print active polls //////////////////////////////////////////////////////
			$tool_content .= <<<cData
				<b>$langPollsActive</b>
				<table border="1" width="95%"><tr>
				<td>$langPollName</td>
				<td>$langPollCreator</td>
				<td>$langPollCreation</td>
				<td>$langPollStart</td>
				<td>$langPollEnd</td>
				
				<td>$langPollOperations</td>
				</tr>
cData;
			
			$active_polls = db_query("
				select * from poll 
				where active=1", $currentCourse);
				
			while ($thepoll = mysql_fetch_array($active_polls)) {	
				
				$creator_id = $thepoll["creator_id"];
				
				$poll_creator = db_query("
				select nom,prenom from user 
				where user_id='$creator_id'", $mysqlMainDb);
				$theCreator = mysql_fetch_array($poll_creator);
				
				$pid = $thepoll["pid"];
				$answers = db_query("
				select * from poll_answer 
				where pid='$pid'", $currentCourse);
				$countAnswers = mysql_num_rows($answers);
				
				if ($is_adminOfCourse) { 
					$tool_content .= "<tr><td><a href=\"pollresults.php?pid=". 
					$pid ."&type=" . $thepoll["type"]."\">" . $thepoll["name"] .
					"</a></td>";
				} else {
					$tool_content .= "<tr><td>" . $thepoll["name"] . "</td>";
				}	
					
				$tool_content .= "<td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
				$tool_content .= "<td>" . $thepoll["creation_date"] . "</td>";
				$tool_content .= "<td>" . $thepoll["start_date"] . "</td>";
				$tool_content .= "<td>" . $thepoll["end_date"] . "</td>";
				
				if ($is_adminOfCourse) 
					$tool_content .= "<td><!--<a href='editpoll.php?pid={$pid}'>".$langPollEdit."</a> | -->".
						"<a href='deletepoll.php?pid={$pid}'>".$langPollRemove."</a> | ".
						"<a href='deactivatepoll.php?pid={$pid}'>".$langPollDeactivate."</a>  ".
						"</td></tr>";
				else {
//					$participant = db_query("
//					select nom,prenom from user 
//					where user_id='$creator_id'", $mysqlMainDb);
//					$theCreator = mysql_fetch_array($survey_creator);
//					
//					$sid = $theSurvey["sid"];
						$thepid = $thepoll["pid"];
						$has_participated = mysql_fetch_array(
							mysql_query("SELECT COUNT(*) FROM poll_answer where creator_id='$uid' AND pid='$thepid'"));
					if ( $has_participated[0] == 0) {
						$tool_content .= "<td><a href='pollparticipate.php?UseCase=1&pid=". $pid ."'>";
						$tool_content .= $langPollParticipate;
						$tool_content .= "</a></td></tr>";
						//$tool_content .= $has_participated[0].$uid;
					} else {
						$tool_content .= "<td>".$langPollHasParticipated."</td></tr>";
					}
				}
			}
			$tool_content .= "</table><br>";
			
			// Print inactive polls //////////////////////////////////////////////////////
			if ($is_adminOfCourse) {
				
				$tool_content .= <<<cData
					<b>$langPollsInactive</b>
					<table border="1" width="95%"><tr>
					<td>$langPollName</td>
					<td>$langPollCreator</td>
					<td>$langPollCreation</td>
					<td>$langPollStart</td>
					<td>$langPollEnd</td>
					
					<td>$langPollOperations</td>
					</tr>
cData;
				
				$inactive_polls = db_query("
					select * from poll 
					where active=0", $currentCourse);
					
				while ($thepoll = mysql_fetch_array($inactive_polls)) {	
					
					$creator_id = $thepoll["creator_id"];
					
					$poll_creator = db_query("
					select nom,prenom from user 
					where user_id='$creator_id'", $mysqlMainDb);
					$theCreator = mysql_fetch_array($poll_creator);
					
					$pid = $thepoll["pid"];
					$answers = db_query("
					select * from poll_answer 
					where pid='$pid'", $currentCourse);
					$countAnswers = mysql_num_rows($answers);
					
					if ($is_adminOfCourse) { 
						$tool_content .= "<tr><td><a href=\"pollresults.php?pid=". 
						$pid ."&type=" . $thepoll["type"]."\">" . $thepoll["name"] .
					"</a></td>";
					} else {
						$tool_content .= "<tr><td>" . $thepoll["name"] . "</td>";
					}
					$tool_content .= "<td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
					$tool_content .= "<td>" . $thepoll["creation_date"] . "</td>";
					$tool_content .= "<td>" . $thepoll["start_date"] . "</td>";
					$tool_content .= "<td>" . $thepoll["end_date"] . "</td>";
					
					$tool_content .= "<td><!--<a href='editpoll.php?pid={$pid}'>".$langPollEdit."</a> | -->".
					"<a href='deletepoll.php?pid={$pid}'>".$langPollRemove."</a> | ".
					"<a href='activatepoll.php?pid={$pid}'>".$langPollActivate."</a>  ".
					"</td></tr>";
				}
				$tool_content .= "</table><br>";
			}
		}
		
	}

?>
