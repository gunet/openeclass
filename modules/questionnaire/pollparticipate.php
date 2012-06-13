<?php
/* ========================================================================
 * Open eClass 2.4
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

/*===========================================================================
	pollparticipate.php
	@last update: 26-5-2006 by Dionysios Synodinos
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================
*/

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';

$nameTools = $langParticipate;
$navigation[] = array("url"=>"questionnaire.php?course=$course_code", "name"=> $langQuestionnaire);

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
	global $mysqlMainDb, $course_id, $course_code, $tool_content, $langPollStart, 
	$langPollEnd, $langSubmit, $langPollInactive, $langPollUnknown;
	
	$pid = intval($_REQUEST['pid']);
	
	// *****************************************************************************
	//		Get poll data
	//******************************************************************************/

	$poll = db_query("SELECT * FROM poll WHERE course_id = $course_id AND pid='".mysql_real_escape_string($pid)."' "
		."ORDER BY pid", $mysqlMainDb);
	$thePoll = mysql_fetch_array($poll);
	$temp_CurrentDate = date("Y-m-d H:i");
	$temp_StartDate = $thePoll["start_date"];
	$temp_EndDate = $thePoll["end_date"];
	$temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
	$temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
	$temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));
	
	if (($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {
		$tool_content .= "
	<form action='$_SERVER[PHP_SELF]?course=$course_code' id='poll' method='post'>
	<input type='hidden' value='2' name='UseCase' />
	<input type='hidden' value='$pid' name='pid' />

        <p class=\"title1\">".$thePoll["name"]."</p>\n";

		//*****************************************************************************
		//		Get answers + questions
		//******************************************************************************/
		$questions = db_query("SELECT * FROM poll_question 
			WHERE pid=" . intval($pid) . " ORDER BY pqid", $mysqlMainDb);
		while ($theQuestion = mysql_fetch_array($questions)) {
			$pqid = $theQuestion["pqid"];
			$qtype = $theQuestion["qtype"];
			$tool_content .= "
        <p class=\"sub_title1\"><b>".$theQuestion["question_text"]."</b></p>
        <p>
	<input type='hidden' name='question[$pqid]' value='$qtype' />";
			if ($qtype == 'multiple') {
				$answers = db_query("SELECT * FROM poll_question_answer 
					WHERE pqid=$pqid ORDER BY pqaid", $mysqlMainDb);
				while ($theAnswer = mysql_fetch_array($answers)) {
					$tool_content .= "
        <label><input type='radio' name='answer[$pqid]' value='$theAnswer[pqaid]' />$theAnswer[answer_text] </label><br />\n";
				}
				$tool_content .= "
        <label><input type='radio' name='answer[$pqid]' value='-1' checked='checked' />$langPollUnknown</label>\n";
			} else {
				$tool_content .= "
        <label><textarea cols='40' rows='3' name='answer[$pqid]'></textarea></label>\n";
			}
			$tool_content .= "<br /><br />";
		}
		$tool_content .= "
        <input name='submit' type='submit' value='$langSubmit' />
        </p>
        </form>";
	} else {
		$tool_content .= $langPollInactive;
	}	
}


function submitPoll() {
	global $tool_content, $course_code, $user_id, $langPollSubmitted, $langBack;
	
	// first populate poll_answer
	$user_id = $GLOBALS['uid'];
	$CreationDate = date("Y-m-d H:i");
	$pid = intval($_POST['pid']);
	mysql_select_db($GLOBALS['mysqlMainDb']);
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
	$tool_content .= "<p class='success'>".$langPollSubmitted."<br /><a href=\"questionnaire.php?course=$course_code\">".$langBack."</a></p>";
}
