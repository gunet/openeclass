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


require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';

$require_current_course = TRUE;
$guest_allowed = true;
require_once '../../include/baseTheme.php';
require_once 'modules/exercise/exercise.lib.php';
require_once 'modules/gradebook/functions.php';
require_once 'game.php';
require_once 'analytics.php';
require_once 'include/lib/ai/services/AIService.php';
require_once 'include/lib/ai/services/AIExerciseEvaluationService.php';

$unit = $unit ?? null;

if ($unit) {
    $unit_name = Database::get()->querySingle('SELECT title FROM course_units WHERE course_id = ?d AND id = ?d',
        $course_id, $unit)->title;
    $navigation[] = ['url' => "index.php?course=$course_code&id=$unit", 'name' => q($unit_name)];
} else {
    $navigation[] = ['url' => "index.php?course=$course_code", 'name' => $langExercices];
}

$head_content .= "
<script>
$(document).ready(function() {
    localStorage.removeItem('openEx');
    localStorage.removeItem('isTinyMCEFocused');
});
</script>
";

# is this an AJAX request to check grades?
$checking = false;
$ajax_regrade = false;

// picture path
$picturePath = "courses/$course_code/image";

require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

load_js('tools.js');

$user = null;
if (isset($_GET['eurId'])) {
    $eurid = $_GET['eurId'];
    $exercise_user_record = Database::get()->querySingle("SELECT *, DATE_FORMAT(record_start_date, '%Y-%m-%d %H:%i') AS record_start_date,
                                                                      TIME_TO_SEC(TIMEDIFF(record_end_date, record_start_date)) AS time_duration
                                                                    FROM exercise_user_record WHERE eurid = ?d", $eurid);
    $exercise_question_ids = Database::get()->queryArray("SELECT question_id, q_position, MAX(answer_record_id) as answer_record_id FROM exercise_answer_record WHERE eurid = ?d GROUP BY question_id, q_position ORDER BY q_position", $eurid);
    if (!$exercise_user_record) {
        // No record matches with this exercise user record id
        Session::flash('message',$langExerciseNotFound);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
    $user = Database::get()->querySingle("SELECT * FROM user WHERE id = ?d", $exercise_user_record->uid);
    if (!$is_course_reviewer && $exercise_user_record->uid != $uid || $exercise_user_record->attempt_status == ATTEMPT_PAUSED) {
       // student is not allowed to view other people's exercise results
       // Nobody can see the results of a paused exercise
       redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
    }
    $objExercise = new Exercise();
    $objExercise->read($exercise_user_record->eid);
    $exercise_id = $exercise_user_record->eid;
    if (!$unit) {
        $navigation[] = array('url' => "results.php?course=$course_code&exerciseId=" . getIndirectReference($exercise_user_record->eid), 'name' => $langResults);
    }
} else {
    // exercise user record id is not set
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

// Handle AJAX requests for course editor
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' and $is_editor) {
    if (isset($_GET['check'])) {
        $checking = true;
        header('Content-Type: application/json');
    } elseif (isset($_POST['regrade'])) {
        $ajax_regrade = true;
    } else {
        $grade = $_POST['question_grade'];
        $question_id = $_POST['question_id'];
        Database::get()->query("UPDATE exercise_answer_record
                    SET weight = ?f WHERE eurid = ?d AND question_id = ?d",
            $grade, $eurid, $question_id);
        $ungraded = Database::get()->querySingle("SELECT COUNT(*) AS count
            FROM exercise_answer_record WHERE eurid = ?d AND weight IS NULL",
            $eurid)->count;
        $totalScore = $objExercise->calculate_total_score($eurid);
        if ($ungraded == 0) {
            // if no more ungraded questions, set an attempt as complete
            $attempt_status = ATTEMPT_COMPLETED;
        } else {
            $attempt_status = ATTEMPT_PENDING;
        }
        Database::get()->query('UPDATE exercise_user_record
            SET attempt_status = ?d, total_score = ?f WHERE eurid = ?d',
            $attempt_status, $totalScore, $eurid);
        $data = Database::get()->querySingle("SELECT eid, uid, total_score, total_weighting
                             FROM exercise_user_record WHERE eurid = ?d", $eurid);
        // update grade book
        if (is_null($data->total_weighting) or $data->total_weighting == 0) {
            update_gradebook_book($data->uid, $data->eid, 0, GRADEBOOK_ACTIVITY_EXERCISE);
        } else {
            update_gradebook_book($data->uid, $data->eid, $data->total_score / $data->total_weighting, GRADEBOOK_ACTIVITY_EXERCISE);
        }
        triggerGame($course_id, $data->uid, $data->eid);
        triggerExerciseAnalytics($course_id, $data->uid, $data->eid);
        exit();
    }
}

if ($is_editor && ($exercise_user_record->attempt_status == ATTEMPT_PENDING || $exercise_user_record->attempt_status == ATTEMPT_COMPLETED)) {
    $head_content .= "<script type='text/javascript'>
            $(document).ready(function(){
                    function save_grade(elem){
                        var grade = parseFloat($(elem).val().replace(',', '.'));
                        var element_name = $(elem).attr('name');
                        var questionId = parseInt(element_name.substring(14,element_name.length - 1));
                        var questionMaxGrade = parseFloat($(elem).next().val());
                        if (grade > questionMaxGrade) {
                            bootbox.alert('$langGradeTooBig');
                            return false;
                        } else if (isNaN(grade)){
                            $(elem).css({'border-color':'red'});
                            return false;
                        } else {
                            $.ajax({
                              type: 'POST',
                              url: '',
                              data: {question_grade: grade, question_id: questionId},
                            });
                            $(elem).parent().prev().hide();
                            $(elem).prop('disabled', true);
                            $(elem).css({'border-color':'#dfdfdf'});
                            return true;
                        }
                    }
                    $('.questionGradeBox').keyup(function (e) {
                        if (e.keyCode == 13) {
                            save_grade(this);
                            var countnotgraded = $('input.questionGradeBox').not(':disabled').length;
                            if (countnotgraded == 0) {
                                $('a#submitButton').hide();
                                $('a#all').hide();
                                $('a#ungraded').hide();
                                $('table.graded').show('slow');
                            }
                        }
                    });
                    if ($('*').hasClass('questionGradeBox')) {
                        $('a#submitButton').show();
                    } else {
                        $('a#submitButton').hide();
                    }
                    $('a#submitButton').click(function(e){
                        e.preventDefault();
                        var success = true;
                        $('.questionGradeBox').each(function() {
                           success = save_grade(this);
                        });
                        if (success) {
                            $('a#submitButton').removeClass('submitAdminBtn').addClass('successAdminBtn pe-none');
                            $('#text_submit').text('".js_escape($langGradebookLimit)."');
                            $('#cancel_submit').text('".js_escape($langBack)."');
                        }
                    });
                    $('a#ungraded').click(function(e){
                        e.preventDefault();
                        $('a#all').removeClass('submitAdminBtn').addClass('cancelAdminBtn');
                        $(this).removeClass('cancelAdminBtn').addClass('submitAdminBtn');
                        $('table.graded').hide('slow');
                    });
                    $('a#all').click(function(e){
                        e.preventDefault();
                        $('a#ungraded').removeClass('submitAdminBtn').addClass('cancelAdminBtn');
                        $(this).removeClass('cancelAdminBtn').addClass('submitAdminBtn');
                        $('table.graded').show('slow');
                    });
                });
                
                // AI Evaluation functionality
                function performAIEvaluation(answerRecordId) {
                    var statusDiv = $('#ai-eval-status-' + answerRecordId);
                    var resultDiv = $('#ai-eval-result-' + answerRecordId);
                    var container = $('#ai-eval-container-' + answerRecordId);
                    
                    // Show loading state
                    statusDiv.html('<div class=\"d-flex align-items-center\"><div class=\"spinner-border spinner-border-sm me-2\" role=\"status\"></div>$langEvaluatingResponseWithAI</div>');
                    
                    // Make AJAX request
                    $.ajax({
                        url: 'ai_evaluate.php?course=' + encodeURIComponent('$course_code'),
                        method: 'POST',
                        data: {
                            answer_record_id: answerRecordId
                        },
                        dataType: 'json',
                        timeout: 60000, // 60 second timeout
                        success: function(response) {
                            if (response.success && response.status === 'completed') {
                                var eval = response.evaluation;
                                var confidencePercent = Math.round(eval.confidence * 100);
                                var confidenceClass = eval.confidence >= 0.8 ? 'text-success' : 
                                                    (eval.confidence >= 0.5 ? 'text-warning' : 'text-danger');
                                var confidenceText = eval.confidence >= 0.8 ? '$langHighConfidence' : 
                                                    (eval.confidence >= 0.5 ? '$langMediumConfidence' : '$langLowConfidence');
                                
                                // Hide status, show results
                                statusDiv.hide();
                                
                                var resultHtml = '<div class=\"row mb-2\">' +
                                    '<div class=\"col-md-6\">' +
                                    '<strong>$langAISuggestion: ' + eval.suggested_score + '/' + eval.max_score + '</strong>' +
                                    '</div>' +
                                    '<div class=\"col-md-6 text-end\">' +
                                    '<span class=\"' + confidenceClass + '\">$langConfidence: ' + confidencePercent + '% (' + confidenceText + ')</span>' +
                                    '</div>' +
                                    '</div>' +
                                    '<div class=\"mb-2\">' +
                                    '<strong>$langReasoning:</strong><br>' +
                                    eval.reasoning.replace(/\\n/g, '<br>') +
                                    '</div>';
                                
                                resultDiv.html(resultHtml).show();
                            } else {
                                showAIEvaluationError(answerRecordId, response.message || 'AI evaluation failed');
                            }
                        },
                        error: function(xhr, status, error) {
                            var errorMsg = 'Unable to connect to AI service';
                            if (status === 'timeout') {
                                errorMsg = 'AI evaluation timed out';
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showAIEvaluationError(answerRecordId, errorMsg);
                        }
                    });
                }
                
                function showAIEvaluationError(answerRecordId, errorMessage) {
                    var statusDiv = $('#ai-eval-status-' + answerRecordId);
                    var container = $('#ai-eval-container-' + answerRecordId);
                    
                    // Update container styling to show error
                    container.removeClass('border-info').addClass('border-danger');
                    container.find('h6').removeClass('text-info').addClass('text-danger');
                    
                    var errorHtml = '<div class=\"text-danger mb-2\">' +
                        '<i class=\"fa fa-exclamation-triangle\"></i> ' + errorMessage +
                        '</div>' +
                        '<button type=\"button\" class=\"btn btn-sm btn-outline-primary\" onclick=\"retryAIEvaluation(' + answerRecordId + ')\">' +
                        '<i class=\"fa fa-refresh\"></i> Retry AI Evaluation' +
                        '</button>';
                    
                    statusDiv.html(errorHtml);
                }
                
                window.retryAIEvaluation = function(answerRecordId) {
                    var container = $('#ai-eval-container-' + answerRecordId);
                    // Reset styling
                    container.removeClass('border-danger').addClass('border-info');
                    container.find('h6').removeClass('text-danger').addClass('text-info');
                    
                    // Retry the evaluation
                    performAIEvaluation(answerRecordId);
                };
                
                // Auto-trigger AI evaluations on page load for pending evaluations
                $(document).ready(function() {
                    $('.ai-eval-pending').each(function() {
                        var answerRecordId = $(this).data('answer-id');
                        if (answerRecordId) {
                            // Add small delay to avoid overwhelming the server with multiple requests
                            setTimeout(function() {
                                performAIEvaluation(answerRecordId);
                            }, Math.random() * 2000 + 500); // Random delay between 500ms-2.5s
                        }
                    });
                });
                
                </script>";
}

$exerciseTitle = $objExercise->selectTitle();
$exerciseDescription = mathfilter(nl2br(make_clickable($objExercise->selectDescription())), 12, "../../courses/mathimg/");
$displayResults = $objExercise->selectResults();
$exerciseRange = $objExercise->selectRange();
$canonical_score = $objExercise->canonicalize_exercise_score($exercise_user_record->total_score, $exercise_user_record->total_weighting);
$displayScore = $objExercise->selectScore();
$gradePass = $objExercise->getPassingGrade();
$exerciseAttemptsAllowed = $objExercise->selectAttemptsAllowed();
$calc_grade_method = $objExercise->getCalcGradeMethod();
$exerciseFeedback = $objExercise->getFeedback();
$userAttempts = Database::get()->querySingle("SELECT COUNT(*) AS count FROM exercise_user_record WHERE eid = ?d AND uid= ?d", $exercise_user_record->eid, $uid)->count;

$cur_date = new DateTime("now");
if (!is_null($objExercise->selectEndDate())) {
    $end_date = new DateTime($objExercise->selectEndDate());
} else {
    $end_date = null;
}

$showResults = $displayResults == 1
               || $is_course_reviewer
               || $displayResults == 3 && $exerciseAttemptsAllowed == $userAttempts
               || $displayResults == 4 && $end_date < $cur_date;

$showScore = $displayScore == 1
            || $is_course_reviewer
            || $displayScore == 3 && $exerciseAttemptsAllowed == $userAttempts
            || $displayScore == 4 && $end_date < $cur_date;

$toolName = $langExercicesResult;

if (!isset($_GET['pdf'])) {
    if ($unit) {
        $action_bar = action_bar([
            [
                'title' => $langBack,
                'url' => "../units/index.php?course=$course_code&id=$_REQUEST[unit]",
                'icon' => 'fa fa-reply',
                'level' => 'primary'
            ],
            [
                'title' => $langDumpPDF,
                'url' => "../units/view.php?course=$course_code&res_type=exercise_results&eurId=$eurid&unit=$unit&pdf=true",
                'icon' => 'fa-file-pdf',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            ]
        ]);
    } else {
        $action_bar = action_bar([
            [
                'title' => $langBack,
                'url' => "results.php?course=$course_code&exerciseId=" . getIndirectReference($exercise_user_record->eid) . "'",
                'icon' => 'fa fa-reply',
                'level' => 'primary'
            ],
            [
                'title' => $langDumpPDF,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&eurId=$eurid&pdf=true",
                'icon' => 'fa-file-pdf',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            ]

        ]);
    }
    $tool_content .= $action_bar;
}

$tool_content .= "<div class='col-12 mt-4'><div class='card panelCard card-default px-lg-4 py-lg-3'>
                      <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                            <h3>" . q_math($exerciseTitle) . "</h3>
                      </div>";

if (!empty($exerciseDescription)) {
    $tool_content .= "<div class='card-body'>$exerciseDescription</div>";
}

$tool_content .= "</div></div>";

$tool_content .= "<div class='row margin-bottom-fat mt-3'>
    <div class='col-12 d-flex justify-content-center align-items-center'>";
if ($is_editor && $exercise_user_record->attempt_status == ATTEMPT_PENDING) {
    $tool_content .= "
            <div class='btn-group btn-group-sm'>
                <a class='btn submitAdminBtn' id='all'>$langAllExercises</a>
                <a class='btn cancelAdminBtn ms-1' id='ungraded'>$langAttemptPending</a>
            </div>";
}
$tool_content .= "
    </div>
  </div>";

$tool_content .= "<div class='col-sm-12'>";

    $tool_content .= "<div class='card panelCard card-default px-lg-4 py-lg-3'>"; //panelCard

    $tool_content .= "<div class='card-header border-0 d-flex justify-content-between align-items-center'>"; //card-header

    if ($user) { // user details
        $tool_content .= "<h3>" . q($user->surname) . " " . q($user->givenname);
        if ($user->am) {
            $tool_content .= " ($langAmShort: " . q($user->am) . ")";
        }
        $tool_content .= "</h3>";
    }
    $tool_content .= "</div>"; //card-header end

    $tool_content .= "<div class='card-body row'>"; // card-body
    $tool_content .= "<div class='col-md-6'>";

    $message_range = $grade_icon = '';
//    $canonicalized_message_range = "<strong>$exercise_user_record->total_score / $exercise_user_record->total_weighting</strong>";
    $canonicalized_message_range = "
        <div class='gauge-container'>
          <div class='gauge-wrap' aria-label='Score gauge'>
            <div class='gauge-clip'>
              <div class='gauge-arc'></div>
              <div class='gauge-mask'></div>
            </div>
            <div class='gauge-needle-group' id='needleGroup'>
              <div class='gauge-needle'></div>
              <div class='needle-value' id='avgNeedleValue'>$exercise_user_record->total_score</div>
            </div>
            <div class='gauge-center'></div>
            <div class='gauge-extreme left' id='minGaugeValue'>0.00</div>
            <div class='gauge-extreme right' id='maxGaugeValue'>$exercise_user_record->total_weighting</div>
          </div>
        </div>
    ";

    $canonicalized_message_range .= "
    
    <script>
      const minGaugeValueEl = document.getElementById('minGaugeValue');
      const maxGaugeValueEl = document.getElementById('maxGaugeValue');
      const avgNeedleValueEl = document.getElementById('avgNeedleValue');
      const needleGroupEl = document.getElementById('needleGroup');

      const formatScore = (value) => Number(value).toFixed(2);

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

      // Fixed values
      const fixedMin = 0;
      const fixedMax = " . $exercise_user_record->total_weighting . ";
      const fixedScore = " . $exercise_user_record->total_score . ";

      // Run on page load with fixed values
      function initializeGauge() {
        updateGauge(fixedScore, fixedMin, fixedMax);
      }
      
      initializeGauge();
    </script>
    
    ";

    if (!is_null($gradePass) && $gradePass > 0) {
        if ($canonical_score >= $objExercise->canonicalize_exercise_pass_grade($gradePass, $exercise_user_record->total_weighting)) {
            $grade_icon = "<span class='fa-solid fa-check ps-1' style='color: green;' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='$langSuccess'></span>";
        } else {
            $grade_icon = "<span class='fa-solid fa-times ps-1' style='color: red;' data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-title='$langFailure'></span>";
        }
    }
    if ($exerciseRange > 0) { // exercise grade range (if any)
        $canonicalized_message_range = "<strong><span>$canonical_score</span> / $exerciseRange</strong>";
        $message_range = "<small> (<strong>$exercise_user_record->total_score / $exercise_user_record->total_weighting</strong>)</small>";
    }

    if ($showScore) {
        $tool_content .= "<p><h5>$langTotalScore</h5> $canonicalized_message_range&nbsp;&nbsp;$message_range $grade_icon</p>";
    }

    $tool_content .= "</div>"; // leftt end
    $tool_content .= "<div class='col-md-6'>"; // right
    $tool_content .= "
            <p><h5>$langStart</h5><em>" . format_locale_date(strtotime($exercise_user_record->record_start_date), 'short') . "</em><br><br>
            <h5>$langDuration</h5><em>" . format_time_duration($exercise_user_record->time_duration) . "</em></p>" .
        ($user && $exerciseAttemptsAllowed ? "<p>$langAttempt: <em>{$exercise_user_record->attempt}</em></p>" : '');

$tool_content .= "</div>"; // right end
    $tool_content .= "</div>"; // card-body end
    $tool_content .= "</div>"; // card end
$tool_content .= "</div>";

if ($is_editor and in_array($exercise_user_record->attempt_status, [ATTEMPT_COMPLETED, ATTEMPT_PENDING]) and isset($_POST['regrade'])) {
    $regrade = true;
} else {
    $regrade = false;
}

$totalWeighting = $totalScore = 0;
$i = 1;
$qid_display = $edit_link = '';
if (count($exercise_question_ids) > 0) {
    // for each question
    foreach ($exercise_question_ids as $row) {
        if (!$showResults) {
            $tool_content .= "<div class='col-12 mt-3'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langExerciseCompleted</span></div></div>";
            break;
        }
        // creates a temporary Question object
        $objQuestionTmp = new Question();
        $is_question = $objQuestionTmp->read($row->question_id);
        if (!$is_question) { // no question found
            continue;
        }
        // gets the student choice for this question
        $choice = $objQuestionTmp->get_answers_record($eurid);
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();
        $questionFeedback = $objQuestionTmp->selectFeedback();
        $questionWeighting = $objQuestionTmp->selectWeighting();
        $answerType = $objQuestionTmp->selectType();
        $questionType = $objQuestionTmp->selectTypeLegend($answerType);
        $questionId = $objQuestionTmp->selectId();
        if ($is_editor) {
            $qid_display = " - id: $questionId";
            $edit_link = icon('fa-edit', $langEdit,
                $urlAppend . "modules/exercise/admin.php?course=$course_code&amp;modifyAnswers=$questionId&fromExercise=$exercise_id");
        }

        // destruction of the Question object
        unset($objQuestionTmp);
        // check if the question has been graded
        $question_weight = Database::get()->querySingle("SELECT SUM(weight) AS weight FROM exercise_answer_record WHERE question_id = ?d AND eurid =?d", $row->question_id, $eurid)->weight;
        $question_graded = !is_null($question_weight);

        $answer_class = ($question_weight == $questionWeighting) ? 'correct_div' : (($question_weight > 0) ? 'partial_div' : 'wrong_div');
        $answer_text = ($question_weight == $questionWeighting) ? $langTrue : (($question_weight > 0) ? $langPartiallyCorrect : $langIncorrect);

        $tool_content .= "<div class='table-responsive question-container mb-5 $answer_class' style='border-radius: 10px;overflow: hidden;'>";
        $tool_content .= "
            <table class='table ".(($question_graded)? 'graded' : 'ungraded')." table-default table-exercise table-exercise-secondary' style='margin: 0;'>
            <thead><tr class='active'>
              <td class='d-flex justify-content-between'>
                <div class='col-10'>
                <strong class='fs-6'>$i. $questionName</strong>";
        $arithmetic_expression_str = '';
        if ($answerType == CALCULATED) {
            $des_arr = unserialize($questionDescription);
            $questionDescription = $des_arr['question_description'];

            $objAn = new Answer($questionId);
            $arithmetic_expression = $des_arr['arithmetic_expression'];
            $arithmetic_expression_str = $objAn->replaceItemsBracesWithWildCards($arithmetic_expression, $questionId);
            unset($objAn);
        }
//        $tool_content .= "<div class='questionName'><p>" . q_math($questionName) . "</p></div><div class='questionDescription'>" . standard_text_escape($questionDescription) . "</div>" . $arithmetic_expression_str;
        $tool_content .= "<div class='questionDescription'>" . standard_text_escape($questionDescription) . "</div>" . $arithmetic_expression_str;

        $classImg = '';
        $classContainer = '';
        $classCanvas = '';
        if ($answerType == DRAG_AND_DROP_MARKERS) {
            $classImg = 'drag-and-drop-markers-img';
            $classContainer = 'drag-and-drop-markers-container';
            $classCanvas = 'drag-and-drop-markers-canvas';
        }
        if (file_exists($picturePath . '/quiz-' . $row->question_id)) {
            $tool_content .= "<div class='$classContainer' id='image-container-$row->question_id' style='position: relative; display: inline-block;'>
                                <img class='$classImg' id='img-quiz-$row->question_id' src='../../$picturePath/quiz-$row->question_id' style='width: 100%;'>
                                <canvas id='drawingCanvas-$row->question_id' class='$classCanvas'></canvas>
                              </div>";
        }
//        $tool_content .= "<span class='fw-lighter m-2'><small>($questionType$qid_display)</small></span>$edit_link"; // question type

        $tool_content .= "</div><div class='col-2 text-end d-flex flex-column'>" . $answer_text;
        if ($answerType == FREE_TEXT or $answerType == ORAL) {
            $choice = purify($choice);
            if (!empty($choice)) {
                if (!$question_graded) {
                    $tool_content .= " <small class='text-danger'>(<span class='text-danger'>$langAnswerUngraded</span>) </small>";
                } else {
                    $tool_content .= " <small>($langGradebookGrade: <strong>$question_weight</strong></span>)</small>";
                }
            }
        } else {
            if (($showScore) and (!is_null($choice))) {
                if ($answerType == MULTIPLE_ANSWER && $question_weight < 0 && $calc_grade_method == 1) {
                    $qw_legend1 = "<span class='Accent-200-cl'>$question_weight</span>";
                    $qw_legend2 = " $langConvertedTo <strong>0 / $questionWeighting</strong>";
                } else {
                    $qw_legend1 = "$question_weight";
                    $qw_legend2 = "";
                }
//                $tool_content .= " <span class='fw-light m-1'><small>($langGradebookGrade: <strong>$qw_legend1 / $questionWeighting</strong>$qw_legend2)</small></span>";
                $tool_content .= " <span class='fw-light m-1'><strong>$qw_legend1 / $questionWeighting</strong></span>";
            }
        }
        $tool_content .= "</div>";
        $tool_content .= "</td></tr></thead>";

        if ($questionDescription) {

        }
//        $tool_content .= "<tr><td colspan='2' class='question-text d-flex flex-column gap-2'>";
//        $arithmetic_expression_str = '';
//        if ($answerType == CALCULATED) {
//            $des_arr = unserialize($questionDescription);
//            $questionDescription = $des_arr['question_description'];
//
//            $objAn = new Answer($questionId);
//            $arithmetic_expression = $des_arr['arithmetic_expression'];
//            $arithmetic_expression_str = $objAn->replaceItemsBracesWithWildCards($arithmetic_expression, $questionId);
//            unset($objAn);
//        }
////        $tool_content .= "<div class='questionName'><p>" . q_math($questionName) . "</p></div><div class='questionDescription'>" . standard_text_escape($questionDescription) . "</div>" . $arithmetic_expression_str;
//        $tool_content .= "<div class='questionDescription'>" . standard_text_escape($questionDescription) . "</div>" . $arithmetic_expression_str;
//
//        $classImg = '';
//        $classContainer = '';
//        $classCanvas = '';
//        if ($answerType == DRAG_AND_DROP_MARKERS) {
//            $classImg = 'drag-and-drop-markers-img';
//            $classContainer = 'drag-and-drop-markers-container';
//            $classCanvas = 'drag-and-drop-markers-canvas';
//        }
//        if (file_exists($picturePath . '/quiz-' . $row->question_id)) {
//            $tool_content .= "<div class='$classContainer' id='image-container-$row->question_id' style='position: relative; display: inline-block;'>
//                                <img class='$classImg' id='img-quiz-$row->question_id' src='../../$picturePath/quiz-$row->question_id' style='width: 100%;'>
//                                <canvas id='drawingCanvas-$row->question_id' class='$classCanvas'></canvas>
//                              </div>";
//        }
//
//        $tool_content .= "</td></tr>";

//        if (!is_null($choice)) {
//            $tool_content .= "<tr class='active'><th colspan='2'><u>$langAnswer</u></th></tr>";
//        }
        $questionScore = 0;

        // display results
        $tool_content .= question_result($answerType, $row->question_id, $choice, $eurid, $regrade);

        if ($questionFeedback !== '') {
            $tool_content .= "<tr><td>";
            $tool_content .= "<div class='text-primary'><strong>$langComment:</strong><br>" . standard_text_escape($questionFeedback) . "</div>";
            $tool_content .= "</td></tr>";
        }

        if ($showScore) {
            if (!is_null($choice)) {
                if (($answerType == FREE_TEXT or $answerType == ORAL) && $is_editor) {
                    if (isset($question_graded) && !$question_graded) {
                        $value = '';
                    } else {
                        $value = round($questionScore, 2);
                    }
                    $tool_content .= "<tr><th colspan='2'>";
                    $tool_content .= "<span>
                                   $langQuestionScore: <input style='display:inline-block;width:auto;' type='text' class='questionGradeBox form-control' maxlength='6' size='6' name='questionScore[$row->question_id]' value='$value'>
                                   <input type='hidden' name='questionMaxGrade' value='$questionWeighting'>
                                   <strong>/$questionWeighting</strong>
                                    </span>";
                    $tool_content .= "</th></tr>";
                }
            }
        }

        if ($answerType == MULTIPLE_ANSWER and $questionScore < 0) {
            $questionScore = 0;
        }

        $rounded_weight = round($question_weight ?? 0, 2);

        if ($rounded_weight < 0 and $answerType == MULTIPLE_ANSWER) {
            $rounded_weight = 0;
        }
        $rounded_score = round($questionScore ?? 0, 2);

        if ($showScore and $rounded_weight != $rounded_score) {
            $tool_content .= "<tr class='warning'>
                                <th colspan='2' class='text-end'>
                                    $langQuestionStoredScore: $rounded_weight / $questionWeighting
                                </th>
                              </tr>";

        }

        $tool_content .= "</table>";
        $tool_content .= "</div>";

        $totalScore += $questionScore;
        $totalWeighting += $questionWeighting;

        // destruction of Answer
        unset($objAnswerTmp);
        $i++;
    } // end foreach()
} else {
    redirect_to_home_page('modules/exercise/index.php?course='.$course_code);
}

if ($totalScore < 0) {
    $totalScore = 0;
}

if ($regrade) {
    $totalScore = $objExercise->calculate_total_score($eurid);

    Database::get()->query('UPDATE exercise_user_record
        SET total_score = ?f, total_weighting = ?f
        WHERE eurid = ?d', $totalScore, $totalWeighting, $eurid);
    update_gradebook_book($exercise_user_record->uid,
        $exercise_user_record->eid, $totalScore / $totalWeighting, GRADEBOOK_ACTIVITY_EXERCISE);

    // find all duplicate wrong entries (for questions with type `unique answer)
    $wrong_data = Database::get()->queryArray("SELECT question_id FROM exercise_answer_record
                                            JOIN exercise_question
                                                ON question_id = id
                                                AND `type` = " . UNIQUE_ANSWER . "
                                                AND eurid = ?d
                                            GROUP BY eurid, question_id, answer_id
                                            HAVING COUNT(question_id) > 1", $eurid);
    // delete all duplicate entries
    foreach ($wrong_data as $d) {
        $max_arid = Database::get()->querySingle("SELECT MAX(answer_record_id) AS max_arid FROM exercise_answer_record WHERE eurid=?d AND question_id=?d", $eurid, $d)->max_arid;
        Database::get()->querySingle("DELETE FROM exercise_answer_record WHERE eurid=?d AND question_id=?d AND answer_record_id != ?d", $eurid, $d, $max_arid);
    }
    Session::flash('message',$langNewScoreRecorded);
    Session::flash('alert-class', 'alert-success');
    if ($ajax_regrade) {
        echo json_encode(['result' => 'ok']);
        exit;
    } else {
        redirect_to_home_page("modules/exercise/exercise_result.php?course=$course_code&eurId=$eurid");
    }
}

$totalScore = round($totalScore, 2);
$totalWeighting = round($totalWeighting, 2);
$oldScore = round($exercise_user_record->total_score, 2);
$oldWeighting = round($exercise_user_record->total_weighting, 2);

if ($is_editor and ($totalScore != $oldScore or $totalWeighting != $oldWeighting)) {
    if ($checking) {
        if ($user) {
            echo json_encode(['result' => 'regrade', 'eurid' => $eurid,
                'title' => "$user->surname $user->givenname (" . $exercise_user_record->record_start_date . ')',
                'url' => $urlAppend . "modules/exercise/exercise_result.php?course=$course_code&eurId=$eurid"],
                JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['result' => 'regrade', 'eurid' => $eurid,
                'title' => "$langNoGroupStudents (" . $exercise_user_record->record_start_date . ')',
                'url' => $urlAppend . "modules/exercise/exercise_result.php?course=$course_code&eurId=$eurid"],
                JSON_UNESCAPED_UNICODE);
        }
        exit;
    } else {

             Session::flash('message',$langScoreDiffers .
             "<form action='exercise_result.php?course=$course_code&eurId=$eurid' method='post'>
                 <button class='btn submitAdminBtn mt-3' type='submit' name='regrade' value='true'>$langRegrade</button>
              </form>");
            Session::flash('alert-class', 'alert-warning');
    }
}

if ($checking) {
    echo json_encode(['result' => 'ok']);
    exit;
}

if (!isset($_GET['pdf']) and $is_editor) {
    $tool_content .= "<div class='col-12 d-flex justify-content-start align-items-center mt-4'>
        <a class='btn submitAdminBtn submitAdminBtnDefault' href='index.php' id='submitButton'><span id='text_submit' class='TextBold'>$langSubmit</span></a>
        <a class='btn cancelAdminBtn m-2' href='results.php?course=$course_code&exerciseId=" . getIndirectReference($exercise_user_record->eid) . "' id='cancelButton'><span id='cancel_submit' class='TextBold'>$langBack</span></a>
    </div>";
}

if (isset($_GET['pdf'])) {
    $pdf_content = "
        <!DOCTYPE html>
        <html lang='el'>
        <head>
          <meta charset='utf-8'>
          <title>" . q("$currentCourseName - $langExercicesResult") . "</title>
          <style>
            * { font-family: 'opensans'; }
            body { font-family: 'opensans'; font-size: 10pt; }
            small, .small { font-size: 8pt; }
            h1, h2, h3, h4 { font-family: 'roboto'; margin: .8em 0 0; }
            h1 { font-size: 16pt; }
            h2 { font-size: 12pt; border-bottom: 1px solid black; }
            h3 { font-size: 10pt; color: #158; border-bottom: 1px solid #158; }
            th { text-align: left; border-bottom: 1px solid #999; }
            td { text-align: left; padding: 10px 0px 10px 0px;}
            .text-danger{color: red;}
            .text-success{color: green;}
            .table-responsive{
                padding: 25px;
                margin: 15px 0px 15px 0px;
                background-color: #eeeeee;
                border: solid 1px #eeeeee;
            }
          </style>
        </head>
        <body>
        <h2> " . get_config('site_name') . " - " . q($currentCourseName) . "</h2>
        <h2> " . q($langExercicesResult) . "</h2>";

    $pdf_content .= $tool_content;
    $pdf_content .= "</body></html>";

    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf\Mpdf([
        'margin_top' => 53,     // approx 200px
        'margin_bottom' => 53,  // approx 200px
        'tempDir' => _MPDF_TEMP_PATH,
        'fontDir' => array_merge($fontDirs, [ $webDir . '/template/modern/fonts' ]),
        'fontdata' => $fontData + [
                'opensans' => [
                    'R' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-regular.ttf',
                    'B' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700.ttf',
                    'I' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-italic.ttf',
                    'BI' => 'open-sans-v13-greek_cyrillic_latin_greek-ext-700italic.ttf'
                ],
                'roboto' => [
                    'R' => 'roboto-v15-latin_greek_cyrillic_greek-ext-regular.ttf',
                    'I' => 'roboto-v15-latin_greek_cyrillic_greek-ext-italic.ttf',
                ]
            ]
    ]);


    $mpdf->SetHTMLHeader(get_platform_logo());
    $footerHtml = '
    <div>
        <table width="100%" style="border: none;">
            <tr>
                <td style="text-align: left;">{DATE j-n-Y}</td>
                <td style="text-align: right;">{PAGENO} / {nb}</td>
            </tr>
        </table>
    </div>
    ' . get_platform_logo('','footer') . '';
    $mpdf->SetHTMLFooter($footerHtml);
    $mpdf->SetCreator(course_id_to_prof($course_id));
    $mpdf->SetAuthor(course_id_to_prof($course_id));
    $mpdf->WriteHTML($pdf_content);
    $mpdf->Output("$course_code exercise_results.pdf", 'I'); // 'D' or 'I' for download / inline display
} else {
    draw($tool_content, 2, null, $head_content);
}


function drag_and_drop_user_results_as_text($eurid,$questionId) {

    global $langPoint, $course_code, $urlAppend;

    $objAnswerTmp = new Answer($questionId);
    $qType = Database::get()->querySingle("SELECT type FROM exercise_question WHERE id = ?d", $questionId)->type;
    $ex_answer = $objAnswerTmp->get_drag_and_drop_text();

    $definedAnswers = $objAnswerTmp->get_drag_and_drop_answer_text();
    // Create an array of new indexes starting from 1
    $keys = range(1, count($definedAnswers));
    // Combine the new keys with the original values
    $definedAnswers = array_combine($keys, $definedAnswers);

    $ex_user_record = Database::get()->queryArray("SELECT answer,answer_id,weight FROM exercise_answer_record WHERE eurid = ?d AND question_id = ?d", $eurid, $questionId);
    $userGrade = 0;
    // Use preg_replace_callback to find all [number] patterns
    $result = preg_replace_callback('/\[(\d+)\]/', function($matches) use ($definedAnswers, $ex_user_record, &$userGrade, $qType, $langPoint, $questionId, $course_code, $urlAppend) {
        $bracket = (int)$matches[1];
        $replacement = ''; // Initialize to empty

        if ($bracket > 0) {
            foreach ($ex_user_record as $an) {
                $userAnswerAsImage = $predefindedAnswerAsImage = false;
                $newLine = $currentBracket = $userAnswerImg = $predefinedAnswerImg= '';
                if ($an->answer_id == $bracket) {
                    if ($qType == DRAG_AND_DROP_MARKERS) {
                        $newLine = '</br>';
                        $currentBracket = $langPoint . "[$bracket] -> ";
                        $userAnswerAsImage = checkMarkerImage($an->answer_id, $an->answer, $questionId);
                        $predefindedAnswerAsImage = checkMarkerImage($bracket, $definedAnswers[$bracket], $questionId);
                    }

                    if ($userAnswerAsImage && $predefindedAnswerAsImage) {
                        $userAnswerImg = "<img src='../../courses/$course_code/image/answer-$questionId-$an->answer_id' style='width:30px; height: 30px;'>";
                        $predefinedAnswerImg = "<img src='../../courses/$course_code/image/answer-$questionId-$bracket' style='width:30px; height: 30px;'>";
                    }

                    if ($an->answer == $definedAnswers[$bracket]) { // correct answer
                        $userGrade += $an->weight;
                        $replacement = $currentBracket . "[" . "<strong class='Success-200-cl'>".$an->answer."</strong>$userAnswerImg" . "&nbsp;/&nbsp;" . $definedAnswers[$bracket] . "$predefinedAnswerImg]" . "&nbsp;&nbsp;<span class='fa-solid fa-check text-success'></span>$newLine";
                    } else {
                        if (!empty($an->answer)) {
                            // Get the correct bracket for incorrect answer for displaying image
                            if ($predefindedAnswerAsImage) {
                                $usIndex = array_search($an->answer, $definedAnswers);
                                $userAnswerImg = "<img src='../../courses/$course_code/image/answer-$questionId-$usIndex' style='width:30px; height: 30px;'>";
                                $prIndex = array_search($definedAnswers[$bracket], $definedAnswers);
                                $predefinedAnswerImg = "<img src='../../courses/$course_code/image/answer-$questionId-$prIndex' style='width:30px; height: 30px;'>";
                            }
                            $replacement = $currentBracket . "[" . "<span class='text-danger'><s>".$an->answer."</s></span>$userAnswerImg" . "&nbsp;/&nbsp;" . $definedAnswers[$bracket] . "$predefinedAnswerImg]" . "&nbsp;&nbsp;<span class='fa-solid fa-xmark text-danger'></span>$newLine";
                        } else {
                            $replacement = $currentBracket . "[" . "<span>&nbsp;&nbsp;&nbsp;</span>" . "/&nbsp;" . $definedAnswers[$bracket] . "$predefinedAnswerImg]" . "&nbsp;&nbsp;<span class='fa-solid fa-xmark text-danger'></span>$newLine";
                        }
                    }
                    break; // Exit loop once a match is found
                }
            }
        }
        // If no matching answer was found, $replacement remains '' (empty string)
        return $replacement;
    }, $ex_answer);

    $arr[] = ['aboutUserAnswers' => $result, 'aboutUserGrade' => $userGrade];

    return $arr;
}


/**
 * Function to check if marker_answer_with_image is "1" for given marker_id and marker_answer
 *
 * @param array $data The decoded JSON data
 * @param int $marker_id The marker ID to search for
 * @param string $marker_answer The marker answer to match
 * @return bool Returns true if marker_answer_with_image == "1", false otherwise
 */
function checkMarkerImage($marker_id, $marker_answer, $questionId) {
    // Split the string into individual JSON objects
    $dataString = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
    if ($dataString) {
        $jsonObjects = explode('|', $dataString);

            foreach ($jsonObjects as $jsonStr) {
                $jsonStr = trim($jsonStr);
                if (empty($jsonStr)) continue;

                // Decode JSON
                $obj = json_decode($jsonStr, true);
                if ($obj === null) {
                    // Invalid JSON, skip or handle error
                    continue;
                }

                // Check for matching marker_id and marker_answer
                if (isset($obj['marker_id'], $obj['marker_answer'], $obj['marker_answer_with_image']) && $obj['marker_id'] == $marker_id && $obj['marker_answer'] == $marker_answer) {
                    // Return true if marker_answer_with_image is "1"
                    return $obj['marker_answer_with_image'] === "1";
                }
            }
            // Not found or no match
            return false;
    } else {
        return false;
    }
}
