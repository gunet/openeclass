<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

$nameTools = $langUsage;
$navigation[] = array("url"=>"group.php", "name"=> $langGroupSpace,
"url"=>"group_space.php?userGroupId=$userGroupId", "name"=>$langGroupSpace);

$userGroupId = intval($_REQUEST['userGroupId']);
list($tutor_id) = mysql_fetch_row(db_query("SELECT tutor FROM student_group WHERE id='$userGroupId'", $currentCourseID));
$is_tutor = ($tutor_id == $uid);
if (!$is_adminOfCourse and !$is_tutor) {
        header('Location: group_space.php?userGroupId=' . $userGroupId);
        exit;
}

$type = 'duration';
if (isset($_GET['type']) and
    in_array($_GET['type'], array('duration', 'visits', 'lp'))) {
        $type = $_GET['type'];
}

$head_content = '<script type="text/javascript" src="../auth/sorttable.js"></script>';

$tool_content = "";


$base = 'group_usage.php?userGroupId=' . $userGroupId . '&amp;type=';

function link_current($title, $this_type)
{
        global $type, $base;
        if ($type == $this_type) {
                return "<li><b>$title</b></li>";
        } else {
                return "<li><a href='$base$this_type'>$title</a></li>";
        }
}

$tool_content .= "<div id='operations_container'><ul id='opslist'>" .
        link_current($langUsage, 'duration') .
        link_current($langLearningPaths, 'lp') .
        // link_curent($langUsageVisits, 'visits') .
        "</ul></div>";

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

if ($type == 'duration') {
        $label = $langDuration;
} elseif ($type == 'lp') {
        $label = $langProgress;
        // list available learning paths
        $learningPathList = db_query_fetch_all('SELECT learnPath_id FROM lp_learnPath');
} else {
        $label = '?';
}

$tool_content .= "<table class='FormData sortable' width='100%' id='1'><tbody>
	<tr>
	<th class='left'>$langSurname $langName</th>
	<th>$langAm</th>
	<th>$langGroup</th>
	<th>$label</th>
	</tr>
	</thead>
	<tbody>";


$i = 0;
$result = db_query('SELECT user FROM user_group WHERE team = ' . $userGroupId);
while ($row = mysql_fetch_row($result)) {
	$user_id = $row[0];
        if ($i%2 == 0) {
                $tool_content .= "<tr>";
        } else {
                $tool_content .= "<tr class='odd'>";
        }
        $i++;
        if ($type == 'duration') {
        	$request = db_query('SELECT SUM(duration) FROM actions WHERE user_id = ' . $user_id);
        	list($time) = mysql_fetch_row($request);
        	$value = format_time_duration(0 + $time);
                $sortkey = $time;
        } elseif ($type == 'lp') {
                $iterator = 0;
                $progress = 0;
                foreach ($learningPathList as $learningPath) {
                        $progress += get_learnPath_progress($learningPath['learnPath_id'], $user_id);
                        $iterator++;
                }
                $total = round($progress / $iterator);
                $sortkey = $total;
                $value = disp_progress_bar($total, 1) . '&nbsp;<small>' . $total . '%</small>';
        }
        $tool_content .= "<td width='30%'>" .uid_to_name($user_id) . "</td><td width='30%'>" . uid_to_am($user_id) . "</td><td align='center'>" . user_group($user_id) . "</td><td sorttable_customkey='$sortkey'>$value</td></tr>";
}
$tool_content .= "</tbody></table>";

draw($tool_content, 2, 'group', $head_content);
