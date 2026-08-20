<?php

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/ai/AIProviderFactory.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providerType = $_POST['provider'] ?? '';
    $response = ['provider' => $providerType, 'models' => []];

    try {
        if ($providerType === 'other') {
            $providerType = 'custom';
        }

        $provider = AIProviderFactory::create($providerType, ['provider_type' => $providerType]);

        // Fetch available models
        $models = $provider->getAvailableModels();
        
        if (empty($models)) {
            $response['error'] = 'No models available. Please check your API key.';
            $response['success'] = false;
        } else {
            $response['models'] = $models;
            $response['success'] = true;
        }
    } catch (Exception $e) {
        error_log("Error in aigetmodels.php: " . $e->getMessage());
        $response['error'] = $e->getMessage();
        $response['success'] = false;
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}