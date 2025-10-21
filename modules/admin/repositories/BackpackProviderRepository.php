<?php

require_once dirname(__DIR__) . '/entities/BackpackProvider.php';

/**
 * BackpackProvider Repository
 * 
 * Handles all database operations for BackpackProvider entities
 * Uses functional programming concepts for data transformation
 */
class BackpackProviderRepository
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::get();
    }

    /**
     * Find all providers with optional filtering
     */
    public function findAll(callable $filter = null): array
    {
        $providers = $this->db->queryArray(
            "SELECT id, name, api_url, ob_version, active, created_at, updated_at,
                    client_id, client_secret, authorization_endpoint, token_endpoint, registration_endpoint
             FROM backpack_provider 
             ORDER BY name ASC"
        );

        $entities = $this->mapToEntities($providers);

        return $filter ? array_filter($entities, $filter) : $entities;
    }

    /**
     * Find provider by ID
     */
    public function findById(int $id): ?BackpackProvider
    {
        $provider = $this->db->querySingle(
            "SELECT id, name, api_url, ob_version, active, created_at, updated_at,
                    client_id, client_secret, authorization_endpoint, token_endpoint, registration_endpoint
             FROM backpack_provider 
             WHERE id = ?d",
            $id
        );

        return $provider ? BackpackProvider::fromRow($provider) : null;
    }

    /**
     * Find active providers only
     */
    public function findActive(): array
    {
        return $this->findAll(fn($provider) => $provider->isEnabled());
    }

    /**
     * Save a new provider
     */
    public function save(BackpackProvider $provider): ?BackpackProvider
    {
        $errors = $provider->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $data = $provider->toArray();

        $result = $this->db->query(
            "INSERT INTO backpack_provider (name, api_url, ob_version, active, client_id, client_secret, 
                    authorization_endpoint, token_endpoint, registration_endpoint, created_at, updated_at) 
             VALUES (?s, ?s, ?s, ?d, ?s, ?s, ?s, ?s, ?s, NOW(), NOW())",
            $data['name'],
            $data['api_url'],
            $data['ob_version'],
            $data['active'],
            $data['client_id'],
            $data['client_secret'],
            $data['authorization_endpoint'],
            $data['token_endpoint'],
            $data['registration_endpoint']
        );

        if ($result && is_object($result) && isset($result->lastInsertID)) {
            $lastId = $result->lastInsertID;
            return $this->findById($lastId);
        }

        return null;
    }

    /**
     * Update an existing provider
     */
    public function update(BackpackProvider $provider): ?BackpackProvider
    {
        if (!$provider->id) {
            throw new InvalidArgumentException('Provider ID is required for update');
        }

        $errors = $provider->validate();
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }

        $result = $this->db->query(
            "UPDATE backpack_provider 
             SET name = ?s, api_url = ?s, ob_version = ?s, active = ?d, 
                 client_id = ?s, client_secret = ?s, authorization_endpoint = ?s, 
                 token_endpoint = ?s, registration_endpoint = ?s, updated_at = NOW() 
             WHERE id = ?d",
            $provider->name,
            $provider->api_url,
            $provider->ob_version,
            $provider->active ? 1 : 0,
            $provider->client_id,
            $provider->client_secret,
            $provider->authorization_endpoint,
            $provider->token_endpoint,
            $provider->registration_endpoint,
            $provider->id
        );

        if ($result && is_object($result) && isset($result->affectedRows)) {
            return $result->affectedRows > 0 ? $this->findById($provider->id) : null;
        }

        return null;
    }

    /**
     * Delete a provider
     */
    public function delete(int $id): bool
    {
        error_log("Repository delete called with ID: " . $id);
        $result = $this->db->query("DELETE FROM backpack_provider WHERE id = ?d", $id);
        
        if ($result && is_object($result) && isset($result->affectedRows)) {
            return $result->affectedRows > 0;
        }
        
        return false;
    }

    /**
     * Check if provider exists by name
     */
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM backpack_provider WHERE name = ?s";
        $params = [$name];

        if ($excludeId) {
            $sql .= " AND id != ?d";
            $params[] = $excludeId;
        }

        $result = $this->db->querySingle($sql, ...$params);
        return $result && $result->count > 0;
    }

    /**
     * Find providers that support OpenBadges 2.1
     */
    public function findVersion21Providers(): array
    {
        return $this->findAll(fn($provider) => $provider->isVersion21());
    }

    /**
     * Find providers with OAuth configuration
     */
    public function findProvidersWithOAuth(): array
    {
        return $this->findAll(fn($provider) => $provider->hasOAuthConfig());
    }

    /**
     * Map database rows to entities
     */
    private function mapToEntities(array $rows): array
    {
        return array_map(
            fn($row) => BackpackProvider::fromRow($row),
            $rows
        );
    }
} 