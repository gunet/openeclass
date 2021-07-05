<?php

require_once 'test.php';

$content_id = $_GET['id'];

echo "This is delete.php<br>";

if (delete_content($content_id)) {
    header("location: index.php");
}