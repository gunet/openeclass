<?php
$langFiles = 'admin';
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Πληροφορίες για την PHP";

// Initialise $tool_content
$tool_content = "";
// Main body

if (!isset($to)) $to = '';

if ($to=="phpinfo") {
	$tool_content .= '<div>';
	ob_start();
	phpinfo();
	$phpinfo = ob_get_contents();
	ob_end_clean();
	$tool_content .= $phpinfo;
	$tool_content .= '</div>';
}

$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

draw($tool_content,3,'admin');
?>