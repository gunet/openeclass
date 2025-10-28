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
 * Connected Providers API
 * 
 * Returns list of backpack providers that the user has connected to
 * 
 * Method: GET
 * Auth: Required (session-based)
 * Response: JSON array of providers
 */

$require_login = true;
require_once __DIR__ . '/../../../include/baseTheme.php';

// Set JSON content type
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($uid) || $uid <= 0) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'errorcode' => 401,
            'errormessage' => 'Authentication required'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Query connected providers for the user
    $providers = Database::get()->queryArray("
        SELECT 
            bp.id,
            bp.name,
            bp.api_url,
            bp.ob_version,
            ubc.status
        FROM backpack_provider bp
        JOIN user_backpack_connection ubc ON bp.id = ubc.backpack_provider_id
        WHERE ubc.user_id = ?d 
          AND ubc.status = 'connected'
          AND bp.active = 1
        ORDER BY bp.name ASC
    ", $uid);

    $providerList = [];
    if ($providers) {
        foreach ($providers as $provider) {
            $providerList[] = [
                'id' => $provider->id,
                'name' => $provider->name,
                'api_url' => $provider->api_url,
                'ob_version' => $provider->ob_version
            ];
        }
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'providers' => $providerList,
        'count' => count($providerList)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('Get providers API error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errorcode' => 500,
        'errormessage' => 'Failed to retrieve providers'
    ], JSON_UNESCAPED_UNICODE);
}

exit;
