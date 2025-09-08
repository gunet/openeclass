<?php

require_once __DIR__ . '/AbstractAIProvider.php';

/**
 * Google Gemini Provider implementation for OpenEclass AI integration
 * TODO: Implement full functionality when needed
 */
class GeminiProvider extends AbstractAIProvider {
    
    private const DEFAULT_MODEL = 'gemini-1.5-flash';
    private const DEFAULT_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    
    public function getProviderType(): string {
        return 'gemini';
    }
    
    public function getDisplayName(): string {
        return 'Google Gemini';
    }
    
    protected function getDefaultModel(): string {
        return self::DEFAULT_MODEL;
    }
    
    protected function getDefaultEndpoint(): string {
        return self::DEFAULT_ENDPOINT;
    }
    
    public function getAvailableModels(): array {
        return [
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
            'gemini-1.0-pro' => 'Gemini 1.0 Pro'
        ];
    }
    
    public function isHealthy(): bool {
        // TODO: Implement health check
        return false;
    }
    
    protected function makeApiRequest(string $endpoint, array $data, string $method = 'POST'): array {
        // TODO: Implement Gemini API request
        throw new Exception("Gemini provider not yet implemented");
    }
    
    protected function buildApiRequest(string $prompt, array $options): array {
        // TODO: Implement Gemini request format
        return [];
    }
    
    protected function formatQuestionsResponse(array $apiResponse, array $options): array {
        // TODO: Implement response formatting
        return [];
    }

    /**
     * Evaluate a text response using AI
     */
    public function evaluateText(string $prompt, array $options = []): array {
        throw new Exception("Gemini provider not yet implemented");
    }

    /**
     * Extract course data from content (syllabus text or manual prompt)
     */
    public function extractCourseData(string $content, string $contentType = 'prompt', array $options = []): array {
        throw new Exception("Gemini course data extraction not yet implemented");
    }
}