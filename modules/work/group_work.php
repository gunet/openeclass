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
 | Group work submission page                                           |
 +----------------------------------------------------------------------+
*/

$langFiles="work";
$require_current_course = TRUE;
include('../../include/init.php');

include('work_functions.php');

mysql_select_db($currentCourseID);
$gid = user_group($uid);
$workPath = $webDir.$currentCourseID."/work";
$groupPath = $webDir.$currentCourseID."/group/".group_secret($gid);

$nameTools = $langGroupSubmit;

if (isset($_GET['submit'])) {
	begin_page();
	printf("<p>$langGroupWorkIntro</p>", basename($_GET['submit']));
	show_assignments();	
	end_page();
} elseif (isset($_POST['assign'])) {
	begin_page();
	submit_work($uid, $_POST['assign'], $_POST['file']);
	end_page();
} else {
	header("Location: work.php");
}


// show non-expired assignments list to allow selection
function show_assignments()
{
	global $m, $uid, $langSubmit, $langDays, $langNoAssign;
		
	$res = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days
		FROM assignments");

	if (mysql_num_rows($res) == 0) {
		echo $langNoAssign;
		return;
	}

?>
	<form action="group_work.php" method="post">
	<input type="hidden" name="file" value="<?= $_GET['submit'] ?>">
	<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
		<tr bgcolor="#E6E6E6">
			<th align="center"><?= $m['title'] ?></th>
			<th align="center"><?= $m['deadline'] ?></th>
			<th align="center" width="10%"><?= $m['submitted'] ?></th>
			<th align="center" width="10%"><?= $m['select'] ?></th>
		</tr>
<?

	while ($row = mysql_fetch_array($res)) {
		if (!$row['active']) {
			continue;
		}
?>
		<tr>
			<td><a href="work.php?id=<?= $row['id'] ?>"
				><?= htmlspecialchars($row['title']) ?></a></td>
			<td width="30%"><?
				echo $row['deadline'];
				if ($row['days'] > 1) {
					echo " ($m[in]&nbsp;$row[days]&nbsp;$langDays";
				} elseif ($row['days'] < 0) {
					echo " ($m[expired])";
				} elseif ($row['days'] == 1) {
					echo " ($m[tomorrow])";
				} else {
					echo " ($m[today])";
				}
				?></td>
				<td width="10%" align="center">
					<?
						$subm = was_submitted($uid, user_group($uid), $row['id']);
						if ($subm == 'user') {
							echo $m['yes'];
						} elseif ($subm == 'group') {
							echo $m['by_groupmate'];
						} else {
							echo $m['no'];
						}
					?>
				</td>
				<td align="center">
					<?
						if ($row['days'] >= 0
							and !was_graded($uid, $row['id'])
							and is_group_assignment($row['id'])) {
							echo "<input type='radio' name='assign' value='$row[id]'>";
						} else {
							echo '-';
						}
					?>
				</td>
		</tr>
<?
	}
?>
		<tr><td colspan='4'>
		<strong><?= $m['comments'] ?>:</strong><br>
		<textarea name='comments' rows='4' cols='60'></textarea>
	</table>
	<input type='submit' name='submit' value='<?= $langSubmit ?>'></form>
<?

}


// Insert a group work submitted by user uid to assignment id
function submit_work($uid, $id, $file) {
	global $groupPath, $REMOTE_ADDR, $langUploadError, $langUpload,
		$langBack, $m, $currentCourseID;

	$group = user_group($uid);

	// $file = cleanup_filename($file);
	// $local_name = greek_to_latin(basename_noext($file).'_group '.$group.'.'.extension($file));
	$local_name = greek_to_latin('Group '.$group.'.'.extension($file));

	$source = "$groupPath/$file";
	$destination = work_secret($id)."/$local_name";

	if (copy($source, "$GLOBALS[workPath]/$destination")) {
		echo "<p>$langUpload</p>";
		delete_submissions_by_uid($uid, $group, $id);
		db_query("INSERT INTO assignment_submit (uid, assignment_id, submission_date,
			submission_ip, file_path, file_name, comments, group_id) 
			VALUES ('$uid','$id', NOW(), '$REMOTE_ADDR', '$destination',
				'".basename($file)."', '$_POST[comments]', '$group')", $currentCourseID);
		echo "<p>$m[the_file] \"".basename($file)."\" $m[was_submitted]</p>";
	} else {
		echo "<p>$langUploadError</p>";
	}
	echo "<p><center><a href='work.php'>$langBack</a></center></p>";	
}

?>
