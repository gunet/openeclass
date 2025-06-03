<?php

$require_current_course = TRUE;
include '../../include/baseTheme.php';

// Path to the JSON file
$course_code = $_GET['course_code'];
$questionId = $_GET['questionId'];

// Create json file
$dropZonesDir = "$webDir/courses/$course_code/dropZones";
// Ensure directory exists with correct permissions
if (!file_exists($dropZonesDir)) {
    mkdir($dropZonesDir, 0775, true); // Use 775 for web server user group
    chmod($dropZonesDir, 0775);
}
// Define the file path
$dropZonesFile = "$dropZonesDir/dropZones_$questionId.json";
// Check if file exists
if (!file_exists($dropZonesFile)) {
    // Create the file
    $file = fopen($dropZonesFile, "w");
    if ($file === false) {
        die("Unable to open or create the file in '$dropZonesFile'.");
    }
    fwrite($file, '[]');
    fclose($file);
    // Set permissions after file creation
    chmod($dropZonesFile, 0664);
}

// Read POST data
$verticesJson = file_get_contents("php://input");
// Decode vertices
$vertices = json_decode($verticesJson, true);

// print_r($vertices);

if (!$vertices) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Get the data from json file
$dataFromJsonFile = file_get_contents($dropZonesFile);
$oldData = json_decode($dataFromJsonFile, true);
// Call update function
$updated = updateData($oldData, $vertices);
if ($updated) {
    // Save back to JSON file
    file_put_contents($dropZonesFile, json_encode($oldData, JSON_PRETTY_PRINT));
} else {
    // Read existing data from JSON file
    $currentDataJson = file_get_contents($dropZonesFile);
    $currentData = json_decode($currentDataJson, true);
    // Check if decoding was successful
    if ($currentData === null) {
        // If the file is empty or invalid, start with an empty array
        $currentData = [];
    }
    // Append the new vertices data
    $currentData[] = $vertices;
    // Save the updated array back to the JSON file
    file_put_contents($dropZonesFile, json_encode($currentData, JSON_PRETTY_PRINT));
    chmod($dropZonesFile, 0664);
}

// Get the data from json file
$finalDataFromJsonFile = file_get_contents($dropZonesFile);
$finalData = json_decode($finalDataFromJsonFile, true);
echo json_encode(['status' => 'success', 'data' => $finalData]);
exit;



// Function to find and replace key in existing data
function updateData(&$oldData, $vertices) {
    foreach ($oldData as &$subArray) {
        foreach ($subArray as &$item) {
            // Check if item has 'marker_id' matching
            if (isset($item['marker_id']) && isset($vertices[0]['marker_id'])) {
                if ($item['marker_id'] == $vertices[0]['marker_id']) {
                    // Loop over sent data objects to update matching keys
                    foreach ($vertices as $newItem) {
                        foreach ($subArray as &$existingItem) {
                            // Check if key exists in existing item
                            foreach ($newItem as $key => $value) {
                                if (isset($existingItem[$key])) {
                                    // Replace value
                                    $existingItem[$key] = $value;
                                }
                            }
                        }
                    }
                    return true; // Exit once updated
                }
            }
        }
    }
    return false; // Not found
}