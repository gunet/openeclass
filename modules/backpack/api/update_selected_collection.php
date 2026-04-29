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
 * API endpoint to update user's selected collection
 * 
 * This endpoint saves the user's selected collection to the database
 * and updates the last_sync timestamp.
 */

$require_login = true;
require_once '../../../include/baseTheme.php';
require_once '../../main/services/BackpackConnectionService.php';

// Set JSON response header
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Check if user is logged in
    if (!isset($uid) || $uid <= 0) {
        error_log("Update Selected Collection API: User not logged in (uid: " . ($uid ?? 'null') . ")");
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'errorcode' => 401,
            'errormessage' => 'Authentication required. User must be logged in.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON input'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validate required fields
    $collectionId = $input['collection_id'] ?? '';
    $collectionName = $input['collection_name'] ?? '';
    
    if (empty($collectionId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required field: collection_id'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (empty($collectionName)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required field: collection_name'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Update selected collection
    $connectionService = new BackpackConnectionService();
    $success = $connectionService->updateSelectedCollection(
        $uid,
        $collectionId,
        $collectionName
    );
    
    if ($success) {
        error_log("Update Selected Collection API: Successfully updated for user $uid - Collection: $collectionName ($collectionId)");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Selected collection updated successfully',
            'data' => [
                'collection_id' => $collectionId,
                'collection_name' => $collectionName,
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        error_log("Update Selected Collection API: Failed to update for user $uid");
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update selected collection'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    error_log("Update Selected Collection API: Exception - " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

