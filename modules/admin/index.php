<?php
$langFiles = array('gunet','admin');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = $langAdmin;

// Initialise $tool_content
$tool_content = "";
// Main body


draw($tool_content,3,'admin');
?>