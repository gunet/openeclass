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
$toolName = $langQuestionnaire;
$navigation[] = array(
            'url' => "index.php?course=$course_code", 
            'name' => $langQuestionnaire
        );

if (isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
}

if (isset($_GET['moveDown']) || isset($_GET['moveUp'])) {   
    $pqid = isset($_GET['moveUp']) ? intval($_GET['moveUp']) : intval($_GET['moveDown']);
    $poll = Database::get()->querySingle("SELECT * FROM poll_question WHERE pid = ?d and pqid = ?d", $pid,$pqid);
    if(!$poll){        
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
    $position = $poll->q_position;
    $new_position = isset($_GET['moveUp']) ? $position - 1 : $position + 1;
    $trade_position_pqid = Database::get()->querySingle("SELECT pqid FROM `poll_question`
				  WHERE pid = ?d AND q_position = ?d", $pid, $new_position)->pqid;  
    Database::get()->query("UPDATE poll_question SET q_position = ?d WHERE pid = ?d AND pqid= ?d", $new_position, $pid, $pqid);
    Database::get()->query("UPDATE poll_question SET q_position = ?d WHERE pid = ?d AND pqid = ?d", $position, $pid, $trade_position_pqid);
    redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
}

if (isset($_POST['submitPoll'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('PollName','PollStart','PollEnd'));
    $v->rule('date', array('PollStart','PollEnd'));
    $v->labels(array(
        'PollName' => "$langTheField $langTitle",
        'PollStart' => "$langTheField $langTitle",
        'PollEnd' => "$langTheField $langTitle"
    ));
    if($v->validate()) {
        $PollName = $_POST['PollName'];
        $PollStart = date('Y-m-d H:i', strtotime($_POST['PollStart']));
        $PollEnd = date('Y-m-d H:i', strtotime($_POST['PollEnd']));
        $PollDescription = purify($_POST['PollDescription']);
        $PollEndMessage = purify($_POST['PollEndMessage']);    
        $PollAnonymized = (isset($_POST['PollAnonymized'])) ? $_POST['PollAnonymized'] : 0;
        $PollShowResults = (isset($_POST['PollShowResults'])) ? $_POST['PollShowResults'] : 0;
        if(isset($_GET['pid'])) {
            Database::get()->query("UPDATE poll SET name = ?s,
                    start_date = ?t, end_date = ?t, description = ?s, end_message = ?s, anonymized = ?d, show_results = ?d WHERE course_id = ?d AND pid = ?d", $PollName, $PollStart, $PollEnd, $PollDescription, $PollEndMessage, $PollAnonymized, $PollShowResults, $course_id, $pid);
            Session::Messages($langPollEdited, 'alert-success');
        } else {
            $PollActive = 1;
            $pid = Database::get()->query("INSERT INTO poll
                        (course_id, creator_id, name, creation_date, start_date, end_date, active, description, end_message, anonymized, show_results)
                        VALUES (?d, ?d, ?s, NOW(), ?t, ?t, ?d, ?s, ?s, ?d, ?d)", $course_id, $uid, $PollName, $PollStart, $PollEnd, $PollActive, $PollDescription, $PollEndMessage, $PollAnonymized, $PollShowResults)->lastInsertID;
            Session::Messages($langPollCreated, 'alert-success');
        }
        redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
    } else {
        // Errors
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        if (isset($_GET['pid'])) {
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid&modifyPoll=yes");
        } else {        
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&newPoll=yes");
        }
    } 
}
if (isset($_POST['submitQuestion'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('questionName'));
    if (isset($_POST['questionScale'])) {
        $v->rule('required', array('questionScale'));
    }
    $v->rule('numeric', array('questionScale'));
    $v->rule('min', array('questionScale'), 1);
    $v->labels(array(
        'questionName' => "$langTheField $langPollStart",
        'questionScale' => "$langTheField $langPollEnd"
    ));
    if($v->validate()) {    
        $question_text = $_POST['questionName'];
        $qtype = $_POST['answerType'];    
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
            $query_columns = "pid, question_text, qtype, q_position";
            $query_values = "?d, ?s, ?d, ?d";
            $query_vars = array($pid, $question_text, $qtype, $max_position + 1);
            if(isset($_POST['questionScale'])){
                $query_columns .= ", q_scale";
                $query_values .=", ?d";
                $query_vars[] = $_POST['questionScale'];
            }
            $pqid = Database::get()->query("INSERT INTO poll_question
                        ($query_columns)
                        VALUES ($query_values)", $query_vars)->lastInsertID;
        }
        if ($qtype == QTYPE_FILL || $qtype == QTYPE_LABEL || $qtype == QTYPE_SCALE) {
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
        } else {
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid&modifyAnswers=$pqid");
        }
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        if(isset($_GET['modifyQuestion'])) {
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid&modifyQuestion=$pqid");
        } else {
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid&newQuestion=yes");
        }
    }
}
if (isset($_POST['submitAnswers'])) {
    $pqid = intval($_GET['modifyAnswers']); 
    $question = Database::get()->querySingle("SELECT * FROM poll_question WHERE pid = ?d and pqid = ?d", $pid,$pqid);
    if(!$question){
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
    $poll = Database::get()->querySingle("SELECT * FROM poll_question WHERE pid = ?d and pqid = ?d", $pid,$pqid);
    if(!$poll){
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
    Database::get()->query("DELETE FROM poll_question_answer WHERE pqid = ?d", $pqid);
    Database::get()->query("DELETE FROM poll_question WHERE pqid = ?d", $pqid);
    
    redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
}
if (isset($_GET['pid'])) {
    $poll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d", $course_id, $pid);
    if(!$poll){
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
    $pageName = $poll->name;
    $attempt_counter = Database::get()->querySingle("SELECT COUNT(*) AS count FROM poll_answer_record WHERE pid = ?d", $pid)->count;  
    if ($attempt_counter>0) {
        Session::Messages($langThereAreParticipants);
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }         
} else {
    if (!isset($_GET['newPoll'])) {
        redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
    }
}
//question type text array
$aType = array($langUniqueSelect, $langFreeText, $langMultipleSelect, $langLabel.'/'.$langComment, $langScale);
// Modify/Create poll form
if (isset($_GET['modifyPoll']) || isset($_GET['newPoll'])) {
    if (isset($_GET['modifyPoll'])) {
        $pageName = $langInfoPoll;
        $navigation[] = array(
            'url' => "admin.php?course=$course_code&amp;pid=$pid", 
            'name' => $poll->name
        );
    } else {
        $pageName = $langCreatePoll;
    }    
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

    $PollName = Session::has('PollName') ? Session::get('PollName') : (isset($poll) ? $poll->name : '');
    $PollDescription = Session::has('PollDescription') ? Session::get('PollDescription') : (isset($poll) ? $poll->description : '');
    $PollEndMessage = Session::has('PollEndMessage') ? Session::get('PollEndMessage') : (isset($poll) ? $poll->end_message : '');
    $PollStart = Session::has('PollStart') ? Session::get('PollStart') : date('d-m-Y H:i', (isset($poll) ? strtotime($poll->start_date) : strtotime('now')));
    $PollEnd = Session::has('PollEnd') ? Session::get('PollEnd') : date('d-m-Y H:i', (isset($poll) ? strtotime($poll->end_date) : strtotime('now +1 year')));

    $link_back = isset($_GET['modifyPoll']) ? "admin.php?course=$course_code&amp;pid=$pid" : "index.php?course=$course_code";
    $pageName = isset($_GET['modifyPoll']) ? "$langEditPoll" : "$langCreatePoll";
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'level' => 'primary-label',
              'url' => $link_back,
              'icon' => 'fa-reply'))); 
    $tool_content .= " 
    <div class='form-wrapper'>    
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code".(isset($_GET['modifyPoll']) ? "&amp;pid=$pid&amp;modifyPoll=yes" : "&amp;newPoll=yes")."' method='post'>
            <fieldset>
            <div class='form-group ".(Session::getError('PollName') ? "has-error" : "")."'>
              <label for='PollName' class='col-sm-2 control-label'>$langTitle:</label>
              <div class='col-sm-10'>
                <input type='text' class='form-control' id='PollName' name='PollName' placeholder='$langTitle' value='$PollName'>
                <span class='help-block'>".Session::getError('PollName')."</span>
              </div>
            </div>
            <div class='input-append date form-group".(Session::getError('PollStart') ? " has-error" : "")."' id='startdatepicker' data-date='$PollStart' data-date-format='dd-mm-yyyy'>
                <label for='PollStart' class='col-sm-2 control-label'>$langPollStart:</label>
                <div class='col-xs-10 col-sm-9'>        
                    <input class='form-control' name='PollStart' id='PollStart' type='text' value='$PollStart'>
                    <span class='help-block'>".Session::getError('PollStart')."</span>
                </div>
                <div class='col-xs-2 col-sm-1'>  
                    <span class='add-on'><i class='fa fa-calendar'></i></span>
                </div>
            </div>            
            <div class='input-append date form-group".(Session::getError('PollEnd') ? " has-error" : "")."' id='enddatepicker' data-date='$PollEnd' data-date-format='dd-mm-yyyy'>
                <label for='PollEnd' class='col-sm-2 control-label'>$langPollEnd:</label>
                <div class='col-xs-10 col-sm-9'>        
                    <input class='form-control' name='PollEnd' id='PollEnd' type='text' value='$PollEnd'>
                    <span class='help-block'>".Session::getError('PollEnd')."</span>
                </div>
                <div class='col-xs-2 col-sm-1'>  
                    <span class='add-on'><i class='fa fa-calendar'></i></span>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langResults:</label>
                <div class='col-sm-10'>
                    <div class='checkbox'>
                        <label>   
                            <input type='checkbox' name='PollAnonymized' id='PollAnonymized' value='1' ".((isset($poll->anonymized) && $poll->anonymized) ? 'checked' : '').">
                            $langPollAnonymize    
                        </label>
                    </div>
                    <div class='checkbox'>
                        <label>   
                            <input type='checkbox' name='PollShowResults' id='PollShowResults' value='1' ".((isset($poll->show_results) && $poll->show_results) ? 'checked' : '').">
                            $langPollShowResults
                        </label>
                    </div>                    
              </div>
            </div>              
            <div class='form-group'>
              <label for='PollDescription' class='col-sm-2 control-label'>$langDescription:</label>
              <div class='col-sm-10'>
                ".rich_text_editor('PollDescription', 4, 52, $PollDescription)."
              </div>
            </div> 
            <div class='form-group'>
              <label for='PollEndMessage' class='col-sm-2 control-label'>$langPollEndMessage:</label>
              <div class='col-sm-10'>
                ".rich_text_editor('PollEndMessage', 4, 52, $PollEndMessage)."
              </div>
            </div>                
            <div class='form-group'>
              <div class='col-sm-offset-2 col-sm-10'>".
            form_buttons(array(
                array(
                    'text'  => $langSave,
                    'name'  => 'submitPoll',
                    'value' => (isset($_GET['newPoll']) ? $langCreate : $langModify)
                ),
                array(
                    'href' => "index.php?course=$course_code",
                )
            ))
            ."
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
    $pageName = $langPollManagement;
    $navigation[] = array(
        'url' => "admin.php?course=$course_code&amp;pid=$pid", 
        'name' => $poll->name
    );
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => "admin.php?course=$course_code&amp;pid=$pid"
        )
    ));
    
    $tool_content .= "
    <div class='panel panel-primary'>
      <div class='panel-heading'>
        <h3 class='panel-title'>". (($question->qtype == QTYPE_LABEL) ? $langLabel.' / '.$langComment : $langQuestion) ."&nbsp;".  icon('fa-edit', $langEditChange, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&modifyQuestion=$question->pqid"). "</h3>
      </div>
      <div class='panel-body'>
        <h4>$question->question_text<br><small>".$aType[$question->qtype - 1]."</small></h4>
      </div>
    </div>";     
    if ($question->qtype != QTYPE_LABEL && $question->qtype != QTYPE_FILL && $question->qtype != QTYPE_SCALE) {
        $tool_content .= "
        <div class='panel panel-info'>
                  <div class='panel-heading'>
                    <h3 class='panel-title'>$langQuestionAnswers &nbsp;&nbsp;" . icon('fa-edit', $langEditChange, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&modifyAnswers=$question->pqid") . "</a></h3>
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
        $pageName = $question->question_text;
        $navigation[] = array(
            'url' => "admin.php?course=$course_code&amp;pid=$pid&amp;editQuestion=$question->pqid", 
            'name' => $langPollManagement
        );         
    } else {
        $pageName = $langNewQu;        
    }
     
    $action_url = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid".(isset($_GET['modifyQuestion']) ? "&amp;modifyQuestion=$question->pqid" : "&amp;newQuestion=yes");
    $action_url .= isset($_GET['questionType']) ?  '&amp;questionType=label' : '';
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'url' => "admin.php?course=$course_code&pid=$pid".(isset($_GET['modifyQuestion']) ? "&editQuestion=".$_GET['modifyQuestion'] : ""),
            'icon' => 'fa-reply'
        )
    ));
    $questionName = Session::has('questionName') ? Session::get('questionName') : (isset($question) ? $question->question_text : '');
    $questionNameError = Session::getError('questionName');
    $questionNameErrorClass = ($questionNameError) ? "has-error" : "";
    
    $answerType = Session::has('answerType') ? Session::get('answerType') : (isset($question) ? $question->qtype : '');
    
    $questionScale = Session::has('questionScale') ? Session::get('questionScale') : (isset($question) ? $question->q_scale : 5);
    $questionScaleError = Session::getError('questionScale');
    $questionScaleErrorClass = ($questionScaleError) ? " has-error" : "";
    $questionScaleShowHide = $answerType == QTYPE_SCALE ? "" : " hidden";
    
    $tool_content .= "<div class='form-wrapper'><form class='form-horizontal' role='form' action='$action_url' method='post'>
	<fieldset>
            <div class='form-group $questionNameErrorClass'>
                <label for='questionName' class='col-sm-2 control-label'>".(isset($_GET['questionType']) ? $langLabel : $langQuestion).":</label>
                <div class='col-sm-10'>
                  ".(isset($_GET['questionType']) || isset($question) && $question->qtype == QTYPE_LABEL ? rich_text_editor('questionName', 10, 10, $questionName) :"<input type='text' class='form-control' id='questionName' name='questionName' value='".q($questionName)."'>")."
                  <span class='help-block'>$questionNameError</span>    
                </div>
            </div>";
    if (isset($_GET['questionType']) || isset($question) && $question->qtype == QTYPE_LABEL) {
        $tool_content .= "<input type='hidden' name='answerType' value='".QTYPE_LABEL."'>";
    } else {
        $head_content .= "<script type='text/javascript'>
        $(function() {
            $('.answerType').change(function() {
                if($(this).val()==5){
                    $('#questionScale').prop('disabled', false);
                    $('#questionScale').closest('div.form-group').removeClass('hidden');
                } else {
                    $('#questionScale').prop('disabled', true);
                    $('#questionScale').closest('div.form-group').addClass('hidden');
                }
            });        
        });
        </script>";
        $tool_content .= "
            <div class='form-group'>
                <label for='answerType' class='col-sm-2 control-label'>$langSurveyType:</label>
                <div class='col-sm-10'>            
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' class='answerType' value='1' value='".QTYPE_SINGLE."' ".($answerType == QTYPE_SINGLE || !isset($question) ? 'checked' : '').">
                        ". $aType[QTYPE_SINGLE - 1] . "
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' class='answerType' value='".QTYPE_MULTIPLE."' ".($answerType == QTYPE_MULTIPLE ? 'checked' : '').">
                        ". $aType[QTYPE_MULTIPLE - 1] . "
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' class='answerType' value='".QTYPE_FILL."' ".($answerType == QTYPE_FILL ? 'checked' : '').">
                        ". $aType[QTYPE_FILL - 1] . "
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' class='answerType' value='".QTYPE_SCALE."' ".($answerType == QTYPE_SCALE ? 'checked' : '').">
                        ". $aType[QTYPE_SCALE - 1] . "
                      </label>
                    </div>                    
                </div>              
            </div>
            <div class='form-group$questionScaleErrorClass$questionScaleShowHide'>
                <label for='questionScale' class='col-sm-2 control-label'>$langMax $langScale (1-..):</label>
                <div class='col-sm-10 col-md-3'>
                    <input type='text' class='form-control' name='questionScale' id='questionScale' value='".q($questionScale)."'>
                    <span class='help-block'>$questionScaleError</span>                    
                </div>
            </div>";
    }
    $tool_content .= "
            <div class='col-md-10 col-md-offset-2'>".
            form_buttons(array(
                array(
                    'text'  => $langSave,
                    'name'  => 'submitQuestion',
                    'value' => (isset($_GET['newQuestion']) ? $langCreate : $langModify)
                ),
                array(
                    'href' => "admin.php?course=$course_code&pid=$pid".(isset($_GET['modifyQuestion']) ? "&editQuestion=".$_GET['modifyQuestion'] : "")
                )
            ))
            ."</div>
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
    if(!$question || $question->qtype == QTYPE_LABEL || $question->qtype == QTYPE_FILL || $question->qtype == QTYPE_SCALE) {
        redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
    }
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
                        <label class='col-xs-3 control-label'>$langPollAddAnswer:</label>
                        <div class='col-xs-9'>
                          <input class='btn btn-primary' type='submit' name='MoreAnswers' value='+'>
                        </div>
                    </div><hr><br>";
        if (count($answers) > 0) {
            foreach ($answers as $answer) {    
              $tool_content .="      
                  <div class='form-group'>
                        <div class='col-xs-11'>
                            <input type='text' class='form-control' name='answers[]' value='$answer->answer_text'>                        
                        </div>
                        <div class='col-xs-1 form-control-static'>
                            " . icon('fa-times', $langDelete, '#', ' class="del_btn"') . "
                        </div>
                    </div>";
              }
        } else {
            $tool_content .="      
                  <div class='form-group'>
                        <div class='col-xs-11'>
                            <input class='form-control' type='text' name='answers[]' value=''>                        
                        </div>
                        <div class='col-xs-1 form-control-static'>
                            " . icon('fa-times', $langDelete, '#', ' class="del_btn"') . "
                        </div>
                    </div>
                  <div class='form-group'>
                        <div class='col-xs-11'>
                            <input class='form-control' type='text' name='answers[]' value=''>                        
                        </div>
                        <div class='col-xs-1 form-control-static'>
                            " . icon('fa-times', $langDelete, '#', ' class="del_btn"') . "
                        </div>
                    </div>";
        }                                        
        $tool_content .= "
                    <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>                          
                            <input class='btn btn-primary' type='submit' name='submitAnswers' value='$langCreate'>
                            <a class='btn btn-default' href='admin.php?course=$course_code&amp;pid=$pid&amp;editQuestion=$question_id'>$langCancel</a>
                        </div>
                    </div>
                    </fieldset>
                    </form>
            </div>
        </div>";
// View edit poll page     
} else {  
    $pageName = $langEditChange;
    $navigation[] = array(
            'url' => "admin.php?course=$course_code&amp;pid=$pid", 
            'name' => $poll->name
        );
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'level' => 'primary-label',
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
                    <strong>$langResults:</strong>
                </div>
                <div class='col-sm-9'>
                    ".(($poll->anonymized) ? icon('fa-check-square-o') : icon('fa-square-o'))." $langPollAnonymize <br>
                    ".(($poll->show_results) ? icon('fa-check-square-o') : icon('fa-square-o'))." $langPollShowResults   
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
        ),false); 
    if ($questions) {    
        $tool_content .= "<table class='table-default'>
                    <tbody>
                        <tr class='list-header'>
                          <th colspan='2'>$langQuesList</th>
                          <th class='text-center'>".icon('fa-gears', $langCommands)."</th>
                        </tr>";
        $i=1;
        $nbrQuestions = count($questions);
        foreach ($questions as $question) {
        $tool_content .= "<tr class='even'>
                            <td align='text-right' width='1'>$i.</td>
                            <td>".(($question->qtype != QTYPE_LABEL) ? q($question->question_text).'<br>' : $question->question_text).
                            $aType[$question->qtype - 1]."</td>
                            <td class='option-btn-cell'>".action_button(array(
                                array(
                                    'title' => $langEditChange,
                                    'icon' => 'fa-edit',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;editQuestion=$question->pqid"
                                ),
                                array(
                                    'title' => $langDelete,
                                    'icon' => 'fa-times',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;deleteQuestion=$question->pqid",
                                    'class' => 'delete',
                                    'confirm' => $langConfirmYourChoice                                  
                                ),
                                array(
                                    'title' => $langUp,
                                    'icon' => 'fa-arrow-up',
                                    'level' => 'primary',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;moveUp=$question->pqid",
                                    'disabled' => $i==1
                                ),
                                array(
                                    'title' => $langDown,
                                    'icon' => 'fa-arrow-down',
                                    'level' => 'primary',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;moveDown=$question->pqid",
                                    'disabled' => $i==$nbrQuestions                                   
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
