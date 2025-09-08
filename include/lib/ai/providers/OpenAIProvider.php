<?php

require_once __DIR__ . '/AbstractAIProvider.php';

/**
 * OpenAI Provider implementation for OpenEclass AI integration
 * Handles communication with OpenAI's GPT models
 */
class OpenAIProvider extends AbstractAIProvider {

    private const DEFAULT_MODEL = 'gpt-4o-mini';
    private const DEFAULT_ENDPOINT = 'https://api.openai.com/v1/chat/completions';


    /**
     * Get provider type identifier
     */
    public function getProviderType(): string {
        return 'openai';
    }

    /**
     * Get display name
     */
    public function getDisplayName(): string {
        return 'OpenAI (ChatGPT)';
    }

    /**
     * Get default model
     */
    protected function getDefaultModel(): string {
        return self::DEFAULT_MODEL;
    }

    /**
     * Get default endpoint
     */
    protected function getDefaultEndpoint(): string {
        return self::DEFAULT_ENDPOINT;
    }

    /**
     * Get available models for OpenAI
     */
    public function getAvailableModels(): array {
        return [
            'gpt-4.1' => 'GPT-4.1',
            'gpt-4.1-mini' => 'GPT-4.1 Mini',
            'gpt-4.1-nano' => 'GPT-4.1 Nano',
            'gpt-4o' => 'GPT-4o',
            'gpt-4o-mini' => 'GPT-4o Mini',
            'o4-mini' => 'O4 Mini (Reasoning)',
            'o3' => 'O3 (Reasoning)',
            'o3-mini' => 'O3 Mini (Reasoning)'
        ];
    }

    /**
     * Check if OpenAI service is healthy
     */
    public function isHealthy(): bool {
        try {
            // Use models endpoint for connectivity check - simpler and doesn't consume tokens
            $response = $this->makeApiRequest('https://api.openai.com/v1/models', [], 'GET');
            return isset($response['data']) && is_array($response['data']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Make API request to OpenAI
     */
    protected function makeApiRequest(string $endpoint, array $data, string $method = 'POST'): array {
        $headers = [
            'Authorization: Bearer ' . $this->apiKey
        ];

        $ch = curl_init();
        $curlOptions = [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ];
        
        if ($method === 'POST') {
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
            $headers[] = 'Content-Type: application/json';
            $curlOptions[CURLOPT_HTTPHEADER] = $headers;
        } else {
            $curlOptions[CURLOPT_HTTPGET] = true;
        }
        
        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL error: " . $error);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "HTTP error: " . $httpCode;
            throw new Exception("OpenAI API error: " . $errorMessage);
        }

        $decodedResponse = json_decode($response, true);
        if (!$decodedResponse) {
            throw new Exception("Invalid JSON response from OpenAI API");
        }

        // Log usage for monitoring
        $this->logApiUsage('api_request', [
            'model' => $data['model'] ?? $this->modelName,
            'tokens_used' => $decodedResponse['usage']['total_tokens'] ?? 0,
            'http_code' => $httpCode
        ]);

        return $decodedResponse;
    }

    /**
     * Build API request for OpenAI format
     */
    protected function buildApiRequest(string $prompt, array $options): array {
        return [
            'model' => $this->modelName,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an educational assistant that generates high-quality questions for learning assessment. Always respond with valid JSON format.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object'] // Ensure JSON response
        ];
    }

    /**
     * Format OpenAI response into OpenEclass question format
     */
    protected function formatQuestionsResponse(array $apiResponse, array $options): array {
        if (!isset($apiResponse['choices'][0]['message']['content'])) {
            throw new Exception("Invalid response format from OpenAI");
        }

        $content = $apiResponse['choices'][0]['message']['content'];
        $questionsData = json_decode($content, true);

        if (!$questionsData || !isset($questionsData['questions'])) {
            throw new Exception("Invalid JSON format in OpenAI response");
        }

        $formattedQuestions = [];
        foreach ($questionsData['questions'] as $question) {
            $formattedQuestions[] = $this->formatSingleQuestion($question);
        }

        return $formattedQuestions;
    }

    /**
     * Format a single question into an OpenEclass format
     */
    private function formatSingleQuestion(array $questionData): array {
        $formatted = [
            'question_text' => $questionData['question'] ?? '',
            'question_type' => $this->mapQuestionType($questionData['type'] ?? 'multiple_choice'),
            'difficulty' => $questionData['difficulty'] ?? 3,
            'correct_answer' => $questionData['correct_answer'] ?? '',
            'correct_answer_index' => $questionData['correct_answer_index'] ?? null,
            'explanation' => $questionData['explanation'] ?? '',
            'provider' => $this->getProviderType(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Handle options for multiple choice questions
        if (isset($questionData['options']) && is_array($questionData['options'])) {
            $formatted['options'] = $questionData['options'];
        }

        // Handle different question types
        switch ($formatted['question_type']) {
            case 'multiple_choice':
                if (!isset($formatted['options']) || count($formatted['options']) < 2) {
                    $formatted['options'] = ['Option A', 'Option B', 'Option C', 'Option D'];
                }
                break;

            case 'true_false':
                // Use language-appropriate true/false labels from language files
                global $langFalse, $langTrue;
                $formatted['options'] = [$langFalse, $langTrue]; // Order: False=0, True=1
                break;
        }

        return $formatted;
    }

    /**
     * Map AI question types to OpenEclass question types
     */
    private function mapQuestionType(string $aiType): string {
        $mapping = [
            'multiple_choice' => UNIQUE_ANSWER,
            'true_false' => TRUE_FALSE,
            'fill_blank' => FILL_IN_BLANKS_TOLERANT,
            'essay' => FREE_TEXT,
            'short_answer' => FREE_TEXT
        ];

        return $mapping[$aiType] ?? 'multiple_choice';
    }

}
