<?php

require_once __DIR__ . '/providers/OpenAIProvider.php';
require_once __DIR__ . '/providers/AnthropicProvider.php';
require_once __DIR__ . '/providers/GeminiProvider.php';

/**
 * Factory class for creating AI provider instances
 * Handles provider instantiation and configuration
 */
class AIProviderFactory {
    
    /**
     * Create an AI provider instance
     * 
     * @param string $providerType Provider type (openai, anthropic, gemini)
     * @param array $config Provider configuration
     * @return AIProviderInterface Provider instance
     * @throws Exception If provider type is unknown
     */
    public static function create(string $providerType, array $config = []): AIProviderInterface {
        switch (strtolower($providerType)) {
            case 'openai':
                return new OpenAIProvider($config);
                
            case 'anthropic':
                return new AnthropicProvider($config);
                
            case 'gemini':
                return new GeminiProvider($config);
                
            default:
                throw new InvalidArgumentException("Unknown AI provider type: {$providerType}");
        }
    }
    
    /**
     * Get all enabled AI providers
     * TODO: Replace hardcoded logic with database queries when admin system is ready
     * 
     * @return array Array of enabled provider instances
     */
    public static function getEnabledProviders(): array {
        $providers = [];
        
        // TODO: Replace with actual database query
        // $results = Database::get()->queryArray("SELECT * FROM ai_providers WHERE enabled='true'");
        
        // For now, using hardcoded configuration
        $hardcodedProviders = self::getHardcodedProviderConfigs();
        
        foreach ($hardcodedProviders as $providerConfig) {
            if ($providerConfig['enabled']) {
                try {
                    $provider = self::create($providerConfig['provider_type'], $providerConfig);
                    $providers[] = $provider;
                } catch (Exception $e) {
                    error_log("Failed to create AI provider {$providerConfig['provider_type']}: " . $e->getMessage());
                }
            }
        }
        
        return $providers;
    }
    
    /**
     * Get a specific enabled provider by type
     * 
     * @param string $providerType Provider type to get
     * @return AIProviderInterface|null Provider instance or null if not found/enabled
     */
    public static function getProvider(string $providerType): ?AIProviderInterface {
        // TODO: Replace with database query
        // $config = Database::get()->querySingle("SELECT * FROM ai_providers WHERE provider_type = ? AND enabled='true'", [$providerType]);
        
        $hardcodedProviders = self::getHardcodedProviderConfigs();
        
        foreach ($hardcodedProviders as $providerConfig) {
            if ($providerConfig['provider_type'] === $providerType && $providerConfig['enabled']) {
                try {
                    return self::create($providerType, $providerConfig);
                } catch (Exception $e) {
                    error_log("Failed to create AI provider {$providerType}: " . $e->getMessage());
                    return null;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get the primary/default AI provider
     * 
     * @return AIProviderInterface|null Primary provider or null if none available
     */
    public static function getPrimaryProvider(): ?AIProviderInterface {
        $providers = self::getEnabledProviders();
        return !empty($providers) ? $providers[0] : null;
    }
    
    /**
     * Check if any AI providers are configured and enabled
     * 
     * @return bool True if at least one provider is available
     */
    public static function hasEnabledProviders(): bool {
        return !empty(self::getEnabledProviders());
    }
    
    /**
     * Get list of available provider types
     * 
     * @return array Array of provider type identifiers
     */
    public static function getAvailableProviderTypes(): array {
        return ['openai', 'anthropic', 'gemini'];
    }
    
    /**
     * Get provider display names mapped to their types
     * 
     * @return array Array mapping provider types to display names
     */
    public static function getProviderDisplayNames(): array {
        return [
            'openai' => 'OpenAI (ChatGPT)',
            'anthropic' => 'Anthropic (Claude)', 
            'gemini' => 'Google Gemini'
        ];
    }
    
    /**
     * Hardcoded provider configurations for development
     * TODO: Remove this method when database configuration is implemented
     * 
     * @return array Array of provider configurations
     */
    private static function getHardcodedProviderConfigs(): array {
        return [
            [
                'id' => 1,
                'name' => 'OpenAI Development',
                'provider_type' => 'openai',
                'api_key' => 'YOUR_OPENAI_API_KEY_HERE', // TODO: Replace with actual key for testing
                'model_name' => 'gpt-4o-mini',
                'endpoint_url' => 'https://api.openai.com/v1/chat/completions',
                'enabled' => true, // Enable OpenAI for development
                'enabled_features' => ['question_generation'],
                'course_restrictions' => null
            ],
            [
                'id' => 2,
                'name' => 'Anthropic Development',
                'provider_type' => 'anthropic',
                'api_key' => 'YOUR_ANTHROPIC_API_KEY_HERE',
                'model_name' => 'claude-3-5-sonnet-20241022',
                'endpoint_url' => 'https://api.anthropic.com/v1/messages',
                'enabled' => false, // Disabled for now
                'enabled_features' => ['question_generation'],
                'course_restrictions' => null
            ],
            [
                'id' => 3,
                'name' => 'Gemini Development',
                'provider_type' => 'gemini',
                'api_key' => 'YOUR_GEMINI_API_KEY_HERE',
                'model_name' => 'gemini-1.5-flash',
                'endpoint_url' => 'https://generativelanguage.googleapis.com/v1beta/models/',
                'enabled' => false, // Disabled for now
                'enabled_features' => ['question_generation'],
                'course_restrictions' => null
            ]
        ];
    }
    
    /**
     * Validate provider configuration
     * 
     * @param array $config Provider configuration
     * @return bool True if configuration is valid
     */
    public static function validateProviderConfig(array $config): bool {
        $requiredFields = ['provider_type', 'api_key', 'model_name'];
        
        foreach ($requiredFields as $field) {
            if (empty($config[$field])) {
                return false;
            }
        }
        
        if (!in_array($config['provider_type'], self::getAvailableProviderTypes())) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Test provider connectivity
     * 
     * @param string $providerType Provider type to test
     * @return array Test result with status and message
     */
    public static function testProvider(string $providerType): array {
        try {
            $provider = self::getProvider($providerType);
            
            if (!$provider) {
                return ['status' => 'error', 'message' => 'Provider not found or disabled'];
            }
            
            if (!$provider->validateApiKey()) {
                return ['status' => 'error', 'message' => 'Invalid API key'];
            }
            
            if (!$provider->isHealthy()) {
                return ['status' => 'error', 'message' => 'Provider service is not healthy'];
            }
            
            return ['status' => 'success', 'message' => 'Provider is working correctly'];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}