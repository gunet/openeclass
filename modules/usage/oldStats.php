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

$toolName = $langUsage;
$pageName = $langOldStats;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);


$tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "index.php?course=$course_code",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
            ),false);


if (isset($_POST['user_date_start'])) {
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_POST['user_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');
    $user_date_start = $uds->format('d-m-Y H:i');
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P2Y'));
    $u_date_start = $date_start->format('Y-m-d H:i');
    $user_date_start = $date_start->format('d-m-Y H:i');       
}
if (isset($_POST['user_date_end'])) {
    $ude = DateTime::createFromFormat('d-m-Y H:i', $_POST['user_date_end']);
    $u_date_end = $ude->format('Y-m-d H:i');
    $user_date_end = $ude->format('d-m-Y H:i');
} else {
    $last_month = "P" . get_config('actions_expire_interval') . "M";
    $date_end = new DateTime();
    $date_end->sub(new DateInterval($last_month));
    $u_date_end = $date_end->format('Y-m-d H:i');
    $user_date_end = $date_end->format('d-m-Y H:i');        
}


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

$made_chart = true;
//make chart
require_once 'modules/graphics/plotter.php';
$usage_defaults = array(
    'u_stats_value' => 'visits',
    'u_module_id' => -1  
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = $_POST[$key];
    }
}

$date_fmt = '%Y-%m-%d';
$date_where = " (start_date BETWEEN '$u_date_start' AND '$u_date_end') ";


if ($u_module_id != -1) {
    $mod_where = " (module_id = '$u_module_id') ";
} else {
    $mod_where = " (1) ";
}

$chart = new Plotter(600, 300);
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
            $chart->growWithPoint($mont . " - " . $row->year, $row->visits);
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
            $chart->growWithPoint($mont . " - " . $row->year, $row->tot_dur);
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


$tool_content .= '<div class="form-wrapper">';
$tool_content .= '<form class="form-horizontal" role="form" method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">';
$tool_content .= '<div class="form-group">  
                    <label class="col-sm-2 control-label">' . $langValueType . ':</label>
                    <div class="col-sm-10"><select name="u_stats_value" class="form-control">' . $statsValueOptions . '</select></div>
                  </div>';
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
        <label class="col-sm-2 control-label">' . $langModule . ':</label>
        <div class="col-sm-10"><select name="u_module_id" class="form-control">' . $mod_opts . '</select></div>
  </div>
  <div class="col-sm-offset-2 col-sm-10">
    <input class="btn btn-primary" type="submit" name="btnUsage" value="' . $langSubmit . '">
    </div>
</form></div>';

draw($tool_content, 2, null, $head_content);
