<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
$require_editor = true;

include '../../include/baseTheme.php';
include('exercise.class.php');
include('question.class.php');

$exerciseId = $_GET['exerciseId'];
$objExercise = new Exercise();
$objExercise->read($exerciseId);

$pageName = $langExerciseStats;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);

$tool_content .= action_bar(array(
    array(
        'title' => $langBack,
        'level' => 'primary-label',
        'icon' => 'fa-reply',
        'url' => "index.php?course=$course_code"
    ),          
));
 
$completedAttempts = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_COMPLETED)->count;
$pausedAttempts = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_PAUSED)->count;
$pendingAttempts = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_PENDING)->count;
$cancelledAttempts = Database::get()->querySingle("SELECT count(*) AS count FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_CANCELED)->count; 
$total_attempts = $completedAttempts + $pausedAttempts + $pendingAttempts + $cancelledAttempts;

$grade_stats = Database::get()->querySingle("SELECT COUNT(DISTINCT uid) AS unique_users, AVG(TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date))) AS avg_time, AVG(total_score) AS avg_grade, MIN(total_score) AS min_grade, MAX(total_score) AS max_grade FROM exercise_user_record WHERE eid = ?d AND attempt_status = ?d", $exerciseId, ATTEMPT_COMPLETED);
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
                <tr>
                    <th colspan='4' class='text-center'>$langAttempts</th>
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
                <tr class='active'>
                    <th colspan='3'>$langTotal:</th>
                    <th colspan='1'>$total_attempts</th>
                </tr>            
            </tfoot>
        </table>
    </div>
    <div class='table-responsive'>
        <table class='table-default'>
            <thead>
                <tr>
                    <th colspan='2' class='text-center'>$langScore</th>
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
    <div class='table-responsive'>
        <table class='table-default'>
            <thead>
                <tr>
                    <th colspan='2' class='text-center'>$langStudents</th>
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
    <h3>$langQuestions</h3>
    <div class='table-responsive'>
        <table class='table-default'>
            <thead>
                <tr>
                    <th>$langTitle</th>
                    <th>Ποσοστό Επιτυχίας</th>
                </tr>
            </thead>
            <tbody>";
foreach($questionList as $id){
    $objQuestionTmp = new Question();
    $objQuestionTmp->read($id);    
    $tool_content .= "
                <tr>
                    <td>".q_math($objQuestionTmp->selectTitle())."</th>
                    <td>
                        <div class='progress'>
                            <div class='progress-bar progress-bar-success progress-bar-striped' role='progressbar' aria-valuenow='".$objQuestionTmp->successRate($exerciseId)."' aria-valuemin='0' aria-valuemax='100' style='width: ".$objQuestionTmp->successRate($exerciseId)."%;'>
                              ".$objQuestionTmp->successRate($exerciseId)."%
                            </div>
                        </div></td>                   
                </tr>";         
}
               
$tool_content .= "
            </tbody>
        </table>
    </div>";    
draw($tool_content, 2, null, $head_content);
