<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

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

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langVisitsStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$page_title = $langPlatformStats.": ".$langVisitsStats;

// Initialise $tool_content
$tool_content = "";
$tool_content .= "
  <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li><a href='stateclass.php'>".$langPlatformGenStats."</a></li>
      <li><a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a></li>
      <li><a href='oldStats.php'>".$langOldStats."</a></li>
      <li><a href='monthlyReport.php'>".$langMonthlyReport."</a></li>
    </ul>
  </div>";

// jscalendar is used in order to select the time period for the statistics
include('../../include/jscalendar/calendar.php');
if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
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
?>
