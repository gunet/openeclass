<?php

require_once 'answer.class.php';

class FreeTextAnswer extends QuestionType
{
    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        // TODO: Implement PreviewQuestion() method.
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        $questionId = $this->question_id;
        $text = '';
        $html_content = '';

        if (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] != '') {
            $text = $exerciseResult[$questionId];
        }

        $html_content .= "
            <div class='col-12' id='freetext_{$questionId}'>
                " . rich_text_editor("choice[$questionId]", 14, 90, $text, options: $options) . "
            </div>";

        return $html_content;
    }


    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $questionScore, $question_weight,
               $langHighConfidence, $langMediumConfidence, $langLowConfidence,
               $langAIEvaluation, $langAISuggestion, $langConfidence,$langReasoning,
               $langEvaluatingResponseWithAI, $course_id, $is_editor, $uid;

        $questionId = $this->question_id;
        $questionScore = $question_weight;

        $html_content = '';

        $text = $choice; // plain text
        $html_content .= "<tr><td>" . purify($text). "</td></tr>";

        if ($is_editor) {
            $arid = Database::get()->querySingle("SELECT answer_record_id FROM exercise_answer_record WHERE eurid = ?d", $eurid)->answer_record_id;
            $ai_evaluation = Database::get()->querySingle("SELECT * FROM exercise_ai_evaluation WHERE answer_record_id = ?d", $arid);
            if ($ai_evaluation) {
                $confidence_percent = round($ai_evaluation->ai_confidence * 100);

                if ($ai_evaluation->ai_confidence >= 0.8) {
                    $confidence_class = 'text-success';
                    $confidence_text = $langHighConfidence;
                } elseif ($ai_evaluation->ai_confidence >= 0.5) {
                    $confidence_class = 'text-warning';
                    $confidence_text = $langMediumConfidence;
                } else {
                    $confidence_class = 'text-danger';
                    $confidence_text = $langLowConfidence;
                }

                $html_content .= "<tr><td>";
                $html_content .= "<div class='mt-3 p-3 bg-light border-start border-info border-4'>";
                $html_content .= "<h6 class='text-info'><i class='fa fa-robot'></i> $langAIEvaluation</h6>";

                $html_content .= "<div class='row mb-2'>";
                $html_content .= "<div class='col-md-6'>";
                $html_content .= "<strong>$langAISuggestion: {$ai_evaluation->ai_suggested_score}/{$ai_evaluation->ai_max_score}</strong>";
                $html_content .= "</div>";
                $html_content .= "<div class='col-md-6 text-end'>";
                $html_content .= "<span class='$confidence_class'>$langConfidence: {$confidence_percent}% ($confidence_text)</span>";
                $html_content .= "</div>";
                $html_content .= "</div>";

                $html_content .= "<div class='mb-2'>";
                $html_content .= "<strong>$langReasoning:</strong><br>";
                $html_content .= nl2br(q($ai_evaluation->ai_reasoning));
                $html_content .= "</div>";

                $html_content .= "</div>";
                $html_content .= "</td></tr>";
            } else {
                // Check if AI evaluation is enabled for this question
                $aiService = new AIService($course_id, $uid);
                $aiEvaluationService = new AIExerciseEvaluationService();

                if ($aiService->isEnabledForCourse(AI_MODULE_FREE_TEXT_EVALUATION) && $aiEvaluationService->isEnabledForQuestion($questionId, $course_id)) {
                    // Show AI evaluation trigger button
                    $html_content .= "<tr><td>";
                    $html_content .= "<div class='mt-3 p-3 bg-light border-start border-info border-4' id='ai-eval-container-{$arid}'>";
                    $html_content .= "<h6 class='text-info'><i class='fa fa-robot'></i> $langAIEvaluation</h6>";
                    $html_content .= "<div id='ai-eval-status-{$arid}' class='ai-eval-pending' data-answer-id='{$arid}'>";
                    $html_content .= "<div class='d-flex align-items-center'>";
                    $html_content .= "<div class='spinner-border spinner-border-sm me-2' role='status'></div>";
                    $html_content .= "$langEvaluatingResponseWithAI";
                    $html_content .= "</div>";
                    $html_content .= "</div>";
                    $html_content .= "<div id='ai-eval-result-{$arid}' style='display: none;'></div>";
                    $html_content .= "</div>";
                    $html_content .= "</td></tr>";
                }
            }
        }
        return $html_content;
    }
}
