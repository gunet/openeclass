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
	addsurvey.php
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

$nameTools = $langSurveyCharts;
$navigation[] = array("url"=>"questionnaire.php", "name"=> $langQuestionnaire);

$tool_content = "";

if(!isset($_REQUEST['UseCase'])) $_REQUEST['UseCase'] = "";
if(!isset($_REQUEST['sid'])) die();

switch ($_REQUEST['UseCase']) {
case 1:
   printSurveyForm();
   break;
case 2:
   submitSurvey();
   break;
default:
   printSurveyForm();
}

$head_content = <<<hContent
<script type="text/javascript">
<!-- Begin

function checkrequired(which, entry) {
var pass=true;
temp_name = "answer1";
pass_temp = false;
counter_temp = 0;
if (document.images) {
	for (i=0;i<which.length;i++) {
		var tempobj=which.elements[i];
		if (tempobj.name.substring(0,6) == entry) {


			counter_temp++;
			//alert("Entered for : "+counter_temp+" "+tempobj.name);
			if (tempobj.checked) {
				//alert(counter_temp+" "+tempobj.name+"is checked so pass_temp = true");
				pass_temp = true;
		  }
			
			
			if (temp_name != tempobj.name) {
				//alert("Changing group since "+temp_name+" != "+tempobj.name);
				counter_temp = 0;
				if (pass_temp == false) {
					//alert("Last group was empty so pass = false");
					pass=false;
					break;
				} else {
					//alert("Last grup was cool so just changing names");
					temp_name = tempobj.name;
					if (tempobj.checked) {
						pass_temp = true;
		  		} else {
						pass_temp = false;
				}
				}
			}
			
			
		  
	  }
	  pass = pass_temp;
	}
}
if (!pass) {
	alert("$langQFillInAllQs");
	return false;
} else {
	return true;
}
}
//  End -->
</script>
hContent;

draw($tool_content, 2, '', $head_content); 

function printSurveyForm() {
	global $currentCourse, $tool_content, $langSurveyStart, 
		$langSurveyEnd, $langSurveyContinue, $langSurveyInactive;
		
	$sid = htmlspecialchars($_REQUEST['sid']);
	
// *****************************************************************************
//		Get survey data
//******************************************************************************/
$CurrentQuestion = 0;
$CurrentAnswer = 0;
$survey = db_query("
	select * from survey 
	where sid='".mysql_real_escape_string($sid)."' "
	."ORDER BY sid", $currentCourse);
$theSurvey = mysql_fetch_array($survey);

$temp_CurrentDate = date("Y-m-d H:i:s");
$temp_StartDate = $theSurvey["start_date"];
$temp_EndDate = $theSurvey["end_date"];

$temp_StartDate = mktime(substr($temp_StartDate, 11,2),substr($temp_StartDate, 14,2),substr($temp_StartDate, 17,2),substr($temp_StartDate, 5,2),substr($temp_StartDate, 8,2),substr($temp_StartDate, 0,4));

$temp_EndDate = mktime(substr($temp_EndDate, 11,2),substr($temp_EndDate, 14,2),substr($temp_EndDate, 17,2),substr($temp_EndDate, 5,2),substr($temp_EndDate, 8,2),substr($temp_EndDate, 0,4));

$temp_CurrentDate = mktime(substr($temp_CurrentDate, 11,2),substr($temp_CurrentDate, 14,2),substr($temp_CurrentDate, 17,2),substr($temp_CurrentDate, 5,2),substr($temp_CurrentDate, 8,2),substr($temp_CurrentDate, 0,4));
if (($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {

		$tool_content .= <<<cData
	<form action="surveyparticipate.php" id="survey" method="post" onSubmit="return checkrequired(this, 'answer')">
		<input type="hidden" value="2" name="UseCase">
		<input type="hidden" value="$sid" name="sid">
		
cData;
			$tool_content .= "<b>".$theSurvey["name"]."</b>\n<br><br>";
			$tool_content .= "$langSurveyStart : <b>".$theSurvey["start_date"]."</b><br>\n";
			$tool_content .= "$langSurveyEnd : <b>".$theSurvey["end_date"]."</b><br>";
///*****************************************************************************
//		Get answers + questions
//******************************************************************************/
	if ($theSurvey["type"] == 1) { //MC
		$tool_content .= "\n<br><input type=\"hidden\" value=\"1\" name=\"SurveyType\"><br>\n";
		$questions = db_query("
		select * from survey_question 
		where sid='".mysql_real_escape_string($sid)."' "
		."ORDER BY sqid", $currentCourse);
		while ($theQuestion = mysql_fetch_array($questions)) {	
			++$CurrentQuestion;
			$tool_content .= "\n\n<br><br>".$theQuestion["question_text"]."<br>\n";
			$tool_content .= "<input type=\"hidden\" value=\"". 
				$theQuestion["question_text"] .
				"\" name=\"question" . $CurrentQuestion . "\">";
			$sqid=$theQuestion["sqid"];
			$answers = db_query("
				select * from survey_question_answer 
				where sqid=$sqid 
				ORDER BY sqaid", $currentCourse);
				while ($theAnswer = mysql_fetch_array($answers)) {
					$tool_content .= "<br>";
					$tool_content .= "\n<label><input type=\"radio\" ";
					$tool_content .= " name=\"answer" . $CurrentQuestion . "\" ";
					$tool_content .= " value=\"" . $theAnswer["answer_text"] . "\" ";
					$tool_content .= "> " . $theAnswer["answer_text"] . "</label>\n";
				}
		}
		$tool_content .= "<br><br>";
	} else { //TF
		$tool_content .= "<br>\n<input type=\"hidden\" value=\"2\" name=\"SurveyType\"><br>\n";
		$questions = db_query("
		select * from survey_question 
		where sid='".mysql_real_escape_string($sid)."' "
		, $currentCourse); 
		while ($theQuestion = mysql_fetch_array($questions)) {	
			++$CurrentQuestion;
			$tool_content .= "\n\n<input type=\"hidden\" value=\"" . 
				$theQuestion["question_text"] . "\" name=\"question" . $CurrentQuestion . "\">".
				"\n<br><br><label>".$theQuestion["question_text"].
				": <input type=\"text\" name=\"answer". $CurrentQuestion. "\" /></label><br>";
		}
		$tool_content .= "<br><br>\n";
	}
		$tool_content .= <<<cData
			  <input name="$langSurveyContinue" type="submit" value="$langSurveyContinue -&gt;">
		</form>
cData;
} else {
	$tool_content .= $langSurveyInactive;
}	


}
function submitSurvey() {
	global $tool_content,$langQuestion,$langAnswer,$user_id ;
	
	// first populate survey_answer
	$creator_id = $GLOBALS['uid'];
	$CreationDate = date("Y-m-d H:m:s");
	$sid = htmlspecialchars($_POST['sid']);
	$aid = date("YmdHms"); 
	mysql_select_db($GLOBALS['currentCourseID']);
	$result = db_query("INSERT INTO survey_answer VALUES ('".
	mysql_real_escape_string($aid) . "','".
	mysql_real_escape_string($creator_id) . "','".
	mysql_real_escape_string($sid) . "','".
	mysql_real_escape_string($CreationDate) ."')");
	if ($_POST["SurveyType"] == 1) { // MC
		$counter_foreach = 0;
		$counter_qas = 0;
		$qas = (count($_POST)-4)/2;
		foreach (array_keys($_POST) as $key) {
			++$counter_foreach;
			$$key = $_POST[$key];
			if (($counter_foreach >= 4)&&
				($counter_foreach <= (count($_POST)-1))&&
				(!($counter_foreach%2))) {
				++$counter_qas;
				$QuestionText = $$key;
				$QuestionAnswer = "answer" . $counter_qas; 
				$QuestionAnswer = $_POST[$QuestionAnswer];
				mysql_select_db($GLOBALS['currentCourseID']);
				$result2 = db_query("INSERT INTO survey_answer_record VALUES ('0','".
					mysql_real_escape_string($aid). "','".
					mysql_real_escape_string($QuestionText). "','".
					mysql_real_escape_string($QuestionAnswer) ."')");
				}
			}  
		
		} else { // TF
		$counter_foreach = 0;
		$counter_qas = 0;
		$qas = (count($_POST)-4)/2;
		foreach (array_keys($_POST) as $key) {
			++$counter_foreach;
			$$key = $_POST[$key];
			if (($counter_foreach >= 4)&&
				($counter_foreach <= (count($_POST)-1))&&
				(!($counter_foreach%2))) {
				++$counter_qas;
				$QuestionText = $$key;
				$QuestionAnswer = "answer" . $counter_qas; 
				$QuestionAnswer = $_POST[$QuestionAnswer];
				//$tool_content .= "\$QuestionText = " . $QuestionText . " \$QuestionAnswer = " . $QuestionAnswer . "<br>\n";;
				mysql_select_db($GLOBALS['currentCourseID']);
				$result2 = db_query("INSERT INTO survey_answer_record VALUES ('0','".
					mysql_real_escape_string($aid). "','".
					mysql_real_escape_string($QuestionText). "','".
					mysql_real_escape_string($QuestionAnswer) ."')");
				}
			}  
		}
	$GLOBALS["tool_content"] .= $GLOBALS["langSurveySubmitted"];
}
?>
