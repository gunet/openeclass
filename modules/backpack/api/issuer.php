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
 * OpenBadges 2.0/2.1 Issuer Profile API
 * 
 * Serves the JSON issuer profile as per OpenBadges specification
 * Reference: https://github.com/mozilla/openbadges-specification/blob/master/Assertion/latest/index.html
 * 
 * This endpoint is accessed by backpack providers to verify badge issuers
 * 
 * Method: GET
 * Auth: Not required
 * Response: JSON Issuer Profile
 */

require_once __DIR__ . '/../../../include/baseTheme.php';

// Set JSON content type
header('Content-Type: application/json; charset=utf-8');

try {
    // Retrieve issuer configuration from database
    $siteName = get_config('site_name') ?? 'Open eClass';
    $institutionName = get_config('institution') ?? 'Institution';
    $institutionUrl = get_config('institution_url') ?? get_config('base_url');
    $adminEmail = get_config('email_sender') ?? 'admin@openeclass.org';
    $baseUrl = rtrim(get_config('base_url'), '/');
    
    // Optional badge id for per-badge issuer URL (Moodle-style)
    $badgeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $selfUrl = $baseUrl . '/modules/backpack/api/issuer.php' . ($badgeId > 0 ? ('?id=' . $badgeId) : '');
    
    // Retrieve logo if available (use filesystem path for existence check)
    $logoPath = __DIR__ . '/../../../template/logo.png';
    $defaultLogoPath = __DIR__ . '/../../../template/default_logo.png';
    $logoUrl = $baseUrl . '/template/logo.png';
    if (!file_exists($logoPath)) {
        $logoUrl = $baseUrl . '/template/default_logo.png';
    }
    
    // Build issuer profile according to OpenBadges 2.0/2.1 specification
    $issuerProfile = [
        '@context' => 'https://w3id.org/openbadges/v2',
        'type' => 'Issuer',
        'id' => $selfUrl,
        'name' => $institutionName . ' - ' . $siteName,
        'url' => $institutionUrl,
        'email' => $adminEmail,
        'image' => $logoUrl,
        'description' => 'OpenBadges Issuer Profile for ' . $institutionName
    ];
    
    http_response_code(200);
    echo json_encode($issuerProfile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log('Issuer profile API error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to retrieve issuer profile',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
