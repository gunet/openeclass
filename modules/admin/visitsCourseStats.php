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
 * @file visitsCourseStats.php
 * @description Shows statistics conserning the number of visits on courses for a time period.
  Statistics can be shown for a specific course or for all courses.
 */
$require_admin = true;

require_once '../../include/baseTheme.php';

load_js('tools.js');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_start, #user_date_end').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-left',
                language: '".$language."',
                autoclose: true    
            });            
        });
    </script>";

$toolName = $langVisitsCourseStats;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

require_once 'admin_statistics_tools_bar.php';
admin_statistics_tools("visitsCourseStats");

/* * ******************************************
  start making the chart
 * ******************************************* */
require_once 'modules/graphics/plotter.php';

if (isset($_POST['user_date_start'])) {
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_POST['user_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');
    $user_date_start = $uds->format('d-m-Y H:i');
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P30D'));    
    $u_date_start = $date_start->format('Y-m-d H:i');
    $user_date_start = $date_start->format('d-m-Y H:i');       
}
if (isset($_POST['user_date_end'])) {
    $ude = DateTime::createFromFormat('d-m-Y H:i', $_POST['user_date_end']);    
    $u_date_end = $ude->format('Y-m-d H:i');
    $user_date_end = $ude->format('d-m-Y H:i');        
} else {
    $date_end = new DateTime();
    $u_date_end = $date_end->format('Y-m-d H:i');
    $user_date_end = $date_end->format('d-m-Y H:i');        
}

//default values for chart
$usage_defaults = array(
    'u_interval' => 'daily',
    'u_course_id' => -1,    
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = q($_POST[$key]);
    }
}

$date_fmt = '%Y-%m-%d';
$date_where = "(day BETWEEN '$u_date_start' AND '$u_date_end') ";
$date_what = "";


#see if chart has content
$chart_content = 0;

switch ($u_interval) {
    case "summary":
        $date_what = '';
        $date_group = '';
        break;
    case "daily":
        $date_what .= " DATE_FORMAT(day, '$date_fmt') AS date, ";
        $date_group = " GROUP BY day ";
        break;
    case "weekly":
        $date_what .= " DATE_FORMAT(day - INTERVAL WEEKDAY(day) DAY, '$date_fmt') AS week_start " .
                ", DATE_FORMAT(day + INTERVAL (6 - WEEKDAY(day)) DAY, '$date_fmt') AS week_end, ";
        $date_group = " GROUP BY WEEK(day) ";
        break;
    case "monthly":
        $date_what .= " MONTH(day) AS month, ";
        $date_group = " GROUP BY MONTH(day) ";
        break;
    case "yearly":
        $date_what .= " YEAR(day) AS year, ";
        $date_group = " GROUP BY YEAR(day) ";
        break;
    default:
        $date_what = '';
        $date_group = '';
        break;
}


if ($u_course_id == -1) {
    //show chart for all courses
    $res1 = Database::get()->queryArray("SELECT id FROM course");
    $point = array();
    foreach ($res1 as $row1) {
        $cid = $row1->id;

        $result = Database::get()->queryArray("SELECT " . $date_what . " SUM(hits) AS cnt FROM actions_daily
                        WHERE course_id = $cid AND $date_where $date_group
                        ORDER BY day ASC");

        switch ($u_interval) {
            case "summary":
                foreach ($result as $row) {
                    if (array_key_exists($langSummary, $point)) {
                        $point[$langSummary] += $row->cnt;
                    } else {
                        $point[$langSummary] = $row->cnt;
                    }
                }
                break;
            case "daily":
                foreach ($result as $row) {
                    if (array_key_exists($row->date, $point)) {
                        $point[$row->date] += $row->cnt;
                    } else {
                        $point[$row->date] = $row->cnt;
                    }
                }
                break;
            case "weekly":
                foreach ($result as $row) {
                    $week = $row->week_start . ' - ' . $row->week_end;
                    if (array_key_exists($week, $point)) {
                        $point[$week] += $row->cnt;
                    } else {
                        $point[$week] = $row->cnt;
                    }
                }
                break;
            case "monthly":
                foreach ($result as $row) {
                    $month = $langMonths[$row->month];
                    if (array_key_exists($month, $point)) {
                        $point[$month] += $row->cnt;
                    } else {
                        $point[$month] = $row->cnt;
                    }
                }
                break;
            case "yearly":
                foreach ($result as $row) {
                    $year = $row->year;
                    if (array_key_exists($year, $point)) {
                        $point[$year] += $row->cnt;
                    } else {
                        $point[$year] = $row->cnt;
                    }
                }
                break;
        }
    }

    if ($u_interval != "monthly") {
        ksort($point);
    }

    $chart = new Plotter();
    $chart->setTitle($langVisits);
    //add points to chart
    while ($newp = current($point)) {
        $chart->growWithPoint(key($point), $newp);
        next($point);
    }
} else {    //show chart for a specific course
    $cid = course_code_to_id($u_course_id);

    $result = Database::get()->queryArray("SELECT " . $date_what . " SUM(hits) AS cnt FROM actions_daily
                WHERE course_id = $cid AND $date_where $date_group ORDER BY day ASC");

    $chart = new Plotter();
    $chart->setTitle($langVisits);

    switch ($u_interval) {
        case "summary":
            foreach ($result as $$row) {
                $chart->growWithPoint($langSummary, $row->cnt);
            }
            break;
        case "daily":
            foreach ($result as $$row) {
                $chart->growWithPoint($row->date, $row->cnt);
            }
            break;
        case "weekly":
            foreach ($result as $$row) {
                $chart->growWithPoint($row->week_start . ' - ' . $row->week_end, $row->cnt);
            }
            break;
        case "monthly":
            foreach ($result as $$row) {
                $chart->growWithPoint($langMonths[$row->month], $row->cnt);
            }
            break;
        case "yearly":
            foreach ($result as $$row) {
                $chart->growWithPoint($row->year, $row->cnt);
            }
            break;
    }
}

$tool_content .= $chart->plot($langNoStatistics);

/* * ***********************************************************************
  Form for determining time period, time interval and course
 * ************************************************************************* */

//possible courses
$letterlinks = '';
Database::get()->queryFunc("SELECT LEFT(title, 1) AS first_letter FROM course GROUP BY first_letter ORDER BY first_letter"
        , function ($row) use(&$letterlinks) {
    $first_letter = $row->first_letter;
    $letterlinks .= '<a href="?first=' . $first_letter . '">' . $first_letter . '</a> ';
});

if (isset($_GET['first'])) {
    $firstletter = $_GET['first'];
    $qry = "SELECT code, title FROM course
                           WHERE LEFT(title,1) = '" . q($firstletter) . "'";
} else {
    $qry = "SELECT code, title FROM course";
}

$cours_opts = '<option value="-1">' . $langAllCourses . "</option>";
$result = Database::get()->queryArray($qry);
foreach ($result as $row) {
    if ($u_course_id == $row->code) {
        $selected = 'selected';
    } else {
        $selected = '';
    }
    $cours_opts .= '<option ' . $selected . ' value="' . $row->code . '">' . $row->title . "</option>\n";
}

//possible time intervals
$statsIntervalOptions = '<option value="daily"   ' . (($u_interval == 'daily') ? ('selected') : ('')) . ' >' . $langDaily . "</option>\n" .
        '<option value="weekly"  ' . (($u_interval == 'weekly') ? ('selected') : ('')) . '>' . $langWeekly . "</option>\n" .
        '<option value="monthly" ' . (($u_interval == 'monthly') ? ('selected') : ('')) . '>' . $langMonthly . "</option>\n" .
        '<option value="yearly"  ' . (($u_interval == 'yearly') ? ('selected') : ('')) . '>' . $langYearly . "</option>\n" .
        '<option value="summary" ' . (($u_interval == 'summary') ? ('selected') : ('')) . '>' . $langSummary . "</option>\n";

//form
$tool_content .= '<div class="form-wrapper"><form class="form-horizontal" role="form" method="post">';
$tool_content .= "<div class='input-append date form-group' id='user_date_start' data-date = '" . q($user_date_start) . "' data-date-format='dd-mm-yyyy'>
    <label class='col-sm-2 control-label'>$langStartDate:</label>
        <div class='col-xs-10 col-sm-9'>               
            <input class='form-control' name='user_date_start' type='text' value = '" . q($user_date_start) . "'>
        </div>
        <div class='col-xs-2 col-sm-1'>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>
        </div>";        
$tool_content .= "<div class='input-append date form-group' id='user_date_end' data-date= '" . q($user_date_end) . "' data-date-format='dd-mm-yyyy'>
        <label class='col-sm-2 control-label'>$langEndDate:</label>
            <div class='col-xs-10 col-sm-9'>
                <input class='form-control' name='user_date_end' type='text' value= '" . q($user_date_end) . "'>
            </div>
        <div class='col-xs-2 col-sm-1'>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>
        </div>";
$tool_content .= '<div class="form-group">  
    <label class="col-sm-2 control-label">' . $langFirstLetterCourse . ':</label>
    <div class="col-sm-10">' . $letterlinks . '</div>
  </div>
  <div class="form-group">  
    <label class="col-sm-2 control-label">' . $langCourse . ':</label>
     <div class="col-sm-10"><select name="u_course_id" class="form-control">' . $cours_opts . '</select></div>
  </div>    
<div class="form-group">  
    <label class="col-sm-2 control-label">' . $langInterval . ':</label>
     <div class="col-sm-10"><select name="u_interval" class="form-control">' . $statsIntervalOptions . '</select></div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">    
        <input class="btn btn-primary" type="submit" name="btnUsage" value="' . $langSubmit . '">
    </div> 
  </div>
</form></div>';

draw($tool_content, 3, null, $head_content);
