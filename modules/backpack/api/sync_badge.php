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
 * Sync Badge API
 * 
 * This endpoint imports a badge from an external backpack into the user's OpenEClass portfolio.
 * It creates a record in the badge table and associates it with the user.
 * 
 * Method: POST
 * Auth: Required (session-based)
 * Parameters:
 *   - assertion_id: The ID of the badge assertion from the external backpack
 *   - collection_id: The collection ID where the badge resides
 *   - badge_data: The complete badge assertion data
 * Response: JSON with sync result
 */

$require_login = true;
require_once __DIR__ . '/../../../include/baseTheme.php';
require_once __DIR__ . '/../../main/services/BackpackConnectionService.php';

// Set JSON content type
header('Content-Type: application/json');

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = null;
    
    // Check for token in POST data
    if (isset($_POST['token'])) {
        $csrfToken = $_POST['token'];
    }
    // Check for token in custom header (for AJAX requests)
    elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'];
    }
    // Check in JSON body
    else {
        $rawInput = file_get_contents('php://input');
        if ($rawInput) {
            $jsonData = json_decode($rawInput, true);
            if (isset($jsonData['token'])) {
                $csrfToken = $jsonData['token'];
            }
        }
    }
    
    if (!$csrfToken || !validate_csrf_token($csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'errorcode' => 403,
            'errormessage' => 'CSRF token validation failed. Please refresh the page and try again.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

try {
    // Check if user is logged in
    if (!isset($uid) || $uid <= 0) {
        error_log("Sync Badge API: User not logged in (uid: " . ($uid ?? 'null') . ")");
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'errorcode' => 401,
            'errormessage' => 'Authentication required. User must be logged in.',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errorcode' => 400,
            'errormessage' => 'Invalid JSON data',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $assertionId = isset($data['assertion_id']) ? trim($data['assertion_id']) : '';
    $collectionId = isset($data['collection_id']) ? trim($data['collection_id']) : '';
    $collectionName = isset($data['collection_name']) ? trim($data['collection_name']) : '';
    $badgeData = isset($data['badge_data']) ? $data['badge_data'] : null;
    
    if (empty($assertionId) || !$badgeData) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errorcode' => 400,
            'errormessage' => 'Missing required parameters: assertion_id and badge_data are required',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    error_log("Sync Badge API: User ID: $uid, Assertion ID: $assertionId");
    
    // Extract badge information from the assertion data
    $badgeName = extractBadgeName($badgeData);
    $badgeDescription = extractBadgeDescription($badgeData);
    $badgeImage = extractBadgeImage($badgeData);
    $issuerName = extractIssuerName($badgeData);
    $issuedOn = extractIssuedOn($badgeData);
    
    error_log("Sync Badge API: Badge name: $badgeName, Issuer: $issuerName");
    
    // Check if badge already exists for this user in user_badge_external
    // Check if badge already exists in external badges
    $existingBadge = Database::get()->querySingle(
        "SELECT id FROM user_badge_external WHERE user_id = ?d AND external_assertion_id = ?s",
        $uid,
        $assertionId
    );
    
    if ($existingBadge) {
        error_log("Sync Badge API: Badge already exists in external badges (ID: {$existingBadge->id})");
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'action' => 'already_exists',
            'badge_id' => $existingBadge->id,
            'message' => 'Badge already exists in your portfolio'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Check if this badge originated from this platform (was published from here)
    // If so, don't re-import it as an external badge
    $originatedHere = Database::get()->querySingle(
        "SELECT id, badge FROM user_badge WHERE user = ?d AND external_assertion_id = ?s",
        $uid,
        $assertionId
    );
    
    if ($originatedHere) {
        error_log("Sync Badge API: Badge originated from this platform (user_badge ID: {$originatedHere->id}, badge ID: {$originatedHere->badge}). Skipping import to prevent duplication.");
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'action' => 'skipped_local_badge',
            'user_badge_id' => $originatedHere->id,
            'badge_id' => $originatedHere->badge,
            'message' => 'This badge originated from this platform and already exists in your local badges. Skipped to prevent duplication.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Create external badge table if it doesn't exist
    ensureExternalBadgeTableExists();
    
    // Get the backpack provider ID from user's active connection
    $providerConnection = Database::get()->querySingle(
        "SELECT backpack_provider_id FROM user_backpack_connection 
         WHERE user_id = ?d AND status = 'connected' 
         LIMIT 1",
        $uid
    );
    
    if (!$providerConnection || !$providerConnection->backpack_provider_id) {
        error_log("Sync Badge API: No active backpack provider connection found for user $uid");
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errorcode' => 400,
            'errormessage' => 'No active backpack provider connection found',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $providerId = $providerConnection->backpack_provider_id;
    error_log("Sync Badge API: Using provider ID: $providerId");
    
    // Insert the badge into the external badges table
    $badgeId = Database::get()->query(
        "INSERT INTO user_badge_external (
            user_id,
            backpack_provider_id,
            title, 
            description, 
            image_url, 
            issuer, 
            issued_on,
            external_assertion_id,
            external_collection_id,
            badge_data,
            created_at,
            updated_at
        ) VALUES (
            ?d, ?d, ?s, ?s, ?s, ?s, ?t, ?s, ?s, ?s, NOW(), NOW()
        )",
        $uid,
        $providerId,
        $badgeName,
        $badgeDescription,
        $badgeImage,
        $issuerName,
        $issuedOn,
        $assertionId,
        $collectionId,
        json_encode($badgeData)
    )->lastInsertID;
    
    if ($badgeId) {
        error_log("Sync Badge API: Badge synced successfully (ID: $badgeId)");
        
        // Update the selected collection in user_backpack_connection
        if (!empty($collectionId)) {
            try {
                $backpackService = new BackpackConnectionService();
                $backpackService->updateSelectedCollection($uid, $collectionId, $collectionName);
                error_log("Sync Badge API: Selected collection updated (ID: $collectionId)");
            } catch (Exception $e) {
                error_log("Sync Badge API: Failed to update selected collection: " . $e->getMessage());
                // Don't fail the sync if collection update fails
            }
        }
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'action' => 'created',
            'badge_id' => $badgeId,
            'message' => 'Badge synced successfully',
            'data' => [
                'badge_id' => $badgeId,
                'title' => $badgeName,
                'issuer' => $issuerName,
                'issued_on' => $issuedOn
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        error_log("Sync Badge API: Failed to insert badge");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'errorcode' => 500,
            'errormessage' => 'Failed to sync badge to database',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // Log the error
    error_log('Sync Badge API error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errorcode' => 500,
        'errormessage' => 'Internal server error while syncing badge',
        'error_details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;

/**
 * Ensure the external badge table exists
 */
function ensureExternalBadgeTableExists() {
    try {
        // Check if table exists
        $tableExists = Database::get()->querySingle(
            "SHOW TABLES LIKE 'user_badge_external'"
        );
        
        if (!$tableExists) {
            error_log("Sync Badge API: Creating user_badge_external table");
            
            // Create the table
            Database::get()->query("
                CREATE TABLE `user_badge_external` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT(11) NOT NULL,
                    `backpack_provider_id` INT(11) NOT NULL,
                    `title` VARCHAR(255) NOT NULL,
                    `description` TEXT,
                    `image_url` VARCHAR(512),
                    `issuer` VARCHAR(255),
                    `issued_on` DATETIME,
                    `external_assertion_id` VARCHAR(512) NOT NULL,
                    `external_collection_id` VARCHAR(512),
                    `badge_data` LONGTEXT,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `user_assertion` (`user_id`, `external_assertion_id`),
                    INDEX `user_id_idx` (`user_id`),
                    INDEX `backpack_provider_id_idx` (`backpack_provider_id`),
                    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`backpack_provider_id`) REFERENCES `backpack_provider`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            error_log("Sync Badge API: user_badge_external table created successfully");
        }
    } catch (Exception $e) {
        error_log("Sync Badge API: Error ensuring table exists: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Helper functions to extract badge data from different OpenBadges formats
 */

function extractBadgeName($badgeData) {
    // First try to parse originalJson if it exists (Badgr v2 format)
    if (isset($badgeData['originalJson'])) {
        $originalData = json_decode($badgeData['originalJson'], true);
        if ($originalData && isset($originalData['badge']['name'])) {
            return $originalData['badge']['name'];
        }
    }
    
    // Try different paths where the badge name might be
    if (isset($badgeData['badgeclass']['name'])) {
        return $badgeData['badgeclass']['name'];
    }
    if (isset($badgeData['badge']['name'])) {
        return $badgeData['badge']['name'];
    }
    if (isset($badgeData['name'])) {
        return $badgeData['name'];
    }
    if (isset($badgeData['badgeClass']['name'])) {
        return $badgeData['badgeClass']['name'];
    }
    return 'Untitled Badge';
}

function extractBadgeDescription($badgeData) {
    // First try to parse originalJson if it exists (Badgr v2 format)
    if (isset($badgeData['originalJson'])) {
        $originalData = json_decode($badgeData['originalJson'], true);
        if ($originalData && isset($originalData['badge']['description'])) {
            return $originalData['badge']['description'];
        }
    }
    
    // Try different paths where the description might be
    if (isset($badgeData['badgeclass']['description'])) {
        return $badgeData['badgeclass']['description'];
    }
    if (isset($badgeData['badge']['description'])) {
        return $badgeData['badge']['description'];
    }
    if (isset($badgeData['description'])) {
        return $badgeData['description'];
    }
    if (isset($badgeData['badgeClass']['description'])) {
        return $badgeData['badgeClass']['description'];
    }
    return '';
}

function extractBadgeImage($badgeData) {
    // First try to parse originalJson if it exists (Badgr v2 format)
    if (isset($badgeData['originalJson'])) {
        $originalData = json_decode($badgeData['originalJson'], true);
        if ($originalData && isset($originalData['badge']['image'])) {
            $image = $originalData['badge']['image'];
            // Handle data URI or URL
            if (is_string($image) && !str_starts_with($image, 'data:')) {
                return $image;
            }
        }
    }
    
    // Badgr provides a public image URL
    if (isset($badgeData['image'])) {
        return is_array($badgeData['image']) ? $badgeData['image']['id'] ?? '' : $badgeData['image'];
    }
    
    // Try different paths where the image URL might be
    if (isset($badgeData['badgeclass']['image'])) {
        return $badgeData['badgeclass']['image'];
    }
    if (isset($badgeData['badge']['image'])) {
        return $badgeData['badge']['image'];
    }
    if (isset($badgeData['badgeClass']['image'])) {
        return is_array($badgeData['badgeClass']['image']) ? $badgeData['badgeClass']['image']['id'] ?? '' : $badgeData['badgeClass']['image'];
    }
    return '';
}

function extractIssuerName($badgeData) {
    // First try to parse originalJson if it exists (Badgr v2 format)
    if (isset($badgeData['originalJson'])) {
        $originalData = json_decode($badgeData['originalJson'], true);
        if ($originalData && isset($originalData['badge']['issuer']['name'])) {
            return $originalData['badge']['issuer']['name'];
        }
    }
    
    // Try different paths where the issuer name might be
    if (isset($badgeData['badgeclass']['issuer']['name'])) {
        return $badgeData['badgeclass']['issuer']['name'];
    }
    if (isset($badgeData['badge']['issuer']['name'])) {
        return $badgeData['badge']['issuer']['name'];
    }
    if (isset($badgeData['issuer']['name'])) {
        return $badgeData['issuer']['name'];
    }
    if (isset($badgeData['badgeClass']['issuer']['name'])) {
        return $badgeData['badgeClass']['issuer']['name'];
    }
    return 'Unknown Issuer';
}

function extractIssuedOn($badgeData) {
    // Try different paths where the issue date might be
    if (isset($badgeData['issuedOn'])) {
        return date('Y-m-d H:i:s', strtotime($badgeData['issuedOn']));
    }
    if (isset($badgeData['issued_on'])) {
        return date('Y-m-d H:i:s', strtotime($badgeData['issued_on']));
    }
    if (isset($badgeData['createdAt'])) {
        return date('Y-m-d H:i:s', strtotime($badgeData['createdAt']));
    }
    if (isset($badgeData['created_at'])) {
        return date('Y-m-d H:i:s', strtotime($badgeData['created_at']));
    }
    return date('Y-m-d H:i:s'); // Default to current time
}

