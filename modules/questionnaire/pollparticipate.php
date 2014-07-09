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

/*===========================================================================
	pollparticipate.php
	@last update: 26-5-2006 by Dionysios Synodinos
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================
*/

$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

$nameTools = $langParticipate;
if ($is_editor) {
    $nameTools .= " ($langSee)";
}

$navigation[] = array('url' => "questionnaire.php?course=$code_cours", 'name' => $langQuestionnaire);

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
    global $currentCourse, $code_cours, $tool_content, $langPollStart, 
        $langPollEnd, $langSubmit, $langPollInactive, $langPollUnknown, $uid,
        $langPollAlreadyParticipated, $langBack, $is_editor;
	
    $pid = intval($_REQUEST['pid']);
	
    // check if user has participated
    $has_participated = mysql_fetch_array(db_query("SELECT COUNT(*) FROM poll_answer_record
        WHERE user_id = $uid AND pid = $pid"));
    if ($has_participated[0] > 0){
        $tool_content .= "<p class='alert1'>".$langPollAlreadyParticipated."<br /><a href=\"questionnaire.php?course=$code_cours\">".$langBack."</a></p>";
        draw($tool_content, 2, null);
        exit();
    }                
	// *****************************************************************************
	//		Get poll data
	//******************************************************************************/

	$poll = db_query("SELECT * FROM poll WHERE pid='".mysql_real_escape_string($pid)."' "
		."ORDER BY pid", $currentCourse);
	$thePoll = mysql_fetch_array($poll);
	$temp_CurrentDate = date('Y-m-d H:i');
	$temp_StartDate = $thePoll['start_date'];
	$temp_EndDate = $thePoll['end_date'];
	$temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
	$temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
	$temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));
	
    if ($thePoll['description']) {
        $tool_content .= $thePoll['description'];
    }
	if (($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {
		$tool_content .= "
	<form action='$_SERVER[SCRIPT_NAME]?course=$code_cours' id='poll' method='post'>
	<input type='hidden' value='2' name='UseCase' />
	<input type='hidden' value='$pid' name='pid' />

        <p class='title1'>".q($thePoll['name'])."</p>\n";

		//*****************************************************************************
		//		Get answers + questions
		//******************************************************************************/
		$questions = db_query("SELECT * FROM poll_question 
			WHERE pid = " . intval($pid) . " ORDER BY qorder", $currentCourse);
		while ($theQuestion = mysql_fetch_array($questions)) {
			$pqid = $theQuestion['pqid'];
			$qtype = $theQuestion['qtype'];
			$tool_content .= "
                <p class='sub_title1'><b>".$theQuestion['question_text']."</b></p>
                <p>
                <input type='hidden' name='question[$pqid]' value='$qtype' />";
			if ($qtype == QTYPE_SINGLE or $qtype == QTYPE_MULTIPLE) {
                $name_ext = ($qtype == QTYPE_SINGLE)? '': '[]';
                $type_attr = ($qtype == QTYPE_SINGLE)? "type='radio'": "type='checkbox'";
				$answers = db_query("SELECT * FROM poll_question_answer 
					WHERE pqid=$pqid ORDER BY pqaid", $currentCourse);
				while ($theAnswer = mysql_fetch_array($answers)) {
					$tool_content .= "<label><input $type_attr name='answer[$pqid]$name_ext' value='$theAnswer[pqaid]' />$theAnswer[answer_text] </label><br />\n";
				}
                if ($qtype == QTYPE_SINGLE) {
    				$tool_content .= "<label><input type='radio' name='answer[$pqid]' value='-1' checked='checked' />$langPollUnknown</label>\n";
                }
			} elseif ($qtype == QTYPE_FILL) {
				$tool_content .= "<label><textarea cols='40' rows='3' name='answer[$pqid]'></textarea></label>\n";
			}
			$tool_content .= "<br /><br />";
		}
        if ($is_editor) {
            $tool_content .= "<p><a href='questionnaire.php?course=$code_cours'>".q($langBack)."</a></p>";
        } else {
            $tool_content .= "<input name='submit' type='submit' value='".q($langSubmit)."'></p></form>";
        }
	} else {
		$tool_content .= $langPollInactive;
	}	
}


function submitPoll() {
	global $tool_content, $code_cours, $user_id, $langPollSubmitted, $langBack;
	
	// first populate poll_answer
	$user_id = $GLOBALS['uid'];
	$pid = intval($_POST['pid']);
	mysql_select_db($code_cours);
	$answer = $_POST['answer'];
	foreach ($_POST['question'] as $pqid => $qtype) {
        $pqid = intval($pqid);
        if ($qtype == QTYPE_MULTIPLE) {
            foreach ($answer[$pqid] as $aid) {
                $aid = intval($aid);
                db_query("INSERT INTO poll_answer_record (pid, qid, aid, answer_text, user_id, submit_date)
                    VALUES ($pid, $pqid, $aid, '', $user_id , NOW())");
            }
            continue;
        } elseif ($qtype == QTYPE_SINGLE) {
			$aid = intval($answer[$pqid]);
			$answer_text = "''";
		} elseif ($qtype == QTYPE_FILL) {
			$answer_text = quote($answer[$pqid]);
			$aid = 0;
		} else {
            continue;
        }
		db_query("INSERT INTO poll_answer_record (pid, qid, aid, answer_text, user_id, submit_date)
			VALUES ($pid, $pqid, $aid, $answer_text, $user_id, NOW())");
	}
	$tool_content .= "<p class='success'>".$langPollSubmitted."<br /><a href=\"questionnaire.php?course=$code_cours\">".$langBack."</a></p>";
}
