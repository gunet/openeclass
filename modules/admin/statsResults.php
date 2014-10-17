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


require_once 'modules/graphics/plotter.php';

if (isset($_POST['u_date_start'])) {
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_POST['u_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');        
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P30D'));       
    $u_date_start = $date_start->format('d-m-Y H:i');
}
if (isset($_POST['u_date_end'])) {
    $ude = DateTime::createFromFormat('d-m-Y H:i', $_POST['u_date_end']);
    $u_date_end = $ude->format('Y-m-d H:i');            
} else {
    $date_end = new DateTime();    
    $u_date_end = $date_end->format('d-m-Y H:i');
}

$usage_defaults = array(
    'u_stats_type' => 'visits',
    'u_interval' => 'daily',
    'u_user_id' => -1,    
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = q($_POST[$key]);
    }
}

#see if chart has content

$date_fmt = '%Y-%m-%d';
$date_where = " (`when` BETWEEN '$u_date_start' AND '$u_date_end') ";
$date_what = "DATE_FORMAT(MIN(`when`), '$date_fmt') AS date_start, DATE_FORMAT(MAX(`when`), '$date_fmt') AS date_end ";

switch ($u_interval) {
    case "summary":
        $date_what = '';
        $date_group = '';
        break;
    case "daily":
        $date_what .= ", DATE_FORMAT(`when`, '$date_fmt') AS date ,";
        $date_group = " GROUP BY DATE(`when`) ";
        break;
    case "weekly":
        $date_what .= ", DATE_FORMAT(`when` - INTERVAL WEEKDAY(`when`) DAY, '$date_fmt') AS week_start " .
                ", DATE_FORMAT(`when` + INTERVAL (6 - WEEKDAY(`when`)) DAY, '$date_fmt') AS week_end ,";
        $date_group = " GROUP BY WEEK(`when`)";
        break;
    case "monthly":
        $date_what .= ", MONTH(`when`) AS month ,";
        $date_group = " GROUP BY MONTH(`when`)";
        break;
    case "yearly":
        $date_what .= ", YEAR(`when`) AS year ,";
        $date_group = "  GROUP BY YEAR(`when`) ";
        break;
    default:
        $date_what = '';
        $date_group = '';
        break;
}
if ($u_user_id != -1) {
    $user_where = " (id_user = '$u_user_id') ";
} else {
    $user_where = " (1) ";
}


switch ($u_stats_type) {
    case "visits":
        $result = Database::get()->queryArray("SELECT " . $date_what . " COUNT(*) AS cnt FROM loginout WHERE $date_where AND $user_where AND action='LOGIN' $date_group ORDER BY `when` ASC");
        $chart = new Plotter();
        $chart->setTitle($langVisits);
        switch ($u_interval) {
            case "summary":
                foreach ($result as $row) {
                    $chart->addPoint($langSummary, $row->cnt);
                }
                break;
            case "daily":
                foreach ($result as $row) {
                    $chart->addPoint($row->date, $row->cnt);
                }
                break;
            case "weekly":
                foreach ($result as $row) {
                    $chart->addPoint($row->week_start . ' - ' . $row->week_end, $row->cnt);
                }
                break;
            case "monthly":
                foreach ($result as $row) {
                    $chart->addPoint($langMonths[$row->month], $row->cnt);
                }
                break;
            case "yearly":
                foreach ($result as $row) {
                    $chart->addPoint($row->year, $row->cnt);
                }
                break;
        }
        break;
}

$tool_content .= $chart->plot($langNoStatistics);
