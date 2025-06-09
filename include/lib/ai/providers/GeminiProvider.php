<?php

require_once __DIR__ . '/AbstractAIProvider.php';

/**
 * Google Gemini Provider implementation for OpenEclass AI integration
 * TODO: Implement full functionality when needed
 */
class GeminiProvider extends AbstractAIProvider {
    
    private const DEFAULT_MODEL = 'gemini-1.5-flash';
    private const DEFAULT_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    /**
     * Load hardcoded configuration for development
     * TODO: Remove this when admin configuration is implemented
     */
    protected function loadHardcodedConfig() {
        // TODO: Replace with database configuration when admin pages are ready
        $this->apiKey = 'YOUR_GEMINI_API_KEY_HERE'; // TODO: Get from database
        $this->modelName = self::DEFAULT_MODEL; // TODO: Get from database
        $this->endpointUrl = self::DEFAULT_ENDPOINT;
        $this->enabled = false; // Disabled for now - TODO: Get from database
    }
    
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
}