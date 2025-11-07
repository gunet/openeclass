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
    $coordinatesXY = [];
    $dataJsonMarkers = explode('|', $_SESSION['data_shapes'][$questionId]);
    foreach ($dataJsonMarkers as $dataJsonValue) {
        $markersData = json_decode($dataJsonValue, true);
        // Loop through each item in the original array
        if ($markersData) {
            foreach ($markersData as $index => $value) {
                if (count($markersData) == 10) { // circle or rectangle
                    $arrDataMarkers[$markersData['marker_id']] = [
                        'marker_answer' => $markersData['marker_answer'],
                        'marker_shape' => $markersData['shape_type'],
                        'marker_coordinates' => $markersData['x'] . ',' . $markersData['y'],
                        'marker_offsets' => $markersData['endX'] . ',' . $markersData['endY'],
                        'marker_grade' => $markersData['marker_grade'],
                        'marker_radius' => $markersData['marker_radius'],
                        'marker_answer_with_image' => $markersData['marker_answer_with_image']
                    ];
                } elseif (count($markersData) == 6) { // polygon
                    $arrDataMarkers[$markersData['marker_id']] = [
                        'marker_answer' => $markersData['marker_answer'],
                        'marker_shape' => $markersData['shape_type'],
                        'marker_coordinates' => $markersData['points'],
                        'marker_grade' => $markersData['marker_grade'],
                        'marker_answer_with_image' => $markersData['marker_answer_with_image']
                    ];
                } elseif (count($markersData) == 5) { // without shape . So the defined answer is not correct
                    $arrDataMarkers[$markersData['marker_id']] = [
                        'marker_answer' => $markersData['marker_answer'],
                        'marker_shape' => null,
                        'marker_coordinates' => null,
                        'marker_grade' => 0,
                        'marker_answer_with_image' => $markersData['marker_answer_with_image']
                    ];
                }
            }
        }
    }
    foreach ($arrDataMarkers as $index => $m) {
        $arr_m = explode(',', $m['marker_coordinates'] ?? '');
        if (count($arr_m) == 2) {
            $m['x'] = $arr_m[0];
            $m['y'] = $arr_m[1];
        }
        if ($m['marker_shape'] == 'circle' or $m['marker_shape'] == 'rectangle') {
            $arr_of = explode(',', $m['marker_offsets']);
            $m['endx'] = $arr_of[0];
            $m['endy'] = $arr_of[1];
        }
        if ($m['marker_shape'] == 'circle' && count($arr_m) == 2) {
            $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'marker_shape' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)', 'radius' => $m['marker_radius'], 'marker_answer_with_image' => $m['marker_answer_with_image']];
        } elseif ($m['marker_shape'] == 'rectangle' && count($arr_m) == 2) {
            $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'marker_shape' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)', 'width' => $m['endy'], 'height' => $m['endx'], 'marker_answer_with_image' => $m['marker_answer_with_image']];
        } elseif ($m['marker_shape'] == 'polygon') {
            $coordinatesXY[] = ['marker_id' => $index, 'points' => $m['marker_coordinates'], 'marker_shape' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)', 'marker_answer_with_image' => $m['marker_answer_with_image']];
        }
    }
    $DataMarkersToJson = json_encode($coordinatesXY) ?? '';
    echo json_encode(['status' => 'success', 'data' => $DataMarkersToJson, 'vertices' => $vertices]);
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