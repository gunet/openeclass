<?php

/**
 * OpenBadgesApiClient
 * 
 * HTTP client wrapper for making authenticated API calls to OpenBadges backpack providers.
 * Handles authentication, request formatting, and response parsing.
 */
class OpenBadgesApiClient
{
    private int $timeout;
    private int $connectTimeout;
    private array $defaultHeaders;

    public function __construct(int $timeout = 30, int $connectTimeout = 10)
    {
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->defaultHeaders = [
            'User-Agent: OpenEClass/4.0 OpenBadges Client',
            'Accept: application/json',
            'Content-Type: application/json'
        ];
    }

    /**
     * Make an authenticated API request
     */
    public function authenticatedRequest(
        string $url,
        string $method = 'GET',
        ?string $accessToken = null,
        ?array $data = null,
        string $obVersion = '2.0'
    ): OpenBadgesApiResponse {
        $headers = $this->defaultHeaders;
        
        if ($accessToken) {
            $headers[] = 'Authorization: Bearer ' . $accessToken;
        }

        return $this->request($url, $method, $data, $obVersion, $headers);
    }

    /**
     * Make a standard API request (without authentication)
     */
    public function request(
        string $url,
        string $method = 'GET',
        ?array $data = null,
        string $obVersion = '2.0',
        array $additionalHeaders = []
    ): OpenBadgesApiResponse {
        $headers = array_merge($this->defaultHeaders, $additionalHeaders);
        
        // Add version-specific headers
        $headers = $this->addVersionHeaders($headers, $obVersion);

        try {
            switch (strtoupper($method)) {
                case 'GET':
                    return $this->performGetRequest($url, $headers);
                case 'POST':
                    return $this->performPostRequest($url, $data, $headers);
                case 'PUT':
                    return $this->performPutRequest($url, $data, $headers);
                case 'DELETE':
                    return $this->performDeleteRequest($url, $headers);
                default:
                    return OpenBadgesApiResponse::error("Unsupported HTTP method: {$method}");
            }
        } catch (Exception $e) {
            error_log('OpenBadges API Client Error: ' . $e->getMessage());
            return OpenBadgesApiResponse::error('Request failed: ' . $e->getMessage());
        }
    }

    /**
     * Perform GET request
     */
    private function performGetRequest(string $url, array $headers): OpenBadgesApiResponse
    {
        if (!extension_loaded('curl')) {
            return OpenBadgesApiResponse::error('cURL extension is not available');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return OpenBadgesApiResponse::error("cURL error: {$error}");
        }

        return $this->parseResponse($response, $httpCode);
    }

    /**
     * Perform POST request
     */
    private function performPostRequest(string $url, ?array $data, array $headers): OpenBadgesApiResponse
    {
        if (!extension_loaded('curl')) {
            return OpenBadgesApiResponse::error('cURL extension is not available');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($data) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return OpenBadgesApiResponse::error("cURL error: {$error}");
        }

        return $this->parseResponse($response, $httpCode);
    }

    /**
     * Perform PUT request
     */
    private function performPutRequest(string $url, ?array $data, array $headers): OpenBadgesApiResponse
    {
        if (!extension_loaded('curl')) {
            return OpenBadgesApiResponse::error('cURL extension is not available');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($data) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return OpenBadgesApiResponse::error("cURL error: {$error}");
        }

        return $this->parseResponse($response, $httpCode);
    }

    /**
     * Perform DELETE request
     */
    private function performDeleteRequest(string $url, array $headers): OpenBadgesApiResponse
    {
        if (!extension_loaded('curl')) {
            return OpenBadgesApiResponse::error('cURL extension is not available');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return OpenBadgesApiResponse::error("cURL error: {$error}");
        }

        return $this->parseResponse($response, $httpCode);
    }

    /**
     * Parse API response
     */
    private function parseResponse(string $response, int $httpCode): OpenBadgesApiResponse
    {
        // Try to decode JSON response
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // If JSON decode fails, treat as plain text response
            $data = ['raw_response' => $response];
        }

        // Determine if response is successful based on HTTP status code
        $isSuccess = $httpCode >= 200 && $httpCode < 300;

        if ($isSuccess) {
            return OpenBadgesApiResponse::success($data, $httpCode);
        } else {
            $errorMessage = $this->extractErrorMessage($data, $httpCode);
            return OpenBadgesApiResponse::error($errorMessage, $httpCode, $data);
        }
    }

    /**
     * Extract error message from response data
     */
    private function extractErrorMessage(array $data, int $httpCode): string
    {
        // Try common error message fields
        $errorFields = ['error', 'message', 'error_description', 'detail', 'title'];
        
        foreach ($errorFields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                return $data[$field];
            }
        }

        // If no specific error message found, use HTTP status
        return "HTTP {$httpCode} error";
    }

    /**
     * Add version-specific headers
     */
    private function addVersionHeaders(array $headers, string $obVersion): array
    {
        switch ($obVersion) {
            case '3.0':
                $headers[] = 'Accept: application/vc+ld+json, application/json';
                break;
            case '2.1':
                $headers[] = 'Accept: application/json; version=2.1';
                break;
            case '2.0':
            default:
                $headers[] = 'Accept: application/json; version=2.0';
                break;
        }

        return $headers;
    }

    /**
     * Set custom timeout values
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Set custom connection timeout
     */
    public function setConnectTimeout(int $connectTimeout): void
    {
        $this->connectTimeout = $connectTimeout;
    }

    /**
     * Add default header
     */
    public function addDefaultHeader(string $header): void
    {
        $this->defaultHeaders[] = $header;
    }

    /**
     * Remove default header by pattern
     */
    public function removeDefaultHeader(string $pattern): void
    {
        $this->defaultHeaders = array_filter($this->defaultHeaders, function($header) use ($pattern) {
            return strpos($header, $pattern) === false;
        });
    }

    /**
     * Get current default headers
     */
    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }
} 