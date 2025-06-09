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
require_once '../../include/lib/ai/services/AIQuestionBankService.php';

$require_editor = TRUE;
$require_current_course = TRUE;
$require_help = true;
$helpTopic = 'exercises';
$helpSubTopic = 'ai_question_generation';

include '../../include/baseTheme.php';

$pageName = $langAIQuestionGeneration ?? 'AI Question Generation';

// Initialize AI service
$aiService = new AIQuestionBankService($course_id, $uid);

// Check if AI is available
if (!$aiService->isAvailable()) {
    Session::Messages($langAINotAvailable ?? 'AI functionality is not available', 'alert-warning');
    redirect_to_home_page("modules/exercise/index.php?course=$course_code");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_questions'])) {
        try {
            $content = $_POST['content'] ?? '';
            $questionCount = intval($_POST['question_count'] ?? 5);
            $difficulty = $_POST['difficulty'] ?? 'medium';
            $questionTypes = $_POST['question_types'] ?? ['multiple_choice'];
            
            if (empty($content)) {
                Session::Messages($langContentRequired ?? 'Content is required', 'alert-danger');
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
                    Session::Messages($langNoQuestionsGenerated ?? 'No questions were generated', 'alert-warning');
                }
            }
        } catch (Exception $e) {
            Session::Messages($langAIGenerationError ?? 'Error generating questions: ' . $e->getMessage(), 'alert-danger');
        }
    } elseif (isset($_POST['save_questions'])) {
        // Save selected questions to question bank
        $selectedQuestions = $_POST['selected_questions'] ?? [];
        $categoryId = intval($_POST['category_id'] ?? 0);
        
        if (!empty($selectedQuestions) && isset($_SESSION['ai_generated_questions'])) {
            $questionsToSave = [];
            foreach ($selectedQuestions as $index) {
                if (isset($_SESSION['ai_generated_questions'][$index])) {
                    $questionsToSave[] = $_SESSION['ai_generated_questions'][$index];
                }
            }
            
            if (!empty($questionsToSave)) {
                try {
                    $result = $aiService->saveQuestionsToBank($questionsToSave, $categoryId);
                    Session::Messages(sprintf($langQuestionsSaved ?? '%d questions saved to question bank', count($result['saved'])), 'alert-success');
                    
                    // Clear generated questions from session
                    unset($_SESSION['ai_generated_questions']);
                } catch (Exception $e) {
                    Session::Messages($langSaveError ?? 'Error saving questions: ' . $e->getMessage(), 'alert-danger');
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
                <h3 class='panel-title'>" . ($langAIQuestionGeneration ?? 'AI Question Generation') . "</h3>
            </div>
            <div class='panel-body'>
                <div class='alert alert-info'>
                    <i class='fa fa-info-circle'></i> 
                    " . ($langAIQuestionInfo ?? 'Use AI to generate questions from your content. The AI will analyze your text and create questions suitable for assessments.') . "
                    <br><small><strong>" . ($langProvider ?? 'Provider') . ":</strong> {$providerInfo['name']}</small>
                </div>
                
                <form method='post' class='form-horizontal'>
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>" . ($langContent ?? 'Content') . " <span class='asterisk'>*</span></label>
                        <div class='col-sm-10'>
                            <textarea name='content' class='form-control' rows='8' placeholder='" . ($langEnterContent ?? 'Enter the content you want to generate questions from...') . "' required>" . (isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '') . "</textarea>
                            <small class='help-block'>" . ($langContentHelp ?? 'Paste your lesson content, document text, or any educational material here.') . "</small>
                        </div>
                    </div>
                    
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>" . ($langQuestionCount ?? 'Number of Questions') . "</label>
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
                        <label class='col-sm-2 control-label'>" . ($langDifficulty ?? 'Difficulty') . "</label>
                        <div class='col-sm-3'>
                            <select name='difficulty' class='form-control'>";

foreach ($availableDifficulties as $value => $label) {
    $selected = (($_POST['difficulty'] ?? 'medium') === $value) ? ' selected' : '';
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
    $checked = (in_array($value, $_POST['question_types'] ?? ['multiple_choice'])) ? ' checked' : '';
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
                                <i class='fa fa-magic'></i> " . ($langGenerateQuestions ?? 'Generate Questions') . "
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>";

// Display generated questions if available
if (isset($_SESSION['ai_generated_questions']) && !empty($_SESSION['ai_generated_questions'])) {
    $questions = $_SESSION['ai_generated_questions'];
    
    $tool_content .= "
    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-success'>
                <div class='panel-heading'>
                    <h3 class='panel-title'>" . ($langGeneratedQuestions ?? 'Generated Questions') . " (" . count($questions) . ")</h3>
                </div>
                <div class='panel-body'>
                    <form method='post'>
                        <div class='alert alert-info'>
                            <i class='fa fa-check-square-o'></i> " . ($langSelectQuestions ?? 'Select the questions you want to save to your question bank') . "
                        </div>";
    
    foreach ($questions as $index => $question) {
        $tool_content .= "
                        <div class='panel panel-default question-preview'>
                            <div class='panel-body'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='selected_questions[]' value='$index' checked>
                                        <strong>" . ($langQuestion ?? 'Question') . " " . ($index + 1) . ":</strong>
                                    </label>
                                </div>
                                <div class='question-content' style='margin-left: 20px;'>
                                    <p><strong>" . htmlspecialchars($question['question_text']) . "</strong></p>
                                    <p><small><span class='label label-info'>" . htmlspecialchars($question['question_type']) . "</span> 
                                    <span class='label label-warning'>" . htmlspecialchars($question['difficulty']) . "</span></small></p>";
        
        if (isset($question['options']) && !empty($question['options'])) {
            $tool_content .= "<ul>";
            foreach ($question['options'] as $option) {
                $tool_content .= "<li>" . htmlspecialchars($option) . "</li>";
            }
            $tool_content .= "</ul>";
        }
        
        if (!empty($question['correct_answer'])) {
            $tool_content .= "<p><strong>" . ($langCorrectAnswer ?? 'Correct Answer') . ":</strong> " . htmlspecialchars($question['correct_answer']) . "</p>";
        }
        
        if (!empty($question['explanation'])) {
            $tool_content .= "<p><strong>" . ($langExplanation ?? 'Explanation') . ":</strong> " . htmlspecialchars($question['explanation']) . "</p>";
        }
        
        $tool_content .= "
                                </div>
                            </div>
                        </div>";
    }
    
    $tool_content .= "
                        <div class='form-group'>
                            <label>" . ($langQuestionCategory ?? 'Question Category') . ":</label>
                            <select name='category_id' class='form-control' style='width: 300px;'>";
    
    foreach ($questionCategories as $catId => $catName) {
        $tool_content .= "<option value='$catId'>$catName</option>";
    }
    
    $tool_content .= "
                            </select>
                        </div>
                        
                        <div class='form-group'>
                            <button type='submit' name='save_questions' class='btn btn-success'>
                                <i class='fa fa-save'></i> " . ($langSaveToQuestionBank ?? 'Save to Question Bank') . "
                            </button>
                            <a href='ai_question_generation.php?course=$course_code' class='btn btn-default'>
                                <i class='fa fa-refresh'></i> " . ($langGenerateNew ?? 'Generate New Questions') . "
                            </a>
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
                    <li>" . ($langTip1 ?? 'Provide clear, well-structured content for better question generation') . "</li>
                    <li>" . ($langTip2 ?? 'Review all generated questions before saving to ensure accuracy') . "</li>
                    <li>" . ($langTip3 ?? 'Mix different question types for comprehensive assessments') . "</li>
                    <li>" . ($langTip4 ?? 'Use appropriate difficulty levels based on your students\' level') . "</li>
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
    // Select/deselect all questions
    $('#selectAll').change(function() {
        $('input[name=\"selected_questions[]\"]').prop('checked', this.checked);
    });
    
    // Add select all checkbox if questions exist
    if ($('input[name=\"selected_questions[]\"]').length > 0) {
        $('.panel-success .panel-heading').append(
            '<div class=\"pull-right\"><label><input type=\"checkbox\" id=\"selectAll\" checked> " . ($langSelectAll ?? 'Select All') . "</label></div>'
        );
    }
});
</script>";

draw($tool_content, 2, null, $head_content);
?>