<?php

/**
 * Interface for AI providers in OpenEclass
 * Defines the contract that all AI providers must implement
 */
interface AIProviderInterface {
    /**
     * Generate questions from given content
     * 
     * @param string $content The source content to generate questions from
     * @param array $options Configuration options (question_count, difficulty, type, etc.)
     * @return array Array of generated questions with metadata
     */
    public function generateQuestions(string $content, array $options = []): array;
    
    /**
     * Validate the API key for this provider
     * 
     * @return bool True if API key is valid, false otherwise
     */
    public function validateApiKey(): bool;
    
    /**
     * Get list of available models for this provider
     * 
     * @return array Array of model names/identifiers
     */
    public function getAvailableModels(): array;
    
    /**
     * Check if the provider service is healthy and reachable
     * 
     * @return bool True if service is healthy, false otherwise
     */
    public function isHealthy(): bool;
    
    /**
     * Get the provider type identifier
     * 
     * @return string Provider type (openai, anthropic, gemini, custom)
     */
    public function getProviderType(): string;
    
    /**
     * Get the display name for this provider
     * 
     * @return string Human-readable provider name
     */
    public function getDisplayName(): string;
    
    /**
     * Generate a single question from content
     * 
     * @param string $content The source content
     * @param string $questionType Type of question (multiple_choice, true_false, fill_blank, essay)
     * @param string $difficulty Difficulty level (easy, medium, hard)
     * @return array Single question data
     */
    public function generateSingleQuestion(string $content, string $questionType = 'multiple_choice', string $difficulty = 'medium'): array;
    
    /**
     * Evaluate a text response using AI
     * 
     * @param string $prompt The evaluation prompt
     * @param array $options Request options (temperature, max_tokens, etc.)
     * @return array AI response data
     */
    public function evaluateText(string $prompt, array $options = []): array;
    
    /**
     * Extract course data from content (syllabus text or manual prompt)
     * 
     * @param string $content The source content (syllabus text or course description prompt)
     * @param string $contentType Type of content ('syllabus' or 'prompt')
     * @param array $options Configuration options for extraction
     * @return array Extracted course data formatted for OpenEclass
     */
    public function extractCourseData(string $content, string $contentType = 'prompt', array $options = []): array;
}