<?
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
        $tool_content .= "
                <table class='sortable' width='99%' id='b'>
		<tr>
		  <th class='left'>$langGroupName</th>
		  <th>$langGroupTutor</th>
		  <th class='center'>$langRegistered</th>
		  <th class='center'>$langMax</th>
		</tr>\n";
	while ($group = mysql_fetch_array($q)) {
		// Count students registered in each group
		$resultRegistered = db_query("SELECT id FROM user_group WHERE team = $group[id]", $currentCourseID);
		$countRegistered = mysql_num_rows($resultRegistered);
		if ($i % 2 == 0) {
			$tool_content .= "                  <tr class='even'>\n";
		} else {
			$tool_content .= "                  <tr class='odd'>\n";
		}
		$tool_content .= "                    <td class='arrow'><a href='../group/group_usage.php?module=usage&amp;userGroupId=".$group["id"]."'>".$group["name"]."</a></td>\n";
		$tool_content .= "                    <td>".uid_to_name($group['tutor'])."</td>\n";
      		$tool_content .= "                    <td class='center'>$countRegistered</td>\n";
		if ($group['maxStudent'] == 0) {
			$tool_content .= "                    <td class='center'>-</td>\n";
		} else {
			$tool_content .= "                    <td class='center'>$group[maxStudent]</td>\n";
		}
    		$tool_content .= "                  </tr>\n";
		$i++;
        }
        $tool_content .= "                  </table>\n";
} else {
	$tool_content .= "    <p class='caution_small'>$langNoGroup</p>";
}

draw($tool_content, 2, 'usage', $head_content);
