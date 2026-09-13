<?php

if (isset($_GET['token'])) {
    $_COOKIE[session_name()] = $_GET['token'];
}
require_once '../../include/baseTheme.php';
session_regenerate_id(true);
redirect_to_home_page();
