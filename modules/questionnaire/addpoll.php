<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

// -------------- jscalendar -----------------
require_once 'include/jscalendar/calendar.php';

$jscalendar = new DHTML_Calendar($urlServer . 'include/jscalendar/', $language, 'calendar-blue2', false);
$head_content = $jscalendar->get_load_files_code();

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);
$nameTools = $langCreatePoll;

if (isset($_REQUEST['pid'])) {
    $pid = intval($_REQUEST['pid']);
    $nameTools = $langEditPoll;
}

if (isset($_GET['edit']) and isset($pid)) {
    if (check_poll_participants($pid)) {
        $tool_content .= "$langThereAreParticipants";
        $tool_content .= "<br ><br /><div align='right'><a href='index.php?course=$course_code'>$langBack</a></div>";
        draw($tool_content, 2, null, $head_content);
        exit();
    } else {
        fill_questions($pid);
    }
}

if (isset($_POST['PollCreate'])) {
    if (isset($_POST['question']) and questions_exist()) {
        if (isset($pid)) {
            editPoll($pid, $_POST['question'], $_POST['question_type']);
        } else {
            createPoll($_POST['question'], $_POST['question_type']);
        }
        draw($tool_content, 2, null, $head_content);
        exit;
    } else {
        $tool_content .= "$langPollEmpty<br />";
    }
}

printPollCreationForm();
draw($tool_content, 2, null, $head_content);

/* * ***************************************************************************
  Fill the appropriate $_POST values from the database as if poll $pid was submitted
 * **************************************************************************** */

function fill_questions($pid) {
    global $course_id;
    $poll = mysql_fetch_array(db_query("SELECT * FROM poll WHERE course_id = $course_id AND pid=$pid"));
    $_POST['PollName'] = $poll['name'];
    $_POST['PollStart'] = $poll['start_date'];
    $_POST['PollEnd'] = $poll['end_date'];
    $questions = db_query("SELECT * FROM poll_question WHERE pid=$pid ORDER BY pqid");
    $_POST['question'] = array();
    $qnumber = 0;
    while ($theQuestion = mysql_fetch_array($questions)) {
        $_POST['question'][$qnumber] = $theQuestion['question_text'];
        $qtype = ($theQuestion['qtype'] == 'multiple') ? 1 : 2;
        $pqid = $theQuestion['pqid'];
        $_POST['question_type'][$qnumber] = $qtype;
        if ($qtype == 1) {
            $answers = db_query("SELECT * FROM poll_question_answer
					WHERE pqid=$pqid ORDER BY pqaid");
            $_POST['answer' . $qnumber] = array();
            while ($theAnswer = mysql_fetch_array($answers)) {
                $_POST['answer' . $qnumber][] = $theAnswer['answer_text'];
            }
        }
        $qnumber++;
    }
}

/* * ***************************************************************************
  Create the HTML for a jscalendar field
 * **************************************************************************** */

function jscal_html($name, $u_date = FALSE) {
    global $jscalendar;
    if (!$u_date) {
        $u_date = strftime('%Y-%m-%d %H:%M', strtotime('now -0 day'));
    }

    $cal = $jscalendar->make_input_field(
            array('showsTime' => true,
        'showOthers' => true,
        'ifFormat' => '%Y-%m-%d %H:%M'), array('style' => '',
        'name' => $name,
        'value' => $u_date));
    return $cal;
}

/* * ***************************************************************************
  Prints the new poll creation form
 * **************************************************************************** */

function printPollCreationForm() {
    global $tool_content, $langTitle, $langPollStart, $langPollAddMultiple, $langPollAddFill,
    $langPollEnd, $langPollMC, $langPollFillText, $langPollContinue, $langCreatePoll,
    $nameTools, $pid, $langSurvey, $langSelection, $course_code;

    if (isset($_POST['PollName'])) {
        $PollName = htmlspecialchars($_POST['PollName']);
    } else {
        $PollName = '';
    }
    if (isset($_POST['PollStart'])) {
        $PollStart = jscal_html('PollStart', $_POST['PollStart']);
    } else {
        $PollStart = jscal_html('PollStart');
    }
    if (isset($_POST['PollEnd'])) {
        $PollEnd = jscal_html('PollEnd', $_POST['PollEnd']);
    } else {
        $PollEnd = jscal_html('PollEnd', strftime('%Y-%m-%d %H:%M', strtotime('now +1 year')));
    }
    if (isset($pid)) {
        $pidvar = "<input type='hidden' name='pid' value='$pid'>";
    } else {
        $pidvar = '';
    }
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?course=$course_code' id='poll' method='post'>";
    $tool_content .= "
        <div id=\"operations_container\">
          <ul id=\"opslist\">
           <li>$langSelection:&nbsp;
               <input type='submit' name='MoreMultiple' value='$langPollAddMultiple' />&nbsp;&nbsp;
	       <input type='submit' size=\"5\" name='MoreFill' value='$langPollAddFill' />
           </li>
	  </ul>
	</div>

        <fieldset>
        <legend>$langSurvey</legend>
	<table width=\"100%\" class='tbl'>
	<tr>
	  <th width='100'>$langTitle:</th>
	  <td><input type='text' size='50' name='PollName' value='$PollName'></td>
	</tr>
	<tr>
	  <th>$langPollStart:</th>
	  <td>$PollStart</td></tr>
	<tr>
	  <th>$langPollEnd:</th>
	  <td>$PollEnd</td>
	</tr>
	</table>
        <br />";

    if (isset($_POST['question'])) {
        $questions = $_POST['question'];
        $question_types = $_POST['question_type'];
    } else {
        $questions = array();
        $question_types = array();
    }
    if (isset($_POST['MoreMultiple'])) {
        $questions[] = '';
        $question_types[] = 1;
    } elseif (isset($_POST['MoreFill'])) {
        $questions[] = '';
        $question_types[] = 2;
    }
    printQuestionForm($questions, $question_types);
    if (isset($pid)) {
        $tool_content .= "
        <input type='hidden' name='pid' value='$pid'>";
    }
    $tool_content .= '
        <hr />
        <table width="100%" class="tbl">
	<tr>
	  <th>&nbsp;</th>
	  <td class="right">
	  <input type="submit" name="PollCreate" value="' . $nameTools . '">
	  </td>
	</tr>
	</table>
        </fieldset>
	</form>';
}

/* * ***************************************************************************
  Prints a form to edit current questions and answers
 * **************************************************************************** */

function printQuestionForm($questions, $question_types) {
    global $tool_content, $langPollMC, $langPollFillText, $langPollContinue,
    $langCreate, $langPollMoreQuestions,
    $langPollCreated, $MoreQuestions;

    foreach ($questions as $i => $text) {
        if ($question_types[$i] == 1) {
            add_multiple_choice_question($i, $text);
        } else {
            add_fill_text_question($i, $text);
        }
    }
}

// ----------------------------------------
// Insert questions and answers for poll $pid
// ----------------------------------------
function insertPollQuestions($pid, $questions, $question_types) {
    global $langPollEmptyAnswers;

    foreach ($questions as $i => $QuestionText) {
        $QuestionText = trim($QuestionText);
        if (!empty($QuestionText)) {
            $qtype = ($question_types[$i] == 1) ? 'multiple' : 'fill';
            db_query("INSERT INTO poll_question (pid, question_text, qtype) VALUES ('" .
                    mysql_real_escape_string($pid) . "','" .
                    mysql_real_escape_string($QuestionText) . "', '$qtype')");
            $pqid = mysql_insert_id();
            if ($question_types[$i] == 1) {
                if (isset($_POST['answer' . $i])) {
                    $answers = $_POST['answer' . $i];
                } else {
                    die("$langPollEmptyAnswers $i");
                }
                foreach ($answers as $j => $AnswerText) {
                    $AnswerText = trim($AnswerText);
                    if (!empty($AnswerText)) {
                        db_query("INSERT INTO poll_question_answer (pqid, answer_text)
							VALUES ($pqid, '" . mysql_real_escape_string($AnswerText) . "')");
                    }
                }
            }
        }
    }
}

// ----------------------------------------
// Create a Poll
// ----------------------------------------
function createPoll($questions, $question_types) {
    global $tool_content, $course_code, $langPollCreated, $langBack;

    $CreationDate = date("Y-m-d H:i");
    $PollName = $_POST['PollName'];
    $StartDate = $_POST['PollStart'];
    $EndDate = $_POST['PollEnd'];
    $PollActive = 1;

    mysql_select_db($GLOBALS['mysqlMainDb']);
    $result = db_query("INSERT INTO poll
		(course_id, creator_id, name, creation_date, start_date, end_date, active)
		VALUES ('" .
            $GLOBALS['course_id'] . "','" .
            $GLOBALS['uid'] . "','" .
            mysql_real_escape_string($PollName) . "','" .
            mysql_real_escape_string($CreationDate) . "','" .
            mysql_real_escape_string($StartDate) . "','" .
            mysql_real_escape_string($EndDate) . "','" .
            mysql_real_escape_string($PollActive) . "')");
    $pid = mysql_insert_id();
    insertPollQuestions($pid, $questions, $question_types);
    $tool_content .= "<p class='success'>" . $langPollCreated . "</p><a href='index.php?course=$course_code'>" . $langBack . "</a>";
}

// ----------------------------------------
// Modify existing Poll
// ----------------------------------------
function editPoll($pid, $questions, $question_types) {
    global $pid, $tool_content, $course_id, $course_code, $langPollEdited, $langBack;

    $PollName = $_POST['PollName'];
    $StartDate = $_POST['PollStart'];
    $EndDate = $_POST['PollEnd'];

    mysql_select_db($GLOBALS['mysqlMainDb']);
    $result = db_query("UPDATE poll SET name = '$PollName',
		start_date = '$StartDate', end_date = '$EndDate' WHERE course_id = $course_id AND pid='$pid'");
    db_query("DELETE FROM poll_question_answer WHERE pqid IN
		(SELECT pqid FROM poll_question WHERE pid='$pid')");
    db_query("DELETE FROM poll_question WHERE pid='$pid'");
    insertPollQuestions($pid, $questions, $question_types);
    $tool_content .= "<p class='success'>" . $langPollEdited . "</p><a href='index.php?course=$course_code'>" . $langBack . "</a>";
}

/* * ***************************************************************************
  Add multiple choice question $i to $tool_content
 * **************************************************************************** */

function add_multiple_choice_question($i, $text) {
    global $tool_content, $langQuestion, $langPollMoreAnswers, $langAnswers,
    $langPollUnknown, $langPollFillText, $langPollNumAnswers,
    $langPollAddAnswer, $langPollMC, $themeimg;

    $tool_content .= "
        <hr />
        <table width=\"100%\" class='tbl'>
	<tr>
	  <td width='150'><b>$langQuestion #" . ($i + 1) . "</b>&nbsp;&nbsp;&nbsp;</td>
          <td>
	    <input type='text' name='question[$i]' value='$text' size='52' />" . "
	    <input type='hidden' name='question_type[$i]' value='1' />&nbsp;($langPollMC)
	  </td>
	</tr>";
    if (isset($_POST['answer' . $i])) {
        $answers = $_POST['answer' . $i];
    } else {
        $answers = array('', '');
    }
    if (isset($_POST['MoreAnswers' . $i])) {
        $answers[] = '';
    }
    $tool_content .= "
        <tr>
          <td width='80'><b>$langAnswers:</b></td>
          <td>
          $langPollAddAnswer: <input type='submit' name='MoreAnswers$i' value='$langPollMoreAnswers' /></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>";
    foreach ($answers as $j => $answertext) {
        $tool_content .= "<img src='$themeimg/arrow.png' title='$langPollNumAnswers'>&nbsp;&nbsp;<input type='text' name='answer${i}[]' value='$answertext' size='50'><br /><br />";
    }
    $tool_content .= "<img src='$themeimg/arrow.png' title='$langPollNumAnswers'>&nbsp;&nbsp;$langPollUnknown
          </td>
          <td>&nbsp;</td>
        </tr>";
    $tool_content .= "
        </table>
        <br />";
}

/* * ***************************************************************************
  Add fill text question $i to $tool_content
 * **************************************************************************** */

function add_fill_text_question($i, $text) {
    global $tool_content, $langQuestion, $langAnswer, $langPollFillText;

    $tool_content .= "
        <hr />
        <table width=\"100%\" class='tbl'>
	<tr>
	  <td width=\"120\"><b>$langQuestion #" . ($i + 1) . "</b></td>
	  <td>
	  <input type='text' name='question[$i]' value='$text' size='52' />" . "
	  <input type='hidden' name='question_type[$i]' value='2'> ($langPollFillText)
	  </td>
	</tr>
	</table>
        <br />";
}

/* * ******************************************************
  Check if there are participants in the poll
 * ******************************************************* */

function check_poll_participants($pid) {
    global $mysqlMainDb;

    $sql = db_query("SELECT * FROM poll_answer_record WHERE pid='$pid'", $mysqlMainDb);
    if (mysql_num_rows($sql) > 0)
        return true;
    else
        return false;
}

/* * ******************************************************
  Check if there are some non-empty questions
 * ******************************************************* */

function questions_exist() {
    foreach ($_POST['question'] as $question) {
        trim($question);
        if (!empty($question)) {
            return true;
        }
    }
    return false;
}
