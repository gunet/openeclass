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


$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';

require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'modules/graphics/plotter.php';

$toolName = $langQuestionnaire;
$pageName = $langPollCharts;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langQuestionnaire);

$head_content .= "<script type = 'text/javascript'>
    $(document).ready(function(){
      $('a.trigger_names').click(function(e){
        e.preventDefault();
        var action = $(this).attr('id');
        var field_type = $(this).data('type');
        if (action == 'show') {
            if (field_type == 'multiple') {
                var hidden_field = $(this).parent().next();
                $(this).parent().hide();
                hidden_field.show();              
            } else {
                $(this).closest('tr').siblings('.hidden_row').show('slow');
                $(this).text('$shownone');
                $(this).attr('id', 'hide');
            }
        } else {
            if (field_type == 'multiple') {
                var hidden_field = $(this).parent();
                hidden_field.hide();
                hidden_field.prev().show();
            } else {
                $(this).closest('tr').siblings('.hidden_row').hide('slow');
                $(this).text('$showall');
                $(this).attr('id', 'show');            
            }
        }
      });
    });  
</script>";

if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    redirect_to_home_page();
}
$pid = intval($_GET['pid']);
$thePoll = Database::get()->querySingle("SELECT * FROM poll WHERE course_id = ?d AND pid = ?d ORDER BY pid", $course_id, $pid);
if (!$is_editor && !$thePoll->show_results) {
    Session::Messages($langPollResultsAccess);
    redirect_to_home_page('modules/questionnaire/index.php?course='.$course_code);    
}
if(!$thePoll){
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
$total_participants = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_user_record WHERE pid = ?d", $pid)->total;
if(!$total_participants) {
    redirect_to_home_page("modules/questionnaire/index.php?course=$course_code");
}
$export_box = "";
if ($is_editor) {
    $export_box .= "
        <div class='alert alert-info'>
            <b>$langDumpUserDurationToFile:</b><br>
            <b>$langPollPercentResults:</b> <a href='dumppollresults.php?course=$course_code&amp;pid=$pid'>$langcsvenc2</a>,
               <a href='dumppollresults.php?course=$course_code&amp;enc=1253&amp;pid=$pid'>$langcsvenc1</a><br>
            <b>$langPollFullResults:</b> <a href='dumppollresults.php?course=$course_code&amp;pid=$pid&amp;full=1'>$langcsvenc2</a>,
               <a href='dumppollresults.php?course=$course_code&amp;enc=1253&amp;pid=$pid&amp;full=1'>$langcsvenc1</a>
        </div>";
}
$tool_content .= action_bar(array(
            array(
                'title' => $langBack,
                'url' => "index.php?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            )
        ))."
$export_box
<div class='panel panel-primary'>
    <div class='panel-heading'>
        <h3 class='panel-title'>$langInfoPoll</h3>
    </div>
    <div class='panel-body'>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langTitle:</strong>
            </div>
            <div class='col-sm-9'>
                " . q($thePoll->name) . "
            </div>                
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollCreation:</strong>
            </div>
            <div class='col-sm-9'>
                " . nice_format(date("Y-m-d H:i", strtotime($thePoll->creation_date)), true) . "
            </div>                
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollStart:</strong>
            </div>
            <div class='col-sm-9'>
                " . nice_format(date("Y-m-d H:i", strtotime($thePoll->start_date)), true) . "
            </div>                
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollEnd:</strong>
            </div>
            <div class='col-sm-9'>
                " . nice_format(date("Y-m-d H:i", strtotime($thePoll->end_date)), true) . "
            </div>                
        </div>
        <div class='row  margin-bottom-fat'>
            <div class='col-sm-3'>
                <strong>$langPollTotalAnswers:</strong>
            </div>
            <div class='col-sm-9'>
                $total_participants
            </div>                
        </div>         
    </div>
</div>";

$questions = Database::get()->queryArray("SELECT * FROM poll_question WHERE pid = ?d ORDER BY q_position ASC", $pid);
$j=1;                                                                  
foreach ($questions as $theQuestion) {
    if ($theQuestion->qtype == QTYPE_LABEL) {
        $tool_content .= "<div class='alert alert-info'>$theQuestion->question_text</div>"; 
    } else {
        $tool_content .= "
        <div class='panel panel-success'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$langQuestion $j</h3>
            </div>
            <div class='panel-body'>
                <h4>".q($theQuestion->question_text)."</h4>";

        $j++;

        if ($theQuestion->qtype == QTYPE_MULTIPLE || $theQuestion->qtype == QTYPE_SINGLE) {
            $all_answers = Database::get()->queryArray("SELECT * FROM poll_question_answer WHERE pqid = ?d", $theQuestion->pqid);
            $chart = new Plotter(800, 300);
            foreach ($all_answers as $row) {
                $chart->addPoint(q($row->answer_text), 0);
            }
            if ($theQuestion->qtype == QTYPE_SINGLE) {
                $chart->addPoint($langPollUnknown, 0);
            }
            $answers = Database::get()->queryArray("SELECT a.aid AS aid, b.answer_text AS answer_text, count(a.aid) AS count FROM poll_answer_record a LEFT JOIN poll_question_answer b ON a.aid = b.pqaid WHERE a.qid = ?d GROUP BY a.aid", $theQuestion->pqid);
            $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record WHERE qid= ?d", $theQuestion->pqid)->total;
            $answers_table = "
                <table class='table-default'>
                    <tr>
                        <th>$langAnswer</th>
                        <th>$langSurveyTotalAnswers</th>".(($thePoll->anonymized) ? '' : '<th>' . $langStudents . '</th>')."</tr>";            
            foreach ($answers as $answer) {              
                $percentage = round(100 * ($answer->count / $answer_total),2);
                if(isset($answer->answer_text)){
                    $q_answer = q($answer->answer_text);
                    $aid = $answer->aid;
                } else {
                    $q_answer = $langPollUnknown;
                    $aid = -1;
                }
                $chart->addPoint($q_answer, $percentage);
                if ($thePoll->anonymized != 1) {
                    $names = Database::get()->queryArray("SELECT CONCAT(b.surname, ' ', b.givenname) AS fullname
                            FROM poll_user_record AS a, user AS b
                            WHERE a.id IN (
                                    SELECT poll_user_record_id FROM poll_answer_record WHERE aid = ?d
                                )
                            AND a.uid = b.id
                            UNION
                            SELECT a.email AS fullname
                            FROM poll_user_record a, poll_answer_record b 
                            WHERE b.aid = ?d
                            AND a.email IS NOT NULL
                            AND b.poll_user_record_id = a.id                            
                            ", $aid, $aid);                    
                    foreach($names as $name) {
                      $names_array[] = $name->fullname;
                    }
                    $names_str = implode(', ', $names_array);  
                    $ellipsized_names_str = q(ellipsize($names_str, 60));
                }
                $answers_table .= "
                    <tr>
                            <td>".$q_answer."</td>
                            <td>$answer->count</td>".(($thePoll->anonymized == 1)?'':'<td>'.$ellipsized_names_str.(($ellipsized_names_str != $names_str)? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$showall.'</a>' : '').'</td><td class="hidden_names" style="display:none;">'.q($names_str).' <a href="#" class="trigger_names" data-type="multiple" id="hide">'.$shownone.'</a></td>')."</tr>";     
                unset($names_array);
            }
            $answers_table .= "</table><br>";
            $chart->normalize();
            $tool_content .= $chart->plot();                
            $tool_content .= $answers_table;
        } elseif ($theQuestion->qtype == QTYPE_SCALE) {
            $chart = new Plotter(800, 300);
            for ($i=1;$i<=$theQuestion->q_scale;$i++) {
                $chart->addPoint($i, 0);
            }

            $answers = Database::get()->queryArray("SELECT answer_text, count(answer_text) as count FROM poll_answer_record WHERE qid = ?d GROUP BY answer_text", $theQuestion->pqid);
            $answer_total = Database::get()->querySingle("SELECT COUNT(*) AS total FROM poll_answer_record WHERE qid= ?d", $theQuestion->pqid)->total;

            $answers_table = "
                <table class='table-default'>
                    <tr>
                        <th>$langAnswer</th>
                        <th>$langSurveyTotalAnswers</th>".(($thePoll->anonymized == 1)?'':'<th>'.$langStudents.'</th>')."</tr>";
            foreach ($answers as $answer) {
                $percentage = round(100 * ($answer->count / $answer_total),2);
                $chart->addPoint(q($answer->answer_text), $percentage);
                if ($thePoll->anonymized != 1) {
                    // Gets names for registered users and emails for unregistered
                    $names = Database::get()->queryArray("SELECT CONCAT(b.surname, ' ', b.givenname) AS fullname
                            FROM poll_user_record AS a, user AS b
                            WHERE a.id IN (
                                    SELECT poll_user_record_id FROM poll_answer_record WHERE answer_text = ?s
                                )
                            AND a.uid = b.id
                            UNION
                            SELECT a.email AS fullname
                            FROM poll_user_record a, poll_answer_record b 
                            WHERE b.answer_text = ?s
                            AND a.email IS NOT NULL
                            AND b.poll_user_record_id = a.id                            
                            ", $answer->answer_text, $answer->answer_text);
                    
                    foreach($names as $name) {
                      $names_array[] = $name->fullname;
                    }
                    $names_str = implode(', ', $names_array);  
                    $ellipsized_names_str = q(ellipsize($names_str, 60));
                }
                $answers_table .= "
                    <tr>
                        <td>".q($answer->answer_text)."</td>
                        <td>$answer->count</td>"
                        . (($thePoll->anonymized == 1) ? 
                        '' :
                        '<td>'.$ellipsized_names_str.
                            (($ellipsized_names_str != $names_str)? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$showall.'</a>' : '').
                        '</td>
                        <td class="hidden_names" style="display:none;">'
                            . q($names_str) .
                            ' <a href="#" class="trigger_names" data-type="multiple" id="hide">'.$shownone.'</a>
                        </td>').
                    "</tr>";     
                unset($names_array);                
            }
            $answers_table .= "</table>";
            $chart->normalize();
            $tool_content .= $chart->plot();            
            $tool_content .= $answers_table;
        } elseif ($theQuestion->qtype == QTYPE_FILL) {
            $answers = Database::get()->queryArray("SELECT COUNT(arid) AS count, answer_text FROM poll_answer_record
                                        WHERE qid = ?d GROUP BY answer_text", $theQuestion->pqid);                   
            $tool_content .= "<table class='table-default'>
                    <tbody>
                    <tr>
                            <th>$langAnswer</th>
                            <th>$langSurveyTotalAnswers</th>
                            ".(($thePoll->anonymized == 1)?'':'<th>'.$langStudents.'</th>')."    
                    </tr>";
            $k=1;
            foreach ($answers as $answer) {             
                if (!$thePoll->anonymized) {
                    // Gets names for registered users and emails for unregistered
                    $names = Database::get()->queryArray("SELECT CONCAT(b.surname, ' ', b.givenname) AS fullname
                            FROM poll_user_record AS a, user AS b
                            WHERE a.id IN (
                                    SELECT poll_user_record_id FROM poll_answer_record WHERE answer_text = ?s
                                )
                            AND a.uid = b.id
                            UNION
                            SELECT a.email AS fullname
                            FROM poll_user_record a, poll_answer_record b 
                            WHERE b.answer_text = ?s
                            AND a.email IS NOT NULL
                            AND b.poll_user_record_id = a.id                            
                            ", $answer->answer_text, $answer->answer_text);                    
                    foreach($names as $name) {
                      $names_array[] = $name->fullname;
                    }
                    $names_str = implode(', ', $names_array);  
                    $ellipsized_names_str = q(ellipsize($names_str, 60));
                }
                $row_class = ($k>3) ? 'class="hidden_row" style="display:none;"' : '';
                $extra_column = !$thePoll->anonymized ? 
                        "<td>"
                        . $ellipsized_names_str
                        . (($ellipsized_names_str != $names_str) ? ' <a href="#" class="trigger_names" data-type="multiple" id="show">'.$showall.'</a>' : '').
                        "</td>
                        <td class='hidden_names' style='display:none;'>'
                           . q($names_str) .
                           ' <a href='#' class='trigger_names' data-type='multiple' id='hide'>'.$shownone.'</a>
                       </td>" : "";                       
                $tool_content .= "
                <tr $row_class>
                        <td>".q($answer->answer_text)."</td>
                        <td>$answer->count</td>
                        $extra_column
                </tr>";                               
                $k++;
                if (!$thePoll->anonymized) unset($names_array);
            }
            if ($k>4) {
             $tool_content .= "
                <tr>
                        <td colspan='".($thePoll->anonymized ? 2 : 3)."'><a href='#' class='trigger_names' data-type='fill' id='show'>$showall</a></td>
                </tr>";                       
            }             

            $tool_content .= '</tbody></table><br>';
        }
        $tool_content .= "</div></div>"; 
    }
}
// display page
draw($tool_content, 2, null, $head_content);
