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
$require_help = TRUE;
$helpTopic = 'Group';

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'group_functions.php';
require_once 'modules/usage/duration_query.php';

load_js('tools.js');
load_js('bootstrap-datepicker');

$head_content .= "
    <script type='text/javascript'>
    $(function() {
        $('#u_date_start').datepicker({
            format: 'dd-mm-yyyy',
            language: '$language',
            autoclose: true
        });
        $('#u_date_end').datepicker({
            format: 'dd-mm-yyyy',
            language: '$language',
            autoclose: true
        });
    });"
. "</script>";

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

$nameTools = $group_name;

$type = 'duration';
if (isset($_GET['type']) and in_array($_GET['type'], array('duration', 'visits', 'lp'))) {
    $type = $_GET['type'];
}

$base = $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;' . $module . 'group_id=' . $group_id . '&amp;type=';

if (isset($_POST['u_date_start']) and isset($_POST['u_date_end'])) {
    $link = "<li>$langDumpUserDurationToFile (<a href='dumpgroupduration.php?course=$course_code&amp;group_id=$group_id&u_date_start=$_POST[u_date_start]&u_date_end=$_POST[u_date_end]'>$langCodeUTF</a>
	&nbsp;<a href='dumpgroupduration.php?course=$course_code&amp;group_id=$group_id&amp;enc=1253&u_date_start=$_POST[u_date_start]&u_date_end=$_POST[u_date_end]'>$langCodeWin</a>)</li>";
} else {
    $link = "<li>$langDumpUserDurationToFile (<a href='dumpgroupduration.php?course=$course_code&amp;group_id=$group_id'>$langCodeUTF</a>
	&nbsp;<a href='dumpgroupduration.php?course=$course_code&amp;group_id=$group_id&amp;enc=1253'>$langCodeWin</a>)</li>";
}

$tool_content .= "<div id='operations_container'><ul id='opslist'>" .
        link_current($langUsage, 'duration') .
        link_current($langLearningPaths, 'lp') .
        $link .
        // link_curent($langUsageVisits, 'visits') .
        "</ul></div>";

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

if ($type == 'duration') {    
    $label = $langDuration;    
    
    $min_date = Database::get()->querySingle("SELECT MIN(day) AS minday FROM actions_daily WHERE course_id = ?d", $course_id)->minday;

    if (isset($_POST['u_date_start']) and
            isset($_POST['u_date_end'])) {
        $u_date_start = $_POST['u_date_start'];
        $u_date_end = $_POST['u_date_end'];
    } else {
        $u_date_start = strftime('%d-%m-%Y', strtotime($min_date));
        $u_date_end = strftime('%d-%m-%Y', strtotime('now'));
    }
    
    $tool_content .= "<form method='post' action='$base$type'>
        <table class = 'FormData' align = 'left'>
            <tr><th class='left'>$langStartDate:</th>
                <td><input type='text' name='u_date_start' id='u_date_start' value='$u_date_start'></td></tr>
            <tr><th class='left'>$langEndDate:</th>
                <td><input type='text' name='u_date_end' id='u_date_end' value='$u_date_end'></td></tr>                
            <tr><th class='left'>&nbsp;</th>
                <td><input type='submit' name='submit' value='$langSubmit'></td></tr>
        </table>
      </form>";
} elseif ($type == 'lp') {
    $label = $langProgress;
    // list available learning paths
    $learningPathList = Database::get()->queryArray("SELECT learnPath_id FROM `$course_code`.lp_learnPath");
} else {
    $label = '?';
}

$tool_content .= "<table class='FormData' width='100%' id='a'>
	<tr>
	<th class='left'>$langSurname $langName</th>
	<th>$langAm</th>
	<th>$langGroup</th>
	<th>$label</th>
	</tr>";

$i = 0;
if ($type == 'duration') {   
    $startDate_obj = DateTime::createFromFormat('d-m-Y', $u_date_start);
    $startdate = $startDate_obj->format('Y-m-d');
    $endDate_obj = DateTime::createFromFormat('d-m-Y', $u_date_end);
    $enddate = $endDate_obj->format('Y-m-d');     
    $result = user_duration_query($course_id, $startdate, $enddate, $group_id);    
} else {        
    $result = Database::get()->queryArray("SELECT user_id AS id FROM group_members WHERE group_id = ?d", $group_id);
}
if (count($result) > 0) {
    foreach ($result as $row) {
        $user_id = $row->id;
        if ($i % 2 == 0) {
            $tool_content .= "<tr>";
        } else {
            $tool_content .= "<tr class='odd'>";
        }
        $i++;
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
                $value = '--';
            }
        }
        $tool_content .= "<td width='30%'>" . q($name) . "</td><td width='30%'>" . q($am) . "</td><td align='center'>$group_name</td>"
                       . "<td>$value</td></tr>";
    }
}
$tool_content .= "</table>";

draw($tool_content, 2, null, $head_content);

/**
 * 
 * @global type $type
 * @global string $base
 * @param type $title
 * @param type $this_type
 * @return type
 */
function link_current($title, $this_type) {
    
    global $type, $base;
    
    if ($type == $this_type) {
        return "<li>$title</li>";
    } else {
        return "<li><a href='$base$this_type'>$title</a></li>";
    }
}
