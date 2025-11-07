<?php
/**
 * AJAX endpoint for AI evaluation of exercise responses
 */

$require_login = true;
$require_current_course = true;
$require_editor = true;
$require_help = false;
$helpTopic = 'exercise';

include '../../include/baseTheme.php';
require_once 'include/lib/ai/services/AIExerciseEvaluationService.php';

// Only allow AJAX requests
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    exit('Bad Request');
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

header('Content-Type: application/json');

try {
    // Validate required parameters
    if (!isset($_POST['answer_record_id'])) {
        throw new Exception('Missing answer_record_id parameter');
    }

    $answer_record_id = intval($_POST['answer_record_id']);

    // Get answer record details and verify permissions
    $answerRecord = Database::get()->querySingle("
        SELECT ear.*, eur.eid, eur.uid as student_uid, eq.question, eq.weight
        FROM exercise_answer_record ear
        JOIN exercise_user_record eur ON eur.eurid = ear.eurid  
        JOIN exercise_question eq ON eq.id = ear.question_id
        JOIN exercise e ON e.id = eur.eid
        WHERE ear.answer_record_id = ?d AND e.course_id = ?d",
        $answer_record_id, $course_id);

    // Check if evaluation already exists
    $existingEval = Database::get()->querySingle("
        SELECT * FROM exercise_ai_evaluation 
        WHERE answer_record_id = ?d", $answer_record_id);

    if ($existingEval) {
        // Return existing evaluation
        echo json_encode([
            'success' => true,
            'status' => 'completed',
            'evaluation' => [
                'suggested_score' => floatval($existingEval->ai_suggested_score),
                'max_score' => floatval($existingEval->ai_max_score),
                'reasoning' => $existingEval->ai_reasoning,
                'confidence' => floatval($existingEval->ai_confidence),
                'provider' => $existingEval->ai_provider
            ]
        ]);
        exit;
    }

    // Initialize AI evaluation service
    $aiService = new AIExerciseEvaluationService();
    if (!$aiService->isEnabledForQuestion($answerRecord->question_id, $course_id)) {
        throw new Exception('AI evaluation service is not available');
    }

    // Check if AI evaluation is enabled for this question
    $aiConfig = Database::get()->querySingle("SELECT * FROM exercise_ai_config 
                                                WHERE question_id = ?d 
                                                AND enabled = 1",
                                            $answerRecord->question_id);

    // Perform AI evaluation
    $evaluation = $aiService->evaluateResponse($answer_record_id, $answerRecord->answer);

    echo json_encode([
        'success' => true,
        'status' => 'completed',
        'evaluation' => [
            'suggested_score' => $evaluation['suggested_score'],
            'max_score' => $evaluation['max_score'],
            'reasoning' => $evaluation['reasoning'],
            'confidence' => $evaluation['confidence'],
            'requires_review' => $evaluation['requires_review'],
            'key_points' => $evaluation['key_points'],
            'missing_elements' => $evaluation['missing_elements'],
            'suggestions' => $evaluation['suggestions']
        ]
    ]);

} catch (Exception $e) {
    error_log("AI Evaluation AJAX Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
