<?php

$require_admin = true;
require_once '../../include/baseTheme.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providerType = $_POST['provider'] ?? '';
    $response = ['provider' => $providerType, 'models' => []];

    try {
        // Determine the provider and call its getAvailableModels() method
        switch ($providerType) {
            case 'openai':
                require_once __DIR__ . '/../../include/lib/ai/providers/OpenAIProvider.php';
                $provider = new OpenAIProvider(['provider_type' => 'openai']);
                break;
            case 'anthropic':
                require_once __DIR__ . '/../../include/lib/ai/providers/AnthropicProvider.php';
                $provider = new AnthropicProvider(['provider_type' => 'anthropic']);
                break;
            case 'gemini':
                require_once __DIR__ . '/../../include/lib/ai/providers/GeminiProvider.php';
                $provider = new GeminiProvider(['provider_type' => 'gemini']);
                break;
            default:
                throw new Exception("Invalid provider type: " . $providerType);
        }

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