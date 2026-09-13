<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

require_once __DIR__ . '/ExternalRepoInterface.php';

/**
 * AbstractExternalRepo
 * 
 * Abstract base class for external repository implementations.
 * Provides common functionality for HTTP requests, error handling, and configuration.
 */
abstract class AbstractExternalRepo implements ExternalRepoInterface
{
    /** @var object Repository configuration from database */
    protected object $config;
    
    /** @var string Base URL for API requests */
    protected string $baseUrl;
    
    /** @var string|null API key for authentication */
    protected ?string $apiKey;
    
    /** @var string Authentication type */
    protected string $authType;
    
    /** @var array Additional configuration as associative array */
    protected array $additionalConfig;

    /**
     * Constructor
     * 
     * @param object $config Repository configuration object from database
     */
    public function __construct(object $config)
    {
        $this->config = $config;
        $this->baseUrl = rtrim($config->base_url ?? '', '/');
        $this->apiKey = $config->api_key ?? null;
        $this->authType = $config->auth_type ?? 'none';
        
        // Handle config field - it might be null, empty string, or JSON string
        $configJson = $config->config ?? null;
        if (!empty($configJson) && is_string($configJson)) {
            $decoded = json_decode($configJson, true);
            $this->additionalConfig = is_array($decoded) ? $decoded : [];
        } else {
            $this->additionalConfig = [];
        }
    }

    /**
     * Get the repository name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->config->name;
    }

    /**
     * Check if the repository is properly configured
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        // Base URL is required for most repositories
        if (empty($this->baseUrl) && $this->requiresBaseUrl()) {
            return false;
        }
        
        // API key is required if auth type is api_key
        if ($this->authType === 'api_key' && empty($this->apiKey)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if this repository type requires a base URL
     * 
     * @return bool
     */
    protected function requiresBaseUrl(): bool
    {
        return true;
    }

    /**
     * Make an HTTP GET request
     * 
     * @param string $url Full URL or path to append to base URL
     * @param array $params Query parameters
     * @param array $headers Additional headers
     * @return array Response with 'success', 'data', 'http_code', 'error' keys
     */
    protected function httpGet(string $url, array $params = [], array $headers = []): array
    {
        // Build full URL
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = $this->baseUrl . '/' . ltrim($url, '/');
        }
        
        // Add query parameters
        if (!empty($params)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }
        
        // Log the URL being called for debugging
        error_log("External Repo API Call - URL: $url");
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set default headers
        $defaultHeaders = [
            'Accept: application/json',
            'User-Agent: OpenEclass/1.0'
        ];
        
        // Add API key to headers if needed
        if ($this->authType === 'api_key' && !empty($this->apiKey)) {
            $defaultHeaders[] = $this->getAuthHeader();
        }
        
        // Merge with custom headers
        $allHeaders = array_merge($defaultHeaders, $headers);
        
        // Configure cURL
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $allHeaders,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle errors
        if ($response === false) {
            error_log("External repo HTTP error: $error (URL: $url)");
            return [
                'success' => false,
                'data' => null,
                'http_code' => 0,
                'error' => $error
            ];
        }
        
        // Parse JSON response
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Response might not be JSON, return as string
            $data = $response;
        }
        
        $errorMsg = null;
        if ($httpCode >= 400) {
            if ($httpCode == 404) {
                $errorMsg = $GLOBALS['langEndpointNotFound'] ?? "API endpoint not found (HTTP 404). Please check the repository base URL.";
            } elseif ($httpCode == 401 || $httpCode == 403) {
                $errorMsg = $GLOBALS['langAuthenticationFailed'] ?? "Authentication failed (HTTP $httpCode). Please check your API key.";
            } else {
                $errorMsg = $GLOBALS['langHttpError'] ?? "HTTP error $httpCode";
            }
        }
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $data,
            'http_code' => $httpCode,
            'error' => $errorMsg
        ];
    }

    /**
     * Get the authorization header based on auth type
     * Override in subclasses for custom auth header format
     * 
     * @return string
     */
    protected function getAuthHeader(): string
    {
        return "Authorization: Bearer {$this->apiKey}";
    }

    /**
     * Build a standardized search result item
     * 
     * @param string $id External ID
     * @param string $title Item title
     * @param string|null $description Item description
     * @param string $url Item URL
     * @param string $type Resource type (video, article, image, document)
     * @param string|null $thumbnail Thumbnail URL
     * @param array $metadata Additional metadata
     * @return array
     */
    protected function buildResultItem(
        string $id,
        string $title,
        ?string $description,
        string $url,
        string $type,
        ?string $thumbnail = null,
        array $metadata = []
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'type' => $type,
            'thumbnail' => $thumbnail,
            'metadata' => $metadata,
            'repository_id' => $this->config->id,
            'repository_name' => $this->config->name,
            'repository_type' => $this->getType()
        ];
    }

    /**
     * Build a standardized search results response
     * 
     * @param array $items Result items
     * @param int $total Total count of results
     * @param int $page Current page
     * @param int $perPage Results per page
     * @return array
     */
    protected function buildSearchResults(array $items, int $total, int $page, int $perPage): array
    {
        return [
            'success' => true,
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'repository' => [
                'id' => $this->config->id,
                'name' => $this->config->name,
                'type' => $this->getType()
            ]
        ];
    }

    /**
     * Build an error response
     * 
     * @param string $message Error message
     * @param int $code Optional error code
     * @return array
     */
    protected function buildErrorResponse(string $message, int $code = 0): array
    {
        return [
            'success' => false,
            'error' => $message,
            'code' => $code,
            'items' => [],
            'total' => 0
        ];
    }
}

