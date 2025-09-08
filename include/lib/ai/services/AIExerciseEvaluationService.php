<?php

require_once __DIR__ . '/AIService.php';

/**
 * AI Exercise Evaluation Service for OpenEclass Exercises
 * Handles AI-based evaluation of free text responses in exercises based on tutor-defined criteria
 */
class AIExerciseEvaluationService extends AIService{

    private $provider;

    /**
     * @param AIProviderInterface|null $provider Optional AI provider, will use primary if null
     * @throws Exception
     */
    public function __construct(?AIProviderInterface $provider = null) {
        $this->provider = $provider ?? AIProviderFactory::getPrimaryProvider();

        if (!$this->provider) {
            throw new Exception('No AI provider available for course extraction');
        }
    }

    public function isEnabledForQuestion(int $questionId, int $course_id): bool {

        $q = Database::get()->querySingle("SELECT * FROM exercise_ai_config
                                            WHERE question_id = ?d
                                            AND course_id = ?d
                                            AND enabled = 1",
                                        $questionId, $course_id);
        if ($q) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Evaluate a student response against tutor-defined criteria
     */
    public function evaluateResponse(int $answerRecordId, string $responseText): array {

        // Get question and evaluation criteria from exercise tables
        $evaluation = Database::get()->querySingle("
            SELECT eq.*, eac.evaluation_prompt, eac.sample_responses,
                   ear.question_id, ear.eurid, eur.eid
            FROM exercise_answer_record ear 
            JOIN exercise_question eq ON eq.id = ear.question_id 
            JOIN exercise_user_record eur ON eur.eurid = ear.eurid
            JOIN exercise_ai_config eac ON eac.question_id = eq.id AND eac.enabled = 1
            WHERE ear.answer_record_id = ?d", $answerRecordId);

        if (!$evaluation) {
            throw new Exception("Question not found or AI evaluation not enabled");
        }

        // Check if evaluation already exists
        $existingEval = Database::get()->querySingle("
            SELECT id FROM exercise_ai_evaluation 
            WHERE answer_record_id = ?d", $answerRecordId);

        if ($existingEval) {
            throw new Exception("Response has already been evaluated");
        }

        // Build evaluation prompt
        $prompt = $this->buildEvaluationPrompt(
            $evaluation->question,
            $evaluation->evaluation_prompt,
            $evaluation->weight, // Use question weight as max points
            $responseText,
            $evaluation->sample_responses
        );

        // Get AI evaluation
        $options = [
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3, // Lower temperature for more consistent grading
            'max_tokens' => 1000
        ];

        $aiResponse = $this->provider->evaluateText($prompt, $options);

        $content = $aiResponse['choices'][0]['message']['content'];
        $result = json_decode($content, true);

        if (!$result || !isset($result['suggested_score'])) {
            throw new Exception("Invalid AI response format");
        }

        // Validate the score is within bounds
        $suggestedScore = min(max(0, floatval($result['suggested_score'])), floatval($evaluation->weight));

        // Get provider type before transaction
        $providerType = $this->provider->getProviderType();

        // Store evaluation result with transaction
        $evaluationId = null;
        Database::get()->transaction(function() use ($answerRecordId, $evaluation, $suggestedScore, $result, $providerType, &$evaluationId) {
            // Double-check that evaluation doesn't already exist within transaction
            $existingEval = Database::get()->querySingle("
                SELECT id FROM exercise_ai_evaluation 
                WHERE answer_record_id = ?d", $answerRecordId);

            if ($existingEval) {
                throw new Exception("Response has already been evaluated");
            }

            $evaluationId = Database::get()->query("
                INSERT INTO exercise_ai_evaluation 
                (answer_record_id, question_id, exercise_id, student_record_id, ai_suggested_score, ai_max_score, 
                 ai_reasoning, ai_confidence, ai_provider, created_at) 
                VALUES (?d, ?d, ?d, ?d, ?f, ?f, ?s, ?f, ?s, NOW())",
                $answerRecordId,
                $evaluation->question_id,
                $evaluation->eid,
                $evaluation->eurid,
                $suggestedScore,
                $evaluation->weight,
                $result['reasoning'] ?? 'No reasoning provided',
                isset($result['confidence']) ? min(max(0, floatval($result['confidence'])), 1.0) : 0.5,
                $providerType
            )->lastInsertID;
        });

        return [
            'evaluation_id' => $evaluationId,
            'suggested_score' => $suggestedScore,
            'max_score' => $evaluation->weight,
            'reasoning' => $result['reasoning'] ?? 'No reasoning provided',
            'confidence' => isset($result['confidence']) ? floatval($result['confidence']) : 0.5,
            'requires_review' => (isset($result['confidence']) ? floatval($result['confidence']) : 0.5) < 0.8,
            'key_points' => $result['key_points_identified'] ?? [],
            'missing_elements' => $result['missing_elements'] ?? [],
            'suggestions' => $result['suggestions_for_improvement'] ?? ''
        ];
    }

    /**
     * Build the evaluation prompt for AI
     */
    private function buildEvaluationPrompt(string $question, string $criteria, float $maxPoints, string $response, ?string $samples): string {

        // Get user's language preference
        $userLang = Database::get()->querySingle("SELECT lang FROM user WHERE id = ?d", $this->userId);
        $language = $userLang ? $userLang->lang : 'el'; // Default to Greek

        // Set language instruction based on user preference
        $languageInstruction = '';
        if ($language === 'el') {
            $languageInstruction = 'IMPORTANT: Respond in Greek (Ελληνικά). All explanations, reasoning, and feedback must be written in Greek.';
        } elseif ($language === 'en') {
            $languageInstruction = 'IMPORTANT: Respond in English. All explanations, reasoning, and feedback must be written in English.';
        } else {
            $languageInstruction = 'IMPORTANT: Respond in the same language as the question and evaluation criteria.';
        }

        $prompt = "You are an educational assessment assistant. Evaluate the following student response fairly and consistently.

$languageInstruction

QUESTION: $question

EVALUATION CRITERIA:
$criteria

MAXIMUM POINTS: $maxPoints

STUDENT RESPONSE:
\"$response\"

";

        if ($samples) {
            $sampleData = json_decode($samples, true);
            if ($sampleData && is_array($sampleData)) {
                $prompt .= "SAMPLE RESPONSES FOR REFERENCE:\n";
                foreach ($sampleData as $sample) {
                    if (isset($sample['response']) && isset($sample['quality'])) {
                        $prompt .= "- " . $sample['response'] . " (Quality: " . $sample['quality'] . ")\n";
                    }
                }
                $prompt .= "\n";
            }
        }

        $prompt .= "Provide your evaluation in the following JSON format:
{
    \"suggested_score\": [score between 0 and $maxPoints],
    \"reasoning\": \"[detailed explanation of your evaluation, highlighting what the student did well and what could be improved]\",
    \"confidence\": [confidence level between 0.0 and 1.0],
    \"key_points_identified\": [\"list of key points the student covered\"],
    \"missing_elements\": [\"list of important elements the student missed\"],
    \"suggestions_for_improvement\": \"[specific suggestions for the student]\"
}

EVALUATION GUIDELINES:
- Be fair and consistent in your assessment
- Consider partial credit for partially correct responses
- Explain your reasoning clearly and specifically
- Rate your confidence based on how clear the evaluation criteria are and how well the response fits them
- If confidence is below 0.8, explain why manual review is recommended
- Focus on content quality, understanding, and completeness based on the provided criteria
- Award points proportionally based on how well the response meets the stated criteria";

        return $prompt;
    }



    /**
     * Get evaluation statistics for a question
     */
    public function getEvaluationStats(int $questionId): array {
        $stats = Database::get()->querySingle("
            SELECT 
                COUNT(*) as total_evaluations,
                AVG(ai_suggested_score) as avg_ai_score,
                AVG(ai_confidence) as avg_confidence,
                SUM(CASE WHEN ai_confidence < 0.8 THEN 1 ELSE 0 END) as low_confidence_count
            FROM exercise_ai_evaluation 
            WHERE question_id = ?d", $questionId);

        return [
            'total_evaluations' => intval($stats->total_evaluations),
            'avg_ai_score' => floatval($stats->avg_ai_score),
            'avg_confidence' => floatval($stats->avg_confidence),
            'low_confidence_count' => intval($stats->low_confidence_count)
        ];
    }

}
