<?php

/**
 * OpenBadgesApiResponse
 * 
 * Data Transfer Object for OpenBadges API responses.
 * Provides a consistent interface for handling API response data.
 */
readonly class OpenBadgesApiResponse
{
    public function __construct(
        public bool $success,
        public ?array $data,
        public ?string $error,
        public int $httpCode,
        public string $timestamp
    ) {}

    /**
     * Create a successful response
     */
    public static function success(array $data, int $httpCode = 200): self
    {
        return new self(
            success: true,
            data: $data,
            error: null,
            httpCode: $httpCode,
            timestamp: date('Y-m-d H:i:s')
        );
    }

    /**
     * Create an error response
     */
    public static function error(string $error, int $httpCode = 400, ?array $data = null): self
    {
        return new self(
            success: false,
            data: $data,
            error: $error,
            httpCode: $httpCode,
            timestamp: date('Y-m-d H:i:s')
        );
    }

    /**
     * Check if the response is successful
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Check if the response is an error
     */
    public function isError(): bool
    {
        return !$this->success;
    }

    /**
     * Get response data
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Get error message
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get HTTP status code
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * Get specific data field
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if data field exists
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Get badges from response (handles different OpenBadges versions)
     */
    public function getBadges(): array
    {
        if (!$this->isSuccess() || !$this->data) {
            return [];
        }

        // OpenBadges 2.x format
        if (isset($this->data['result'])) {
            return $this->data['result'];
        }

        // OpenBadges 3.0 format (credentials)
        if (isset($this->data['credentials'])) {
            return $this->data['credentials'];
        }

        // Direct array of badges
        if (is_array($this->data) && $this->isArrayOfBadges($this->data)) {
            return $this->data;
        }

        return [];
    }

    /**
     * Get user profile from response
     */
    public function getProfile(): ?array
    {
        if (!$this->isSuccess() || !$this->data) {
            return null;
        }

        // OpenBadges 2.x format
        if (isset($this->data['result'])) {
            return $this->data['result'];
        }

        // Direct profile data
        if (isset($this->data['email']) || isset($this->data['name'])) {
            return $this->data;
        }

        return null;
    }

    /**
     * Get collections from response
     */
    public function getCollections(): array
    {
        if (!$this->isSuccess() || !$this->data) {
            return [];
        }

        // OpenBadges 2.x format
        if (isset($this->data['result'])) {
            return $this->data['result'];
        }

        // OpenBadges 3.0 format
        if (isset($this->data['collections'])) {
            return $this->data['collections'];
        }

        // Direct array of collections
        if (is_array($this->data) && $this->isArrayOfCollections($this->data)) {
            return $this->data;
        }

        return [];
    }

    /**
     * Get OAuth tokens from response
     */
    public function getTokens(): ?array
    {
        if (!$this->isSuccess() || !$this->data) {
            return null;
        }

        if (isset($this->data['access_token'])) {
            return [
                'access_token' => $this->data['access_token'],
                'refresh_token' => $this->data['refresh_token'] ?? null,
                'token_type' => $this->data['token_type'] ?? 'Bearer',
                'expires_in' => $this->data['expires_in'] ?? null
            ];
        }

        return null;
    }

    /**
     * Get pagination information
     */
    public function getPagination(): ?array
    {
        if (!$this->isSuccess() || !$this->data) {
            return null;
        }

        $pagination = [];
        
        // Common pagination fields
        $paginationFields = ['total', 'count', 'page', 'pages', 'limit', 'offset', 'next', 'previous'];
        
        foreach ($paginationFields as $field) {
            if (isset($this->data[$field])) {
                $pagination[$field] = $this->data[$field];
            }
        }

        return empty($pagination) ? null : $pagination;
    }

    /**
     * Convert response to array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'error' => $this->error,
            'http_code' => $this->httpCode,
            'timestamp' => $this->timestamp
        ];
    }

    /**
     * Convert response to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Get summary of the response
     */
    public function getSummary(): array
    {
        $summary = [
            'success' => $this->success,
            'http_code' => $this->httpCode,
            'timestamp' => $this->timestamp
        ];

        if ($this->success) {
            $summary['data_keys'] = $this->data ? array_keys($this->data) : [];
            $summary['data_count'] = $this->data ? count($this->data) : 0;
        } else {
            $summary['error'] = $this->error;
        }

        return $summary;
    }

    /**
     * Check if response contains valid OAuth tokens
     */
    public function hasValidTokens(): bool
    {
        $tokens = $this->getTokens();
        return $tokens && isset($tokens['access_token']) && !empty($tokens['access_token']);
    }

    /**
     * Get error details for debugging
     */
    public function getErrorDetails(): ?array
    {
        if ($this->isSuccess()) {
            return null;
        }

        return [
            'error' => $this->error,
            'http_code' => $this->httpCode,
            'response_data' => $this->data,
            'timestamp' => $this->timestamp
        ];
    }

    // Private helper methods

    /**
     * Check if array contains badge objects
     */
    private function isArrayOfBadges(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $firstItem = reset($data);
        return is_array($firstItem) && (
            isset($firstItem['badge']) || 
            isset($firstItem['badgeclass']) || 
            isset($firstItem['id']) ||
            isset($firstItem['entityId'])
        );
    }

    /**
     * Check if array contains collection objects
     */
    private function isArrayOfCollections(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $firstItem = reset($data);
        return is_array($firstItem) && (
            isset($firstItem['name']) || 
            isset($firstItem['title']) ||
            (isset($firstItem['id']) && isset($firstItem['badges']))
        );
    }
} 