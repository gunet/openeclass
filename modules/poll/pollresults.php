<?php
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
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
	pollresults.php
	@last update: 26-5-2006 by Dionysios Synodinos
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the poll tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights - to create polls.

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
 
  	@todo: eliminate code duplication between
 	document/document.php, scormdocument.php
==============================================================================
*/

$require_current_course = TRUE;
$langFiles = 'poll';

$require_help = TRUE;
$helpTopic = 'poll';

include '../../include/baseTheme.php';

$tool_content = "";
$total_answers = 0;
$questions = array();
/////////////////////////////////////////////////////
$tool_content .= "<table>";

$total_answers_query = db_query("
	select * from poll_answer 
	where pid=$pid", $currentCourse);
while ($totalAnswer = mysql_fetch_array($total_answers_query)) {
	++$total_answers;
}
$results = db_query("
	select * from poll_answer 
	where pid=$pid", $currentCourse);
while ($qas = mysql_fetch_array($results)) {
	$count = count($questions);
	$check = 0;
	for ($i = 0; $i < $count; $i++) {
		if ($questions[$i] == $question_text) {
			$check = 1;
		}
	}
	if (!$check) 
		$questions[$question_text] = 0;  
}
	
$tool_content .= $totalAnswer;
//$tool_content .= $questions;
$tool_content .= "</table>";
//////////////////////////////////////////////////////

	$tool_content = "\n<!-- BEGIN SURVEY -->\n";
	$current_poll = db_query("
		select * from poll 
		where pid=$pid 
		ORDER BY pid", $currentCourse);
	$theSurvey = mysql_fetch_array($current_poll);
	$tool_content .= "<b>" . $theSurvey["name"] . "</b></b><br>";
	$tool_content .= $langPollCreation . ":" . $theSurvey["creation_date"] . "<br>";
	$tool_content .= $langPollStart . ":" . $theSurvey["start_date"] . "<br>";
	$tool_content .= $langPollEnd . ":" . $theSurvey["end_date"] . "<br><br>";

if ($type == 2) { //TF
	$tool_content .= "\n<!-- BEGIN TF -->\n";

	$answers = db_query("
	select * from poll_answer 
	where pid=$pid 
	ORDER BY pid", $currentCourse);
	
	while ($theAnswer = mysql_fetch_array($answers)) {
		++$total_answers;	
		$creator_id = $theAnswer["creator_id"];
		$aid = $theAnswer["aid"];
		$answer_creator = db_query("
			select nom,prenom from user 
			where user_id='$creator_id'", $mysqlMainDb);
		$theCreator = mysql_fetch_array($answer_creator);
		$tool_content .= "<table border=\"1\" width=\"100%\"><tr><td>";
		$tool_content .= "<br><b>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</b><br>";
		$qas = db_query("
			select * from poll_answer_record 
			where aid=$aid 
			ORDER BY aid", $currentCourse);
			while ($theQAs = mysql_fetch_array($qas)) {	
				$tool_content .= "<br>" . $theQAs["question_text"]. ": <br>" . $theQAs["question_answer"] . "<br>";
			}
			$tool_content .= "<br>";
			$tool_content .= "</td></tr></table><br><br>";
			$tool_content .= "<b>" . $langPollTotalAnswers . ": " . $total_answers . "</b><br>";
	}
} else { //MC
	$tool_content .= "\n<!-- BEGIN MC -->\n";

	
		
	$total_answers = 0;
	
// Print pie chart ////////////////////////////////////////////////////

	require_once '../../include/libchart/libchart.php';
	$chart = new PieChart(500, 250);
	$chart->setTitle("Αποτελέσματα Δημοσκόπισης");
	
	$answers = db_query("
		select * from poll_answer 
		where pid=$pid 
		ORDER BY pid", $currentCourse);
		
	while ($theAnswer = mysql_fetch_array($answers)) {
		$aid = $theAnswer["aid"];
		
		$arids = db_query("
			select arid from poll_answer_record 
			where aid=$aid 
			ORDER BY aid", $currentCourse);
		
		while ($theArid = mysql_fetch_array($arids)) {
			// Creat array to hold IDs to ANSWER_RECORDs for current poll
			$arid_GD[] = $theArid["arid"]; 
			
			// Get the text of questions
			$q_ts = db_query("
				select question_text from poll_answer_record 
				where aid=$aid 
				ORDER BY arid", $currentCourse);
			
			$q_t_GD = array(); // the array to hold the text of questions
			
			while ($theQ_Ts = mysql_fetch_array($q_ts)) {
				if (!count($q_t_GD)) {
					$q_t_GD[] = $theQ_Ts["question_text"]; 
				} else {
					$flag = 0;
					for ($i = 0; $i < count($q_t_GD); $i++) {
   					if ($q_t_GD[$i] == $theQ_Ts["question_text"]) 
   						++$flag;
					}
					if (!$flag) 
						$q_t_GD[] = $theQ_Ts["question_text"]; 
				}
			}
			for ($i = 0; $i < count($q_t_GD); $i++) {
   			
   			 $current_q_t = $q_t_GD[$i];
   			
   			//$chart->addPoint(new Point("Other (50)", 50));
   			
   			$q_as = db_query("
				select question_answer from poll_answer_record 
				where question_text=$current_q_t; 
				ORDER BY arid", $currentCourse);
   			
			}
		}
	}

$chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';
$chart->render($webDir.$chart_path);
$tool_content .= '<img src="'.$urlServer.$chart_path.'" />';		

// Print individual results ///////////////////////////////////////////
	$answers = db_query("
		select * from poll_answer 
		where pid=$pid 
		ORDER BY pid", $currentCourse);
	
	while ($theAnswer = mysql_fetch_array($answers)) {
		//++$total_answers;	
		$creator_id = $theAnswer["creator_id"];
		$aid = $theAnswer["aid"];
		$answer_creator = db_query("
			select nom,prenom from user 
			where user_id='$creator_id'", $mysqlMainDb);
		$theCreator = mysql_fetch_array($answer_creator);
		$tool_content .= "<table border=\"1\" width=\"100%\"><tr><td>";
		$tool_content .= "<br><b>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</b><br>";
		$qas = db_query("
			select * from poll_answer_record 
			where aid=$aid 
			ORDER BY aid", $currentCourse);
			while ($theQAs = mysql_fetch_array($qas)) {	
				$tool_content .= "<br>" . $theQAs["question_text"]. ": <br>" . $theQAs["question_answer"] . "<br>";
			}
			$tool_content .= "<br>";
			$tool_content .= "</td></tr></table><br><br>";
			
	}
}
$tool_content .= "<b>" . $langPollTotalAnswers . ": " . $total_answers . "</b><br>";
/*****************************************************************************
		Print the page
******************************************************************************/
draw($tool_content, 2); 


?>
