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

require_once '../../include/baseTheme.php';
require_once 'exercise.class.php';
require_once 'question.class.php';

$exerciseId = $_GET['exerciseId'];
$objExercise = new Exercise();
$found = $objExercise->read($exerciseId);
if (!$found) { // exercise not found
    Session::flash('message',$langExerciseNotFound);
    Session::flash('alert-class', 'alert-danger');
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

$toolName = $langUsage;
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
    <div class='card panelCard card-default px-lg-4 py-lg-3'>
        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
            <h3>" . $objExercise->selectTitle() . "</h3>
        </div>
        <div class='card-body'>
            <div class='row row-cols-1 g-3'>
                <div class='col-12'>
                <h5>$langAttempts ($total_attempts $langSumFrom)</h5>
                    $langAttemptsCompleted: <strong>$completedAttempts</strong>,                            
                    $langAttemptsPaused: <strong>$pausedAttempts</strong>,
                    $langAttemptPending: <strong>$pendingAttempts</strong>,
                    $langAttemptsCanceled: <strong>$cancelledAttempts</strong>                   
                </div>                          
            </div>
        </div>
        <div class='card-body'>
            <div class='row row-cols-1 g-3'>
                <div class='col-12'>
                <h5>$langScore</h5>
                    $langHighestGrade: <strong>$max_grade</strong>,                            
                    $langLowestGrade: <strong>$min_grade</strong>,
                    $langRatingAverage: <strong>$avg_grade</strong>                   
                </div>                          
            </div>
        </div>
        
        <div class='card-body'>
            <div class='row row-cols-1 g-3'>
                <div class='col-12'>
                <h5>$langStudents</h5>
                    $langStudentsExerciseCompleted: <strong>$unique_users</strong>,                            
                    $langAverage $langExerciseDuration: <strong>" . format_time_duration($avg_time) . "</strong>,                                   
                </div>                          
            </div>
        </div>        
    </div>
";

//Questions Table
$questionList = $objExercise->selectQuestionList();
$exerciseCalcGradeMethod = $objExercise->getCalcGradeMethod();

$tool_content .= "
    <div class='col-12 mt-4'>
        <div class='card panelCard card-default px-lg-4 py-lg-3'>
            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                <h3>$langQuestions</h3>                
            </div>
            <div class='card-body'>            
                <div class='table-responsive mt-0'>
                    <table class='table-default'>
                        <thead>
                            <tr class='list-header'>
                                <th>$langTitle</th>
                                <th>$langSuccessPercentage</th>";
                                if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_CERTAINTY_BASED) {
                                    $tool_content .= "<th>$langCertaintyPercentage</th>";
                                }
                            $tool_content .= "
                            </tr>
                        </thead>
                        <tbody>";

                            foreach($questionList as $id) {
                                $objQuestionTmp = new Question();
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
                                    $objQuestionTmp->read($id);
                                    $correctCertaintyBased = $objQuestionTmp->certaintyBasedResults($exerciseId, true);
                                    $wrongCertaintyBased = $objQuestionTmp->certaintyBasedResults($exerciseId, false, true);
                                    $tool_content .= "
                                         <tr>
                                             <td>" . q_math($objQuestionTmp->selectTitle()) . "</td>";
                                                if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_CERTAINTY_BASED) {
                                                    $tool_content .= "<td style='width: 10%';><strong>" . $objQuestionTmp->successRate($exerciseId) . "</strong> %</td>";
                                                    $tool_content .= "<td>";
                                                    $tool_content .= "<span class='p-2' style='color: green;'>$langCorrect</span>";
                                                    foreach ($correctCertaintyBased as $data) {
                                                        $tool_content .= "&nbsp;&nbsp;" . $objQuestionTmp->getCertaintyLegend($data['certainty']) . ":&nbsp;" . $data['count'] . "&nbsp;";
                                                    }
                                                    $tool_content .= "<br><span class='p-2' style='color: red;'>$langIncorrect</span>";
                                                    foreach ($wrongCertaintyBased as $key => $data) {
                                                        $tool_content .= "&nbsp;&nbsp;" . $objQuestionTmp->getCertaintyLegend($data['certainty']) . ":&nbsp; " . $data['count'] . "&nbsp;";
                                                    }
                                                    $tool_content .= "</td>";
                                                } else {
                                                    $tool_content .= "<td>
                                                             <div class='progress'>
                                                                 <div class='progress-bar progress-bar-success progress-bar-striped' role='progressbar' aria-valuenow='" . $objQuestionTmp->successRate($exerciseId) . "' aria-valuemin='0' aria-valuemax='100' style='width: " . $objQuestionTmp->successRate($exerciseId) . "%;'>
                                                                   " . $objQuestionTmp->successRate($exerciseId) . "%
                                                                 </div>
                                                             </div>
                                                         </td>";
                                                }
                                             $tool_content .= "</tr>";
                                }
                            }

                    $tool_content .= "
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>";

draw($tool_content, 2, null, $head_content);
