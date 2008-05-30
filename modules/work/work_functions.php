<?php 

/*=============================================================================
       	GUnet eClass 2.0 
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
	work_functions.php
	@version $Id$
	@author: Dionysios G. Synodinos <synodinos@gmail.com>
	@author: Evelthon Prodromou	<eprodromou@upnet.gr>
==============================================================================        
        @Description: Main script for the work tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights

 	The user can : - navigate through files and directories.
                       - upload a file
                       - delete, copy a file or a directory
                       - edit properties & content (name, comments, 
			 html content)

 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
   	2) Define the directory to display
  	3) Read files and directories from the directory defined in part 2
  	4) Display all of that on an HTML page
 
  	@TODO: eliminate code duplication between document/document.php, scormdocument.php
==============================================================================
*/


// Print a two-cell table row with that title, if the content is non-empty
function table_row($title, $content)
{
	global $tool_content;
	if (trim($content) != '') {
		$tool_content .= "
    <tr>
      <th class=\"left\">$title:</th>
      <td>".htmlspecialchars($content)."</td>
    </tr>";
	}
}


// Find secret subdir of this assignment - if a secret subdir isn't set,
// use the assignment's id instead. Also insures that secret subdir exists
function work_secret($id)
{
	global $currentCourseID, $workPath, $tool_content, $coursePath;
	
	$res = db_query("SELECT secret_directory FROM assignments WHERE id = '$id'", $currentCourseID);
	if ($res) {
		$secret = mysql_fetch_row($res);
		if (!empty($secret[0])) {
			$s = $secret[0];
		} else {
			$s = $id;
		}
		if (!is_dir("$workPath/$s")) {
			if (!file_exists($coursePath)) {
				mkdir("$coursePath",0777);
			}
			mkdir("$workPath",0777);
			mkdir("$workPath/$s",0777);
			$tool_content .= "$workPath/$s";
		}
		return $s;
	} else {
		die("Error: group $gid doesn't exist");
	}
}


// Is this a group assignment?
function is_group_assignment($id)
{
	global $tool_content;
	$res = db_query("SELECT group_submissions FROM assignments WHERE id = '$id'");
	if ($res) {
		$row = mysql_fetch_row($res);
		if ($row[0] == 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	} else {
		die("Error: assignment $id doesn't exist");
	}
}


// Delete submissions to exercise $id if submitted by user $uid or group $gid
function delete_submissions_by_uid($uid, $gid, $id)
{
	global $m, $tool_content;
	$return="";
	$res = db_query("SELECT * FROM assignment_submit WHERE
		uid = '$uid' AND assignment_id = '$id'");
	while ($row = mysql_fetch_array($res)) {
		@unlink("$GLOBALS[workPath]/$row[file_path]");
		db_query("DELETE FROM assignment_submit WHERE id = '$row[id]'");
		$return .= "$m[deleted_work_by_user] \"$row[file_name]\"";
	}
	$res = db_query("SELECT * FROM assignment_submit WHERE
		group_id = '$gid' AND assignment_id = '$id'");
	while ($row = mysql_fetch_array($res)) {
		@unlink("$GLOBALS[workPath]/$row[file_path]");
		db_query("DELETE FROM assignment_submit WHERE id = '$row[id]'");
		$return .= "$m[deleted_work_by_group] \"$row[file_name]\".";
	}
	return $return;
}


// Translate Greek characters to Latin
function greek_to_latin($string)
{
	return str_replace(
		array(
			'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π',
			'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ',
			'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω',
			'ς', 'ά', 'έ', 'ή', 'ί', 'ύ', 'ό', 'ώ', 'Ά', 'Έ', 'Ή', 'Ί', 'Ύ', 'Ό', 'Ώ', 'ϊ',
			'ΐ', 'ϋ', 'ΰ', 'Ϊ', 'Ϋ'),
		array(
			'a', 'b', 'g', 'd', 'e', 'z', 'i', 'th', 'i', 'k', 'l', 'm', 'n', 'x', 'o', 'p',
			'r', 's', 't', 'y', 'f', 'x', 'ps', 'o', 'A', 'B', 'G', 'D', 'E', 'Z', 'H', 'Th',
			'I', 'K', 'L', 'M', 'N', 'X', 'O', 'P', 'R', 'S', 'T', 'Y', 'F', 'X', 'Ps', 'O',
			's', 'a', 'e', 'i', 'i', 'y', 'o', 'o', 'A', 'E', 'H', 'I', 'Y', 'O', 'O', 'i',
			'i', 'y', 'y', 'I', 'Y'),
		$string);
}


// Returns an array of a group's members' uids
function group_members($gid)
{	
	global $currentCourseID, $tool_content;

	$members = array();
	$res = db_query("SELECT user FROM user_group WHERE team = '$gid'",
		$currentCourseID);
	while ($user = mysql_fetch_row($res)) {
		$members[] = $user[0];
	}
	return $members;
}


// Returns a string of the names and student numbers of
// a group's members
function group_member_names($gid)
{
	global $tool_content;
	$start = TRUE;
	$names= '';
	foreach (group_members($gid) as $id) {
		if ($start) {
			$start = FALSE;
		} else {
			$names .= ', ';
		}
		$names .= uid_to_name($id);
		if ($am = uid_to_am($id)) {
			$names .= " ($am)";
		}
	}
	return $names;
}


// Find submission by a user (or the user's group)
function find_submission($uid, $id)
{
	global $tool_content;
	if (is_group_assignment($id)) {
		$gid = user_group($uid);
		$res = db_query("SELECT id FROM assignment_submit
				WHERE assignment_id = '$id'
				AND (uid = '$uid' OR group_id = '$gid')");
	} else {
		$res = db_query("SELECT id FROM assignment_submit
				WHERE assignment_id = '$id' AND uid = '$uid'");
	}
	if ($res) {
		$row = mysql_fetch_row($res);
		return $row[0];
	} else {
		return FALSE;
	}
}



// Returns grade, if submission has been graded, or "Yes" (translated) if
// there is a comment by the professor but no grade, or FALSE if neither
// grade or professor comment is set
function submission_grade($subid)
{
	global $m, $tool_content;

	$res = mysql_fetch_row(db_query("SELECT grade, grade_comments
		FROM assignment_submit WHERE id = '$subid'"));
	if ($res) {
		$grade = trim($res[0]);
		if (!empty($grade)) {
			return $grade;
		} elseif (!empty($res[1])) {
			return $m['yes'];
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}


// Check if a file has been submitted by user uid or by the user's group,
// and has been graded. Returns the submission id or the whole
// submission details row (depending on ret_val), or FALSE if no graded
// assignments were found.
function was_graded($uid, $id, $ret_val = FALSE)
{
	global $tool_content;
	$gid = user_group($uid);
	$res = db_query("SELECT * FROM assignment_submit
			WHERE assignment_id = '$id'
			AND (uid = '$uid' OR group_id = '$gid')");
	if ($res) {
		while ($row = mysql_fetch_array($res)) {
			if ($row['grade']) {
				if ($ret_val) {
					return $row;
				} else {
					return $row['id'];
				}
			}
		}
	} else {
		return FALSE;
	}
}


// Show details of a submission
function show_submission_details($id)
{
	global $uid, $m, $currentCourseID, $langSubmittedAndGraded, $tool_content;

	$sub = mysql_fetch_array(
		db_query("SELECT * FROM assignment_submit
			WHERE id = '$id'"));
	if (!$sub) {
		die("Error: submission $id doesn't exist.");
	}
	if (!empty($sub['grade']) or !empty($sub['grade_comment'])) {
		$graded = TRUE;
		$notice = $langSubmittedAndGraded;
	} else {
		$graded = FALSE;
		$notice = $GLOBALS['langSubmitted'];
	}
	
	if ($sub['uid'] != $uid) {
		$sub_notice = "$m[submitted_by_other_member] ".
			"<a href='../group/group_space.php?userGroupId=$sub[group_id]'>".
			"$m[your_group]</a> (".uid_to_name($sub['uid']).")";
	} else $sub_notice = "";
	
	$tool_content .= "
    <br />
    <table width=\"99%\" class=\"FormData\">
    <tbody>";
	$tool_content .= "
    <tr>
      <th width=\"220\">&nbsp;</th>
      <td><b>$m[SubmissionWorkInfo]</b></td>
    </tr>
    <tr>
      <th class=\"left\">$m[SubmissionStatusWorkInfo]:</th>
      <td>$notice</td>
    </tr>";
	table_row($m['grade'], $sub['grade']);
	table_row($m['gradecomments'], $sub['grade_comments']);
	table_row($m['sub_date'], $sub['submission_date']);
	table_row($m['filename'], $sub['file_name']);
	table_row($m['comments'], $sub['comments']);
	$tool_content .= "
    </tbody>
    </table>
    $sub_notice";
	mysql_select_db($currentCourseID);
}


// Check if a file has been submitted by user uid or group gid
// for assignment id. Returns 'user' if by user, 'group' if by group
function was_submitted($uid, $gid, $id)
{
	global $tool_content;
	if (mysql_num_rows(db_query(
		"SELECT id FROM assignment_submit WHERE assignment_id = '$id'
			AND uid = '$uid'"))) {
		return 'user';
	}
	if (mysql_num_rows(db_query(
		"SELECT id FROM assignment_submit WHERE assignment_id = '$id'
			AND group_id = '$gid'"))) {
		return 'group';
	}
	return FALSE;
}


// Remove extension and directory from filename
function basename_noext($f)
{
	return preg_replace('{\.[^\.]*$}', '', basename($f));
}


// return the extension of a file name
function extension($f)
{
	$old = $f;
	$f = preg_replace('/^.*\./', '', $f);
	if ($f == "php") {
		return "txt";
	} elseif ($f == $old) {
                
//	return extension(add_ext_on_mime($old));
	} else {
		return $f;
	}
}


// Disallow '..' and initial '/' in filenames
function cleanup_filename($f)
{
	if (preg_match('{/\.\./}', $f) or
	    preg_match('{^\.\./}', $f)) {
		die("Error: up-dir detected in filename: $f");
	}
	$f = preg_replace('{^/+}', '', $f);
	return preg_replace('{//}', '/', $f);
}

?>
