<?php

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	viewer.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================        
    @Description:

 	@Comments:
 
  	@todo: 
==============================================================================
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
."   \"http://www.w3.org/TR/html4/frameset.dtd\">"."\n"
."<html>"."\n"
."<head>"."\n"
.'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">'."\n"
."<title>".$titlePage."</title>"."\n"
."</head>"."\n"
."<frameset cols=\"*\" border=\"0\">"."\n"
."<frame src=\"navigation/startModule.php\" name=\"mainFrame\" />"."\n"
."</frameset>"."\n"
."<noframes>"."\n"
."<body>"."\n"
.$langBrowserCannotSeeFrames
."<br />"."\n"
."<a href=\"module.php\">".$langBack."</a>"."\n"
."</body>"."\n"
."</noframes>"."\n"
."</html>"."\n";

}

?>