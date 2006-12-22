<?php 

/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	grade_edit.php
 * @version $Id$
	@author: Dionysios G. Synodinos <synodinos@gmail.com>
	@author: Evelthon Prodromou <eprodromou@upnet.gr>
==============================================================================        
*/


$langFiles="work";
$require_current_course = TRUE;

//include('../../include/init.php');

include '../../include/baseTheme.php';

$tool_content = "";

include('work_functions.php');

$nameTools = $langGradeWork;
mysql_select_db($currentCourseID);

if ($is_adminOfCourse and isset($_GET['assignment']) and isset($_GET['submission'])) {
		$assign = get_assignment_details($_GET['assignment']);
		$navigation[] = array("url"=>"work.php", "name"=>$langWorks);
		$navigation[] = array("url"=>"work.php?id=$_GET[assignment]", "name"=>$assign['title']);
		//begin_page();
		show_edit_form($_GET['assignment'], $_GET['submission'], $assign);
		draw($tool_content, 2);
} else {
		header('Location: work.php');
		exit;
}

// Returns an array of the details of assignment $id
function get_assignment_details($id)
{
	return mysql_fetch_array(db_query("SELECT * FROM assignments WHERE id = '$id'"));
}


// Show to professor details of a student's submission and allow editing of fields
// $assign contains an array with the assignment's details
function show_edit_form($id, $sid, $assign)
{
	global $m, $langGradeOk, $tool_content;

	if ($sub = mysql_fetch_array(db_query("SELECT * FROM assignment_submit WHERE id = '$sid'"))) {
		
		$uid_2_name = uid_to_name($sub['uid']);
		if (!empty($sub['group_id'])) {
					$group_submission = "($m[groupsubmit] ".
						"<a href='../group/group_space.php?userGroupId=$sub[group_id]'>".
						"$m[ofgroup] $sub[group_id]</a>)";
			} else $group_submission = "";
		$tool_content .= <<<cData
			<form method="post" action="work.php">
			<input type="hidden" name="assignment" value="${id}">
			<input type="hidden" name="submission" value="${sid}">
			<table>
			<thead>
			<tr><th>${m['username']}:</th>
			<td>${uid_2_name} $group_submission</td></tr>
			<tr><th>${m['sub_date']}:</th>
			<td>${sub['submission_date']}</td></tr>
			<tr><th>${m['filename']}:</th>
				<td><a href='work.php?get=${sub['id']}'>${sub['file_name']}</a></td></tr>
cData;

//			if (!empty($sub['group_id'])) {
//					$tool_content .= "<tr><td colspan='2'><b>$m[groupsubmit] ".
//						"<a href='../group/group_space.php?userGroupId=$sub[group_id]'>".
//						"$m[ofgroup] $sub[group_id]</a></b></td></tr>\n";
//			}
			
			$tool_content .= <<<cData
				<tr><th>${m['grade']}:</th>
			   <td> <input type="text" name="grade" maxlength="3" size="3" value="${sub['grade']}"></td>
			</tr>
		<tr><th>${m['gradecomments']}:</th>
			<td><textarea cols="60" rows="3" name="comments">${sub['grade_comments']}
				</textarea></td></tr>
				</thead></table><br/>
			<input type="submit" name="grade_comments" value="${langGradeOk}">
			</form>
cData;

	} else {
		$tool_content .= "<p>error - no such submission with id $sid</p>\n";
	}
}

?>
