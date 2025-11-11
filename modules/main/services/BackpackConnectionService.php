<?php

/**
 * BackpackConnectionService
 * 
 * Handles backpack connection operations using functional programming principles
 */
class BackpackConnectionService
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::get();
    }

    /**
     * Get user's backpack connection with provider details
     * Only returns connections for active (enabled) providers
     */
    public function getUserConnection(int $userId): ?object
    {
        $result = $this->db->querySingle(
            "SELECT ubc.*, bp.name as provider_name, bp.ob_version, bp.api_url 
             FROM user_backpack_connection ubc 
             JOIN backpack_provider bp ON ubc.backpack_provider_id = bp.id 
             WHERE ubc.user_id = ?d AND ubc.status = 'connected' AND bp.active = 1",
            $userId
        );
        
        return $result ?: null;
    }

    /**
     * Create or update backpack connection
     */
    public function connectBackpack(
        int $userId, 
        int $providerId, 
        ?string $email = null, 
        ?string $password = null
    ): bool {
        try {
            $passwordHash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
            
            // Check if connection exists
            $existingConnection = $this->db->querySingle(
                "SELECT id FROM user_backpack_connection WHERE user_id = ?d AND backpack_provider_id = ?d",
                $userId, $providerId
            );

            if ($existingConnection) {
                // Update existing connection
                $result = $this->db->query(
                    "UPDATE user_backpack_connection 
                     SET email = ?s, password = ?s, status = 'connected', updated_at = NOW() 
                     WHERE user_id = ?d AND backpack_provider_id = ?d",
                    $email, $passwordHash, $userId, $providerId
                );
            } else {
                // Create new connection
                $result = $this->db->query(
                    "INSERT INTO user_backpack_connection 
                     (user_id, backpack_provider_id, email, password, status, created_at, updated_at) 
                     VALUES (?d, ?d, ?s, ?s, 'connected', NOW(), NOW())",
                    $userId, $providerId, $email, $passwordHash
                );
            }

            return $result && is_object($result) && 
                   (isset($result->affectedRows) ? $result->affectedRows > 0 : true);
                   
        } catch (Exception $e) {
            error_log('Backpack connection error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create or update backpack connection with OAuth tokens
     */
    public function connectBackpackWithTokens(
        int $userId, 
        int $providerId, 
        ?string $email = null, 
        ?string $accessToken = null,
        ?string $refreshToken = null
    ): bool {
        try {
            // Check if connection exists
            $existingConnection = $this->db->querySingle(
                "SELECT id FROM user_backpack_connection WHERE user_id = ?d AND backpack_provider_id = ?d",
                $userId, $providerId
            );

            if ($existingConnection) {
                // Update existing connection
                $result = $this->db->query(
                    "UPDATE user_backpack_connection 
                     SET email = ?s, access_token = ?s, refresh_token = ?s, status = 'connected', updated_at = NOW() 
                     WHERE user_id = ?d AND backpack_provider_id = ?d",
                    $email, $accessToken, $refreshToken, $userId, $providerId
                );
            } else {
                // Create new connection
                $result = $this->db->query(
                    "INSERT INTO user_backpack_connection 
                     (user_id, backpack_provider_id, email, access_token, refresh_token, status, created_at, updated_at) 
                     VALUES (?d, ?d, ?s, ?s, ?s, 'connected', NOW(), NOW())",
                    $userId, $providerId, $email, $accessToken, $refreshToken
                );
            }

            return $result && is_object($result) && 
                   (isset($result->affectedRows) ? $result->affectedRows > 0 : true);
                   
        } catch (Exception $e) {
            error_log('Backpack connection with tokens error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disconnect user's backpack and delete connection entry
     */
    public function disconnectBackpack(int $userId): bool
    {
        try {
            $result = $this->db->query(
                "DELETE FROM user_backpack_connection 
                 WHERE user_id = ?d",
                $userId
            );

            return $result && is_object($result) && 
                   (isset($result->affectedRows) ? $result->affectedRows > 0 : true);
                   
        } catch (Exception $e) {
            error_log('Backpack disconnection error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate connection data based on provider requirements
     */
    public function validateConnectionData(object $provider, ?string $email, ?string $password): array
    {
        $errors = [];

        if (in_array($provider->ob_version, ['2.0', '2.1'])) {
            if (empty(trim($email))) {
                $errors[] = trans('langEmailRequired');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = trans('langInvalidEmail');
            }

            if (empty(trim($password))) {
                $errors[] = trans('langPasswordRequired');
            }
        }

        return $errors;
    }

    /**
     * Update the selected collection for a user's backpack connection
     */
    public function updateSelectedCollection(
        int $userId, 
        string $collectionId, 
        string $collectionName
    ): bool {
        try {
            $result = $this->db->query(
                "UPDATE user_backpack_connection 
                 SET selected_collection_id = ?s, 
                     selected_collection_name = ?s,
                     last_sync = NOW(),
                     updated_at = NOW() 
                 WHERE user_id = ?d",
                $collectionId,
                $collectionName,
                $userId
            );

            return $result && is_object($result) && 
                   (isset($result->affectedRows) ? $result->affectedRows > 0 : true);
                   
        } catch (Exception $e) {
            error_log('Update selected collection error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the selected collection for a user's backpack connection
     */
    public function getSelectedCollection(int $userId): ?array
    {
        try {
            $result = $this->db->querySingle(
                "SELECT selected_collection_id, selected_collection_name, last_sync 
                 FROM user_backpack_connection 
                 WHERE user_id = ?d AND status = 'connected'",
                $userId
            );
            
            if ($result && $result->selected_collection_id) {
                return [
                    'id' => $result->selected_collection_id,
                    'name' => $result->selected_collection_name,
                    'last_sync' => $result->last_sync
                ];
            }
            
            return null;
                   
        } catch (Exception $e) {
            error_log('Get selected collection error: ' . $e->getMessage());
            return null;
        }
    }
} 