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

/**
 * @file ai_question_generation.php
 * @brief AI-powered question generation interface for exercises
 */

require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
require_once 'exercise.lib.php';

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = true;
$helpTopic = 'exercises';
$helpSubTopic = 'ai_question_generation';

include '../../include/baseTheme.php';

// AI service and exercise setup AFTER baseTheme
require_once 'include/lib/ai/services/AIQuestionBankService.php';

// Get exercise ID if provided
$exerciseId = isset($_GET['exerciseId']) ? intval($_GET['exerciseId']) : 0;
$exercise = null;
if ($exerciseId > 0) {
    $exercise = new Exercise();
    $exercise->read($exerciseId);
}

$toolName = $langQuestionPool;
$pageName = $langAIQuestionGeneration;
$navigation[] = array("url" => "question_pool.php?course=$course_code", "name" => $langQuestionPool);

// Initialize AI service
$aiService = new AIQuestionBankService($course_id, $uid);

// Check if AI is available
if (!$aiService->isAvailable()) {
    Session::Messages($langAINotAvailable, 'alert-warning');
    redirect_to_home_page("modules/exercise/index.php?course=$course_code");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_questions'])) {
        try {
            $content = $_POST['content'] ?? '';
            $questionCount = intval($_POST['question_count'] ?? 5);
            $difficulty = $_POST['difficulty'] ?? 'medium';
            $questionTypes = $_POST['question_types'] ?? [UNIQUE_ANSWER];

            if (empty($content)) {
                Session::Messages($langContentRequired, 'alert-danger');
            } else {
                $options = [
                    'question_count' => $questionCount,
                    'difficulty' => $difficulty,
                    'question_types' => $questionTypes,
                    'language' => $language === 'el' ? 'el' : 'en'
                ];

                $questions = $aiService->generateQuestionsForBank($content, $options);

                if (!empty($questions)) {
                    $_SESSION['ai_generated_questions'] = $questions;
                    Session::Messages(sprintf($langQuestionsGenerated ?? '%d questions generated successfully', count($questions)), 'alert-success');
                } else {
                    Session::Messages($langNoQuestionsGenerated, 'alert-warning');
                }
            }
        } catch (Exception $e) {
            Session::Messages($langAIGenerationError . $e->getMessage(), 'alert-danger');
        }
    } elseif (isset($_POST['save_questions']) || isset($_POST['add_to_exercise'])) {
        // Save selected questions to question bank or add to exercise
        $selectedQuestions = $_POST['selected_questions'] ?? [];
        $categoryId = intval($_POST['category_id'] ?? 0);
        $addToExercise = isset($_POST['add_to_exercise']);

        if (!empty($selectedQuestions) && isset($_SESSION['ai_generated_questions'])) {
            $questionsToSave = [];
            foreach ($selectedQuestions as $index) {
                if (isset($_SESSION['ai_generated_questions'][$index])) {
                    $questionsToSave[] = $_SESSION['ai_generated_questions'][$index];
                }
            }

            if (!empty($questionsToSave)) {
                try {
                    if ($addToExercise && $exerciseId > 0) {
                        // Add questions directly to the exercise
                        $result = $aiService->saveQuestionsToExercise($questionsToSave, $exerciseId, $categoryId);
                        Session::Messages(sprintf($langQuestionsAddedToExercise ?? '%d questions added to exercise', count($result['saved'])), 'alert-success');
                    } else {
                        // Save to question bank only
                        $result = $aiService->saveQuestionsToBank($questionsToSave, $categoryId);
                        Session::Messages(sprintf($langQuestionsSaved ?? '%d questions saved to question bank', count($result['saved'])), 'alert-success');
                    }
                    // Clear generated questions from the session
                    unset($_SESSION['ai_generated_questions']);
                } catch (Exception $e) {
                    Session::Messages($langSaveError . $e->getMessage(), 'alert-danger');
                }
            }
        }
    }
}

// Get available options
$availableQuestionTypes = $aiService->getAvailableQuestionTypes();
$availableDifficulties = $aiService->getAvailableDifficultyLevels();
$providerInfo = $aiService->getProviderInfo();

// Get question categories for save dropdown
// TODO: Replace with actual database query when admin system is ready
$questionCategories = [
    0 => $langDefaultCategory ?? 'Default Category',
    // TODO: Get from database: Database::get()->queryArray("SELECT id, name FROM exercise_question_categories WHERE course_id = ?", [$course_id])
];

$tool_content .= "
    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-primary'>
                <div class='panel-heading'>
                    <h3 class='panel-title'>$langAIQuestionGeneration" .
                    ($exercise ? " - " . htmlspecialchars($exercise->selectTitle()) : "") . "</h3>
                </div>
                <div class='panel-body'>
                    <div class='alert alert-info'>
                        <i class='fa fa-info-circle'></i> 
                        $langAIQuestionInfo
                        <br><small><strong>$langProvider:</strong> {$providerInfo['name']}</small>
                    </div>
                    
                    <form method='post' class='form-horizontal'>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>$langContent <span class='asterisk'>*</span></label>
                            <div class='col-sm-10'>
                                <textarea name='content' class='form-control' rows='8' placeholder='$langEnterContent' required>" . (isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '') . "</textarea>
                                <small class='help-block'>$langContentHelp</small>
                            </div>
                        </div>
                        
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>$langQuestionCount</label>
                            <div class='col-sm-3'>
                                <select name='question_count' class='form-control'>
                                    <option value='3'" . (($_POST['question_count'] ?? 5) == 3 ? ' selected' : '') . ">3</option>
                                    <option value='5'" . (($_POST['question_count'] ?? 5) == 5 ? ' selected' : '') . ">5</option>
                                    <option value='10'" . (($_POST['question_count'] ?? 5) == 10 ? ' selected' : '') . ">10</option>
                                    <option value='15'" . (($_POST['question_count'] ?? 5) == 15 ? ' selected' : '') . ">15</option>
                                    <option value='20'" . (($_POST['question_count'] ?? 5) == 20 ? ' selected' : '') . ">20</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>$langQuestionDiffGrade</label>
                            <div class='col-sm-3'>
                                <select name='difficulty' class='form-control'>";

                        foreach ($availableDifficulties as $value => $label) {
                            $selected = (($_POST['difficulty'] ?? 3) === $value) ? ' selected' : '';
                            $tool_content .= "<option value='$value'$selected>$label</option>";
                        }

                        $tool_content .= "
                                </select>
                            </div>
                        </div>
                        
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>" . ($langQuestionTypes ?? 'Question Types') . "</label>
                            <div class='col-sm-8'>";

                    foreach ($availableQuestionTypes as $value => $label) {
                        $checked = (in_array($value, $_POST['question_types'] ?? [UNIQUE_ANSWER])) ? ' checked' : '';
                        $tool_content .= "
                                        <div class='checkbox'>
                                            <label>
                                                <input type='checkbox' name='question_types[]' value='$value'$checked> $label
                                            </label>
                                        </div>";
                    }

                    $tool_content .= "
                            </div>
                        </div>
                        
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <button type='submit' name='generate_questions' class='btn btn-primary'>
                                    <i class='fa fa-magic'></i>$langGenerateQuestions
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>";

// Display generated questions if available
if (!empty($_SESSION['ai_generated_questions'])) {
    $questions = $_SESSION['ai_generated_questions'];

    $tool_content .= "
    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-success'>
                <div class='panel-heading'>
                    <h3 class='panel-title'>$langGeneratedQuestions: " .  count($questions) . "</h3>
                </div>
                <div class='panel-body'>
                    <form method='post'>
                        <div class='alert alert-info'>
                            <i class='fa fa-check-square-o'></i> $langSelectQuestions
                        </div>";

    foreach ($questions as $index => $question) {
        $objQuestion = new Question();
        $tool_content .= "
                <div class='panel panel-default question-preview'>
                    <div class='panel-body'>
                        <div class='checkbox'>
                            <label>
                                <input type='checkbox' name='selected_questions[]' value='$index' checked>
                                <strong>$langQuestion " . ($index + 1) . ":</strong>
                                <small>" . $objQuestion->selectTypeLegend($question['question_type']) . "&nbsp;&mdash;&nbsp;" . $objQuestion->selectDifficultyLegend($question['difficulty']) . "</small>                                
                            </label>
                        </div>
                        <div class='question-content' style='margin-left: 20px;'>
                            <p><strong>" . htmlspecialchars($question['question_text']) . "</strong></p>";

        if (!empty($question['options'])) {
            $tool_content .= "<ul>";
            foreach ($question['options'] as $option) {
                $tool_content .= "<li>" . htmlspecialchars($option) . "</li>";
            }
            $tool_content .= "</ul>";
        }

        if (!empty($question['correct_answer'])) {
            $tool_content .= "<p><strong>$langCorrectAnswer:</strong> " . htmlspecialchars($question['correct_answer']) . "</p>";
        }

        if (!empty($question['explanation'])) {
            $tool_content .= "<p><strong>$langExplanation:</strong> " . htmlspecialchars($question['explanation']) . "</p>";
        }

        $tool_content .= "
                                </div>
                            </div>
                        </div>";
    }

    $tool_content .= "
                        <div class='form-group' style='padding: 15px; background-color: #f9f9f9; border-radius: 5px; margin-bottom: 20px;'>
                            <label style='margin-bottom: 10px; font-weight: bold;'>$langQuestionCategory:</label>
                            <select name='category_id' class='form-control' style='width: 300px; margin-bottom: 15px;'>";

    foreach ($questionCategories as $catId => $catName) {
        $tool_content .= "<option value='$catId'>$catName</option>";
    }

    $tool_content .= "
                            </select>
                        </div>
                        
                            <div class='text-center' style='padding-top: 10px; border-top: 1px solid #ddd; margin-top: 15px; white-space: nowrap;'>";

    // Show appropriate save buttons based on context
    if ($exerciseId > 0) {
        $tool_content .= "
                                <button type='submit' name='add_to_exercise' class='btn btn-primary' style='margin-right: 10px; display: inline-block; vertical-align: middle; line-height: 1.5; padding: 8px 12px;'>
                                    <i class='fa fa-plus-circle'></i> $langAddToExercise
                                </button>
                                <button type='submit' name='save_questions' class='btn btn-success' style='margin-right: 10px; display: inline-block; vertical-align: middle; line-height: 1.5; padding: 8px 12px;'>
                                    <i class='fa fa-save'></i> $langSaveToQuestionBank
                                </button>";
    } else {
        $tool_content .= "
                                <button type='submit' name='save_questions' class='btn btn-success' style='margin-right: 10px; display: inline-block; vertical-align: middle; line-height: 1.5; padding: 8px 12px;'>
                                    <i class='fa fa-save'></i> $langSaveToQuestionBank
                                </button>";
    }

    $redirectUrl = $exerciseId > 0 ? "ai_question_generation.php?course=$course_code&exerciseId=$exerciseId" : "ai_question_generation.php?course=$course_code";
    $tool_content .= "
                                <a href='$redirectUrl' class='btn btn-default' style='display: inline-block; vertical-align: middle; line-height: 1.5; padding: 8px 12px;'>
                                    <i class='fa fa-refresh'></i> $langGenerateNew
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>";
}

$tool_content .= "
<div class='row'>
    <div class='col-md-12'>
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h4>" . ($langUsageTips ?? 'Usage Tips') . "</h4>
            </div>
            <div class='panel-body'>
                <ul>
                    <li>$langTip1</li>
                    <li>$langTip2</li>
                    <li>$langTip3</li>
                    <li>$langTip4</li>
                </ul>
            </div>
        </div>
    </div>
</div>";

// Add custom CSS for better styling
$head_content .= "
    <style>
        .question-preview {
            margin-bottom: 15px;
        }
        .question-content {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #5cb85c;
        }
        .asterisk {
            color: red;
        }
</style>";

// Add JavaScript for enhanced interaction
$head_content .= "
    <script>
        $(document).ready(function() {
            // Add select all checkbox if questions exist
            setTimeout(function() {
                var questionCheckboxes = $('input[name=\"selected_questions[]\"]');
                if (questionCheckboxes.length > 0) {
                    $('.panel-success .panel-heading h3').after(
                        '<div class=\"pull-right\"><label><input type=\"checkbox\" id=\"selectAll\" checked> " . ($langSelectAll ?? 'Select All') . "</label></div>'
                    );
                    
                    // Select/deselect all questions functionality
                    $('#selectAll').change(function() {
                        questionCheckboxes.prop('checked', this.checked);
                    });
                    
                    // Update select all checkbox when individual checkboxes change
                    questionCheckboxes.change(function() {
                        var totalCheckboxes = questionCheckboxes.length;
                        var checkedCheckboxes = questionCheckboxes.filter(':checked').length;
                        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
                    });
                }
            }, 100);
        });
    </script>";

draw($tool_content, 2, null, $head_content);
