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
 * @file oldStats.php
 * @description Shows statistics older than two months that concern the number of visits
  on the platform for a time period. Information for old statistics is taken from table 'loginout_summary' where
  cummulative monthly data are stored.
 * 
 */
$require_admin = TRUE;

require_once '../../include/baseTheme.php';

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

$nameTools = $langOldStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='stateclass.php'>" . $langPlatformGenStats . "</a></li>
      <li><a href='platformStats.php?first='>" . $langVisitsStats . "</a></li>
      <li><a href='visitsCourseStats.php?first='>" . $langVisitsCourseStats . "</a></li>
      <li><a href='monthlyReport.php'>" . $langMonthlyReport . "</a></li>
    </ul>
  </div>";

//$min_w is the min date in 'loginout'. Statistics older than $min_w will be shown.
$query = "SELECT MIN(`when`) as min_when FROM loginout";
foreach (Database::get()->queryArray($query) as $row) {
    $min_when = strtotime($row->min_when);
}
$min_w = date("d-m-Y", $min_when);

$tool_content .= '<div class="info">' . sprintf($langOldStatsLoginsExpl, get_config('actions_expire_interval')) . '</div>';

/* * ***************************************
  start making chart
 * ***************************************** */
require_once 'modules/graphics/plotter.php';

//default values for chart
$usage_defaults = array(
    'u_date_start' => strftime('%Y-%m-%d', strtotime('now -4 month')),
    'u_date_end' => strftime('%Y-%m-%d', strtotime('now -1 month')),
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = q($_POST[$key]);
    }
}

$date_fmt = '%Y-%m-%d';
$u_date_start = mysql_real_escape_string($u_date_start);
$u_date_end = mysql_real_escape_string($u_date_end);
$date_where = " (start_date BETWEEN '$u_date_start' AND '$u_date_end') ";
$query = "SELECT MONTH(start_date) AS month, YEAR(start_date) AS year, SUM(login_sum) AS visits
                        FROM loginout_summary
                        WHERE $date_where
                        GROUP BY MONTH(start_date)";

$result = Database::get()->queryArray($query);

if (count($result) > 0) {
    $chart = new Plotter();
    $chart->setTitle($langOldStats);

    //add points to chart
    foreach ($result as $row) {
        $mont = $langMonths[$row->month];
        $chart->growWithPoint($mont . " - " . $row->year, $row->visits);
    }
    $tool_content .= "<p>" . $langVisits . "</p>" . $chart->plot();
} else {
    $tool_content .= "<div class='alert1'>$langNoStatistics</div>";
}
$tool_content .= '<br />';

$tool_content .= '<form method="post">
    <table width="100%" class="tbl">
    <tr>
      <th width="150" class="left">' . $langStartDate . ':</th>
      <td><input type="text" name="u_date_start" value = "' . $u_date_start . '"></td>      
    </tr>
    <tr>
      <th class="left">' . $langEndDate . ':</th>
      <td><input type="text" name="u_date_end" value = "' . $u_date_end . '"></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td class="right"><input type="submit" name="btnUsage" value="' . $langSubmit . '"></td>
    </tr>
    </table>
    </form>';

draw($tool_content, 3, null, $head_content);
