<?php
session_start();
$path2add = 0;
include 'include/baseTheme.php';

if (!isset($_SESSION['saved_editor'])) {
    $cours_id = intval(course_code_to_id($_GET['course']));
    $is_editor = FALSE;
    if (check_editor()) {
        $is_editor = TRUE;
    }
}

if ((isset($_SESSION['statut']) and $_SESSION['statut'] == 1) or $is_editor) {
        $_SESSION['saved_statut'] = $_SESSION['statut'];
        $_SESSION['statut'] = 5;
        $_SESSION['saved_editor'] = $is_editor;
} elseif (isset($_SESSION['saved_statut'])) {    
        $_SESSION['statut'] = $_SESSION['saved_statut'];
        unset($_SESSION['saved_statut']);
        unset($_SESSION['saved_editor']);
}
if (isset($_SESSION['dbname'])) {
	$_SESSION['status'][$_SESSION['dbname']] = $_SESSION['statut'];
        header("Location: {$urlServer}courses/$_SESSION[dbname]/");
} else {
        header('Location: ' . $urlServer);
}
