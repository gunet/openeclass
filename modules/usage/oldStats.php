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
  usage/oldStats.php
 * @version $Id$
  @last update: 2006-12-27 by Evelthon Prodromou <eprodromou@upnet.gr>
  @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr
  Ophelia Neofytou    ophelia@ucnet.uoc.gr
  ==============================================================================
  @Description:
  Show old statistics for the course, taken from table "action_summary" of the course's database.

  ==============================================================================
 */

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;

include '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'include/jscalendar/calendar.php';

$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='index.php?course=$course_code'>" . $langUsageVisits . "</a></li>
      <li><a href='favourite.php?course=$course_code&amp;first='>" . $langFavourite . "</a></li>
      <li><a href='userlogins.php?course=$course_code&amp;first='>" . $langUserLogins . "</a></li>
    </ul>
  </div>";
$query = "SELECT MIN(day) as min_time FROM actions_daily WHERE course_id = $course_id";
$result = db_query($query);
while ($row = mysql_fetch_assoc($result)) {
    if (!empty($row['min_time'])) {
        $min_time = strtotime($row['min_time']);
    } else
        break;
}

mysql_free_result($result);
if ($min_time + get_config('actions_expire_interval') * 30 * 24 * 3600 < time()) { // actions more than X months old
    $action = new action();
    $action->summarize();     // move data to action_summary
}

$query = "SELECT MIN(day) as min_time FROM actions_daily WHERE course_id = $course_id";
$result = db_query($query);
while ($row = mysql_fetch_assoc($result)) {
    if (!empty($row['min_time'])) {
        $min_time = strtotime($row['min_time']);
    } else
        break;
}
mysql_free_result($result);

$min_t = date("d-m-Y", $min_time);

$dateNow = date("d-m-Y / H:i:s", time());
$nameTools = $langUsage;
$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

$jscalendar = new DHTML_Calendar($urlServer . 'include/jscalendar/', $language, 'calendar-blue2', false);
$head_content = $jscalendar->get_load_files_code();

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

$chart = new Plotter(600, 300);
$chart->setTitle("$langOldStats");
switch ($u_stats_value) {
    case "visits":
        $query = "SELECT module_id, MONTH(start_date) AS month,
                        YEAR(start_date) AS year,
                        SUM(visits) AS visits
                        FROM actions_summary
                        WHERE $date_where
                        AND $mod_where
                        AND course_id = $course_id
                        GROUP BY MONTH(start_date)";

        $result = db_query($query);
        while ($row = mysql_fetch_assoc($result)) {
            $mont = $langMonths[$row['month']];
            $chart->growWithPoint($mont . " - " . $row['year'], $row['visits']);
        }
        break;

    case "duration":
        $query = "SELECT module_id, MONTH(start_date) AS month,
                        YEAR(start_date) AS year,
                        SUM(duration) AS tot_dur FROM actions_summary
		    WHERE $date_where
                    AND $mod_where
                    AND course_id = $course_id
                    GROUP BY MONTH(start_date)";

        $result = db_query($query);
        while ($row = mysql_fetch_assoc($result)) {
            $mont = $langMonths[$row['month']];
            $chart->growWithPoint($mont . " - " . $row['year'], $row['tot_dur']);
        }
        $tool_content .= "<p>$langDurationExpl</p>";
        break;
}
mysql_free_result($result);
$chart_path = 'courses/' . $course_code . '/temp/chart_' . md5(serialize($chart)) . '.png';

if (!$chart->isEmpty()) {
    $tool_content .= "<p>" . sprintf($langOldStatsExpl, get_config('actions_expire_interval')) . "</p>";
    $tool_content .= $chart->plot($langNoStatistics);
}
// make form
$start_cal = $jscalendar->make_input_field(
        array('showsTime' => false,
    'showOthers' => true,
    'ifFormat' => '%Y-%m-%d',
    'timeFormat' => '24'), array('style' => 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center',
    'name' => 'u_date_start',
    'value' => $u_date_start));

$end_cal = $jscalendar->make_input_field(
        array('showsTime' => false,
    'showOthers' => true,
    'ifFormat' => '%Y-%m-%d',
    'timeFormat' => '24'), array('style' => 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center',
    'name' => 'u_date_end',
    'value' => $u_date_end));

$qry = "SELECT module_id FROM course_module WHERE visible = 1 AND course_id = $course_id";

$mod_opts = '<option value="-1">' . $langAllModules . "</option>\n";
$result = db_query($qry);
while ($row = mysql_fetch_assoc($result)) {
    $mid = $row['module_id'];
    $extra = '';
    if ($u_module_id == $mid) {
        $extra = 'selected';
    }
    $mod_opts .= "<option value=" . $mid . " $extra>" . $modules[$mid]['title'] . "</option>";
}
mysql_free_result($result);

$statsValueOptions =
        '<option value="visits" ' . (($u_stats_value == 'visits') ? ('selected') : ('')) . '>' . $langVisits . "</option>\n" .
        '<option value="duration" ' . (($u_stats_value == 'duration') ? ('selected') : ('')) . '>' . $langDuration . "</option>\n";

$tool_content .= '
       <form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">
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
	   <th>' . $langStartDate . '</th>
	   <td>' . "$start_cal" . '</td>
	 </tr>
	 <tr>
	    <th>' . $langEndDate . '</th>
	    <td>' . "$end_cal" . '</td>
	 </tr>
	 <tr>
	   <th>' . $langModule . '</th>
	   <td><select name="u_module_id">' . $mod_opts . '</select></td>
	 </tr>
	 <tr>
	   <th>&nbsp;</th>
	   <td><input type="submit" name="btnUsage" value="' . $langSubmit . '"></td>
	 </tr>
	 </table>
       </fieldset>
       </form>';

draw($tool_content, 2, null, $head_content);
