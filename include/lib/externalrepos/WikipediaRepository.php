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
 * WikipediaRepository
 * 
 * Implementation for Wikipedia/Wikidata API.
 * Uses Wikidata's structured knowledge base to search entities and link to
 * Wikipedia articles. Provides richer metadata and better search relevance.
 */
class WikipediaRepository extends AbstractExternalRepo
{
    /** @var string Default language for Wikipedia articles */
    private string $language;
    
    /** @var string Wikidata API endpoint */
    private const WIKIDATA_API = 'https://www.wikidata.org/w/api.php';

    /**
     * Constructor
     * 
     * @param object $config Repository configuration
     */
    public function __construct(object $config)
    {
        parent::__construct($config);
        
        // Get language from additional config or default to English
        $this->language = $this->additionalConfig['language'] ?? 'en';
    }

    /**
     * Get repository type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'wikipedia';
    }

    /**
     * Wikipedia doesn't require a custom base URL
     * 
     * @return bool
     */
    protected function requiresBaseUrl(): bool
    {
        return false;
    }

    /**
     * Wikipedia is always configured (no auth needed)
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * Search for entities on Wikidata with Wikipedia links
     * 
     * @param string $query Search query
     * @param array $filters Optional filters
     * @param int $page Page number
     * @param int $perPage Results per page
     * @return array Search results
     */
    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if (empty(trim($query))) {
            return $this->buildErrorResponse('Search query cannot be empty');
        }

        try {
            // Override language if specified in filters
            $lang = $filters['language'] ?? $this->language;
            
            // Wikidata doesn't support offset-based pagination well
            // We'll fetch more results and slice them
            $limit = min($perPage * 3, 50); // Fetch extra to account for filtering

            // Use Wikidata's entity search API
            $params = [
                'action' => 'wbsearchentities',
                'search' => $query,
                'language' => $lang,
                'limit' => $limit,
                'format' => 'json',
                'origin' => '*',
                'type' => 'item', // Search for items (entities)
                'props' => 'url|description|label|aliases' // Get rich metadata
            ];

            $response = $this->httpGet(self::WIKIDATA_API, $params);

            if (!$response['success']) {
                error_log("Wikidata search failed: " . ($response['error'] ?? 'Unknown error'));
                return $this->buildErrorResponse(
                    $GLOBALS['langSearchError'] ?? 'Search failed',
                    $response['http_code'] ?? 0
                );
            }

            return $this->parseWikidataResults($response['data'], $page, $perPage, $lang);

        } catch (Exception $e) {
            error_log("Wikidata search exception: " . $e->getMessage());
            return $this->buildErrorResponse($e->getMessage());
        }
    }

    /**
     * Get details of a single entity by Wikidata ID
     * 
     * @param string $entityId Wikidata entity ID (e.g., Q42)
     * @return array|null Entity details or null if not found
     */
    public function getItem(string $entityId): ?array
    {
        try {
            // Get entity data from Wikidata
            $params = [
                'action' => 'wbgetentities',
                'ids' => $entityId,
                'props' => 'labels|descriptions|sitelinks',
                'languages' => $this->language,
                'sitefilter' => $this->language . 'wiki',
                'format' => 'json',
                'origin' => '*'
            ];

            $response = $this->httpGet(self::WIKIDATA_API, $params);

            if (!$response['success'] || empty($response['data']['entities'][$entityId])) {
                return null;
            }

            $entity = $response['data']['entities'][$entityId];

            // Check if entity exists
            if (isset($entity['missing'])) {
                return null;
            }

            $title = $entity['labels'][$this->language]['value'] ?? 
                     $entity['labels']['en']['value'] ?? 'Untitled';
            $description = $entity['descriptions'][$this->language]['value'] ?? 
                          $entity['descriptions']['en']['value'] ?? '';

            // Get Wikipedia article URL
            $wikipediaUrl = $this->getWikipediaUrl($entityId, $this->language);
            if (!$wikipediaUrl) {
                // No Wikipedia article in requested language
                return null;
            }

            $thumbnail = $this->getEntityThumbnail($entityId);

            return $this->buildResultItem(
                $entityId,
                $title,
                $description,
                $wikipediaUrl,
                'article',
                $thumbnail,
                [
                    'wikidata_id' => $entityId,
                    'wikidata_url' => "https://www.wikidata.org/wiki/{$entityId}",
                    'language' => $this->language
                ]
            );

        } catch (Exception $e) {
            error_log("Wikidata getItem exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test connection to Wikidata API
     * 
     * @return array Result with success and message
     */
    public function testConnection(): array
    {
        try {
            // Test simple entity search
            $params = [
                'action' => 'wbsearchentities',
                'search' => 'test',
                'language' => 'en',
                'limit' => 1,
                'format' => 'json',
                'origin' => '*'
            ];

            $response = $this->httpGet(self::WIKIDATA_API, $params);

            if ($response['success'] && isset($response['data']['search'])) {
                return [
                    'success' => true,
                    'message' => ($GLOBALS['langConnectionSuccess'] ?? 'Connection successful') . 
                                ' (Wikidata)'
                ];
            }

            return [
                'success' => false,
                'message' => $GLOBALS['langConnectionFailed'] ?? 'Connection failed'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse Wikidata search results into standardized format
     * 
     * @param array $data Raw Wikidata API response data
     * @param int $page Current page
     * @param int $perPage Results per page
     * @param string $lang Language code
     * @return array Standardized search results
     */
    private function parseWikidataResults(array $data, int $page, int $perPage, string $lang): array
    {
        $allItems = [];
        $entities = $data['search'] ?? [];
        
        // Total available from Wikidata
        $totalFound = $data['search-continue'] ?? count($entities);

        foreach ($entities as $entity) {
            $item = $this->parseWikidataEntity($entity, $lang);
            if ($item) {
                $allItems[] = $item;
            }
        }

        // Apply pagination (simple client-side slicing)
        $offset = ($page - 1) * $perPage;
        $items = array_slice($allItems, $offset, $perPage);
        $total = count($allItems);

        return $this->buildSearchResults($items, $total, $page, $perPage);
    }

    /**
     * Parse a Wikidata entity into standardized format
     * 
     * @param array $entity Raw Wikidata entity
     * @param string $lang Language code for Wikipedia link
     * @return array|null Standardized item or null if no Wikipedia article exists
     */
    private function parseWikidataEntity(array $entity, string $lang): ?array
    {
        $entityId = $entity['id'] ?? null;
        $title = $entity['label'] ?? $entity['match']['text'] ?? 'Untitled';
        $description = $entity['description'] ?? '';
        
        if (!$entityId) {
            return null;
        }

        // Get Wikipedia article URL for this entity
        $wikipediaUrl = $this->getWikipediaUrl($entityId, $lang);
        
        // If no Wikipedia article exists in the requested language, skip this entity
        if (!$wikipediaUrl) {
            return null;
        }

        // Try to get thumbnail from Wikidata
        $thumbnail = $this->getEntityThumbnail($entityId);

        return $this->buildResultItem(
            $entityId,
            $title,
            $description,
            $wikipediaUrl,
            'article',
            $thumbnail,
            [
                'wikidata_id' => $entityId,
                'wikidata_url' => $entity['url'] ?? "https://www.wikidata.org/wiki/{$entityId}",
                'concepturi' => $entity['concepturi'] ?? null,
                'language' => $lang,
                'aliases' => $entity['aliases'] ?? []
            ]
        );
    }

    /**
     * Get Wikipedia article URL for a Wikidata entity
     * 
     * @param string $entityId Wikidata entity ID (e.g., Q42)
     * @param string $lang Language code
     * @return string|null Wikipedia URL or null if article doesn't exist
     */
    private function getWikipediaUrl(string $entityId, string $lang): ?string
    {
        try {
            $params = [
                'action' => 'wbgetentities',
                'ids' => $entityId,
                'props' => 'sitelinks',
                'sitefilter' => $lang . 'wiki',
                'format' => 'json',
                'origin' => '*'
            ];

            $response = $this->httpGet(self::WIKIDATA_API, $params);

            if ($response['success'] && isset($response['data']['entities'][$entityId])) {
                $entity = $response['data']['entities'][$entityId];
                $sitelink = $entity['sitelinks'][$lang . 'wiki'] ?? null;
                
                if ($sitelink && isset($sitelink['title'])) {
                    $title = $sitelink['title'];
                    return "https://{$lang}.wikipedia.org/wiki/" . urlencode(str_replace(' ', '_', $title));
                }
            }

            return null;

        } catch (Exception $e) {
            error_log("Failed to get Wikipedia URL for entity {$entityId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get thumbnail image URL for a Wikidata entity
     * 
     * @param string $entityId Wikidata entity ID
     * @return string|null Thumbnail URL or null if not available
     */
    private function getEntityThumbnail(string $entityId): ?string
    {
        try {
            // Get image property (P18) from Wikidata
            $params = [
                'action' => 'wbgetclaims',
                'entity' => $entityId,
                'property' => 'P18', // Image property
                'format' => 'json',
                'origin' => '*'
            ];

            $response = $this->httpGet(self::WIKIDATA_API, $params);

            if ($response['success'] && isset($response['data']['claims']['P18'][0])) {
                $claim = $response['data']['claims']['P18'][0];
                $filename = $claim['mainsnak']['datavalue']['value'] ?? null;
                
                if ($filename) {
                    // Generate Wikimedia Commons thumbnail URL
                    $md5 = md5($filename);
                    $dir1 = substr($md5, 0, 1);
                    $dir2 = substr($md5, 0, 2);
                    $encodedFilename = urlencode(str_replace(' ', '_', $filename));
                    
                    return "https://upload.wikimedia.org/wikipedia/commons/thumb/{$dir1}/{$dir2}/{$encodedFilename}/300px-{$encodedFilename}";
                }
            }

            return null;

        } catch (Exception $e) {
            // Thumbnails are optional, don't log errors
            return null;
        }
    }

    /**
     * Get available Wikipedia languages
     * 
     * @return array Language codes and names
     */
    public static function getAvailableLanguages(): array
    {
        return [
            'en' => 'English',
            'el' => 'Ελληνικά',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'es' => 'Español',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'ja' => '日本語',
            'zh' => '中文'
        ];
    }
}


