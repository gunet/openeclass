<?php

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

/*===========================================================================
	work.php
    @version $Id$
	@author : Dionysios G. Synodinos <synodinos@gmail.com>
	@author : Evelthon Prodromou <eprodromou@upnet.gr>
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


$require_current_course = TRUE;
$require_login = true;

include 'work_functions.php' ;
include '../../include/baseTheme.php';
include '../../include/lib/forcedownload.php';

$tool_content = "";

mysql_select_db($currentCourseID);
$gid = user_group($uid);

$coursePath = $webDir."/courses/".$currentCourseID;
if (!file_exists($coursePath))
	mkdir("$coursePath",0777);

$workPath = $coursePath."/work";
$groupPath = $coursePath."/group/".group_secret($gid);

$nameTools = $langGroupSubmit;

if (isset($_GET['submit'])) {
	$tool_content .= "<p>$langGroupWorkIntro</p>";
	show_assignments();
	draw($tool_content, 2, 'work');
} elseif (isset($_POST['assign'])) {
	submit_work($uid, $_POST['assign'], $_POST['file']);
	draw($tool_content, 2, 'work');
} else {
	header("Location: work.php");
}


// show non-expired assignments list to allow selection
function show_assignments()
{
	global $m, $uid, $langSubmit, $langDays, $langNoAssign, $tool_content, $langWorks;

	$res = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days
		FROM assignments");

	if (mysql_num_rows($res) == 0) {
		$tool_content .=  $langNoAssign;
		return;
	}



$tool_content .= <<<cData
	<form action="group_work.php" method="post">
	<input type="hidden" name="file" value="${_GET['submit']}">
    <table class="FormData" width="99%">
    <tbody>
    <tr>
      <th class="left" width="170">&nbsp;</th>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <th class="left">$langWorks (${m['select']}):</th>
      <td>

    <table width="99%" class="WorkSum align="left">
    <thead>
    <tr>
		<th class="left" colspan="2">${m['title']}</th>
		<th align="center" width="30%">${m['deadline']}</th>
		<th align="center" width="10%">${m['submitted']}</th>
		<th align="center" width="10%">${m['select']}</th>
		</tr>
	</thead>
	<tbody>
cData;
	while ($row = mysql_fetch_array($res)) {
		if (!$row['active']) {
			continue;
		}

$tool_content .= "
    <tr>
      <td width=\"1%\"><img style='border:0px; padding-top:2px;' src='../../template/classic/img/arrow_grey.gif' title='bullet'></td>
      <td><div align=\"left\"><a href=\"work.php?id=".$row['id']."\">".htmlspecialchars($row['title'])."</a></td>
      <td align=\"center\">".nice_format($row['deadline']);

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

						$subm = was_submitted($uid, user_group($uid), $row['id']);
						if ($subm == 'user') {
							$tool_content .=  $m['yes'];
						} elseif ($subm == 'group') {
							$tool_content .=  $m['by_groupmate'];
						} else {
							$tool_content .=  $m['no'];
						}


				$tool_content .= "</td>\n      <td align=\"center\">";

						if ($row['days'] >= 0
							and !was_graded($uid, $row['id'])
							and is_group_assignment($row['id'])) {
							$tool_content .=  "<input type='radio' name='assign' value='$row[id]'>";
						} else {
							$tool_content .=  '-';
						}

					$tool_content .= "</td>\n    </tr>";

	}


		$tool_content .= "\n    </tbody>\n    </table>";



		$tool_content .= "
      </td>
    </tr>
    <tr>
      <th class=\"left\">".$m['comments'].":</th>
      <td><textarea name=\"comments\" rows=\"4\" cols=\"60\">"."</textarea></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type=\"submit\" name=\"submit\" value=\"".$langSubmit."\"></td>
    </tr>
    </tbody>
    </table>

    </form>";

}


// Insert a group work submitted by user uid to assignment id
function submit_work($uid, $id, $file) {
	global $groupPath, $langUploadError, $langUploadSuccess,
		$langBack, $m, $currentCourseID, $tool_content, $workPath;

	$group = user_group($uid);

        $ext = get_file_extension($file);
	$local_name = greek_to_latin('Group '. $group . (empty($ext)? '': '.' . $ext));

        $r = mysql_fetch_row(db_query('SELECT filename FROM group_documents WHERE path = ' .
                                      autoquote($file)));
        $original_filename = $r[0];

	$source = $groupPath.$file;
	$destination = work_secret($id)."/$local_name";


        delete_submissions_by_uid($uid, $group, $id, $destination);
	if (copy($source, "$workPath/$destination")) {
		db_query("INSERT INTO assignment_submit (uid, assignment_id, submission_date,
			             submission_ip, file_path, file_name, comments, group_id)
                          VALUES ('$uid','$id', NOW(), '$_SERVER[REMOTE_ADDR]', '$destination'," .
				quote($original_filename) . ', ' .
                                autoquote($_POST['comments']) . ", $group)",
                        $currentCourseID);

		$tool_content .="<p class=\"success_small\">$langUploadSuccess<br />$m[the_file] \"$original_filename\" $m[was_submitted]<br /><a href='work.php'>$langBack</a></p><br />";
	} else {
		$tool_content .="<p class=\"caution_small\">$langUploadError<br /><a href='work.php'>$langBack</a></p><br />";
	}
}

