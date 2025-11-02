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

$require_login = true;
$require_help = true;
$helpTopic = 'Backpacks';

require_once '../include/baseTheme.php';
require_once  dirname(__DIR__) .  '/modules/main/services/BackpackConnectionService.php';
require_once  dirname(__DIR__) .  '/modules/admin/repositories/BackpackProviderRepository.php';

// Handle AJAX test connection request
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'test_connection') {
    header('Content-Type: application/json');
    
    $provider_id = intval($_POST['provider_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!$provider_id || !$email || !$password) {
        echo json_encode([
            'success' => false,
            'error' => 'Missing required parameters',
            'status' => 400
        ]);
        exit;
    }
    
    // Get provider details
    $providerRepo = new BackpackProviderRepository();
    $provider = $providerRepo->findById($provider_id);
    
    if (!$provider || !$provider->isEnabled()) {
        echo json_encode([
            'success' => false,
            'error' => 'Provider not found or disabled',
            'status' => 404
        ]);
        exit;
    }
    
    // Test the connection and save if successful
    $backpackConnectionService = new BackpackConnectionService();
    $result = testBackpackConnection($provider, $email, $password, $uid, $backpackConnectionService);
    echo json_encode($result);
    exit;
}

$toolName = $langPortfolio;
$pageName = $langMyBackpacks ?? 'My Backpacks';

// Initialize services
$backpackProviderRepo = new BackpackProviderRepository();
$backpackConnectionService = new BackpackConnectionService();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'connect':
                handleBackpackConnection($backpackProviderRepo, $backpackConnectionService);
                break;
            case 'disconnect':
                handleBackpackDisconnection($backpackConnectionService);
                break;
        }
    }
}

// Get user's current backpack connection
$userConnection = $backpackConnectionService->getUserConnection($uid);

// Get available backpack providers
$availableProviders = $backpackProviderRepo->findActive();

// Prepare data for view
$viewData = [
    'userConnection' => $userConnection,
    'availableProviders' => $availableProviders,
    'uid' => $uid,
    'urlAppend' => $urlAppend
];

view('main.mybackpacks', $viewData);

/**
 * Handle backpack connection
 */
function handleBackpackConnection(
    BackpackProviderRepository $providerRepo, 
    BackpackConnectionService $connectionService
): void {
    global $uid;
    
    $providerId = intval($_POST['provider_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$providerId) {
        Session::flash('message', trans('langBackpackProviderRequired'));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('main/mybackpacks.php');
        return;
    }
    
    $provider = $providerRepo->findById($providerId);
    if (!$provider || !$provider->isEnabled()) {
        Session::flash('message', trans('langBackpackProviderNotFound'));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('main/mybackpacks.php');
        return;
    }
    
    // Validate connection data
    $errors = $connectionService->validateConnectionData($provider, $email, $password);
    if (!empty($errors)) {
        Session::flash('message', implode('<br>', $errors));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page('main/mybackpacks.php');
        return;
    }
    
    // Attempt connection
    $success = $connectionService->connectBackpack($uid, $providerId, $email, $password);
    
    if ($success) {
        Session::flash('message', trans('langBackpackConnectedSuccessfully'));
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message', trans('langBackpackConnectionFailed'));
        Session::flash('alert-class', 'alert-danger');
    }
    
    redirect_to_home_page('main/mybackpacks.php');
}

/**
 * Handle backpack disconnection
 */
function handleBackpackDisconnection(BackpackConnectionService $connectionService): void
{
    global $uid;
    
    $success = $connectionService->disconnectBackpack($uid);
    
    if ($success) {
        Session::flash('message', trans('langBackpackDisconnectedSuccessfully'));
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message', trans('langBackpackDisconnectionFailed'));
        Session::flash('alert-class', 'alert-danger');
    }
    
    redirect_to_home_page('main/mybackpacks.php');
}

/**
 * Test backpack connection via server-side request
 */
function testBackpackConnection($provider, $email, $password, $userId, $backpackConnectionService) {
    // $tokenUrl = rtrim($provider->api_url, '/') . '/o/token';
    $tokenUrl = 'https://api.eu.badgr.io/o/token';
    error_log('Token URL: ' . $tokenUrl);
    // Prepare POST data - Badgr API expects 'username' and 'password' parameters
    $postData = http_build_query([
        'username' => $email,
        'password' => $password
    ]);
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $tokenUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: OpenEClass-BackpackConnector/1.0'
        ]
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Handle cURL errors
    if ($error) {
        return [
            'success' => false,
            'error' => 'Connection error: ' . $error,
            'status' => 0,
            'response' => null
        ];
    }
    
    // Try to decode JSON response
    $decodedResponse = json_decode($response, true);
    
    // Check if request was successful
    if ($httpCode < 200 || $httpCode >= 300) {
    return [
            'success' => false,
        'status' => $httpCode,
            'error' => 'Authentication failed',
        'response' => $decodedResponse ?: $response,
        'raw_response' => $response
    ];
    }
    
    // Validate OAuth response structure
    if (!$decodedResponse || !is_array($decodedResponse)) {
        return [
            'success' => false,
            'error' => 'Invalid response format',
            'status' => $httpCode,
            'response' => $response
        ];
    }
    
    // Check for required OAuth fields
    $requiredFields = ['access_token', 'token_type', 'expires_in'];
    foreach ($requiredFields as $field) {
        if (!isset($decodedResponse[$field]) || empty($decodedResponse[$field])) {
            return [
                'success' => false,
                'error' => "Invalid OAuth response: missing or empty '$field' field",
                'status' => $httpCode,
                'response' => $decodedResponse
            ];
        }
    }
    
    // Validate token_type is Bearer
    if (strtolower($decodedResponse['token_type']) !== 'bearer') {
        return [
            'success' => false,
            'error' => 'Invalid token type. Expected "Bearer", got "' . $decodedResponse['token_type'] . '"',
            'status' => $httpCode,
            'response' => $decodedResponse
        ];
    }
    
    // Validate expires_in is a positive number
    if (!is_numeric($decodedResponse['expires_in']) || $decodedResponse['expires_in'] <= 0) {
        return [
            'success' => false,
            'error' => 'Invalid expires_in value',
            'status' => $httpCode,
            'response' => $decodedResponse
        ];
    }
    
    // Response is valid, save the connection
    $accessToken = $decodedResponse['access_token'];
    $refreshToken = $decodedResponse['refresh_token'] ?? null; // refresh_token is optional
    
    $connectionSaved = $backpackConnectionService->connectBackpackWithTokens(
        $userId,
        $provider->id,
        $email,
        $accessToken,
        $refreshToken
    );
    
    if (!$connectionSaved) {
        return [
            'success' => false,
            'error' => 'Failed to save backpack connection',
            'status' => $httpCode,
            'response' => $decodedResponse
        ];
    }
    
    return [
        'success' => true,
        'status' => $httpCode,
        'response' => $decodedResponse,
        'raw_response' => $response,
        'connection_saved' => true
    ];
}

/**
 * Handle API operations using the new OpenBadges API service
 */
function handleApiOperation(): void
{
    global $uid;
    
    if (!isset($_POST['api_operation'])) {
        return;
    }
    
    $operation = $_POST['api_operation'];
    $apiService = new OpenBadgesApiService();
    
    try {
        switch ($operation) {
            case 'get_badges':
                $response = $apiService->getUserBadges($uid);
                if ($response->isSuccess()) {
                    $badges = $response->getBadges();
                    Session::flash('message', sprintf(trans('langFoundBadges'), count($badges)));
                    Session::flash('alert-class', 'alert-success');
                    Session::flash('badges_data', json_encode($badges));
                } else {
                    Session::flash('message', trans('langErrorFetchingBadges') . ': ' . $response->getError());
                    Session::flash('alert-class', 'alert-danger');
                }
                break;
                
            case 'get_profile':
                $response = $apiService->getUserProfile($uid);
                if ($response->isSuccess()) {
                    $profile = $response->getProfile();
                    Session::flash('message', trans('langProfileFetchedSuccessfully'));
                    Session::flash('alert-class', 'alert-success');
                    Session::flash('profile_data', json_encode($profile));
                } else {
                    Session::flash('message', trans('langErrorFetchingProfile') . ': ' . $response->getError());
                    Session::flash('alert-class', 'alert-danger');
                }
                break;
                
            case 'get_collections':
                $response = $apiService->getUserCollections($uid);
                if ($response->isSuccess()) {
                    $collections = $response->getCollections();
                    Session::flash('message', sprintf(trans('langFoundCollections'), count($collections)));
                    Session::flash('alert-class', 'alert-success');
                    Session::flash('collections_data', json_encode($collections));
                } else {
                    Session::flash('message', trans('langErrorFetchingCollections') . ': ' . $response->getError());
                    Session::flash('alert-class', 'alert-danger');
                }
                break;
                
            case 'refresh_token':
                $response = $apiService->refreshAccessToken($uid);
                if ($response->isSuccess()) {
                    Session::flash('message', trans('langTokenRefreshedSuccessfully'));
                    Session::flash('alert-class', 'alert-success');
                } else {
                    Session::flash('message', trans('langErrorRefreshingToken') . ': ' . $response->getError());
                    Session::flash('alert-class', 'alert-danger');
                }
                break;
                
            default:
                Session::flash('message', trans('langUnsupportedOperation'));
                Session::flash('alert-class', 'alert-warning');
                break;
        }
    } catch (Exception $e) {
        error_log('API Operation Error: ' . $e->getMessage());
        Session::flash('message', trans('langApiOperationFailed') . ': ' . $e->getMessage());
        Session::flash('alert-class', 'alert-danger');
    }
    
    redirect_to_home_page('main/mybackpacks.php');
}

/**
 * Get provider capabilities for display
 */
function getProviderCapabilities($connection): array
{
    if (!$connection) {
        return [];
    }
    
    try {
        $provider = BackpackProvider::create(
            $connection->provider_name,
            $connection->api_url,
            $connection->ob_version
        );
        
        $apiService = new OpenBadgesApiService();
        return $apiService->getProviderCapabilities($provider);
    } catch (Exception $e) {
        error_log('Error getting provider capabilities: ' . $e->getMessage());
        return [];
    }
}
