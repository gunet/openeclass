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
 | Student work utility functions                                       |
 +----------------------------------------------------------------------+
*/


// Print a two-cell table row with that title, if the content is non-empty
function table_row($title, $content)
{
	if (trim($content) != '') {
		echo "<tr><td><b>$title:</b></td><td>".
			htmlspecialchars($content)."</td></tr>";
	}
}


// Find secret subdir of this assignment - if a secret subdir isn't set,
// use the assignment's id instead. Also insures that secret subdir exists
function work_secret($id)
{
	global $currentCourseID, $workPath;
	
	$res = db_query("SELECT secret_directory FROM assignments WHERE id = '$id'", $currentCourseID);
	if ($res) {
		$secret = mysql_fetch_row($res);
		if (!empty($secret[0])) {
			$s = $secret[0];
		} else {
			$s = $id;
		}
		if (!is_dir("$workPath/$s")) {
			mkdir("$workPath/$s",0777);
		}
		return $s;
	} else {
		die("Error: group $gid doesn't exist");
	}
}


// Is this a group assignment?
function is_group_assignment($id)
{
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
	global $m;

	$res = db_query("SELECT * FROM assignment_submit WHERE
		uid = '$uid' AND assignment_id = '$id'");
	while ($row = mysql_fetch_array($res)) {
		@unlink("$GLOBALS[workPath]/$row[file_path]");
		db_query("DELETE FROM assignment_submit WHERE id = '$row[id]'");
		echo "<p>$m[deleted_work_by_user] \"$row[file_name]\".</p>";
	}
	$res = db_query("SELECT * FROM assignment_submit WHERE
		group_id = '$gid' AND assignment_id = '$id'");
	while ($row = mysql_fetch_array($res)) {
		@unlink("$GLOBALS[workPath]/$row[file_path]");
		db_query("DELETE FROM assignment_submit WHERE id = '$row[id]'");
		echo "<p>$m[deleted_work_by_group] \"$row[file_name]\".</p>";
	}
}


// Translate Greek characters to Latin
function greek_to_latin($string)
{
	return str_replace(
		array(
			'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð',
			'ñ', 'ó', 'ô', 'õ', 'ö', '÷', 'ø', 'ù', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È',
			'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ó', 'Ô', 'Õ', 'Ö', '×', 'Ø', 'Ù',
			'ò', 'Ü', 'Ý', 'Þ', 'ß', 'ý', 'ü', 'þ', '¶', '¸', '¹', 'º', '¾', '¼', '¿', 'ú',
			'À', 'û', 'à', 'Ú', 'Û'),
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
	global $currentCourseID;

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
	global $m;

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
	global $uid, $m, $currentCourseID, $langSubmittedAndGraded;

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
	echo "<p><b>$notice</b></p><table>";
	table_row($m['grade'], $sub['grade']);
	table_row($m['gradecomments'], $sub['grade_comments']);
	table_row($m['sub_date'], $sub['submission_date']);
	table_row($m['filename'], $sub['file_name']);
	table_row($m['comments'], $sub['comments']);
	if ($sub['uid'] != $uid) {
		echo "<tr><td colspan='2'>$m[submitted_by_other_member] ".
			"<a href='../group/group_space.php?userGroupId=$sub[group_id]'>".
			"$m[your_group]</a> (".uid_to_name($sub['uid']).")</td></tr>\n";
	}
	echo "</table>";
	mysql_select_db($currentCourseID);
}


// Check if a file has been submitted by user uid or group gid
// for assignment id. Returns 'user' if by user, 'group' if by group
function was_submitted($uid, $gid, $id)
{
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
