<?php

require_once 'test.php';

global $webDir;

$content_id = $_GET['id'];

if (show_content($content_id)) {
    header("location: show.php?id=" . urlencode($content_id));
}