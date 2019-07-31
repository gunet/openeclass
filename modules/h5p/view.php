<?php

require_once 'test.php';

$content_id = $_GET['id'];

if(show_content($content_id)){
	header("location: show.php");
}
?>