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

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_QUESTIONNAIRE');
/**************************************/

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
	
//$tool_content .= "<table width=\"90%\"><thead>$langNamesSurvey</thead><tbody><tr><td>";
$tool_content .= "<p><b>$langNamesSurvey</b></p><br>";
	printSurveys();
//	$tool_content .= "</td></tr></tbody></table>";
	
//	$tool_content .= "<table width=\"90%\"><thead>$langNamesPoll</thead><tbody><tr><td>";
$tool_content .= "<p><b>$langNamesPoll</b></p><br>";
	printPolls();
//	$tool_content .= "</td></tr></tbody></table>";
	
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
 			$langSurveysInactive, $langSurveyActivate, $langSurveyParticipate, 
 			$langSurveyHasParticipated, $uid;

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
			$tool_content .= "<p>".$langSurveyNone . "</p><br>";
			if ($is_adminOfCourse) 
				$tool_content .= '<a href="addsurvey.php?UseCase=0">'.$langSurveyCreate.'</a><br><br>  ';
			}
		else {
			//$tool_content .= $num_rows . " " . $survey_check;
		
			if ($is_adminOfCourse) 
				$tool_content .= '<b><a href="addsurvey.php?UseCase=0">'.$langSurveyCreate.'</a></b><br><br>  ';
			
			// Print active surveys //////////////////////////////////////////
			$tool_content .= <<<cData
				<!--<b>$langSurveysActive</b>-->
				<table border="0" width="95%"><thead><tr>
				<th>$langSurveyName</th>
				<th>$langSurveyCreator</th>
				<th>$langSurveyCreation</th>
				<th>$langSurveyStart</th>
				<th>$langSurveyEnd</th>
				<th>$langSurveyType</th>
				<th>
cData;
				
				
				if ($is_adminOfCourse) {
					$tool_content .= $langSurveyRemove;
				}
				
				if ($is_adminOfCourse) {
					$tool_content .= "</th><th>$langSurveyActivate</th>";
				} else {
					$tool_content .= "</th>";
				}				
				$tool_content .= "</tr></thead><tbody>";
			
			$active_surveys = db_query("
				select * from survey", $currentCourse);
				
			while ($theSurvey = mysql_fetch_array($active_surveys)) {	
				
				$visibility = $theSurvey["active"];
				
				if (($visibility)||($is_adminOfCourse)) {
				
					if ($visibility) {
						$visibility_css = " ";
						$visibility_gif = "invisible";
						$visibility_func = "deactivate";
					} else {
						$visibility_css = " class=\"invisible\"";
						$visibility_gif = "visible";
						$visibility_func = "activate";
					}
					
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
						$tool_content .= "\n<tr><td><a href=\"surveyresults.php?sid=". 
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
					if ($is_adminOfCourse)   {
						
						$tool_content .= "<td align=center><!--<a href='editsurvey.php?sid={$sid}'>".$langSurveyEdit."</a> | -->".
							"<a href='deletesurvey.php?sid={$sid}'><img src='../../template/classic/img/delete.gif' border='0'></a></td><td align=center> ".
							"<a href='".$visibility_func."survey.php?sid={$sid}'><img src='../../template/classic/img/".$visibility_gif.".gif' border='0'></a>  ".
							"</td></tr>\n";
					} else {
						////////////////////////////////////////////////////
							$thesid = $theSurvey["sid"];
							$has_participated = mysql_fetch_array(
								mysql_query("SELECT COUNT(*) FROM survey_answer where creator_id='$uid' AND sid='$thesid'"));
						if ($has_participated[0] == 0) {
							$tool_content .= "<td><a href='surveyparticipate.php?UseCase=1&sid=". $sid ."'>";
							$tool_content .= $langSurveyParticipate;
							$tool_content .= "</a></td></tr>";
							//$tool_content .= $has_participated[0].$uid;
						} else {
							$tool_content .= "<td>".$langSurveyHasParticipated."</td></tr>";
						}
						////////////////////////////////////////////////////
					}
				}
				//$tool_content .= "sssssssssssss</table><br>";
				
		}
		$tool_content .= "</table><br>";
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
			$tool_content .= "<p>".$langPollNone . "</p><br>";
			if ($is_adminOfCourse) 
				$tool_content .= '<a href="addpoll.php?UseCase=0">'.$langPollCreate.'</a><br><br>  ';
			}
		else {
		
			if ($is_adminOfCourse) 
				$tool_content .= '<b><a href="addpoll.php?UseCase=0">'.$langPollCreate.'</a></b><br><br>  ';
			
			// Print active polls //////////////////////////////////////////////////////
			$tool_content .= <<<cData
				<!--<b>$langPollsActive</b>-->
				<table border="1" width="95%"><thead><tr>
				<th>$langPollName</th>
				<th>$langPollCreator</th>
				<th>$langPollCreation</th>
				<th>$langPollStart</th>
				<th>$langPollEnd</th>
				
				<th>
cData;
				
				
				if ($is_adminOfCourse) {
					$tool_content .= $langPollRemove;
				}
				
				if ($is_adminOfCourse) {
					$tool_content .= "</th><th>$langPollActivate</th>";
				} else {
					$tool_content .= "</th>";
				}				
				$tool_content .= "</tr></thead><tbody>";

			
			$active_polls = db_query("
				select * from poll", $currentCourse);
				
			while ($thepoll = mysql_fetch_array($active_polls)) {	
				
				$visibility = $thepoll["active"];
				
	if (($visibility)||($is_adminOfCourse)) {
				
				if ($visibility) {
					$visibility_css = " ";
					$visibility_gif = "invisible";
					$visibility_func = "deactivate";
				} else {
					$visibility_css = " class=\"invisible\"";
					$visibility_gif = "visible";
					$visibility_func = "activate";
				}
				
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
					$tool_content .= "<tr".$visibility_css."><td>" . $thepoll["name"] . "</td>";
				}	
					
				$tool_content .= "<td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
				$tool_content .= "<td>" . $thepoll["creation_date"] . "</td>";
				$tool_content .= "<td>" . $thepoll["start_date"] . "</td>";
				$tool_content .= "<td>" . $thepoll["end_date"] . "</td>";
				
				if ($is_adminOfCourse) 
					$tool_content .= "<td align=center><!--<a href='editpoll.php?pid={$pid}'>".$langPollEdit."</a> | -->".
						"<a href='deletepoll.php?pid={$pid}'><img src='../../template/classic/img/delete.gif' border='0'></a> </td><td align=center> ".
						"<a href='".$visibility_func."poll.php?pid={$pid}'><img src='../../template/classic/img/".$visibility_gif.".gif' border='0'></a>  ".
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
/////////////////////////////////////////////
			}
			}
			$tool_content .= "</tbody></table><br>";
		}
		
	}

?>
