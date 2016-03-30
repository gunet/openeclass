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
 * @file monltyReport.php
 * @brief Shows a form in order for the user to choose a month and display
  a report regarding this month. The report is based on information stored in table
  'monthly_summary' in database.
 */


$require_admin = true;
require_once '../../include/baseTheme.php';

$toolName = $langMonthlyReport;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array("url" => "../usage/index.php?t=a", "name" => $langUsage);

$tool_content .= action_bar(array(                
                array('title' => $langBack,
                    'url' => "../usage/index.php?t=a",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));

$months = "";
for ($i = 0; $i < 12; $i++) {
    $mon = mktime(0, 0, 0, date('m') - $i - 1, date('d'), date('Y'));
    $mval = date('m Y', $mon);
    $months .= "<option value='$mval'>" . $langMonths[date('m', $mon)] . date(' Y', $mon);
}

$tool_content .= '<div class="form-wrapper">
    <form class="form-horizontal" role="form" method="post">
    <div class="form-group"><div class="col-sm-10"><select name="selectedMonth" class="form-control">' . $months . '</select></div>
        <input class="btn btn-primary" type="submit" name="btnUsage" value="' . $langSubmit . '">
    </div>
    </form>
    </div>';

if (isset($_POST["selectedMonth"])) {
    $month = q($_POST["selectedMonth"]);
    list($m, $y) = explode(' ', $month);  //only month

    $coursNum = '';
    $row = Database::get()->querySingle("SELECT profesNum, studNum, visitorsNum, coursNum, logins, details
                       FROM monthly_summary WHERE `month` = ?s", $month);
    if ($row) {
        $profesNum = $row->profesNum;
        $studNum = $row->studNum;
        $visitorsNum = $row->visitorsNum;
        $coursNum = $row->coursNum;
        $logins = $row->logins;
        $details = $row->details;
    }

    if (isset($localize) and $localize == 'greek') {
        $msg_of_month = substr($langMonths[$m], 0, -1);
    } else {
        $msg_of_month = $langMonths[$m];
    }

    if ($coursNum) {
        $tool_content .= '
		<table class="table-default">
		<tbody>		
		<tr>
		<th colspan="2" class="text-center">' . $langReport . ': ' . $msg_of_month . ' ' . $y . '</th>
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
        $tool_content .= '<div class="alert alert-warning">' . $langNoReport . ': ' . $msg_of_month . ' ' . $y . '</div>';
    }
}
load_js('tools.js');
draw($tool_content, 3, 'admin', $head_content);
