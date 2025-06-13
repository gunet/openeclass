<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $providerType = $_POST['provider'] ?? '';
    $response = ['provider' => $providerType, 'models' => []];

    try {
        // Determine the provider and call its getAvailableModels() method
        switch ($providerType) {
            case 'openai':
                require_once '../../include/lib/ai/providers/OpenAIProvider.php';
                $provider = new OpenAIProvider();
                break;
            case 'anthropic':
                require_once '../../include/lib/ai/providers/AnthropicProvider.php';
                $provider = new AnthropicProvider();
                break;
            case 'gemini':
                require_once '../../include/lib/ai/providers/GeminiProvider.php';
                $provider = new GeminiProvider();
                break;
            default:
                throw new Exception("Invalid provider type: " . $providerType);
        }

        // Fetch available models
        $response['models'] = $provider->getAvailableModels();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}