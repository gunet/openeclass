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
 * OpenBadges Badge Class API
 * 
 * Serves badge class definitions as per OpenBadges 2.0/2.1 specification.
 * Accessed by backpack providers to retrieve badge information.
 * 
 * URL Format: /modules/backpack/api/badge.php?id=123
 * 
 * Method: GET
 * Auth: Not required
 * Response: JSON Badge Class
 */

require_once __DIR__ . '/../../../include/baseTheme.php';

// Set JSON content type
header('Content-Type: application/json; charset=utf-8');

try {
    // Get badge ID from query parameters
    $badgeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($badgeId <= 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid badge ID',
            'message' => 'Badge ID must be a positive integer'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Query badge data from database
    $badgeData = Database::get()->querySingle("
        SELECT 
            b.id,
            b.title,
            b.description,
            b.issuer,
            b.created,
            b.expires,
            b.icon,
            bi.filename as icon_filename,
            c.title as course_title,
            c.id as course_id
        FROM badge b
        LEFT JOIN badge_icon bi ON b.icon = bi.id
        LEFT JOIN course c ON b.course_id = c.id
        WHERE b.id = ?d AND b.active = 1
    ", $badgeId);
    
    if (!$badgeData) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Badge not found',
            'message' => 'The requested badge does not exist or is not active'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get base URL for building URLs
    $baseUrl = rtrim(get_config('base_url'), '/');
    
    // Build badge class JSON according to OpenBadges 2.0/2.1 specification
    $badgeClass = [
        '@context' => 'https://w3id.org/openbadges/v2',
        'type' => 'BadgeClass',
        'id' => $baseUrl . '/modules/backpack/api/badge.php?id=' . $badgeData->id,
        'name' => $badgeData->title,
        'description' => $badgeData->description,
        // Point issuer to issuer endpoint with badge id (Moodle-style)
        'issuer' => $baseUrl . '/modules/backpack/api/issuer.php?id=' . $badgeData->id,
        'criteria' => [
            'narrative' => $badgeData->description
        ]
    ];
    
    // Add badge image (embed as data URI for maximum compatibility)
    $imageDataUri = null;
    if (!empty($badgeData->icon_filename)) {
        // Try to read from filesystem and embed
        $imagePath = __DIR__ . '/../../../courses/user_progress_data/badge_templates/' . $badgeData->icon_filename;
        if (!file_exists($imagePath)) {
            $imagePath = __DIR__ . '/../../../../courses/user_progress_data/badge_templates/' . $badgeData->icon_filename;
        }
        if (file_exists($imagePath)) {
            $raw = @file_get_contents($imagePath);
            if ($raw !== false) {
                $mime = @mime_content_type($imagePath) ?: 'image/png';
                $imageDataUri = 'data:' . $mime . ';base64,' . base64_encode($raw);
            }
        }
    }
    if (!$imageDataUri) {
        // Fallback to default logo (embed if possible)
        $fallbackPath = __DIR__ . '/../../../template/logo.png';
        if (!file_exists($fallbackPath)) {
            $fallbackPath = __DIR__ . '/../../../template/default_logo.png';
        }
        if (file_exists($fallbackPath)) {
            $raw = @file_get_contents($fallbackPath);
            if ($raw !== false) {
                $mime = @mime_content_type($fallbackPath) ?: 'image/png';
                $imageDataUri = 'data:' . $mime . ';base64,' . base64_encode($raw);
            }
        }
        // If all else fails, use absolute URL to logo
        if (!$imageDataUri) {
            $imageDataUri = $baseUrl . '/template/logo.png';
        }
    }
    $badgeClass['image'] = $imageDataUri;
    
    // Add course as tag if available
    if (!empty($badgeData->course_title)) {
        $badgeClass['tags'] = [$badgeData->course_title];
    }
    
    // Add alignment to course if available
    if ($badgeData->course_id) {
        $badgeClass['alignment'] = [
            [
                'targetName' => $badgeData->course_title,
                'targetUrl' => $baseUrl . '/modules/course/index.php?course=' . $badgeData->course_id
            ]
        ];
    }
    
    // Return badge class JSON
    http_response_code(200);
    echo json_encode($badgeClass, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Log the error
    error_log('Badge class API error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'Failed to retrieve badge class'
    ], JSON_UNESCAPED_UNICODE);
}

exit;
