<?php

session_start();
require_once 'include/baseTheme.php';

if (!isset($_SESSION['saved_editor'])) {
    $course_id = intval(course_code_to_id($_GET['course']));
    $is_editor = FALSE;
    if (check_editor()) {
        $is_editor = TRUE;
    }
}

if ((isset($_SESSION['status']) and $_SESSION['status'] == 1) or $is_editor) {
    $_SESSION['saved_status'] = $_SESSION['status'];
    $_SESSION['status'] = 5;
    $_SESSION['saved_editor'] = $is_editor;
} elseif (isset($_SESSION['saved_status'])) {
    $_SESSION['status'] = $_SESSION['saved_status'];
    unset($_SESSION['saved_status']);
    unset($_SESSION['saved_editor']);
}
if (isset($_SESSION['dbname'])) {
    $_SESSION['courses'][$_SESSION['dbname']] = $_SESSION['status'];
    header("Location: {$urlServer}courses/$_SESSION[dbname]/");
} else {
    header('Location: ' . $urlServer);
}
