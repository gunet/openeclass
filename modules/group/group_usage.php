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
 * @file group_usage.php
 * @brief Groups usage / statistics
 */
$require_current_course = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'group_functions.php';
require_once 'modules/usage/duration_query.php';

load_js('tools.js');
load_js('bootstrap-datepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_start, #user_date_end').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true    
            });            
        });
    </script>";

$group_id = intval($_REQUEST['group_id']);

if (isset($_GET['module']) and $_GET['module'] == 'usage') {
    $navigation[] = array('url' => '../usage/?course=' . $course_code, 'name' => $langUsage);
    $navigation[] = array('url' => '../usage/group.php?course=' . $course_code, 'name' => $langGroupUsage);
    $module = 'module=usage&amp;';
} else {
    $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langGroupSpace);
    $navigation[] = array('url' => "group_space.php?course=$course_code&amp;group_id=$group_id", 'name' => $langGroupSpace);
    $module = '';
}

initialize_group_info($group_id);

if (!$is_editor and !$is_tutor) {
    header('Location: group_space.php?course=' . $course_code . '&group_id=' . $group_id);
    exit;
}

$pageName = $group_name;

$type = 'duration';
if (isset($_GET['type'])) {
    $type = $_GET['type'];
}

$base = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;' . $module . 'group_id=' . $group_id . '';
if (isset($_POST['user_date_start']) or isset($_POST['user_date_end'])) {
    $append_url_link = "&amp;u_date_start=$_POST[user_date_start]&u_date_end=$_POST[user_date_end]";
} else {
    $append_url_link = '';
}
$tool_content .= action_bar(array(
            array('title' => $langLearningPaths,
                'url' => "$base&amp;type=lp",
                'icon' => 'fa-bar-chart',
                'show' => $type == "duration",
                'level' => 'primary'),
            array('title' => $langUsage,
                'url' => "$base&amp;type=duration",
                'icon' => 'fa-bar-chart',
                'show' => $type == "lp",
                'level' => 'primary'),
            array('title' => "$langDumpUserDurationToFile ($langCodeUTF)",
                'url' => "dumpgroup.php?course=$course_code&amp;group_id=$group_id$append_url_link",
                'icon' => 'fa-file-archive-o',
                'level' => 'primary'),
            array('title' => "$langDumpUserDurationToFile ($langCodeWin)",
                'url' => "dumpgroup.php?course=$course_code&amp;group_id=$group_id$append_url_link",
                'icon' => 'fa-file-archive-o',                
                'level' => 'primary')));

if ($type == 'duration') {
    $label = $langDuration;    
    
    $min_date = Database::get()->querySingle("SELECT MIN(day) AS minday FROM actions_daily WHERE course_id = ?d", $course_id)->minday;
    
    if (isset($_POST['user_date_start'])) {
        $uds = DateTime::createFromFormat('d-m-Y', $_POST['user_date_start']);
        $u_date_start = $uds->format('Y-m-d');
        $user_date_start = $uds->format('d-m-Y');
    } else {        
        $date_start = DateTime::createFromFormat('Y-m-d', $min_date);
        $u_date_start = $date_start->format('Y-m-d');
        $user_date_start = $date_start->format('d-m-Y');       
    }
    if (isset($_POST['user_date_end'])) {
        $ude = DateTime::createFromFormat('d-m-Y', $_POST['user_date_end']);    
        $u_date_end = $ude->format('Y-m-d');
        $user_date_end = $ude->format('d-m-Y');        
    } else {
        $date_end = new DateTime();
        $u_date_end = $date_end->format('Y-m-d');
        $user_date_end = $date_end->format('d-m-Y');        
    }    
    $tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$base&amp;type=$type'>
            <div class='input-append date form-group' id='user_date_start' data-date = '" . q($user_date_start) . "' data-date-format='dd-mm-yyyy'>
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
    $tool_content .= "<div class='col-sm-offset-2 col-sm-10'>
            <input class='btn btn-primary' type='submit' name='btnUsage' value='$langSubmit'>
            </div>";
    $tool_content .= "</form></div>";
} elseif ($type == 'lp') {
    $label = $langProgress;
    // list available learning paths
    $learningPathList = Database::get()->queryArray("SELECT learnPath_id FROM `$course_code`.lp_learnPath");
} else {
    $label = '?';
}

$tool_content .= "<div class='table-responsive'><table class='table-default'>
	<tr>
	<th class='text-left'>$langSurname $langName</th>
	<th>$langAm</th>
	<th>$langGroup</th>
	<th>$label</th>
	</tr>";

if ($type == 'duration') {    
    $result = user_duration_query($course_id, $u_date_start, $u_date_end, $group_id);
} else {
    $result = Database::get()->queryArray("SELECT user_id AS id FROM group_members WHERE group_id = ?d", $group_id);
}
if (count($result) > 0) {
    foreach ($result as $row) {
        $user_id = $row->id;        
        if ($type == 'duration') {
            $value = format_time_duration(0 + $row->duration);
            $sortkey = $row->duration;
            $name = $row->surname . ' ' . $row->givenname;
            $am = $row->am;
        } elseif ($type == 'lp') {
            $name = uid_to_name($user_id);
            $am = uid_to_am($user_id);
            $iterator = 0;
            $progress = 0;
            foreach ($learningPathList as $learningPath) {
                $progress += get_learnPath_progress($learningPath->learnPath_id, $user_id);
                $iterator++;
            }
            if ($iterator > 0) {
                $total = round($progress / $iterator);
                $sortkey = $total;
                $value = disp_progress_bar($total, 1) . '&nbsp;<small>' . $total . '%</small>';
            } else {
                $value = '&mdash;';
            }
        }
        $tool_content .= "<td width='30%'>" . q($name) . "</td><td width='30%'>" . q($am) . "</td><td align='text-center'>$group_name</td>"
                       . "<td>$value</td></tr>";
    }
}
$tool_content .= "</table></div>";

draw($tool_content, 2, null, $head_content);