<?php

/**
 * OpenBadgesApiService
 * 
 * Declarative service for communicating with various OpenBadges backpack providers.
 * Provides a unified interface for different provider implementations while respecting
 * the OpenBadges specification endpoints.
 */
class OpenBadgesApiService
{
    private OpenBadgesApiClient $client;
    private OpenBadgesEndpointRegistry $registry;
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::get();
        $this->client = new OpenBadgesApiClient();
        $this->registry = new OpenBadgesEndpointRegistry();
    }

    /**
     * Get user's badges from their connected backpack
     */
    public function getUserBadges(int $userId): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'badges');
        
        return $this->client->authenticatedRequest(
            $endpoint,
            'GET',
            $connection->access_token,
            null,
            $provider->ob_version
        );
    }

    /**
     * Get specific badge/assertion details
     */
    public function getUserBadge(int $userId, string $badgeId): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'badge', ['id' => $badgeId]);
        
        return $this->client->authenticatedRequest(
            $endpoint,
            'GET',
            $connection->access_token,
            null,
            $provider->ob_version
        );
    }

    /**
     * Get specific assertion details by assertion ID
     */
    public function getAssertion(int $userId, string $assertionId): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'assertion', ['id' => $assertionId]);
        
        return $this->client->authenticatedRequest(
            $endpoint,
            'GET',
            $connection->access_token,
            null,
            $provider->ob_version
        );
    }

    /**
     * Get specific badge details
     */
    public function getBadgeDetails(int $userId, string $badgeId): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'badge', ['id' => $badgeId]);
        
        return $this->client->authenticatedRequest(
            $endpoint,
            'GET',
            $connection->access_token,
            null,
            $provider->ob_version
        );
    }

    /**
     * Upload/Push a badge to user's backpack
     */
    public function pushBadgeToBackpack(int $userId, array $badgeData): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'push_badge');
        
        return $this->client->authenticatedRequest(
            $endpoint,
            'POST',
            $connection->access_token,
            $badgeData,
            $provider->ob_version
        );
    }

    /**
     * Get user profile information from backpack
     */
    public function getUserProfile(int $userId): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'profile');
        
        return $this->client->authenticatedRequest(
            $endpoint,
            'GET',
            $connection->access_token,
            null,
            $provider->ob_version
        );
    }

    /**
     * Get collections from user's backpack
     */
    public function getUserCollections(int $userId): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'collections');
        
        return $this->client->authenticatedRequest(
            $endpoint,
            'GET',
            $connection->access_token,
            null,
            $provider->ob_version
        );
    }

    /**
     * Get badges within a specific collection
     */
    public function getCollectionBadges(int $userId, string $collectionId): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'collection', ['id' => $collectionId]);

        return $this->client->authenticatedRequest(
            $endpoint,
            'GET',
            $connection->access_token,
            null,
            $provider->ob_version
        );
    }

    /**
     * Test connection to backpack provider
     */
    public function testConnection(BackpackProvider $provider, string $accessToken): OpenBadgesApiResponse
    {
        $endpoint = $this->registry->getEndpoint($provider, 'profile');
        
        return $this->client->authenticatedRequest(
            $endpoint,
            'GET',
            $accessToken,
            null,
            $provider->ob_version
        );
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(int $userId): OpenBadgesApiResponse
    {
        $connection = $this->getUserConnection($userId);
        if (!$connection || !$connection->refresh_token) {
            return OpenBadgesApiResponse::error('No refresh token available');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, 'refresh_token');
        
        $response = $this->client->request(
            $endpoint,
            'POST',
            [
                'grant_type' => 'refresh_token',
                'refresh_token' => $connection->refresh_token
            ],
            $provider->ob_version
        );

        // Update stored tokens if refresh was successful
        if ($response->isSuccess() && isset($response->getData()['access_token'])) {
            $this->updateConnectionTokens(
                $userId,
                $response->getData()['access_token'],
                $response->getData()['refresh_token'] ?? $connection->refresh_token
            );
        }

        return $response;
    }

    /**
     * Get all available endpoints for a provider
     */
    public function getProviderEndpoints(BackpackProvider $provider): array
    {
        return $this->registry->getAllEndpoints($provider);
    }

    /**
     * Make a custom API call to any provider endpoint
     */
    public function customApiCall(
        int $userId, 
        string $endpointName, 
        string $method = 'GET', 
        ?array $data = null,
        array $parameters = []
    ): OpenBadgesApiResponse {
        $connection = $this->getUserConnection($userId);
        if (!$connection) {
            return OpenBadgesApiResponse::error('No backpack connection found for user');
        }

        $provider = $this->getProvider($connection->backpack_provider_id);
        if (!$provider) {
            return OpenBadgesApiResponse::error('Backpack provider not found');
        }

        $endpoint = $this->registry->getEndpoint($provider, $endpointName, $parameters);
        
        return $this->client->authenticatedRequest(
            $endpoint,
            $method,
            $connection->access_token,
            $data,
            $provider->ob_version
        );
    }

    /**
     * Batch operation: Get multiple badges details
     */
    public function getBatchBadgeDetails(int $userId, array $badgeIds): array
    {
        $results = [];
        foreach ($badgeIds as $badgeId) {
            $results[$badgeId] = $this->getBadgeDetails($userId, $badgeId);
        }
        return $results;
    }

    /**
     * Get provider capabilities based on OpenBadges version
     */
    public function getProviderCapabilities(BackpackProvider $provider): array
    {
        return $this->registry->getCapabilities($provider);
    }

    // Private helper methods

    private function getUserConnection(int $userId): ?object
    {
        $result = $this->db->querySingle(
            "SELECT ubc.*, bp.name as provider_name, bp.ob_version, bp.api_url 
             FROM user_backpack_connection ubc 
             JOIN backpack_provider bp ON ubc.backpack_provider_id = bp.id 
             WHERE ubc.user_id = ?d AND ubc.status = 'connected' AND bp.active = 1",
            $userId
        );
        
        // Database returns false when no rows found, convert to null for type hint
        return $result ?: null;
    }

    private function getProvider(int $providerId): ?BackpackProvider
    {
        $row = $this->db->querySingle(
            "SELECT * FROM backpack_provider WHERE id = ?d AND active = 1",
            $providerId
        );
        
        return $row ? BackpackProvider::fromRow($row) : null;
    }

    private function updateConnectionTokens(int $userId, string $accessToken, string $refreshToken): bool
    {
        try {
            $result = $this->db->query(
                "UPDATE user_backpack_connection 
                 SET access_token = ?s, refresh_token = ?s, updated_at = NOW() 
                 WHERE user_id = ?d",
                $accessToken, $refreshToken, $userId
            );

            return $result && is_object($result) && 
                   (isset($result->affectedRows) ? $result->affectedRows > 0 : true);
        } catch (Exception $e) {
            error_log('Token update error: ' . $e->getMessage());
            return false;
        }
    }
} 