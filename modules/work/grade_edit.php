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

/*===========================================================================
	grade_edit.php
 * @version $Id$
	@author: Dionysios G. Synodinos <synodinos@gmail.com>
	@author: Evelthon Prodromou <eprodromou@upnet.gr>
==============================================================================        
*/

$require_current_course = TRUE;

include '../../include/baseTheme.php';
include 'work_functions.php';
include '../group/group_functions.php';

$nameTools = $m['grades'];
mysql_select_db($mysqlMainDb);

if ($is_editor and isset($_GET['assignment']) and isset($_GET['submission'])) {
		$assign = get_assignment_details($_GET['assignment']);
		$navigation[] = array("url"=>"work.php?course=$code_cours", "name"=>$langWorks);
		$navigation[] = array("url"=>"work.php?course=$code_cours&amp;id=$_GET[assignment]", "name"=>$m['WorkView']);
		show_edit_form($_GET['assignment'], $_GET['submission'], $assign);
		draw($tool_content, 2);
} else {
		header('Location: work.php?course='.$code_cours);
		exit;
}

// Returns an array of the details of assignment $id
function get_assignment_details($id)
{
    global $cours_id;
	return mysql_fetch_array(db_query("SELECT * FROM assignments WHERE course_id = $cours_id AND id = '$id'"));
}


// Show to professor details of a student's submission and allow editing of fields
// $assign contains an array with the assignment's details
function show_edit_form($id, $sid, $assign)
{
	global $m, $langGradeOk, $tool_content, $langGradeWork, $code_cours;

	if ($sub = mysql_fetch_array(db_query("SELECT * FROM assignment_submit WHERE id = '$sid'"))) {		
		$uid_2_name = display_user($sub['uid']);
		if (!empty($sub['group_id'])) {
				$group_submission = "($m[groupsubmit] ".
					"<a href='../group/group_space.php?course=$code_cours&amp;group_id=$sub[group_id]'>".
					"$m[ofgroup] ".gid_to_name($sub['group_id'])."</a>)";
		} else $group_submission = "";
			$tool_content .= "
			<form method='post' action='work.php?course=$code_cours'>
			<input type='hidden' name='assignment' value='$id'>
			<input type='hidden' name='submission' value='$sid'>
                        <fieldset>
                        <legend>$m[addgradecomments]</legend>
			<table width='99%' class='tbl'>
			<tr>
			  <th class='left' width='180'>${m['username']}:</th>
			  <td>${uid_2_name} $group_submission</td></tr>
			<tr>
			  <th class='left'>${m['sub_date']}:</th>
			  <td>${sub['submission_date']}</td></tr>
			<tr>
			  <th class='left'>${m['filename']}:</th>
			  <td><a href='work.php?course=$code_cours&amp;get=${sub['id']}'>${sub['file_name']}</a></td>
			</tr>";
		        $tool_content .= "<tr>
			  <th class='left'>$m[grade]:</th>
			  <td><input type='text' name='grade' maxlength='3' size='3' value='$sub[grade]'></td>
			</tr>
			<tr>
			  <th class='left'>$m[gradecomments]:</th>
			  <td><textarea cols='60' rows='3' name='comments'>$sub[grade_comments]</textarea></td>
			</tr>
			<tr>
			  <th class='left'>&nbsp;</th>
			  <td><input type='submit' name='grade_comments' value='$langGradeOk'></td>
			</tr>
			</table>
                        </fieldset>
			</form>
			<br/>";
	} else {
		$tool_content .= "<p class='caution'>error - no such submission with id $sid</p>\n";
	}
}
?>
