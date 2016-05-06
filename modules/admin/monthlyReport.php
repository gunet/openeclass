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


$data['option_date'] = new Datetime();

if (isset($_POST["selectedMonth"])) {
    $month = q($_POST["selectedMonth"]);
    list($m, $data['y']) = explode(' ', $month);  //only month
    
    $data['monthly_data'] = Database::get()->querySingle("SELECT profesNum, studNum, visitorsNum, coursNum, logins, details
                       FROM monthly_summary WHERE `month` = ?s", $month);

    if (isset($localize) and $localize == 'greek') {
        $data['msg_of_month'] = substr($langMonths[$m], 0, -1);
    } else {
        $data['msg_of_month'] = $langMonths[$m];
    }


}
load_js('tools.js');
$data['menuTypeID'] = 3;
view('admin.other.stats.monthlyReport', $data);
//draw($tool_content, 3, 'admin', $head_content);
