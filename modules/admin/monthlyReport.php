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

$data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                    'url' => "../usage/index.php?t=a",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));

$months = "";
for ($i = 0; $i < 12; $i++) {
    $mon = mktime(0, 0, 0, date('m') - $i - 1, date('d'), date('Y'));
    $mval = date('m Y', $mon);
    if (isset($_POST['selectedMonth']) and $_POST['selectedMonth'] == $mval) {
        $selected = ' selected';
    } else {
        $selected = '';
    }
    $months .= "<option value='$mval' $selected>" . $langMonths[date('m', $mon)] . date(' Y', $mon);
}

$data['months'] = $months;


if (isset($_POST["selectedMonth"])) {
    $month = q($_POST["selectedMonth"]);
    list($m, $y) = explode(' ', $month);  //only month
    $data['y'] = $y;
    $data['coursNum'] = $coursNum = '';
    $data['diff_profesNum'] = $data['diff_logins'] = $data['diff_studNum'] = $data['diff_visitorsNum'] = $data['diff_coursNum'] = 0;
    $row = Database::get()->querySingle("SELECT profesNum, studNum, visitorsNum, coursNum, logins, details
                       FROM monthly_summary WHERE `month` = ?s", $month);
    if ($row) {
        $data['profesNum'] = $profesNum = $row->profesNum;
        $data['studNum'] = $studNum = $row->studNum;
        $data['visitorsNum'] = $visitorsNum = $row->visitorsNum;
        $data['coursNum'] = $coursNum = $row->coursNum;
        $data['logins'] = $logins = $row->logins;
        $data['details'] = $details = $row->details;
        // previous month
        $prev_month_date_format = date_create($y . "-" . $m);
        date_sub($prev_month_date_format, date_interval_create_from_date_string('1 month'));
        $prev_month = date_format($prev_month_date_format, 'm Y');
        $row_prev = Database::get()->querySingle("SELECT profesNum, studNum, visitorsNum, coursNum, logins, details
                       FROM monthly_summary WHERE `month` = ?s", $prev_month);
        if ($row_prev) {
            $prev_profesNum = $row_prev->profesNum;
            $prev_studNum = $row_prev->studNum;
            $prev_visitorsNum = $row_prev->visitorsNum;
            $prev_coursNum = $row_prev->coursNum;
            $prev_logins = $row_prev->logins;

            $data['diff_profesNum'] = $profesNum - $prev_profesNum;
            $data['diff_studNum'] = $studNum - $prev_studNum;
            $data['diff_visitorsNum'] = $visitorsNum - $prev_visitorsNum;
            $data['diff_coursNum'] = $coursNum - $prev_coursNum;
            $data['diff_logins'] = $logins - $prev_logins;
        }
    }

    if (isset($localize) and $localize == 'greek') {
        $data['msg_of_month'] = substr($langMonths[$m], 0, -1);
    } else {
        $data['msg_of_month'] = $langMonths[$m];
    }


}
load_js('tools.js');
$data['menuTypeID'] = 3;
view('admin.other.stats.monthlyReport', $data);
