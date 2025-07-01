<?php

/**
 * OpenBadgesEndpointRegistry
 * 
 * Declarative registry for OpenBadges API endpoints across different providers and versions.
 * Maps standard OpenBadges operations to provider-specific endpoint configurations.
 */
class OpenBadgesEndpointRegistry
{
    /**
     * Standard OpenBadges API endpoint mappings
     * Organized by OpenBadges version and operation type
     */
    private array $endpointMappings = [
        // OpenBadges 2.0 endpoints
        '2.0' => [
            'profile' => [
                'path' => '/v2/backpack/profile',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get user profile information'
            ],
            'badges' => [
                'path' => '/v2/backpack/badges',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get all badges in user backpack'
            ],
            'badge' => [
                'path' => '/v2/backpack/badges/{id}',
                'method' => 'GET',
                'auth_required' => true,
                'parameters' => ['id'],
                'description' => 'Get specific badge details'
            ],
            'collections' => [
                'path' => '/v2/backpack/collections',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get user badge collections'
            ],
            'collection' => [
                'path' => '/v2/backpack/collections/{id}',
                'method' => 'GET',
                'auth_required' => true,
                'parameters' => ['id'],
                'description' => 'Get specific collection details'
            ],
            'push_badge' => [
                'path' => '/v2/backpack/import',
                'method' => 'POST',
                'auth_required' => true,
                'description' => 'Import/push badge to backpack'
            ],
            'refresh_token' => [
                'path' => '/oauth/token',
                'method' => 'POST',
                'auth_required' => false,
                'description' => 'Refresh OAuth access token'
            ]
        ],
        
        // OpenBadges 2.1 endpoints (mostly same as 2.0 with some additions)
        '2.1' => [
            'profile' => [
                'path' => '/v2/backpack/profile',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get user profile information'
            ],
            'badges' => [
                'path' => '/v2/backpack/badges',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get all badges in user backpack'
            ],
            'badge' => [
                'path' => '/v2/backpack/badges/{id}',
                'method' => 'GET',
                'auth_required' => true,
                'parameters' => ['id'],
                'description' => 'Get specific badge details'
            ],
            'collections' => [
                'path' => '/v2/backpack/collections',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get user badge collections'
            ],
            'collection' => [
                'path' => '/v2/backpack/collections/{id}',
                'method' => 'GET',
                'auth_required' => true,
                'parameters' => ['id'],
                'description' => 'Get specific collection details'
            ],
            'push_badge' => [
                'path' => '/v2/backpack/import',
                'method' => 'POST',
                'auth_required' => true,
                'description' => 'Import/push badge to backpack'
            ],
            'refresh_token' => [
                'path' => '/oauth/token',
                'method' => 'POST',
                'auth_required' => false,
                'description' => 'Refresh OAuth access token'
            ],
            'endorsements' => [
                'path' => '/v2/backpack/endorsements',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get badge endorsements (2.1 feature)'
            ]
        ],
        
        // OpenBadges 3.0 endpoints
        '3.0' => [
            'profile' => [
                'path' => '/v3/profile',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get user profile information'
            ],
            'badges' => [
                'path' => '/v3/credentials',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get all credentials in user backpack'
            ],
            'badge' => [
                'path' => '/v3/credentials/{id}',
                'method' => 'GET',
                'auth_required' => true,
                'parameters' => ['id'],
                'description' => 'Get specific credential details'
            ],
            'collections' => [
                'path' => '/v3/collections',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get user credential collections'
            ],
            'collection' => [
                'path' => '/v3/collections/{id}',
                'method' => 'GET',
                'auth_required' => true,
                'parameters' => ['id'],
                'description' => 'Get specific collection details'
            ],
            'push_badge' => [
                'path' => '/v3/credentials',
                'method' => 'POST',
                'auth_required' => true,
                'description' => 'Import/push credential to backpack'
            ],
            'refresh_token' => [
                'path' => '/oauth/token',
                'method' => 'POST',
                'auth_required' => false,
                'description' => 'Refresh OAuth access token'
            ],
            'achievements' => [
                'path' => '/v3/achievements',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get achievement definitions (3.0 feature)'
            ],
            'verifications' => [
                'path' => '/v3/verifications',
                'method' => 'GET',
                'auth_required' => true,
                'description' => 'Get verification records (3.0 feature)'
            ]
        ]
    ];

    /**
     * Provider-specific endpoint overrides
     * Some providers may have different endpoint structures
     */
    private array $providerOverrides = [
        // Example: Badgr has different endpoint structure
        'badgr' => [
            '2.0' => [
                'profile' => [
                    'path' => '/v2/users/self',
                    'method' => 'GET',
                    'auth_required' => true
                ],
                'badges' => [
                    'path' => '/v2/backpack/assertions',
                    'method' => 'GET',
                    'auth_required' => true
                ]
            ]
        ],
        
        // Example: Canvas Badges (Instructure)
        'canvas' => [
            '2.0' => [
                'badges' => [
                    'path' => '/api/v1/badges',
                    'method' => 'GET',
                    'auth_required' => true
                ]
            ]
        ]
    ];

    /**
     * Get endpoint configuration for a provider and operation
     */
    public function getEndpoint(BackpackProvider $provider, string $operation, array $parameters = []): string
    {
        $version = $this->normalizeVersion($provider->ob_version);
        $endpoint = $this->getEndpointConfig($provider, $operation, $version);
        
        if (!$endpoint) {
            throw new InvalidArgumentException("Endpoint '{$operation}' not found for provider '{$provider->name}' version '{$version}'");
        }

        $url = rtrim($provider->api_url, '/') . $endpoint['path'];
        
        // Replace path parameters
        if (isset($endpoint['parameters'])) {
            foreach ($endpoint['parameters'] as $param) {
                if (isset($parameters[$param])) {
                    $url = str_replace('{' . $param . '}', urlencode($parameters[$param]), $url);
                }
            }
        }

        return $url;
    }

    /**
     * Get all available endpoints for a provider
     */
    public function getAllEndpoints(BackpackProvider $provider): array
    {
        $version = $this->normalizeVersion($provider->ob_version);
        $providerKey = strtolower($provider->name);
        
        // Start with standard endpoints
        $endpoints = $this->endpointMappings[$version] ?? [];
        
        // Apply provider-specific overrides
        if (isset($this->providerOverrides[$providerKey][$version])) {
            $endpoints = array_merge($endpoints, $this->providerOverrides[$providerKey][$version]);
        }

        return $endpoints;
    }

    /**
     * Get provider capabilities based on OpenBadges version
     */
    public function getCapabilities(BackpackProvider $provider): array
    {
        $version = $this->normalizeVersion($provider->ob_version);
        $endpoints = $this->getAllEndpoints($provider);
        
        $capabilities = [
            'version' => $version,
            'operations' => array_keys($endpoints),
            'features' => $this->getVersionFeatures($version),
            'auth_methods' => $this->getAuthMethods($version)
        ];

        return $capabilities;
    }

    /**
     * Check if an operation is supported by a provider
     */
    public function supportsOperation(BackpackProvider $provider, string $operation): bool
    {
        $endpoints = $this->getAllEndpoints($provider);
        return isset($endpoints[$operation]);
    }

    /**
     * Get endpoint configuration with provider overrides
     */
    private function getEndpointConfig(BackpackProvider $provider, string $operation, string $version): ?array
    {
        $providerKey = strtolower($provider->name);
        
        // Check provider-specific overrides first
        if (isset($this->providerOverrides[$providerKey][$version][$operation])) {
            return $this->providerOverrides[$providerKey][$version][$operation];
        }
        
        // Fall back to standard endpoints
        return $this->endpointMappings[$version][$operation] ?? null;
    }

    /**
     * Normalize OpenBadges version string
     */
    private function normalizeVersion(string $version): string
    {
        // Handle various version formats
        if (preg_match('/(\d+\.\d+)/', $version, $matches)) {
            return $matches[1];
        }
        
        // Default to 2.0 if version cannot be parsed
        return '2.0';
    }

    /**
     * Get features available in specific OpenBadges version
     */
    private function getVersionFeatures(string $version): array
    {
        $features = [
            '2.0' => [
                'basic_badges',
                'collections',
                'import_export',
                'oauth2'
            ],
            '2.1' => [
                'basic_badges',
                'collections',
                'import_export',
                'oauth2',
                'endorsements',
                'evidence_extensions'
            ],
            '3.0' => [
                'verifiable_credentials',
                'achievement_definitions',
                'verification_records',
                'oauth2',
                'collections',
                'endorsements',
                'evidence_extensions',
                'alignment_objects'
            ]
        ];

        return $features[$version] ?? $features['2.0'];
    }

    /**
     * Get supported authentication methods for version
     */
    private function getAuthMethods(string $version): array
    {
        return [
            'oauth2' => true,
            'bearer_token' => true,
            'api_key' => $version === '2.0' // Some 2.0 providers still use API keys
        ];
    }

    /**
     * Add custom provider override
     */
    public function addProviderOverride(string $providerName, string $version, string $operation, array $config): void
    {
        $providerKey = strtolower($providerName);
        $this->providerOverrides[$providerKey][$version][$operation] = $config;
    }

    /**
     * Get endpoint method (GET, POST, etc.)
     */
    public function getEndpointMethod(BackpackProvider $provider, string $operation): string
    {
        $version = $this->normalizeVersion($provider->ob_version);
        $endpoint = $this->getEndpointConfig($provider, $operation, $version);
        
        return $endpoint['method'] ?? 'GET';
    }

    /**
     * Check if endpoint requires authentication
     */
    public function requiresAuth(BackpackProvider $provider, string $operation): bool
    {
        $version = $this->normalizeVersion($provider->ob_version);
        $endpoint = $this->getEndpointConfig($provider, $operation, $version);
        
        return $endpoint['auth_required'] ?? true;
    }

    /**
     * Get endpoint description
     */
    public function getEndpointDescription(BackpackProvider $provider, string $operation): string
    {
        $version = $this->normalizeVersion($provider->ob_version);
        $endpoint = $this->getEndpointConfig($provider, $operation, $version);
        
        return $endpoint['description'] ?? "OpenBadges {$operation} operation";
    }
} 