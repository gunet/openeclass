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

require_once __DIR__ . '/AbstractExternalRepo.php';

/**
 * DSpaceRepository
 * 
 * Implementation for DSpace digital repositories.
 * Supports DSpace REST API v7+ for searching and retrieving items.
 */
class DSpaceRepository extends AbstractExternalRepo
{
    /**
     * Get repository type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'dspace';
    }

    /**
     * Search for items in DSpace repository
     * 
     * @param string $query Search query
     * @param array $filters Optional filters
     * @param int $page Page number
     * @param int $perPage Results per page
     * @return array Search results
     */
    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if (!$this->isConfigured()) {
            return $this->buildErrorResponse('Repository is not properly configured');
        }

        if (empty(trim($query))) {
            return $this->buildErrorResponse('Search query cannot be empty');
        }

        try {
            // DSpace 7+ REST API endpoint for discovery search
            $params = [
                'query' => $query,
                'page' => $page - 1, // DSpace uses 0-based pagination
                'size' => $perPage,
                'sort' => 'score,DESC'
            ];

            // Add filters if specified
            if (!empty($filters['type'])) {
                $params['dsoType'] = $filters['type'];
            }

            $response = $this->httpGet('/server/api/discover/search/objects', $params);

            if (!$response['success']) {
                $errorMsg = $response['error'] ?? ($GLOBALS['langSearchError'] ?? 'Search failed');
                if (isset($response['http_code'])) {
                    $errorMsg .= " (HTTP {$response['http_code']})";
                }
                error_log("DSpace search failed: " . $errorMsg);
                return $this->buildErrorResponse($errorMsg, $response['http_code'] ?? 0);
            }

            return $this->parseSearchResults($response['data'], $page, $perPage);

        } catch (Exception $e) {
            error_log("DSpace search exception: " . $e->getMessage());
            return $this->buildErrorResponse($e->getMessage());
        }
    }

    /**
     * Get details of a single item
     * 
     * @param string $itemId Item UUID
     * @return array|null Item details or null if not found
     */
    public function getItem(string $itemId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->httpGet("/server/api/core/items/$itemId");

            if (!$response['success'] || empty($response['data'])) {
                return null;
            }

            return $this->parseItem($response['data']);

        } catch (Exception $e) {
            error_log("DSpace getItem exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test connection to DSpace server
     * 
     * @return array Result with success and message
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => $GLOBALS['langRepoNotConfigured'] ?? 'Repository is not properly configured'
            ];
        }

        try {
            // Try to access the DSpace API root
            $response = $this->httpGet('/server/api');

            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => $GLOBALS['langConnectionSuccess'] ?? 'Connection successful'
                ];
            }

            return [
                'success' => false,
                'message' => ($GLOBALS['langConnectionFailed'] ?? 'Connection failed') . 
                            " (HTTP {$response['http_code']})"
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse DSpace search results into standardized format
     * 
     * @param array $data Raw API response data
     * @param int $page Current page
     * @param int $perPage Results per page
     * @return array Standardized search results
     */
    private function parseSearchResults(array $data, int $page, int $perPage): array
    {
        $items = [];
        $total = $data['_embedded']['searchResult']['page']['totalElements'] ?? 0;

        if (isset($data['_embedded']['searchResult']['_embedded']['objects'])) {
            foreach ($data['_embedded']['searchResult']['_embedded']['objects'] as $object) {
                $indexableObject = $object['_embedded']['indexableObject'] ?? null;
                if ($indexableObject) {
                    $item = $this->parseItem($indexableObject);
                    if ($item) {
                        $items[] = $item;
                    }
                }
            }
        }

        return $this->buildSearchResults($items, $total, $page, $perPage);
    }

    /**
     * Parse a single DSpace item into standardized format
     * 
     * @param array $data Raw item data
     * @return array|null Standardized item or null if invalid
     */
    private function parseItem(array $data): ?array
    {
        $id = $data['uuid'] ?? $data['id'] ?? null;
        if (!$id) {
            return null;
        }

        // Extract metadata
        $metadata = $data['metadata'] ?? [];
        $title = $this->getMetadataValue($metadata, 'dc.title') ?? 'Untitled';
        $description = $this->getMetadataValue($metadata, 'dc.description.abstract') 
                      ?? $this->getMetadataValue($metadata, 'dc.description');
        $type = $this->getMetadataValue($metadata, 'dc.type') ?? 'document';
        $authors = $this->getMetadataValues($metadata, 'dc.contributor.author');
        $date = $this->getMetadataValue($metadata, 'dc.date.issued');

        $handle = $data['handle'] ?? null;

        // Build item URL - prefer dc.identifier.uri from metadata (canonical public URL)
        $url = $this->getMetadataValue($metadata, 'dc.identifier.uri');

        // Fallback: construct URL from handle or UUID if dc.identifier.uri not available
        if (!$url) {
            // Try to derive public URL by removing '-api' from baseUrl
            $publicUrl = preg_replace('/-api\./', '.', $this->baseUrl);
            $url = $handle
                ? $publicUrl . '/handle/' . $handle
                : $publicUrl . '/items/' . $id;
        }

        // Get thumbnail URL
        $thumbnail = $this->getThumbnailUrl($data);

        return $this->buildResultItem(
            $id,
            $title,
            $description,
            $url,
            $this->mapResourceType($type),
            $thumbnail,
            [
                'authors' => $authors,
                'date' => $date,
                'handle' => $handle,
                'dspace_type' => $type
            ]
        );
    }

    /**
     * Get a single metadata value
     * 
     * @param array $metadata Metadata array
     * @param string $key Metadata key
     * @return string|null
     */
    private function getMetadataValue(array $metadata, string $key): ?string
    {
        if (isset($metadata[$key]) && is_array($metadata[$key]) && !empty($metadata[$key])) {
            return $metadata[$key][0]['value'] ?? null;
        }
        return null;
    }

    /**
     * Get all values for a metadata key
     * 
     * @param array $metadata Metadata array
     * @param string $key Metadata key
     * @return array
     */
    private function getMetadataValues(array $metadata, string $key): array
    {
        $values = [];
        if (isset($metadata[$key]) && is_array($metadata[$key])) {
            foreach ($metadata[$key] as $item) {
                if (isset($item['value'])) {
                    $values[] = $item['value'];
                }
            }
        }
        return $values;
    }

    /**
     * Get thumbnail URL for an item
     * 
     * @param array $data Item data
     * @return string|null
     */
    private function getThumbnailUrl(array $data): ?string
    {
        // DSpace 7+ provides thumbnail links
        if (isset($data['_links']['thumbnail']['href'])) {
            $thumbnailUrl = $data['_links']['thumbnail']['href'];
            
            // Convert relative URL to absolute if needed
            if (!preg_match('/^https?:\/\//', $thumbnailUrl)) {
                // If it's a relative URL, prepend the base URL
                $thumbnailUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($thumbnailUrl, '/');
            }
            
            return $thumbnailUrl;
        }
        
        // Also check for bitstreams that might be thumbnails
        if (isset($data['_embedded']['bitstreams'])) {
            foreach ($data['_embedded']['bitstreams'] as $bitstream) {
                $bundleName = $bitstream['bundleName'] ?? '';
                if (strtolower($bundleName) === 'thumbnail' || strpos(strtolower($bitstream['name'] ?? ''), 'thumb') !== false) {
                    if (isset($bitstream['_links']['content']['href'])) {
                        $thumbnailUrl = $bitstream['_links']['content']['href'];
                        if (!preg_match('/^https?:\/\//', $thumbnailUrl)) {
                            $thumbnailUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($thumbnailUrl, '/');
                        }
                        return $thumbnailUrl;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Map DSpace type to standardized resource type
     * 
     * @param string $type DSpace type
     * @return string Standardized type
     */
    private function mapResourceType(string $type): string
    {
        $typeMap = [
            'Article' => 'article',
            'Book' => 'document',
            'Book chapter' => 'document',
            'Conference paper' => 'document',
            'Dataset' => 'document',
            'Image' => 'image',
            'Video' => 'video',
            'Audio' => 'audio',
            'Thesis' => 'document',
            'Technical Report' => 'document',
            'Learning Object' => 'learning_object'
        ];

        return $typeMap[$type] ?? 'document';
    }
}

