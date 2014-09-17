<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

load_js('tools.js');
load_js('jquery');
load_js('jquery-ui');
load_js('jquery-ui-timepicker-addon.min.js'); 
global $themeimg;
$head_content .= "<link rel='stylesheet' type='text/css' href='$urlAppend/js/jquery-ui-timepicker-addon.min.css'>
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
        $('.success').delay(3000).fadeOut(1500);
    });
    </script>";
if (isset($_POST['cancelPoll']) || isset($_POST['cancelQuestion']) || isset($_POST['cancelAnswers'])) {
    if(isset($_GET['pid'])) {
        redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$_GET[pid]");          
    } else {
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");       
    }
}
if (isset($_GET['moveDown']) || isset($_GET['moveUp'])) {   
    $pqid = isset($_GET['moveUp']) ? $_GET['moveUp'] : $_GET['moveDown'];
    $pid = $_GET['pid'];
    $position = Database::get()->querySingle("SELECT q_position FROM `poll_question`
				  WHERE pid = ?d AND pqid = ?d", $pid, $pqid)->q_position;
    $new_position = isset($_GET['moveUp']) ? $position - 1 : $position + 1;
    $trade_position_pqid = Database::get()->querySingle("SELECT pqid FROM `poll_question`
				  WHERE pid = ?d AND q_position = ?d", $pid, $new_position)->pqid;
    Database::get()->query("UPDATE poll_question SET q_position = ?d WHERE pid = ?d AND pqid= ?d", $new_position, $pid, $pqid);
    Database::get()->query("UPDATE poll_question SET q_position = ?d WHERE pqid = ?d AND pid = ?d", $position, $pid, $trade_position_pqid);
    redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
}

if (isset($_POST['submitPoll'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', ['PollName']);
    $v->rule('alpha', ['PollName']);
    $v->labels(array(
        'PollName' => "$langTheField $langTitle"
    ));
    if($v->validate()) {
        $PollName = $_POST['PollName'];
        $PollStart = date('Y-m-d H:i', strtotime($_POST['PollStart']));
        $PollEnd = date('Y-m-d H:i', strtotime($_POST['PollEnd']));
        $PollDescription = purify($_POST['PollDescription']);
        $PollEndMessage = purify($_POST['PollEndMessage']);    
        $PollAnonymized = (isset($_POST['PollAnonymized'])) ? $_POST['PollAnonymized'] : 0;   
        if(isset($_GET['pid'])) {
            $pid = $_GET['pid'];
            Database::get()->query("UPDATE poll SET name = ?s,
                    start_date = ?t, end_date = ?t, description = ?s, end_message = ?s, anonymized = ?d WHERE course_id = ?d AND pid = ?d", $PollName, $PollStart, $PollEnd, $PollDescription, $PollEndMessage, $PollAnonymized, $course_id, $pid);
            Session::Messages($langPollEdited, 'success');
        } else {
            $PollActive = 1;
            $pid = Database::get()->query("INSERT INTO poll
                        (course_id, creator_id, name, creation_date, start_date, end_date, active, description, end_message, anonymized)
                        VALUES (?d, ?d, ?s, NOW(), ?t, ?t, ?d, ?s, ?s, ?d)", $course_id, $uid, $PollName, $PollStart, $PollEnd, $PollActive, $PollDescription, $PollEndMessage, $PollAnonymized)->lastInsertID;
            Session::Messages($langPollCreated, 'success');
        }
        redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
    } else {
        // Errors
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        if(isset($_GET['pid'])) {
            $pid = $_GET['pid']; 
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid&modifyPoll=yes");
        } else {        
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&newPoll=yes");
        }
    } 
}
if (isset($_POST['submitQuestion'])) {
    $question_text = $_POST['questionName'];
    $qtype = $_POST['answerType'];    
    $pid = $_GET['pid'];  
    if(isset($_GET['modifyQuestion'])) {
        $pqid = $_GET['modifyQuestion'];
        Database::get()->query("UPDATE poll_question SET question_text = ?s, qtype = ?d
		WHERE pqid = ?d AND pid = ?d", $question_text, $qtype, $pqid, $pid);
    } else {
        $max_position = Database::get()->querySingle("SELECT MAX(q_position) AS position FROM poll_question WHERE pid = ?d", $pid)->position;
        $pqid = Database::get()->query("INSERT INTO poll_question
                    (pid, question_text, qtype, q_position)
                    VALUES (?d, ?s, ?d, ?d)", $pid, $question_text, $qtype, $max_position + 1)->lastInsertID;
    }
    if ($qtype == QTYPE_FILL || $qtype == QTYPE_LABEL) {
        redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
    } else {
        redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid&modifyAnswers=$pqid");
    }
}
if (isset($_POST['submitAnswers'])) {
    $pqid = $_GET['modifyAnswers']; 
    $pid = $_GET['pid'];
    $answers = $_POST['answers'];
    
    Database::get()->query("DELETE FROM poll_question_answer WHERE pqid IN
		(SELECT pqid FROM poll_question WHERE pid = ?d)", $pid);
    
    foreach ($answers as $answer) {
        if (!empty($answer)) {
            Database::get()->query("INSERT INTO poll_question_answer (pqid, answer_text)
							VALUES (?d, ?s)", $pqid, $answer);
        }
    }
    redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
    
}
if (isset($_GET['deleteQuestion'])) {
    $pqid = $_GET['deleteQuestion'];    
    $pid = $_GET['pid'];  

    Database::get()->query("DELETE FROM poll_question_answer WHERE pqid = ?d", $pqid);
    Database::get()->query("DELETE FROM poll_question WHERE pqid = ?d", $pqid);
    
    redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
}
if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];
    $poll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
    if(!$poll){
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
    $nameTools = $poll->name;
} else {
    if (!isset($_GET['newPoll'])) {
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
}
//question type text array
$aType = array($langUniqueSelect, $langFreeText, $langMultipleSelect, $langLabel.'/'.$langComment);
// Modify/Create poll form
if (isset($_GET['modifyPoll']) || isset($_GET['newPoll'])) {
    if (isset($_GET['modifyPoll'])) {
        $nameTools = $langInfoPoll;
        $navigation[] = array(
            'url' => "admin.php?course=$course_code&amp;pid=$pid", 
            'name' => $poll->name
        );            
    } else {
        $nameTools = $langCreatePoll;
    }
    $PollName = Session::has('PollName') ? Session::get('PollName') : (isset($poll) ? $poll->name : '');
    $PollDescription = Session::has('PollDescription') ? Session::get('PollDescription') : (isset($poll) ? $poll->description : '');
    $PollEndMessage = Session::has('PollEndMessage') ? Session::get('PollEndMessage') : (isset($poll) ? $poll->end_message : '');
    $PollStart = Session::has('PollStart') ? Session::get('PollStart') : date('d-m-Y H:i', (isset($poll) ? strtotime($poll->start_date) : strtotime('now')));
    $PollEnd = Session::has('PollEnd') ? Session::get('PollEnd') : date('d-m-Y H:i', (isset($poll) ? strtotime($poll->end_date) : strtotime('now +1 year')));

    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?course=$course_code".(isset($_GET['modifyPoll']) ? "&amp;pid=$pid&amp;modifyPoll=yes" : "&amp;newPoll=yes")."' method='post'>";
    $tool_content .= "
        <div id=\"operations_container\">
          <ul id=\"opslist\">
            <li><a href='".(isset($_GET['modifyPoll']) ? "admin.php?course=$course_code&amp;pid=$pid" : "index.php?course=$course_code")."'>".$langBack."</a></li>
	  </ul>
	</div>

        <fieldset>
        <legend>$langInfoPoll</legend>
	<table width=\"100%\" class='tbl'>
	<tr ".(Session::getError('PollName') ? "class='error'" : "").">
	  <th width='100'>$langTitle:</th>
	  <td><input type='text' size='50' name='PollName' value='$PollName'>".Session::getError('PollName', 'caution')."</td>
	</tr>
	<tr>
	  <th>$langPollStart:</th>
	  <td><input type='text' size='47' name='PollStart' value='$PollStart'></td></tr>
	<tr>
	  <th>$langPollEnd:</th>
	  <td><input type='text' size='47' name='PollEnd' value='$PollEnd'></td>
	</tr>
	<tr>
	  <th>$langPollAnonymize:</th>
	  <td><input type='checkbox' name='PollAnonymized' value='1' ".((isset($poll->anonymized) && $poll->anonymized) ? 'checked' : '')."></td>
	</tr>
	<tr>
	  <th>$langDescription:</th>
	  <td>".rich_text_editor('PollDescription', 4, 52, $PollDescription)."</td>
	</tr>
	<tr>
	  <th>$langPollEndMessage:</th>
	  <td>".rich_text_editor('PollEndMessage', 4, 52, $PollEndMessage)."</td>
	</tr>        
	<tr>
        <th>&nbsp;</th>";
        if (isset($_GET['newPoll'])) {
            $tool_content .= "<td><input type='submit' name='submitPoll' value='$langCreate'>&nbsp;&nbsp;";
        } else {
            $tool_content .= "<td><input type='submit' name='submitPoll' value='$langModify'>&nbsp;&nbsp;";
        }
        $tool_content .= "<input type='submit' name='cancelPoll' value='$langCancel'></td></tr></table></form>";
} elseif (isset($_GET['editQuestion'])) {
    if (isset($_GET['editQuestion'])) {
        $question_id = $_GET['editQuestion'];
        $question = Database::get()->querySingle('SELECT * FROM poll_question WHERE pid = ?d AND pqid = ?d', $pid, $question_id);
        if(!$question) {
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
        }
    }
    $nameTools = $langPollManagement;
    $navigation[] = array(
        'url' => "admin.php?course=$course_code&amp;pid=$pid", 
        'name' => $poll->name
    );            
    $tool_content .= "
        <fieldset>
            <legend>". (($question->qtype == QTYPE_LABEL) ? $langLabel.' / '.$langComment : $langQuestion) ."&nbsp;".  icon('edit', $langEdit, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&modifyQuestion=$question->pqid"). "</legend>
            <em><small>".$aType[$question->qtype - 1]."</small><br>
            <b>$question->question_text</b></em><br>
        </fieldset>        
    ";
    if ($question->qtype != QTYPE_LABEL && $question->qtype != QTYPE_FILL) {
        $tool_content .= "
          <table width='100%' class='tbl'>
            <tbody>
            <tr>
                <th>
                    <b><u>$langQuestionAnswers</u>:</b>&nbsp;&nbsp;
                    " . icon('edit', $langEdit, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&modifyAnswers=$question->pqid") . "<br>
                </th>
            </tr>
            </tbody>
          </table><br>  
        ";
    }
    $tool_content .= "
        <div class='right'><a href='admin.php?course=$course_code&amp;pid=$pid'>$langBackPollManagement</a></div>    
    ";
// Modify/Create question form        
} elseif (isset($_GET['newQuestion']) || isset($_GET['modifyQuestion'])) {
    $navigation[] = array(
        'url' => "admin.php?course=$course_code&amp;pid=$pid", 
        'name' => $poll->name
    );        
    if (isset($_GET['modifyQuestion'])) {
        $question_id = $_GET['modifyQuestion'];
        $question = Database::get()->querySingle('SELECT * FROM poll_question WHERE pid = ?d AND pqid = ?d', $pid, $question_id);
        if(!$question) {
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
        }
        $nameTools = $question->question_text;
        $navigation[] = array(
            'url' => "admin.php?course=$course_code&amp;pid=$pid&amp;editQuestion=$question->pqid", 
            'name' => $langPollManagement
        );         
    } else {
        $nameTools = $langNewQu;        
    }
     
    $action_url = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid".(isset($_GET['modifyQuestion']) ? "&amp;modifyQuestion=$question->pqid" : "&amp;newQuestion=yes");
    $action_url .= isset($_GET['questionType']) ?  '&amp;questionType=label' : '';
    $tool_content .= "<form action='$action_url' method='post'>";
    $tool_content .= "
	<fieldset>
	  <legend>$langInfoQuestion</legend>
	  <table class='tbl'>
	  <tr>
	    <th>$langQuestion:</th>
	    <td>".(isset($_GET['questionType']) || isset($question) && $question->qtype == QTYPE_LABEL ? rich_text_editor('questionName', 10, 10, isset($question)? $question->question_text : '') :"<input type='text' name='questionName'" . "size='50' value='".(isset($question)? $question->question_text : '')."'>")."</td>
	  </tr>";
    if (isset($_GET['questionType']) || isset($question) && $question->qtype == QTYPE_LABEL) {
        $tool_content .= "<tr><th>&nbsp;</th><td><input type='hidden' name='answerType' value='".QTYPE_LABEL."'></td></tr><tr><th>&nbsp;</th>";   
    } else {
        $tool_content .= "<tr>
            <th valign='top'>$langAnswerType: </th>
            <td><input type='radio' name='answerType' value='".QTYPE_SINGLE."' ".((isset($question) && $question->qtype == QTYPE_SINGLE) || !isset($question) ? 'checked="checked"' : '').">". $aType[QTYPE_SINGLE - 1] . "<br>"
            . "<input type='radio' name='answerType' value='".QTYPE_MULTIPLE."' ".(isset($question) && $question->qtype == QTYPE_MULTIPLE ? 'checked="checked"' : '').">". $aType[QTYPE_MULTIPLE - 1] . "<br>"
            . "<input type='radio' name='answerType' value='".QTYPE_FILL."' ".(isset($question) && $question->qtype == QTYPE_FILL ? 'checked="checked"' : '').">". $aType[QTYPE_FILL - 1] . "<br>
            </td>
          </tr>
          <tr>
            <th>&nbsp;</th>";
    }
    if (isset($_GET['newQuestion'])) {
        $tool_content .= "<td><input type='submit' name='submitQuestion' value='$langCreate'>&nbsp;&nbsp;";
    } else {
        $tool_content .= "<td><input type='submit' name='submitQuestion' value='$langModify'>&nbsp;&nbsp;";
    }
    $tool_content .= "<input type='submit' name='cancelQuestion' value='$langCancel'></td></tr></table></form>";

//Modify Answers    
} elseif (isset($_GET['modifyAnswers'])) {    
    $question_id = $_GET['modifyAnswers'];
    $question = Database::get()->querySingle('SELECT * FROM poll_question WHERE pid = ?d AND pqid = ?d', $pid, $question_id);
    $answers = Database::get()->queryArray("SELECT * FROM poll_question_answer
					WHERE pqid = ?d ORDER BY pqaid", $question->pqid);    
    if(!$question || $question->qtype == QTYPE_LABEL || $question->qtype == QTYPE_FILL) {
        redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
    }
    $nameTools = $langAnswers;
    $navigation[] = array(
        'url' => "admin.php?course=$course_code&amp;pid=$pid&amp;editQuestion=$question->pqid", 
        'name' => $langPollManagement
    );     
    $tool_content .= "
        <fieldset>
            <legend>$langQuestion</legend>
            <em><small>".$aType[$question->qtype - 1]."</small><br>
            <b>$question->question_text</b></em><br>
        </fieldset>        
    ";
    $tool_content .= "
    <fieldset>
    <legend>$langQuestionAnswers</legend>
    <form action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;modifyAnswers=$question_id' method='post'>
        <table width='100%' class='tbl poll_item'>
            <tbody>
            <tr>
              <td>
              $langPollAddAnswer: <input type='submit' name='MoreAnswers' value='+'></td>
            </tr>
            <tr>
                <td>
                    <ul class='poll_answers'>";
    if (count($answers) > 0) {
        foreach ($answers as $answer) {
            $tool_content .= "
                <li>
                    <input type='text' name='answers[]' value='$answer->answer_text' size='80'>&nbsp;" . icon('delete', $langDelete) . "&nbsp;" . icon('move_order', $langMove, null, "id='moveIconImg'") . "
                </li>            
            ";
        }
    } else {
        $tool_content .= "
            <li>
                <input type='text' name='answers[]' value='' size='80'>&nbsp;" . icon('delete', $langDelete) . "&nbsp;" . icon('move_order', $langMove, null, "id='moveIconImg'") . "
            </li>
            <li>
                <input type='text' name='answers[]' value='' size='80'>&nbsp;" . icon('delete', $langDelete) . "&nbsp;" . icon('move_order', $langMove, null, "id='moveIconImg'") . "
            </li>             
        ";        
    }
        
    $tool_content .= "</ul>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class='right'>
                    <input type='submit' name='submitAnswers' value='$langCreate'>&nbsp;&nbsp;
                    <input type='submit' name='cancelAnswers' value='$langCancel'>
                </td>
            </tr>
            </tbody>
        </table>
        </form>
    </fieldset>
    ";   
// View edit poll page     
} else {
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);
    $tool_content .= "
        <fieldset>
            <legend>$langInfoPoll &nbsp;".icon('edit', $langEditPoll, "admin.php?course=TMAPOST104&amp;pid=$pid&amp;modifyPoll=yes")."</legend>
            <table width='99%' class='tbl'>
            <tbody><tr>
              <th width='180'>$langTitle:</th>
              <td>".q($poll->name)."</td>
            </tr>
            <tr>
              <th>$langPollStart:</th>
              <td>".date('d-m-Y H:i',strtotime($poll->start_date))."</td>
            </tr>
            <tr>
                <th>$langPollEnd:</th>
                <td>".date('d-m-Y H:i',strtotime($poll->end_date))."</td>
            </tr>
            <tr>
              <th>$langPollAnonymize:</th>
              <td><input type='checkbox' disabled ".(($poll->anonymized)? 'checked' : '')."></td>
            </tr>
            <tr>
              <th>$langDescription:</th>
              <td>".$poll->description."</td>
            </tr>
            <tr>
                <th>$langPollEndMessage:</th>
                <td>".$poll->end_message."</td>
            </tr>
            </tbody></table>
        </fieldset>
        <div align='left' id='operations_container'>
            <ul id='opslist'>
              <li><a href='".$_SERVER['SCRIPT_NAME'] . "?course=$course_code&pid=$pid&newQuestion=yes'>$langNewQu</a></li>
              <li><a href='".$_SERVER['SCRIPT_NAME'] . "?course=$course_code&pid=$pid&newQuestion=yes&questionType=label'>$langNewLa</a></li>
            </ul>
        </div>";
    if ($questions) {    
        $tool_content .= "<table width='100%' class='tbl_alt'>
                    <tbody>
                        <tr>
                          <th colspan='2' class='left'>$langQuesList</th>
                          <th colspan='4' class='center'>$langCommands</th>
                        </tr>";
        $i=1;
        $nbrQuestions = count($questions);
        foreach ($questions as $question) {
        $tool_content .= "<tr class='even'>
                            <td align='right' width='1'>$i.</td>
                            <td>$question->question_text<br>".
                            $aType[$question->qtype - 1]."</td>
                            <td class='right' width='50'>".  icon('edit', $langEdit, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&editQuestion=$question->pqid")."&nbsp;".  icon('delete', $langDelete, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&deleteQuestion=$question->pqid", "onclick='return confirm(\"$langConfirmYourChoice\");'")."</td>
                            <td width='20'>".(($i!=1) ? icon('up', $langUp, $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid&amp;moveUp=$question->pqid") : '')."</td>
                            <td width='20'>".(($i!=$nbrQuestions) ? icon('down', $langDown, $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;pid=$pid&amp;moveDown=$question->pqid") : '')."</td>
                        </tr>";
            $i++;
        }
        $tool_content .= "</tbody></table>";
    }
}
draw($tool_content, 2, null, $head_content);
