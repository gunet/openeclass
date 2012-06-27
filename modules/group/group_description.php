<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


$require_current_course = TRUE;
$require_help = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

$nameTools = $langAddDescription;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langGroups);
$group_id = isset($_REQUEST['group_id'])?intval($_REQUEST['group_id']):'';

if (isset($_GET['delete'])) {
	$sql = db_query("UPDATE group_members SET description = ''
		WHERE group_id = $group_id AND user_id = $uid", $mysqlMainDb);
	if (mysql_affected_rows() > 0) {
		$tool_content .= "<div class='success'>$langBlockDeleted<br /><br />";
	}
	$tool_content .= "<a href='index.php?course=$course_code'>$langBack</a></div>";	
} else if (isset($_POST['submit'])) {
		$sql = db_query("UPDATE group_members SET description = '".q($_POST['group_desc'])."'
			WHERE group_id = $group_id AND user_id = $uid", $mysqlMainDb);
		if (mysql_affected_rows() > 0) {
			$tool_content .= "<div class='success'>$langRegDone<br /><br />";
		} else {
			$tool_content .= "<div class='caution'>$langNoChanges<br /><br />";
		}
		$tool_content .= "<a href='index.php?course=$course_code'>$langBack</a></div>";	
} else { // display form
	$sql = db_query("SELECT description FROM group_members
			WHERE group_id = $group_id AND user_id = $uid", $mysqlMainDb);
	list($description) = mysql_fetch_array($sql);
	
	$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
	  <table class='FormData' width='99%' align='left'>
	  <tbody>
	  <tr>
	    <th>&nbsp;</th>
	    <td>$langGroupDescInfo</td>
	  </tr>
	  <tr>
	    <th class='left'>$langDescription</th>
		<td><textarea class=auth_input name='group_desc' rows='10' cols='80'>".@$description."</textarea></td>    
	  </tr>
	  <tr>
	    <th>&nbsp;</th>
	    <input type='hidden' name='group_id' value='$group_id'>
	<td><input type='submit' name='submit' value='$langAddModify' /></td>
	  </tr>
	  </tbody>
	  </table>
	  </form>";
}
draw($tool_content, 2);
