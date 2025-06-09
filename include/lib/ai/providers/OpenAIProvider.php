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
     * Load hardcoded configuration for development
     * TODO: Remove this when admin configuration is implemented
     */
    protected function loadHardcodedConfig() {
        // TODO: Replace with database configuration when admin pages are ready
        // For now, using hardcoded values for development
        
        // IMPORTANT: Replace with your actual OpenAI API key for testing
        $this->apiKey = 'YOUR_OPENAI_API_KEY_HERE'; // TODO: Get from database: $this->getProviderConfig('openai', 'api_key')
        $this->modelName = 'gpt-4o-mini'; // TODO: Get from database: $this->getProviderConfig('openai', 'model_name') 
        $this->endpointUrl = self::DEFAULT_ENDPOINT;
        $this->enabled = true; // TODO: Get from database: $this->getProviderConfig('openai', 'enabled')
        
        // For development/testing purposes only
        // Remove this warning when configuration is implemented
        if ($this->apiKey === 'YOUR_OPENAI_API_KEY_HERE') {
            error_log("WARNING: OpenAI provider is using placeholder API key. Update OpenAIProvider.php with actual key for testing.");
        }
    }
    
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
        // TODO: When admin is ready, this could be fetched dynamically from OpenAI API
        return [
            'gpt-4o' => 'GPT-4o (Latest)',
            'gpt-4o-mini' => 'GPT-4o Mini (Fast & Efficient)',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo'
        ];
    }
    
    /**
     * Check if OpenAI service is healthy
     */
    public function isHealthy(): bool {
        try {
            // Make a simple test request
            $testData = [
                'model' => $this->modelName,
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello, just testing connectivity.']
                ],
                'max_tokens' => 10
            ];
            
            $response = $this->makeApiRequest($this->endpointUrl, $testData);
            return isset($response['choices']) && !empty($response['choices']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Make API request to OpenAI
     */
    protected function makeApiRequest(string $endpoint, array $data, string $method = 'POST'): array {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => ($method === 'POST'),
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
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
     * Format a single question into OpenEclass format
     */
    private function formatSingleQuestion(array $questionData): array {
        $formatted = [
            'question_text' => $questionData['question'] ?? '',
            'question_type' => $this->mapQuestionType($questionData['type'] ?? 'multiple_choice'),
            'difficulty' => $questionData['difficulty'] ?? 'medium',
            'correct_answer' => $questionData['correct_answer'] ?? '',
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
                $formatted['options'] = ['True', 'False'];
                break;
        }
        
        return $formatted;
    }
    
    /**
     * Map AI question types to OpenEclass question types
     */
    private function mapQuestionType(string $aiType): string {
        $mapping = [
            'multiple_choice' => 'multiple_choice',
            'true_false' => 'true_false', 
            'fill_blank' => 'fill_in_blanks',
            'essay' => 'free_text',
            'short_answer' => 'free_text'
        ];
        
        return $mapping[$aiType] ?? 'multiple_choice';
    }
    
    /**
     * TODO: Method to get provider configuration from database
     * This will replace hardcoded values when admin system is ready
     */
    private function getProviderConfig(string $provider, string $key) {
        // TODO: Implement database query
        // return Database::get()->querySingle("SELECT value FROM ai_provider_config WHERE provider = ? AND config_key = ?", [$provider, $key])->value;
        return null;
    }
}