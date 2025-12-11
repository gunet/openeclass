<?php
/*
 * ========================================================================
 * * Open eClass
 * * E-learning and Course Management System
 * * ========================================================================
 * * Copyright 2003-2024, Greek Universities Network - GUnet
 * *
 * * Open eClass is an open platform distributed in the hope that it will
 * * be useful (without any warranty), under the terms of the GNU (General
 * * Public License) as published by the Free Software Foundation.
 * * The full license can be read in "/info/license/license_gpl.txt".
 * *
 * * Contact address: GUnet Asynchronous eLearning Group
 * *                  e-mail: info@openeclass.org
 * * ========================================================================
 */

/**
 * AI Course Generation Endpoint
 * Handles manual course generation from user prompts
 */

$require_login = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/ai/services/AICourseExtractionService.php';

// Check permissions - only teachers and department managers can create courses
if ($session->status !== USER_TEACHER && !$is_departmentmanage_user) {
    header('HTTP/1.0 403 Forbidden');
    exit(json_encode(['success' => false, 'error' => 'Access denied']));
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    exit(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

// Validate CSRF token
if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
    header('HTTP/1.0 400 Bad Request');
    exit(json_encode(['success' => false, 'error' => 'Invalid token']));
}

// Set JSON response header
header('Content-Type: application/json');

try {
    // Check if AI service is available
    if (!AICourseExtractionService::isEnabled()) {
        throw new Exception($langAINotAvailable ?? 'AI service is not available');
    }

    // Validate required fields
    if (empty($_POST['course_prompt'])) {
        throw new Exception($langFieldsMissing ?? 'Course description is required');
    }

    $prompt = trim($_POST['course_prompt']);

    // Validate prompt length
    if (strlen($prompt) < 10) {
        throw new Exception($langPromptTooShort ?? 'Course description is too short. Please provide more details.');
    }

    if (strlen($prompt) > 5000) {
        throw new Exception($langPromptTooLong ?? 'Course description is too long. Please keep it under 5000 characters.');
    }

    // Get generation options
    $options = [];

    // Initialize AI service
    $aiService = new AICourseExtractionService();

    // Generate course data from prompt
    $courseData = $aiService->generateFromPrompt($prompt, $options);

    // Validate generated data
    if (!$aiService->validateCourseData($courseData)) {
        throw new Exception($langAIGenerationFailed ?? 'Generated course data is invalid');
    }

    // Sanitize the data
    $sanitizedData = $aiService->sanitizeCourseData($courseData);

    // Add generation metadata
    $sanitizedData['generation_method'] = 'prompt';
    $sanitizedData['original_prompt'] = substr($prompt, 0, 500); // Store first 500 chars for reference
    $sanitizedData['options_used'] = $options;

    // Log generation activity
    $promptSample = substr($prompt, 0, 100) . (strlen($prompt) > 100 ? '...' : '');
    error_log("AI Course Generation: Prompt processed - \"$promptSample\"");

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $sanitizedData,
        'message' => $langAIGenerationSuccess ?? 'Course data generated successfully',
        'provider_info' => $aiService->getProviderInfo()
    ]);

} catch (Exception $e) {
    // Log error
    error_log("AI Course Generation Error: " . $e->getMessage());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'error_type' => get_class($e)
    ]);
}
