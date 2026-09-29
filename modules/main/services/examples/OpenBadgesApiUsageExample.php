<?php

/**
 * OpenBadgesApiUsageExample
 * 
 * Examples demonstrating how to use the declarative OpenBadges API service
 * to communicate with various backpack providers.
 */

// Example 1: Basic usage - Get user's badges
function getUserBadgesExample()
{
    $apiService = new OpenBadgesApiService();
    $userId = 123; // User ID from your system
    
    $response = $apiService->getUserBadges($userId);
    
    if ($response->isSuccess()) {
        $badges = $response->getBadges();
        echo "Found " . count($badges) . " badges\n";
        
        foreach ($badges as $badge) {
            echo "Badge: " . ($badge['name'] ?? $badge['badge']['name'] ?? 'Unknown') . "\n";
        }
    } else {
        echo "Error: " . $response->getError() . "\n";
    }
}

// Example 2: Get user profile information
function getUserProfileExample()
{
    $apiService = new OpenBadgesApiService();
    $userId = 123;
    
    $response = $apiService->getUserProfile($userId);
    
    if ($response->isSuccess()) {
        $profile = $response->getProfile();
        echo "User: " . ($profile['name'] ?? 'Unknown') . "\n";
        echo "Email: " . ($profile['email'] ?? 'Unknown') . "\n";
    } else {
        echo "Error: " . $response->getError() . "\n";
    }
}

// Example 3: Push a badge to user's backpack
function pushBadgeExample()
{
    $apiService = new OpenBadgesApiService();
    $userId = 123;
    
    // Badge data in OpenBadges format
    $badgeData = [
        '@context' => 'https://w3id.org/openbadges/v2',
        'type' => 'Assertion',
        'id' => 'https://example.com/badges/assertion/123',
        'badge' => [
            'type' => 'BadgeClass',
            'id' => 'https://example.com/badges/badge-class/456',
            'name' => 'Course Completion Badge',
            'description' => 'Awarded for completing the advanced course',
            'image' => 'https://example.com/badge-images/course-completion.png',
            'criteria' => 'https://example.com/criteria/course-completion',
            'issuer' => [
                'type' => 'Issuer',
                'id' => 'https://example.com/issuer',
                'name' => 'OpenEClass University',
                'url' => 'https://university.example.com'
            ]
        ],
        'recipient' => [
            'type' => 'email',
            'hashed' => false,
            'identity' => 'student@example.com'
        ],
        'issuedOn' => date('c'),
        'verification' => [
            'type' => 'hosted'
        ]
    ];
    
    $response = $apiService->pushBadgeToBackpack($userId, $badgeData);
    
    if ($response->isSuccess()) {
        echo "Badge successfully pushed to backpack\n";
        $data = $response->getData();
        if (isset($data['id'])) {
            echo "Badge ID: " . $data['id'] . "\n";
        }
    } else {
        echo "Error pushing badge: " . $response->getError() . "\n";
    }
}

// Example 4: Get provider capabilities
function getProviderCapabilitiesExample()
{
    $apiService = new OpenBadgesApiService();
    
    // Get a provider (this would typically come from your database)
    $provider = BackpackProvider::create(
        'Badgr',
        'https://api.badgr.io',
        '2.0'
    );
    
    $capabilities = $apiService->getProviderCapabilities($provider);
    
    echo "Provider: " . $provider->name . "\n";
    echo "Version: " . $capabilities['version'] . "\n";
    echo "Operations: " . implode(', ', $capabilities['operations']) . "\n";
    echo "Features: " . implode(', ', $capabilities['features']) . "\n";
}

// Example 5: Custom API call
function customApiCallExample()
{
    $apiService = new OpenBadgesApiService();
    $userId = 123;
    
    // Make a custom call to get endorsements (OpenBadges 2.1+ feature)
    $response = $apiService->customApiCall(
        $userId,
        'endorsements',
        'GET'
    );
    
    if ($response->isSuccess()) {
        $endorsements = $response->getData();
        echo "Found " . count($endorsements) . " endorsements\n";
    } else {
        echo "Error or feature not supported: " . $response->getError() . "\n";
    }
}

// Example 6: Batch operations
function batchOperationsExample()
{
    $apiService = new OpenBadgesApiService();
    $userId = 123;
    
    // First get all badges
    $badgesResponse = $apiService->getUserBadges($userId);
    
    if ($badgesResponse->isSuccess()) {
        $badges = $badgesResponse->getBadges();
        $badgeIds = [];
        
        // Extract badge IDs
        foreach ($badges as $badge) {
            if (isset($badge['id'])) {
                $badgeIds[] = $badge['id'];
            } elseif (isset($badge['entityId'])) {
                $badgeIds[] = $badge['entityId'];
            }
        }
        
        // Get detailed information for multiple badges
        $detailsResults = $apiService->getBatchBadgeDetails($userId, array_slice($badgeIds, 0, 5));
        
        foreach ($detailsResults as $badgeId => $response) {
            if ($response->isSuccess()) {
                echo "Badge {$badgeId}: Success\n";
            } else {
                echo "Badge {$badgeId}: Error - " . $response->getError() . "\n";
            }
        }
    }
}

// Example 7: Error handling and debugging
function errorHandlingExample()
{
    $apiService = new OpenBadgesApiService();
    $userId = 999; // Non-existent user
    
    $response = $apiService->getUserBadges($userId);
    
    if ($response->isError()) {
        $errorDetails = $response->getErrorDetails();
        
        echo "Request failed:\n";
        echo "Error: " . $errorDetails['error'] . "\n";
        echo "HTTP Code: " . $errorDetails['http_code'] . "\n";
        echo "Timestamp: " . $errorDetails['timestamp'] . "\n";
        
        if ($errorDetails['response_data']) {
            echo "Raw Response: " . json_encode($errorDetails['response_data'], JSON_PRETTY_PRINT) . "\n";
        }
    }
}

// Example 8: Working with different OpenBadges versions
function multiVersionExample()
{
    $apiService = new OpenBadgesApiService();
    
    // Example providers with different versions
    $providers = [
        BackpackProvider::create('Badgr 2.0', 'https://api.badgr.io', '2.0'),
        BackpackProvider::create('Canvas Badges', 'https://canvas.instructure.com', '2.1'),
        BackpackProvider::create('Future Provider', 'https://future-provider.com', '3.0')
    ];
    
    foreach ($providers as $provider) {
        echo "\nProvider: " . $provider->name . " (v" . $provider->ob_version . ")\n";
        
        $capabilities = $apiService->getProviderCapabilities($provider);
        echo "Supported operations: " . implode(', ', $capabilities['operations']) . "\n";
        
        $endpoints = $apiService->getProviderEndpoints($provider);
        foreach ($endpoints as $operation => $config) {
            echo "  {$operation}: {$config['method']} {$config['path']}\n";
        }
    }
}

// Example 9: Testing connection to a provider
function testConnectionExample()
{
    $apiService = new OpenBadgesApiService();
    
    $provider = BackpackProvider::create(
        'Test Provider',
        'https://api.example-badges.com',
        '2.0'
    );
    
    $accessToken = 'your-test-access-token';
    
    $response = $apiService->testConnection($provider, $accessToken);
    
    if ($response->isSuccess()) {
        echo "Connection successful!\n";
        $profile = $response->getProfile();
        if ($profile) {
            echo "Connected as: " . ($profile['name'] ?? 'Unknown user') . "\n";
        }
    } else {
        echo "Connection failed: " . $response->getError() . "\n";
        echo "HTTP Code: " . $response->getHttpCode() . "\n";
    }
}

// Example 10: Token refresh
function tokenRefreshExample()
{
    $apiService = new OpenBadgesApiService();
    $userId = 123;
    
    $response = $apiService->refreshAccessToken($userId);
    
    if ($response->isSuccess()) {
        $tokens = $response->getTokens();
        if ($tokens) {
            echo "Token refreshed successfully\n";
            echo "New access token: " . substr($tokens['access_token'], 0, 20) . "...\n";
            echo "Expires in: " . ($tokens['expires_in'] ?? 'Unknown') . " seconds\n";
        }
    } else {
        echo "Token refresh failed: " . $response->getError() . "\n";
    }
}

// Example usage in a real application context
function realWorldUsageExample()
{
    // This would typically be called from your application controllers
    $apiService = new OpenBadgesApiService();
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        echo "User not logged in\n";
        return;
    }
    
    try {
        // Get user's badges for display
        $badgesResponse = $apiService->getUserBadges($userId);
        
        if ($badgesResponse->isSuccess()) {
            $badges = $badgesResponse->getBadges();
            
            // Process badges for display
            $displayBadges = [];
            foreach ($badges as $badge) {
                $displayBadges[] = [
                    'name' => $badge['badge']['name'] ?? $badge['name'] ?? 'Unknown Badge',
                    'description' => $badge['badge']['description'] ?? $badge['description'] ?? '',
                    'image' => $badge['badge']['image'] ?? $badge['image'] ?? '',
                    'issued_on' => $badge['issuedOn'] ?? $badge['issued_on'] ?? null
                ];
            }
            
            // Return data for template rendering
            return [
                'success' => true,
                'badges' => $displayBadges,
                'total_count' => count($displayBadges)
            ];
        } else {
            // Log error for debugging
            error_log("Failed to fetch badges for user {$userId}: " . $badgesResponse->getError());
            
            return [
                'success' => false,
                'error' => 'Unable to fetch badges from your backpack. Please check your connection.',
                'badges' => []
            ];
        }
    } catch (Exception $e) {
        error_log("Exception in badge fetching: " . $e->getMessage());
        
        return [
            'success' => false,
            'error' => 'An unexpected error occurred. Please try again later.',
            'badges' => []
        ];
    }
}

/*
 * Usage Tips:
 * 
 * 1. Always check response->isSuccess() before processing data
 * 2. Use specific getter methods like getBadges(), getProfile(), etc.
 * 3. Handle different OpenBadges versions gracefully
 * 4. Log errors for debugging purposes
 * 5. Use batch operations when fetching multiple items
 * 6. Test connections before making bulk operations
 * 7. Implement proper error handling and user feedback
 * 8. Cache responses when appropriate to reduce API calls
 * 9. Use the capabilities method to check what operations are supported
 * 10. Implement token refresh logic for long-running applications
 */ 