<?php
/* ========================================================================
 * Open eClass 2.6
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

/* ========================================================================
	work.php
        @version $Id$
	@author : Dionysios G. Synodinos <synodinos@gmail.com>
	@author : Evelthon Prodromou <eprodromou@upnet.gr>
 * ======================================================================== */


$require_current_course = TRUE;
$require_login = true;

include 'work_functions.php' ;
include '../../include/baseTheme.php';
include '../../include/pclzip/pclzip.lib.php';
include '../../include/lib/fileManageLib.inc.php';
include '../../include/lib/forcedownload.php';

mysql_select_db($currentCourseID);

define('GROUP_DOCUMENTS', true);
$group_id = intval($_REQUEST['group_id']);
include '../document/doc_init.php';

$coursePath = $webDir.'/courses/'.$currentCourseID;
if (!file_exists($coursePath))
	mkdir($coursePath, 0777);

$workPath = $coursePath.'/work';
$groupPath = $coursePath.'/group/'.group_secret($group_id);

$nameTools = $langGroupSubmit;

if (isset($_GET['submit'])) {
	$tool_content .= "<p>$langGroupWorkIntro</p>";
	show_assignments();
	draw($tool_content, 2);
} elseif (isset($_POST['assign'])) {
	submit_work($uid, $group_id, $_POST['assign'], $_POST['file']);
	draw($tool_content, 2);
} else {
	header("Location: work.php?course=$code_cours");
}


// show non-expired assignments list to allow selection
function show_assignments()
{
        global $m, $uid, $group_id, $langSubmit, $langDays, $langNoAssign, $tool_content,
               $langWorks, $currentCourseID, $code_cours, $themeimg;

	$res = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days
		FROM `$currentCourseID`.assignments");
        
	if (mysql_num_rows($res) == 0) {
		$tool_content .=  $langNoAssign;
		return;
	}

	$tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?course=$code_cours' method='post'>
		<input type='hidden' name='file' value='".q($_GET['submit'])."'>
		<input type='hidden' name='group_id' value='$group_id'>
	    <table class='tbl' width='99%'>
	    <tr>
	      <th class='left' width='170'>&nbsp;</th>
	      <td>&nbsp;</td>
	    </tr>
	    <tr>
	      <th class='left'>$langWorks ($m[select]):</th>
	      <td>
	    <table width='99%' align='left'>
	    <tr>
		<th class='left' colspan='2'>$m[title]</th>
		<th align='center' width='30%'>$m[deadline]</th>
		<th align='center' width='10%'>$m[submitted]</th>
		<th align='center' width='10%'>$m[select]</th>
		</tr>";
	
	while ($row = mysql_fetch_array($res)) {
		if (!$row['active']) {
			continue;
		}

		$tool_content .= "<tr><td width=\"1%\">
			<img style='padding-top:2px;' src='$themeimg/arrow.png' alt=''></td>
			<td><div align='left'><a href='work.php?course=$code_cours&amp;id=$row[id]'>".q($row['title'])."</a></td>
			<td align='center'>".nice_format($row['deadline']);
		if ($row['days'] > 1) {
			$tool_content .=  " ($m[in]&nbsp;$row[days]&nbsp;$langDays";
		} elseif ($row['days'] < 0) {
			$tool_content .=  " ($m[expired])";
		} elseif ($row['days'] == 1) {
			$tool_content .=  " ($m[tomorrow])";
		} else {
			$tool_content .=  " ($m[today])";
		}

		$tool_content .= "</div></td>\n      <td align=\"center\">";
		$subm = was_submitted($uid, $group_id, $row['id']);
		if ($subm == 'user') {
			$tool_content .=  $m['yes'];
		} elseif ($subm == 'group') {
			$tool_content .=  $m['by_groupmate'];
		} else {
			$tool_content .=  $m['no'];
		}
		$tool_content .= "</td><td align=\"center\">";
		if ($row['days'] >= 0 and !was_graded($uid, $row['id']) and is_group_assignment($row['id'])) {
			$tool_content .=  "<input type='radio' name='assign' value='$row[id]'>";
		} else {
			$tool_content .=  '-';
		}
		$tool_content .= "</td>\n    </tr>";
	}
	$tool_content .= "\n    </table>";
	$tool_content .= "</td></tr>
	<tr>
	  <th class='left'>".$m['comments'].":</th>
	  <td><textarea name='comments' rows='4' cols='60'>"."</textarea></td>
	</tr>
	<tr>
	  <th>&nbsp;</th>
	  <td><input type='submit' name='submit' value='".q($langSubmit)."'></td>
	</tr>
	</table>
	</form>";
}


// Insert a group work submitted by user uid to assignment id
function submit_work($uid, $group_id, $id, $file) {
	global $groupPath, $langUploadError, $langUploadSuccess,
                $langBack, $m, $currentCourseID, $tool_content, $workPath,
                $group_sql, $mysqlMainDb, $webDir, $code_cours;

        $ext = get_file_extension($file);
	$local_name = greek_to_latin('Group '. $group_id . (empty($ext)? '': '.' . $ext));

        mysql_select_db($mysqlMainDb);
        list($original_filename) = mysql_fetch_row(db_query("SELECT filename FROM document
                                                                WHERE $group_sql AND
                                                                      path = " . autoquote($file)));
	$source = $groupPath.$file;
	$destination = work_secret($id)."/$local_name";

        delete_submissions_by_uid($uid, $group_id, $id, $destination);
        mysql_select_db($mysqlMainDb);
        if (is_dir($source)) {
                $original_filename = $original_filename.'.zip';
                $zip_filename = $webDir . 'courses/temp/'.safe_filename('zip');
                zip_documents_directory($zip_filename, $file, $is_editor);
                $source = $zip_filename;
        }
        if (copy($source, "$workPath/$destination")) {
                db_query("INSERT INTO `$currentCourseID`.assignment_submit (uid, assignment_id, submission_date,
                                     submission_ip, file_path, file_name, comments, group_id, grade_comments)
                                 VALUES ('$uid','$id', NOW(), '$_SERVER[REMOTE_ADDR]', '$destination'," .
                                         quote($original_filename) . ', ' .
                                         autoquote($_POST['comments']) . ", $group_id, '')",
                        $currentCourseID);

		$tool_content .="<p class=\"success\">$langUploadSuccess
			<br />$m[the_file] \"$original_filename\" $m[was_submitted]<br />
			<a href='work.php?course=$code_cours'>$langBack</a></p><br />";
	} else {
		$tool_content .="<p class=\"caution\">$langUploadError<br />
		<a href='work.php?course=$code_cours'>$langBack</a></p><br />";
	}
}
