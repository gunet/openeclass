<?php

require_once dirname(__DIR__) . '/AIProviderFactory.php';

/**
 * Main AI Service class for OpenEclass
 * Orchestrates AI functionality across different modules
 */
class AIService {
    
    private $primaryProvider;
    private $courseId;
    private $userId;
    
    /**
     * Constructor
     * 
     * @param int|null $courseId Current course ID
     * @param int|null $userId Current user ID
     */
    public function __construct(?int $courseId = null, ?int $userId = null) {
        $this->courseId = $courseId;
        $this->userId = $userId;
        $this->primaryProvider = AIProviderFactory::getPrimaryProvider();
    }
    
    /**
     * Check if AI functionality is available
     * 
     * @return bool True if AI is available and configured
     */
    public function isAvailable(): bool {
        return $this->primaryProvider !== null && AIProviderFactory::hasEnabledProviders();
    }
    
    /**
     * Check if AI is enabled for the current course
     * TODO: Implement course-specific AI permissions when admin system is ready
     * 
     * @return bool True if AI is enabled for current course
     */
    public function isEnabledForCourse(): bool {
        if (!$this->isAvailable() || !$this->courseId) {
            return false;
        }
        
        // TODO: Check course-specific AI permissions from database
        // For now, return true if AI is generally available
        return true;
    }
    
    /**
     * Check if user has permission to use AI features
     * TODO: Implement user permission checks when admin system is ready
     * 
     * @return bool True if user can use AI
     */
    public function canUserUseAI(): bool {
        if (!$this->isAvailable() || !$this->userId) {
            return false;
        }
        
        // TODO: Check user permissions from database
        // For now, allow all users if AI is available
        return true;
    }
    
    /**
     * Generate questions using AI
     * 
     * @param string $content Source content
     * @param array $options Generation options
     * @return array Generated questions
     * @throws Exception If AI is not available or generation fails
     */
    public function generateQuestions(string $content, array $options = []): array {
        if (!$this->isAvailable()) {
            throw new Exception("AI functionality is not available");
        }
        
        if (!$this->canUserUseAI()) {
            throw new Exception("User does not have permission to use AI");
        }
        
        if (!$this->isEnabledForCourse()) {
            throw new Exception("AI is not enabled for this course");
        }
        
        // Log the AI usage
        $this->logAIUsage('question_generation', [
            'content_length' => strlen($content),
            'options' => $options
        ]);
        
        try {
            return $this->primaryProvider->generateQuestions($content, $options);
        } catch (Exception $e) {
            $this->logAIError('question_generation', $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate a single question using AI
     * 
     * @param string $content Source content
     * @param string $questionType Type of question
     * @param string $difficulty Difficulty level
     * @return array Single generated question
     */
    public function generateSingleQuestion(string $content, string $questionType = 'multiple_choice', string $difficulty = 'medium'): array {
        if (!$this->isAvailable()) {
            throw new Exception("AI functionality is not available");
        }
        
        return $this->primaryProvider->generateSingleQuestion($content, $questionType, $difficulty);
    }
    
    /**
     * Get available question types for AI generation
     * 
     * @return array Array of question types with display names
     */
    public function getAvailableQuestionTypes(): array {
        return [
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False', 
            'fill_in_blanks' => 'Fill in the Blanks',
            'free_text' => 'Essay/Short Answer'
        ];
    }
    
    /**
     * Get available difficulty levels
     * 
     * @return array Array of difficulty levels
     */
    public function getAvailableDifficultyLevels(): array {
        return [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard'
        ];
    }
    
    /**
     * Get AI provider information for display
     * 
     * @return array Provider information
     */
    public function getProviderInfo(): array {
        if (!$this->primaryProvider) {
            return ['name' => 'None', 'type' => 'none', 'available' => false];
        }
        
        return [
            'name' => $this->primaryProvider->getDisplayName(),
            'type' => $this->primaryProvider->getProviderType(), 
            'available' => true,
            'models' => $this->primaryProvider->getAvailableModels()
        ];
    }
    
    /**
     * Test AI connectivity
     * 
     * @return array Test result
     */
    public function testConnection(): array {
        if (!$this->primaryProvider) {
            return ['status' => 'error', 'message' => 'No AI provider configured'];
        }
        
        try {
            if ($this->primaryProvider->isHealthy()) {
                return ['status' => 'success', 'message' => 'AI service is working correctly'];
            } else {
                return ['status' => 'error', 'message' => 'AI service is not responding'];
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Set course context
     * 
     * @param int $courseId Course ID
     */
    public function setCourseId(int $courseId): void {
        $this->courseId = $courseId;
    }
    
    /**
     * Set user context
     * 
     * @param int $userId User ID
     */
    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }
    
    /**
     * Log AI usage for monitoring and analytics
     * TODO: Implement proper database logging when admin system is ready
     * 
     * @param string $action Action performed
     * @param array $metadata Additional metadata
     */
    private function logAIUsage(string $action, array $metadata = []): void {
        // TODO: Implement database logging
        // Database::get()->query("INSERT INTO ai_usage_log (user_id, course_id, action, metadata, created_at) VALUES (?, ?, ?, ?, ?)", 
        //     [$this->userId, $this->courseId, $action, json_encode($metadata), date('Y-m-d H:i:s')]);
        
        // For now, just log to error log
        error_log("AI Usage - User: {$this->userId}, Course: {$this->courseId}, Action: {$action}, Metadata: " . json_encode($metadata));
    }
    
    /**
     * Log AI errors for debugging
     * TODO: Implement proper error logging when admin system is ready
     * 
     * @param string $action Action that failed
     * @param string $error Error message
     */
    private function logAIError(string $action, string $error): void {
        // TODO: Implement database error logging
        // Database::get()->query("INSERT INTO ai_error_log (user_id, course_id, action, error_message, created_at) VALUES (?, ?, ?, ?, ?)", 
        //     [$this->userId, $this->courseId, $action, $error, date('Y-m-d H:i:s')]);
        
        // For now, just log to error log
        error_log("AI Error - User: {$this->userId}, Course: {$this->courseId}, Action: {$action}, Error: {$error}");
    }
}