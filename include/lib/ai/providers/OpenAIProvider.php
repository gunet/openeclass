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
            $jsonData = json_encode($data);
            
            // Check if JSON encoding failed
            if ($jsonData === false) {
                throw new Exception('JSON encoding failed: ' . json_last_error_msg());
            }
            
            $curlOptions[CURLOPT_POSTFIELDS] = $jsonData;
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

    /**
     * Evaluate a text response using AI
     * 
     * @param string $prompt The evaluation prompt
     * @param array $options Request options (temperature, max_tokens, etc.)
     * @return array AI response data
     */
    public function evaluateText(string $prompt, array $options = []): array {
        $requestData = [
            'model' => $this->modelName,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an educational assessment assistant that evaluates student responses fairly and consistently. Always respond with valid JSON format.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $options['max_tokens'] ?? 1000,
            'temperature' => $options['temperature'] ?? 0.3,
            'response_format' => $options['response_format'] ?? ['type' => 'json_object']
        ];

        return $this->makeApiRequest($this->getDefaultEndpoint(), $requestData);
    }

    /**
     * Extract course data from content (syllabus text or manual prompt)
     * 
     * @param string $content The source content (syllabus text or course description prompt)
     * @param string $contentType Type of content ('syllabus' or 'prompt')
     * @param array $options Configuration options for extraction
     * @return array Extracted course data formatted for OpenEclass
     */
    public function extractCourseData(string $content, string $contentType = 'prompt', array $options = []): array {
        $systemPrompt = $this->buildCourseExtractionSystemPrompt($contentType);
        $userPrompt = $this->buildCourseExtractionPrompt($content, $contentType, $options);

        $requestData = [
            'model' => $this->modelName,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.3,
            'tools' => [$this->getCourseExtractionToolDefinition()],
            'tool_choice' => ['type' => 'function', 'function' => ['name' => 'extract_course_data']]
        ];

        $response = $this->makeApiRequest($this->getDefaultEndpoint(), $requestData);
        return $this->formatCourseDataFunctionResponse($response, $options);
    }

    /**
     * Build system prompt for course data extraction
     */
    private function buildCourseExtractionSystemPrompt(string $contentType): string {
        $basePrompt = "You are an expert educational assistant that extracts structured course information for learning management systems. ";
        
        if ($contentType === 'syllabus') {
            $basePrompt .= "You analyze syllabus documents and extract key course information including title, description, objectives, prerequisites, and metadata.";
        } else {
            $basePrompt .= "You generate comprehensive course information based on user requirements and educational best practices.";
        }
        
        $basePrompt .= " Always respond with valid JSON format containing the requested course fields.";
        
        return $basePrompt;
    }

    /**
     * Build user prompt for course data extraction
     */
    private function buildCourseExtractionPrompt(string $content, string $contentType, array $options): string {
        if ($contentType === 'syllabus') {
            $prompt = "Extract course information from the following syllabus content:\n\n";
            $prompt .= "SYLLABUS CONTENT:\n" . $content . "\n\n";
        } else {
            $prompt = "Generate comprehensive course information based on the following requirements:\n\n";
            $prompt .= "COURSE REQUIREMENTS:\n" . $content . "\n\n";
        }
        
        $prompt .= "Guidelines:\n";
        $prompt .= "- Extract information accurately from the provided content\n";
        $prompt .= "- Use proper HTML formatting in the description field\n";
        $prompt .= "- Choose appropriate view_type based on course structure\n";
        $prompt .= "- Set formvisible to 1 (registration required) as default for academic courses\n";
        $prompt .= "- Generate realistic course codes if not provided\n";
        $prompt .= "- Detect and respond in the same language as the input content\n";
        $prompt .= "- Ensure all fields are present even if extracted from context or generated\n";
        
        return $prompt;
    }

    /**
     * Get the function/tool definition for course data extraction
     */
    private function getCourseExtractionToolDefinition(): array {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'extract_course_data',
                'description' => 'Extract or generate structured course information for a learning management system',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'title' => [
                            'type' => 'string',
                            'description' => 'Course title (max 255 characters)',
                            'maxLength' => 255
                        ],
                        'public_code' => [
                            'type' => 'string',
                            'description' => 'Course code/identifier (uppercase letters and numbers only, max 20 chars)',
                            'maxLength' => 20,
                            'pattern' => '^[A-Z0-9]*$'
                        ],
                        'description' => [
                            'type' => 'string',
                            'description' => 'Detailed HTML course description including objectives, content, and prerequisites'
                        ],
                        'prof_names' => [
                            'type' => 'string',
                            'description' => 'Instructor names if mentioned'
                        ],
                        'language' => [
                            'type' => 'string',
                            'description' => 'Course language code',
                            'enum' => ['el', 'en', 'fr', 'de', 'es', 'it']
                        ],
                        'view_type' => [
                            'type' => 'string',
                            'description' => 'Course format type',
                            'enum' => ['simple', 'units', 'activity', 'wall', 'flippedclassroom']
                        ],
                        'formvisible' => [
                            'type' => 'integer',
                            'description' => 'Course access level: 2=open, 1=registration required, 0=closed',
                            'enum' => [0, 1, 2]
                        ],
                        'course_license' => [
                            'type' => 'integer',
                            'description' => 'License type: 0=no license, 10=copyright, or CC license ID',
                            'minimum' => 0,
                            'maximum' => 10
                        ],
                        'keywords' => [
                            'type' => 'string',
                            'description' => 'Comma-separated relevant keywords for the course'
                        ]
                    ],
                    'required' => ['title', 'description', 'language', 'view_type', 'formvisible']
                ]
            ]
        ];
    }

    /**
     * Format course data response from OpenAI function calling
     */
    private function formatCourseDataFunctionResponse(array $apiResponse, array $options): array {
        if (!isset($apiResponse['choices'][0]['message']['tool_calls'][0]['function']['arguments'])) {
            throw new Exception("Invalid function response format from OpenAI");
        }

        $functionArgs = $apiResponse['choices'][0]['message']['tool_calls'][0]['function']['arguments'];
        $courseData = json_decode($functionArgs, true);

        if (!$courseData) {
            throw new Exception("Invalid JSON format in OpenAI function response");
        }

        // Format and validate data according to OpenEclass requirements
        $formattedData = [
            'title' => $courseData['title'] ?? 'Untitled Course',
            'public_code' => $courseData['public_code'] ?? '',
            'description' => $courseData['description'] ?? '',
            'prof_names' => $courseData['prof_names'] ?? '',
            'language' => $courseData['language'] ?? 'en',
            'view_type' => $courseData['view_type'] ?? 'units',
            'formvisible' => intval($courseData['formvisible'] ?? 1),
            'course_license' => intval($courseData['course_license'] ?? 0),
            'keywords' => $courseData['keywords'] ?? '',
            'provider' => $this->getProviderType(),
            'generated_at' => date('Y-m-d H:i:s')
        ];

        return $formattedData;
    }

}
