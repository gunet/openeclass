<?
$langFiles = 'admin';

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;

include '../../include/baseTheme.php';

$nameTools = $langCleanUp;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);

// Initialise $tool_content
$tool_content = "";

$tool_content .= "<table width=99% border='0' height=316 cellspacing='0' align=center cellpadding='0'>\n";
$tool_content .= "<tr>\n";
$tool_content .= "<td valign=top>\n";
$tool_content .= "<table width='96%' align='center' class='admin'>
							   <tr><td valign=top><br>";

if (isset($_POST['submit'])) {
	foreach (array('temp' => 2, 'garbage' => 5, 'archive' => 1, 'tmpUnzipping' => 1) as $dir => $days) {
		$tool_content .= sprintf("<p class=kk>$langCleaningUp</p>", $days,
			($days == 1)? $langDaySing: $langDayPlur, $dir);
		cleanup("${webDir}courses/$dir", $days);
	}
} else {
	$tool_content .= "<p class=kk>$langCleanupInfo,</p><br><div align=center>";
	$tool_content .= "<form method='post' action='cleanup.php'>
			<input type='submit' name='submit' value='$langCleanup'></div></form>";
}

$tool_content .= "</td></tr><tr><td align=right>";
$tool_content .= "<a href=\"index.php\" class=mainpage>$langBackAdmin&nbsp;</a>";
$tool_content .= "</td></tr></table></td></tr></table>";


/*****************************************************************************
                DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');


// Remove all files under $path older than $max_age days
// Afterwards, remove $path as well if it points to an empty directory
function cleanup($path, $max_age)
{
	$max_age_seconds = $max_age * 60 * 60 * 24;
	$files_left = 0;
	if ($dh = opendir($path)) {
		while (($file = readdir($dh)) !== false) {
			if ($file != '.' and $file != '..') {
				$filepath = "$path/$file";
				if (is_dir($filepath)) {
					if (cleanup($filepath, $max_age) == 0) {
						rmdir($filepath);
					} else {
						$files_left++;
					}
				} else {
					if (file_older($filepath, $max_age_seconds)) {
						unlink($filepath);
					} else {
						$files_left++;
					}
	        		}
			}
		}
		closedir($dh);
	}
	return $files_left;
}

// Returns true if file pointed to by $path is older than $seconds
function file_older($path, $seconds)
{
	if (filemtime($path) > time() - $seconds) {
		return false;
	} else {
		return true;
	}
}

?>
