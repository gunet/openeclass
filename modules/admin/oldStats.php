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
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#u_date_start, #u_date_end').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-left',
                language: '".$language."',
                autoclose: true    
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
$query = "SELECT MIN(`when`) AS min_when FROM loginout";
foreach (Database::get()->queryArray($query) as $row) {
    $min_when = strtotime($row->min_when);
}
$min_w = date("d-m-Y", $min_when);

$tool_content .= '<div class="info">' . sprintf($langOldStatsLoginsExpl, get_config('actions_expire_interval')) . '</div>';

/* * ***************************************
  start making chart
 * ***************************************** */
require_once 'modules/graphics/plotter.php';

if (isset($_POST['u_date_start'])) {
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_POST['u_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P4M'));
    $u_date_start = $date_start->format('d-m-Y H:i');
}
if (isset($_POST['u_date_end'])) {
    $ude = DateTime::createFromFormat('d-m-Y H:i', $_POST['u_date_end']);
    $u_date_end = $ude->format('Y-m-d H:i');
} else {
    $date_end = new DateTime();
    $date_start->sub(new DateInterval('P1M'));
    $u_date_end = $date_end->format('d-m-Y H:i');
}


$date_fmt = '%Y-%m-%d';
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
        $chart->addPoint($mont . " - " . $row->year, $row->visits);
    }
    $tool_content .= "<p>" . $langVisits . "</p>" . $chart->plot();
} else {
    $tool_content .= "<div class='alert alert-warning'>$langNoStatistics</div>";
}
$tool_content .= '<br />';

$tool_content .= '<form method="post">
    <table width="100%" class="tbl">
    <tr>
      <th width="150" class="left">' . $langStartDate . ':</th>
      <td>';
$tool_content .= "<div class='input-append date form-group' id='u_date_start' data-date = '" . q($u_date_start) . "'>
                <div class='col-xs-11'>        
                    <input name='u_date_start' type='text' value = '" . q($u_date_start) . "'>
                </div>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
            </div>";      
$tool_content .= '</td>
    </tr>
    <tr>
      <th class="left">' . $langEndDate . ':</th>
      <td>';

$tool_content .= "<div class='input-append date form-group' id='u_date_end' data-date= '" . q($u_date_end) . "'>
            <div class='col-xs-11'>
                <input name='u_date_end' type='text' value= '" . q($u_date_end) . "'>
            </div>
        <span class='add-on'><i class='fa fa-times'></i></span>
        <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>";
              
$tool_content .= '</td>
</tr>
<tr>
  <td>&nbsp;</td>
  <td class="right"><input type="submit" name="btnUsage" value="' . $langSubmit . '"></td>
</tr>
</table>
</form>';

draw($tool_content, 3, null, $head_content);
