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
 * Badge Publication API
 * 
 * Publishes a user's local badge to an external backpack provider.
 * Handles validation, assertion generation, and communication with backpack APIs.
 * 
 * Method: POST
 * Auth: Required (session-based)
 * Parameters: JSON body with user_badge_id and provider_id
 * Response: JSON status
 */

$require_login = true;
require_once __DIR__ . '/../../../include/baseTheme.php';
require_once __DIR__ . '/../../main/services/OpenBadgesApiService.php';
require_once __DIR__ . '/../../main/services/OpenBadgesApiClient.php';
require_once __DIR__ . '/../../main/services/OpenBadgesApiResponse.php';
require_once __DIR__ . '/../../main/services/OpenBadgesEndpointRegistry.php';
require_once __DIR__ . '/../../admin/entities/BackpackProvider.php';
require_once __DIR__ . '/../services/BadgePublicationService.php';

// Set JSON content type
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($uid) || $uid <= 0) {
        error_log("Badge publish API: User not logged in (uid: " . ($uid ?? 'null') . ")");
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'errorcode' => 401,
            'errormessage' => 'Authentication required. User must be logged in.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    error_log("Badge publish API: User ID: $uid - Starting request");
    
    // Accept both JSON body (POST) and query/form params (GET/POST) for ease of integration
    $inputData = null;
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $inputData = $decoded;
        }
    }
    // Fallback to standard form/query params
    if (!is_array($inputData)) {
        $inputData = [
            'user_badge_id' => isset($_REQUEST['user_badge_id']) ? $_REQUEST['user_badge_id'] : null,
            'provider_id' => isset($_REQUEST['provider_id']) ? $_REQUEST['provider_id'] : null,
        ];
    }
    
    // Extract parameters
    $userBadgeId = isset($inputData['user_badge_id']) ? intval($inputData['user_badge_id']) : 0;
    $providerId = isset($inputData['provider_id']) ? intval($inputData['provider_id']) : 0;
    
    if ($userBadgeId <= 0 || $providerId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errorcode' => 400,
            'errormessage' => 'Invalid parameters. user_badge_id and provider_id are required and must be positive integers.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    error_log("Badge publish API: Publishing badge $userBadgeId to provider $providerId for user $uid");
    
    // Initialize badge publication service
    $publicationService = new BadgePublicationService();
    
    // Publish badge to backpack
    $result = $publicationService->publishBadgeToBackpack($uid, $userBadgeId, $providerId);
    
    if ($result['success']) {
        error_log("Badge publish API: Successfully published badge $userBadgeId to provider $providerId");
        
        // Store the external assertion ID in user_badge to track published badges
        if (!empty($result['assertion_id'])) {
            try {
                Database::get()->query(
                    "UPDATE user_badge SET external_assertion_id = ?s WHERE id = ?d",
                    $result['assertion_id'],
                    $userBadgeId
                );
                error_log("Badge publish API: Stored external assertion ID for badge $userBadgeId");
            } catch (Exception $e) {
                error_log("Badge publish API: Failed to store external assertion ID: " . $e->getMessage());
                // Don't fail the request - the badge was still published successfully
            }
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Badge published successfully to backpack',
            'assertion_id' => $result['assertion_id'] ?? null,
            'external_id' => $result['external_id'] ?? null
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $httpCode = $result['http_code'] ?? 400;
        $errorMessage = $result['error_message'] ?? 'Failed to publish badge';
        
        error_log("Badge publish API: Failed to publish badge - HTTP $httpCode - $errorMessage");
        
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'errorcode' => $httpCode,
            'errormessage' => $errorMessage,
            'error_details' => $result['error_details'] ?? null
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // Log the error
    error_log('Badge publish API error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errorcode' => 500,
        'errormessage' => 'Internal server error while publishing badge',
        'error_details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
