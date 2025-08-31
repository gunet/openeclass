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
 * AI Syllabus Extraction Endpoint
 * Handles PDF syllabus upload and extraction (currently stub with dummy data)
 */

$require_login = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/ai/services/AICourseExtractionService.php';

/**
 * Validate URL (handles Unicode characters better than filter_var)
 * 
 * @param string $url The URL to validate
 * @return bool True if valid URL
 */
function validateURL(string $url): bool {
    // Basic format check
    if (!preg_match('/^https?:\/\//', $url)) {
        return false;
    }
    
    // Try to parse the URL
    $parsed = parse_url($url);
    if (!$parsed || !isset($parsed['host']) || !isset($parsed['scheme'])) {
        return false;
    }
    
    // Check scheme
    if (!in_array($parsed['scheme'], ['http', 'https'])) {
        return false;
    }
    
    // Check if host is valid
    if (empty($parsed['host']) || !filter_var($parsed['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        return false;
    }
    
    return true;
}

/**
 * Download content from URL using cURL (supports both PDF and HTML)
 * 
 * @param string $url The URL to download from
 * @return array Array with file_path, file_name, file_size, content_type
 * @throws Exception If download fails
 */
function downloadContentFromURL(string $url): array {
    global $langAIURLDownloadFailed, $langAIURLNotAccessible, $langFileSizeExceeded;
    
    // Create temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'ai_syllabus_');
    if (!$tempFile) {
        throw new Exception($langAIURLDownloadFailed ?? 'Failed to create temporary file');
    }

    // Initialize cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 60, // 60 seconds timeout
        CURLOPT_CONNECTTIMEOUT => 30, // 30 seconds connection timeout
        CURLOPT_FILE => fopen($tempFile, 'w'),
        CURLOPT_USERAGENT => 'OpenEclass AI Course Extractor/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_MAXFILESIZE => 10 * 1024 * 1024, // 10MB limit
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    // Check for cURL errors
    if ($result === false || $errno !== CURLE_OK) {
        unlink($tempFile);
        throw new Exception(($langAIURLDownloadFailed ?? 'Download failed') . ': ' . $error);
    }

    // Check HTTP response code
    if ($httpCode !== 200) {
        unlink($tempFile);
        throw new Exception(($langAIURLNotAccessible ?? 'URL not accessible') . ' (HTTP ' . $httpCode . ')');
    }

    // Validate file size
    $fileSize = filesize($tempFile);
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($fileSize > $maxSize) {
        unlink($tempFile);
        throw new Exception($langFileSizeExceeded ?? 'File size exceeds maximum limit');
    }

    if ($fileSize === 0) {
        unlink($tempFile);
        throw new Exception($langAIURLDownloadFailed ?? 'Downloaded file is empty');
    }

    // Determine content type and validate
    $detectedContentType = 'unknown';
    if ($contentType) {
        if (str_contains($contentType, 'application/pdf')) {
            $detectedContentType = 'pdf';
        } elseif (str_contains($contentType, 'text/html') || str_contains($contentType, 'text/plain')) {
            $detectedContentType = 'html';
        }
    }
    
    // If content type detection failed, check file signature
    if ($detectedContentType === 'unknown') {
        $fileHandle = fopen($tempFile, 'rb');
        $signature = fread($fileHandle, 4);
        fclose($fileHandle);
        
        if ($signature === '%PDF') {
            $detectedContentType = 'pdf';
        } else {
            // Assume it's HTML/text content if not PDF
            $detectedContentType = 'html';
        }
    }

    // Extract filename from URL
    $parsedUrl = parse_url($url);
    $fileName = basename($parsedUrl['path'] ?? '');
    
    // Generate appropriate filename based on content type
    if ($detectedContentType === 'pdf') {
        if (empty($fileName)) {
            $fileName = 'syllabus.pdf';
        } elseif (!str_ends_with(strtolower($fileName), '.pdf')) {
            $fileName .= '.pdf';
        }
    } else {
        if (empty($fileName)) {
            $fileName = 'content.html';
        } elseif (!str_ends_with(strtolower($fileName), '.html') && !str_ends_with(strtolower($fileName), '.htm')) {
            $fileName .= '.html';
        }
    }

    // Decode URL-encoded filename
    $fileName = urldecode($fileName);

    return [
        'file_path' => $tempFile,
        'file_name' => $fileName,
        'file_size' => $fileSize,
        'content_type' => $detectedContentType
    ];
}

/**
 * Extract text content from HTML file
 * 
 * @param string $filePath Path to HTML file
 * @return string Extracted text content
 * @throws Exception If extraction fails
 */
function extractTextFromHTML(string $filePath): string {
    $htmlContent = file_get_contents($filePath);
    if ($htmlContent === false) {
        throw new Exception('Failed to read HTML file');
    }

    // Remove script and style elements completely
    $htmlContent = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $htmlContent);
    $htmlContent = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $htmlContent);
    
    // Convert HTML entities to their corresponding characters
    $htmlContent = html_entity_decode($htmlContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Strip HTML tags but preserve line breaks
    $htmlContent = str_replace(['</p>', '</div>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '<br>', '<br/>', '<br />'], "\n", $htmlContent);
    $htmlContent = strip_tags($htmlContent);
    
    // Clean up whitespace
    $htmlContent = preg_replace('/\n\s*\n/', "\n\n", $htmlContent); // Multiple newlines to double newlines
    $htmlContent = preg_replace('/[ \t]+/', ' ', $htmlContent); // Multiple spaces/tabs to single space
    $htmlContent = trim($htmlContent);
    
    if (empty($htmlContent)) {
        throw new Exception('No readable text content found in HTML');
    }
    
    return $htmlContent;
}

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
    if (!AICourseExtractionService::isAvailable()) {
        throw new Exception($langAINotAvailable ?? 'AI service is not available');
    }

    $inputMethod = $_POST['input_method'] ?? 'upload';
    $pdfFilePath = null;
    $fileName = '';
    $fileSize = 0;
    $needsCleanup = false;

    if ($inputMethod === 'url') {
        // Handle URL download
        $syllabusUrl = $_POST['syllabus_url'] ?? '';
        
        if (empty($syllabusUrl)) {
            throw new Exception($langAIInvalidURL ?? 'URL is required');
        }

        // Validate URL format (handle Unicode characters)
        if (!validateURL($syllabusUrl)) {
            throw new Exception($langAIInvalidURL ?? 'Invalid URL format');
        }

        // Download content from URL (PDF or HTML)
        $downloadResult = downloadContentFromURL($syllabusUrl);
        $pdfFilePath = $downloadResult['file_path'];
        $fileName = $downloadResult['file_name'];
        $fileSize = $downloadResult['file_size'];
        $contentType = $downloadResult['content_type'];
        $needsCleanup = true;

    } else {
        // Handle file upload
        if (!isset($_FILES['syllabus_pdf']) || $_FILES['syllabus_pdf']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception($langInvalidFile ?? 'Invalid file upload');
        }

        $uploadedFile = $_FILES['syllabus_pdf'];
        $pdfFilePath = $uploadedFile['tmp_name'];
        $fileName = $uploadedFile['name'];
        $fileSize = $uploadedFile['size'];
        
        // Validate file type for uploaded files
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $pdfFilePath);
        finfo_close($fileInfo);
        
        if ($mimeType !== 'application/pdf') {
            throw new Exception($langInvalidFileType ?? 'Only PDF files are allowed');
        }

        // Validate file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($fileSize > $maxSize) {
            throw new Exception($langFileSizeExceeded ?? 'File size exceeds maximum limit');
        }
    }

    // Get extraction options
    $options = [];

    // Initialize AI service
    $aiService = new AICourseExtractionService();
    
    // Extract text based on content type
    if ($inputMethod === 'url' && isset($contentType) && $contentType === 'html') {
        // Extract text from HTML content
        $extractedText = extractTextFromHTML($pdfFilePath);
    } else {
        // Extract text from PDF (default behavior for uploads and PDF URLs)
        $extractedText = $aiService->extractTextFromPDF($pdfFilePath);
    }
    
    // Log successful text extraction
    $textLength = mb_strlen($extractedText);
    $source = $inputMethod === 'url' ? "URL: " . ($syllabusUrl ?? '') : "Upload: " . $fileName;
    error_log("AI Course Extraction: Text extracted from PDF - " . $source . " (" . round($fileSize / 1024, 2) . "KB, {$textLength} chars)");
    
    // Generate course data using AI
    $courseData = $aiService->extractFromSyllabus($extractedText, $options);
    
    // Add extraction metadata
    if ($inputMethod === 'url') {
        $courseData['extraction_method'] = isset($contentType) && $contentType === 'html' ? 'web_url' : 'pdf_url';
    } else {
        $courseData['extraction_method'] = 'pdf_upload';
    }
    $courseData['file_name'] = $fileName;
    $courseData['file_size'] = $fileSize;
    $courseData['text_length'] = $textLength;
    $courseData['generated_at'] = date('Y-m-d H:i:s');
    if ($inputMethod === 'url') {
        $courseData['source_url'] = $syllabusUrl ?? '';
    }

    // Clean up downloaded file if needed
    if ($needsCleanup && $pdfFilePath && file_exists($pdfFilePath)) {
        unlink($pdfFilePath);
    }

    // Validate generated course data
    if (!$aiService->validateCourseData($courseData)) {
        throw new Exception($langAIGenerationFailed ?? 'Generated course data is invalid');
    }
    
    // Sanitize the data
    $sanitizedData = $aiService->sanitizeCourseData($courseData);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $sanitizedData,
        'message' => $langAIExtractionSuccess ?? 'Course data extracted successfully from syllabus',
        'provider_info' => $aiService->getProviderInfo()
    ]);

} catch (Exception $e) {
    // Clean up downloaded file if needed
    if (isset($needsCleanup) && $needsCleanup && isset($pdfFilePath) && $pdfFilePath && file_exists($pdfFilePath)) {
        unlink($pdfFilePath);
    }
    
    // Log error
    error_log("AI Syllabus Extraction Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}