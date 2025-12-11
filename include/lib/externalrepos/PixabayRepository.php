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
 * PixabayRepository
 * 
 * Implementation for Pixabay API.
 * Allows searching and using free images and videos from Pixabay.
 */
class PixabayRepository extends AbstractExternalRepo
{
    /** @var string Pixabay API base URL */
    private const API_BASE = 'https://pixabay.com/api/';

    /**
     * Get repository type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'pixabay';
    }

    /**
     * Pixabay doesn't require a custom base URL
     * 
     * @return bool
     */
    protected function requiresBaseUrl(): bool
    {
        return false;
    }

    /**
     * Check if properly configured
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        // Pixabay requires an API key
        return !empty($this->apiKey);
    }

    /**
     * Search for images/videos on Pixabay
     * 
     * @param string $query Search query
     * @param array $filters Optional filters (type, category, colors, etc.)
     * @param int $page Page number
     * @param int $perPage Results per page (3-200)
     * @return array Search results
     */
    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if (!$this->isConfigured()) {
            return $this->buildErrorResponse('Pixabay API key is required');
        }

        if (empty(trim($query))) {
            return $this->buildErrorResponse('Search query cannot be empty');
        }

        // Pixabay limits per_page to 3-200
        $perPage = max(3, min($perPage, 200));

        try {
            // Determine if searching for images or videos
            $mediaType = $filters['mediaType'] ?? 'image'; // image or video
            $endpoint = $mediaType === 'video' ? self::API_BASE . 'videos/' : self::API_BASE;

            $params = [
                'key' => $this->apiKey,
                'q' => $query,
                'page' => $page,
                'per_page' => $perPage,
                'safesearch' => 'true',
                'lang' => $filters['language'] ?? 'en'
            ];

            // Add image-specific filters
            if ($mediaType === 'image') {
                if (!empty($filters['image_type'])) {
                    $params['image_type'] = $filters['image_type']; // all, photo, illustration, vector
                }
                if (!empty($filters['orientation'])) {
                    $params['orientation'] = $filters['orientation']; // all, horizontal, vertical
                }
                if (!empty($filters['colors'])) {
                    $params['colors'] = $filters['colors']; // grayscale, transparent, red, orange, etc.
                }
            }

            // Add video-specific filters
            if ($mediaType === 'video') {
                if (!empty($filters['video_type'])) {
                    $params['video_type'] = $filters['video_type']; // all, film, animation
                }
            }

            // Add category filter
            if (!empty($filters['category'])) {
                $params['category'] = $filters['category'];
            }

            // Add editorial filter
            if (isset($filters['editors_choice'])) {
                $params['editors_choice'] = $filters['editors_choice'] ? 'true' : 'false';
            }

            $response = $this->httpGet($endpoint, $params);

            if (!$response['success']) {
                error_log("Pixabay search failed: " . ($response['error'] ?? 'Unknown error'));
                return $this->buildErrorResponse(
                    $GLOBALS['langSearchError'] ?? 'Search failed',
                    $response['http_code']
                );
            }

            return $this->parseSearchResults($response['data'], $page, $perPage, $mediaType);

        } catch (Exception $e) {
            error_log("Pixabay search exception: " . $e->getMessage());
            return $this->buildErrorResponse($e->getMessage());
        }
    }

    /**
     * Get details of a single image/video
     * 
     * @param string $itemId Pixabay item ID
     * @return array|null Item details or null if not found
     */
    public function getItem(string $itemId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            // Try image first
            $params = [
                'key' => $this->apiKey,
                'id' => $itemId
            ];

            $response = $this->httpGet(self::API_BASE, $params);

            if ($response['success'] && !empty($response['data']['hits'])) {
                return $this->parseImageItem($response['data']['hits'][0]);
            }

            // Try video if image not found
            $response = $this->httpGet(self::API_BASE . 'videos/', $params);

            if ($response['success'] && !empty($response['data']['hits'])) {
                return $this->parseVideoItem($response['data']['hits'][0]);
            }

            return null;

        } catch (Exception $e) {
            error_log("Pixabay getItem exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test connection to Pixabay API
     * 
     * @return array Result with success and message
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => $GLOBALS['langPixabayApiKeyRequired'] ?? 'Pixabay API key is required'
            ];
        }

        try {
            $params = [
                'key' => $this->apiKey,
                'q' => 'test',
                'per_page' => 3
            ];

            $response = $this->httpGet(self::API_BASE, $params);

            if ($response['success'] && isset($response['data']['total'])) {
                return [
                    'success' => true,
                    'message' => $GLOBALS['langConnectionSuccess'] ?? 'Connection successful'
                ];
            }

            // Parse Pixabay error
            $errorMessage = $response['data']['error'] ?? 
                           ($GLOBALS['langConnectionFailed'] ?? 'Connection failed');

            return [
                'success' => false,
                'message' => $errorMessage
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Parse search results into standardized format
     * 
     * @param array $data Raw API response data
     * @param int $page Current page
     * @param int $perPage Results per page
     * @param string $mediaType 'image' or 'video'
     * @return array Standardized search results
     */
    private function parseSearchResults(array $data, int $page, int $perPage, string $mediaType): array
    {
        $items = [];
        $total = $data['totalHits'] ?? $data['total'] ?? 0;

        foreach ($data['hits'] ?? [] as $hit) {
            if ($mediaType === 'video') {
                $items[] = $this->parseVideoItem($hit);
            } else {
                $items[] = $this->parseImageItem($hit);
            }
        }

        return $this->buildSearchResults($items, $total, $page, $perPage);
    }

    /**
     * Parse an image item
     * 
     * @param array $item Raw image data
     * @return array Standardized item
     */
    private function parseImageItem(array $item): array
    {
        return $this->buildResultItem(
            (string)$item['id'],
            $item['tags'] ?? 'Untitled',
            null,
            $item['pageURL'],
            'image',
            $item['previewURL'] ?? $item['webformatURL'],
            [
                'type' => $item['type'] ?? 'photo',
                'imageWidth' => $item['imageWidth'] ?? null,
                'imageHeight' => $item['imageHeight'] ?? null,
                'imageSize' => $item['imageSize'] ?? null,
                'views' => $item['views'] ?? null,
                'downloads' => $item['downloads'] ?? null,
                'likes' => $item['likes'] ?? null,
                'user' => $item['user'] ?? null,
                'userImageURL' => $item['userImageURL'] ?? null,
                // Image URLs
                'previewURL' => $item['previewURL'] ?? null,
                'webformatURL' => $item['webformatURL'] ?? null,
                'largeImageURL' => $item['largeImageURL'] ?? null,
                'fullHDURL' => $item['fullHDURL'] ?? null,
                'imageURL' => $item['imageURL'] ?? null,
                // License
                'license' => 'Pixabay License (Free for commercial use)'
            ]
        );
    }

    /**
     * Parse a video item
     * 
     * @param array $item Raw video data
     * @return array Standardized item
     */
    private function parseVideoItem(array $item): array
    {
        // Get best video URL
        $videos = $item['videos'] ?? [];
        $videoUrl = $videos['large']['url'] ?? $videos['medium']['url'] ?? $videos['small']['url'] ?? null;
        $thumbnail = $item['videos']['tiny']['thumbnail'] ?? null;

        // If no thumbnail from video, try picture_id
        if (!$thumbnail && !empty($item['picture_id'])) {
            $thumbnail = "https://i.vimeocdn.com/video/{$item['picture_id']}_640x360.jpg";
        }

        return $this->buildResultItem(
            (string)$item['id'],
            $item['tags'] ?? 'Untitled',
            null,
            $item['pageURL'],
            'video',
            $thumbnail,
            [
                'type' => $item['type'] ?? 'film',
                'duration' => $item['duration'] ?? null,
                'views' => $item['views'] ?? null,
                'downloads' => $item['downloads'] ?? null,
                'likes' => $item['likes'] ?? null,
                'user' => $item['user'] ?? null,
                'userImageURL' => $item['userImageURL'] ?? null,
                // Video URLs by quality
                'videos' => $videos,
                'embedUrl' => $videoUrl,
                // License
                'license' => 'Pixabay License (Free for commercial use)'
            ]
        );
    }

    /**
     * Pixabay doesn't use header-based authentication
     * API key is passed as query parameter, so don't add Authorization header
     * 
     * @return string
     */
    protected function getAuthHeader(): string
    {
        return ''; // Pixabay uses query param 'key', not Authorization header
    }

    /**
     * Get available Pixabay categories
     * 
     * @return array Categories
     */
    public static function getCategories(): array
    {
        return [
            'backgrounds',
            'fashion',
            'nature',
            'science',
            'education',
            'feelings',
            'health',
            'people',
            'religion',
            'places',
            'animals',
            'industry',
            'computer',
            'food',
            'sports',
            'transportation',
            'travel',
            'buildings',
            'business',
            'music'
        ];
    }
}


