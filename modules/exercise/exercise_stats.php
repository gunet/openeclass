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

$require_current_course = TRUE;
$require_editor = true;

include '../../include/baseTheme.php';
include 'exercise.class.php';
include 'question.class.php';

$exerciseId = $_GET['exerciseId'];
$objExercise = new Exercise();
$found = $objExercise->read($exerciseId);
if (!$found) { // exercise not found
    Session::flash('message',$langExerciseNotFound);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

$toolName = $langUsage;
$pageName = $objExercise->selectTitle();
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

$completedAttempts = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_COMPLETED)->count;
$pausedAttempts = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_PAUSED)->count;
$pendingAttempts = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_PENDING)->count;
$cancelledAttempts = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_CANCELED)->count;
$total_attempts = $completedAttempts + $pausedAttempts + $pendingAttempts + $cancelledAttempts;

$grade_stats = Database::get()->querySingle("SELECT COUNT(DISTINCT uid) AS unique_users, 
                                                ROUND(AVG(TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date))),1) AS avg_time, 
                                                ROUND(AVG(total_score),2) AS avg_grade, 
                                                MIN(total_score) AS min_grade, 
                                                MAX(total_score) AS max_grade 
                                            FROM exercise_user_record WHERE eid = ?d 
                                                AND attempt_status = ?d", $exerciseId, ATTEMPT_COMPLETED);
$max_grade = $grade_stats->max_grade;
$min_grade = $grade_stats->min_grade;
$avg_grade = $grade_stats->avg_grade;
$avg_time = $grade_stats->avg_time;
$unique_users = $grade_stats->unique_users;

//average number of attempts
//avg completion time

$tool_content .= "
    <div class='table-responsive'>
        <table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th>$langAttempts</th>
                    <th aria-label='$langAttempts'></th>
                    <th aria-label='$langAttempts'></th>
                    <th aria-label='$langAttempts'></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>$langAttemptsCompleted</th>
                    <td>$completedAttempts</td>
                    <td>$langAttemptsPaused</th>
                    <td>$pausedAttempts</td>                        
                </tr>
                <tr>
                    <td>$langAttemptPending</th>
                    <td>$pendingAttempts</td>
                    <td>$langAttemptsCanceled</th>
                    <td>$cancelledAttempts</td>                        
                </tr>
            </tbody>
            <tfoot>
                <tr class='active user-details-exec mt-2'>
                    <th class='form-label px-2'>$langTotal</th>
                    <th class='form-label px-2'>$total_attempts</th>
                    <th aria-label='$langAttempts'></th>
                    <th aria-label='$langAttempts'></th>
                </tr>            
            </tfoot>
        </table>
    </div>
    <div class='table-responsive mt-4'>
        <table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th>$langScore</th>
                    <th aria-label='$langGradebookGrade'></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>$langHighestGrade</th>
                    <td>$max_grade</td>                    
                </tr>
                <tr>
                    <td>$langLowestGrade</th>
                    <td>$min_grade</td>                   
                </tr>
                <tr>
                    <td>$langRatingAverage</th>
                    <td>$avg_grade</td>                   
                </tr>              
            </tbody>
        </table>
    </div>
    <div class='table-responsive mt-4'>
        <table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th>$langStudents</th>
                    <th aria-label='$langDuration'></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>$langStudentsExerciseCompleted</th>
                    <td>$unique_users</td>                   
                </tr>              
                <tr>
                    <td>$langAverage $langExerciseDuration</th>
                    <td>".format_time_duration($avg_time)."</td>                   
                </tr>                
            </tbody>
        </table>
    </div>";
//Questions Table
$questionList = $objExercise->selectQuestionList();
$tool_content .= "


    <div class='col-12 mt-4'>
    <div class='card panelCard card-default px-lg-4 py-lg-3'>
    <div class='card-header border-0 d-flex justify-content-between align-items-center'><h3>$langQuestions</h3></div>
    <div class='card-body'>

    <div class='table-responsive mt-0'>
        <table class='table-default'>
            <thead>
                <tr class='list-header'>
                    <th>$langTitle</th>
                    <th>$langSuccessPercentage</th>
                </tr>
            </thead>
            <tbody>";

foreach($questionList as $id) {
    $objQuestionTmp = new Question();
    if (!is_array($id)) {
        $objQuestionTmp->read($id);
    }
    if (is_array($id)) { // placeholder for random questions (if any)
        if ($id['criteria'] == 'difficulty') {
            next($id);
            $number = key($id);
            $difficulty = $id[$number];
            $tool_content .= "<tr><td>";
            $tool_content .= "<span class='fa fa-random' style='margin-right:10px; color: grey'></span><em>$number $langFromRandomDifficultyQuestions '" . $objQuestionTmp->selectDifficultyLegend($difficulty) . "'</em>";
            $tool_content .= "</td></tr>";
        } else if ($id['criteria'] == 'category') {
            next($id);
            $number = key($id);
            $category = $id[$number];
            $tool_content .= "<tr><td>";
            $tool_content .= "<span class='fa fa-random' style='margin-right:10px; color: grey'></span><em>$number $langFromRandomCategoryQuestions '" . $objQuestionTmp->selectCategoryName($category) . "'</em>";
            $tool_content .= "</td></tr>";
        }  else if ($id['criteria'] == 'difficultycategory') {
            next($id);
            $number = key($id);
            $difficulty = $id[$number][0];
            $category = $id[$number][1];
            $tool_content .= "<tr><td>";
            $tool_content .= "<span class='fa fa-random' style='margin-right:10px; color: grey'></span>
                    <em>$number $langFromRandomDifficultyQuestions '" . $objQuestionTmp->selectDifficultyLegend($difficulty) . "' $langFrom2 '" . $objQuestionTmp->selectCategoryName($category) . "'</em>";
            $tool_content .= "</td></tr>";
        }
    } else {
         $tool_content .= "
             <tr>
                 <td>".q_math($objQuestionTmp->selectTitle())."</td>
                 <td>
                     <div class='progress'>
                         <div class='progress-bar progress-bar-success progress-bar-striped' role='progressbar' aria-valuenow='".$objQuestionTmp->successRate($exerciseId)."' aria-valuemin='0' aria-valuemax='100' style='width: ".$objQuestionTmp->successRate($exerciseId)."%;'>
                           ".$objQuestionTmp->successRate($exerciseId)."%
                         </div>
                     </div>
                 </td>
             </tr>";
    }
}

$tool_content .= "
            </tbody>
        </table>
    </div>
    </div></div></div>";
draw($tool_content, 2, null, $head_content);
