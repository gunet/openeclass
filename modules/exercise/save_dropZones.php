<?php

$require_current_course = TRUE;
include '../../include/baseTheme.php';

// Path to the JSON file
$course_code = $_GET['course_code'];
$questionId = $_GET['questionId'];

// Delete marker id
if (isset($_POST['deleteMarker']) && $_POST['deleteMarker'] == 1) {
    remove_json_marker_id_if_exists($_POST['marker_id'], $questionId);
    Database::get()->query("UPDATE exercise_question SET options = ?s WHERE id = ?d", $_SESSION['data_shapes'][$questionId], $questionId);
    // Delete image if needs
    $mId = $_POST['marker_id'];
    $filePath = "$webDir/courses/$course_code/image/answer-$questionId-$mId";
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    exit;
}

header('Content-Type: application/json');

// Read POST data
$verticesJson = file_get_contents("php://input");
// Decode vertices
$vertices = json_decode($verticesJson, true);

if (!$vertices or $questionId < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Initialize an empty associative array
$assocArray = array();
// Loop through each object and add key-value pairs to the associative array
foreach ($vertices as $item) {
    foreach ($item as $key => $value) {
        $assocArray[$key] = purify($value);
    }
}
$str_json = json_encode($assocArray ?? '');

$_SESSION['data_shapes'][$questionId] = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
if (isset($_SESSION['data_shapes'][$questionId]) && !empty($_SESSION['data_shapes'][$questionId])) {
    remove_json_marker_id_if_exists($assocArray['marker_id'], $questionId);
    $_SESSION['data_shapes'][$questionId] .=  '|' . $str_json;
} else {
    $_SESSION['data_shapes'][$questionId] = $str_json;
}

$q = Database::get()->query("UPDATE exercise_question SET options = ?s WHERE id = ?d", $_SESSION['data_shapes'][$questionId], $questionId);
if ($q) {
    echo json_encode(['status' => 'success', 'data' => $vertices]);
}

exit;

// Remove marker id with its values if needs
function remove_json_marker_id_if_exists($markerId, $questionId) {
   
    if ($markerId > 0 && isset($_SESSION['data_shapes'][$questionId])) {
        $jsonArray = explode('|', $_SESSION['data_shapes'][$questionId]);
        $newJsonArray = [];

        foreach ($jsonArray as $json) {
            $jsonDecoded = json_decode($json, true);
            if ($jsonDecoded && isset($jsonDecoded['marker_id'])) {
                if ($jsonDecoded['marker_id'] != $markerId) {
                    $newJsonArray[] = $json; // keep if not matching
                }
                // else, skip (this removes the matching marker_id)
            } else {
                // handle invalid JSON if needed
                $newJsonArray[] = $json; // keep invalid JSON as is
            }
        }

        $_SESSION['data_shapes'][$questionId] = implode('|', $newJsonArray);
    }
}