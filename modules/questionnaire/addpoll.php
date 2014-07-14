<?php
/* ========================================================================
 * Open eClass 2.9
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

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once '../../include/jscalendar/calendar.php';

load_js('jquery');
load_js('jquery-ui-new');
load_js('tools.js');

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

$lang = langname_to_code($language);

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$head_content .= $jscalendar->get_load_files_code() .
    "<script type = 'text/javascript'>
    var langEmptyGroupName = '" . js_escape($langEmptyPollTitle) . "',
        langPollNumAnswers  = '" . js_escape($langPollNumAnswers) . "',
        themeimg = '" . js_escape($themeimg) . "';
    $(poll_init);
    $(document).ready(function(){
      $('a.new_question').click(function(){
        var question_type = $(this).attr('id');
        $('<input />').attr('type', 'hidden')
            .attr('name', question_type)
            .attr('value', 1)
            .appendTo('#poll');         
         $('#poll').submit();
      });
    });  
</script>";

$navigation[] = array('url' => "questionnaire.php?course=$code_cours", 'name' => $langQuestionnaire);
$nameTools = $langCreatePoll;

if (isset($_REQUEST['pid'])) {
	$pid = intval($_REQUEST['pid']);
	$nameTools = $langEditPoll;
}

if (isset($_GET['edit']) and isset($pid)) {
	if (check_poll_participants($pid)) {
		$tool_content .= "$langThereAreParticipants";
		$tool_content .= "<br ><br /><div align='right'><a href='questionnaire.php?course=$code_cours'>$langBack</a></div>";
		draw($tool_content, 2, null, $head_content);
		exit();
	} else {
		fill_questions($pid);
	}
}

if (isset($_POST['PollCreate'])) {
	if (isset($_POST['question']) and questions_exist()) {
        register_posted_variables(array(
            'PollName' => true, 'PollStart' => true, 'PollEnd' => true,
            'PollAnonymized' => true, 'PollDescription' => true, 'PollEndMessage' => true));
        $PollDescription = purify($PollDescription);
        $PollEndMessage = purify($PollEndMessage);

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

/*****************************************************************************
Fill the appropriate $_POST values from the database as if poll $pid was submitted
******************************************************************************/
function fill_questions($pid)
{
	$poll = mysql_fetch_array(db_query("SELECT * FROM poll WHERE pid = $pid"));
	$_POST['PollName'] = $poll['name'];
	$_POST['PollStart'] = $poll['start_date'];
	$_POST['PollEnd'] = $poll['end_date'];
        $_POST['PollAnonymized'] = $poll['anonymized'];
	$_POST['PollDescription'] = $poll['description'];
	$_POST['PollEndMessage'] = $poll['end_message'];
	$questions = db_query("SELECT * FROM poll_question WHERE pid = $pid ORDER BY pqid");
	$_POST['question'] = array();
	$qnumber = 0;
	while ($theQuestion = mysql_fetch_array($questions)) {
		$_POST['question'][$qnumber] = $theQuestion['question_text'];
		$qtype = $theQuestion['qtype'];
		$pqid = $theQuestion['pqid'];
		$_POST['question_type'][$qnumber] = $qtype;
		if ($qtype == QTYPE_SINGLE or $qtype == QTYPE_MULTIPLE) {
			$answers = db_query("SELECT * FROM poll_question_answer
					WHERE pqid = $pqid ORDER BY pqaid");
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
		$u_date = strftime('%Y-%m-%d %H:%M', strtotime('now -0 day'));
	}

	$cal = $jscalendar->make_input_field(
           array('showsTime' => true,
                 'showOthers' => true,
                 'ifFormat' => '%Y-%m-%d %H:%M'),
           array('style' => '',
                 'name' => $name,
                 'value' => $u_date));
	return $cal;
}

/*****************************************************************************
		Prints the new poll creation form
******************************************************************************/
function printPollCreationForm() {
	global $tool_content, $langTitle, $langPollStart, $langDescription, $langPollEnd, $langPollEndMessage, $langPollEndMessageText,
        $nameTools, $pid, $langSurvey, $langDelete, $langNewQu, $code_cours,
        $PollName, $PollDescription, $PollEndMessage, $PollStart, $PollEnd, $PollAnonymized, $langPollAnonymize, $langUniqueSelect,
        $langMultipleSelect, $langFreeText, $langLabel, $langComment;

    register_posted_variables(array('PollName' => true, 'PollDescription' => true, 'PollEndMessage' => true,
                                    'PollStart' => true, 'PollEnd' => true, 'PollAnonymized' => true));
    if (!$PollEndMessage) {
        $PollEndMessage = $langPollEndMessageText;
    }
    $PollStart = jscal_html('PollStart', $PollStart);
    $PollEnd = jscal_html('PollEnd',
        empty($PollEnd)? strftime('%Y-%m-%d %H:%M', strtotime('now +1 year')): $PollEnd);
    $pidvar = isset($pid)? "<input type='hidden' name='pid' value='$pid'>": '';

	$tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?course=$code_cours' id='poll' method='post' onsubmit=\"return checkrequired(this, 'PollName');\">";
	$tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
          $langNewQu:&nbsp;
            <li><a id='MoreSingle' class='new_question'>".$langUniqueSelect."</a></li>
            <li><a id='MoreMultiple' class='new_question'>".$langMultipleSelect."</a></li>
            <li><a id='MoreFill' class='new_question'>".$langFreeText."</a></li>
            <li><a id='MoreLabel' class='new_question'>".$langLabel."/".$langComment."</a></li>
           </li>
	  </ul>
	</div>

        <fieldset>
        <legend>$langSurvey</legend>
	<table width='100%' class='tbl'>
	<tr>
	  <th width='100'>$langTitle:</th>
	  <td><input type='text' size='50' name='PollName' value='".q($PollName)."'></td>
	</tr>
	<tr>
	  <th>$langPollStart:</th>
	  <td>$PollStart</td></tr>
	<tr>
	  <th>$langPollEnd:</th>
	  <td>$PollEnd</td>
	</tr>
	<tr>
	  <th>$langPollAnonymize:</th>
	  <td><input type='checkbox' name='PollAnonymized' value='1' ".((isset($PollAnonymized) && $PollAnonymized==1)?'checked':'')."></td>
	</tr>          
	<tr>
	  <th>$langDescription:</th>
	  <td>".rich_text_editor('PollDescription', 4, 52, $PollDescription)."</td>
	</tr>
	<tr>
	  <th>$langPollEndMessage:</th>
	  <td>".rich_text_editor('PollEndMessage', 4, 52, $PollEndMessage)."</td>
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
        if (isset($_POST['MoreSingle'])) {
		$questions[] = '';
		$question_types[] = QTYPE_SINGLE;            
        } elseif (isset($_POST['MoreMultiple'])) {
		$questions[] = '';
		$question_types[] = QTYPE_MULTIPLE;
	} elseif (isset($_POST['MoreFill'])) {
		$questions[] = '';
		$question_types[] = QTYPE_FILL;
	} elseif (isset($_POST['MoreLabel'])) {
		$questions[] = '';
		$question_types[] = QTYPE_LABEL;
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
	  <input type="submit" name="PollCreate" value="'.q($nameTools).'">
	  </td>
	</tr>
	</table>
        </fieldset>
	</form>
    <div id="deleteIcon" style="display: none">&nbsp;' .
        icon('delete', $langDelete) . '</div>';
}

/*****************************************************************************
		Prints a form to edit current questions and answers
******************************************************************************/
function printQuestionForm($questions, $question_types) {
	global $tool_content, $langPollMC, $langPollFillText, $langPollContinue,
		$langCreate, $langPollMoreQuestions,
		$langPollCreated, $MoreQuestions;

    $number = 1;
	foreach ($questions as $i => $text) {
        if ($question_types[$i] == QTYPE_SINGLE or
            $question_types[$i] == QTYPE_MULTIPLE) {
                add_multiple_choice_question($i, $number, $text, $question_types[$i]);
        } elseif ($question_types[$i] == QTYPE_FILL) {
                add_fill_text_question($i, $number, $text);
		} else {
                add_label_question($i, $text);
                $number--; // don't increment number for labels
        }
        $number++;
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
			$qtype = validate_qtype($question_types[$i]);
            if ($qtype == QTYPE_LABEL) {
                $QuestionText = purify($QuestionText);
            }
            db_query("INSERT INTO poll_question (pid, question_text, qtype) VALUES
                ($pid, " . quote($QuestionText) . ", $qtype)");
			$pqid = mysql_insert_id();
			if ($question_types[$i] == QTYPE_SINGLE or $question_types[$i] == QTYPE_MULTIPLE) {
				if (isset($_POST['answer'.$i])) {
					$answers = $_POST['answer'.$i];
				} else {
					die("$langPollEmptyAnswers $i");
				}
				foreach ($answers as $j => $AnswerText) {
					$AnswerText = trim($AnswerText);
					if (!empty($AnswerText)) {
						db_query("INSERT INTO poll_question_answer (pqid, answer_text)
							VALUES ($pqid, " . quote($AnswerText) . ")");
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
    global $tool_content, $code_cours, $cours_id, $uid, $langPollCreated, $langBack,
        $PollName, $PollStart, $PollEnd, $PollDescription, $PollEndMessage, $PollAnonymized;

	mysql_select_db($GLOBALS['currentCourseID']);
	$CreationDate = date("Y-m-d H:i");
	$result = db_query("INSERT INTO poll
                            SET creator_id = $uid,
                                course_id = $cours_id,
                                name = " . quote($PollName) . ",
                                creation_date = " . quote($CreationDate) . ",
                                start_date = " . quote($PollStart) . ",
                                end_date = " . quote($PollEnd) . ",
                                description = " . quote($PollDescription) . ",
                                end_message = " . quote($PollEndMessage) . ",
                                anonymized = " . quote($PollAnonymized) . ",    
                                active = 1");
	$pid = mysql_insert_id();
	insertPollQuestions($pid, $questions, $question_types);
	$tool_content .= "<p class='success'>".q($langPollCreated)."</p><a href='questionnaire.php?course=$code_cours'>".q($langBack)."</a>";
}


// ----------------------------------------
// Modify existing Poll
// ----------------------------------------
function editPoll($pid, $questions, $question_types) {
    global $pid, $tool_content, $code_cours, $langPollEdited, $langBack,
        $PollName, $PollStart, $PollEnd, $PollDescription, $PollEndMessage, $PollAnonymized;

	mysql_select_db($GLOBALS['currentCourseID']);
    $result = db_query("UPDATE poll
                            SET name = " . quote($PollName) . ",
                                start_date = " . quote($PollStart) . ",
                                end_date = " . quote($PollEnd) . ",
                                description = " . quote($PollDescription) . ",
                                end_message = " . quote($PollEndMessage) . ",
                                anonymized = " . quote($PollAnonymized) . "
		                    WHERE pid='$pid'");
	db_query("DELETE FROM poll_question_answer WHERE pqid IN
		(SELECT pqid FROM poll_question WHERE pid = $pid)");
	db_query("DELETE FROM poll_question WHERE pid = $pid");
 	insertPollQuestions($pid, $questions, $question_types);
	$tool_content .= "<p class='success'>".$langPollEdited."</p><a href=\"questionnaire.php?course=$code_cours\">".$langBack."</a>";
}


/*****************************************************************************
	Add multiple choice question $i to $tool_content
******************************************************************************/
function add_multiple_choice_question($i, $number, $text, $qtype=QTYPE_SINGLE)
{
    global $tool_content, $langQuestion, $langPollMoreAnswers, $langAnswers,
           $langPollUnknown, $langPollFillText, $langPollNumAnswers,
           $langPollAddAnswer, $langDelete,
           $langUniqueSelect, $langMultipleSelect;

	$tool_content .= "
        <hr />
        <table width=\"100%\" class='tbl poll_item'>
	<tr>
	  <td width='150'><b>$langQuestion #$number</b>" . toolbar($i) . "</td>
      <td>
	    <input type='text' name='question[$i]' value='$text' size='52' />" ."
            <input type='hidden' name='question_type[$i]' value='$qtype' />
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
          <td width='80'><b>$langAnswers:</b></td>
          <td>
          $langPollAddAnswer: <input type='submit' name='MoreAnswers$i' value='".q($langPollMoreAnswers)."' /></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><ul class='poll_answers'>";

	foreach ($answers as $j => $answertext) {
            $tool_content .= "<li><input type='text' name='answer${i}[]' value='$answertext' size='50'></li>";
	}
        if ($qtype == QTYPE_SINGLE) {
            $tool_content .= "<li id='unknown'>$langPollUnknown</li>";
        }
        $tool_content .= "
            </ul>
          </td>
          <td>&nbsp;</td>
        </tr>";
	$tool_content .= "
        </table>
        <br />";
}

/*****************************************************************************
	Add fill text question $i to $tool_content
******************************************************************************/
function add_fill_text_question($i, $number, $text)
{
	global $tool_content, $langQuestion, $langAnswer, $langPollFillText;

	$tool_content .= "
        <hr />
        <table width=\"100%\" class='tbl poll_item'>
	<tr>
	  <td width=\"120\"><b>$langQuestion #$number</b>" . toolbar($i) . "</td>
	  <td>
	  <input type='text' name='question[$i]' value='$text' size='52' />" . "
	  <input type='hidden' name='question_type[$i]' value='2'> ($langPollFillText)
	  </td>
	</tr>
	</table>
        <br />";
}

/*****************************************************************************
	Add label/comment "question" $i to $tool_content
******************************************************************************/
function add_label_question($i, $text)
{
	global $tool_content, $langComment;

	$tool_content .= "
        <hr />
        <table width='100%' class='tbl poll_item'>
	<tr>
	  <td width='120'><b>$langComment</b>" .  toolbar($i) . "</td>
	  <td>" . rich_text_editor("question[$i]", 4, 52, $text)  . "
	  <input type='hidden' name='question_type[$i]' value='".QTYPE_LABEL."'>
	  </td>
	</tr>
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

function toolbar($i)
{
    global $langDelete, $langUp, $langDown;
    return "<div class='poll_toolbar' data-id='$i'>" . 
        icon('up', $langUp) . '&nbsp;' .
        icon('down', $langDown) . '&nbsp;' . 
        icon('delete', $langDelete) . "</div>";
}
