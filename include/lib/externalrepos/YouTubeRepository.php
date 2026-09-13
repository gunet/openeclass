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
 * YouTubeRepository
 * 
 * Implementation for YouTube Data API v3.
 * Allows searching and embedding YouTube videos in courses.
 */
class YouTubeRepository extends AbstractExternalRepo
{
    /** @var string YouTube API base URL */
    private const API_BASE = 'https://www.googleapis.com/youtube/v3';

    /**
     * Get repository type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'youtube';
    }

    /**
     * YouTube doesn't require a custom base URL
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
        // YouTube requires an API key
        return !empty($this->apiKey);
    }

    /**
     * Search for videos on YouTube
     * 
     * @param string $query Search query
     * @param array $filters Optional filters (type, duration, etc.)
     * @param int $page Page number
     * @param int $perPage Results per page (max 50)
     * @return array Search results
     */
    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        if (!$this->isConfigured()) {
            return $this->buildErrorResponse('YouTube API key is required');
        }

        if (empty(trim($query))) {
            return $this->buildErrorResponse('Search query cannot be empty');
        }

        // YouTube API max is 50
        $perPage = min($perPage, 50);

        try {
            $params = [
                'part' => 'snippet',
                'q' => $query,
                'type' => 'video',
                'maxResults' => $perPage,
                'key' => $this->apiKey,
                'videoEmbeddable' => 'true', // Only embeddable videos
                'safeSearch' => $filters['safeSearch'] ?? 'moderate'
            ];

            // Handle pagination with page tokens
            if ($page > 1 && !empty($filters['pageToken'])) {
                $params['pageToken'] = $filters['pageToken'];
            }

            // Add duration filter
            if (!empty($filters['duration'])) {
                $params['videoDuration'] = $filters['duration']; // short, medium, long
            }

            // Add category filter
            if (!empty($filters['categoryId'])) {
                $params['videoCategoryId'] = $filters['categoryId'];
            }

            // Add license filter
            if (!empty($filters['license'])) {
                $params['videoLicense'] = $filters['license']; // creativeCommon, youtube
            }

            $response = $this->httpGet(self::API_BASE . '/search', $params);

            if (!$response['success']) {
                error_log("YouTube search failed: " . ($response['error'] ?? 'Unknown error'));
                return $this->buildErrorResponse(
                    $GLOBALS['langSearchError'] ?? 'Search failed',
                    $response['http_code']
                );
            }

            return $this->parseSearchResults($response['data'], $page, $perPage);

        } catch (Exception $e) {
            error_log("YouTube search exception: " . $e->getMessage());
            return $this->buildErrorResponse($e->getMessage());
        }
    }

    /**
     * Get details of a single video
     * 
     * @param string $videoId YouTube video ID
     * @return array|null Video details or null if not found
     */
    public function getItem(string $videoId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $params = [
                'part' => 'snippet,contentDetails,statistics',
                'id' => $videoId,
                'key' => $this->apiKey
            ];

            $response = $this->httpGet(self::API_BASE . '/videos', $params);

            if (!$response['success'] || empty($response['data']['items'])) {
                return null;
            }

            return $this->parseVideoItem($response['data']['items'][0]);

        } catch (Exception $e) {
            error_log("YouTube getItem exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test connection to YouTube API
     * 
     * @return array Result with success and message
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => $GLOBALS['langYouTubeApiKeyRequired'] ?? 'YouTube API key is required'
            ];
        }

        try {
            // Test with a simple search
            $params = [
                'part' => 'snippet',
                'q' => 'test',
                'type' => 'video',
                'maxResults' => 1,
                'key' => $this->apiKey
            ];

            $response = $this->httpGet(self::API_BASE . '/search', $params);

            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => $GLOBALS['langConnectionSuccess'] ?? 'Connection successful'
                ];
            }

            // Parse YouTube API error
            $errorMessage = $response['data']['error']['message'] ?? 
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
     * @return array Standardized search results
     */
    private function parseSearchResults(array $data, int $page, int $perPage): array
    {
        $items = [];
        // YouTube doesn't give exact total, estimate from pageInfo
        $total = $data['pageInfo']['totalResults'] ?? count($data['items'] ?? []);

        foreach ($data['items'] ?? [] as $item) {
            $parsedItem = $this->parseSearchItem($item);
            if ($parsedItem) {
                $items[] = $parsedItem;
            }
        }

        $results = $this->buildSearchResults($items, $total, $page, $perPage);
        
        // Add page tokens for pagination
        $results['nextPageToken'] = $data['nextPageToken'] ?? null;
        $results['prevPageToken'] = $data['prevPageToken'] ?? null;

        return $results;
    }

    /**
     * Parse a search result item
     * 
     * @param array $item Raw search item
     * @return array|null Standardized item or null if invalid
     */
    private function parseSearchItem(array $item): ?array
    {
        $videoId = $item['id']['videoId'] ?? null;
        if (!$videoId) {
            return null;
        }

        $snippet = $item['snippet'] ?? [];
        
        return $this->buildResultItem(
            $videoId,
            $snippet['title'] ?? 'Untitled',
            $snippet['description'] ?? null,
            "https://www.youtube.com/watch?v=$videoId",
            'video',
            $this->getBestThumbnail($snippet['thumbnails'] ?? []),
            [
                'channelId' => $snippet['channelId'] ?? null,
                'channelTitle' => $snippet['channelTitle'] ?? null,
                'publishedAt' => $snippet['publishedAt'] ?? null,
                'embedUrl' => "https://www.youtube.com/embed/$videoId"
            ]
        );
    }

    /**
     * Parse a full video item with details
     * 
     * @param array $item Raw video item
     * @return array|null Standardized item or null if invalid
     */
    private function parseVideoItem(array $item): ?array
    {
        $videoId = $item['id'] ?? null;
        if (!$videoId) {
            return null;
        }

        $snippet = $item['snippet'] ?? [];
        $contentDetails = $item['contentDetails'] ?? [];
        $statistics = $item['statistics'] ?? [];

        return $this->buildResultItem(
            $videoId,
            $snippet['title'] ?? 'Untitled',
            $snippet['description'] ?? null,
            "https://www.youtube.com/watch?v=$videoId",
            'video',
            $this->getBestThumbnail($snippet['thumbnails'] ?? []),
            [
                'channelId' => $snippet['channelId'] ?? null,
                'channelTitle' => $snippet['channelTitle'] ?? null,
                'publishedAt' => $snippet['publishedAt'] ?? null,
                'duration' => $contentDetails['duration'] ?? null,
                'viewCount' => $statistics['viewCount'] ?? null,
                'likeCount' => $statistics['likeCount'] ?? null,
                'embedUrl' => "https://www.youtube.com/embed/$videoId",
                'tags' => $snippet['tags'] ?? []
            ]
        );
    }

    /**
     * Get the best available thumbnail
     * 
     * @param array $thumbnails Thumbnails array
     * @return string|null Thumbnail URL
     */
    private function getBestThumbnail(array $thumbnails): ?string
    {
        // Prefer medium or high quality thumbnails
        $preferred = ['medium', 'high', 'standard', 'default'];
        
        foreach ($preferred as $quality) {
            if (isset($thumbnails[$quality]['url'])) {
                return $thumbnails[$quality]['url'];
            }
        }

        return null;
    }

    /**
     * YouTube doesn't use header-based authentication
     * API key is passed as query parameter, so don't add Authorization header
     * 
     * @return string
     */
    protected function getAuthHeader(): string
    {
        return ''; // YouTube uses query param 'key', not Authorization header
    }
}


