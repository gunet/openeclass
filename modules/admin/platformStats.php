<?php
/*****************************************************************************
        DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = array('usage', 'admin');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
@include "check_admin.inc";
// Define $nameTools
$nameTools = $langPlatformStats;
// Initialise $tool_content
$tool_content = "";

$tool_content .= "<a href=".$_SERVER['PHP_SELF'].">".$langPlatformStats."</a> | ".
             "<a href='usersCourseStats.php'>".$langUsersCourse."</a> | ".
             "<a href='visitsCourseStats.php'>".$langVisitsCourseStats."</a> | ".
             "<a href='oldStats.php'>".$langOldStats."</a>".
          "<p>&nbsp</p>";


$dateNow = date("d-m-Y / H:i:s",time());

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

include('../../include/jscalendar/calendar.php');
if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-win2k-2', false);
$local_head = $jscalendar->get_load_files_code();

    if (!extension_loaded('gd')) {
        $tool_content .= "<p>$langGDRequired</p>";
    } else {
        $made_chart = true;
        
        require_once "statsResults.php";
        require_once "statsForm.php";

    }


draw($tool_content, 3, 'admin', $local_head, '');

if ($made_chart) {


    ob_end_flush();
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}



?>
