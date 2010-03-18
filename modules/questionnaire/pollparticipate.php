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
	pollparticipate.php
	@last update: 26-5-2006 by Dionysios Synodinos
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================
*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';

$nameTools = $langParticipate;
$navigation[] = array("url"=>"questionnaire.php", "name"=> $langQuestionnaire);
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
	global $currentCourse, $tool_content, $langPollStart, 
	$langPollEnd, $langSubmit, $langPollInactive, $langPollUnknown;
	
	$pid = intval($_REQUEST['pid']);
	
	// *****************************************************************************
	//		Get poll data
	//******************************************************************************/

	$poll = db_query("SELECT * FROM poll WHERE pid='".mysql_real_escape_string($pid)."' "
		."ORDER BY pid", $currentCourse);
	$thePoll = mysql_fetch_array($poll);
	$temp_CurrentDate = date("Y-m-d");
	$temp_StartDate = $thePoll["start_date"];
	$temp_EndDate = $thePoll["end_date"];
	$temp_StartDate = mktime(0, 0, 0, substr($temp_StartDate, 5,2), substr($temp_StartDate, 8,2),substr($temp_StartDate, 0,4));
	$temp_EndDate = mktime(0, 0, 0, substr($temp_EndDate, 5,2), substr($temp_EndDate, 8,2), substr($temp_EndDate, 0,4));
	$temp_CurrentDate = mktime(0, 0 , 0,substr($temp_CurrentDate, 5,2), substr($temp_CurrentDate, 8,2),substr($temp_CurrentDate, 0,4));
	
	if (($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {
		$tool_content .= <<<cData
	<p>
	<form action="$_SERVER[PHP_SELF]" id="poll" method="post">
		<input type="hidden" value="2" name="UseCase">
		<input type="hidden" value="$pid" name="pid">
		
cData;
		$tool_content .= "<div id=\"topic_title_id\">".$thePoll["name"]."</div>\n";

		//*****************************************************************************
		//		Get answers + questions
		//******************************************************************************/
		$questions = db_query("SELECT * FROM poll_question 
			WHERE pid=" . intval($pid) . " ORDER BY pqid", $currentCourse);
		while ($theQuestion = mysql_fetch_array($questions)) {
			$pqid = $theQuestion["pqid"];
			$qtype = $theQuestion["qtype"];
			$tool_content .= "<p><b>".$theQuestion["question_text"]."</b><br>\n" .
				"<input type='hidden' name='question[$pqid]' value='$qtype'>";
			if ($qtype == 'multiple') {
				$answers = db_query("SELECT * FROM poll_question_answer 
					WHERE pqid=$pqid ORDER BY pqaid", $currentCourse);
				while ($theAnswer = mysql_fetch_array($answers)) {
					$tool_content .= "\n<label><input type='radio' name='answer[$pqid]' value='$theAnswer[pqaid]'>$theAnswer[answer_text]</label><br>\n";
				}
				$tool_content .= "\n<label><input type='radio' name='answer[$pqid]' value='-1' checked='checked'>$langPollUnknown</label>\n";
			} else {
				$tool_content .= "\n<label><textarea cols='40' rows='3' name='answer[$pqid]'></textarea>\n";
			}
			$tool_content .= "<br><br><br>";
		}
		$tool_content .= "<input name='submit' type='submit' value='$langSubmit'></form></p>";
	} else {
		$tool_content .= $langPollInactive;
	}	
}


function submitPoll() {
	global $tool_content, $user_id ;
	
	// first populate poll_answer
	$user_id = $GLOBALS['uid'];
	$CreationDate = date("Y-m-d");
	$pid = intval($_POST['pid']);
	mysql_select_db($GLOBALS['currentCourseID']);
	$answer = $_POST['answer'];
	foreach ($_POST['question'] as $pqid => $qtype) {
		$pqid = intval($pqid);
		if ($qtype == 'multiple') {
			$aid = intval($answer[$pqid]);
			$answer_text = "''";
		} else {
			$answer_text = quote($answer[$pqid]);
			$aid = 0;
		}
		db_query("INSERT INTO poll_answer_record (pid, qid, aid, answer_text, user_id, submit_date)
			VALUES ($pid, $pqid, $aid, $answer_text, $user_id , '$CreationDate')");
	}
	$GLOBALS["tool_content"] .= "<p class='alert1'>".$GLOBALS["langPollSubmitted"]."</p>";
}
