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
 * OpenBadges Badge Assertion API
 * 
 * Serves badge assertions (issued badges) as per OpenBadges 2.0/2.1 specification.
 * Accessed by backpack providers to retrieve specific badge awards to users.
 * 
 * URL Format: /modules/backpack/api/assertion.php?id=UB_456
 * 
 * Method: GET
 * Auth: Not required
 * Response: JSON Assertion
 */

require_once __DIR__ . '/../../../include/baseTheme.php';

// Set JSON content type
header('Content-Type: application/json; charset=utf-8');

try {
    // Get assertion ID from query parameters (format: UB_456)
    $assertionParam = isset($_GET['id']) ? $_GET['id'] : '';
    $userBadgeId = 0;
    
    // Parse assertion ID to extract user_badge ID
    if (preg_match('/^UB_(\d+)$/', $assertionParam, $matches)) {
        $userBadgeId = intval($matches[1]);
    }
    
    if ($userBadgeId <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid assertion ID',
            'message' => 'Assertion ID must be in format UB_[number]'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Query assertion data from database
    $assertionData = Database::get()->querySingle("
        SELECT 
            ub.id as user_badge_id,
            ub.user as user_id,
            ub.badge as badge_id,
            ub.assigned as issued_date,
            ub.completed,
            u.email as user_email,
            b.title as badge_title,
            b.description as badge_description,
            b.expires as badge_expires,
            b.issuer as badge_issuer
        FROM user_badge ub
        JOIN user u ON ub.user = u.id
        JOIN badge b ON ub.badge = b.id
        WHERE ub.id = ?d AND ub.completed = 1 AND b.active = 1
    ", $userBadgeId);
    
    if (!$assertionData) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Assertion not found',
            'message' => 'The requested assertion does not exist or badge is not completed'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get base URL for building URLs
    $baseUrl = rtrim(get_config('base_url'), '/');
    
    // Email hashing for Badgr verification
    // Format: sha256$hash where hash is SHA256(email)
    // Include a salt like Moodle does for better verification
    $emailSalt = 'openeclass_' . $assertionData->user_badge_id;
    $hashedEmail = 'sha256$' . hash('sha256', strtolower(trim($assertionData->user_email)) . $emailSalt);
    
    // Fetch badge class data to embed it
    $badgeClassData = Database::get()->querySingle("
        SELECT
            b.id,
            b.title,
            b.description,
            b.icon,
            bi.filename as icon_filename,
            c.title as course_title,
            c.id as course_id
        FROM badge b
        LEFT JOIN badge_icon bi ON b.icon = bi.id
        LEFT JOIN course c ON b.course_id = c.id
        WHERE b.id = ?d AND b.active = 1
    ", $assertionData->badge_id);
    
    // Fetch issuer profile data
    $siteName = get_config('site_name') ?? 'Open eClass';
    $institutionName = get_config('institution') ?? 'Institution';
    $institutionUrl = get_config('institution_url') ?? get_config('base_url');
    $adminEmail = get_config('email_sender') ?? '';
    
    // Build issuer reference URL (Moodle-style, per badge id)
    $issuerUrl = $baseUrl . '/modules/backpack/api/issuer.php?id=' . $assertionData->badge_id;
    
    // Convert badge image to base64 data URI (like Moodle does)
    $badgeImageUrl = '';
    if (!empty($badgeClassData->icon_filename)) {
        $imagePath = __DIR__ . '/../../../courses/user_progress_data/badge_templates/' . $badgeClassData->icon_filename;
        // Try alternative path if first doesn't exist
        if (!file_exists($imagePath)) {
            $imagePath = __DIR__ . '/../../../../courses/user_progress_data/badge_templates/' . $badgeClassData->icon_filename;
        }
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath) ?: 'image/png';
            $badgeImageUrl = "data:$mimeType;base64,$imageData";
        } else {
            $badgeImageUrl = $baseUrl . '/courses/user_progress_data/badge_templates/' . $badgeClassData->icon_filename;
        }
    } else {
        $badgeImageUrl = $baseUrl . '/template/logo.png';
    }
    
    // BadgeClass will be referenced by URL; keep only URL building
    $badgeUrl = $baseUrl . '/modules/backpack/api/badge.php?id=' . $assertionData->badge_id;
    
    // Add tags and alignment if course exists
    if (!empty($badgeClassData->course_title)) {
        $badgeClass['tags'] = [$badgeClassData->course_title];
    }
    if ($badgeClassData->course_id) {
        $badgeClass['alignment'] = [[
            'targetName' => $badgeClassData->course_title,
            'targetUrl' => $baseUrl . '/modules/course/index.php?course=' . $badgeClassData->course_id
        ]];
    }
    
    // Build the assertion using URL references (Moodle-style)
    $assertion = [
        '@context' => 'https://w3id.org/openbadges/v2',
        'type' => 'Assertion',
        'id' => $baseUrl . '/modules/backpack/api/assertion.php?id=UB_' . $assertionData->user_badge_id,
        'recipient' => [
            'type' => 'email',
            'identity' => $hashedEmail,
            'hashed' => true,
            'salt' => $emailSalt
        ],
        'badge' => $badgeUrl,
        'issuedOn' => date('c', strtotime($assertionData->issued_date)),
        'evidence' => $baseUrl . '/main/profile/display_profile.php',
        'verify' => [
            'type' => 'hosted',
            'url' => $baseUrl . '/modules/backpack/api/assertion.php?id=UB_' . $assertionData->user_badge_id
        ]
    ];
    
    // Add expiration date if badge has one
    if (!empty($assertionData->badge_expires)) {
        $assertion['expires'] = date('c', strtotime($assertionData->badge_expires));
    }
    
    // Return assertion JSON
    http_response_code(200);
    echo json_encode($assertion, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Log the error
    error_log('Assertion API error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'Failed to retrieve assertion'
    ], JSON_UNESCAPED_UNICODE);
}

exit;
