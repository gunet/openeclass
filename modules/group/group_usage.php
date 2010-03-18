<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/*
 * Groups usage / statistics
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';
include '../../include/lib/learnPathLib.inc.php';
include '../usage/duration_query.php';

if (isset($_GET['module']) and $_GET['module'] == 'usage') {
        $navigation[] = array('url' => '../usage/usage.php', 'name'=> $langUsage);
        $navigation[] = array('url' => '../usage/group.php', 'name'=> $langGroupUsage);
        $module = 'module=usage&amp;';
} else {
        $navigation[] = array('url' => 'group.php', 'name'=> $langGroupSpace);
        $navigation[] = array('url' => "group_space.php?userGroupId=$userGroupId", 'name'=> $langGroupSpace);
        $module = '';
}

$userGroupId = intval($_REQUEST['userGroupId']);
list($tutor_id, $group_name) = mysql_fetch_row(db_query("SELECT tutor, name FROM student_group WHERE id='$userGroupId'", $currentCourseID));
$is_tutor = ($tutor_id == $uid);
if (!$is_adminOfCourse and !$is_tutor) {
        header('Location: group_space.php?userGroupId=' . $userGroupId);
        exit;
}

$nameTools = $group_name;

$type = 'duration';
if (isset($_GET['type']) and
    in_array($_GET['type'], array('duration', 'visits', 'lp'))) {
        $type = $_GET['type'];
}

$head_content = '<script type="text/javascript" src="../auth/sorttable.js"></script>';

$tool_content = "";


$base = 'group_usage.php?' . $module . 'userGroupId=' . $userGroupId . '&amp;type=';

function link_current($title, $this_type)
{
        global $type, $base;
        if ($type == $this_type) {
                return "<li><b>$title</b></li>";
        } else {
                return "<li><a href='$base$this_type'>$title</a></li>";
        }
}
if (isset($_POST['u_date_start']) and
            isset($_POST['u_date_end'])) {
	$link = "<li>$langDumpUserDurationToFile (<a href='dumpgroupduration.php?userGroupId=$userGroupId&u_date_start=$_POST[u_date_start]&u_date_end=$_POST[u_date_end]'>$langCodeUTF</a>&nbsp;<a href='dumpgroupduration.php?userGroupId=$userGroupId&enc=1253&u_date_start=$_POST[u_date_start]&u_date_end=$_POST[u_date_end]'>$langCodeWin</a>)</li>";
} else {
	$link = "<li>$langDumpUserDurationToFile (<a href='dumpgroupduration.php?userGroupId=$userGroupId'>$langCodeUTF</a>&nbsp;<a href='dumpgroupduration.php?userGroupId=$userGroupId&enc=1253'>$langCodeWin</a>)</li>";
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
        include('../../include/jscalendar/calendar.php');
        $jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/',
                                         langname_to_code($language),
                                         'calendar-blue2', false);
        $head_content .= $jscalendar->get_load_files_code();

        list($min_date) = mysql_fetch_row(db_query(
                                'SELECT MIN(date_time) FROM actions'));

        if (isset($_POST['u_date_start']) and
            isset($_POST['u_date_end'])) {
		$u_date_start = autounquote($_POST['u_date_start']);
                $u_date_end = autounquote($_POST['u_date_end']);
        } else {
                $u_date_start = strftime('%Y-%m-%d', strtotime($min_date));
                $u_date_end = strftime('%Y-%m-%d', strtotime('now'));
        }

        // date range form
        $style = 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center';
        $start_cal = $jscalendar->make_input_field(
                        array('showsTime' => false,
                              'showOthers' => true,
                              'ifFormat' => '%Y-%m-%d',
                              'timeFormat' => '24'),
                        array('style' => $style,
                              'name' => 'u_date_start',
                              'value' => $u_date_start));
        $end_cal = $jscalendar->make_input_field(
                        array('showsTime' => false,
                                'showOthers' => true,
                                'ifFormat' => '%Y-%m-%d',
                                'timeFormat' => '24'),
                        array('style' => $style,
                              'name' => 'u_date_end',
                              'value' => $u_date_end));
        $tool_content .= '<form method="post" action="' . $base . $type .
                '"><table class="FormData" align="left">' .
                "<tr><th class='left'>$langStartDate:</th>" .
                "<td>$start_cal</td></tr>" . 
                "<tr><th class='left'>$langEndDate:</th>" .
                "<td>$end_cal</td></tr>" .
                '<tr><th class="left">&nbsp;</th>' .
                "<td><input type='submit' name='submit' value='$langSubmit' />" .
                '</td></tr></table></form>';
} elseif ($type == 'lp') {
        $label = $langProgress;
        // list available learning paths
        $learningPathList = db_query_fetch_all('SELECT learnPath_id FROM lp_learnPath');
} else {
        $label = '?';
}

$tool_content .= "<table class='FormData sortable' width='100%' id='a'>
	<tr>
	<th class='left'>$langSurname $langName</th>
	<th>$langAm</th>
	<th>$langGroup</th>
	<th>$label</th>
	</tr>";

$i = 0;
if ($type == 'duration') {
        $result = user_duration_query($currentCourseID, $cours_id, $u_date_start, $u_date_end, $userGroupId);
} else {
        $result = db_query('SELECT user AS user_id FROM user_group WHERE team = ' . $userGroupId);
}
if ($result) {
        while ($row = mysql_fetch_array($result)) {
                $user_id = $row['user_id'];
                if ($i%2 == 0) {
                        $tool_content .= "<tr>";
                } else {
                        $tool_content .= "<tr class='odd'>";
                }
                $i++;
                if ($type == 'duration') {
                	$value = format_time_duration(0 + $row['duration']);
                        $sortkey = $row['duration'];
                        $name = $row['nom'] . ' ' .$row['prenom'];
                        $am = $row['am'];
                } elseif ($type == 'lp') {
                        $name = uid_to_name($user_id);
                        $am = uid_to_am($user_id);
                        $iterator = 0;
                        $progress = 0;
                        mysql_select_db($currentCourseID);
                        foreach ($learningPathList as $learningPath) {
                                $progress += get_learnPath_progress($learningPath['learnPath_id'], $user_id);
                                $iterator++;
                        }
                        $total = round($progress / $iterator);
                        $sortkey = $total;
                        $value = disp_progress_bar($total, 1) . '&nbsp;<small>' . $total . '%</small>';
                }
                $tool_content .= "<td width='30%'>$name</td><td width='30%'>$am</td><td align='center'>$group_name</td><td sorttable_customkey='$sortkey'>$value</td></tr>";
        }
        $tool_content .= "</tbody></table>";
}

if ($type == 'duration') {
        user_duration_query_end();
}

draw($tool_content, 2, 'group', $head_content);
