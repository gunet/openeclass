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

require_once 'genericparam.php';

/**
 * ExternalReposApp
 * 
 * ExtApp class for managing external content repositories.
 * Allows administrators to configure connections to external repositories
 * such as DSpace, Reasonable Graph, YouTube, Wikipedia, and Pixabay.
 */
class ExternalReposApp extends ExtApp
{
    const NAME = "External Repositories";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the display name of the app
     * 
     * @return string
     */
    public function getDisplayName(): string
    {
        return self::NAME;
    }

    /**
     * Get short description for the app listing
     * 
     * @return string
     */
    public function getShortDescription(): string
    {
        return $GLOBALS['langExternalReposShortDescription'] ?? 
               'Connect to external content repositories (DSpace, YouTube, Wikipedia, etc.)';
    }

    /**
     * Get long description for the app details
     * 
     * @return string
     */
    public function getLongDescription(): string
    {
        return $GLOBALS['langExternalReposLongDescription'] ?? 
               'Configure connections to external multimedia and educational content repositories. ' .
               'Teachers can search and link content from these repositories into their course units.';
    }

    /**
     * Get the configuration URL for this app
     * 
     * @return string
     */
    public function getConfigUrl(): string
    {
        return 'modules/admin/externalreposconf.php';
    }

    /**
     * Check if the app is configured
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        // Check if at least one repository is configured and enabled
        $count = Database::get()->querySingle(
            "SELECT COUNT(*) as cnt FROM external_repository WHERE enabled = 1"
        );
        return $count && $count->cnt > 0;
    }

    /**
     * Get all configured repositories
     * 
     * @param bool $enabledOnly Only return enabled repositories
     * @return array
     */
    public static function getRepositories(bool $enabledOnly = false): array
    {
        $query = "SELECT * FROM external_repository";
        if ($enabledOnly) {
            $query .= " WHERE enabled = 1";
        }
        $query .= " ORDER BY name ASC";
        
        $result = Database::get()->queryArray($query);
        return $result ?: [];
    }

    /**
     * Get a single repository by ID
     * 
     * @param int $id Repository ID
     * @return object|null
     */
    public static function getRepository(int $id): ?object
    {
        return Database::get()->querySingle(
            "SELECT * FROM external_repository WHERE id = ?d",
            $id
        );
    }

    /**
     * Get supported repository types
     * 
     * @return array
     */
    public static function getRepositoryTypes(): array
    {
        return [
            'dspace' => [
                'name' => 'DSpace',
                'description' => $GLOBALS['langDSpaceDescription'] ?? 'DSpace digital repository',
                'auth_types' => ['none', 'api_key'],
                'icon' => 'fa-database',
                'hardcoded_url' => false,
                'requires_url' => true
            ],
            'reasonable_graph' => [
                'name' => 'Reasonable Graph',
                'description' => $GLOBALS['langReasonableGraphDescription'] ?? 'Reasonable Graph educational resources',
                'auth_types' => ['none', 'api_key'],
                'icon' => 'fa-project-diagram',
                'hardcoded_url' => false,
                'requires_url' => true
            ],
            'youtube' => [
                'name' => 'YouTube',
                'description' => $GLOBALS['langYouTubeDescription'] ?? 'YouTube video platform',
                'auth_types' => ['api_key'],
                'icon' => 'fa-youtube',
                'hardcoded_url' => true,
                'api_endpoint' => 'https://www.googleapis.com/youtube/v3'
            ],
            'wikipedia' => [
                'name' => 'Wikipedia',
                'description' => $GLOBALS['langWikipediaDescription'] ?? 'Wikipedia free encyclopedia',
                'auth_types' => ['none'],
                'icon' => 'fa-wikipedia-w',
                'hardcoded_url' => true,
                'api_endpoint' => 'https://en.wikipedia.org/w/api.php'
            ],
            'pixabay' => [
                'name' => 'Pixabay',
                'description' => $GLOBALS['langPixabayDescription'] ?? 'Pixabay free images and videos',
                'auth_types' => ['api_key'],
                'icon' => 'fa-image',
                'hardcoded_url' => true,
                'api_endpoint' => 'https://pixabay.com/api/'
            ],
            'islandora' => [
                'name' => 'Islandora',
                'description' => $GLOBALS['langIslandoraDescription'] ?? 'Drupal/Islandora repository (JSON:API Search)',
                'auth_types' => ['none', 'api_key'],
                'icon' => 'fa-archive',
                'hardcoded_url' => false,
                'requires_url' => true
            ]
        ];
    }

    /**
     * Get the hardcoded API endpoint for a repository type
     * 
     * @param string $type Repository type
     * @return string|null
     */
    public static function getHardcodedEndpoint(string $type): ?string
    {
        $types = self::getRepositoryTypes();
        return $types[$type]['api_endpoint'] ?? null;
    }

    /**
     * Save a repository configuration
     * 
     * @param array $data Repository data
     * @return int|bool Repository ID on success, false on failure
     */
    public static function saveRepository(array $data)
    {
        $now = date('Y-m-d H:i:s');
        
        // Use hardcoded endpoint if applicable
        $hardcodedEndpoint = self::getHardcodedEndpoint($data['type']);
        if ($hardcodedEndpoint) {
            $data['base_url'] = $hardcodedEndpoint;
        }
        
        if (!empty($data['id'])) {
            // Update existing repository
            $result = Database::get()->query(
                "UPDATE external_repository SET 
                    name = ?s,
                    type = ?s,
                    base_url = ?s,
                    api_key = ?s,
                    auth_type = ?s,
                    enabled = ?d,
                    config = ?s,
                    updated = ?t
                WHERE id = ?d",
                $data['name'],
                $data['type'],
                $data['base_url'] ?? '',
                $data['api_key'] ?? '',
                $data['auth_type'] ?? 'none',
                $data['enabled'] ?? 1,
                $data['config'] ?? null,
                $now,
                $data['id']
            );
            return $result ? $data['id'] : false;
        } else {
            // Insert new repository
            $result = Database::get()->query(
                "INSERT INTO external_repository 
                    (name, type, base_url, api_key, auth_type, enabled, config, created, updated)
                VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, ?t, ?t)",
                $data['name'],
                $data['type'],
                $data['base_url'] ?? '',
                $data['api_key'] ?? '',
                $data['auth_type'] ?? 'none',
                $data['enabled'] ?? 1,
                $data['config'] ?? null,
                $now,
                $now
            );
            return $result ? $result->lastInsertID : false;
        }
    }

    /**
     * Delete a repository
     * 
     * @param int $id Repository ID
     * @return bool
     */
    public static function deleteRepository(int $id): bool
    {
        // First delete any external resources linked to this repository
        Database::get()->query(
            "DELETE FROM external_resource WHERE repository_id = ?d",
            $id
        );
        
        // Then delete the repository itself
        $result = Database::get()->query(
            "DELETE FROM external_repository WHERE id = ?d",
            $id
        );
        
        return $result !== null;
    }

    /**
     * Toggle repository enabled status
     * 
     * @param int $id Repository ID
     * @param bool $enabled New enabled status
     * @return bool
     */
    public static function toggleRepository(int $id, bool $enabled): bool
    {
        $result = Database::get()->query(
            "UPDATE external_repository SET enabled = ?d, updated = ?t WHERE id = ?d",
            $enabled ? 1 : 0,
            date('Y-m-d H:i:s'),
            $id
        );
        return $result !== null;
    }
}

