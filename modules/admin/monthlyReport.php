<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
  admin/monthlyReport.php
  @last update: 23-09-2006
  @authors list: ophelia neofytou
  ==============================================================================
  @Description: Shows a form in order for the user to choose a month and display
  a report regarding this month. The report is based on information stored in table
  'monthly_summary' in database.
  ==============================================================================
 */

$require_admin = true;
require_once '../../include/baseTheme.php';

$nameTools = $langMonthlyReport;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='stateclass.php'>$langPlatformGenStats</a></li>
      <li><a href='platformStats.php?first='>$langVisitsStats</a></li>
      <li><a href='visitsCourseStats.php?first='>$langVisitsCourseStats</a></li>
      <li><a href='oldStats.php' onClick='return confirmation(\"$langOldStatsExpireConfirm\");'>" . $langOldStats . "</a></li>
    </ul>
  </div>";

$months = "";
for ($i = 0; $i < 12; $i++) {
    $mon = mktime(0, 0, 0, date('m') - $i - 1, date('d'), date('Y'));
    $mval = date('m Y', $mon);
    $months .= "<option value='$mval'>" . $langMonths[date('m', $mon)] . date(' Y', $mon);
}

$tool_content .= '
<form method="post">
<div><select name="selectedMonth">' . $months . '</select>
<input type="submit" name="btnUsage" value="' . $langSubmit . '">
</div>
</form>';

if (isset($_POST["selectedMonth"])) {
    $month = q($_POST["selectedMonth"]);
    list($m, $y) = explode(' ', $month);  //only month

    $coursNum = '';
    $row = Database::get()->querySingle("SELECT profesNum, studNum, visitorsNum, coursNum, logins, details
                       FROM monthly_summary WHERE `month` = ?s", $month);
    if ($row) {
        $row = (array) $row;
        $profesNum = $row['profesNum'];
        $studNum = $row['studNum'];
        $visitorsNum = $row['visitorsNum'];
        $coursNum = $row['coursNum'];
        $logins = $row['logins'];
        $details = $row['details'];
    }

    if (isset($localize) and $localize == 'greek') {
        $msg_of_month = substr($langMonths[$m], 0, -1);
    } else {
        $msg_of_month = $langMonths[$m];
    }

    if ($coursNum) {
        $tool_content .= '
		<table class="FormData" width="99%" align="left">
		<tbody>
		<tr>
		<td class="left">&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
		<tr>
		<th width="220"  class="left">&nbsp;</th>
		<td>' . $langReport . ': ' . $msg_of_month . ' ' . $y . '</td>
		</tr>
		<tr>
		<th class="left">' . $langNbProf . ': </th>
		<td>' . $profesNum . '</td>
		</tr>
		<tr>
		<th class="left">' . $langNbStudents . ': </th>
		<td>' . $studNum . '</td>
		</tr>
		<tr>
		<th class="left">' . $langNbVisitors . ': </th>
		<td>' . $visitorsNum . '</td>
		</tr>
		<tr>
		<th class="left">' . $langNbCourses . ':  </th>
		<td>' . $coursNum . '</td>
		</tr>
		<tr>
		<th class="left">' . $langNbLogin . ': </th>
		<td>' . $logins . '</td>
		</tr>
		<tr>
		<td colspan="2">' . $details . '</td>
		</tr>
		</tbody>
		</table>';           // $details includes an html table with all details
    } else {
        $tool_content .= '<div class="alert1">' . $langNoReport . ': ' . $msg_of_month . ' ' . $y . '</div>';
    }
}
load_js('tools.js');
draw($tool_content, 3, 'admin', $head_content);
