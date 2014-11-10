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
global $themeimg;

if (isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    $poll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
    if(!$poll){     
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
}
if (isset($_GET['moveDown']) || isset($_GET['moveUp'])) {   
    $pqid = isset($_GET['moveUp']) ? intval($_GET['moveUp']) : intval($_GET['moveDown']);
    $pid = intval($_GET['pid']);
    $poll = Database::get()->querySingle("SELECT * FROM poll_question WHERE pid = ?d and pqid = ?d", $pid,$pqid);
    if(!$poll){
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
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
    $v->rule('required', array('PollName'));
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
            $pid = intval($_GET['pid']);
            Database::get()->query("UPDATE poll SET name = ?s,
                    start_date = ?t, end_date = ?t, description = ?s, end_message = ?s, anonymized = ?d WHERE course_id = ?d AND pid = ?d", $PollName, $PollStart, $PollEnd, $PollDescription, $PollEndMessage, $PollAnonymized, $course_id, $pid);
            Session::Messages($langPollEdited, 'alert-success');
        } else {
            $PollActive = 1;
            $pid = Database::get()->query("INSERT INTO poll
                        (course_id, creator_id, name, creation_date, start_date, end_date, active, description, end_message, anonymized)
                        VALUES (?d, ?d, ?s, NOW(), ?t, ?t, ?d, ?s, ?s, ?d)", $course_id, $uid, $PollName, $PollStart, $PollEnd, $PollActive, $PollDescription, $PollEndMessage, $PollAnonymized)->lastInsertID;
            Session::Messages($langPollCreated, 'alert-success');
        }
        redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
    } else {
        // Errors
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        if(isset($_GET['pid'])) {
            $pid = intval($_GET['pid']); 
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid&modifyPoll=yes");
        } else {        
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&newPoll=yes");
        }
    } 
}
if (isset($_POST['submitQuestion'])) {
    $question_text = $_POST['questionName'];
    $qtype = $_POST['answerType'];    
    $pid = intval($_GET['pid']);  
    if(isset($_GET['modifyQuestion'])) {
        $pqid = intval($_GET['modifyQuestion']);
        $poll = Database::get()->querySingle("SELECT * FROM poll_question WHERE pid = ?d and pqid = ?d", $pid,$pqid);
        if(!$poll){
            redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
        }
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
    $pqid = intval($_GET['modifyAnswers']); 
    $pid = intval($_GET['pid']);
    $poll = Database::get()->querySingle("SELECT * FROM poll_question WHERE pid = ?d and pqid = ?d", $pid,$pqid);
    if(!$poll){
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
    $answers = $_POST['answers'];
    
    Database::get()->query("DELETE FROM poll_question_answer WHERE pqid IN
		(SELECT pqid FROM poll_question WHERE pid = ?d AND pqid = ?d)", $pid, $pqid);
    
    foreach ($answers as $answer) {
        if (!empty($answer)) {
            Database::get()->query("INSERT INTO poll_question_answer (pqid, answer_text)
							VALUES (?d, ?s)", $pqid, $answer);
        }
    }
    redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
    
}
if (isset($_GET['deleteQuestion'])) {
    $pqid = intval($_GET['deleteQuestion']);    
    $pid = intval($_GET['pid']);  
    $poll = Database::get()->querySingle("SELECT * FROM poll_question WHERE pid = ?d and pqid = ?d", $pid,$pqid);
    if(!$poll){
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
    Database::get()->query("DELETE FROM poll_question_answer WHERE pqid = ?d", $pqid);
    Database::get()->query("DELETE FROM poll_question WHERE pqid = ?d", $pqid);
    
    redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
}
if (isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
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
    load_js('bootstrap-datetimepicker');
    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#startdatepicker, #enddatepicker').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', 
                pickerPosition: 'bottom-left', 
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";    
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

    $link_back = isset($_GET['modifyPoll']) ? "admin.php?course=$course_code&amp;pid=$pid" : "index.php?course=$course_code";
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'level' => 'primary',
              'url' => $link_back,
              'icon' => 'fa-reply'))); 
    $tool_content .= " 
    <div class='form-wrapper'>    
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code".(isset($_GET['modifyPoll']) ? "&amp;pid=$pid&amp;modifyPoll=yes" : "&amp;newPoll=yes")."' method='post'>
            <fieldset>
            <div class='form-group ".(Session::getError('PollName') ? "has-error" : "")."'>
              <label for='PollName' class='col-sm-2 control-label'>$langTitle :</label>
              <div class='col-sm-10'>
                <input type='text' class='form-control' id='PollName' name='PollName' placeholder='$langTitle' value='$PollName'>
                <span class='help-block'>".Session::getError('PollName')."</span>
              </div>
            </div>
            <div class='input-append date form-group' id='startdatepicker' data-date='$PollStart' data-date-format='dd-mm-yyyy'>
                <label for='PollStart' class='col-sm-2 control-label'>$langPollStart :</label>
                <div class='col-xs-10 col-sm-9'>        
                    <input name='PollStart' id='PollStart' type='text' value='$PollStart'>
                </div>
                <div class='col-xs-2 col-sm-1'>  
                    <span class='add-on'><i class='fa fa-times'></i></span>
                    <span class='add-on'><i class='fa fa-calendar'></i></span>
                </div>
            </div>            
            <div class='input-append date form-group' id='enddatepicker' data-date='$PollEnd' data-date-format='dd-mm-yyyy'>
                <label for='PollEnd' class='col-sm-2 control-label'>$langPollEnd :</label>
                <div class='col-xs-10 col-sm-9'>        
                    <input name='PollEnd' id='PollEnd' type='text' value='$PollEnd'>
                </div>
                <div class='col-xs-2 col-sm-1'>  
                    <span class='add-on'><i class='fa fa-times'></i></span>
                    <span class='add-on'><i class='fa fa-calendar'></i></span>
                </div>
            </div>
            <div class='form-group'>
              <label for='PollAnonymized' class='col-sm-2 control-label'>$langPollAnonymize : </label>
              <div class='col-sm-10'>
                <input type='checkbox' name='PollAnonymized' id='PollAnonymized' value='1' ".((isset($poll->anonymized) && $poll->anonymized) ? 'checked' : '')."> 
              </div>
            </div>            
            <div class='form-group'>
              <label for='PollDescription' class='col-sm-2 control-label'>$langDescription :</label>
              <div class='col-sm-10'>
                ".rich_text_editor('PollDescription', 4, 52, $PollDescription)."
              </div>
            </div> 
            <div class='form-group'>
              <label for='PollEndMessage' class='col-sm-2 control-label'>$langPollEndMessage : </label>
              <div class='col-sm-10'>
                ".rich_text_editor('PollEndMessage', 4, 52, $PollEndMessage)."
              </div>
            </div>                
            <div class='form-group'>
              <div class='col-sm-offset-2 col-sm-10'>
                <input type='submit' class='btn btn-primary' name='submitPoll' value='".(isset($_GET['newPoll']) ? $langCreate : $langModify)."'>
                <a href='$link_back' class='btn btn-default'>$langCancel</a>    
              </div>
            </div>
        </fieldset>        
        </form>
    </div>";
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
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary',
            'icon' => 'fa-reply',
            'url' => "admin.php?course=$course_code&amp;pid=$pid"
        )
    ));
    
    $tool_content .= "
    <div class='panel panel-primary'>
      <div class='panel-heading'>
        <h3 class='panel-title'>". (($question->qtype == QTYPE_LABEL) ? $langLabel.' / '.$langComment : $langQuestion) ."&nbsp;".  icon('fa-edit', $langEdit, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&modifyQuestion=$question->pqid"). "</h3>
      </div>
      <div class='panel-body'>
        <h4>$question->question_text<br><small>".$aType[$question->qtype - 1]."</small></h4>
      </div>
    </div>";     
    if ($question->qtype != QTYPE_LABEL && $question->qtype != QTYPE_FILL) {
        $tool_content .= "
        <div class='panel panel-info'>
                  <div class='panel-heading'>
                    <h3 class='panel-title'>$langQuestionAnswers &nbsp;&nbsp;" . icon('fa-edit', $langEdit, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&modifyAnswers=$question->pqid") . "</a></h3>
                  </div>
        <!--      <div class='panel-body'>
                    Answers should be placed here
                  </div>
        -->          
        </div>";            
    }
    $tool_content .= "
        <div class='pull-right'><a href='admin.php?course=$course_code&amp;pid=$pid'>$langBackPollManagement</a></div>    
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
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary',
            'url' => "admin.php?course=$course_code&pid=$pid".(isset($_GET['modifyQuestion']) ? "&editQuestion=".$_GET['modifyQuestion'] : ""),
            'icon' => 'fa-reply'
        )
    ));
    
    $tool_content .= "<div class='form-wrapper'><form class='form-horizontal' role='form' action='$action_url' method='post'>
	<fieldset>
            <div class='form-group'>
                <label for='questionName' class='col-sm-2 control-label'>".(isset($_GET['questionType']) ? $langLabel : $langQuestion).":</label>
                <div class='col-sm-10'>
                  ".(isset($_GET['questionType']) || isset($question) && $question->qtype == QTYPE_LABEL ? rich_text_editor('questionName', 10, 10, isset($question)? $question->question_text : '') :"<input type='text' id='questionName' name='questionName' size='50' value='".(isset($question)? q($question->question_text) : '')."'>")."
                </div>
            </div>";
    if (isset($_GET['questionType']) || isset($question) && $question->qtype == QTYPE_LABEL) {
        $tool_content .= "<input type='hidden' name='answerType' value='".QTYPE_LABEL."'>";
    } else {
        $tool_content .= "
            <div class='form-group'>
                <label for='answerType' class='col-sm-2 control-label'>$langExerciseType:</label>
                <div class='col-sm-10'>            
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' id='answerType' value='1' value='".QTYPE_SINGLE."' ".((isset($question) && $question->qtype == QTYPE_SINGLE) || !isset($question) ? 'checked' : '').">
                        ". $aType[QTYPE_SINGLE - 1] . "
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' id='answerType' value='".QTYPE_MULTIPLE."' ".(isset($question) && $question->qtype == QTYPE_MULTIPLE ? 'checked' : '').">
                        ". $aType[QTYPE_MULTIPLE - 1] . "
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' id='answerType' value='".QTYPE_FILL."' ".(isset($question) && $question->qtype == QTYPE_FILL ? 'checked="checked"' : '').">
                        ". $aType[QTYPE_FILL - 1] . "
                      </label>
                    </div>                    
                </div>
            </div>";
    }
    $tool_content .= "
            <div class='col-md-10 col-md-offset-2'>
                <input type='submit' class='btn btn-primary' name='submitQuestion' value='".(isset($_GET['newQuestion']) ? $langCreate : $langModify)."'>
                <a href='admin.php?course=$course_code&pid=$pid".(isset($_GET['modifyQuestion']) ? "&editQuestion=".$_GET['modifyQuestion'] : "")."' class='btn btn-default'>$langCancel</a>
            </div>
        </fieldset>
    </form></div>";

//Modify Answers    
} elseif (isset($_GET['modifyAnswers'])) {
    $head_content .= "
    <script>
        $(function() {
            $(poll_init);
        });
    </script>
    ";
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
        <div class='panel panel-primary'>
            <div class='panel-heading'>
              <h3 class='panel-title'>$langQuestion</h3>
            </div>
            <div class='panel-body'>
                  <h4>$question->question_text<br><small><em>".$aType[$question->qtype - 1]."</em></small></h4>                         
            </div>
        </div>";
    
    $tool_content .= "      
        <div class='panel panel-info'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$langQuestionAnswers</h3>
            </div>
            <div class='panel-body'>
                    <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;modifyAnswers=$question_id' method='post'>                   
                    <fieldset>
                    <div class='form-group'>
                        <label for='questionName' class='col-xs-3 control-label'>$langPollAddAnswer:</label>
                        <div class='col-xs-9'>
                          <input class='btn btn-primary' type='submit' name='MoreAnswers' value='+'>
                        </div>
                    </div><hr><br>";
        if (count($answers) > 0) {
              foreach ($answers as $answer) {    
              $tool_content .="      
                  <div class='form-group'>
                        <div class='col-xs-11'>
                            <input type='text' name='answers[]' value='$answer->answer_text'>                        
                        </div>
                        <div class='col-xs-1'>
                            " . icon('fa-times', $langDelete, '#') . "
                        </div>
                    </div>";
              }
        } else {
              $tool_content .="      
                  <div class='form-group'>
                        <div class='col-xs-11'>
                            <input type='text' name='answers[]' value=''>                        
                        </div>
                        <div class='col-xs-1'>
                            " . icon('fa-times', $langDelete, '#') . "
                        </div>
                    </div>
                  <div class='form-group'>
                        <div class='col-xs-11'>
                            <input type='text' name='answers[]' value=''>                        
                        </div>
                        <div class='col-xs-1'>
                            " . icon('fa-times', $langDelete, '#') . "
                        </div>
                    </div>";
        }                                        
        $tool_content .= "
                    <div class='row'>
                        <div class='col-sm-10 col-sm-offset-2'>                          
                            <input class='btn btn-primary' type='submit' name='submitAnswers' value='$langCreate'>
                            <a class='btn btn-default' href='admin.php?course=TMAPOST106&pid=$pid&editQuestion=$question_id'>$langCancel</a>
                        </div>
                    </div>
                    </fieldset>
                    </form>
            </div>
        </div>";
// View edit poll page     
} else {    
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'level' => 'primary',
              'url' => "index.php?course=$course_code",
              'icon' => 'fa-reply')));    
    $tool_content .= "
        <div class='panel panel-primary'>
          <div class='panel-heading'>
            <h3 class='panel-title'>$langInfoPoll &nbsp;".icon('fa-edit', $langEditPoll, "admin.php?course=$course_code&amp;pid=$pid&amp;modifyPoll=yes")."</h3>
          </div>
          <div class='panel-body'>
            <div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langTitle:</strong>
                </div>
                <div class='col-sm-9'>
                    $poll->name
                </div>                
            </div>
            <div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langPollStart:</strong>
                </div>
                <div class='col-sm-9'>
                    ".date('d-m-Y H:i',strtotime($poll->start_date))."
                </div>                
            </div>
            <div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langPollEnd:</strong>
                </div>
                <div class='col-sm-9'>
                    ".date('d-m-Y H:i',strtotime($poll->end_date))."
                </div>                
            </div>            
            <div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langPollAnonymize:</strong>
                </div>
                <div class='col-sm-9'>
                    ".(($poll->anonymized)? icon('fa-check-square-o') : icon('fa-square-o'))."
                </div>                
            </div>
            <div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langDescription:</strong>
                </div>
                <div class='col-sm-9'>
                    $poll->description
                </div>                
            </div>
            <div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langPollEndMessage:</strong>
                </div>
                <div class='col-sm-9'>
                    $poll->end_message
                </div>                
            </div>                
          </div>          
        </div>        
    ";
    $tool_content .= action_bar(array(
        array('title' => $langNewQu,
              'level' => 'primary-label',
              'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&pid=$pid&newQuestion=yes",
              'icon' => 'fa-plus-circle',
              'button-class' => 'btn-success'),
        array('title' => $langNewLa,
              'level' => 'primary-label',
              'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&pid=$pid&newQuestion=yes&questionType=label",
              'icon' => 'fa-tag',
              'button-class' => 'btn-success')        
        )); 
    if ($questions) {    
        $tool_content .= "<table class='table table-striped table-bordered table-hover'>
                    <tbody>
                        <tr>
                          <th colspan='2'>$langQuesList</th>
                          <th class='text-center'>".icon('fa-gears', $langCommands)."</th>
                        </tr>";
        $i=1;
        $nbrQuestions = count($questions);
        foreach ($questions as $question) {
        $tool_content .= "<tr class='even'>
                            <td align='right' width='1'>$i.</td>
                            <td>".(($question->qtype != QTYPE_LABEL) ? q($question->question_text) : $question->question_text)."<br>".
                            $aType[$question->qtype - 1]."</td>
                            <td class='option-btn-cell'>".action_button(array(
                                array(
                                    'title' => $langEdit,
                                    'icon' => 'fa-edit',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&pid=$pid&editQuestion=$question->pqid"
                                ),
                                array(
                                    'title' => $langDelete,
                                    'icon' => 'fa-times',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&pid=$pid&deleteQuestion=$question->pqid",
                                    'class' => 'delete',
                                    'confirm' => $langConfirmYourChoice                                  
                                ),
                                array(
                                    'title' => $langUp,
                                    'icon' => 'fa-arrow-up',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;moveUp=$question->pqid",
                                    'show' => $i!=1
                                ),
                                array(
                                    'title' => $langDown,
                                    'icon' => 'fa-arrow-down',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;moveDown=$question->pqid",
                                    'show' => $i!=$nbrQuestions                                   
                                )
                            ))."</td></tr>";
            $i++;
        }
        $tool_content .= "</tbody></table>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langPollEmpty</div>";
    }
}
draw($tool_content, 2, null, $head_content);
