<?php

/*
===========================================================================
    admin/platformStats.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description:  Shows statistics conserning the number of visits on the platform in a time period.
        Statistics can be shown for a specific user or for all users.

==============================================================================
*/



// Set the langfiles needed
$langFiles = array('usage', 'admin');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
@include "check_admin.inc";
// Define $nameTools
$nameTools = $langVisitsStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$page_title = $langPlatformStats.": ".$langVisitsStats;

// Initialise $tool_content
$tool_content = "";

$tool_content .=  "<a href='statClaro.php'>".$langPlatformGenStats."</a> <br> ".
                "<a href='platformStats.php?first='>".$langVisitsStats."</a> <br> ".
             "<a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a> <br> ".
              "<a href='oldStats.php'>".$langOldStats."</a> <br> ".
               "<a href='monthlyReport.php'>".$langMonthlyReport."</a>".
          "<p>&nbsp</p>";




// jscalendar is used in order to select the time period for the statistics
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
        
        //show chart with statistics
        require_once "statsResults.php";
        
        //show form for determining time period and user
        require_once "statsForm.php";

    }


draw($tool_content, 3, 'admin', $local_head, '');

/*if ($made_chart) {
		while (ob_get_level() > 0) {
     ob_end_flush();
  	} 
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}
*/


?>
