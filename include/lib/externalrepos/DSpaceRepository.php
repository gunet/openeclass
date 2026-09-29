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
    /** Default metadata profile when none is configured. */
    const PROFILE_DEFAULT = 'dublin_core';

    /**
     * Per-profile metadata field map.
     * Each logical field lists candidate metadata keys, tried in order;
     * the first non-empty value wins.
     *
     * The 'dublin_core' profile reproduces the historical hardcoded
     * behaviour exactly. The 'lom' profile is LOM-primary with Dublin
     * Core fallbacks, for Learning Object Repositories (e.g. Photodentro).
     */
    private const FIELD_MAP = [
        'dublin_core' => [
            'title'       => ['dc.title'],
            'description' => ['dc.description.abstract', 'dc.description'],
            'type'        => ['dc.type'],
            'authors'     => ['dc.contributor.author'],
            'date'        => ['dc.date.issued'],
            'url'         => ['dc.identifier.uri'],
        ],
        'lom' => [
            'title'       => ['lom.general-title', 'dc.title'],
            'description' => ['lom.general-description', 'dc.description.abstract'],
            'type'        => ['lom.technical-format'],
            'authors'     => ['lom.lifecycle-contribute-entity', 'lom.lifecycle-contribute-publisher'],
            'date'        => ['dc.date.issued', 'lom.annotation-published-on-date'],
            // Empty: LOM repos store a stale host in dc.identifier.uri,
            // so fall through to the handle-based URL on the configured base.
            'url'         => [],
        ],
    ];

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
                'sort' => 'score,DESC',
                'embed' => 'thumbnail' // inline the thumbnail bitstream
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
            $response = $this->httpGet("/server/api/core/items/$itemId", ['embed' => 'thumbnail']);

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

        $metadata = $data['metadata'] ?? [];
        $profile = $this->metadataProfile();
        $map = self::FIELD_MAP[$profile];

        $title = $this->resolveField($metadata, $map['title']) ?? 'Untitled';
        $description = $this->resolveField($metadata, $map['description']);
        $rawType = $this->resolveField($metadata, $map['type']);
        $authors = $this->resolveFieldMulti($metadata, $map['authors']);
        $date = $this->resolveField($metadata, $map['date']);

        $handle = $data['handle'] ?? null;

        // Build item URL from the profile's url candidates; when none
        // resolve, fall back to the handle (or UUID) on the configured base.
        $url = $this->resolveField($metadata, $map['url']);
        if (!$url) {
            // Try to derive public URL by removing '-api' from baseUrl
            $publicUrl = preg_replace('/-api\./', '.', $this->baseUrl);
            $url = $handle
                ? $publicUrl . '/handle/' . $handle
                : $publicUrl . '/items/' . $id;
        }

        // DC profile maps a dc.type string; LOM maps a MIME type.
        $resourceType = ($profile === 'lom')
            ? $this->mapMimeType($rawType)
            : $this->mapResourceType($rawType ?? 'document');

        $thumbnail = $this->getThumbnailUrl($data);

        return $this->buildResultItem(
            $id,
            $title,
            $description,
            $url,
            $resourceType,
            $thumbnail,
            [
                'authors' => $authors,
                'date' => $date,
                'handle' => $handle,
                'dspace_type' => $rawType,
                'metadata_profile' => $profile
            ]
        );
    }

    /**
     * Resolve the configured metadata profile, defaulting safely.
     *
     * @return string 'dublin_core' or 'lom'
     */
    private function metadataProfile(): string
    {
        $profile = $this->additionalConfig['metadata_profile'] ?? '';
        return isset(self::FIELD_MAP[$profile]) ? $profile : self::PROFILE_DEFAULT;
    }

    /**
     * Return the first non-empty metadata value among candidate keys.
     *
     * @param array $metadata Metadata array
     * @param array $keys Ordered candidate metadata keys
     * @return string|null
     */
    private function resolveField(array $metadata, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->getMetadataValue($metadata, $key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }
        return null;
    }

    /**
     * Return all values of the first candidate key that yields any.
     *
     * @param array $metadata Metadata array
     * @param array $keys Ordered candidate metadata keys
     * @return array
     */
    private function resolveFieldMulti(array $metadata, array $keys): array
    {
        foreach ($keys as $key) {
            $values = $this->getMetadataValues($metadata, $key);
            if (!empty($values)) {
                return $values;
            }
        }
        return [];
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
     * Get thumbnail URL for an item.
     *
     * Relies on the search/item request being made with ?embed=thumbnail,
     * which inlines the thumbnail bitstream. The bitstream's content link
     * is the actual image — the bare _links.thumbnail href only points at
     * a JSON resource, so it is not used as an <img> source.
     *
     * @param array $data Item data
     * @return string|null
     */
    private function getThumbnailUrl(array $data): ?string
    {
        // DSpace 7/8: embedded thumbnail bitstream, content link = real image.
        $contentHref = $data['_embedded']['thumbnail']['_links']['content']['href'] ?? null;
        if (is_string($contentHref) && $contentHref !== '') {
            return $this->absoluteThumbUrl($contentHref);
        }

        // Fallback: scan embedded bitstreams for a thumbnail-bundle entry.
        if (isset($data['_embedded']['bitstreams'])) {
            foreach ($data['_embedded']['bitstreams'] as $bitstream) {
                $bundleName = $bitstream['bundleName'] ?? '';
                if (strtolower($bundleName) === 'thumbnail'
                    || strpos(strtolower($bitstream['name'] ?? ''), 'thumb') !== false) {
                    if (isset($bitstream['_links']['content']['href'])) {
                        return $this->absoluteThumbUrl($bitstream['_links']['content']['href']);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Make a possibly-relative thumbnail URL absolute against the base URL.
     */
    private function absoluteThumbUrl(string $url): string
    {
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }
        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
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

    /**
     * Map a MIME type (LOM lom.technical-format) to a resource type.
     *
     * @param string|null $mime MIME type, e.g. "video/mp4"
     * @return string Standardized type
     */
    private function mapMimeType(?string $mime): string
    {
        if (!$mime) {
            return 'document';
        }
        $mime = strtolower(trim($mime));
        if (strncmp($mime, 'video/', 6) === 0) {
            return 'video';
        }
        if (strncmp($mime, 'audio/', 6) === 0) {
            return 'audio';
        }
        if (strncmp($mime, 'image/', 6) === 0) {
            return 'image';
        }
        return 'document';
    }
}

