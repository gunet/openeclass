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
	pollparticipate.php
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
 
  	@TODO: eliminate code duplication between document/document.php, scormdocument.php
==============================================================================
*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';

$tool_content = "";

if(!isset($_REQUEST['UseCase'])) $_REQUEST['UseCase'] = "";
if(!isset($_REQUEST['pid'])) die();

switch ($_REQUEST['UseCase']) {
case 1:
   printPollForm();
   break;
case 2:
   submitPoll();
   break;
default:
   printPollForm();
}

draw($tool_content, 2); 

function printPollForm() {
	global $currentCourse, $tool_content, $langName, $langPollStart, 
		$langPollEnd, $langPollContinue, $langPollInactive;
	
	$pid = htmlspecialchars($_REQUEST['pid']);
	
// *****************************************************************************
//		Get poll data
//******************************************************************************/
$CurrentQuestion = 0;
$CurrentAnswer = 0;
$poll = db_query("
	select * from poll 
	where pid='".mysql_real_escape_string($pid)."' "
	."ORDER BY pid", $currentCourse);
$thePoll = mysql_fetch_array($poll);
	
$temp_CurrentDate = date("Y-m-d H:i:s");
$temp_StartDate = $thePoll["start_date"];
$temp_EndDate = $thePoll["end_date"];

$temp_StartDate = mktime(substr($temp_StartDate, 11,2),substr($temp_StartDate, 14,2),substr($temp_StartDate, 17,2),substr($temp_StartDate, 5,2),substr($temp_StartDate, 8,2),substr($temp_StartDate, 0,4));
$temp_EndDate = mktime(substr($temp_EndDate, 11,2),substr($temp_EndDate, 14,2),substr($temp_EndDate, 17,2),substr($temp_EndDate, 5,2),substr($temp_EndDate, 8,2),substr($temp_EndDate, 0,4));
$temp_CurrentDate = mktime(substr($temp_CurrentDate, 11,2),substr($temp_CurrentDate, 14,2),substr($temp_CurrentDate, 17,2),substr($temp_CurrentDate, 5,2),substr($temp_CurrentDate, 8,2),substr($temp_CurrentDate, 0,4));

if (($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {
	$tool_content .= <<<cData
	<form action="pollparticipate.php" id="poll" method="post">
		<input type="hidden" value="2" name="UseCase">
		<input type="hidden" value="$pid" name="pid">
		
cData;
			$tool_content .= "<b>".$thePoll["name"]."</b>\n<br>";

///*****************************************************************************
//		Get answers + questions
//******************************************************************************/
	if ($thePoll["type"] == 1) { //MC
		$tool_content .= "\n<br><input type=\"hidden\" value=\"1\" name=\"PollType\"><br>\n";
		$questions = db_query("
		select * from poll_question 
		where pid='".mysql_real_escape_string($pid)."' "
		."ORDER BY pqid", $currentCourse);
		while ($theQuestion = mysql_fetch_array($questions)) {	
			++$CurrentQuestion;
			$tool_content .= "<br><br>".$theQuestion["question_text"]."<br><br>\n";
			$tool_content .= "<input type=\"hidden\" value=\"". 
				$theQuestion["question_text"] .
				"\" name=\"question" . $CurrentQuestion . "\">";
			$pqid=$theQuestion["pqid"];
			$answers = db_query("
				select * from poll_question_answer 
				where pqid=$pqid 
				ORDER BY pqaid", $currentCourse);
				while ($theAnswer = mysql_fetch_array($answers)) {
					$tool_content .= "\n<label><input type=\"radio\" ";
					$tool_content .= " name=\"answer" . $CurrentQuestion . "\" ";
					$tool_content .= " value=\"" . $theAnswer["answer_text"] . "\" ";
					$tool_content .= "> " . $theAnswer["answer_text"] . "</label><br>\n";
				}
				$tool_content .= "\n<label><input type=\"radio\" ";
				$tool_content .= " name=\"answer" . $CurrentQuestion . "\" ";
				$tool_content .= " value=\"" . "Δεν γνωρίζω/Δεν απαντώ" . "\" ";
				$tool_content .= " checked=\"checked\"  ";
				$tool_content .= "> " . "Δεν γνωρίζω/Δεν απαντώ" . "</label>\n";				
				
		}
		$tool_content .= "<br><br><br>";
	} else { //TF
		$tool_content .= "<br>\n<input type=\"hidden\" value=\"2\" name=\"PollType\"><br>\n";
		$questions = db_query("
		select * from poll_question 
		where pid='".mysql_real_escape_string($pid)."' "
		."ORDER BY pqid", $currentCourse);
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
			  <input name="$langPollContinue" type="submit" value="$langPollContinue -&gt;">
		</form>
cData;
} else {
		$tool_content .= $langPollInactive;
}	
	
	
}
function submitPoll() {
	global $tool_content, $user_id ;
	
	// first populate poll_answer
	$creator_id = $GLOBALS['uid'];
	$CreationDate = date("Y-m-d H:m:s");
	$pid = htmlspecialchars($_POST['pid']);
	$aid = date("YmdHms"); 
	mysql_select_db($GLOBALS['currentCourseID']);
	$result = db_query("INSERT INTO poll_answer VALUES ('".
	mysql_real_escape_string($aid) . "','".
	mysql_real_escape_string($creator_id) . "','".
	mysql_real_escape_string($pid) . "','".
	$CreationDate ."')");
	if ($_POST["PollType"] == 1) { // MC
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
				$result2 = db_query("INSERT INTO poll_answer_record VALUES ('0','".
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
				$result2 = db_query("INSERT INTO poll_answer_record VALUES ('0','".
					mysql_real_escape_string($aid). "','".
					mysql_real_escape_string($QuestionText). "','".
					mysql_real_escape_string($QuestionAnswer) ."')");
				}
			}  
		}
	
	$GLOBALS["tool_content"] .= "<center>".$GLOBALS["langPollSubmitted"]."</center>";
}

?>
