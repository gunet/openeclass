<?php

$require_current_course = true;
$path2add = 2;
require_once '../include/baseTheme.php';

if (isset($_SESSION['student_view'])) {
    unset($_SESSION['student_view']);
} else {
    $_SESSION['student_view'] = $course_code;
}

if (isset($_POST['next'])) {
    header('Location: ' . $_POST['next']);
} else {
    header("Location: {$urlServer}courses/$course_code/");
}
