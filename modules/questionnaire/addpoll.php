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
	addpoll.php
	@last update: 26-5-2006 by Dionysios Synodinos
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the poll tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights - to create polls.

==============================================================================
*/

$require_prof = TRUE;
$require_current_course = TRUE;
$langFiles = 'questionnaire';

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

$nameTools = $langCreatePoll;
$navigation[] = array("url"=>"questionnaire.php", "name"=> $langQuestionnaire);

$tool_content = "";

if (isset($_POST['PollCreate']))  {
	if (isset($_POST['question'])) {
		createPoll($_POST['question'], $_POST['question_type']);
	} else {
		$tool_content .= "<p>Error: please add more questions</p>";
		printPollCreationForm();
	}
} else {
	printPollCreationForm();
}

draw($tool_content, 2, '', $local_head, '');


/*****************************************************************************
		Create the HTML for a jscalendar field 
******************************************************************************/
function jscal_html($name, $u_date = FALSE) {
	global $jscalendar;
	if (!$u_date) {
		$u_date = strftime('%Y-%m-%d %H:%M:%S', strtotime('now -0 day'));
	}

	$cal = $jscalendar->make_input_field(
           array('showsTime' => true,
                 'showOthers' => true,
                 'ifFormat' => '%Y-%m-%d %H:%M:%S',
                 'timeFormat'  => '24'),
           array('style' => 'width: 15em; color: #840; background-color: #fff; border: 1px dotted #000; text-align
: center',
                 'name'        => $name,
                 'value'       => $u_date));
	return $cal;
}


/*****************************************************************************
		Prints the new poll creation form
******************************************************************************/
function printPollCreationForm() {
	global $tool_content, $langTitle, $langPollStart, $langPollAddMultiple, $langPollAddFill,
		$langPollEnd, $langPollMC, $langPollFillText, $langPollContinue, $langCreatePoll;

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
		$PollEnd = jscal_html('PollEnd', strftime('%Y-%m-%d %H:%M:%S', strtotime('now +1 year')));
	}
	
	$tool_content .= "
    <form action='addpoll.php' id='poll' method='post'>
    <table class='FormData'>
    <tbody>
    <tr>
      <th class='left' width='150'>&nbsp;</th>
      <td><b>$langCreatePoll</b></td>
    </tr>
    <tr>
      <th class='left'>$langTitle</th>
      <td><input type='text' size='50' name='PollName' class='FormData_InputText' value='$PollName'></td>
    </tr>
    <tr>
      <th class='left'>$langPollStart</th>
      <td>$PollStart</td>
    </tr>
    <tr>
      <th class='left'>$langPollEnd</th>
      <td>$PollEnd</td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td>
      <input type='submit' name='MoreMultiple' value='$langPollAddMultiple'>
      <input type='submit' name='MoreFill' value='$langPollAddFill'>
      <input type='submit' name='PollCreate' value='$langCreatePoll'>
      </td>
    </tr>";

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

    $tool_content .= '</tbody></table></form>';
}

/*****************************************************************************
		Prints a form to edit current questions and answers
******************************************************************************/
function printQuestionForm($questions, $question_types) {
	global $tool_content, 
	$langPollMC, $langPollFillText, $langPollContinue, 
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


/*****************************************************************************
	Add multiple choice question $i to $tool_content
******************************************************************************/
function add_multiple_choice_question($i, $text)
{
	global $tool_content, $langQuestion, $langPollMoreAnswers, $langAnswers;
	
	$tool_content .= "<tr><td colspan='2'>$langQuestion #" . ($i+1) .
                "<br><input type='text' name='question[$i]' value='$text' size='50'>" .
                "<input type='hidden' name='question_type[$i]' value='1'></td></tr>";
	if (isset($_POST['answer'.$i])) {
		$answers = $_POST['answer'.$i];
	} else {
		$answers = array('', '');
	}
	if (isset($_POST['MoreAnswers'.$i])) {
		$answers[] = '';
	}
	$tool_content .= "<tr><td colspan='2'>$langAnswers:";
	foreach ($answers as $j => $answertext) {
	    $tool_content .= "<br><input type='text' name='answer${i}[]' value='$answertext' size='50'>";
	}
	$tool_content .= "</td></tr><tr><td colspan='2'><input type='submit' name='MoreAnswers$i' value='$langPollMoreAnswers'></td></tr>";
}


/*****************************************************************************
	Add fill text question $i to $tool_content
******************************************************************************/
function add_fill_text_question($i, $text)
{
	global $tool_content, $langQuestion, $langAnswer;
	
	$tool_content .= "<tr><td colspan='2'>$langQuestion #" . ($i+1) .
                "<br><input type='text' name='question[$i]' value='$text' size='50'>" .
                "<input type='hidden' name='question_type[$i]' value='2'></td></tr>";
}


// ----------------------------------------
// Creating a Poll
// ----------------------------------------

function createPoll($questions, $question_types) {
	global $tool_content;

	$CurrentQuestion = 0;
	$CurrentAnswer = 0;
	$CreationDate = date("Y-m-d H:i:s");
	$pid = date("YmdHms");
	$PollType = 1;
	$PollActive = 1;
	$PollName = $_POST['PollName'];
	$StartDate = $_POST['PollStart'];
	$EndDate = $_POST['PollEnd']; 

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
					die("error: no answers for question $i");
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
	$GLOBALS["tool_content"] .= $GLOBALS["langPollCreated"];
}
?>
