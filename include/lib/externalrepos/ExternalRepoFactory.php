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
require_once __DIR__ . '/AbstractExternalRepo.php';
require_once __DIR__ . '/DSpaceRepository.php';
require_once __DIR__ . '/ReasonableGraphRepository.php';
require_once __DIR__ . '/YouTubeRepository.php';
require_once __DIR__ . '/WikipediaRepository.php';
require_once __DIR__ . '/PixabayRepository.php';
require_once __DIR__ . '/IslandoraRepository.php';

/**
 * ExternalRepoFactory
 * 
 * Factory class for creating external repository instances.
 * Returns the appropriate repository class based on the repository type.
 */
class ExternalRepoFactory
{
    /**
     * Mapping of repository types to their class names
     */
    private static array $typeMapping = [
        'dspace' => 'DSpaceRepository',
        'reasonable_graph' => 'ReasonableGraphRepository',
        'youtube' => 'YouTubeRepository',
        'wikipedia' => 'WikipediaRepository',
        'pixabay' => 'PixabayRepository',
        'islandora' => 'IslandoraRepository'
    ];

    /**
     * Create a repository instance based on configuration
     * 
     * @param object $config Repository configuration from database
     * @return ExternalRepoInterface|null Repository instance or null if type is unsupported
     */
    public static function create(object $config): ?ExternalRepoInterface
    {
        if (!isset($config->type) || !isset(self::$typeMapping[$config->type])) {
            error_log("ExternalRepoFactory: Unknown repository type: " . ($config->type ?? 'null'));
            return null;
        }

        $className = self::$typeMapping[$config->type];
        
        try {
            return new $className($config);
        } catch (Exception $e) {
            error_log("ExternalRepoFactory: Failed to create $className: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a repository instance by ID
     * 
     * @param int $repositoryId Repository ID
     * @return ExternalRepoInterface|null Repository instance or null if not found
     */
    public static function createById(int $repositoryId): ?ExternalRepoInterface
    {
        $config = Database::get()->querySingle(
            "SELECT * FROM external_repository WHERE id = ?d",
            $repositoryId
        );

        if (!$config) {
            error_log("ExternalRepoFactory: Repository not found: $repositoryId");
            return null;
        }

        return self::create($config);
    }

    /**
     * Get all enabled repository instances
     * 
     * @return array Array of ExternalRepoInterface instances
     */
    public static function getAllEnabled(): array
    {
        $repositories = [];
        $configs = Database::get()->queryArray(
            "SELECT * FROM external_repository WHERE enabled = 1 ORDER BY name"
        );

        foreach ($configs as $config) {
            $repo = self::create($config);
            if ($repo) {
                $repositories[] = $repo;
            }
        }

        return $repositories;
    }

    /**
     * Get supported repository types
     * 
     * @return array List of supported type identifiers
     */
    public static function getSupportedTypes(): array
    {
        return array_keys(self::$typeMapping);
    }

    /**
     * Check if a repository type is supported
     * 
     * @param string $type Repository type
     * @return bool
     */
    public static function isTypeSupported(string $type): bool
    {
        return isset(self::$typeMapping[$type]);
    }
}

