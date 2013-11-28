<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
 * Groups Statistics
 *
 */

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'modules/group/group_functions.php';

$tool_content .= "
<div id='operations_container'>
  <ul id='opslist'>
    <li><a href='favourite.php?course=$course_code&amp;first='>$langFavourite</a></li>
    <li><a href='userlogins.php?course=$course_code&amp;first='>$langUserLogins</a></li>
    <li><a href='userduration.php?course=$course_code'>$langUserDuration</a></li>
    <li><a href='../learnPath/detailsAll.php?course=$course_code&amp;from_stats=1'>$langLearningPaths</a></li>
    <li><a href='group.php?course=$course_code'>$langGroupUsage</a></li>
  </ul>
</div>\n";


$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
$nameTools = $langGroupUsage;

$head_content = '<script type="text/javascript" src="../auth/sorttable.js"></script>';
initialize_group_info();

$i = 0;
$q = db_query("SELECT id, name, g.description, max_members, COUNT(*) AS registered
	              FROM `group` AS g, group_members AS gm
		      WHERE g.course_id = $course_id AND g.id = gm.group_id
		      GROUP BY g.id", $mysqlMainDb);
if (mysql_num_rows($q) > 0) {
    $tool_content .= "
                <table class='sortable' width='99%' id='b'>
		<tr>
		  <th class='left'>$langGroupName</th>
		  <th>$langGroupTutor</th>
		  <th class='center'>$langRegistered</th>
		  <th class='center'>$langMax</th>
		</tr>\n";
    while ($group = mysql_fetch_array($q)) {
        if ($i % 2 == 0) {
            $tool_content .= "<tr class='even'>\n";
        } else {
            $tool_content .= "<tr class='odd'>\n";
        }
        $tool_content .= "<td class='arrow'>
			<a href='../group/group_usage.php?course=$course_code&amp;module=usage&amp;group_id=$group[id]'>" .
                q($group['name']) . "</a></td>\n";
        $tool_content .= "<td>" . display_user(group_tutors($group['id'])) . "</td>\n";
        $tool_content .= "<td class='center'>$group[registered]</td>\n";
        if ($group['max_members'] == 0) {
            $tool_content .= "<td class='center'>-</td>\n";
        } else {
            $tool_content .= "<td class='center'>$group[max_members]</td>\n";
        }
        $tool_content .= "</tr>\n";
        $i++;
    }
    $tool_content .= "</table>\n";
} else {
    $tool_content .= "<p class='caution_small'>$langNoGroup</p>";
}

draw($tool_content, 2, null, $head_content);
