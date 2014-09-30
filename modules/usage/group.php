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
 * @file group.php
 * @brief group statistics
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
</div>";

$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
$nameTools = $langGroupUsage;

$head_content = '<script type="text/javascript" src="../auth/sorttable.js"></script>';
initialize_group_info();

$i = 0;
$q = Database::get()->queryArray("SELECT id, name, g.description, max_members, COUNT(*) AS registered
	              FROM `group` AS g, group_members AS gm
		      WHERE g.course_id = ?d AND g.id = gm.group_id
		      GROUP BY g.id", $course_id);
if (count($q) > 0) {
    $tool_content .= "<table class='sortable' width='99%' id='b'>
		<tr>
		  <th class='left'>$langGroupName</th>
		  <th>$langGroupTutor</th>
		  <th class='center'>$langRegistered</th>
		  <th class='center'>$langMax</th>
		</tr>";
    foreach ($q as $group) {
        if ($i % 2 == 0) {
            $tool_content .= "<tr class='even'>";
        } else {
            $tool_content .= "<tr class='odd'>";
        }
        $tool_content .= "<td class='arrow'>
			<a href='../group/group_usage.php?course=$course_code&amp;module=usage&amp;group_id=$group->id'>" .
                q($group->name) . "</a></td>";              
        $tool_content .= "<td>" . display_user(group_tutors($group->id)) . "</td>";
        $tool_content .= "<td class='center'>$group->registered</td>";
        if ($group->max_members == 0) {
            $tool_content .= "<td class='center'>-</td>";
        } else {
            $tool_content .= "<td class='center'>$group->max_members</td>";
        }
        $tool_content .= "</tr>";
        $i++;
    }
    $tool_content .= "</table>";
} else {
    $tool_content .= "<p class='caution_small'>$langNoGroup</p>";
}

draw($tool_content, 2, null, $head_content);
