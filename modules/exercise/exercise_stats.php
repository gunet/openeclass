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
$total_grade = $objExercise->selectTotalWeighting();
$max_grade = $grade_stats->max_grade;
$min_grade = $grade_stats->min_grade;
$avg_grade = $grade_stats->avg_grade;
$avg_time = $grade_stats->avg_time;
$unique_users = $grade_stats->unique_users;
$total_users = Database::get()->querySingle("SELECT COUNT(DISTINCT uid) AS count FROM exercise_user_record WHERE eid = ?d", $exerciseId)->count;

//average number of attempts
//avg completion time

$tool_content .= "
    <div class='card panelCard card-default px-lg-4 py-lg-3'>
        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
            <h3>" . $objExercise->selectTitle() . "</h3>
        </div>
        <div class='row g-3'>
        
            <div class='col-12 col-md-4'>
              <div class='border rounded p-2 h-100'>
                <div class='row row-cols-1 g-3'>
                    <div class='col-12'>
                        <h5>$langAttempts ($total_attempts $langSumFrom)</h5>
                        <div class='progress mb-3' style='height: 30px;'>
                            <div class='progress-bar bg-success' role='progressbar'
                                 data-bs-toggle='tooltip' 
                                 data-bs-placement='top' 
                                 title='" . htmlspecialchars($langAttemptsCompleted, ENT_QUOTES) . ": $completedAttempts'
                                 style='width: " . ($completedAttempts + $pausedAttempts + $pendingAttempts > 0 ? round(($completedAttempts / ($completedAttempts + $pausedAttempts + $pendingAttempts)) * 100, 1) : 0) . "%;'
                                 aria-valuenow='$completedAttempts' aria-valuemin='0' aria-valuemax='" . ($completedAttempts + $pausedAttempts + $pendingAttempts) . "'>
                                $completedAttempts
                            </div>
                            <div class='progress-bar bg-warning' role='progressbar'
                                 data-bs-toggle='tooltip' 
                                 data-bs-placement='top' 
                                 title='" . htmlspecialchars($langAttemptsPaused, ENT_QUOTES) . ": $pausedAttempts'
                                 style='width: " . ($completedAttempts + $pausedAttempts + $pendingAttempts > 0 ? round(($pausedAttempts / ($completedAttempts + $pausedAttempts + $pendingAttempts)) * 100, 1) : 0) . "%;'
                                 aria-valuenow='$pausedAttempts' aria-valuemin='0' aria-valuemax='" . ($completedAttempts + $pausedAttempts + $pendingAttempts) . "'>
                                $pausedAttempts
                            </div>
                            <div class='progress-bar bg-info' role='progressbar'
                                 data-bs-toggle='tooltip' 
                                 data-bs-placement='top' 
                                 title='" . htmlspecialchars($langAttemptPending, ENT_QUOTES) . ": $pendingAttempts'
                                 style='width: " . ($completedAttempts + $pausedAttempts + $pendingAttempts > 0 ? round(($pendingAttempts / ($completedAttempts + $pausedAttempts + $pendingAttempts)) * 100, 1) : 0) . "%;'
                                 aria-valuenow='$pendingAttempts' aria-valuemin='0' aria-valuemax='" . ($completedAttempts + $pausedAttempts + $pendingAttempts) . "'>
                                $pendingAttempts
                            </div>
                        </div>
                        $langAttemptsCompleted: <strong>$completedAttempts</strong><br>
                        $langAttemptsPaused: <strong>$pausedAttempts</strong><br>
                        $langAttemptPending: <strong>$pendingAttempts</strong><br>
                        $langAttemptsCanceled: <strong>$cancelledAttempts</strong><br>
                    </div>                          
                </div>
              </div>
            </div>
            
            <div class='col-12 col-md-4'>
              <div class='border rounded p-2 h-100'>
                <div class='row row-cols-1 g-3'>
                    <div class='col-12'>
                    <h5>$langScore</h5>
                    
                    <div class='gauge-container'>
                      <div class='gauge-wrap' aria-label='Score gauge'>
                        <div class='gauge-clip'>
                          <div class='gauge-arc'></div>
                          <div class='gauge-mask'></div>
                        </div>
                        <div class='gauge-needle-group' id='needleGroup'>
                          <div class='gauge-needle'></div>
                          <div class='needle-value' id='avgNeedleValue'>$avg_grade</div>
                        </div>
                        <div class='gauge-marker' id='sminMarker'>
                          <div class='marker-line'></div>
                          <div class='marker-label'>$min_grade</div>
                        </div>
                        <div class='gauge-marker' id='smaxMarker'>
                          <div class='marker-line'></div>
                          <div class='marker-label'>$max_grade</div>
                        </div>
                        <div class='gauge-center'></div>
                        <div class='gauge-extreme left' id='minGaugeValue'>0.00</div>
                        <div class='gauge-extreme right' id='maxGaugeValue'>$total_grade</div>
                    </div>
                    <script>
                      const minGaugeValueEl = document.getElementById('minGaugeValue');
                      const maxGaugeValueEl = document.getElementById('maxGaugeValue');
                      const avgNeedleValueEl = document.getElementById('avgNeedleValue');
                      const needleGroupEl = document.getElementById('needleGroup');
                
                      const formatScore = (value) => Number(value).toFixed(2);
                
                      function updateMarkers(smin, smax, min, max) {
                        const sminMarkerEl = document.getElementById('sminMarker');
                        const smaxMarkerEl = document.getElementById('smaxMarker');
                        const span = max - min;
                
                        if (sminMarkerEl) {
                          const sminRatio = span > 0 ? (smin - min) / span : 0;
                          const sminDeg = -90 + Math.min(Math.max(sminRatio, 0), 1) * 180;
                          sminMarkerEl.style.transform = 'translateX(-50%) rotate(' + sminDeg + 'deg)';
                          sminMarkerEl.querySelector('.marker-label').textContent = smin;
                        }
                
                        if (smaxMarkerEl) {
                          const smaxRatio = span > 0 ? (smax - min) / span : 0;
                          const smaxDeg = -90 + Math.min(Math.max(smaxRatio, 0), 1) * 180;
                          smaxMarkerEl.style.transform = 'translateX(-50%) rotate(' + smaxDeg + 'deg)';
                          smaxMarkerEl.querySelector('.marker-label').textContent = smax;
                        }
                      }
                      
                      function updateGauge(value, min, max) {
                        minGaugeValueEl.textContent = formatScore(min);
                        maxGaugeValueEl.textContent = formatScore(max);
                        avgNeedleValueEl.textContent = formatScore(value);
                
                        const span = max - min;
                        const relativeValue = span > 0 ? (value - min) / span : 0.5;
                        const needleRatio = Math.min(Math.max(relativeValue, 0), 1);
                        
                        // The gauge is a semi-circle (180 degrees). We map the value to a rotation
                        // from -90 degrees (minimum) to +90 degrees (maximum).
                        const needleDeg = -90 + needleRatio * 180;
                        
                        if (needleGroupEl) {
                          needleGroupEl.style.transform = 'translateX(-50%) rotate(' + needleDeg + 'deg)';
                        }
                      }
                
                      const fixedMin = 0;
                      const fixedMax = " . $total_grade . ";
                      const fixedScore = " . $avg_grade . ";
                      const fixedSMin = " . $min_grade . ";
                      const fixedSMax = " . $max_grade . ";
                
                      // Run on page load with fixed values
                      function initializeGauge() {
                        updateGauge(fixedScore, fixedMin, fixedMax);
                        updateMarkers(fixedSMin, fixedSMax, fixedMin, fixedMax);
                      }
                      
                      initializeGauge();
                    </script>
                      
                    </div>
                        $langLowestGrade: <strong>$min_grade</strong><br>
                        $langHighestGrade: <strong>$max_grade</strong>             
                    </div>                          
                </div>
              </div>
            </div>
            
            <div class='col-12 col-md-4'>
                <div class='d-flex flex-column gap-2 h-100'>
                    
                    <div class='border p-3 flex-fill bg-white rounded'>
                        <h5 class='text-muted'>$langStudentsExerciseCompleted</h5>
                        <div class='h5 fw-bold mb-0'>
                            $unique_users
                        </div>
                        <h5 class='text-muted mt-4'>$langStudents</h5>
                        <div class='h5 fw-bold mb-0'>
                            $total_users
                        </div>
                    </div>
                    
                    <div class='border p-3 flex-fill bg-white rounded'>
                        <h5 class='text-muted mb-2'>$langAverage $langExerciseDuration</h5>
                        <div class='h5 fw-bold mb-0'>
                            " . format_time_duration($avg_time) . "
                        </div>
                    </div>
            
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
                <h3>$langQuestions</h3>";
                if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_CERTAINTY_BASED) {
                    $tool_content .= "
                                <div class='d-flex gap-1'>
                                    <button class='btn submitAdminBtn gradeMethodBtn'>".$langGradeMethod."</button>
                                </div>";
                }
$tool_content .= "</div>
            <div class='card-body'>            
                <div class='table-responsive mt-0'>
                    <table class='table-default'>
                        <thead>
                            <tr class='list-header'>
                                <th>$langTitle</th>
                                <th style='width: 200px;'>$langSuccessPercentage</th>";
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
                                         <tr style='border-top: solid 1px #EFF6FF;border-bottom: none!important;'>
                                             <td>" . q_math($objQuestionTmp->selectTitle()) . "</td>";
                                                $tool_content .= "<td>
                                                             <div class='progress'>
                                                                 <div class='progress-bar progress-bar-success progress-bar-striped' role='progressbar' aria-valuenow='" . $objQuestionTmp->successRate($exerciseId) . "' aria-valuemin='0' aria-valuemax='100' style='width: " . $objQuestionTmp->successRate($exerciseId) . "%;'>
                                                                   " . $objQuestionTmp->successRate($exerciseId) . "%
                                                                 </div>
                                                             </div>";
                                                $tool_content .= "</td>";
                                             $tool_content .= "</tr>";
                                    if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_CERTAINTY_BASED) {

                                        $tool_content .= "<tr class='certaintyPercentageTR d-none' style='border: none !important;'><td colspan='2' style='padding-top: 0;'>
                                                        <div class='row g-1 w-100 m-0'>";

                                        $total_answers = 0;

                                        foreach ($correctCertaintyBased as $data) {
                                            $total_answers += $data['count'];
                                        }
                                        foreach ($wrongCertaintyBased as $data) {
                                            $total_answers += $data['count'];
                                        }

                                        foreach ($correctCertaintyBased as $data) {
                                            $percentage = $total_answers > 0 ? round(($data['count'] / $total_answers) * 100, 1) : 0;
                                            $legendInfo = $objQuestionTmp->getCertaintyLegend2(1, $data['certainty']);

                                            $tool_content .= "
                                            <div class='col-12 col-md-2'>
                                                <div class='text-black text-center rounded px-2 h-100 py-1 d-flex justify-content-center align-items-center' 
                                                    data-bs-toggle='tooltip' 
                                                    data-bs-placement='top' 
                                                    title='" . htmlspecialchars($legendInfo['tooltip'], ENT_QUOTES) . "' 
                                                    style='font-size: 10px; border: solid 1px #198754; background-color: #19875435;'>" .
                                                                                    $legendInfo['text'] . " " . $percentage . "%
                                                </div>
                                            </div>";
                                        }

                                        foreach ($wrongCertaintyBased as $key => $data) {
                                            $percentage = $total_answers > 0 ? round(($data['count'] / $total_answers) * 100, 1) : 0;
                                            $legendInfo = $objQuestionTmp->getCertaintyLegend2(0, $data['certainty']);

                                            $tool_content .= "
                                            <div class='col-12 col-md-2'>
                                                <div class='text-black text-center rounded px-2 h-100 py-1 d-flex justify-content-center align-items-center' 
                                                    data-bs-toggle='tooltip' 
                                                    data-bs-placement='top' 
                                                    title='" . htmlspecialchars($legendInfo['tooltip'], ENT_QUOTES) . "' 
                                                    style='font-size: 10px; border: solid 1px #dc3545; background-color: #dc354535;'>" .
                                                                                    $legendInfo['text'] . " " . $percentage . "%
                                                </div>
                                            </div>";
                                        }

                                        $tool_content .= "</div></td></tr>";
                                    }
                                }
                            }

                    $tool_content .= "
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>";

if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_CERTAINTY_BASED) {
    $tool_content .= "
        <script>
            const gradeMethodBtn = document.querySelector('.gradeMethodBtn');
            const certaintyPercentageTR = document.querySelectorAll('.certaintyPercentageTR');
            gradeMethodBtn.addEventListener('click', () => {
                gradeMethodBtn.classList.toggle('submitAdminBtnDefault');
                gradeMethodBtn.classList.toggle('submitAdminBtn');
                certaintyPercentageTR.forEach((el) => {
                    el.classList.toggle('d-none');
                });
            });
            
        </script>";
}

draw($tool_content, 2, null, $head_content);
