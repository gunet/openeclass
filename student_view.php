<?php
session_start();
$path2add = 0;
include 'include/baseTheme.php';
if (isset($_SESSION['statut']) and $_SESSION['statut'] == 1) {
        $_SESSION['saved_statut'] = $_SESSION['statut'];
        $_SESSION['statut'] = 5;
} elseif (isset($_SESSION['saved_statut'])) {
        $_SESSION['statut'] = $_SESSION['saved_statut'];
        unset($_SESSION['saved_statut']);
}
if (isset($_SESSION['dbname'])) {
	$_SESSION['status'][$_SESSION['dbname']] = $_SESSION['statut'];
        header("Location: {$urlServer}courses/$_SESSION[dbname]/");
} else {
        header('Location: ' . $urlServer);
}
