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
     *
     * @return array Array of enabled provider instances
     */
    public static function getEnabledProviders(): array {
        $providers = [];

        try {
            $results = Database::get()->queryArray("SELECT * FROM ai_providers WHERE enabled = 1");
            foreach ($results as $providerConfig) {
                try {
                    $provider = self::create($providerConfig->provider_type, (array)$providerConfig);
                    $providers[] = $provider;
                } catch (Exception $e) {
                    error_log("Failed to create AI provider {$providerConfig->provider_type}: " . $e->getMessage());
                }
            }
        } catch (Exception $e) {
            error_log("Failed to load AI providers from database: " . $e->getMessage());
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
        try {
            $config = Database::get()->querySingle("SELECT * FROM ai_providers WHERE provider_type = ? AND enabled = 1", [$providerType]);

            if ($config) {
                return self::create($providerType, (array)$config);
            }
        } catch (Exception $e) {
            error_log("Failed to load AI provider {$providerType} from database: " . $e->getMessage());
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
