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
 * @file: favourite.php
 * @brief: Creates a pie-chart with the preferences of the users regarding the
 */
$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'modules/graphics/plotter.php';
require_once 'statistics_tools_bar.php';

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

statistics_tools($course_code, "favourite");

$nameTools = $langFavourite;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);

$usage_defaults = array(
    'u_stats_value' => 'visits',
    'u_user_id' => -1,
    'u_date_start' => strftime('%Y-%m-%d', strtotime('now -30 day')),
    'u_date_end' => strftime('%Y-%m-%d', strtotime('now')),
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = $_POST[$key];
    }
}

$date_fmt = '%Y-%m-%d';
$date_where = " (day BETWEEN ?s AND ?s)";
$terms = array($u_date_start, $u_date_end);

if ($u_user_id != -1) {
    $user_where = "AND user_id = ?d";
    $terms[] = intval($u_user_id);
} else {
    $user_where = '';
}

$chart_error = "";
switch ($u_stats_value) {
    case "visits":
        $chart = new Plotter();
        $chart->setTitle($langFavourite);
        $result = Database::get()->queryArray("SELECT module_id, SUM(hits) AS cnt FROM actions_daily
                        WHERE $date_where AND
                              course_id = ?d
                              $user_where
                        GROUP BY module_id", $course_id, $terms);
        foreach ($result as $row) {
            $mid = $row->module_id;
            if ($mid == MODULE_ID_UNITS) { // course units
                $chart->addPoint($langCourseUnits, $row->cnt);
            } else { // other modules
                $chart->addPoint($modules[$mid]['title'], $row->cnt);
            }
        }
        $chart_error = $langNoStatistics;
        break;

    case "duration":
        $chart = new Plotter();
        $chart->setTitle($langFavourite);
        $result = Database::get()->queryArray("SELECT module_id, SUM(duration) AS tot_dur FROM actions_daily
                        WHERE $date_where
                        AND course_id = ?d
                        $user_where GROUP BY module_id", $course_id);

        foreach ($result as $row) {
            $mid = $row->module_id;
            if ($mid == MODULE_ID_UNITS) { // course inits
                $chart->addPoint($langCourseUnits, $row->tot_dur);
            } else { // other modules
                $chart->addPoint($modules[$mid]['title'], $row->tot_dur);
            }
        }
        $chart_error = $langDurationExpl;
        break;
}

if (isset($_POST['btnUsage'])) {
    $chart->normalize();
    $tool_content .= $chart->plot($chart_error);
}

$letterlinks = '';
$result = Database::get()->queryArray("SELECT LEFT(a.surname, 1) AS first_letter
        FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
        WHERE b.course_id = ?d
        GROUP BY first_letter ORDER BY first_letter", $course_id);
foreach ($result as $row) {
    $first_letter = $row->first_letter;
    $letterlinks .= '<a href="?course=' . $course_code . '&amp;first=' . urlencode($first_letter) . '">' . q($first_letter) . '</a> ';
}

$user_opts = '<option value="-1">' . $langAllUsers . "</option>";
$user_opts .= '<option value="0">' . $langAnonymous . "</option>";

if (isset($_GET['first'])) {
    $firstletter = $_GET['first'];
    $result = Database::get()->queryArray("SELECT a.id, a.surname, a.givenname, a.username, a.email, b.status
            FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
            WHERE b.course_id = ?d AND LEFT(a.surname,1) = ?s", $course_id, $first_letter);
} else {
    $result = Database::get()->queryArray("SELECT a.id, a.surname, a.givenname, a.username, a.email, b.status
            FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
            WHERE b.course_id = ?d", $course_id);
}
foreach ($result as $row) {
    if ($u_user_id == $row->id) {
        $selected = 'selected';
    } else {
        $selected = '';
    }
    $user_opts .= '<option ' . $selected . ' value="' . $row->id . '">' . q($row->givenname . ' ' . $row->surname) . "</option>";
}

$statsValueOptions = '<option value="visits" ' . (($u_stats_value == 'visits') ? ('selected') : ('')) . '>' . $langVisits . "</option>" .
        '<option value="duration" ' . (($u_stats_value == 'duration') ? ('selected') : ('')) . '>' . $langDuration . "</option>";

$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
    <fieldset>
     <legend>$langFavourite</legend>
     <table class='tbl'>
     <tr>
       <td>&nbsp;</td>
       <td class='bold'>$langCreateStatsGraph:</td>
     </tr>
     <tr>
       <td>$langValueType':</td>
       <td><select name='u_stats_value'>$statsValueOptions</select></td>
     </tr>
     <tr>
       <td>$langStartDate:</td>
       <td><input type='text' name ='u_date_start' value='$u_date_start'></td>
     </tr>
     <tr>
       <td>$langEndDate:</td>
       <td><input type='text' name='u_date_end' value='$u_date_end'></td>
     </tr>
     <tr>
       <td rowspan='2' valign='top'>$langUser:</td>
       <td>$langFirstLetterUser: $letterlinks</td>
     </tr>
     <tr>
       <td><select name='u_user_id'>$user_opts</select></td>
     </tr>
     </table>
    </fieldset>
    </form>";

draw($tool_content, 2, null, $head_content);
