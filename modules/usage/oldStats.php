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
 * @brief Show old statistics for the course, taken from table "action_summary" of the course's database.
 */
$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;

include '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'statistics_tools_bar.php';

load_js('tools.js');
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

statistics_tools($course_code, "oldStats");

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
$nameTools = $langOldStats;

$result = Database::get()->queryArray("SELECT MIN(day) AS min_time FROM actions_daily WHERE course_id = ?d", $course_id);
foreach ($result as $row) {
    if (!empty($row->min_time)) {
        $min_time = strtotime($row->min_time);
    } else
        break;
}

if ($min_time + get_config('actions_expire_interval') * 30 * 24 * 3600 < time()) { // actions more than X months old
    $action = new action();
    $action->summarize();     // move data to action_summary
}

$result = Database::get()->queryArray("SELECT MIN(day) AS min_time FROM actions_daily WHERE course_id = ?d", $course_id);
foreach ($result as $row) {
    if (!empty($row->min_time)) {
        $min_time = strtotime($row->min_time);
    } else
        break;
}

$min_t = date("d-m-Y", $min_time);
$dateNow = date("d-m-Y / H:i:s", time());

$made_chart = true;
//make chart
require_once 'modules/graphics/plotter.php';
$usage_defaults = array(
    'u_stats_value' => 'visits',
    'u_module_id' => -1,
    'u_date_start' => strftime('%Y-%m-%d', strtotime('now -2 year')),
    'u_date_end' => strftime('%Y-%m-%d', strtotime('now -' . get_config('actions_expire_interval') . ' month')),
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = $_POST[$key];
    }
}

$date_fmt = '%Y-%m-%d';
$date_where = " (start_date BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";


if ($u_module_id != -1) {
    $mod_where = " (module_id = '$u_module_id') ";
} else {
    $mod_where = " (1) ";
}

$chart = new Plotter();
$chart->setTitle("$langOldStats");
switch ($u_stats_value) {
    case "visits":
        $result = Database::get()->queryArray("SELECT module_id, MONTH(start_date) AS month,
                        YEAR(start_date) AS year,
                        SUM(visits) AS visits
                        FROM actions_summary
                        WHERE $date_where
                        AND $mod_where
                        AND course_id = ?d
                        GROUP BY MONTH(start_date)", $course_id);

        foreach ($result as $row) {
            $mont = $langMonths[$row->month];
            $chart->addPoint($mont . " - " . $row->year, $row->visits);
        }
        break;

    case "duration":
        $result = Database::get()->queryArray("SELECT module_id, MONTH(start_date) AS month,
                        YEAR(start_date) AS year,
                        SUM(duration) AS tot_dur FROM actions_summary
                        WHERE $date_where
                        AND $mod_where
                        AND course_id = ?d
                        GROUP BY MONTH(start_date)", $course_id);

        foreach ($result as $row) {
            $mont = $langMonths[$row->month];
            $chart->addPoint($mont . " - " . $row->year, $row->tot_dur);
        }
        $tool_content .= "<div class='alert alert-info'>$langDurationExpl</div>";
        break;
}

$chart_path = 'courses/' . $course_code . '/temp/chart_' . md5(serialize($chart)) . '.png';

if (!$chart->isEmpty()) {
    $tool_content .= "<div class='alert alert-info'>" . sprintf($langOldStatsExpl, get_config('actions_expire_interval')) . "</div>";
    $tool_content .= $chart->plot($langNoStatistics);
}

$mod_opts = '<option value="-1">' . $langAllModules . "</option>";
$result = Database::get()->queryArray("SELECT module_id FROM course_module WHERE visible = 1 AND course_id = ?d", $course_id);
foreach ($result as $row) {
    $mid = $row->module_id;
    $extra = '';
    if ($u_module_id == $mid) {
        $extra = 'selected';
    }
    $mod_opts .= "<option value=" . $mid . " $extra>" . $modules[$mid]['title'] . "</option>";
}

$statsValueOptions = '<option value="visits" ' . (($u_stats_value == 'visits') ? ('selected') : ('')) . '>' . $langVisits . "</option>\n" .
        '<option value="duration" ' . (($u_stats_value == 'duration') ? ('selected') : ('')) . '>' . $langDuration . "</option>\n";

$tool_content .= '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">
       <fieldset>
	 <legend>' . $langOldStats . '</legend>
	 <table class="tbl">
	 <tr>
	   <th>&nbsp;</th>
	   <td>' . $langCreateStatsGraph . ':</td>
	 </tr>
	 <tr>
	   <th>' . $langValueType . '</th>
	   <td><select name="u_stats_value">' . $statsValueOptions . '</select></td>
	 </tr>
	 <tr>
        <th>' . $langStartDate . ':</th>
        <td><input type="text" name="u_date_start" value="' . $u_date_start . '"></td>
        </tr>
        <tr>
        <th>' . $langEndDate . ':</th>
        <td><input type="text" name="u_date_end" value="' . $u_date_end . '"></td>    
        </tr>
	 <tr>
	   <th>' . $langModule . '</th>
	   <td><select name="u_module_id">' . $mod_opts . '</select></td>
	 </tr>
	 <tr>
	   <th>&nbsp;</th>
	   <td><input class="btn btn-primary" type="submit" name="btnUsage" value="' . $langSubmit . '"></td>
	 </tr>
	 </table>
       </fieldset>
       </form>';

draw($tool_content, 2, null, $head_content);
