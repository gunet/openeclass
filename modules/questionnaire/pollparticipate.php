<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

/* ===========================================================================
  pollparticipate.php
  @last update: 26-5-2006 by Dionysios Synodinos
  @authors list: Dionysios G. Synodinos <synodinos@gmail.com>
  ==============================================================================
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

$nameTools = $langParticipate;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langQuestionnaire);

if (!isset($_REQUEST['UseCase']))
    $_REQUEST['UseCase'] = "";
if (!isset($_REQUEST['pid']))
    die();
$p = Database::get()->querySingle("SELECT pid FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $_REQUEST['pid']);
if(!$p){
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

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
    $langPollEnd, $langSubmit, $langPollInactive, $langPollUnknown, $uid,
    $langPollAlreadyParticipated, $is_editor, $langBack;

    $pid = $_REQUEST['pid'];
    
    // check if user has participated
    $has_participated = Database::get()->querySingle("SELECT COUNT(*) AS count FROM poll_answer_record WHERE user_id = ?d AND pid = ?d", $uid, $pid)->count;
    if ($has_participated > 0){
        Session::set_flashdata($langPollAlreadyParticipated, 'alert1');
        redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);
    }        
    // *****************************************************************************
    //		Get poll data
    //******************************************************************************/

    $thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d "
            . "ORDER BY pid",$course_id, $pid);
    $temp_CurrentDate = date("Y-m-d H:i");
    $temp_StartDate = $thePoll->start_date;
    $temp_EndDate = $thePoll->end_date;
    $temp_StartDate = mktime(substr($temp_StartDate, 11, 2), substr($temp_StartDate, 14, 2), 0, substr($temp_StartDate, 5, 2), substr($temp_StartDate, 8, 2), substr($temp_StartDate, 0, 4));
    $temp_EndDate = mktime(substr($temp_EndDate, 11, 2), substr($temp_EndDate, 14, 2), 0, substr($temp_EndDate, 5, 2), substr($temp_EndDate, 8, 2), substr($temp_EndDate, 0, 4));
    $temp_CurrentDate = mktime(substr($temp_CurrentDate, 11, 2), substr($temp_CurrentDate, 14, 2), 0, substr($temp_CurrentDate, 5, 2), substr($temp_CurrentDate, 8, 2), substr($temp_CurrentDate, 0, 4));

    if (($temp_CurrentDate >= $temp_StartDate) && ($temp_CurrentDate < $temp_EndDate)) {
        $tool_content .= "
	<form action='$_SERVER[SCRIPT_NAME]?course=$course_code' id='poll' method='post'>
	<input type='hidden' value='2' name='UseCase' />
	<input type='hidden' value='$pid' name='pid' />

        <p class=\"title1\">" . q($thePoll->name) . "</p>\n";
        if ($thePoll->description) {
        $tool_content .= $thePoll->description.'<br>';
        }        

        //*****************************************************************************
        //		Get answers + questions
        //******************************************************************************/
        $questions = Database::get()->queryArray("SELECT * FROM poll_question
			WHERE pid = ?d ORDER BY pqid", $pid);
        foreach ($questions as $theQuestion) {           
            $pqid = $theQuestion->pqid;
            $qtype = $theQuestion->qtype;
            $tool_content .= "
            <div class='".(($qtype==QTYPE_LABEL)? 'q_comments' : 'sub_title1')."'><b>".(($qtype==QTYPE_LABEL)? ($theQuestion->question_text) : q($theQuestion->question_text))."</b></div>
            <p><input type='hidden' name='question[$pqid]' value='$qtype' />";
            if ($qtype == QTYPE_SINGLE || $qtype == QTYPE_MULTIPLE) {
                $name_ext = ($qtype == QTYPE_SINGLE)? '': '[]';
                $type_attr = ($qtype == QTYPE_SINGLE)? "type='radio'": "type='checkbox'";
                $answers = Database::get()->queryArray("SELECT * FROM poll_question_answer 
                            WHERE pqid = ?d ORDER BY pqaid", $pqid);
                foreach ($answers as $theAnswer) {
                    $tool_content .= "<label><input $type_attr name='answer[$pqid]$name_ext' value='$theAnswer->pqaid' />".q($theAnswer->answer_text)." </label><br />\n";
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
            $tool_content .= "<p><a href='index.php?course=$course_code'>".q($langBack)."</a></p>";
        } else {
            $tool_content .= "<input name='submit' type='submit' value='".q($langSubmit)."'></p></form>";
        }
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
    $answer = $_POST['answer'];
    foreach ($_POST['question'] as $pqid => $qtype) {
        $pqid = intval($pqid);
        if ($qtype == QTYPE_MULTIPLE) {
            foreach ($answer[$pqid] as $aid) {
                $aid = intval($aid);
                Database::get()->query("INSERT INTO poll_answer_record (pid, qid, aid, answer_text, user_id, submit_date)
                    VALUES (?d, ?d, ?d, '', ?d , NOW())", $pid, $pqid, $aid, $user_id);
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
        Database::get()->query("INSERT INTO poll_answer_record (pid, qid, aid, answer_text, user_id, submit_date)
			VALUES (?d, ?d, ?d, ?s, ?d , ?t)", $pid, $pqid, $aid, $answer_text, $user_id, $CreationDate);
    }
    $end_message = Database::get()->querySingle("SELECT end_message FROM poll WHERE pid = ?d", $pid)->end_message;
    $tool_content .= "<p class='success'>".$langPollSubmitted."</p>";
    if (!empty($end_message)) {
        $tool_content .=  $end_message;
    }
    $tool_content .= "<br /><p class=\"right\"><a href=\"index.php?course=$course_code\">".$langBack."</a></p>";
}
