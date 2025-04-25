<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_current_course = true;
require_once '../../include/baseTheme.php';

require_once 'functions.php';
require_once 'modules/group/group_functions.php';

$nameTools = $langAutoJudgeDetailedReport;

if (isset($_GET['assignment']) && isset($_GET['submission'])) {
    $as_id = intval($_GET['assignment']);
    $sub_id = intval($_GET['submission']);
    $assign = get_assignment_details($as_id);
    $sub = get_assignment_submit_details($sub_id);

    if ($sub==null || $assign==null) {
        redirect_to_home_page('modules/work/index.php?course='.$course_code);
    }

    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langWorks);
    $navigation[] = array('url' => "index.php?course=$course_code&amp;id=$as_id", 'name' => q($assign->title));

    if ($sub) {
        if($assign->auto_judge){ // auto_judge enable
            $auto_judge_scenarios = unserialize($assign->auto_judge_scenarios);
            $auto_judge_scenarios_output = unserialize($sub->auto_judge_scenarios_output);

            if (!isset($_GET['downloadpdf'])){
                show_report($as_id, $sub_id, $assign, $sub, $auto_judge_scenarios, $auto_judge_scenarios_output);
                draw($tool_content, 2);
            } else {
                download_pdf_file($assign, $sub, $auto_judge_scenarios, $auto_judge_scenarios_output);
            }
        } else {
            Session::flash('message',$langAutoJudgeNotEnabledForReport);
            Session::flash('alert-class', 'alert-danger');
            draw($tool_content, 2);
        }
    } else {
        Session::flash('message', $langWorkNoSubmission);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
    }

} else {
    redirect_to_home_page('modules/work/index.php?course='.$course_code);
}

// Returns an array of the details of assignment $id
function get_assignment_details($id) {
    global $course_id;
    return Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
}

function get_assignment_submit_details($sid) {
    return Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d",$sid);
}


function get_submission_rank($assign_id,$grade, $submission_date) {
    return Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit WHERE (grade > ?f OR (grade = ?f AND submission_date < ?t)) AND assignment_id = ?d",$grade,$grade, $submission_date,$assign_id)->count+1;
}

/**
 *
 * @global type $course_code
 * @global string $tool_content
 * @global type $langAutoJudgeInput
 * @global type $langAutoJudgeOutput
 * @global type $langAutoJudgeExpectedOutput
 * @global type $langOperator
 * @global type $langAutoJudgeWeight
 * @global type $langAutoJudgeResult
 * @global type $langAutoJudgeResultsFor
 * @global type $langAutoJudgeRank
 * @global type $langAutoJudgeDownloadPdf
 * @global type $langBack
 * @global type $langGradebookGrade
 * @param type $id
 * @param type $sid
 * @param type $assign
 * @param type $sub
 * @param type $auto_judge_scenarios
 * @param type $auto_judge_scenarios_output
 */
function show_report($id, $sid, $assign,$sub, $auto_judge_scenarios, $auto_judge_scenarios_output) {
    global $course_code,$tool_content, $langAutoJudgeInput, $langAutoJudgeOutput,
        $langAutoJudgeExpectedOutput, $langOperator, $langAutoJudgeWeight,
        $langAutoJudgeResult, $langAutoJudgeResultsFor, $langAutoJudgeRank,
        $langAutoJudgeDownloadPdf, $langBack, $langGradebookGrade;

    $tool_content = "
        <div class='col-sm-10'><strong>$langAutoJudgeResultsFor: " . q(uid_to_name($sub->uid)) . "</strong></div>
        <table style='table-layout: fixed; width: 99%' class='table-default'>
        <tr> <td><b>$langGradebookGrade</b>: $sub->grade /$assign->max_grade </td>
             <td><b> $langAutoJudgeRank</b>: ".get_submission_rank($assign->id,$sub->grade, $sub->submission_date)." </td>
        </tr>
          <tr> <td><strong>$langAutoJudgeInput</strong></td>
               <td><strong>$langAutoJudgeOutput</strong></td>
               <td><strong>$langOperator</strong></td>
               <td><strong>$langAutoJudgeExpectedOutput</strong> </td>
               <td><strong>$langAutoJudgeWeight</strong> </td>
               <td><strong>$langAutoJudgeResult</strong> </td>
        </tr>
        ".get_table_content($auto_judge_scenarios, $auto_judge_scenarios_output, $assign->max_grade)."
        </table>
        <p align='left'><a href='work_result_rpt.php?course=".$course_code."&assignment=".$assign->id."&submission=".$sid."&downloadpdf=1'>$langAutoJudgeDownloadPdf</a></p>
        <p align='right'><a href='index.php?course=".$course_code."'>$langBack</a></p>";
}


function get_table_content($auto_judge_scenarios, $auto_judge_scenarios_output, $max_grade) {

    global $langAutoJudgeAssertions;

    $table_content = "";
    $i = 0;
    foreach($auto_judge_scenarios as $cur_senarios) {
        if (!isset($cur_senarios['output'])) { // expected output disable
            $cur_senarios['output'] = '-';
        }
        $icon = ($auto_judge_scenarios_output[$i]['passed']==1) ? "<icon class='fa fa-check'></icon>" : "<icon class='fa-solid fa-xmark'></icon>";
        $table_content .= "
           <tr>
               <td style='word-break:break-all;'>".str_replace(' ', '&nbsp;', $cur_senarios['input'])."</td>
               <td style='word-break:break-all;'>".$auto_judge_scenarios_output[$i]['student_output']."</td>
               <td style='word-break:break-all;'>".$langAutoJudgeAssertions[$cur_senarios['assertion']]."</td>
               <td style='word-break:break-all;'>".str_replace(' ', '&nbsp;', $cur_senarios['output'])."</td>
               <td style='word-break:break-all;'>".$cur_senarios['weight']."/".$max_grade."</td>
               <td>$icon</td></tr>";
        $i++;
    }
    return $table_content;
}

/**
 * @brief download report as pdf file
 * @global type $langAutoJudgeInput
 * @global type $langAutoJudgeOutput
 * @global type $langAutoJudgeExpectedOutput
 * @global type $langOperator
 * @global type $langAutoJudgeWeight
 * @global type $langAutoJudgeResult
 * @global type $langGradebookGrade
 * @global type $langCourse
 * @global type $langAssignment
 * @global type $langStudent
 * @global type $langAutoJudgeRank
 * @global type $course_id
 * @param type $assign
 * @param type $sub
 * @param type $auto_judge_scenarios
 * @param type $auto_judge_scenarios_output
 */
function download_pdf_file($assign, $sub, $auto_judge_scenarios, $auto_judge_scenarios_output) {
    global $langAutoJudgeInput, $langAutoJudgeOutput, $course_id,
        $langAutoJudgeExpectedOutput, $langOperator,
        $langAutoJudgeWeight, $langAutoJudgeResult, $langGradebookGrade,
        $langCourse, $langAssignment, $langStudent, $langAutoJudgeRank;

    $pdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'tempDir' => _MPDF_TEMP_PATH,
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'margin_header' => 0,
        'margin_footer' => 0,
    ]);
    // set document information
    $pdf->SetTitle('Auto Judge Report');
    $pdf->SetSubject('Auto Judge Report');

    $report_table = '
    <style>
    table.first{
        width: 100%;
        border-collapse: collapse;
    }

    td {
        font-size: 0.9em;
        border: 1px solid #95CAFF;
        padding: 3px 7px 2px 7px;
    }

     th {
        font-size: 0.9em;
        text-align: center;
        padding-top: 5px;
        padding-bottom: 4px;
        background-color: #3399FF;
        color: #ffffff;
    }

    </style>
    <table class="first">
        <tr>
            <th>' . $langAutoJudgeInput . '</th>
            <th>' . $langAutoJudgeOutput . '</th>
            <th>' . $langOperator . '</th>
            <th>' . $langAutoJudgeExpectedOutput . '</th>
            <th>' . $langAutoJudgeWeight . '</th>
            <th>' . $langAutoJudgeResult . '</th>
        </tr>
     '. get_table_content($auto_judge_scenarios, $auto_judge_scenarios_output,$assign->max_grade).'
    </table>';

    $report_details ='
    <style>
    table.first{
        width: 100%;
        border-collapse: collapse;
        vertical-align: center;
    }

    td {
        font-size: 1em;
        border: 1px solid #000000;
        padding: 3px 7px 2px 7px;
        text-align: center;
    }

     th {
        font-size: 1.0em;
        text-align: left;
        padding-top: 5px;
        padding-bottom: 4px;
        background-color: #3399FF;
        color: #ffffff;
        width: 120px;
        border: 1px solid #000000;
    }
    </style>

    <table class="first">
      <tr>
        <th>' . $langCourse . '</th><td>' . q(course_id_to_title($course_id)) . '</td>
      </tr>
      <tr>
        <th>' . $langAssignment . '</th><td>' . q($assign->title) . '</td>
      </tr>
      <tr>
        <th>' . $langStudent . '</th><td> '.q(uid_to_name($sub->uid)).'</td>
      </tr>
      <tr>
        <th>' . $langGradebookGrade . '</th><td>' . $sub->grade . '/' . $assign->max_grade . '</td>
      </tr>
      <tr>
        <th>' . $langAutoJudgeRank . '</th><td>' . get_submission_rank($assign->id, $sub->grade, $sub->submission_date) . '</td>
      </tr>
    </table>';

    $pdf->WriteHTML("$report_details $report_table");
    $pdf->Output();
}
