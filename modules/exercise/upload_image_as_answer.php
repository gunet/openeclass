<?php

$require_current_course = TRUE;
include '../../include/baseTheme.php';

if (isset($_GET['delete_image'])) {
    $course_code = $_GET['course'];
    $questionId = $_GET['questionId'];
    $markerId = $_GET['markerId'];
    $exerciseId = $_GET['exerciseId'];
    $htopic = DRAG_AND_DROP_MARKERS;

    $filePath = "$webDir/courses/$course_code/image/answer-$questionId-$markerId";
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            // Update marker_answer_with_image flag of marker_id
            $options = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d AND course_id = ?d", $questionId, $course_id)->options;
            if ($options) {
                $finalData = [];
                $arr = explode('|', $options);
                if ($arr) {
                    foreach ($arr as $a) {
                        $data = json_decode($a, true);
                        if ($data && isset($data['marker_id'])) {
                            if ($data['marker_id'] == $markerId) {
                                // Update the field
                                $data['marker_answer_with_image'] = 0;
                            }
                            // Encode back to JSON
                            $json_str = json_encode($data);
                            $finalData[] = $json_str;
                        } else {
                            // If decoding fails or marker_id is missing, keep original string
                            $finalData[] = $a;
                        }
                    }

                    if (count($finalData) > 0) {
                        $res = implode('|', $finalData);
                        Database::get()->query("UPDATE exercise_question SET options = ?s WHERE id = ?d AND course_id = ?d", $res, $questionId, $course_id);
                    }
                }
            }
            Session::flash('message', $langImageHasBeenDeleted);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message', $langSomethingWentWrong);
            Session::flash('alert-class', 'alert-warning');
        }
    } else {
        Session::flash('message', $langFileNotFound);
        Session::flash('alert-class', 'alert-warning');
    }
    redirect_to_home_page("modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&modifyAnswers=$questionId&htopic=$htopic&remImg=true");
}

// Upload images as predefined answers for each marker.
if (isset($_FILES['image_as_answer'])) {
    $file = $_FILES['image_as_answer'];
    $course_code = $_POST['courseCode-image'];
    $qID = $_POST['questionId-image'];
    $mID = $_POST['markerId-image'];

    // Set the directory where you want to save the uploaded images
    $targetDir = "$webDir/courses/$course_code/image/";

    // Create the directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Generate a unique filename to prevent overwriting
    $filename = 'answer' . '-' . $qID . '-' . $mID;

    // Full path to save the file
    $targetFilePath = $targetDir . $filename;

    // Validate the file (optional but recommended)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($file['type'], $allowedTypes)) {
        // Move the uploaded file to the target directory
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            echo "Image uploaded successfully: " . $filename;
        } else {
            echo "Error: Failed to move uploaded file.";
        }
    } else {
        echo "Error: Only JPG, PNG, and GIF files are allowed.";
    }
} else {
    echo "No file uploaded.";
}