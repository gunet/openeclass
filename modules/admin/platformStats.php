<?php

/* ========================================================================
 * Open eClass 3.0
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

/**
 * @file platformStats.php
 * @description:  Shows statistics conserning the number of visits on the platform in a time period.
                  Statistics can be shown for a specific user or for all users.
 */

$require_admin = TRUE;

require_once '../../include/baseTheme.php';
$nameTools = $langVisitsStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$page_title = $langPlatformStats . ": " . $langVisitsStats;

load_js('tools.js');
load_js('jquery');
load_js('jquery-ui');
load_js('jquery-ui-timepicker-addon.min.js');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
<script type='text/javascript'>
$(function() {
$('input[name=u_date_start]').datetimepicker({
    dateFormat: 'yy-mm-dd', 
    timeFormat: 'hh:mm'
    });
});

$(function() {
$('input[name=u_date_end]').datetimepicker({
    dateFormat: 'yy-mm-dd', 
    timeFormat: 'hh:mm'
    });
});
</script>";

$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='stateclass.php'>" . $langPlatformGenStats . "</a></li>
      <li><a href='visitsCourseStats.php?first='>" . $langVisitsCourseStats . "</a></li>
      <li><a href='oldStats.php' onClick='return confirmation(\"$langOldStatsExpireConfirm\");'>" . $langOldStats . "</a></li>
      <li><a href='monthlyReport.php'>" . $langMonthlyReport . "</a></li>
    </ul>
  </div>";

//show chart with statistics
require_once "modules/admin/statsResults.php";
//show form for determining time period and user
require_once "modules/admin/statsForm.php";

draw($tool_content, 3, null, $head_content);
