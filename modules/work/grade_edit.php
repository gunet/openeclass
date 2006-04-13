<?php 

/*
 +----------------------------------------------------------------------+
 | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
 | Copyright (c) 2003, 2004, 2005 GUNet                                 |
 +----------------------------------------------------------------------+
 | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
 |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
 |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
 |                                                                      |
 | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
 |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
 |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
 +----------------------------------------------------------------------+
 | Submission grading page (available only to professor)                |
 | This file is used when the professor adds or edits                   |
 | submission comments.                                                 |
 +----------------------------------------------------------------------+
*/


$langFiles="work";
$require_current_course = TRUE;
include('../../include/init.php');

include('work_functions.php');

$nameTools = $langGradeWork;
mysql_select_db($currentCourseID);

if ($is_adminOfCourse and isset($_GET['assignment']) and isset($_GET['submission'])) {
		$assign = get_assignment_details($_GET['assignment']);
		$navigation[] = array("url"=>"work.php", "name"=>$langWorks);
		$navigation[] = array("url"=>"work.php?id=$_GET[assignment]", "name"=>$assign['title']);
		begin_page();
		show_edit_form($_GET['assignment'], $_GET['submission'], $assign);
		end_page();
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
	global $m, $langGradeOk;

	if ($sub = mysql_fetch_array(db_query("SELECT * FROM assignment_submit WHERE id = '$sid'"))) {
		?>
			<form method="post" action="work.php">
			<input type="hidden" name="assignment" value="<?= $id ?>">
			<input type="hidden" name="submission" value="<?= $sid ?>">
			<table>
			<tr><td><b><?= $m['username'] ?>:</b></td>
				<td><?= uid_to_name($sub['uid']) ?></td></tr>
			<tr><td><b><?= $m['sub_date'] ?>:</b></td>
				<td><?= $sub['submission_date'] ?></td></tr>
			<tr><td><b><?= $m['filename'] ?>:</b></td>
				<td><?= "<a href='work.php?get=$sub[id]'>$sub[file_name]</a>" ?></td></tr>
			<? if (!empty($sub['group_id'])) {
					echo "<tr><td colspan='2'><b>$m[groupsubmit] ".
						"<a href='../group/group_space.php?userGroupId=$sub[group_id]'>".
						"$m[ofgroup] $sub[group_id]</a></b></td></tr>\n";
			} ?>
			<tr><td><?= $m['grade'] ?>:
			    <input type="text" name="grade" size="3" value="<?= $sub['grade'] ?>"></td>
			</tr>
			<tr><td colspan="2"><?= $m['gradecomments'] ?>:</td></tr>
			<tr><td colspan="2"><textarea cols="60" rows="3" name="comments"><?= $sub['grade_comments']
				?></textarea></td></tr>
			<tr><td colspan="2"><input type="submit" name="grade_comments" value="<?= $langGradeOk ?>"></td></tr>
			</table>
			</form>
		<?
	} else {
		echo "<p>error - no such submission with id $sid</p>\n";
	}
}

?>
