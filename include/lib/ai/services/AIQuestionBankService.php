<?php

require_once __DIR__ . '/AIService.php';

/**
 * AI Question Bank Service for OpenEclass
 * Specialized service for AI-powered question generation in the exercise module
 */
class AIQuestionBankService extends AIService {

    /**
     * Generate questions for the question bank
     *
     * @param string $content Source content (document text, lesson content, etc.)
     * @param array $options Question generation options
     * @return array Array of questions ready for question bank insertion
     */
    public function generateQuestionsForBank(string $content, array $options = []): array {
        // Set default options specific to question bank
        $options = array_merge([
            'question_count' => 5,
            'difficulty' => 3,
            'question_types' => ['multiple_choice'],
            'course_id' => $this->courseId,
            'include_explanations' => true,
            'language' => 'el' // Greek default for OpenEclass
        ], $options);

        $generatedQuestions = $this->generateQuestions($content, $options);

        // Format questions for OpenEclass question bank compatibility
        return $this->formatForQuestionBank($generatedQuestions, $options);
    }

    /**
     * Generate questions from uploaded document
     *
     * @param string $documentPath Path to uploaded document
     * @param array $options Generation options
     * @return array Generated questions
     */
    public function generateQuestionsFromDocument(string $documentPath, array $options = []): array {
        $content = $this->extractContentFromDocument($documentPath);

        if (empty($content)) {
            throw new Exception("Could not extract content from document");
        }

        return $this->generateQuestionsForBank($content, $options);
    }

    /**
     * Generate questions from existing exercise content
     *
     * @param int $exerciseId Exercise ID to use as source
     * @param array $options Generation options
     * @return array Generated questions
     */
    public function generateQuestionsFromExercise(int $exerciseId, array $options = []): array {
        // TODO: Get exercise content from database
        // $exercise = Database::get()->querySingle("SELECT description FROM exercise WHERE id = ? AND course_id = ?", [$exerciseId, $this->courseId]);

        // For now, return empty array with comment
        // $content = $exercise->description ?? '';
        $content = ''; // TODO: Replace with actual exercise content

        if (empty($content)) {
            throw new Exception("Exercise content not found or empty");
        }

        return $this->generateQuestionsForBank($content, $options);
    }

    /**
     * Save generated questions to question bank
     * TODO: Implement database insertion when admin system is ready
     *
     * @param array $questions Generated questions
     * @param int $categoryId Question category ID
     * @return array Results of save operation
     */
    public function saveQuestionsToBank(array $questions, int $categoryId = 0): array {
        $saved = [];
        $errors = [];

        foreach ($questions as $question) {
            try {
                $question_obj = new Question();
                $question_obj->updateTitle($question['question_text']);
                $question_obj->updateWeighting($question['weight']);
                $question_obj->updateType($question['question_type']);
                $question_obj->updateDifficulty($question['difficulty']);
                $question_obj->updateCategory($question['category_id']);
                $question_obj->updateFeedback($question['explanation']);
                $question_obj->save();
                $question_id = $question_obj->selectId();
                $question_correct_answer = $question['correct_answer'];

                $position = 0;
                foreach ($question['options'] as $answer_data) { // answers
                    $position++;
                    $answer = new Answer($question_id);
                    if ($answer_data === $question_correct_answer) {
                        $right_answer = 1;
                        $weighting = 1;
                    } else {
                        $right_answer = 0;
                        $weighting = 0;
                    }
                    $answer->createAnswer($answer_data, $right_answer, '', $weighting, $position);
                    $answer->save();
                }

                $saved[] = [
                    'question_id' => $question_id,
                    'question_text' => $question['question_text'],
                    'status' => 'saved'
                ];
            } catch (Exception $e) {
                $errors[] = [
                    'question_text' => $question['question_text'] ?? 'Unknown question',
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'saved' => $saved,
            'errors' => $errors,
            'total_generated' => count($questions),
            'total_saved' => count($saved)
        ];
    }

    /**
     * Format generated questions for OpenEclass question bank
     *
     * @param array $questions Generated questions
     * @param array $options Original options
     * @return array Formatted questions
     */
    private function formatForQuestionBank(array $questions, array $options): array {
        $formatted = [];

        foreach ($questions as $question) {
            $formattedQuestion = [
                'question_text' => $question['question_text'] ?? '',
                /*'question_type' => $this->mapToOpenEclassQuestionType($question['question_type'] ?? 'multiple_choice'), */
                'question_type' => $question['question_type'],
                'difficulty' => $this->mapToOpenEclassQuestionDifficulty($question['difficulty'] ?? 3),
                'correct_answer' => $question['correct_answer'] ?? '',
                'explanation' => $question['explanation'] ?? '',
                'weight' => 1, // Default weight
                'category_id' => 0, // Default category
                'course_id' => $this->courseId,
                'user_id' => $this->userId,
                'created_by_ai' => true,
                'ai_provider' => $this->getProviderInfo()['type'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Handle question-specific formatting
            switch ($formattedQuestion['question_type']) {
                case UNIQUE_ANSWER:
                    $formattedQuestion['options'] = $question['options'] ?? ['Option A', 'Option B', 'Option C', 'Option D'];
                    break;

                case TRUE_FALSE:
                    $formattedQuestion['options'] = ['true', 'false'];
                    break;

                case FILL_IN_BLANKS_TOLERANT:
                    // Process fill-in-the-blanks format
                    $formattedQuestion['blanks'] = $this->extractBlanks($question['question_text']);
                    break;
            }

            $formatted[] = $formattedQuestion;
        }

        return $formatted;
    }

    /**
     * Map AI question types to OpenEclass question types
     *
     * @param string $aiType AI question type
     * @return string OpenEclass question type
     */
    private function mapToOpenEclassQuestionType(string $aiType): string {
        $mapping = [
            'multiple_choice' => UNIQUE_ANSWER,
            'true_false' => TRUE_FALSE,
            'fill_blank' => FILL_IN_BLANKS_TOLERANT,
            'fill_in_blanks' => FILL_IN_BLANKS_TOLERANT,
            'essay' => FREE_TEXT,
            'short_answer' => FREE_TEXT,
            'free_text' => FREE_TEXT
        ];

        return $mapping[$aiType] ?? UNIQUE_ANSWER;
    }

    private function mapToOpenEclassQuestionDifficulty(string $aiType): string {
        $mapping = [
            'easy' => 3,
            'medium' => 4,
            'hard' => 5
        ];

        return $mapping[$aiType] ?? 3;
    }

    /**
     * Extract content from uploaded document
     * TODO: Implement document parsing for different file types
     *
     * @param string $documentPath Path to document
     * @return string Extracted text content
     */
    private function extractContentFromDocument(string $documentPath): string {
        if (!file_exists($documentPath)) {
            throw new Exception("Document not found: {$documentPath}");
        }

        $extension = strtolower(pathinfo($documentPath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'txt':
                return file_get_contents($documentPath);

            case 'pdf':
                // TODO: Implement PDF text extraction
                throw new Exception("PDF text extraction not yet implemented");

            case 'doc':
            case 'docx':
                // TODO: Implement Word document text extraction
                throw new Exception("Word document text extraction not yet implemented");

            default:
                throw new Exception("Unsupported document type: {$extension}");
        }
    }

    /**
     * Extract blanks from fill-in-the-blanks question text
     *
     * @param string $questionText Question text with blanks
     * @return array Array of blank information
     */
    private function extractBlanks(string $questionText): array {
        // TODO: Implement blank extraction logic
        // Look for patterns like [blank], ____, or {answer}
        $blanks = [];

        // Simple pattern matching for now
        if (preg_match_all('/\[([^\]]+)\]/', $questionText, $matches)) {
            foreach ($matches[1] as $blank) {
                $blanks[] = ['answer' => trim($blank)];
            }
        }

        return $blanks;
    }

    /**
     * Get question generation statistics
     * TODO: Implement when admin system is ready
     *
     * @return array Statistics about AI question generation
     */
    public function getGenerationStatistics(): array {
        // TODO: Get from database
        return [
            'total_generated' => 0, // TODO: Count from ai_usage_log
            'by_difficulty' => ['easy' => 0, 'medium' => 0, 'hard' => 0],
            'by_type' => ['multiple_choice' => 0, 'true_false' => 0, 'fill_in_blanks' => 0, 'free_text' => 0],
            'success_rate' => 0.0
        ];
    }
}
