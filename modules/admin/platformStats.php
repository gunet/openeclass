<?php
/*****************************************************************************
        DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = 'admin';
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
@include "check_admin.inc";
// Define $nameTools
$nameTools = $langPlatformStats;
// Initialise $tool_content
$tool_content = "";
// TODO: fill this.

$tool_content .= "Under Construction (haniotak)";

draw($tool_content,3,'admin');

?>
