<?php

require_once 'exercise.class.php';

$require_current_course = true;
$require_login = true;

require_once '../../include/baseTheme.php';

$unit = $unit ?? null;
$back_url = $unit?
    "modules/units/index.php?course=$course_code&id=$unit":
    "modules/exercise/index.php?course=$course_code";

if (isset($_REQUEST['exerciseId'])) {
    $exerciseId = intval($_REQUEST['exerciseId']);
    // Check if an exercise object exists in session
    if (isset($_SESSION['objExercise'][$exerciseId])) {
        $objExercise = $_SESSION['objExercise'][$exerciseId];
    } else {
        // construction of Exercise
        $objExercise = new Exercise();
        // if the specified exercise is disabled (this only applies to students)
        // or doesn't exist, redirect and show error
        if (!$objExercise->read($exerciseId) || (!$is_editor && $objExercise->selectStatus($exerciseId) == 0)) {
            Session::Messages($langExerciseNotFound, 'alert-warning');
            redirect_to_home_page($back_url);
        }
        // saves the object into the session
        $_SESSION['objExercise'][$exerciseId] = $objExercise;
    }
} else {
    redirect_to_home_page($back_url);
}
$objExercise->LaunchSafeExamBrowser();
