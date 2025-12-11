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
 * ReasonableGraphRepository
 * 
 * Implementation for Academy of Athens Reasonable Graph repository.
 * Provides access to Greek cultural heritage, educational materials, and digital archives.
 * 
 * API Documentation: https://repo.academyofathens.gr/
 */
class ReasonableGraphRepository extends AbstractExternalRepo
{
    /**
     * Get repository type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'reasonable_graph';
    }
    
    /**
     * Reasonable Graph doesn't use header-based authentication
     * It's a public repository with no API key required
     * 
     * @return string
     */
    protected function getAuthHeader(): string
    {
        return ''; // Public repository, no authentication needed
    }

    /**
     * Search for learning objects
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
            // Academy of Athens API v5 parameters
            // API returns 30 results per page by default (resultsPerPage: 30)
            $params = [
                'term' => $query,  // API uses 'term' not 'q'
                'page' => $page,
                'limit' => min($perPage, 50)  // Max 50 items per page
            ];

            // Add type filter if specified (form_type in API)
            if (!empty($filters['type'])) {
                $params['type'] = $filters['type'];
            }

            // Add language filter if specified
            if (!empty($filters['l'])) {
                $params['l'] = $filters['l'];  // Language parameter
            }

            $response = $this->httpGet('/api/v5/search', $params);

            if (!$response['success']) {
                error_log("Reasonable Graph search failed: " . ($response['error'] ?? 'Unknown error'));
                return $this->buildErrorResponse(
                    $GLOBALS['langSearchError'] ?? 'Search failed',
                    $response['http_code']
                );
            }

            return $this->parseSearchResults($response['data'], $page, $perPage);

        } catch (Exception $e) {
            error_log("Reasonable Graph search exception: " . $e->getMessage());
            return $this->buildErrorResponse($e->getMessage());
        }
    }

    /**
     * Get details of a single item from Academy of Athens
     * 
     * @param string $itemId Item ID (vid)
     * @return array|null Item details or null if not found
     */
    public function getItem(string $itemId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            // Academy of Athens doesn't have a single-item API endpoint
            // So we search by ID to get the item
            $params = ['term' => $itemId, 'limit' => 1];
            $response = $this->httpGet('/api/v5/search', $params);

            if (!$response['success'] || empty($response['data'])) {
                return null;
            }

            $docs = $response['data']['resultset']['response']['docs'] ?? [];
            if (empty($docs)) {
                return null;
            }

            return $this->parseItem($docs[0]);

        } catch (Exception $e) {
            error_log("Reasonable Graph getItem exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test connection to Academy of Athens server
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
            // Test with a simple search query
            $response = $this->httpGet('/api/v5/search', ['term' => 'Ελλάς', 'limit' => 1]);

            if ($response['success'] && isset($response['data']['total_cnt'])) {
                return [
                    'success' => true,
                    'message' => ($GLOBALS['langConnectionSuccess'] ?? 'Connection successful') . 
                                ' (Academy of Athens)'
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
     * Parse Academy of Athens search results into standardized format
     * 
     * @param array $data Raw API response data
     * @param int $page Current page
     * @param int $perPage Results per page
     * @return array Standardized search results
     */
    private function parseSearchResults(array $data, int $page, int $perPage): array
    {
        $items = [];
        
        // Academy of Athens API structure
        $total = $data['total_cnt'] ?? 0;
        $results = $data['resultset']['response']['docs'] ?? [];

        foreach ($results as $doc) {
            $item = $this->parseItem($doc);
            if ($item) {
                $items[] = $item;
            }
        }

        return $this->buildSearchResults($items, $total, $page, $perPage);
    }

    /**
     * Parse a single Academy of Athens item into standardized format
     * 
     * @param array $doc Raw document data from API
     * @return array|null Standardized item or null if invalid
     */
    private function parseItem(array $doc): ?array
    {
        $id = $doc['id'] ?? null;
        $vid = $doc['vid'] ?? null;
        
        if (!$id && !$vid) {
            return null;
        }

        // Use label as primary title
        $title = $doc['label'] ?? 'Untitled';
        $form_type = $doc['form_type'] ?? 'item';
        
        // Parse opac1 JSON field for additional metadata
        $opac_data = [];
        if (!empty($doc['opac1'])) {
            $opac_data = json_decode($doc['opac1'], true) ?? [];
        }

        // Get more detailed title from opac1 if available
        if (!empty($opac_data['title'])) {
            $title = $opac_data['title'];
        }

        // Extract description from opac1
        // Try multiple fields: description, abstract, or even content preview
        $description = null;
        if (!empty($opac_data['description'])) {
            $description = $opac_data['description'];
        } elseif (!empty($opac_data['abstract'])) {
            $description = $opac_data['abstract'];
        } elseif (!empty($opac_data['summary'])) {
            $description = $opac_data['summary'];
        }
        
        // Build URL to the item
        $item_id = $vid ?? $id;
        $url = $this->baseUrl . '/item/' . $item_id;
        
        // Get thumbnail URLs - they're already full URLs from the API
        $thumbnail = null;
        if (!empty($doc['thumb_big'])) {
            $thumbnail = $doc['thumb_big'];
        } elseif (!empty($doc['thumb_small'])) {
            $thumbnail = $doc['thumb_small'];
        }
        
        // If thumbnail is relative, make it absolute
        if ($thumbnail && !preg_match('/^https?:\/\//', $thumbnail)) {
            $thumbnail = $this->baseUrl . '/' . ltrim($thumbnail, '/');
        }
        
        // Determine resource type from form_type and opac data
        $obj_type = $opac_data['obj_type'] ?? $form_type;
        $resource_type = $this->mapResourceType($obj_type, $form_type);
        
        // Extract additional metadata
        $metadata = [
            'form_type' => $form_type,
            'obj_type' => $obj_type,
            'vid' => $vid,
            'display' => $opac_data['display'] ?? null,
        ];
        
        // Add collection information
        if (!empty($opac_data['collection_prim'])) {
            $collections = [];
            foreach ($opac_data['collection_prim'] as $coll) {
                $collections[] = $coll['label'] ?? $coll['label_ol'] ?? null;
            }
            $metadata['collections'] = array_filter($collections);
        }
        
        // Add contributors if available
        if (!empty($opac_data['contributors'])) {
            $metadata['contributors'] = $opac_data['contributors'];
        }
        
        // Add keywords if available
        if (!empty($opac_data['keywords'])) {
            $metadata['keywords'] = $opac_data['keywords'];
        }
        
        // Add language if available
        if (!empty($opac_data['language'])) {
            $metadata['language'] = $opac_data['language'];
        }
        
        // Add date/year information if available
        if (!empty($opac_data['year'])) {
            $metadata['year'] = $opac_data['year'];
        }
        if (!empty($opac_data['date'])) {
            $metadata['date'] = $opac_data['date'];
        }
        
        // Add publisher if available
        if (!empty($opac_data['publisher'])) {
            $metadata['publisher'] = $opac_data['publisher'];
        }
        
        // Add source information
        $metadata['repository'] = 'Academy of Athens';
        $metadata['source_url'] = $url;

        return $this->buildResultItem(
            (string)$item_id,
            $title,
            $description,
            $url,
            $resource_type,
            $thumbnail,
            $metadata
        );
    }

    /**
     * Map Academy of Athens types to standardized resource types
     * 
     * @param string $obj_type Object type from opac1
     * @param string $form_type Form type from doc
     * @return string Standardized type
     */
    private function mapResourceType(string $obj_type, string $form_type): string
    {
        // Map Academy of Athens object types
        $typeMap = [
            'digital-item' => 'document',
            'digital-emblem' => 'image',
            'auth-place' => 'article',  // Geographic entries
            'auth-person' => 'article',  // Biographical entries
            'work' => 'document',
            'manifestation' => 'document',
            'video' => 'video',
            'audio' => 'audio',
            'image' => 'image',
        ];
        
        // Check obj_type first
        $typeLower = strtolower($obj_type);
        if (isset($typeMap[$typeLower])) {
            return $typeMap[$typeLower];
        }
        
        // Fallback to form_type mapping
        $formMap = [
            'item' => 'document',
            'person' => 'article',
            'place' => 'article',
            'organization' => 'article',
            'collection' => 'learning_object',
            'file' => 'document',
            'lemma' => 'article',
        ];
        
        $formLower = strtolower($form_type);
        return $formMap[$formLower] ?? 'learning_object';
    }
}


