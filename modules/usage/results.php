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
 * Created on 1 June 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once 'modules/graphics/plotter.php';

$usage_defaults = array (
    'u_stats_value' => 'visits',
    'u_interval' => 'daily',
    'u_module_id' => -1,
    'u_date_start' => strftime('%Y-%m-%d', strtotime('now -15 day')),
    'u_date_end' => strftime('%Y-%m-%d', strtotime('now')),
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = $_POST[$key];
    }
}

if ($u_module_id != -1) {
    $mod_where = " (module_id = '$u_module_id') ";
} else {
    $mod_where = " (1) ";
}


$date_fmt = '%d-%m-%Y';
$date_where = "(`day` BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";
$date_what  = "";

switch ($u_interval) {
    case "summary":
        $date_group = ' ';
        $date_what = ' ';
    break;
    case "daily":
        $date_what .= " DATE_FORMAT(`day`, '$date_fmt') AS `date`, ";
        $date_group = " GROUP BY `day` ";
    break;
    case "weekly":
        $date_what .= " DATE_FORMAT(`day` - INTERVAL WEEKDAY(`day`) DAY, '$date_fmt') AS week_start ".
                      ", DATE_FORMAT(`day` + INTERVAL (6 - WEEKDAY(`day`)) DAY, '$date_fmt') AS week_end, ";
        $date_group = " GROUP BY WEEK(`day`) ";
    break;
    case "monthly":
        $date_what .= " MONTH(`day`) AS `month`, ";
        $date_group = " GROUP BY MONTH(`day`) ";
    break;
    case "yearly":
        $date_what .= " YEAR(`day`) AS `year`, ";
        $date_group = " GROUP BY YEAR(`day`) ";
    break;
}


$chart = new Plotter();
$chart->setDimension(300, 300);

switch ($u_stats_value) {
    case "visits":
        $query = "SELECT " . $date_what . " SUM(hits) AS cnt FROM actions_daily
                         WHERE $date_where
                         AND $mod_where
                         AND course_id = $course_id
                        $date_group ORDER BY `day` ASC";

        $result = db_query($query);

        switch ($u_interval) {
            case "summary":
                while ($row = mysql_fetch_assoc($result)) {
                    $chart->growWithPoint($langSummary, $row['cnt']);
                }
                break;
            case "daily":
                while ($row = mysql_fetch_assoc($result)) {
                    $chart->growWithPoint($row['date'], $row['cnt']);
                }
                break;
            case "weekly":
                while ($row = mysql_fetch_assoc($result)) {
                    $chart->growWithPoint($row['week_start'] . ' - ' . $row['week_end'], $row['cnt']);
                }
                break;
            case "monthly":
                while ($row = mysql_fetch_assoc($result)) {
                    $chart->growWithPoint($langMonths[$row['month']], $row['cnt']);
                }
                break;
            case "yearly":
                while ($row = mysql_fetch_assoc($result)) {
                    $chart->growWithPoint($row['year'], $row['cnt']);
                }
                break;
        }
        $chart->setTitle("$langVisits");

        break;
    case "duration":

        $query = "SELECT " . $date_what . " SUM(duration) AS tot_dur
                FROM actions_daily
                WHERE $date_where
                AND $mod_where
                AND course_id = $course_id
                $date_group ORDER BY day ASC";

        $result = db_query($query);
        switch ($u_interval) {
            case "summary":
                while ($row = mysql_fetch_assoc($result)) {
                    $row['tot_dur'] = round($row['tot_dur'] / 60);
                    $chart->growWithPoint($langSummary, $row['tot_dur']);
                }
                break;
            case "daily":
                while ($row = mysql_fetch_assoc($result)) {
                    $row['tot_dur'] = round($row['tot_dur'] / 60);
                    $chart->growWithPoint($row['date'], $row['tot_dur']);
                }
                break;
            case "weekly":
                while ($row = mysql_fetch_assoc($result)) {
                    $row['tot_dur'] = round($row['tot_dur'] / 60);
                    $chart->growWithPoint($row['week_start'] . ' - ' . $row['week_end'], $row['tot_dur']);
                }
                break;
            case "monthly":
                while ($row = mysql_fetch_assoc($result)) {
                    $row['tot_dur'] = round($row['tot_dur'] / 60);
                    $chart->growWithPoint($langMonths[$row['month']], $row['tot_dur']);
                }
                break;
            case "yearly":
                while ($row = mysql_fetch_assoc($result)) {
                    $row['tot_dur'] = round($row['tot_dur'] / 60);
                    $chart->growWithPoint($row['year'], $row['tot_dur']);
                }
                break;
        }

        $chart->setTitle("$langDurationVisits");
        $tool_content .= "<p>$langDurationExpl</p>";

        break;
}
mysql_free_result($result);



$errorMsg = '<p class="alert1">' . $langNoStatistics . '</p>';
$tool_content .= $chart->plot($errorMsg);
$tool_content .= $chart->plotJS($errorMsg);