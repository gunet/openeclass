<?php

$require_current_course = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';

// Set JSON header
header('Content-Type: application/json');

// Check if required parameters are present
if (!isset($_GET['course_id']) || !isset($_GET['type'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_GET['course_id']);
$type = $_GET['type'];

// Validate type parameter
if (!in_array($type, ['header', 'footer'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid type parameter']);
    exit;
}

// Verify course exists and user has access
$course = Database::get()->querySingle("SELECT code FROM course WHERE id = ?d", $course_id);
if (!$course) {
    echo json_encode(['success' => false, 'error' => 'Course not found']);
    exit;
}

try {
    // Query for image files in course documents
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    $extension_conditions = [];
    foreach ($image_extensions as $ext) {
        $extension_conditions[] = "format = ?s";
    }
    $extension_sql = implode(' OR ', $extension_conditions);

    $sql = "SELECT id, filename, path, format, title, date_modified 
            FROM document 
            WHERE course_id = ?d 
            AND subsystem = 0 
            AND visible = 1 
            AND ($extension_sql)
            ORDER BY filename ASC";

    $params = array_merge([$course_id], $image_extensions);
    $documents = Database::get()->queryArray($sql, ...$params);

    $images = [];
    foreach ($documents as $doc) {
        // Generate accessible URL for the image
        $file_url = $urlServer . "modules/document/index.php?course=" . $course->code . "&download=" . getInDirectReference($doc->path);

        $images[] = [
            'id' => $doc->id,
            'path' => $doc->path,
            'name' => $doc->title ?: $doc->filename,
            'url' => $file_url,
            'filename' => $doc->filename,
            'format' => $doc->format
        ];
    }

    echo json_encode([
        'success' => true,
        'images' => $images,
        'count' => count($images)
    ]);

} catch (Exception $e) {
    error_log("Error in ajax_load_images.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
}