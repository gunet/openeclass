<?php

$require_current_course = TRUE;
include '../../include/baseTheme.php';

// Fetch parameters
$course_code = $_GET['course_code'];
$questionId = $_GET['questionId'];

// Path to JSON file
$dropZonesDir = "$webDir/courses/$course_code/image";
$dropZonesFile = "$dropZonesDir/dropZones_$questionId.json";

// Check if marker_id is provided
if (isset($_POST['marker_id'])) {
    $targetMarkerId = intval($_POST['marker_id']);

    // Delete image as predefined answer
    $imagePathDel = "$webDir/courses/$course_code/image/answer-$questionId-$targetMarkerId";
    if (file_exists($imagePathDel)) {
        unlink($imagePathDel);
    }

    // Read existing JSON data
    $jsonData = file_get_contents($dropZonesFile);
    $data = json_decode($jsonData, true);

    if ($data === null) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
        exit;
    }

    // Filter out entire inner arrays where the first object has marker_id == targetMarkerId
    $filteredData = array_filter($data, function($innerArray) use ($targetMarkerId) {
        if (is_array($innerArray) && count($innerArray) > 0) {
            $firstObject = $innerArray[0];
            if (isset($firstObject['marker_id']) && $firstObject['marker_id'] == $targetMarkerId) {
                // Exclude this array
                return false;
            }
        }
        // Keep this array
        return true;
    });

    // Reindex array to avoid gaps
    $newData = array_values($filteredData);

    // Save the updated data back to JSON
    $jsonToSave = json_encode($newData, JSON_PRETTY_PRINT);
    if (file_put_contents($dropZonesFile, $jsonToSave)) {
        echo json_encode(['status' => 'success', 'message' => 'Markers with specified marker_id removed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to write JSON data']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'No marker_id provided']);
}
?>