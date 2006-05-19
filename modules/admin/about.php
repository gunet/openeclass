<?php
$langFiles = array('admin','about');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = $langVersion;
// Initialise $tool_content
$tool_content = "";
	
$tool_content .= "<table width=\"99%\">
<tbody>
<tr valign=\"top\"><td><br>
<p align=center>".$langAboutText."</p>
<p align=center><b>".$langEclassVersion."</b></p>
<p align=center>".$langHostName."<b>".$SERVER_NAME."</b></p>	
<p align=center>".$langWebVersion."<b>".$SERVER_SOFTWARE."</b></p>";

if (extension_loaded('mysql')) 
	$tool_content .= "<p align=center>$langMySqlVersion<b>".mysql_get_server_info()."</b></p>";
else 
	$tool_content .= "<p align=center font color=\"red\">".$langNoMysql."</p>";

$tool_content .="
<br></tbody></td></tr></table>";

$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

draw($tool_content,3,'admin');
?>