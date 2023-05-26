<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
$helpTopic = 'questionnaire';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

load_js('tools.js');

$toolName = $langQuestionnaire;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);

if (isset($_REQUEST['pid'])) {
    $pid = intval($_REQUEST['pid']);
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['assign_type'])) {
        if ($_POST['assign_type'] == 2) {
            $data = Database::get()->queryArray("SELECT name, id FROM `group` 
                                WHERE course_id = ?d 
                                  AND `group`.visible = 1   
                                ORDER BY name", $course_id);
        } elseif ($_POST['assign_type'] == 1) {
            $data = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                    FROM user, course_user
                                    WHERE user.id = course_user.user_id
                                        AND course_user.course_id = ?d 
                                        AND course_user.status = " . USER_STUDENT . "
                                        AND user.id 
                                    ORDER BY surname", $course_id);
        }
        echo json_encode($data);
    }
    if (isset($_POST['toReorder'])) {
        reorder_table('poll_question', 'pid', $pid, $_POST['toReorder'],
            isset($_POST['prevReorder'])? $_POST['prevReorder']: null, 'pqid', 'q_position');
    }
    exit;
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
        $PollAssignToSpecific = $_POST['assign_to_specific'];
        $PollAssignees = filter_input(INPUT_POST, 'ingroup', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
        $PollSurveyType = $_POST['survey_type'];
        if(isset($pid)) {
            Database::get()->query("UPDATE poll SET name = ?s,
                    start_date = ?t, end_date = ?t, description = ?s,
                    end_message = ?s, anonymized = ?d, show_results = ?d,
                    type = ?d, assign_to_specific = ?d
                    WHERE course_id = ?d AND pid = ?d",
                    $PollName, $PollStart, $PollEnd, $PollDescription, $PollEndMessage, $PollAnonymized, $PollShowResults, $PollSurveyType, $PollAssignToSpecific, $course_id, $pid);
            Database::get()->query("DELETE FROM poll_to_specific WHERE poll_id = ?d", $pid);
            //Session::Messages($langPollEdited, 'alert-success');
            Session::flash('message',$langPollEdited);
            Session::flash('alert-class', 'alert-success');
        } else {
            $PollActive = 1;
            $pid = Database::get()->query("INSERT INTO poll
                        (course_id, creator_id, name, creation_date, start_date, end_date, active, description, end_message, anonymized, show_results,type, assign_to_specific)
                        VALUES (?d, ?d, ?s, NOW(), ?t, ?t, ?d, ?s, ?s, ?d, ?d, ?d, ?d)", $course_id, $uid, $PollName, $PollStart, $PollEnd, $PollActive, $PollDescription, $PollEndMessage, $PollAnonymized, $PollShowResults, $PollSurveyType, $PollAssignToSpecific)->lastInsertID;

            if ($PollSurveyType == POLL_COLLES) {
                createcolles($pid);
            }   elseif($PollSurveyType == POLL_ATTLS) {
                createattls($pid);
            }
            //Session::Messages($langPollCreated, 'alert-success');
            Session::flash('message',$langPollCreated);
            Session::flash('alert-class', 'alert-success');
        }
        if ($PollAssignToSpecific && !empty($PollAssignees)) {
            if ($PollAssignToSpecific == 1) {
                foreach ($PollAssignees as $assignee_id) {
                    Database::get()->query("INSERT INTO poll_to_specific (user_id, poll_id) VALUES (?d, ?d)", $assignee_id, $pid);
                }
            } else {
                foreach ($PollAssignees as $group_id) {
                    Database::get()->query("INSERT INTO poll_to_specific (group_id, poll_id) VALUES (?d, ?d)", $group_id, $pid);
                }
            }
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
    $v->rule('required', 'questionName');
    if (isset($_POST['questionScale']) and $_POST['questionScale']) {
        $v->rule('required', 'questionScale');
        $v->rule('integer', 'questionScale');
        $v->rule('min', 'questionScale', 1);
        $v->labels(array(
            'questionName' => "$langTheField $langQuestion",
            'questionScale' => "$langTheField $langScale"
        ));
    }
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
            redirect_to_home_page("modules/questionnaire/admin.php?course=$course_code&pid=$pid");
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
    $attempt_counter = Database::get()->querySingle("SELECT COUNT(*) AS count FROM poll_user_record WHERE pid = ?d", $pid)->count;
    if ($attempt_counter>0) {
        //Session::Messages($langThereAreParticipants);
        Session::flash('message',$langThereAreParticipants);
        Session::flash('alert-class', 'alert-warning');
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
            $('#PollStart, #PollEnd').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
            $('#assign_button_all').click(hideAssignees);
            $('#assign_button_user, #assign_button_group').click(ajaxAssignees);
        });
        function ajaxAssignees()
        {
            $('#assignees_tbl').removeClass('hide');
            var type = $(this).val();
            $.post('',
            {
              assign_type: type
            },
            function(data,status){
                var index;
                var parsed_data = JSON.parse(data);
                var select_content = '';
                if(type==1){
                    for (index = 0; index < parsed_data.length; ++index) {
                        select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['surname'] + ' ' + parsed_data[index]['givenname'] + '<\/option>';
                    }
                } else {
                    for (index = 0; index < parsed_data.length; ++index) {
                        select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + parsed_data[index]['name'] + '<\/option>';
                    }
                }
                $('#assignee_box').find('option').remove();
                $('#assign_box').find('option').remove().end().append(select_content);
            });
        }
        function hideAssignees()
        {
            $('#assignees_tbl').addClass('hide');
            $('#assignee_box').find('option').remove();
        }
    </script>";

        if (isset($poll) && $poll->assign_to_specific) {
            //preparing options in select boxes for assigning to specific users/groups
            $assignee_options='';
            $unassigned_options='';
            if ($poll->assign_to_specific == 2) {
                $assignees = Database::get()->queryArray("SELECT `group`.id AS id, `group`.name
                                       FROM poll_to_specific, `group`
                                       WHERE `group`.id = poll_to_specific.group_id
                                       AND `group`.visible = 1 
                                       AND `group`.course_id = ?d
                                       AND poll_to_specific.poll_id = ?d", $course_id, $poll->pid, );
                $all_groups = Database::get()->queryArray("SELECT name, id FROM `group` 
                                        WHERE course_id = ?d AND `group`.visible = 1", $course_id);
                foreach ($assignees as $assignee_row) {
                    $assignee_options .= "<option value='".$assignee_row->id."'>".$assignee_row->name."</option>";
                }
                $unassigned = array_udiff($all_groups, $assignees,
                  function ($obj_a, $obj_b) {
                    return $obj_a->id - $obj_b->id;
                  }
                );
                foreach ($unassigned as $unassigned_row) {
                    $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->name</option>";
                }

            } else {
                $assignees = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                       FROM poll_to_specific, user
                                       WHERE user.id = poll_to_specific.user_id AND poll_to_specific.poll_id = ?d", $poll->pid);
                $all_users = Database::get()->queryArray("SELECT user.id AS id, user.givenname, user.surname
                                        FROM user, course_user
                                        WHERE user.id = course_user.user_id
                                        AND course_user.course_id = ?d AND course_user.status = " . USER_STUDENT . "
                                        AND user.id", $course_id);
                foreach ($assignees as $assignee_row) {
                    $assignee_options .= "<option value='$assignee_row->id'>$assignee_row->surname $assignee_row->givenname</option>";
                }
                $unassigned = array_udiff($all_users, $assignees,
                  function ($obj_a, $obj_b) {
                    return $obj_a->id - $obj_b->id;
                  }
                );
                foreach ($unassigned as $unassigned_row) {
                    $unassigned_options .= "<option value='$unassigned_row->id'>$unassigned_row->surname $unassigned_row->givenname</option>";
                }
            }
        }

    $PollName = Session::has('PollName') ? Session::get('PollName') : (isset($poll) ? $poll->name : '');
    $PollDescription = Session::has('PollDescription') ? Session::get('PollDescription') : (isset($poll) ? $poll->description : '');
    $PollEndMessage = Session::has('PollEndMessage') ? Session::get('PollEndMessage') : (isset($poll) ? $poll->end_message : '');
    $PollStart = Session::has('PollStart') ? Session::get('PollStart') : date('d-m-Y H:i', (isset($poll) ? strtotime($poll->start_date) : strtotime('now')));
    $PollEnd = Session::has('PollEnd') ? Session::get('PollEnd') : date('d-m-Y H:i', (isset($poll) ? strtotime($poll->end_date) : strtotime('now +1 year')));
    $PollAssignToSpecific = Session::has('assign_to_specific') ? Session::get('assign_to_specific') : (isset($poll) ? $poll->assign_to_specific : 0);
    $PollSurveyType = Session::has('PollType') ? Session::get('PollType') : (isset($poll) ? $poll->type : '');

    $link_back = isset($_GET['modifyPoll']) ? "admin.php?course=$course_code&amp;pid=$pid" : "index.php?course=$course_code";
    $pageName = isset($_GET['modifyPoll']) ? "$langEditPoll" : "$langCreatePoll";
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'level' => 'primary-label',
              'url' => $link_back,
              'icon' => 'fa-reply')));
    $tool_content .= "

    <div class='col-12'>

    <div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code".(isset($_GET['modifyPoll']) ? "&amp;pid=$pid&amp;modifyPoll=yes" : "&amp;newPoll=yes")."' method='post'>            
            <div class='form-group ".(Session::getError('PollName') ? "has-error" : "")."'>
              <label for='PollName' class='col-sm-6 control-label-notes'>$langTitle</label>
              <div class='col-sm-12'>
                <input type='text' class='form-control' id='PollName' name='PollName' placeholder='$langTitle' value='$PollName'>
                <span class='help-block'>".Session::getError('PollName')."</span>
              </div>
            </div>

            
            <div class='row'>
                <div class='col-md-6 col-12'>
                    <div class='input-append date form-group".(Session::getError('PollStart') ? " has-error" : "")." mt-4' id='startdatepicker' data-date='$PollStart' data-date-format='dd-mm-yyyy'>
                        <label for='PollStart' class='col-sm-12 control-label-notes mb-1'>$langPollStart</label>
                        
                        <div class='input-group'>
                                <input class='form-control mt-0' name='PollStart' id='PollStart' type='text' value='$PollStart'>
                                <span class='help-block'>".Session::getError('PollStart')."</span>
                                <span class='add-on input-group-text h-30px border-0 BordersRightInput bgEclass'><i class='fa fa-calendar'></i></span>
                        </div>
                            
                        
                    </div>
                </div>

            
                <div class='col-md-6 col-12'>
                    <div class='input-append date form-group".(Session::getError('PollEnd') ? " has-error" : "")." mt-4' id='enddatepicker' data-date='$PollEnd' data-date-format='dd-mm-yyyy'>
                        <label for='PollEnd' class='col-sm-6 control-label-notes mb-1'>$langPollEnd</label>
                        
                        <div class='input-group'>
                            <input class='form-control mt-0' name='PollEnd' id='PollEnd' type='text' value='$PollEnd'>
                            <span class='help-block'>".Session::getError('PollEnd')."</span>
                            <span class='add-on input-group-text h-30px border-0 BordersRightInput bgEclass'><i class='fa fa-calendar'></i></span>
                        </div>
                    </div>
                </div>
            </div>

           

            <div class='form-group mt-4'>
                <label class='col-sm-6 control-label-notes mb-2'>$langResults</label>
                <div class='col-sm-12'>
                    <div class='checkbox mb-2'>
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

       


            <div class='form-group mt-4'>
              <label for='PollDescription' class='col-sm-6 control-label-notes'>$langDescription</label>
              <div class='col-sm-12'>
                ".rich_text_editor('PollDescription', 4, 52, $PollDescription)."
              </div>
            </div>

            
            
            <div class='form-group mt-4'>
              <label for='PollEndMessage' class='col-sm-6 control-label-notes'>$langPollEndMessage</label>
              <div class='col-sm-12'>
                ".rich_text_editor('PollEndMessage', 4, 52, $PollEndMessage)."
              </div>
            </div>

            


            <div class='form-group mt-4'>
                <label class='col-sm-6 control-label-notes mb-2'>$m[WorkAssignTo]</label>
                <div class='col-sm-12'>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' id='assign_button_all' name='assign_to_specific' value='0'".($PollAssignToSpecific == 0 ? " checked" : "").">
                        <span>$m[WorkToAllUsers]</span>
                      </label>
                    </div>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' id='assign_button_user' name='assign_to_specific' value='1'".($PollAssignToSpecific == 1 ? " checked" : "").">
                        <span>$m[WorkToUser]</span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' id='assign_button_group' name='assign_to_specific' value='2'".($PollAssignToSpecific == 2 ? " checked" : "").">
                        <span>$m[WorkToGroup]</span>
                      </label>
                    </div>
                </div>
            </div>

           

            <div class='form-group mt-4'>
                <div class='col-sm-12 col-sm-offset-2'>
                    <div class='table-responsive'>
                        <table id='assignees_tbl' class='table-default".(isset($poll) && in_array($poll->assign_to_specific, [1, 2]) ? '' : ' hide')."'>
                            <tr class='title1 list-header'>
                              <td id='assignees'>$langStudents</td>
                              <td class='text-center'>$langMove</td>
                              <td>$m[WorkAssignTo]</td>
                            </tr>
                            <tr>
                              <td>
                                <select class='form-select h-100 rounded-0' id='assign_box' size='10' multiple>
                                ".((isset($unassigned_options)) ? $unassigned_options : '')."
                                </select>
                              </td>
                              <td class='text-center'>
                                <input class='btn-outline-primary h-30px rounded-pill' type='button' onClick=\"move('assign_box','assignee_box')\" value='   &gt;&gt;   ' /><br /><input class='btn-outline-primary h-30px rounded-pill mt-3' type='button' onClick=\"move('assignee_box','assign_box')\" value='   &lt;&lt;   ' />
                              </td>
                              <td width='40%'>
                                <select class='form-select h-100 rounded-0' id='assignee_box' name='ingroup[]' size='10' multiple>
                                ".((isset($assignee_options)) ? $assignee_options : '')."
                                </select>
                              </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            


            <div class='form-group mt-4'>
                <label class='col-sm-6 control-label-notes mb-2'>$langType</label>
                <div class='col-sm-12'>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' id='general_type' name='survey_type' value='0'".($PollSurveyType == POLL_NORMAL ? " checked" : "").">
                        <span>$langGeneralSurvey </span>
                      </label>
                    </div>
                    <div class='radio mb-2'>
                      <label>
                        <input class='input-Questionnaire' type='radio' id='colles_type' name='survey_type' value='1'".($PollSurveyType == POLL_COLLES ? " checked" : "").">
                        <label for='colles_type'>   
                            <span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='bottom' title='$colles_desc'></span>
                            <span>$langCollesSurvey</span>
                        </label>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                         <input class='input-Questionnaire' type='radio' id='attls_type' name='survey_type' value='2'".($PollSurveyType == POLL_ATTLS ? " checked" : "").">
                         <label for='attls_type'>   
                            <span>$langATTLSSurvey</span>
                            <span class='fa fa-info-circle' data-bs-toggle='tooltip' data-bs-placement='bottom' title='$rate_scale'></span>
                        </label>
                      </label>
                    </div>
                </div>
            </div>

           


            <div class='form-group mt-5'>
              <div class='col-12 d-flex justify-content-center align-items-center'>
                
                  
                     "
                     .
                                 form_buttons(array(
                                     array(
                                         'class' => 'submitAdminBtn',
                                         'text'  => $langSave,
                                         'name'  => 'submitPoll',
                                         'value' => (isset($_GET['newPoll']) ? $langCreate : $langModify),
                                         'javascript' => "selectAll('assignee_box',true)"
                                     ),
                                     array(
                                        'class' => 'cancelAdminBtn ms-1',
                                        'href' => "index.php?course=$course_code",
                                    )
                                 ))
                                 ."
                   
                   
               
              </div>
            </div>        
        </form>
    </div></div>";
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
        $pageName = $langModify;
        if (($question->qtype != QTYPE_LABEL) and ($question->qtype != QTYPE_FILL) and ($question->qtype != QTYPE_SCALE)) {
            $navigation[] = array(
                'url' => "admin.php?course=$course_code&amp;pid=$pid&amp;modifyAnswers=$question->pqid",
                'name' => $langPollManagement
            );
        }

    } else {
        $pageName = $langNewQu;
    }

    $action_url = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid".(isset($_GET['modifyQuestion']) ? "&amp;modifyQuestion=$question->pqid" : "&amp;newQuestion=yes");
    $action_url .= isset($_GET['questionType']) ?  '&amp;questionType=label' : '';
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'url' => "admin.php?course=$course_code&amp;pid=$pid",
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

    $tool_content .= "
    <div class='col-12'>
    <div class='form-wrapper form-edit rounded'><form class='form-horizontal' role='form' action='$action_url' method='post'>    
            <div class='form-group $questionNameErrorClass'>
                <label for='questionName' class='col-sm-6 control-label-notes'>".(isset($_GET['questionType']) ? $langLabel : $langQuestion)."</label>
                <div class='col-sm-12'>
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
        
            <div class='form-group mt-4'>
                <label for='answerType' class='col-sm-6 control-label-notes mb-2'>$langType</label>
                <div class='col-sm-12'>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' name='answerType' class='answerType' value='1' value='".QTYPE_SINGLE."' ".($answerType == QTYPE_SINGLE || !isset($question) ? 'checked' : '').">
                        ". $aType[QTYPE_SINGLE - 1] . "
                      </label>
                    </div>
                    <div class='radio mb-2'>
                      <label>
                        <input type='radio' name='answerType' class='answerType' value='".QTYPE_MULTIPLE."' ".($answerType == QTYPE_MULTIPLE ? 'checked' : '').">
                        ". $aType[QTYPE_MULTIPLE - 1] . "
                      </label>
                    </div>
                    <div class='radio mb-2'>
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

            


            <div class='form-group$questionScaleErrorClass$questionScaleShowHide mt-4'>
                <label for='questionScale' class='col-sm-6 control-label-notes'>$langMax $langScale (1-..):</label>
                <div class='col-12'>
                    <input type='text' class='form-control' name='questionScale' id='questionScale' value='".q($questionScale)."'>
                    <span class='help-block'>$questionScaleError</span>
                </div>
            </div>";
    }
    $tool_content .= "

   

            <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-center align-items-center'>
                  
                     
                         ".
                         form_buttons(array(
                             array(
                                 'class' => 'submitAdminBtn',
                                 'text'  => $langSave,
                                 'name'  => 'submitQuestion',
                                 'value' => (isset($_GET['newQuestion']) ? $langCreate : $langModify)
                             ),
                             array(
                                'class' => 'cancelAdminBtn ms-1',
                                'href' => "admin.php?course=$course_code&pid=$pid".(isset($_GET['modifyQuestion']) ? "&modifyAnswers=".$_GET['modifyQuestion'] : "")
                             )
                         ))
                     ."
                     
                     
                   
                </div>
            </div>
    </form></div></div>";

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
        'url' => "admin.php?course=$course_code&amp;pid=$pid",
        'name' => $langPollManagement
    );
    $tool_content .= "

 
    <div class='col-12 mt-3'>
        <div class='card panelCard px-lg-4 py-lg-3'>
            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                <div class='text-uppercase normalColorBlueText TextBold fs-6'>$langQuestion&nbsp;"
                    . icon('fa-edit text-white', $langEditChange, $_SERVER['SCRIPT_NAME']."?course=$course_code&pid=$pid&modifyQuestion=$question->pqid") .
                "</div>
            </div>
            <div class='card-body'>
                  <h6>$question->question_text<br><small><em>".$aType[$question->qtype - 1]."</em></small></h6>
            </div>
        </div>
    </div>";

    $tool_content .= "
    
    <div class='col-12 mt-3'>
        <div class='card panelCard px-lg-4 py-lg-3'>
            <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                <div class='text-uppercase normalColorBlueText TextBold fs-6'>$langQuestionAnswers</div>
            </div>
            <div class='card-body'>
                    <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;modifyAnswers=$question_id' method='post'>                    
                    
                    <div class='form-group'>
                       
                            
                            <div class='col-9 control-label-notes d-inline-flex'>
                               <span class='mt-1 me-1'>$langPollAddAnswer: </span><input class='btn submitAdminBtn' type='submit' name='MoreAnswers' value='+'>
                            </div>
                       
                    </div><hr><br>";
        if (count($answers) > 0) {
            foreach ($answers as $answer) {
              $tool_content .="
             

                  <div class='form-group input-group mt-3'>
                        
                        <input type='text' class='form-control mt-0' name='answers[]' value='$answer->answer_text'>
                        
                        <div class='form-control-static input-group-text h-30px border-0 BordersRightInput bgEclass'>
                            " . icon('fa-times', $langDelete, '#', ' class="del_btn"') . "
                        </div>
                    </div>";
              }
        } else {
            $tool_content .="
                    
                    <div class='form-group input-group mt-3'>
                       
                            <input class='form-control mt-0' type='text' name='answers[]' value=''>
                     
                            <div class='form-control-static input-group-text h-30px border-0 BordersRightInput bgEclass'>
                                " . icon('fa-times', $langDelete, '#', ' class="del_btn"') . "
                            </div>
                    </div>

                   
              
                  <div class='form-group input-group mt-3'>
                       
                        <input class='form-control mt-0' type='text' name='answers[]' value=''>
                      
                        <div class='form-control-static input-group-text h-30px border-0 BordersRightInput bgEclass'>
                            " . icon('fa-times', $langDelete, '#', ' class="del_btn"') . "
                        </div>
                    </div>";
        }
        $tool_content .= "
       
                    <div class='form-group mt-4'>
                        <div class='col-12 d-flex justify-content-center align-items-center'>
                            
                               
                                 <input class='btn submitAdminBtn' type='submit' name='submitAnswers' value='$langCreate'>
                              
                              
                                 <a class='btn cancelAdminBtn ms-1' href='admin.php?course=$course_code&amp;pid=$pid'>$langCancel</a>
                              
                          
                            
                            
                        </div>
                    </div>                    
                    </form>
            </div>
        </div>
    </div>";
// View edit poll page
} else {

    $pageName = $langEditChange;
    $navigation[] = array('url' => "admin.php?course=$course_code&amp;pid=$pid", 'name' => $poll->name);

    if ($poll->type == 0) {
        $poll_type = $langGeneralSurvey;
    } else if($poll->type == 1) {
        $poll_type = $langCollesSurvey." $langSurvey";
    } else if($poll->type == 2) {
        $poll_type = $langATTLSSurvey." $langSurvey";
    }
    $questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position", $pid);

    $tool_content .= action_bar(array(
        array('title' => $langSee,
            'level' => 'primary-label',
            'button-class' => 'btn-danger',
            'url' => "pollparticipate.php?course=$course_code&amp;UseCase=1&amp;pid=$pid",
            'icon' => 'fa-play-circle'),
        array('title' => $langBack,
              'level' => 'primary-label',
              'url' => "index.php?course=$course_code",
              'icon' => 'fa-reply')
        ));

    $tool_content .= "

    <div class='col-12 mb-3'>
        <div class='card panelCard px-lg-4 py-lg-3'>
          <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
            <div class='text-uppercase normalColorBlueText TextBold fs-6'>$langInfoPoll &nbsp;".icon('fa-edit', $langEditPoll, "admin.php?course=$course_code&amp;pid=$pid&amp;modifyPoll=yes")."</div>
          </div>

          <div class='card-body'>
            <div class='row p-2 margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong class='control-label-notes'>$langTitle:</strong>
                </div>
                <div class='col-sm-9'>
                    " . q($poll->name) . "
                </div>
            </div>

            <div class='row p-2 margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong class='control-label-notes'>$langPollStart:</strong>
                </div>
                <div class='col-sm-9'>
                    ". format_locale_date(strtotime($poll->start_date)) ."
                </div>
            </div>

            <div class='row p-2 margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong class='control-label-notes'>$langPollEnd:</strong>
                </div>
                <div class='col-sm-9'>
                    ". format_locale_date(strtotime($poll->end_date)) ."
                </div>
            </div>

            <div class='row p-2 margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong class='control-label-notes'>$langResults:</strong>
                </div>
                <div class='col-sm-9'>
                    ".(($poll->anonymized) ? icon('fa-check-square-o') : icon('fa-square-o'))." $langPollAnonymize <br>
                    ".(($poll->show_results) ? icon('fa-check-square-o') : icon('fa-square-o'))." $langPollShowResults
                </div>
            </div>

            <div class='row p-2 margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong class='control-label-notes'>$langType:</strong>
                </div>
                <div class='col-sm-9'>
                    $poll_type
                </div>
            </div>

            <div class='row p-2 margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong class='control-label-notes'>$langDescription:</strong>
                </div>
                <div class='col-sm-9'>
                    " . standard_text_escape($poll->description) . "
                </div>
            </div>

            <div class='row p-2 margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong class='control-label-notes'>$langPollEndMessage:</strong>
                </div>
                <div class='col-sm-9'>
                    " . standard_text_escape($poll->end_message) . "
                </div>
            </div>
          </div>
        </div>
    </div>
    ";

    if($poll->type == 0) {
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

            load_js('sortable/Sortable.min.js');

            $head_content .= "<script>
                $(document).ready(function(){
                    Sortable.create(pollAnswers,{
                        handle: '.fa-arrows',
                        animation: 150,
                        onEnd: function (evt) {
                            var itemEl = $(evt.item);
                            var idReorder = itemEl.attr('data-id');
                            var prevIdReorder = itemEl.prev().attr('data-id');
                            $.ajax({
                              type: 'post',
                              dataType: 'text',
                              data: {
                                    toReorder: idReorder,
                                    prevReorder: prevIdReorder,
                                }
                            });
                        }
                    });
                });
            </script>";

            $tool_content .= "
            <div class='col-12'>
            <table class='table-default'>
                        <tbody id='pollAnswers'>
                            <tr class='list-header'>
                              <th colspan='2'>$langQuesList</th>
                              <th class='text-center'>".icon('fa-gears', $langCommands)."</th>
                            </tr>";
            $i=1;
            $nbrQuestions = count($questions);
            foreach ($questions as $question) {
                $tool_content .= "<tr class='even' data-id='$question->pqid'>
                                <td align='text-end' width='1'>$i.</td>
                                <td>".(($question->qtype != QTYPE_LABEL) ? q($question->question_text).'<br>' : $question->question_text).
                                $aType[$question->qtype - 1]."</td>
                                <td style='padding: 10px 0; width: 85px;'>
                                    <div class='reorder-btn pull-left mb-3' style='padding:5px 10px 0; font-size: 16px; cursor: pointer; vertical-align: bottom;'>
                                            <span class='fa fa-arrows' data-bs-toggle='tooltip' data-bs-placement='top' title='$langReorder'></span>
                                    </div>
                                <div class='pull-left'>".action_button(array(
                                    array(
                                        'title' => $langEditChange,
                                        'icon' => 'fa-edit',
                                        'url' => (($question->qtype != QTYPE_LABEL) and ($question->qtype != QTYPE_FILL) and ($question->qtype != QTYPE_SCALE))?
                                                        "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;modifyAnswers=$question->pqid" :
                                                        "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;modifyQuestion=$question->pqid",
                                    ),
                                    array(
                                        'title' => $langDelete,
                                        'icon' => 'fa-times',
                                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;pid=$pid&amp;deleteQuestion=$question->pqid",
                                        'class' => 'delete',
                                        'confirm' => $langConfirmYourChoice
                                    )
                                ))."</div></td></tr>";
                $i++;
            }
            $tool_content .= "</tbody></table></div>";
        } else {
            $tool_content .= "<div class='col-12'><div class='alert alert-warning'>$langPollEmpty</div></div>";
        }
    } elseif ($poll->type==1) {
        $tool_content .= "<div class='col-12'><div class='alert alert-info Borders' role='alert'>$colles_desc</div></div>";
    } elseif ($poll->type==2) {
        $tool_content .= "<div class='col-12'><div class='alert alert-info Borders' role='alert'>$rate_scale</div></div>";
    }
}
draw($tool_content, 2, null, $head_content);
