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
 
  	@todo: eliminate code duplication between
 	document/document.php, scormdocument.php
==============================================================================
*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';

$nameTools = $langSurveyCharts;
$navigation[] = array("url"=>"questionnaire.php", "name"=> $langQuestionnaire);

$tool_content = "";
$total_answers = 0;

if(!isset($_GET['sid']) || !is_numeric($_GET['sid'])) die();

	$tool_content = "\n<!-- BEGIN SURVEY -->\n";
	$current_survey = db_query("
		select * from survey 
		where sid=".mysql_real_escape_string($_GET['sid'])." "
		."ORDER BY sid", $currentCourse);
	$theSurvey = mysql_fetch_array($current_survey);
	$tool_content .= "<b>" . $theSurvey["name"] . "</b></b><br><br>";
	$tool_content .= "$langSurveyDateCreated : <b>" . $theSurvey["creation_date"] . "</b><br><br>";
	$tool_content .= "$langSurveyStart <b>" . $theSurvey["start_date"] . "</b> ";
	$tool_content .= "$langSurveyEnd <b>" . $theSurvey["end_date"] . "</b><br><br>";

if(!isset($_GET['type']) || !is_numeric($_GET['type'])) $_GET['type'] = 0;

if ($_GET['type'] == 2) { //TF

	$answers = db_query("
	select * from survey_answer 
	where sid=".mysql_real_escape_string($_GET['sid'])." "
	."ORDER BY sid", $currentCourse);
	
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
			select * from survey_answer_record 
			where aid=$aid 
			ORDER BY aid", $currentCourse);
			while ($theQAs = mysql_fetch_array($qas)) {	
			$tool_content .= "<br>" . $theQAs["question_text"]. ": <br>" . $theQAs["question_answer"] . "<br>";
			}
			$tool_content .= "<br>";
			$tool_content .= "</td></tr></table><br><br>";
			$tool_content .= "<b>" . $langSurveyTotalAnswers . ": " . $total_answers . "</b><br>";
	}
} else { //MC
	$tool_content .= "\n<!-- BEGIN MC -->\n";
	
	
	$total_answers = 0;
	
// Get data to print pie chart ////////////////////////////////////////////////////

	require_once '../../include/libchart/libchart.php';
	//$chart = new PieChart(600, 300);
	//$chart->setTitle("������������ ������������");
	
	$answers = db_query("
		select * from survey_answer 
		where sid=".mysql_real_escape_string($_GET['sid'])." "
		."ORDER BY sid", $currentCourse);
		
	while ($theAnswer = mysql_fetch_array($answers)) {
		++$total_answers;
		
		$aid = $theAnswer["aid"];
		
		$arids = db_query("
			select arid from survey_answer_record 
			where aid=$aid 
			ORDER BY aid", $currentCourse);
		
		while ($theArid = mysql_fetch_array($arids)) {
			// Create array to hold IDs to ANSWER_RECORDs for current survey
			$arid_GD[] = $theArid["arid"]; 
			
			// Get the text of questions
			$q_ts = db_query("
				select question_text from survey_answer_record 
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
		}
	}
	$tool_content .= $langSurveyTotalAnswers . ": " . $total_answers . "</b><br>";
	
	if (isset($q_t_GD)) {
/*****************************************************************************
		Print graphs
******************************************************************************/
			//$chart->reset();
			$tool_content .= "<br><br><b>" . $langCollectiveCharts . "</b><br>";
			for ($i = 0; $i < count($q_t_GD); $i++) {
   		
   			$chart = new PieChart(600, 300);
   			
   		 $current_q_t = $q_t_GD[$i];
   		
   		$q_as = db_query("
			select question_answer from survey_answer_record 
			where question_text='$current_q_t' 
			ORDER BY arid", $currentCourse);
			
			$q_a_GD = array();
			while ($theQ_As = mysql_fetch_array($q_as)) {
				$v = $theQ_As["question_answer"];
				//$tool_content .= "<br>".$v."<br>";
				if (!count($q_a_GD)) {
					$q_a_GD[$v] = 1; 
				} else {
   					if (array_key_exists($v,$q_a_GD))
   						++$q_a_GD[$v];
   					else
   						$q_a_GD[$v] = 1;
				}
			}

			$chart->setTitle("$q_t_GD[$i]");
			
			foreach ($q_a_GD as $k => $v) {
   			//echo "\$a[$k] => $v.\n";
   			$percentage = 100*($v/$total_answers);
   			$label = $q_a_GD["$k"]; 
   			$chart->addPoint(new Point("$k ($percentage)", $percentage));
			}
				
			$chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';
			$chart->render($webDir.$chart_path);
			
			$tool_content .= '<br><table width="100%"><tr><td><img src="'.$urlServer.$chart_path.'" /></td></tr></table><br>';
			
		}
	}


/*****************************************************************************
 Print individual results 
******************************************************************************/

// display individual results
  $tool_content .= "<br><br><b>" . $langIndividuals . "</b><br><br>";

	$answers = db_query("
	select * from survey_answer 
	where sid=".mysql_real_escape_string($_GET['sid'])." "
	."ORDER BY sid", $currentCourse);
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
			select * from survey_answer_record 
			where aid=$aid 
			ORDER BY aid", $currentCourse);
			while ($theQAs = mysql_fetch_array($qas)) {	
				$tool_content .= "<br>" . $theQAs["question_text"]. ": <br>" . $theQAs["question_answer"] . "<br>";
			}
			$tool_content .= "<br>";
			$tool_content .= "</td></tr></table><br><br>";
			
	}
}
/*****************************************************************************
		Print the page
******************************************************************************/
draw($tool_content, 2); 

?>
