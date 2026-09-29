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
 * @file extrepo_search.php
 * @brief AJAX endpoint for searching external repositories
 */

$require_current_course = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/externalrepos/ExternalRepoFactory.php';

header('Content-Type: application/json; charset=utf-8');

// Validate CSRF token
if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
    echo json_encode([
        'success' => false,
        'error' => $langCSRF ?? 'Invalid security token'
    ]);
    exit;
}

// Get request parameters
$repositoryId = isset($_POST['repository_id']) ? intval($_POST['repository_id']) : 0;
$query = isset($_POST['query']) ? trim($_POST['query']) : '';
$page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
$perPage = isset($_POST['per_page']) ? min(50, max(5, intval($_POST['per_page']))) : 20;
$filters = isset($_POST['filters']) ? (array)$_POST['filters'] : [];

// Validate repository ID
if ($repositoryId <= 0) {
    echo json_encode([
        'success' => false,
        'error' => $langSelectRepository ?? 'Please select a repository'
    ]);
    exit;
}

// Validate search query
if (empty($query)) {
    echo json_encode([
        'success' => false,
        'error' => $langEmptySearchQuery ?? 'Please enter a search query'
    ]);
    exit;
}

try {
    // Create repository instance
    $repository = ExternalRepoFactory::createById($repositoryId);
    
    if (!$repository) {
        echo json_encode([
            'success' => false,
            'error' => $langRepoNotFound ?? 'Repository not found'
        ]);
        exit;
    }
    
    // Check if repository is configured
    if (!$repository->isConfigured()) {
        echo json_encode([
            'success' => false,
            'error' => $langRepoNotConfigured ?? 'Repository is not properly configured'
        ]);
        exit;
    }
    
    // Perform search
    $results = $repository->search($query, $filters, $page, $perPage);
    
    // Log the response for debugging
    $jsonResponse = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    error_log("External Repo Search Response (Repository ID: $repositoryId, Query: $query):\n" . $jsonResponse);
    
    // Return results
    echo json_encode($results);
    
} catch (Exception $e) {
    error_log("External repo search error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => $langSearchError ?? 'Search failed. Please try again.',
        'debug' => $e->getMessage() // Include error message for debugging
    ]);
}

