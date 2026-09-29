<?php

require_once __DIR__ . '/OpenAIProvider.php';

/**
 * Custom / Other AI Provider implementation for OpenEclass AI integration
 * Handles OpenAI Chat Completions compatible custom endpoints (e.g. self-hosted models, Ollama, vLLM, LiteLLM, Groq, etc.)
 */
class CustomProvider extends OpenAIProvider {

    /**
     * Get provider type identifier
     */
    public function getProviderType(): string {
        return 'custom';
    }

    /**
     * Get display name
     */
    public function getDisplayName(): string {
        return 'Other';
    }

    /**
     * Get default model
     */
    protected function getDefaultModel(): string {
        return $this->modelName ?? 'custom';
    }

    /**
     * Get default endpoint
     */
    protected function getDefaultEndpoint(): string {
        return $this->endpointUrl ?? '';
    }

    /**
     * Get available models for Custom Provider
     */
    public function getAvailableModels(): array {
        if (!empty($this->modelName)) {
            return [$this->modelName => $this->modelName];
        }
        return ['custom' => 'Custom Model'];
    }

    /**
     * Check if Custom AI service is healthy and reachable
     */
    public function isHealthy(): bool {
        $endpoint = $this->endpointUrl ?: $this->getDefaultEndpoint();
        if (empty($endpoint)) {
            return false;
        }

        // Try models endpoint first (e.g. replacing /chat/completions with /models or appending /models to /v1)
        try {
            $modelsEndpoint = null;
            if (strpos($endpoint, '/chat/completions') !== false) {
                $modelsEndpoint = str_replace('/chat/completions', '/models', $endpoint);
            } elseif (substr($endpoint, -3) === '/v1' || substr($endpoint, -4) === '/v1/') {
                $modelsEndpoint = rtrim($endpoint, '/') . '/models';
            }

            if ($modelsEndpoint) {
                $response = $this->makeApiRequest($modelsEndpoint, [], 'GET');
                if (isset($response['data']) && is_array($response['data'])) {
                    return true;
                }
            }
        } catch (Exception $e) {
            // Models endpoint may not be supported by custom endpoint; proceed to test completion
        }

        // Fallback: test minimal chat completion
        try {
            $testData = [
                'model' => !empty($this->modelName) ? $this->modelName : 'default',
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello']
                ],
                'max_tokens' => 5
            ];
            $response = $this->makeApiRequest($endpoint, $testData, 'POST');
            return isset($response['choices']) && is_array($response['choices']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Make API request to Custom OpenAI-compatible endpoint
     */
    protected function makeApiRequest(string $endpoint, array $data, string $method = 'POST'): array {
        if (empty($endpoint)) {
            $endpoint = $this->endpointUrl ?: $this->getDefaultEndpoint();
        }

        if (empty($endpoint)) {
            throw new Exception("Endpoint URL is not configured for custom AI provider");
        }

        $headers = [];
        if (!empty($this->apiKey)) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        $ch = curl_init();
        $curlOptions = [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true
        ];

        if ($method === 'POST') {
            $curlOptions[CURLOPT_POST] = true;
            $jsonData = json_encode($data);

            if ($jsonData === false) {
                throw new Exception('JSON encoding failed: ' . json_last_error_msg());
            }

            $curlOptions[CURLOPT_POSTFIELDS] = $jsonData;
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'X-Content-Type-Options: nosniff';
        } else {
            $curlOptions[CURLOPT_HTTPGET] = true;
        }

        $curlOptions[CURLOPT_HTTPHEADER] = $headers;
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
            $errorMessage = $errorData['error']['message'] ?? ($errorData['message'] ?? ("HTTP error: " . $httpCode));
            throw new Exception("Custom AI API error: " . $errorMessage);
        }

        $decodedResponse = json_decode($response, true);
        if (!$decodedResponse) {
            throw new Exception("Invalid JSON response from Custom AI API");
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
     * Build API request data structure for Custom OpenAI-compatible provider
     */
    protected function buildApiRequest(string $prompt, array $options): array {
        $maxTokens = $options['max_tokens'] ?? max(4096, ($options['question_count'] ?? 5) * 1000);

        return [
            'model' => $this->modelName,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "You are an educational assistant that generates high-quality assessment questions.\n\n" . $prompt . "\n\nIMPORTANT: Output ONLY valid, parsable JSON without any markdown formatting, preamble, or comments."
                ]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.7
        ];
    }

    /**
     * Generate questions from content with automatic retry and error recovery
     */
    public function generateQuestions(string $content, array $options = []): array {
        global $langAPIKeyNotConfigured, $langProviderDisabled;

        if (!$this->enabled) {
            throw new Exception($langProviderDisabled);
        }

        if (empty($this->apiKey)) {
            throw new Exception($langAPIKeyNotConfigured);
        }

        // Set default options
        $options = array_merge([
            'question_count' => 5,
            'difficulty' => 'medium',
            'question_types' => ['multiple_choice'],
            'language' => 'el'
        ], $options);

        $prompt = $this->buildQuestionGenerationPrompt($content, $options);
        $requestData = $this->buildApiRequest($prompt, $options);
        $endpoint = $this->endpointUrl ?: $this->getDefaultEndpoint();

        try {
            $response = $this->makeApiRequest($endpoint, $requestData);
            $questions = $this->formatQuestionsResponse($response, $options);
            if (!empty($questions)) {
                return $questions;
            }
        } catch (Exception $e) {
            error_log("CustomProvider generateQuestions attempt 1 failed: " . $e->getMessage() . ". Retrying with alternative prompt structure...");
        }

        // Attempt 2: retry with explicit separate system role if supported, or alternative parameters
        $fallbackRequest = [
            'model' => $this->modelName,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an educational assistant. You must respond with valid JSON containing a "questions" array only.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => max(4096, ($options['question_count'] ?? 5) * 1000),
            'temperature' => 0.4
        ];

        $response = $this->makeApiRequest($endpoint, $fallbackRequest);
        return $this->formatQuestionsResponse($response, $options);
    }

    /**
     * Format Custom AI response into OpenEclass question format
     */
    protected function formatQuestionsResponse(array $apiResponse, array $options): array {
        if (!isset($apiResponse['choices'][0]['message']['content'])) {
            throw new Exception("Invalid response format from Custom AI provider");
        }

        $rawContent = $apiResponse['choices'][0]['message']['content'];
        $questionsList = $this->extractAndNormalizeQuestions($rawContent);

        if ($questionsList === null || empty($questionsList)) {
            error_log("Failed to parse JSON in Custom AI response. Raw content: " . substr($rawContent, 0, 500));
            throw new Exception("Invalid JSON format in Custom AI response");
        }

        $formattedQuestions = [];
        foreach ($questionsList as $question) {
            if (is_array($question)) {
                $formattedQuestions[] = $this->formatSingleQuestion($question);
            }
        }

        if (empty($formattedQuestions)) {
            throw new Exception("No valid questions could be extracted from Custom AI response");
        }

        return $formattedQuestions;
    }

    /**
     * Robustly extract and normalize JSON questions list from LLM output,
     * with support for markdown code blocks, comments, trailing commas, and truncated responses.
     */
    protected function extractAndNormalizeQuestions(string $rawContent): ?array {
        $content = trim($rawContent);

        // Strip markdown code fences if present
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $content, $matches)) {
            $content = trim($matches[1]);
        } elseif (preg_match('/```(?:json)?\s*([\s\S]*)$/i', $content, $matches)) {
            // Unclosed code fence from cut-off output
            $content = trim($matches[1]);
        }

        // 1. Try direct decode
        $data = json_decode($content, true);
        if ($data !== null) {
            $normalized = $this->normalizeQuestionsData($data);
            if ($normalized !== null && !empty($normalized)) {
                return $normalized;
            }
        }

        // 2. Extract substring from first { or [ to last } or ]
        $firstBrace = strpos($content, '{');
        $firstBracket = strpos($content, '[');

        if ($firstBrace !== false && ($firstBracket === false || $firstBrace < $firstBracket)) {
            $start = $firstBrace;
            $end = strrpos($content, '}');
        } elseif ($firstBracket !== false) {
            $start = $firstBracket;
            $end = strrpos($content, ']');
        } else {
            $start = false;
            $end = false;
        }

        if ($start !== false && $end !== false && $end > $start) {
            $extracted = substr($content, $start, $end - $start + 1);
            $data = json_decode($extracted, true);
            if ($data !== null) {
                $normalized = $this->normalizeQuestionsData($data);
                if ($normalized !== null && !empty($normalized)) {
                    return $normalized;
                }
            }
        }

        // 3. Clean comments (// ... and /* ... */) and trailing commas
        $cleaned = preg_replace('#^\s*//.*$#m', '', $content);
        $cleaned = preg_replace('#,\s*//.*$#m', ',', $cleaned);
        $cleaned = preg_replace('#//.*$#m', '', $cleaned);
        $cleaned = preg_replace('#/\*[\s\S]*?\*/#', '', $cleaned);
        $cleaned = preg_replace('#,\s*([\}\]])#', '$1', $cleaned);

        $data = json_decode($cleaned, true);
        if ($data !== null) {
            $normalized = $this->normalizeQuestionsData($data);
            if ($normalized !== null && !empty($normalized)) {
                return $normalized;
            }
        }

        // 4. Try repairing truncated/cut-off JSON by closing the array/object
        $lastBrace = strrpos($cleaned, '}');
        if ($lastBrace !== false) {
            $truncated = substr($cleaned, 0, $lastBrace + 1);
            if (strpos($truncated, '[') !== false) {
                $test1 = $truncated . ']}';
                $data = json_decode($test1, true);
                if ($data !== null) {
                    $normalized = $this->normalizeQuestionsData($data);
                    if ($normalized !== null && !empty($normalized)) {
                        return $normalized;
                    }
                }

                $test2 = $truncated . ']';
                $data = json_decode($test2, true);
                if ($data !== null) {
                    $normalized = $this->normalizeQuestionsData($data);
                    if ($normalized !== null && !empty($normalized)) {
                        return $normalized;
                    }
                }
            }
        }

        // 5. Extract all individual complete question objects using regex
        $pattern = '/\{\s*"question"\s*:\s*"(?:[^"\\\\]|\\\\.)*"[\s\S]*?\}/u';
        if (preg_match_all($pattern, $cleaned, $matches)) {
            $questions = [];
            foreach ($matches[0] as $match) {
                $qClean = preg_replace('#,\s*([\}\]])#', '$1', $match);
                $q = json_decode($qClean, true);
                if ($q && is_array($q) && isset($q['question'])) {
                    $questions[] = $q;
                }
            }
            if (!empty($questions)) {
                return $questions;
            }
        }

        return null;
    }

    /**
     * Normalize decoded JSON data structure into an array of question items
     */
    protected function normalizeQuestionsData($data): ?array {
        if (!is_array($data)) {
            return null;
        }

        if (isset($data['questions']) && is_array($data['questions'])) {
            return $data['questions'];
        }

        // If data is a list of question objects (root-level array)
        if (isset($data[0]) && (is_array($data[0]) || is_object($data[0]))) {
            return (array)$data;
        }

        // If data has another wrapper key like "quiz", "data", "items", "results"
        foreach ($data as $key => $val) {
            if (is_array($val) && isset($val[0]) && (is_array($val[0]) || is_object($val[0]))) {
                return $val;
            }
        }

        // Single question object
        if (isset($data['question'])) {
            return [$data];
        }

        return null;
    }

    /**
     * Evaluate a text response using AI
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
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.3
        ];

        return $this->makeApiRequest($this->endpointUrl ?: $this->getDefaultEndpoint(), $requestData);
    }

    /**
     * Extract course data from content (syllabus text or manual prompt)
     */
    public function extractCourseData(string $content, string $contentType = 'prompt', array $options = []): array {
        $systemPrompt = $this->buildCourseExtractionSystemPrompt($contentType);
        $userPrompt = $this->buildCourseExtractionPrompt($content, $contentType, $options);
        $endpoint = $this->endpointUrl ?: $this->getDefaultEndpoint();

        // First attempt with tool calling
        try {
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
                'max_tokens' => $options['max_tokens'] ?? 4096,
                'temperature' => $options['temperature'] ?? 0.3,
                'tools' => [$this->getCourseExtractionToolDefinition()],
                'tool_choice' => ['type' => 'function', 'function' => ['name' => 'extract_course_data']]
            ];

            $response = $this->makeApiRequest($endpoint, $requestData);
            return $this->formatCourseDataResponse($response, $options);
        } catch (Exception $e) {
            // Fallback to JSON completion without tools
            $fallbackSystemPrompt = $systemPrompt . "\nRespond ONLY with a valid JSON object containing fields: title, public_code, description, prof_names, language, view_type, formvisible, course_license, keywords, syllabus_sections.";
            $requestData = [
                'model' => $this->modelName,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $fallbackSystemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt
                    ]
                ],
                'max_tokens' => $options['max_tokens'] ?? 4096,
                'temperature' => $options['temperature'] ?? 0.3
            ];

            $response = $this->makeApiRequest($endpoint, $requestData);
            return $this->formatCourseDataResponse($response, $options);
        }
    }

    /**
     * Format course data response from tool call or JSON content
     */
    protected function formatCourseDataResponse(array $apiResponse, array $options): array {
        $courseData = null;
        if (isset($apiResponse['choices'][0]['message']['tool_calls'][0]['function']['arguments'])) {
            $functionArgs = $apiResponse['choices'][0]['message']['tool_calls'][0]['function']['arguments'];
            $courseData = is_array($functionArgs) ? $functionArgs : json_decode($functionArgs, true);
        } elseif (isset($apiResponse['choices'][0]['message']['content'])) {
            $content = trim($apiResponse['choices'][0]['message']['content']);
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
            $courseData = json_decode($content, true);
        }

        if (!$courseData) {
            throw new Exception("Invalid course extraction response format from Custom AI provider");
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

        // Add structured syllabus sections if present
        if (isset($courseData['syllabus_sections']) && is_array($courseData['syllabus_sections'])) {
            $formattedData['syllabus_sections'] = $courseData['syllabus_sections'];
        }

        return $formattedData;
    }
}
