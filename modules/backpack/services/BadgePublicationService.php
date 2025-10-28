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
 * BadgePublicationService
 * 
 * Service class for publishing local badges to external backpack providers.
 * Handles validation, assertion URL generation, and communication with backpack APIs.
 */
class BadgePublicationService
{
    private Database $db;
    private OpenBadgesApiService $apiService;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::get();
        $this->apiService = new OpenBadgesApiService();
    }

    /**
     * Get user's awarded badges
     * 
     * @param int $userId User ID
     * @return array Array of awarded badges
     */
    public function getUserAwardedBadges(int $userId): array
    {
        $badges = $this->db->queryArray("
            SELECT 
                ub.id as user_badge_id,
                ub.badge as badge_id,
                ub.assigned as award_date,
                ub.completed,
                b.title,
                b.description,
                b.issuer,
                b.expires,
                bi.filename as icon_filename,
                c.title as course_title,
                c.code as course_code
            FROM user_badge ub
            JOIN badge b ON ub.badge = b.id
            LEFT JOIN badge_icon bi ON b.icon = bi.id
            LEFT JOIN course c ON b.course_id = c.id
            WHERE ub.user = ?d 
              AND ub.completed = 1 
              AND b.active = 1
              AND (b.expires IS NULL OR b.expires > NOW())
            ORDER BY ub.assigned DESC
        ", $userId);
        
        return $badges ?? [];
    }

    /**
     * Publish a badge to an external backpack provider
     * 
     * @param int $userId User ID (must be the badge owner)
     * @param int $userBadgeId User badge ID to publish
     * @param int $providerId Backpack provider ID
     * @return array Result array with success flag and details
     */
    public function publishBadgeToBackpack(int $userId, int $userBadgeId, int $providerId): array
    {
        try {
            // Step 1: Validate user owns the badge
            $userBadge = $this->db->querySingle("
                SELECT ub.*, b.active, b.expires
                FROM user_badge ub
                JOIN badge b ON ub.badge = b.id
                WHERE ub.id = ?d AND ub.user = ?d AND ub.completed = 1
            ", $userBadgeId, $userId);
            
            if (!$userBadge) {
                error_log("Badge publication: User $userId does not own badge $userBadgeId or badge is not completed");
                return [
                    'success' => false,
                    'http_code' => 403,
                    'error_message' => 'You do not have permission to publish this badge.'
                ];
            }
            
            // Check if badge is still active
            if (!$userBadge->active) {
                error_log("Badge publication: Badge $userBadge->badge is no longer active");
                return [
                    'success' => false,
                    'http_code' => 400,
                    'error_message' => 'This badge is no longer active.'
                ];
            }
            
            // Check if badge has expired
            if (!empty($userBadge->expires) && strtotime($userBadge->expires) < time()) {
                error_log("Badge publication: Badge $userBadge->badge has expired");
                return [
                    'success' => false,
                    'http_code' => 400,
                    'error_message' => 'This badge has expired.'
                ];
            }
            
            // Step 2: Verify user has connection to provider
            $connection = $this->db->querySingle("
                SELECT ubc.*
                FROM user_backpack_connection ubc
                WHERE ubc.user_id = ?d 
                  AND ubc.backpack_provider_id = ?d 
                  AND ubc.status = 'connected'
            ", $userId, $providerId);
            
            if (!$connection) {
                error_log("Badge publication: User $userId has no active connection to provider $providerId");
                return [
                    'success' => false,
                    'http_code' => 404,
                    'error_message' => 'No active backpack connection found. Please connect to a backpack provider first.'
                ];
            }
            
            // Check if access token is valid (not empty)
            if (empty($connection->access_token)) {
                error_log("Badge publication: No valid access token for user $userId on provider $providerId");
                return [
                    'success' => false,
                    'http_code' => 401,
                    'error_message' => 'Backpack connection is invalid. Please reconnect to your backpack.'
                ];
            }
            
            // Step 3: Get provider details
            $provider = $this->db->querySingle("
                SELECT * FROM backpack_provider WHERE id = ?d AND active = 1
            ", $providerId);
            
            if (!$provider) {
                error_log("Badge publication: Provider $providerId not found or inactive");
                return [
                    'success' => false,
                    'http_code' => 404,
                    'error_message' => 'Backpack provider not found.'
                ];
            }
            
            // Step 4: Generate assertion URL
            $baseUrl = rtrim(get_config('base_url'), '/');
            $assertionUrl = $baseUrl . '/modules/backpack/api/assertion.php?id=UB_' . $userBadgeId;
            
            error_log("Badge publication: Generated assertion URL: $assertionUrl");
            
            // Step 5: Call backpack provider API to import badge
            $importResult = $this->importAssertionToBackpack(
                $connection->access_token,
                $provider->api_url,
                $assertionUrl,
                $provider->ob_version
            );
            
            if (!$importResult['success']) {
                error_log("Badge publication: Failed to import to backpack - " . $importResult['error_message']);
                
                // If we get a 401, try to refresh token and retry once
                if ($importResult['http_code'] === 401) {
                    error_log("Badge publication: Got 401, attempting token refresh");
                    
                    $refreshResult = $this->apiService->refreshAccessToken($userId);
                    
                    if ($refreshResult->isSuccess()) {
                        error_log("Badge publication: Token refreshed, retrying import");
                        
                        // Get updated connection with new token
                        $connection = $this->db->querySingle("
                            SELECT access_token FROM user_backpack_connection 
                            WHERE user_id = ?d AND backpack_provider_id = ?d
                        ", $userId, $providerId);
                        
                        // Retry the import
                        $importResult = $this->importAssertionToBackpack(
                            $connection->access_token,
                            $provider->api_url,
                            $assertionUrl,
                            $provider->ob_version
                        );
                        
                        if (!$importResult['success']) {
                            error_log("Badge publication: Retry failed - " . $importResult['error_message']);
                            return [
                                'success' => false,
                                'http_code' => $importResult['http_code'] ?? 500,
                                'error_message' => 'Failed to publish badge to backpack after token refresh.',
                                'error_details' => $importResult['error_message'] ?? null
                            ];
                        }
                    } else {
                        error_log("Badge publication: Token refresh failed");
                        return [
                            'success' => false,
                            'http_code' => 401,
                            'error_message' => 'Your backpack session has expired. Please reconnect to your backpack.',
                            'error_details' => $refreshResult->getError()
                        ];
                    }
                } else {
                    return [
                        'success' => false,
                        'http_code' => $importResult['http_code'] ?? 500,
                        'error_message' => $importResult['error_message'] ?? 'Failed to publish badge to backpack.',
                        'error_details' => $importResult['error_details'] ?? null
                    ];
                }
            }
            
            // Step 6: Log successful publication (optional tracking)
            error_log("Badge publication: Successfully published badge $userBadgeId to provider $providerId");
            
            return [
                'success' => true,
                'assertion_id' => $userBadgeId,
                'external_id' => $importResult['external_id'] ?? null,
                'message' => 'Badge published successfully'
            ];
            
        } catch (Exception $e) {
            error_log("Badge publication exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return [
                'success' => false,
                'http_code' => 500,
                'error_message' => 'An error occurred while publishing the badge.',
                'error_details' => $e->getMessage()
            ];
        }
    }

    /**
     * Import assertion (badge) to backpack provider
     * 
     * @param string $accessToken OAuth access token
     * @param string $providerApiUrl Provider API base URL
     * @param string $assertionUrl URL to the assertion JSON
     * @param string $obVersion OpenBadges version
     * @return array Result array with success flag
     */
    private function importAssertionToBackpack(
        string $accessToken,
        string $providerApiUrl,
        string $assertionUrl,
        string $obVersion
    ): array {
        try {
            // Now that our assertion uses URL references (Moodle-style), use legacy import-by-URL
            $importEndpoint = rtrim($providerApiUrl, '/') . '/v2/backpack/import';

            error_log("Badge publication: Importing to $importEndpoint");

            // Send just the hosted assertion URL
            $requestData = [
                'url' => $assertionUrl
            ];
            
            // Create API client
            $client = new OpenBadgesApiClient();
            
            // Make the request
            $response = $client->authenticatedRequest(
                $importEndpoint,
                'POST',
                $accessToken,
                $requestData,
                $obVersion
            );
            
            if ($response->isSuccess()) {
                error_log("Badge publication: Import successful");
                
                return [
                    'success' => true,
                    'external_id' => $response->getData()['id'] ?? null
                ];
            } else {
                error_log("Badge publication: Import failed - HTTP " . $response->getHttpCode() . " error");
                error_log("Badge publication: Response error: " . $response->getError());
                error_log("Badge publication: Response data: " . json_encode($response->getData()));
                
                return [
                    'success' => false,
                    'http_code' => $response->getHttpCode(),
                    'error_message' => $response->getError(),
                    'error_details' => json_encode($response->getData())
                ];
            }
            
        } catch (Exception $e) {
            error_log("Badge publication: Import exception - " . $e->getMessage());
            
            return [
                'success' => false,
                'http_code' => 500,
                'error_message' => 'Error importing assertion to backpack',
                'error_details' => $e->getMessage()
            ];
        }
    }

    /**
     * Get the appropriate import endpoint for the provider
     * 
     * @param string $providerApiUrl Provider API base URL
     * @param string $obVersion OpenBadges version
     * @return string Full import endpoint URL
     */
    private function getImportEndpoint(string $providerApiUrl, string $obVersion): string
    {
        $baseUrl = rtrim($providerApiUrl, '/');
        
        // Common import endpoints for different providers
        if (strpos($baseUrl, 'badgr') !== false) {
            // Badgr uses /v2/backpack/import
            return $baseUrl . '/v2/backpack/import';
        }
        
        // Default OpenBadges 2.0/2.1 import endpoint
        return $baseUrl . '/backpack/import';
    }
}
