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

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

// -------------- jscalendar -----------------
load_js('tools.js');
load_js('jquery');
load_js('jquery-ui');
load_js('jquery-ui-timepicker-addon.min.js'); 
global $themeimg;

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
    <script>
    $(function() {
        $('input[name=PollStart], input[name=PollEnd]').datetimepicker({
            showOn: 'both',
            buttonImage: '{$themeimg}/calendar.png',
            buttonImageOnly: true,
            dateFormat: 'dd-mm-yy', 
            timeFormat: 'HH:mm'
        });
        $(poll_init);
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
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);
$nameTools = $langCreatePoll;

if (isset($_REQUEST['pid'])) {
    $pid = intval($_REQUEST['pid']);
    $nameTools = $langEditPoll;
    $p = Database::get()->querySingle("SELECT pid FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
    if(!$p){
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
}

if (isset($_GET['edit']) and isset($pid)) {
    if (check_poll_participants($pid)) {
        Session::set_flashdata($langThereAreParticipants, 'alert1');
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
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
    $poll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
    $_POST['PollName'] = $poll->name;
    $_POST['PollStart'] = $poll->start_date;
    $_POST['PollEnd'] = $poll->end_date;
    $_POST['PollAnonymized'] = $poll->anonymized;
    $_POST['PollDescription'] = $poll->description;
    $_POST['PollEndMessage'] = $poll->end_message;    
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY pqid", $pid);
    $_POST['question'] = array();
    $qnumber = 0;
    foreach ($questions as $theQuestion) {
        $_POST['question'][$qnumber] = $theQuestion->question_text;
        $qtype = $theQuestion->qtype;
        $pqid = $theQuestion->pqid;
        $_POST['question_type'][$qnumber] = $qtype;
        if ($qtype == QTYPE_SINGLE || $qtype == QTYPE_MULTIPLE) {
            $answers = Database::get()->queryArray("SELECT * FROM poll_question_answer
					WHERE pqid = ?d ORDER BY pqaid", $pqid);
            $_POST['answer' . $qnumber] = array();
            foreach ($answers as $theAnswer) {
                $_POST['answer' . $qnumber][] = $theAnswer->answer_text;
            }
        }
        $qnumber++;
    }
}

/* * ***************************************************************************
  Prints the new poll creation form
 * **************************************************************************** */

function printPollCreationForm() {
    global $tool_content, $langTitle, $langPollStart, $langPollAddMultiple, $langPollAddFill,
    $langPollEnd, $langPollMC, $langPollFillText, $langPollContinue, $langCreatePoll,
    $nameTools, $pid, $langSurvey, $langSelection, $langPollAnonymize, $course_code,
    $langDescription, $langPollEndMessage, $PollName, $PollDescription, $PollEndMessage, $PollStart, $PollEnd,
    $langMove, $langUniqueSelect, $langMultipleSelect, $langFreeText, $langLabel, $langComment,
    $langDelete, $langAddQ, $langBack;

    register_posted_variables(array('PollName' => true, 'PollDescription' => true, 'PollEndMessage' => true,
                                    'PollStart' => true, 'PollEnd' => true, 'PollAnonymized' => true));
   
    if (isset($pid)) {
        $pidvar = "<input type='hidden' name='pid' value='$pid'>";
    } else {
        $pidvar = '';
    }
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?course=$course_code#end_poll' id='poll' method='post'>";
    $tool_content .= "
        <div id=\"operations_container\">
          <ul id=\"opslist\">
            <li><a href='index.php?course=$course_code'>".$langBack."</a></li>
           </li>
	  </ul>
	</div>

        <fieldset>
        <legend>$langSurvey</legend>
	<table width=\"100%\" class='tbl'>
	<tr>
	  <th width='100'>$langTitle:</th>
	  <td><input type='text' size='50' name='PollName' value='".q($PollName)."'></td>
	</tr>
	<tr>
	  <th>$langPollStart:</th>
	  <td><input type='text' size='47' name='PollStart' value='".(isset($_POST['PollStart'])? date('d-m-Y H:i',strtotime($_POST['PollStart'])) :date('d-m-Y H:i', strtotime('now')))."'></td></tr>
	<tr>
	  <th>$langPollEnd:</th>
	  <td><input type='text' size='47' name='PollEnd' value='".(isset($_POST['PollEnd'])? date('d-m-Y H:i',strtotime($_POST['PollEnd'])) :date('d-m-Y H:i', strtotime('now +1 year')))."'></td>
	</tr>
	<tr>
	  <th>$langPollAnonymize:</th>
	  <td><input type='checkbox' name='PollAnonymized' value='1' ".((isset($_POST['PollAnonymized']) && $_POST['PollAnonymized']==1)?'checked':'')."></td>
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
    	$tool_content .= "
        <hr />
        <table width='100%' class='tbl'>
        <tr>
	  <th id='end_poll'>
          $langAddQ:&nbsp;
              <ul>
            <li><a id='MoreSingle' class='new_question'>".$langUniqueSelect."</a> </li>
            <li><a id='MoreMultiple' class='new_question'>".$langMultipleSelect."</a></li>
            <li><a id='MoreFill' class='new_question'>".$langFreeText."</a></li>
            <li><a id='MoreLabel' class='new_question'>".$langLabel."/".$langComment."</a></li>
                </ul>
          </th>
	</tr>
        <tr>
	  <td class='right'>
	  <input type='submit' name='PollCreate' value='".q($nameTools)."'>
	  </td>
	</tr>
	</table>
        </fieldset>
	</form>
    <div id='deleteIcon' style='display: none'>&nbsp;" .
        icon('delete', $langDelete) . "</div>"
    . "<div id='moveIcon' style='display: none'>&nbsp;" .
        icon('move_order', $langMove, null, 'id="moveIconImg"') . "</div>";
}

/* * ***************************************************************************
  Prints a form to edit current questions and answers
 * **************************************************************************** */

function printQuestionForm($questions, $question_types) {
    global $tool_content, $langPollMC, $langPollFillText, $langPollContinue,
    $langCreate, $langPollMoreQuestions,
    $langPollCreated, $MoreQuestions;

    $number = 1;
    foreach ($questions as $i => $text) {
        if ($question_types[$i] == QTYPE_SINGLE || $question_types[$i] == QTYPE_MULTIPLE) {
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
function insertPollQuestions($pid, $questions, $question_types) {
    global $langPollEmptyAnswers;

    foreach ($questions as $i => $QuestionText) {
        $QuestionText = trim($QuestionText);
        if (!empty($QuestionText)) {
            $qtype = validate_qtype($question_types[$i]);
            if ($qtype == QTYPE_LABEL) {
                $QuestionText = purify($QuestionText);
            }
            $pqid = Database::get()->query("INSERT INTO poll_question (pid, question_text, qtype) "
                    . "VALUES (?d, ?s, ?d)", $pid, $QuestionText, $qtype)->lastInsertID;
            if ($question_types[$i] == QTYPE_SINGLE || $question_types[$i] == QTYPE_MULTIPLE) {
                    if (isset($_POST['answer'.$i])) {
                            $answers = $_POST['answer'.$i];
                    } else {
                            die("$langPollEmptyAnswers $i");
                    }
                    foreach ($answers as $j => $AnswerText) {
                            $AnswerText = trim($AnswerText);
                            if (!empty($AnswerText)) {
                                Database::get()->query("INSERT INTO poll_question_answer (pqid, answer_text)
							VALUES (?d, ?s)", $pqid, $AnswerText);
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
    $PollStart = date('Y-m-d H:i', strtotime($_POST['PollStart']));
    $PollEnd = date('Y-m-d H:i', strtotime($_POST['PollEnd']));
    $PollActive = 1;
    $PollDescription = $_POST['PollDescription'];
    $PollEndMessage = $_POST['PollEndMessage'];    
    $PollAnonymized = (isset($_POST['PollAnonymized'])) ? $_POST['PollAnonymized'] : 0;
    $pid = Database::get()->query("INSERT INTO poll
		(course_id, creator_id, name, creation_date, start_date, end_date, active, description, end_message, anonymized)
		VALUES (?d, ?d, ?s, NOW(), ?t, ?t, ?d, ?s, ?s, ?d)", $GLOBALS['course_id'], $GLOBALS['uid'], $PollName, $PollStart, $PollEnd, $PollActive, $PollDescription, $PollEndMessage, $PollAnonymized)->lastInsertID;
    insertPollQuestions($pid, $questions, $question_types);
    Session::set_flashdata($langPollCreated, 'success');
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}

// ----------------------------------------
// Modify existing Poll
// ----------------------------------------
function editPoll($pid, $questions, $question_types) {
    global $pid, $tool_content, $course_id, $course_code, $langPollEdited, $langBack,
           $PollName, $PollStart, $PollEnd, $PollDescription, $PollEndMessage, $PollAnonymized;
    
    $PollStart = date('Y-m-d H:i', strtotime($_POST['PollStart']));
    $PollEnd = date('Y-m-d H:i', strtotime($_POST['PollEnd']));
    
    Database::get()->query("UPDATE poll SET name = ?s,
		start_date = ?t, end_date = ?t, description = ?s, end_message = ?s, anonymized = ?d WHERE course_id = ?d AND pid = ?d", $PollName, $PollStart, $PollEnd, $PollDescription, $PollEndMessage, $PollAnonymized, $course_id, $pid);
    Database::get()->query("DELETE FROM poll_question_answer WHERE pqid IN
		(SELECT pqid FROM poll_question WHERE pid = ?d)", $pid);
    Database::get()->query("DELETE FROM poll_question WHERE pid = ?d", $pid);
    insertPollQuestions($pid, $questions, $question_types);
    Session::set_flashdata($langPollEdited, 'success');
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");    
}

/* * ***************************************************************************
  Add multiple choice question $i to $tool_content
 * **************************************************************************** */

function add_multiple_choice_question($i, $number, $text, $qtype=QTYPE_SINGLE) {
    global $tool_content, $langQuestion, $langPollMoreAnswers, $langAnswers,
    $langPollUnknown, $langPollFillText, $langPollNumAnswers,
    $langPollAddAnswer, $langPollMC, $themeimg;
    
    $tool_content .= "
        <hr />
        <table width=\"100%\" class='tbl poll_item'>
	<tr>
	  <td width='150'><b>$langQuestion #$number</b>" . toolbar($i) . "</td>
      <td>
	    <input type='text' name='question[$i]' value='".q($text)."' size='52' />" ."
            <input type='hidden' name='question_type[$i]' value='$qtype' />
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
          <td><ul class='poll_answers'>";
    foreach ($answers as $j => $answertext) {
        $tool_content .= "<li><input type='text' name='answer${i}[]' value='".q($answertext)."' size='50'></li>";
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

/* * ***************************************************************************
  Add fill text question $i to $tool_content
 * **************************************************************************** */

function add_fill_text_question($i, $number, $text) {
    global $tool_content, $langQuestion, $langAnswer, $langPollFillText;

    $tool_content .= "
        <hr />
        <table width=\"100%\" class='tbl'>
	<tr>
	  <td width=\"120\"><b>$langQuestion #" . $number . "</b></td>
	  <td>
	  <input type='text' name='question[$i]' value='".q($text)."' size='52' />" . "
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
/* * ******************************************************
  Check if there are participants in the poll
 * ******************************************************* */

function check_poll_participants($pid) {
    $participants = Database::get()->querySingle("SELECT COUNT(*) AS participants "
            . "FROM poll_answer_record WHERE pid = ?d", $pid)->participants;
    if ($participants > 0)
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

function toolbar($i)
{
    global $langDelete, $langUp, $langDown;
    return "<div class='poll_toolbar' data-id='$i'>" . 
        icon('up', $langUp) . '&nbsp;' .
        icon('down', $langDown) . '&nbsp;' . 
        icon('delete', $langDelete) . "</div>";
}
