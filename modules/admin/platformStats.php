<?php
/* ========================================================================
 * Open eClass 2.9
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/*
===========================================================================
    @file platformStats.php    
    @authors list: ophelia neofytou
    @Description:  Shows statistics conserning the number of visits on the platform in a time period.
        Statistics can be shown for a specific user or for all users.

==============================================================================
*/

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;

include '../../include/baseTheme.php';
$nameTools = $langVisitsStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$page_title = $langPlatformStats.": ".$langVisitsStats;

$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='stateclass.php'>".$langPlatformGenStats."</a></li>
      <li><a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a></li>
      <li><a href='oldStats.php' onClick='return confirmation(\"$langOldStatsExpireConfirm\");'>".$langOldStats."</a></li>
      <li><a href='monthlyReport.php'>".$langMonthlyReport."</a></li>
    </ul>
  </div>";

// jscalendar is used in order to select the time period for the statistics
include('../../include/jscalendar/calendar.php');

$lang = langname_to_code($language);

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

$local_head .= "<script type='text/javascript' src='$urlAppend/js/tools.js'></script>\n";
draw($tool_content, 3, null, $local_head);
