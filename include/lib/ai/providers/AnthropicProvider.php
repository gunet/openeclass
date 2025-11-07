<?php

require_once __DIR__ . '/AbstractAIProvider.php';

/**
 * Anthropic (Claude) Provider implementation for OpenEclass AI integration
 * TODO: Implement full functionality when needed
 */
class AnthropicProvider extends AbstractAIProvider {
    
    private const DEFAULT_MODEL = 'claude-3-5-sonnet-20241022';
    private const DEFAULT_ENDPOINT = 'https://api.anthropic.com/v1/messages';
    
    
    public function getProviderType(): string {
        return 'anthropic';
    }
    
    public function getDisplayName(): string {
        return 'Anthropic (Claude)';
    }
    
    protected function getDefaultModel(): string {
        return self::DEFAULT_MODEL;
    }
    
    protected function getDefaultEndpoint(): string {
        return self::DEFAULT_ENDPOINT;
    }
    
    public function getAvailableModels(): array {
        return [
            'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
            'claude-3-opus-20240229' => 'Claude 3 Opus',
            'claude-3-haiku-20240307' => 'Claude 3 Haiku'
        ];
    }
    
    public function isHealthy(): bool {
        // TODO: Implement health check
        return false;
    }
    
    protected function makeApiRequest(string $endpoint, array $data, string $method = 'POST'): array {
        // TODO: Implement Anthropic API request
        throw new Exception("Anthropic provider not yet implemented");
    }
    
    protected function buildApiRequest(string $prompt, array $options): array {
        // TODO: Implement Anthropic request format
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
        throw new Exception("Anthropic provider not yet implemented");
    }

    /**
     * Extract course data from content (syllabus text or manual prompt)
     */
    public function extractCourseData(string $content, string $contentType = 'prompt', array $options = []): array {
        throw new Exception("Anthropic course data extraction not yet implemented");
    }
}