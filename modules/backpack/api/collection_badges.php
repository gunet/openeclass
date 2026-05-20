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
 * Collection Badges API
 * 
 * This endpoint retrieves badges from a specific collection in the user's backpack.
 * It requires the user to be authenticated and have an active backpack connection.
 * 
 * Method: GET
 * Auth: Required (session-based)
 * Parameters: collection_id (required)
 * Response: JSON array of badges
 */

$require_login = true;
require_once __DIR__ . '/../../../include/baseTheme.php';
require_once __DIR__ . '/../../main/services/OpenBadgesApiService.php';
require_once __DIR__ . '/../../main/services/OpenBadgesApiClient.php';
require_once __DIR__ . '/../../main/services/OpenBadgesApiResponse.php';
require_once __DIR__ . '/../../main/services/OpenBadgesEndpointRegistry.php';
require_once __DIR__ . '/../../admin/entities/BackpackProvider.php';

// Set JSON content type
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($uid) || $uid <= 0) {
        error_log("Collection Badges API: User not logged in (uid: " . ($uid ?? 'null') . ")");
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'errorcode' => 401,
            'errormessage' => 'Authentication required. User must be logged in.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get collection_id from query parameters
    $collectionId = isset($_GET['collection_id']) ? trim($_GET['collection_id']) : '';
    
    if (empty($collectionId)) {
        error_log("Collection Badges API: Missing collection_id parameter");
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errorcode' => 400,
            'errormessage' => 'Missing required parameter: collection_id',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    error_log("Collection Badges API: User ID: $uid, Collection ID: $collectionId - Starting request");
    
    // Initialize the API service
    $apiService = new OpenBadgesApiService();
    
    // Fetch badges from the specified collection
    $response = $apiService->getCollectionBadges($uid, $collectionId);
    
    error_log("Collection Badges API: Response received - Success: " . ($response->isSuccess() ? 'yes' : 'no'));
    
    // If we get a 401 error, try to refresh the token and retry
    if (!$response->isSuccess() && $response->getHttpCode() === 401) {
        error_log("Collection Badges API: Got 401, attempting token refresh");
        
        $refreshResponse = $apiService->refreshAccessToken($uid);
        if ($refreshResponse->isSuccess()) {
            error_log("Collection Badges API: Token refreshed successfully, retrying request");
            // Retry the request with new token
            $response = $apiService->getCollectionBadges($uid, $collectionId);
            error_log("Collection Badges API: Retry result - Success: " . ($response->isSuccess() ? 'yes' : 'no'));
        } else {
            $errorDetails = json_encode($refreshResponse->getData());
            error_log("Collection Badges API: Token refresh failed: " . $refreshResponse->getError() . " - Details: " . $errorDetails);
            
            // Check if it's an invalid_grant error (refresh token expired)
            $isInvalidGrant = $refreshResponse->getData() && isset($refreshResponse->getData()['error']) && 
                              $refreshResponse->getData()['error'] === 'invalid_grant';
            
            $errorMessage = $isInvalidGrant 
                ? 'Your backpack session has completely expired. Please disconnect and reconnect your backpack to continue.'
                : 'Your backpack connection has expired and could not be refreshed. Please disconnect and reconnect your backpack to continue.';
            
            // Return a user-friendly error message
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'errorcode' => 401,
                'errormessage' => $errorMessage,
                'data' => null,
                'action_required' => 'reconnect',
                'error_type' => $isInvalidGrant ? 'refresh_token_expired' : 'auth_failed'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // Check if the request was successful
    if ($response->isSuccess()) {
        $badges = $response->getData();
        
        // DEBUG: Log the raw response structure
        error_log("Collection Badges API: Raw response structure: " . json_encode($badges));
        
        // Handle different response structures from different providers
        $badgesList = [];
        if (is_array($badges)) {
            // Check if it's a direct array of badges
            if (isset($badges[0])) {
                $badgesList = $badges;
                error_log("Collection Badges API: Using direct array structure");
            }
            // Check if badges are nested under a 'result' key (Badgr structure)
            elseif (isset($badges['result']) && is_array($badges['result'])) {
                // Badgr returns the collection object in result[0]
                // The actual badges/assertions are in result[0]['assertions']
                if (isset($badges['result'][0]) && is_array($badges['result'][0])) {
                    $collection = $badges['result'][0];
                    
                    // Check if assertions exist and are in array format
                    if (isset($collection['assertions']) && is_array($collection['assertions'])) {
                        $assertions = $collection['assertions'];
                        
                        // Check if assertions array is empty
                        if (empty($assertions)) {
                            $badgesList = [];
                            error_log("Collection Badges API: Collection has no badges (empty assertions array)");
                        }
                        // Check if assertions are strings (IDs) or objects (full data)
                        elseif (is_string($assertions[0])) {
                            // Assertions are IDs - need to fetch full badge details for each
                            error_log("Collection Badges API: Assertions are IDs, fetching full details for " . count($assertions) . " badges");
                            
                            $badgesList = [];
                            $apiService = new OpenBadgesApiService();
                            
                            foreach ($assertions as $assertionId) {
                                try {
                                    $assertionResponse = $apiService->getAssertion($uid, $assertionId);
                                    
                                    if ($assertionResponse->isSuccess()) {
                                        $assertionData = $assertionResponse->getData();
                                        
                                        // Unwrap the result array if present
                                        if (is_array($assertionData) && isset($assertionData['result']) && is_array($assertionData['result'])) {
                                            // Badge is in result[0]
                                            if (isset($assertionData['result'][0])) {
                                                $badgesList[] = $assertionData['result'][0];
                                            }
                                        } else {
                                            // Already unwrapped
                                            $badgesList[] = $assertionData;
                                        }
                                    } else {
                                        error_log("Collection Badges API: Failed to fetch assertion $assertionId: " . $assertionResponse->getError());
                                    }
                                } catch (Exception $e) {
                                    error_log("Collection Badges API: Exception fetching assertion $assertionId: " . $e->getMessage());
                                }
                            }
                            
                            error_log("Collection Badges API: Successfully fetched " . count($badgesList) . " out of " . count($assertions) . " badges");
                            
                            // DEBUG: Log the structure of the first badge
                            if (!empty($badgesList)) {
                                error_log("Collection Badges API: First badge structure: " . json_encode($badgesList[0]));
                            }
                        } else {
                            // Assertions are already full objects
                            $badgesList = $assertions;
                            error_log("Collection Badges API: Using full badge objects from assertions");
                        }
                    } else {
                        // No assertions field at all - use empty array
                        $badgesList = [];
                        error_log("Collection Badges API: No assertions field found in collection");
                    }
                } else {
                $badgesList = $badges['result'];
                    error_log("Collection Badges API: Using 'result' key structure (legacy)");
                }
            }
            // Check if badges are nested under 'data' key
            elseif (isset($badges['data']) && is_array($badges['data'])) {
                $badgesList = $badges['data'];
                error_log("Collection Badges API: Using 'data' key structure");
            }
            else {
                error_log("Collection Badges API: Unknown structure - Keys: " . implode(', ', array_keys($badges)));
            }
        }
        
        error_log("Collection Badges API: Found " . count($badgesList) . " badges/assertions in collection");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $badgesList,
            'count' => count($badgesList),
            'collection_id' => $collectionId,
            'message' => 'Badges retrieved successfully'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Handle API error
        $errorMessage = $response->getError();
        $httpCode = determineHttpCodeFromError($errorMessage);
        
        error_log("Collection Badges API: Error - HTTP $httpCode - $errorMessage");
        error_log("Collection Badges API: Full response data: " . json_encode($response->getData()));
        
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'errorcode' => $httpCode,
            'errormessage' => $errorMessage,
            'data' => $response->getData(),
            'debug' => [
                'http_code' => $response->getHttpCode(),
                'timestamp' => $response->timestamp ?? time()
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    // Log the error
    error_log('Collection Badges API error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errorcode' => 500,
        'errormessage' => 'Internal server error while fetching badges',
        'error_details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;

/**
 * Determine appropriate HTTP status code based on error message
 */
function determineHttpCodeFromError(string $errorMessage): int {
    $errorMessage = strtolower($errorMessage);
    
    if (strpos($errorMessage, 'no backpack connection') !== false) {
        return 404; // Not Found - no connection exists
    }
    
    if (strpos($errorMessage, 'provider not found') !== false) {
        return 404; // Not Found
    }
    
    if (strpos($errorMessage, 'unauthorized') !== false || 
        strpos($errorMessage, 'authentication') !== false ||
        strpos($errorMessage, 'access denied') !== false) {
        return 401; // Unauthorized
    }
    
    if (strpos($errorMessage, 'token expired') !== false) {
        return 401; // Unauthorized - token expired
    }
    
    if (strpos($errorMessage, 'forbidden') !== false) {
        return 403; // Forbidden
    }
    
    if (strpos($errorMessage, 'not found') !== false) {
        return 404; // Not Found
    }
    
    if (strpos($errorMessage, 'timeout') !== false) {
        return 504; // Gateway Timeout
    }
    
    // Default to 500 for unknown errors
    return 500;
}
