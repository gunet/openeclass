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
 * Get Selected Collection API
 * 
 * Returns the user's last selected backpack collection for auto-selection
 */

$require_login = true;
require_once '../../../include/baseTheme.php';
require_once '../../main/services/BackpackConnectionService.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($uid) || $uid <= 0) {
        error_log("Get Selected Collection API: User not logged in (uid: " . ($uid ?? 'null') . ")");
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'errorcode' => 401,
            'errormessage' => 'Authentication required. User must be logged in.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $service = new BackpackConnectionService();
    $selectedCollection = $service->getSelectedCollection($uid);
    
    if ($selectedCollection) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $selectedCollection
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => null,
            'message' => 'No collection selected yet'
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    error_log("Get Selected Collection API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errormessage' => 'Failed to retrieve selected collection'
    ], JSON_UNESCAPED_UNICODE);
}


