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
 * Backpack Collections API
 * 
 * This endpoint retrieves badge collections from the user's connected backpack provider.
 * It requires the user to be authenticated and have an active backpack connection.
 * 
 * Method: GET
 * Auth: Required (session-based)
 * Response: JSON array of collections
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
        error_log("Collections API: User not logged in (uid: " . ($uid ?? 'null') . ")");
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'errorcode' => 401,
            'errormessage' => 'Authentication required. User must be logged in.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    error_log("Collections API: User ID: $uid - Starting request");
    
    // Initialize the API service
    $apiService = new OpenBadgesApiService();
    
    // Fetch user collections from backpack provider
    $response = $apiService->getUserCollections($uid);
    
    error_log("Collections API: Response received - Success: " . ($response->isSuccess() ? 'yes' : 'no'));
    
    // If we get a 401 error, try to refresh the token and retry
    if (!$response->isSuccess() && $response->getHttpCode() === 401) {
        error_log("Collections API: Got 401, attempting token refresh");
        
        $refreshResponse = $apiService->refreshAccessToken($uid);
        if ($refreshResponse->isSuccess()) {
            error_log("Collections API: Token refreshed successfully, retrying collections request");
            // Retry the request with new token
            $response = $apiService->getUserCollections($uid);
            error_log("Collections API: Retry result - Success: " . ($response->isSuccess() ? 'yes' : 'no'));
        } else {
            $errorDetails = json_encode($refreshResponse->getData());
            error_log("Collections API: Token refresh failed: " . $refreshResponse->getError() . " - Details: " . $errorDetails);
            
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
        $collections = $response->getCollections();
        
        error_log("Collections API: Found " . count($collections) . " collections");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $collections,
            'count' => count($collections),
            'message' => 'Collections retrieved successfully'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // Handle API error
        $errorMessage = $response->getError();
        $httpCode = determineHttpCodeFromError($errorMessage);
        
        error_log("Collections API: Error - HTTP $httpCode - $errorMessage");
        error_log("Collections API: Full response data: " . json_encode($response->getData()));
        
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'errorcode' => $httpCode,
            'errormessage' => $errorMessage,
            'data' => $response->getData(),
            'debug' => [
                'http_code' => $response->getHttpCode(),
                'timestamp' => $response->timestamp
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    // Log the error
    error_log('Backpack collections API error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errorcode' => 500,
        'errormessage' => 'Internal server error while fetching collections',
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

