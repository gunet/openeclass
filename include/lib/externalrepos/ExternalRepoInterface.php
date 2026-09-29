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

/**
 * ExternalRepoInterface
 * 
 * Interface for external repository implementations.
 * All repository types (DSpace, YouTube, Wikipedia, etc.) must implement this interface.
 */
interface ExternalRepoInterface
{
    /**
     * Search for content in the repository
     * 
     * @param string $query Search query
     * @param array $filters Optional filters (type, date range, etc.)
     * @param int $page Page number for pagination
     * @param int $perPage Results per page
     * @return array Search results with 'items', 'total', 'page', 'perPage' keys
     */
    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 20): array;

    /**
     * Get details of a single item
     * 
     * @param string $itemId External item ID
     * @return array|null Item details or null if not found
     */
    public function getItem(string $itemId): ?array;

    /**
     * Test the connection to the repository
     * 
     * @return array Result with 'success' (bool) and 'message' (string) keys
     */
    public function testConnection(): array;

    /**
     * Get the repository type identifier
     * 
     * @return string Repository type (e.g., 'dspace', 'youtube')
     */
    public function getType(): string;

    /**
     * Get the repository name
     * 
     * @return string Repository display name
     */
    public function getName(): string;

    /**
     * Check if the repository is properly configured
     * 
     * @return bool True if configured, false otherwise
     */
    public function isConfigured(): bool;
}


