<?php

require_once dirname(__DIR__) . '/AIProviderInterface.php';

/**
 * Abstract base class for AI providers
 * Provides common functionality and structure for all AI providers
 */
abstract class AbstractAIProvider implements AIProviderInterface {
    
    protected $apiKey;
    protected $modelName;
    protected $endpointUrl;
    protected $enabled;
    protected $providerConfig;
    
    /**
     * Constructor
     * 
     * @param array $config Provider configuration
     */
    public function __construct(array $config = []) {
        $this->apiKey = $config['api_key'] ?? '';
        $this->modelName = $config['model_name'] ?? $this->getDefaultModel();
        $this->endpointUrl = $config['endpoint_url'] ?? $this->getDefaultEndpoint();
        $this->enabled = $config['enabled'] ?? true;
        $this->providerConfig = $config;
        
        // Load hardcoded config for development
        $this->loadHardcodedConfig();
    }
    
    /**
     * Load hardcoded configuration for development
     * TODO: Remove this method when admin configuration is ready
     */
    abstract protected function loadHardcodedConfig();
    
    /**
     * Get the default model for this provider
     * 
     * @return string Default model name
     */
    abstract protected function getDefaultModel(): string;
    
    /**
     * Get the default API endpoint for this provider
     * 
     * @return string Default endpoint URL
     */
    abstract protected function getDefaultEndpoint(): string;
    
    /**
     * Make an API request to the provider
     * 
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param string $method HTTP method
     * @return array Response data
     * @throws Exception On API errors
     */
    abstract protected function makeApiRequest(string $endpoint, array $data, string $method = 'POST'): array;
    
    /**
     * Format the response from the API into OpenEclass question format
     * 
     * @param array $apiResponse Raw API response
     * @param array $options Original request options
     * @return array Formatted questions
     */
    abstract protected function formatQuestionsResponse(array $apiResponse, array $options): array;
    
    /**
     * Generate questions from content
     * 
     * @param string $content Source content
     * @param array $options Generation options
     * @return array Generated questions
     */
    public function generateQuestions(string $content, array $options = []): array {
        if (!$this->enabled) {
            throw new Exception("Provider is disabled");
        }
        
        if (empty($this->apiKey)) {
            throw new Exception("API key not configured");
        }
        
        // Set default options
        $options = array_merge([
            'question_count' => 5,
            'difficulty' => 'medium',
            'question_types' => ['multiple_choice'],
            'language' => 'el' // Greek by default for OpenEclass
        ], $options);
        
        $prompt = $this->buildQuestionGenerationPrompt($content, $options);
        $requestData = $this->buildApiRequest($prompt, $options);
        
        try {
            $response = $this->makeApiRequest($this->endpointUrl, $requestData);
            return $this->formatQuestionsResponse($response, $options);
        } catch (Exception $e) {
            error_log("AI Provider Error ({$this->getProviderType()}): " . $e->getMessage());
            throw new Exception("Failed to generate questions: " . $e->getMessage());
        }
    }
    
    /**
     * Generate a single question
     * 
     * @param string $content Source content
     * @param string $questionType Type of question
     * @param string $difficulty Difficulty level
     * @return array Single question
     */
    public function generateSingleQuestion(string $content, string $questionType = 'multiple_choice', string $difficulty = 'medium'): array {
        $options = [
            'question_count' => 1,
            'difficulty' => $difficulty,
            'question_types' => [$questionType]
        ];
        
        $questions = $this->generateQuestions($content, $options);
        return !empty($questions) ? $questions[0] : [];
    }
    
    /**
     * Validate API key by making a test request
     * 
     * @return bool True if valid
     */
    public function validateApiKey(): bool {
        if (empty($this->apiKey)) {
            return false;
        }
        
        try {
            return $this->isHealthy();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Build the prompt for question generation
     * 
     * @param string $content Source content
     * @param array $options Generation options
     * @return string Generated prompt
     */
    protected function buildQuestionGenerationPrompt(string $content, array $options): string {
        $questionTypes = implode(', ', $options['question_types']);
        $difficulty = $options['difficulty'];
        $count = $options['question_count'];
        $language = $options['language'] === 'el' ? 'Greek' : 'English';
        
        return "Generate {$count} educational questions in {$language} based on the following content. " .
               "Question types: {$questionTypes}. Difficulty level: {$difficulty}.\n\n" .
               "Content:\n{$content}\n\n" .
               "Return the questions in JSON format with the following structure:\n" .
               "{\n" .
               "  \"questions\": [\n" .
               "    {\n" .
               "      \"question\": \"The question text\",\n" .
               "      \"type\": \"multiple_choice|true_false|fill_blank|essay\",\n" .
               "      \"difficulty\": \"easy|medium|hard\",\n" .
               "      \"options\": [\"Option A\", \"Option B\", \"Option C\", \"Option D\"], // for multiple choice\n" .
               "      \"correct_answer\": \"Correct answer or option index\",\n" .
               "      \"explanation\": \"Explanation of the correct answer\"\n" .
               "    }\n" .
               "  ]\n" .
               "}";
    }
    
    /**
     * Build the API request data structure
     * 
     * @param string $prompt The generated prompt
     * @param array $options Request options
     * @return array API request data
     */
    abstract protected function buildApiRequest(string $prompt, array $options): array;
    
    /**
     * Get configuration value with fallback
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed Configuration value
     */
    protected function getConfig(string $key, $default = null) {
        return $this->providerConfig[$key] ?? $default;
    }
    
    /**
     * Log API usage for monitoring
     * TODO: Implement proper logging when admin system is ready
     * 
     * @param string $action Action performed
     * @param array $metadata Additional metadata
     */
    protected function logApiUsage(string $action, array $metadata = []) {
        // TODO: Implement logging to database
        // For now, just log to error log
        error_log("AI API Usage - Provider: {$this->getProviderType()}, Action: {$action}, Metadata: " . json_encode($metadata));
    }
}