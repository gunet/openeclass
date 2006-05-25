<?php

/*
Header, Copyright, etc ...
*/

$require_current_course = TRUE;
$langFiles              = "learnPath";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";

// the following constant defines the default display of the learning path browser
// 0 : display eclass header and footer and table of content, and content
// 1 : display only table of content and content
define ( 'FULL_SCREEN' , 0 );

$nameTools = $langLearningPath;
if (!isset($titlePage)) $titlePage = '';
if(!empty($nameTools))
{
    $titlePage .= $nameTools.' - ';
}

if(!empty($intitule))
{
    $titlePage .= $intitule . ' - ';
}
$titlePage .= $siteName;

if ( !isset($_GET['fullscreen']) )
{
    // choose default display
    // default display is without fullscreen
    $displayFull = FULL_SCREEN;
}
else
{
    $displayFull = $_REQUEST['fullscreen'];
}

if ( $displayFull == 0	) 
{
	$tool_content .= "<iframe src=\"navigation/startModule.php\" name=\"mainFrame\" "
		."width=\"99%\" height=\"550\" scrolling=\"no\" frameborder=\"0\">"
		.$langBrowserCannotSeeFrames
		."<br />"
		."<a href=\"module.php\">".$langBack."</a>"
		."</iframe>";

	draw($tool_content, 2, "learnPath", $head_content);
}
else
{
echo
 "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\""
."   \"http://www.w3.org/TR/html4/frameset.dtd\">"
."<html>"
."<head>"
."<title>".$titlePage."</title>"
."</head>"
."<frameset cols=\"*\" border=\"0\">"
."<frame src=\"navigation/startModule.php\" name=\"mainFrame\" />"
."</frameset>"
."<noframes>"
."<body>"
.$langBrowserCannotSeeFrames
."<br />"
."<a href=\"module.php\">".$langBack."</a>"
."</body>"
."</noframes>"
."</html>";

}

?>