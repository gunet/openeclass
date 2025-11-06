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

// Check if user is administrator
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/ai/AIProviderFactory.php';

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $provider_type = $_POST['provider_type'] ?? '';
    $api_key = trim($_POST['api_key']) ?? '';
    $model_name = $_POST['model_name'] ?? '';
    $endpoint_url = $_POST['endpoint_url'] ?? null;

    if (empty($provider_type) || empty($api_key)) {
        echo json_encode([
            'success' => false,
            'message' => 'Provider type and API key are required'
        ]);
        exit;
    }

    // Handle "other" provider type
    if ($provider_type === 'other') {
        $provider_type = 'custom';
    }

    // Create provider configuration
    $config = [
        'api_key' => $api_key,
        'model_name' => $model_name,
        'endpoint_url' => $endpoint_url,
        'enabled' => true
    ];

    // Create provider instance
    $provider = AIProviderFactory::create($provider_type, $config);

    if (!$provider) {
        echo json_encode([
            'success' => false,
            'message' => 'Unsupported provider type: ' . $provider_type
        ]);
        exit;
    }

    // Test the connection
    $isHealthy = $provider->isHealthy();

    if ($isHealthy) {
        echo json_encode([
            'success' => true,
            'message' => 'Connection successful to ' . $provider->getDisplayName()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Connection failed - please check your API key and settings'
        ]);
    }

} catch (Exception $e) {
    error_log("AI connection test error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Connection test failed: ' . $e->getMessage()
    ]);
}
