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

$require_current_course = true;
$require_course_reviewer = true;
$require_help = true;
$helpTopic = 'course_stats';
$helpSubTopic = 'old_statistics';
$require_login = true;

include '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'modules/usage/usage.lib.php';

load_js('bootstrap-datetimepicker');
load_js('bootstrap-datepicker');

$toolName = $langUsage;
$pageName = $langOldStats;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
$navigation[] = array('url' => 'index.php?course=' . $course_code .'&gc_stats=true', 'name' => $langPlatformGenStats);

$data['plot_placeholder'] = plot_placeholder("old_stats", $langOldStats);

$endDate_obj = new DateTime();
$startDate_obj = $endDate_obj->sub(new DateInterval('P2Y'));
$user_date_start = $startDate_obj->format('d-m-Y');

if (isset($_POST['user_date_start']) && isset($_POST['user_date_end'])) {
    $uds = DateTime::createFromFormat('d-m-Y', $_POST['user_date_start']);
    $u_date_start = $uds->format('Y-m-d');
    $user_date_start = $uds->format('d-m-Y');

    $ude = DateTime::createFromFormat('d-m-Y', $_POST['user_date_end']);
    $u_date_end = $ude->format('Y-m-d');
    $user_date_end = $ude->format('d-m-Y');
} else {
    $last_month = "P" . get_config('actions_expire_interval') . "M";
    $date_end = new DateTime();
    $date_end->sub(new DateInterval($last_month));
    $u_date_end = $date_end->format('Y-m-d');
    $user_date_end = $date_end->format('d-m-Y');
}
$data['user_date_start'] = $user_date_start;
$data['user_date_end'] = $user_date_end;

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
$usage_defaults = array(
    'u_module_id' => -1
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = $_POST[$key];
    }
}

$mod_opts = '<option value="-1">' . $langAllModules . "</option>";
$result = Database::get()->queryArray("SELECT module_id FROM course_module WHERE visible = 1 AND course_id = ?d", $course_id);
foreach ($result as $row) {
    $mid = $row->module_id;
    $extra = '';
    if ($u_module_id == $mid) {
        $extra = 'selected';
    }
    if(array_key_exists($mid,$modules)){
        $mod_opts .= "<option value=" . $mid . " $extra>" . $modules[$mid]['title'] . "</option>";
    }
}

$data['mod_opts'] = $mod_opts;

view('modules.usage.old_stats', $data);
