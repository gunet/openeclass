<?php

/**
 * BackpackProviderService
 * 
 * Unified service for discovering and registering OpenBadges providers.
 * Handles both provider discovery (.well-known/badgeconnect.json) and
 * dynamic client registration (RFC 7591).
 */
class BackpackProviderService
{
    private int $timeout;
    private int $connectTimeout;
    private string $baseUrl;

    public function __construct(int $timeout = 30, int $connectTimeout = 10)
    {
        global $urlServer;
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->baseUrl = rtrim($urlServer, '/');
    }

    /**
     * Discover provider capabilities from .well-known/badgeconnect.json
     */
    public function discoverProvider(string $apiUrl): array
    {
        $wellKnownUrl = rtrim($apiUrl, '/') . '/.well-known/badgeconnect.json';
        
        try {
            $response = $this->fetchWellKnownConfig($wellKnownUrl);
            
            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => $response['error'],
                    'discovery_url' => $wellKnownUrl
                ];
            }

            $config = $response['data'];
            
            // Validate required fields for OpenBadges 2.1
            $validationResult = $this->validateDiscoveryConfig($config);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'error' => 'Invalid discovery configuration: ' . $validationResult['error'],
                    'discovery_url' => $wellKnownUrl,
                    'raw_config' => $config
                ];
            }

            return [
                'success' => true,
                'discovery_url' => $wellKnownUrl,
                'config' => $config,
                'capabilities' => $this->extractCapabilities($config)
            ];

        } catch (Exception $e) {
            error_log('Provider discovery error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Discovery failed: ' . $e->getMessage(),
                'discovery_url' => $wellKnownUrl
            ];
        }
    }

    /**
     * Register OpenEClass as an OAuth client with the provider
     */
    public function registerProvider(string $registrationEndpoint, array $discoveryConfig): array
    {
        try {
            $registrationData = $this->buildRegistrationData();
            $response = $this->performRegistration($registrationEndpoint, $registrationData);
            
            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => $response['error'],
                    'registration_endpoint' => $registrationEndpoint,
                    'registration_data' => $registrationData
                ];
            }

            $clientInfo = $response['data'];
            
            // Validate registration response
            $validationResult = $this->validateRegistrationResponse($clientInfo);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'error' => 'Invalid registration response: ' . $validationResult['error'],
                    'registration_endpoint' => $registrationEndpoint,
                    'raw_response' => $clientInfo
                ];
            }

            return [
                'success' => true,
                'registration_endpoint' => $registrationEndpoint,
                'client_info' => $clientInfo,
                'oauth_config' => $this->extractOAuthConfig($clientInfo, $discoveryConfig)
            ];

        } catch (Exception $e) {
            error_log('Provider registration error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Registration failed: ' . $e->getMessage(),
                'registration_endpoint' => $registrationEndpoint
            ];
        }
    }

    /**
     * Check if provider supports required features for OpenEClass integration
     */
    public function isProviderCompatible(array $discoveryResult): array
    {
        if (!$discoveryResult['success']) {
            return [
                'compatible' => false,
                'reasons' => ['Discovery failed: ' . $discoveryResult['error']]
            ];
        }

        $capabilities = $discoveryResult['capabilities'];
        $reasons = [];

        // Check for required OAuth2 support
        if (!$capabilities['supports_oauth2']) {
            $reasons[] = 'Provider does not support OAuth2 authorization';
        }

        // Check for registration endpoint
        if (!$capabilities['supports_registration']) {
            $reasons[] = 'Provider does not support dynamic client registration';
        }

        // Check for required grant types
        $requiredGrantTypes = ['authorization_code', 'refresh_token'];
        if (isset($capabilities['supported_grant_types'])) {
            $missingGrantTypes = array_diff($requiredGrantTypes, $capabilities['supported_grant_types']);
            if (!empty($missingGrantTypes)) {
                $reasons[] = 'Provider does not support required grant types: ' . implode(', ', $missingGrantTypes);
            }
        }

        // Check for required response types
        if (isset($capabilities['supported_response_types']) && 
            !in_array('code', $capabilities['supported_response_types'])) {
            $reasons[] = 'Provider does not support authorization code response type';
        }

        return [
            'compatible' => empty($reasons),
            'reasons' => $reasons,
            'capabilities' => $capabilities
        ];
    }

    /**
     * Generate authorization URL for user consent
     */
    public function generateAuthorizationUrl(array $oauthConfig, string $state = null): string
    {
        $state = $state ?: bin2hex(random_bytes(16));
        
        $params = [
            'response_type' => 'code',
            'client_id' => $oauthConfig['client_id'],
            'redirect_uri' => $oauthConfig['redirect_uris'][0],
            'scope' => $oauthConfig['scope'],
            'state' => $state
        ];

        return $oauthConfig['authorization_endpoint'] . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(
        array $oauthConfig, 
        string $authorizationCode, 
        string $redirectUri
    ): array {
        $tokenData = [
            'grant_type' => 'authorization_code',
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUri,
            'client_id' => $oauthConfig['client_id'],
            'client_secret' => $oauthConfig['client_secret']
        ];

        return $this->performTokenRequest($oauthConfig['token_endpoint'], $tokenData);
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(array $oauthConfig, string $refreshToken): array
    {
        $tokenData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $oauthConfig['client_id'],
            'client_secret' => $oauthConfig['client_secret']
        ];

        return $this->performTokenRequest($oauthConfig['token_endpoint'], $tokenData);
    }

    /**
     * Fetch the .well-known/badgeconnect.json configuration
     */
    private function fetchWellKnownConfig(string $url): array
    {
        if (!extension_loaded('curl')) {
            return [
                'success' => false,
                'error' => 'cURL extension is not available'
            ];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: OpenEClass/4.0 BadgeConnect Discovery'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => "cURL error: {$error}"
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'success' => false,
                'error' => "HTTP {$httpCode} error when fetching discovery configuration"
            ];
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Invalid JSON in discovery response: ' . json_last_error_msg()
            ];
        }

        return [
            'success' => true,
            'data' => $decodedResponse
        ];
    }

    /**
     * Validate the discovery configuration
     */
    private function validateDiscoveryConfig(array $config): array
    {
        $requiredFields = [
            'openBadgeConnectAPI' => 'OpenBadges Connect API information',
            'registration_endpoint' => 'Registration endpoint',
            'authorization_endpoint' => 'Authorization endpoint',
            'token_endpoint' => 'Token endpoint'
        ];

        foreach ($requiredFields as $field => $description) {
            if (!isset($config[$field]) || empty($config[$field])) {
                return [
                    'valid' => false,
                    'error' => "Missing or empty required field: {$field} ({$description})"
                ];
            }
        }

        // Validate URLs
        $urlFields = ['registration_endpoint', 'authorization_endpoint', 'token_endpoint'];
        foreach ($urlFields as $field) {
            if (!filter_var($config[$field], FILTER_VALIDATE_URL)) {
                return [
                    'valid' => false,
                    'error' => "Invalid URL format for field: {$field}"
                ];
            }
        }

        // Validate OpenBadges Connect API information
        if (!isset($config['openBadgeConnectAPI']['version'])) {
            return [
                'valid' => false,
                'error' => 'Missing OpenBadges Connect API version'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Extract provider capabilities from discovery configuration
     */
    private function extractCapabilities(array $config): array
    {
        $capabilities = [
            'version' => $config['openBadgeConnectAPI']['version'] ?? 'unknown',
            'supports_registration' => isset($config['registration_endpoint']),
            'supports_oauth2' => isset($config['authorization_endpoint']) && isset($config['token_endpoint']),
            'endpoints' => []
        ];

        // Extract available endpoints
        $endpointFields = [
            'registration_endpoint' => 'registration',
            'authorization_endpoint' => 'authorization', 
            'token_endpoint' => 'token',
            'revocation_endpoint' => 'revocation',
            'introspection_endpoint' => 'introspection'
        ];

        foreach ($endpointFields as $configField => $endpointName) {
            if (isset($config[$configField])) {
                $capabilities['endpoints'][$endpointName] = $config[$configField];
            }
        }

        // Extract supported scopes if available
        if (isset($config['scopes_supported'])) {
            $capabilities['supported_scopes'] = $config['scopes_supported'];
        }

        // Extract supported grant types if available
        if (isset($config['grant_types_supported'])) {
            $capabilities['supported_grant_types'] = $config['grant_types_supported'];
        }

        // Extract supported response types if available
        if (isset($config['response_types_supported'])) {
            $capabilities['supported_response_types'] = $config['response_types_supported'];
        }

        return $capabilities;
    }

    /**
     * Build the registration data for OpenEClass
     */
    private function buildRegistrationData(): array
    {
        return [
            'client_name' => 'OpenEClass LMS',
            'client_uri' => $this->baseUrl,
            'logo_uri' => $this->baseUrl . '/template/modern/img/openeclass_logo.png',
            'tos_uri' => $this->baseUrl . '/info/terms.php',
            'policy_uri' => $this->baseUrl . '/info/privacy.php',
            'software_id' => 'openeclass-lms',
            'software_version' => '4.0',
            'redirect_uris' => [
                $this->baseUrl . '/main/oauth/callback.php',
                $this->baseUrl . '/main/mybackpacks.php?oauth_callback=1'
            ],
            'token_endpoint_auth_method' => 'client_secret_basic',
            'grant_types' => [
                'authorization_code',
                'refresh_token'
            ],
            'response_types' => [
                'code'
            ],
            'scope' => 'read write',
            'application_type' => 'web',
            'contacts' => [
                'admin@' . parse_url($this->baseUrl, PHP_URL_HOST)
            ]
        ];
    }

    /**
     * Perform the actual registration request
     */
    private function performRegistration(string $endpoint, array $data): array
    {
        if (!extension_loaded('curl')) {
            return [
                'success' => false,
                'error' => 'cURL extension is not available'
            ];
        }

        $jsonData = json_encode($data);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: OpenEClass/4.0 OAuth Client Registration'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => "cURL error: {$error}"
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMessage = "HTTP {$httpCode} error during registration";
            
            // Try to extract error details from response
            $decodedResponse = json_decode($response, true);
            if ($decodedResponse && isset($decodedResponse['error'])) {
                $errorMessage .= ': ' . $decodedResponse['error'];
                if (isset($decodedResponse['error_description'])) {
                    $errorMessage .= ' - ' . $decodedResponse['error_description'];
                }
            }
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'http_code' => $httpCode,
                'raw_response' => $response
            ];
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Invalid JSON in registration response: ' . json_last_error_msg(),
                'raw_response' => $response
            ];
        }

        return [
            'success' => true,
            'data' => $decodedResponse
        ];
    }

    /**
     * Validate the registration response
     */
    private function validateRegistrationResponse(array $response): array
    {
        $requiredFields = [
            'client_id' => 'Client ID',
            'client_secret' => 'Client Secret'
        ];

        foreach ($requiredFields as $field => $description) {
            if (!isset($response[$field]) || empty($response[$field])) {
                return [
                    'valid' => false,
                    'error' => "Missing or empty required field: {$field} ({$description})"
                ];
            }
        }

        // Validate client_id format (should be a non-empty string)
        if (!is_string($response['client_id']) || strlen($response['client_id']) < 1) {
            return [
                'valid' => false,
                'error' => 'Invalid client_id format'
            ];
        }

        // Validate client_secret format (should be a non-empty string)
        if (!is_string($response['client_secret']) || strlen($response['client_secret']) < 1) {
            return [
                'valid' => false,
                'error' => 'Invalid client_secret format'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Extract OAuth configuration from registration response and discovery config
     */
    private function extractOAuthConfig(array $clientInfo, array $discoveryConfig): array
    {
        return [
            'client_id' => $clientInfo['client_id'],
            'client_secret' => $clientInfo['client_secret'],
            'authorization_endpoint' => $discoveryConfig['authorization_endpoint'],
            'token_endpoint' => $discoveryConfig['token_endpoint'],
            'revocation_endpoint' => $discoveryConfig['revocation_endpoint'] ?? null,
            'introspection_endpoint' => $discoveryConfig['introspection_endpoint'] ?? null,
            'scope' => $clientInfo['scope'] ?? 'read write',
            'grant_types' => $clientInfo['grant_types'] ?? ['authorization_code', 'refresh_token'],
            'response_types' => $clientInfo['response_types'] ?? ['code'],
            'token_endpoint_auth_method' => $clientInfo['token_endpoint_auth_method'] ?? 'client_secret_basic',
            'redirect_uris' => $clientInfo['redirect_uris'] ?? [
                $this->baseUrl . '/main/oauth/callback.php',
                $this->baseUrl . '/main/mybackpacks.php?oauth_callback=1'
            ],
            'client_id_issued_at' => $clientInfo['client_id_issued_at'] ?? time(),
            'client_secret_expires_at' => $clientInfo['client_secret_expires_at'] ?? 0
        ];
    }

    /**
     * Perform token request (authorization code exchange or refresh)
     */
    private function performTokenRequest(string $endpoint, array $data): array
    {
        if (!extension_loaded('curl')) {
            return [
                'success' => false,
                'error' => 'cURL extension is not available'
            ];
        }

        $postData = http_build_query($data);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
                'User-Agent: OpenEClass/4.0 OAuth Token Request'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => "cURL error: {$error}"
            ];
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Invalid JSON in token response: ' . json_last_error_msg(),
                'raw_response' => $response
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMessage = "HTTP {$httpCode} error during token request";
            if (isset($decodedResponse['error'])) {
                $errorMessage .= ': ' . $decodedResponse['error'];
                if (isset($decodedResponse['error_description'])) {
                    $errorMessage .= ' - ' . $decodedResponse['error_description'];
                }
            }
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $decodedResponse
            ];
        }

        // Validate token response
        if (!isset($decodedResponse['access_token'])) {
            return [
                'success' => false,
                'error' => 'Missing access_token in response',
                'response' => $decodedResponse
            ];
        }

        return [
            'success' => true,
            'tokens' => [
                'access_token' => $decodedResponse['access_token'],
                'refresh_token' => $decodedResponse['refresh_token'] ?? null,
                'token_type' => $decodedResponse['token_type'] ?? 'Bearer',
                'expires_in' => $decodedResponse['expires_in'] ?? 3600,
                'scope' => $decodedResponse['scope'] ?? null
            ]
        ];
    }
} 