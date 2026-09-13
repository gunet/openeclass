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
 * Get Assertion API
 * 
 * Fetches full details for a specific badge assertion/badge ID
 * 
 * Method: GET
 * Auth: Required (session-based)
 * Parameters: assertion_id (required)
 * Response: JSON with full badge/assertion data
 */

$require_login = true;
require_once __DIR__ . '/../../../include/baseTheme.php';
require_once __DIR__ . '/../../main/services/OpenBadgesApiService.php';

header('Content-Type: application/json');

try {
    if (!isset($uid) || $uid <= 0) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'errorcode' => 401,
            'errormessage' => 'Authentication required',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $assertionId = isset($_GET['assertion_id']) ? trim($_GET['assertion_id']) : '';
    if ($assertionId === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errorcode' => 400,
            'errormessage' => 'Missing required parameter: assertion_id',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    error_log("Get Assertion API: User ID: $uid, Assertion ID: $assertionId");

    $api = new OpenBadgesApiService();
    $response = $api->getUserBadge($uid, $assertionId);

    // If unauthorized, attempt token refresh and retry
    if (!$response->isSuccess() && (int) $response->getHttpCode() === 401) {
        error_log("Get Assertion API: Got 401, attempting token refresh");
        $refresh = $api->refreshAccessToken($uid);
        if ($refresh->isSuccess()) {
            $response = $api->getUserBadge($uid, $assertionId);
        }
    }

    if ($response->isSuccess()) {
        $data = $response->getData();
        
        error_log("Get Assertion API: Successfully fetched assertion");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $http = $response->getHttpCode() ?: 500;
    http_response_code($http);
    echo json_encode([
        'success' => false,
        'errorcode' => $http,
        'errormessage' => $response->getError() ?: 'Failed to fetch assertion',
        'data' => $response->getData(),
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log('Get Assertion API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errorcode' => 500,
        'errormessage' => 'Internal server error while fetching assertion',
        'error_details' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}

exit;


