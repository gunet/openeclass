<?
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
 * Groups Statistics
 *
 */

$require_current_course = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;
$require_prof = true;

include '../../include/baseTheme.php';

$navigation[] = array('url' => 'usage.php', 'name' => $langUsage);
$nameTools = $langGroupUsage;
$tool_content = '';
$head_content = '<script type="text/javascript" src="../auth/sorttable.js"></script>';

$i = 0;
$q = db_query("SELECT id, name, tutor, maxStudent FROM student_group", $currentCourse);
if (mysql_num_rows($q) > 0) {
        $tool_content .= "<table class='GroupList sortable' id='a'>
		<tbody>
		<tr>
		<th colspan='2' class='GroupHead'><div align='left'>$langGroupName</div></th>
		<th width='15%' class='GroupHead'>$langGroupTutor</th>
		<th class='GroupHead'>$langRegistered</th>
		<th class='GroupHead'>$langMax</th>
		</tr>";
	while ($group = mysql_fetch_array($q)) {
		// Count students registered in each group
		$resultRegistered = db_query("SELECT id FROM user_group WHERE team = $group[id]", $currentCourseID);
		$countRegistered = mysql_num_rows($resultRegistered);
		if ($i % 2 == 0) {
			$tool_content .= "<tr>";
		} else {
			$tool_content .= "<tr class='odd'>";
		}
		$tool_content .= "<td width='2%'>
		<img src='../../template/classic/img/arrow_grey.gif' alt='' /></td><td>
		<div align='left'>
		<a href='../group/group_usage.php?module=usage&amp;userGroupId=".$group["id"]."'>".$group["name"]."</a></div></td>";
		$tool_content .= "<td width='35%'>".uid_to_name($group['tutor'])."</td>";
      		$tool_content .= "<td><div class=\"cellpos\">".$countRegistered."</div></td>";
		if ($group['maxStudent'] == 0) {
			$tool_content .= "<td><div class='cellpos'>-</div></td>";
		} else {
			$tool_content .= "
      			<td><div class='cellpos'>".$group["maxStudent"]."</div></td>";
		}
    		$tool_content .= '</tr>';
		$i++;
        }
        $tool_content .= "</tbody></table>";
} else {
	$tool_content .= "<p>&nbsp;</p><p class='caution_small'>$langNoGroup</p>";
}


draw($tool_content, 2, 'usage', $head_content);
