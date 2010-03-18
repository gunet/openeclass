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

$require_prof = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

include '../../include/baseTheme.php';

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

// -------------- jscalendar -----------------
include('../../include/jscalendar/calendar.php');

if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

$navigation[] = array("url"=>"questionnaire.php", "name"=> $langQuestionnaire);

$tool_content = "";
$nameTools = $langCreatePoll;

if (isset($_REQUEST['pid'])) {
	$pid = intval($_REQUEST['pid']);
	$nameTools = $langEditPoll;
}

if (isset($_GET['edit']) and isset($pid))  {
	if (check_poll_participants($pid)) {
		$tool_content .= "<center>$langThereAreParticipants";
		$tool_content .= "<br><br><a href='questionnaire.php'>$langBack</a></center>";
		draw($tool_content, 2, 'questionnaire', $local_head, '');
		exit();
	} else {
		fill_questions($pid);
	}
}

if (isset($_POST['PollCreate']))  {
	if (isset($_POST['question']) and questions_exist()) {
		if (isset($pid)) {
			editPoll($pid, $_POST['question'], $_POST['question_type']);
		} else {
			createPoll($_POST['question'], $_POST['question_type']);
		}
		draw($tool_content, 2, 'questionnaire', $local_head, '');
		exit;
	} else {
		$tool_content .= "$langPollEmpty<br />";
	}
}

printPollCreationForm();
draw($tool_content, 2, 'questionnaire', $local_head, '');


/*****************************************************************************
Fill the appropriate $_POST values from the database as if poll $pid was submitted
******************************************************************************/
function fill_questions($pid)
{
	$poll = mysql_fetch_array(db_query("SELECT * FROM poll WHERE pid=$pid"));
	$_POST['PollName'] = $poll['name'];
	$_POST['PollStart'] = $poll['start_date'];
	$_POST['PollEnd'] = $poll['end_date'];
	$questions = db_query("SELECT * FROM poll_question WHERE pid=$pid ORDER BY pqid");
	$_POST['question'] = array();
	$qnumber = 0;
	while ($theQuestion = mysql_fetch_array($questions)) {
		$_POST['question'][$qnumber] = $theQuestion['question_text'];
		$qtype = ($theQuestion['qtype'] == 'multiple')? 1: 2;
		$pqid = $theQuestion['pqid'];
		$_POST['question_type'][$qnumber] = $qtype;
		if ($qtype == 1) {
			$answers = db_query("SELECT * FROM poll_question_answer
					WHERE pqid=$pqid ORDER BY pqaid");
			$_POST['answer'.$qnumber] = array();
			while ($theAnswer = mysql_fetch_array($answers)) {
				$_POST['answer'.$qnumber][] = $theAnswer['answer_text'];
			}
		}
		$qnumber++;
	}
}


/*****************************************************************************
		Create the HTML for a jscalendar field
******************************************************************************/
function jscal_html($name, $u_date = FALSE) {
	global $jscalendar;
	if (!$u_date) {
		$u_date = strftime('%Y-%m-%d', strtotime('now -0 day'));
	}

	$cal = $jscalendar->make_input_field(
           array('showsTime' => false,
                 'showOthers' => true,
                 'ifFormat' => '%Y-%m-%d'),
           array('style' => 'width: 15em; color: #840; background-color: #fff; border: 1px dotted #000; text-align: center',
                 'name'        => $name,
                 'value'       => $u_date));
	return $cal;
}

/*****************************************************************************
		Prints the new poll creation form
******************************************************************************/
function printPollCreationForm() {
	global $tool_content, $langTitle, $langPollStart, $langPollAddMultiple, $langPollAddFill,
		$langPollEnd, $langPollMC, $langPollFillText, $langPollContinue, $langCreatePoll,
		$nameTools, $pid, $langSurvey, $langSelection;

	if(isset($_POST['PollName'])) {
		$PollName = htmlspecialchars($_POST['PollName']);
	} else {
		$PollName = '';
	}
	if(isset($_POST['PollStart'])){
		$PollStart = jscal_html('PollStart', $_POST['PollStart']);
	} else {
		$PollStart = jscal_html('PollStart');
	}
	if(isset($_POST['PollEnd'])) {
		$PollEnd = jscal_html('PollEnd', $_POST['PollEnd']);
	} else {
		$PollEnd = jscal_html('PollEnd', strftime('%Y-%m-%d', strtotime('now +1 year')));
	}
	if (isset($pid)) {
		$pidvar = "<input type='hidden' name='pid' value='$pid'>";
	} else {
		$pidvar = '';
	}
	$tool_content .= "
    <form action='$_SERVER[PHP_SELF]' id='poll' method='post'>";
    /*
    $tool_content .= "$pidvar
		<div id=\"operations_container\">
		  <ul id=\"opslist\"> $langSelection:
		    <li><input type='submit' name='MoreMultiple' value='$langPollAddMultiple' class=\"toolBar_Button\"></li>
		    <li><input type='submit'  name='MoreFill' value='$langPollAddFill' class=\"toolBar_Button\"></li>
		  </ul>
        </div>";
    */
	$tool_content .= "
    <table width=\"99%\" align=\"left\" class=\"Questionnaire_Operations\">
    <thead>
    <tr>
      <td width=\"60%\">$langSelection:&nbsp;</td>
      <td width=\"20%\"><div align=\"right\"><input type='submit' name='MoreMultiple' value='$langPollAddMultiple' class=\"toolBar_Button\"></div></td>
      <td width=\"20%\"><div align=\"right\"><input type='submit' size=\"5\" name='MoreFill' value='$langPollAddFill' class=\"toolBar_Button\"></div></td>
    </tr>
    </thead>
    </table>
    <br /><br />


    <table width=\"99%\" class='FormData'>
    <tbody>
    <tr>
      <th class=\"left\" width=\"220\">&nbsp;</th>
      <td><b>$langSurvey</b></td>
    </tr>
    <tr>
      <th class='left'>$langTitle</th>
      <td><input type='text' size='50' name='PollName' class='FormData_InputText' value='$PollName'></td>
    </tr>
    <tr>
      <th class='left'>$langPollStart</th>
      <td>$PollStart</td></tr>
    <tr>
      <th class='left'>$langPollEnd</th>
      <td>$PollEnd</td>
    </tr>
    </tbody>
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
    	$tool_content .= '

    <table width="99%" class="FormData">
    <tbody>
    <tr>
      <th width="220">&nbsp;</th>
      <td>
      <input type="submit" name="PollCreate" value="'.$nameTools.'">
      </td>
    </tr>
    </tbody>
    </table>
    </form>';
}

/*****************************************************************************
		Prints a form to edit current questions and answers
******************************************************************************/
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
function insertPollQuestions($pid, $questions, $question_types)
{
	global $langPollEmptyAnswers;

	foreach ($questions as $i => $QuestionText) {
		$QuestionText = trim($QuestionText);
		if (!empty($QuestionText)) {
			$qtype = ($question_types[$i] == 1)? 'multiple': 'fill';
			db_query("INSERT INTO poll_question (pid, question_text, qtype) VALUES ('".
				mysql_real_escape_string($pid) . "','".
				mysql_real_escape_string($QuestionText) . "', '$qtype')");
			$pqid = mysql_insert_id();
			if ($question_types[$i] == 1) {
				if (isset($_POST['answer'.$i])) {
					$answers = $_POST['answer'.$i];
				} else {
					die("$langPollEmptyAnswers $i");
				}
				foreach ($answers as $j => $AnswerText) {
					$AnswerText = trim($AnswerText);
					if (!empty($AnswerText)) {
						db_query("INSERT INTO poll_question_answer (pqid, answer_text)
							VALUES ($pqid, '".mysql_real_escape_string($AnswerText) ."')");
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
	global $tool_content;

	$CreationDate = date("Y-m-d");
	$PollName = $_POST['PollName'];
	$StartDate = $_POST['PollStart'];
	$EndDate = $_POST['PollEnd'];
	$PollActive = 1;

	mysql_select_db($GLOBALS['currentCourseID']);
	$result = db_query("INSERT INTO poll
		(creator_id, course_id, name, creation_date, start_date, end_date, active)
		VALUES ('".
		$GLOBALS['uid']. "','".
		$GLOBALS['currentCourseID'] . "','".
		mysql_real_escape_string($PollName) . "','".
		mysql_real_escape_string($CreationDate) . "','".
		mysql_real_escape_string($StartDate) . "','".
		mysql_real_escape_string($EndDate) . "','".
		mysql_real_escape_string($PollActive) ."')");
	$pid = mysql_insert_id();
	insertPollQuestions($pid, $questions, $question_types);
	$GLOBALS["tool_content"] .= $GLOBALS["langPollCreated"];
}


// ----------------------------------------
// Modify existing Poll
// ----------------------------------------
function editPoll($pid, $questions, $question_types) {
	global $pid;

	$PollName = $_POST['PollName'];
	$StartDate = $_POST['PollStart'];
	$EndDate = $_POST['PollEnd'];

	mysql_select_db($GLOBALS['currentCourseID']);
	$result = db_query("UPDATE poll SET name = '$PollName',
		start_date = '$StartDate', end_date = '$EndDate' WHERE pid='$pid'");
	db_query("DELETE FROM poll_question_answer WHERE pqid IN
		(SELECT pqid FROM poll_question WHERE pid='$pid')");
	db_query("DELETE FROM poll_question WHERE pid='$pid'");
 	insertPollQuestions($pid, $questions, $question_types);
	$GLOBALS["tool_content"] .= $GLOBALS["langPollEdited"];
}


/*****************************************************************************
	Add multiple choice question $i to $tool_content
******************************************************************************/
function add_multiple_choice_question($i, $text)
{
	global $tool_content, $langQuestion, $langPollMoreAnswers, $langAnswers, $langPollUnknown, $langPollFillText, $langPollNumAnswers, $langPollAddAnswer, $langPollMC;

	$tool_content .= "
    <table width=\"99%\" class='Questionnaire'>
    <tbody>
    <tr>
      <th class=\"left\" width=\"220\"><b>$langQuestion #" . ($i+1) ."</b><br><small>$langPollMC</small></th>
      <td>
      <input type='text' name='question[$i]' value='$text' size='52' class='FormData_InputText'>" ."
      <input type='hidden' name='question_type[$i]' value='1'>
      </td>
    </tr>";
	if (isset($_POST['answer'.$i])) {
		$answers = $_POST['answer'.$i];
	} else {
		$answers = array('', '');
	}
	if (isset($_POST['MoreAnswers'.$i])) {
		$answers[] = '';
	}
	$tool_content .= "
    <tr>
      <td class=\"left\">$langAnswers:<br><br>
      $langPollAddAnswer:
      <input type='submit' name='MoreAnswers$i' value='$langPollMoreAnswers'>
      </td>
      <td>";
	foreach ($answers as $j => $answertext) {
	    $tool_content .= "
        <img src='../../images/arrow_blue.gif' alt='$langPollNumAnswers' title='$langPollNumAnswers'>&nbsp;&nbsp;<input type='text' name='answer${i}[]' value='$answertext' size='50' class='FormData_InputText'><br><br>";
	}
	$tool_content .= "
        <img src='../../images/arrow_blue.gif' alt='$langPollNumAnswers' title='$langPollNumAnswers'>&nbsp;&nbsp;$langPollUnknown
      </td>
    </tr>
";
	$tool_content .= "
    </tbody>
    </table>
    <br />";
}

/*****************************************************************************
	Add fill text question $i to $tool_content
******************************************************************************/
function add_fill_text_question($i, $text)
{
	global $tool_content, $langQuestion, $langAnswer, $langPollFillText;

	$tool_content .= "
    <table width=\"99%\" class='Questionnaire'>
    <tbody>
    <tr>
      <th class=\"left\" width=\"220\"><b>$langQuestion #" . ($i+1) ."</b><br><small>$langPollFillText</small></th>
      <td>
      <input type='text' name='question[$i]' value='$text' size='52' class='FormData_InputText'>" . "
      <input type='hidden' name='question_type[$i]' value='2'>
      </td>
    </tr>";
    $tool_content .= "
    </tbody>
    </table>
    <br />";
}

/********************************************************
	Check if there are participants in the poll
*********************************************************/
function check_poll_participants($pid)
{
	global $currentCourseID;

	$sql = db_query("SELECT * FROM poll_answer_record WHERE pid='$pid'", $currentCourseID);
	if (mysql_num_rows($sql) > 0)
		return true;
	else
		return false;

}

/********************************************************
	Check if there are some non-empty questions
*********************************************************/
function questions_exist()
{
	foreach ($_POST['question'] as $question) {
		trim($question);
		if (!empty($question)) {
			return true;
		}
	}
	return false;
}

?>
