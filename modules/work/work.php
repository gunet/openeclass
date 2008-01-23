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


$langFiles = "work";
$require_current_course = TRUE;
$require_login = true;
$require_help = TRUE;
$helpTopic = 'Work';

include '../../include/baseTheme.php';

$head_content = "
<script>
function confirmation (name)
{

    if (confirm(\"$langDelWarn1 \"+ name + \". $langWarnForSubmissions. $langDelSure \"))
        {return true;}
    else
        {return false;}
}
</script>
";
// For using with the pop-up calendar
include 'jscalendar.inc.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_ASSIGN');
/**************************************/

$tool_content = "";

include('work_functions.php');

$workPath = $webDir."courses/".$currentCourseID."/work";

if ($is_adminOfCourse) { //Only course admins can download assignments
  if (isset($get)) {
	send_file($get);
  }

  if (isset($download)) {
	include "../../include/pclzip/pclzip.lib.php";
	download_assignments($download);
  }
}

$nameTools = $langWorks;
mysql_select_db($currentCourseID);

include('../../include/lib/fileUploadLib.inc.php');
include('../../include/lib/fileManageLib.inc.php');

if ($language == 'greek')
$lang_editor='gr';
else
$lang_editor='en';

$head_content .= <<<hContent
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "en";
        _editor_skin = "silva";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>
hContent;

//-------------------------------------------
// main program
//-------------------------------------------

if ($is_adminOfCourse) {
	if (isset($grade_comments)) {
		submit_grade_comments($assignment, $submission, $grade, $comments);
	} elseif (isset($add)) {
		$nameTools = $langNewAssign;
		$navigation[] = array("url"=>"work.php", "name"=> $langWorks);
		new_assignment();
	} elseif (isset($sid)) {
		show_submission($sid);
	} elseif (isset($_POST['new_assign'])) {
		add_assignment($title, $comments, $desc, "$WorkEnd",
		$group_submissions);
		show_assignments();
	} elseif (isset($grades)) {
		submit_grades($grades_id, $grades);
	} elseif (isset($id)) {
		if (isset($choice)) {
			if ($choice == 'disable') {
				db_query("UPDATE assignments SET active = '0' WHERE id = '$id'");
				show_assignments($langAssignmentDeactivated);
			} elseif ($choice == 'enable') {
				db_query("UPDATE assignments SET active = '1' WHERE id = '$id'");
				show_assignments($langAssignmentActivated);
			} elseif ($choice == 'delete') {
				//				show_delete_assignment($id);
				die("invalid option");
			} elseif ($choice == "do_delete") {
				$navigation[] = array("url"=>"work.php", "name"=> $langWorks);
				delete_assignment($id);
			} elseif ($choice == 'edit') {
				show_edit_assignment($id);
			} elseif ($choice == 'do_edit') {
				edit_assignment($id);
			} elseif ($choice = 'plain') {
				show_plain_view($id);
			}
		} else {
			show_assignment($id);
		}
	} else {
		show_assignments();
	}
} else {
	if (isset($id)) {
		if (isset($work_submit)) {
			submit_work($id);
		} else {
			show_student_assignment($id);
		}
	} else {
		show_student_assignments();
	}
}


draw($tool_content, 2, 'work', $head_content.$local_head);

//-------------------------------------
// end of main program
//-------------------------------------

// Show details of a student's submission to professor
function show_submission($sid)
{
	global $tool_content, $langWorks, $langSubmissionDescr, $langNotice3;

	$nameTools = $langWorks;
	$navigation[] = array("url"=>"work.php", "name"=> $langWorks);

	if ($sub = mysql_fetch_array(db_query("SELECT * FROM assignment_submit WHERE id = '$sid'"))) {

		$tool_content .= "<p>$langSubmissionDescr".
		uid_to_name($sub['uid']).
		$sub['submission_date'].
		"<a href='$GLOBALS[urlServer]$GLOBALS[currentCourseID]".
		"/work/$sub[file_path]'>$sub[file_name]</a>";
		if (!empty($sub['comments'])) {
			$tool_content .=  " $langNotice3: $sub[comments]";
		}
		$tool_content .=  "</p>\n";
	} else {
		$tool_content .= "<p>error - no such submission with id $sid</p>\n";
	}
}


// insert the assignment into the database
function add_assignment($title, $comments, $desc, $deadline, $group_submissions)
{
	global $tool_content, $workPath;

	$secret = uniqid("");
	db_query("INSERT INTO assignments
		(title, description, comments, deadline, submission_date, secret_directory,
			group_submissions) VALUES
		('".mysql_real_escape_string($title)."', '".mysql_real_escape_string($desc)."', '".mysql_real_escape_string($comments)."', '$deadline', NOW(), '$secret',
			'".mysql_real_escape_string($group_submissions)."')");
	mkdir("$workPath/$secret",0777);
}



function submit_work($id) {

	global $tool_content, $workPath, $uid, $stud_comments, $group_sub, $REMOTE_ADDR,
	$langUpload, $langBack, $langWorks, $langUploadError, $currentCourseID;

	//DUKE Work submission bug fix.
	//Do not allow work submission if:
	//	> after work deadline
	//	> user not registered to lesson
	//	> user is guest 
	if(isset($_SESSION["status"])) {
		$status=$_SESSION["status"];
	} else {
		unset($status);
	}
	$submit_ok = FALSE; //Default do not allow submission
	if(isset($uid) && $uid) { //check if loged-in
		if ($GLOBALS['statut'] == 10) { //user is guest
			$submit_ok = FALSE;
		} else { //user NOT guest
			if(isset($status) && isset($status[$_SESSION["dbname"]])) {
				//user is registered to this lesson
				$res = db_query("SELECT (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days 
					FROM assignments WHERE id = '$id'");
				$row = mysql_fetch_array($res);
				if ($row['days'] < 0) {
					$submit_ok = FALSE; //after assignment deadline
				} else { 
					$submit_ok = TRUE; //before deadline
				}
			} else {
				//user NOT registered to this lesson
				$submit_ok = FALSE; 
			}
			
		}
	} //checks for submission validity end here
	
  	$res = db_query("SELECT title FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);
	$nav[] = array("url"=>"work.php?id=$id", "name"=> $row['title']);

  if($submit_ok) { //only if passed the above validity checks...

	$msg1 = delete_submissions_by_uid($uid, -1, $id);

	$local_name = greek_to_latin(uid_to_name($uid));
	$am = mysql_fetch_array(db_query("SELECT am FROM user WHERE user_id = '$uid'"));
	if (!empty($am[0])) {
		$local_name = "$local_name $am[0]";
	}
	$local_name = replace_dangerous_char($local_name);
	$secret = work_secret($id);
	$filename = "$secret/$local_name.".extension($_FILES['userfile']['name']);
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/$filename")) {
		$msg2 = "$langUpload";//to message
		$group_id = user_group($uid, FALSE);
		if ($group_sub == 'yes' and !was_submitted(-1, $group_id, $id)) {
			delete_submissions_by_uid(-1, $group_id, $id);
			db_query("INSERT INTO assignment_submit
				(uid, assignment_id, submission_date, submission_ip, file_path,
				file_name, comments, group_id) VALUES ('$uid','$id', NOW(),
				'$REMOTE_ADDR', '$filename','".$_FILES['userfile']['name'].
				"', '$stud_comments', '$group_id')", $currentCourseID);
		} else {
			db_query("INSERT INTO assignment_submit
				(uid, assignment_id, submission_date, submission_ip, file_path,
				file_name, comments) VALUES ('$uid','$id', NOW(), '$REMOTE_ADDR',
				'$filename','".$_FILES['userfile']['name'].
				"', '$stud_comments')", $currentCourseID);
		}
		
		$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$msg2</b></p><p>$msg1</p>
							<p><a href='work.php'>$langBack</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
		
	} else {
		$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p><b>$langUploadError</b></p>
							<p><a href='work.php'>$langBack</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
		
	}
//	$tool_content .= "<p><center><a href='work.php'>$langBack</a></center></p>";

  } else { // not submit_ok
  	$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p><b>$langExerciseNotPermit<br>$langExerciseNotPermit</b></p>
							<p><a href='work.php'>$langBack</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
  }
}


//  assignment - prof view only
function new_assignment()
{
	global $tool_content, $m, $langAdd;
	global $urlAppend;
	global $desc;
	global $end_cal_Work;
	global $langBack;

	$day	= date("d");
	$month	= date("m");
	$year	= date("Y");

	
	$tool_content .= "
    <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li><a href='work.php'>$langBack</a></li>
    </ul>
    </div>
    ";

	$tool_content .= "
    <form action=\"work.php\" method=\"post\">
    <table width=\"99%\" class='FormData'
    <tbody>
    <tr>
      <th width='150'>&nbsp;</th>
      <td><b>".$m['WorkInfo ']."</b></td>
    </tr>
    <tr>
      <th class=\"left\">".$m['title'].":</th>
      <td><input type=\"text\" name=\"title\" size=\"55\" class='FormData_InputText'></td>
    </tr>

    <tr>
      <th class=\"left\">".$m['description'].":</th>
      <td>"."<textarea id=\"xinha\" name=\"desc\" value=\"$desc\" style='width:100%' rows=\"10\" cols=\"20\">";

	if ($desc)
		$tool_content .= $desc;

	$tool_content .=	"</textarea></td>
    </tr>
    <tr>
      <th class=\"left\">".$m['comments'].":</th>
      <td>"."<textarea name=\"comments\" rows=\"3\" cols=\"55\"></textarea></td>
    </tr>
    <tr>
      <th class=\"left\">".$m['deadline'].":</th>
      <td>$end_cal_Work</td>
    </tr>
    <tr>
      <th class=\"left\">".$m['group_or_user'].":</th>
      <td><input type=\"radio\" name=\"group_submissions\" value=\"0\" checked=\"1\">".$m['user_work']."
      <br><input type=\"radio\" name=\"group_submissions\" value=\"1\">".$m['group_work']."</td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type=\"submit\" name=\"new_assign\" value=\"$langAdd\"></td>
    </tr>
    </thead>
    </table>
    </form>
    <br/>
    ";
}


function date_form($day, $month, $year)
{
	global $tool_content, $langMonthNames;
	$tool_content .=  "<select name=\"fday\">\n";
	for ($i = 1; $i <= 31; $i++) {
		if ($i == $day)
		$tool_content .= "<option value=\"$i\" selected=\"1\">$i</option>\n";
		else
		$tool_content .= "<option value=\"$i\">$i</option>\n";
	}
	$tool_content .= "</select><select name=\"fmonth\">\n";
	for ($i = 1; $i <= 12; $i++) {
		if ($i == $month)
		$tool_content .= "<option value=\"$i\" selected=\"1\">".$langMonthNames['long'][$i-1]."</option>\n";
		else
		$tool_content .= "<option value=\"$i\">".$langMonthNames['long'][$i-1]."</option>\n";
	}
	$tool_content .= "</select><select name=\"fyear\">\n";
	for ($i = date("Y"); $i <= date("Y") + 1; $i++) {
		if ($i == $year)
		$tool_content .= "<option value=\"$i\" selected=\"1\">$i</option>\n";
		else
		$tool_content .= "<option value=\"$i\">$i</option>\n";
	}
	$tool_content .= "</select>\n";
}

//form for editing
function show_edit_assignment($id) {

	global $tool_content, $m, $langEdit, $langWorks, $langBack;
	global $urlAppend;
	global $end_cal_Work_db;

	$res = db_query("SELECT * FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);
	$nav[] = array("url"=>"work.php?id=$id", "name"=> $row['title']);

	$deadline = $row['deadline'];

	$tool_content .= <<<cData
	<form action="work.php" method="post">
	<input type="hidden" name="id" value="$id">
	<input type="hidden" name="choice" value="do_edit">
	<table width="99%" ><thead>
	<tr><th>${m['title']}:</th>
	<td><input type="text" name="title" size="45" value="${row['title']}"></td></tr>
	</thead></table><br/>
	<table width="99%" >
	<thead>
<tr><th>${m['description']}:</th></tr></thead><tbody>
	<tr><td>
<textarea id='xinha' name='desc' value='${row['description']}' style='width:100%' rows='20' cols='60'>
${row['description']}
</textarea>
	</td></tr>
	</tbody></table><br/>
	<table><thead>
	<tr><th>${m['comments']}:</th>
	<td><textarea name="comments" rows="5" cols="65">${row['comments']}</textarea></td></tr>
<tr><th>${m['deadline']}:</th><td>

cData;

	$tool_content .= getJsDeadline($deadline)."</td></tr><tr><th>".$m['group_or_user'].":</th><td>".
	"<input type=\"radio\" name=\"group_submissions\" value=\"0\"";

	if ($row['group_submissions'] == '0')
	$tool_content .= " checked=\"1\" >";
	else $tool_content .= ">";

	$tool_content .= $m['user_work']."<br><input type=\"radio\" name=\"group_submissions\" value=\"1\" ";

	if ($row['group_submissions'] != '0')
	$tool_content .= " checked=\"1\" >";
	else $tool_content .= ">";
	$tool_content .= $m['group_work']."</td></tr>
	</thead></table><br/>
	<input type=\"submit\" name=\"do_edit\" value=\"".$langEdit."\">";
	//////////////////////////////////////////////////////////////////////////////////////
	$tool_content .= "<br/><br/><p><a href='work.php'>$langBack</a></p>";
}

// edit assignment
function edit_assignment($id)
{
	global $tool_content, $langBackAssignment, $langEditSuccess, $langEditError, $langWorks, $langEdit;

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);
	$nav[] = array("url"=>"work.php?id=$id", "name"=> $_POST['title']);

	if (db_query("UPDATE assignments SET title='".mysql_real_escape_string($_POST[title])."',
		description='".mysql_real_escape_string($_POST[desc])."', group_submissions='".mysql_real_escape_string($_POST[group_submissions])."',
		comments='".mysql_real_escape_string($_POST[comments])."', deadline='".mysql_real_escape_string($_POST[WorkEnd])."' WHERE id='$id'")) {

	$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langEditSuccess</b></p>
							<p><a href='work.php?id=$id'>$langBackAssignment \"$_POST[title]\"</a></p>
						</td>
					</tr>
				</tbody>
			</table>";


		} else {
			$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langEditError</b></p>
							<p><a href='work.php?id=$id'>$langBackAssignment \"$_POST[title]\"</a></p>
						</td>
					</tr>
				</tbody>
			</table>";

		}

}

// show delete confirmation
/*function show_delete_assignment($id)
{

global $tool_content, $langDelAssign, $langDelWarn1, $langDelSure, $langDelWarn2, $langDelTitle;
global $langDelMany1, $langDelMany2, $langWorks, $m;

$info = mysql_fetch_array(db_query("SELECT * FROM assignments
WHERE id = '$id'"));
$subs = mysql_num_rows(db_query("SELECT * FROM assignment_submit
WHERE assignment_id = '$id'"));

//	$tool_content .= "<h4>".$langDelAssign."</h4><p>".$langDelWarn1." ".$info['title'].". ".$langDelSure." </p>";

if ($subs > 0) {
$tool_content .= "<p><strong>".$langDelTitle."</strong><br>";
if ($subs == 1)
$return = $langDelWarn2;
else
$return = "$langDelMany1 $subs $langDelMany2";
}
return $return;
$tool_content .= "<p><a href='work.php?id=$id&choice=do_delete'>".$m['yes']."<a> | <a href='work.php'>".$m['no']."<a></p>\n";
}*/


//delete assignment
function delete_assignment($id) {

	global $tool_content, $workPath, $currentCourseID, $webDir, $langBack, $langDeleted;

	$secret = work_secret($id);
	db_query("DELETE FROM assignments WHERE id='$id'");
	db_query("DELETE FROM assignment_submit WHERE assignment_id='$id'");
	@mkdir("$webDir/courses/garbage");
	@mkdir("$webDir/courses/garbage/$currentCourseID",0777);
	@mkdir("$webDir/courses/garbage/$currentCourseID/work",0777);
	move_dir("$workPath/$secret",
	"$webDir/courses/garbage/$currentCourseID/work/${id}_$secret");

	$tool_content .= "
    <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li><a href=\"work.php\">".$langBack."</a></li>
    </ul>
    </div>
    ";
		
		
	$tool_content .="
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"success\"><p><b>$langDeleted</b></p></td>
    </tr>
    </tbody>
    </table>";
}


// show assignment - student
function show_student_assignment($id)
{
	global $tool_content, $m, $uid, $langSubmitted, $langSubmittedAndGraded, $langNotice3,
	$langWorks, $langUserOnly, $langBack, $langWorkGrade, $langGradeComments;

	$res = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days
		FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);

	assignment_details($id, $row);

	if ($row['days'] < 0) {
		$submit_ok = FALSE;
	} else {
		$submit_ok = TRUE;
	}

	if (!$uid) {
		$tool_content .= "<p>$langUserOnly</p>";
		$submit_ok = FALSE;
	} elseif ($GLOBALS['statut'] == 10) {
		$tool_content .= "<p>$m[noguest]</p>";
		$submit_ok = FALSE;
	} else {
		if ($submission = was_graded($uid, $id)) {
			show_submission_details($submission);
			$submit_ok = FALSE;
		} elseif ($submission = find_submission($uid, $id)) {
			show_submission_details($submission);
			$tool_content .= "<p>$langNotice3</p>";
		}
	}
	if ($submit_ok) {
		show_submission_form($id);
	}
	$tool_content .= "<br/><p><a href='work.php'>$langBack</a></p>";
}


function show_submission_form($id)
{
	global $tool_content, $m, $langWorkFile, $langSendFile, $langSubmit, $uid;

	if (is_group_assignment($id) and ($gid = user_group($uid))) {
		$tool_content .= "<p>$m[this_is_group_assignment] ".
		"<a href='../group/document.php?userGroupId=$gid'>".
		"$m[group_documents]</a> $m[select_publish]</p>";
	} else {

		$tool_content .= <<<cData
		<form enctype="multipart/form-data" action="work.php" method="post">
			<input type="hidden" name="id" value="${id}">
			<table width="99%"><caption>${langSubmit}</caption><thead>
			
			<tr><th>${langWorkFile}:</th><td><input type="file" name="userfile"></td></tr>
			<tr><th>${m['comments']}:</th><td><textarea name="stud_comments" rows="5"
				cols="55"></textarea></td></tr></thead></table>
			<br/><input type="submit" value="${langSubmit}" name="work_submit">
		</form>
cData;

}
}


// Print a box with the details of an assignment
function assignment_details($id, $row, $message = null)
{
	global $tool_content, $m, $langDaysLeft, $langDays, $langWEndDeadline, $langWEndDeadline, $langNEndDeadline, $langEndDeadline;
	global $color2, $langDelAssign, $is_adminOfCourse, $langZipDownload, $langSaved ;


	if ($is_adminOfCourse) {
		$tool_content .= "
		<div id=\"operations_container\">
		<ul id=\"opslist\">
		<li><a href=\"work.php?id=$id&choice=do_delete\" onClick=\"return confirmation('".addslashes($row['title'])."');\">$langDelAssign</a></li>
		<li><a href=\"work.php?download=$id\">$langZipDownload</a></li>
		</ul>
		</div>
		";
	}
	//<a href='work.php?id=${row['id']}&choice=do_delete' onClick=\"return confirmation('".addslashes($row_title)."');\">
	if (isset($message)) {
		$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$langSaved </b></p>
							
						</td>
					</tr>
				</tbody>
			</table><br/>";
	}

	$tool_content .= "<table><thead>
	<tr><th>$m[title]:</th><td>$row[title]</td></tr>";
	$tool_content .= "<tr><th>$m[description]:</th><td>$row[description]</td></tr>
	";
	if (!empty($row['comments'])) {
		$tool_content .= "<tr><th>$m[comments]:</th><td>$row[comments]</td></tr>";
	}
	$tool_content .= "<tr><th>$m[start_date]:</th><td>$row[submission_date]</td></tr>
		<tr><th>$m[deadline]:</th><td>$row[deadline] ";
	if ($row['days'] > 1) {
		$tool_content .= "$langDaysLeft $row[days] $langDays</td></tr>";
	} elseif ($row['days'] < 0) {
		$tool_content .= "$langEndDeadline</td></tr>";
	} elseif ($row['days'] == 1) {
		$tool_content .= "$langWEndDeadline</td></tr>";
	} else {
		$tool_content .= "$langNEndDeadline</td></tr>";
	}
	$tool_content .= "<tr><th>$m[group_or_user]:</th><td>";
	if ($row['group_submissions'] == '0') {
		$tool_content .= "$m[user_work]</td></tr>";
	} else {
		$tool_content .= "$m[group_work]</td></tr>";
	}
	$tool_content .= "</thead></table><br/>";
}


// Show a table header which is a link with the appropriate sorting
// parameters - $attrib should contain any extra attributes requered in
// the <th> tags
function sort_link($title, $opt, $attrib = '')
{
	global $tool_content;
	$i = '';
	if (isset($_REQUEST['id'])) {
		$i = "&id=$_REQUEST[id]";
	}
	if (@($_REQUEST['sort'] == $opt)) {
		if (@($_REQUEST['rev'] == 1)) {
			$r = 0;
		} else {
			$r = 1;
		}
		$tool_content .= "<th $attrib><a href='work.php?sort=$opt&rev=$r$i'>" .
		"$title</a></th>";
	} else {
		$tool_content .= "<th $attrib><a href='work.php?sort=$opt$i'>$title</a></th>";
	}
}


// show assignment - prof view only
// the optional message appears insted of assignment details
function show_assignment($id, $message = FALSE)
{
	global $tool_content, $m, $langBack, $langNoSubmissions, $langSubmissions, $mysqlMainDb, $langWorks;
	global $langEndDeadline, $langWEndDeadline, $langNEndDeadline, $langDays, $langDaysLeft, $langGradeOk;
	global $colorMedium, $currentCourseID, $webDir, $urlServer, $nameTools, $m;

	$res = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);

	if ($message) {
		assignment_details($id, $row, $message);
	} else {
		assignment_details($id, $row);
	}

	//$tool_content .= "<h4>".$langSubmissions."</h4>";

	$rev = (@($_REQUEST['rev'] == 1))? ' DESC': '';
	if (isset($_REQUEST['sort'])) {
		if ($_REQUEST['sort'] == 'am') {
			$order = 'am';
		} elseif ($_REQUEST['sort'] == 'date') {
			$order = 'submission_date';
		} elseif ($_REQUEST['sort'] == 'grade') {
			$order = 'grade';
		} else {
			$order = 'nom';
		}
	} else {
		$order = 'nom';
	}

	$result = db_query("SELECT *
		FROM `$GLOBALS[code_cours]`.assignment_submit AS assign,
		`$mysqlMainDb`.user AS user
		WHERE assign.assignment_id='$id' AND user.user_id = assign.uid
		ORDER BY $order $rev");

	$num_results = mysql_num_rows($result);
	if ($num_results > 0) {
		if ($num_results == 1) {
			$tool_content .= "<p>$m[one_submission]</p>\n";
		} else {
			//			$tool_content .= sprintf("<p>$m[more_submissions]</p>\n", $num_results);
			$nameTools .= sprintf(" ($m[more_submissions])", $num_results);
		}

		// Print pie chart for grade distribution /////////////////////////////////////////////////////////
		//		$tool_content .= "\n\n<!-- BEGIN GRAPH -->\n\n";
		require_once '../../include/libchart/libchart.php';

		$chart = new PieChart(600, 300);
		$chart->setTitle("Κατανομή βαθολογίας εργασίας");

		$gradeOccurances = array(); // Named array to hold grade occurances/stats
		$gradesExists = 0;
		////////////////////////////////////////////////////
		while ($row = mysql_fetch_array($result)) {

			$theGrade = $row['grade'];

			if ($theGrade) {
				$gradesExists = 1;

				//if (!$gradeOccurances[$theGrade]) {
				if (!isset($gradeOccurances[$theGrade])) {
					$gradeOccurances[$theGrade] = 1;
				} else {
					if ($gradesExists) {
						++$gradeOccurances[$theGrade];
					}
				}
			}
		}
		////////////////////////////////////////////////////
		if ($gradesExists) {
			foreach ( $gradeOccurances as $gradeValue=>$gradeOccurance ) {
				$percentage = 100*($gradeOccurance/$num_results);
				$chart->addPoint(new Point("$gradeValue ($percentage)", $percentage));
			}

			$chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';
			$chart->render($webDir.$chart_path);

			$tool_content .= '<table width="99%"><tr><td><img src="'.$urlServer.$chart_path.'" /></td></tr></tbody></table>';
		}
		//		$tool_content .= "\n\n<!-- END GRAPH -->\n\n<br>";
		// end of pie chart /////////////////////////////////////////////////////////////////////////////////

		$result = db_query("SELECT *
					FROM `$GLOBALS[code_cours]`.assignment_submit AS assign,
					`$mysqlMainDb`.user AS user
					WHERE assign.assignment_id='$id' AND user.user_id = assign.uid
					ORDER BY $order $rev");

		$tool_content .= <<<cData
				<form action="work.php" method="post">
				<input type="hidden" name="grades_id" value="${id}">
				
				<p><b>$langSubmissions</b></p>
cData;

		/*	$tool_content .= "
		<table width=\"99%\"><caption>$langSubmissions</caption><thead>
		<tr>";


		sort_link($m['username'], 'nom', 'align="left"');
		sort_link($m['am'], 'am');

		$tool_content .= "<th>Sxolia kauhghth</th>
		<th>".$m['filename']."</th>
		";

		sort_link($m['sub_date'], 'date');
		sort_link($m['grade'], 'grade');
		$tool_content .= "</tr>";
		*/

		$i = 0;
		while ($row = mysql_fetch_array($result)) {
			$tool_content .= "
				<table width=\"99%\"><thead>
					<tr>";


			sort_link($m['username'], 'nom', 'align="left"');
			sort_link($m['am'], 'am');

			$tool_content .= "<th>".$m['gradecomments']."</th>
		<th>".$m['filename']."</th>
		";

			sort_link($m['sub_date'], 'date');
			sort_link($m['grade'], 'grade');
			$tool_content .= "</tr>";
			//is it a group assignment?
			if (!empty($row['group_id'])) {
				$subContentGroup = "($m[groupsubmit] ".
				"<a href='../group/group_space.php?userGroupId=$row[group_id]'>".
				"$m[ofgroup] $row[group_id]</a>)";
			} else $subContentGroup = "";

			//professor comments
			if (trim($row['grade_comments'] != '')) {
				$prof_comment = "$m[gradecomments]: ".
				htmlspecialchars($row['grade_comments']).
				" <a href='grade_edit.php?assignment=$id&submission=$row[id]'>".
				"($m[edit])</a>";
			} else {
				$prof_comment = "
				<a href='grade_edit.php?assignment=$id&submission=$row[id]'>".
				$m['addgradecomments']."</a>";
			}
			//			$color = (($i++) % 2)? $color1: $color2;
			$uid_2_name = uid_to_name($row['uid']);
			$stud_am = mysql_fetch_array(db_query("SELECT am from $mysqlMainDb.user WHERE user_id = '$row[uid]'"));
			$tool_content .= <<<cData
				<tr>
				<td>${uid_2_name} $subContentGroup</td>
					<td>${stud_am[0]}</td>
					<td>$prof_comment</td>
					<td><a href="work.php?get=${row['id']}">${row['file_name']}</a></td>
				<td align="center">${row['submission_date']}</td>
					<td align="center"><input type="text" value="${row['grade']}" maxlength="3" size="3"
						name="grades[${row['id']}]"></td>
				</tr>
cData;
			$tool_content .="
			</tbody></table>
			";
			if (trim($row['comments'] != '')) {
				$tool_content .= "<p><b>$m[comments]: ".
				"</b>$row[comments]</p><br/>";
			} else $tool_content .= "<br/>";

			/*if (trim($row['grade_comments'] != '')) {
			$tool_content .= "<tr bgcolor='$color'><td colspan='5'><b>$m[gradecomments]:</b> ".
			htmlspecialchars($row['grade_comments']).
			" <a href='grade_edit.php?assignment=$id&submission=$row[id]'>".
			"($m[edit])</a></td></tr>\n";
			} else {
			$tool_content .= "<tr bgcolor='$color'><td colspan='5'>".
			"<a href='grade_edit.php?assignment=$id&submission=$row[id]'>".
			$m['addgradecomments']."</a></td></tr>\n";
			}*/
		}
		$tool_content .= <<<cData
			<input type="submit" name="submit_grades" value="${langGradeOk}">
			</form>
cData;
		/* echo "<p><a href=\"work.php?choice=plain&id=$id\">$m[plainview]</a></p>"; */
		//		$tool_content .= "<p><a href=\"work.php?download=$id\">$langZipDownload</a></p>";
	} else {
		$tool_content .= "<p>$langNoSubmissions</p>";
	}
	$tool_content .= "<br/><p><a href='work.php'>$langBack</a></p>";
}


// // show assignment - student view only
function show_student_assignments()
{

	global $tool_content, $m, $uid;
	global $langDaysLeft, $langDays, $langNoAssign;

	$result = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days FROM assignments
			WHERE active = '1' ORDER BY submission_date");

	if (mysql_num_rows($result)) {

		$tool_content .= <<<cData
			<table width="99%"><thead>
				<tr>
					<th>${m['title']}</th>
			  <th>
			${m['deadline']}
			  </th>
			  <th>
				${m['submitted']}
			  </th>
			  <th>
			${m['grade']}
			  </th>
			</tr></thead><tbody>
cData;

		while ($row = mysql_fetch_array($result)) {
			$title_temp = htmlspecialchars($row['title']);
			$tool_content .= <<<cData
			<tr><td><a href="work.php?id=${row['id']}">${title_temp}</a></td><td width="30%">
			${row['deadline']}
cData;

			if ($row['days'] > 1) {
				$tool_content .= " ($m[in]&nbsp;$row[days]&nbsp;$langDays";
			} elseif ($row['days'] < 0) {
				$tool_content .= " ($m[expired])";
			} elseif ($row['days'] == 1) {
				$tool_content .= " ($m[tomorrow])";
			} else {
				$tool_content .= " ($m[today])";
			}
			$tool_content .= "</td><td width=\"10%\" align=\"center\">";

			$grade = ' - ';
			if ($submission = find_submission($uid, $row['id'])) {
				$tool_content .= "<img src='../../template/classic/img/checkbox_on.gif' alt='$m[yes]'>";
				$grade = submission_grade($submission);
				if (!$grade) {
					$grade = ' - ';
				}
			} else {
				$tool_content .= "<img src='../../template/classic/img/checkbox_off.gif' alt='$m[no]'>";
			}
			$tool_content .= "</td><td width=\"10%\" align=\"center\">${grade}</td></tr>";
		}
		$tool_content .= '</tbody></table>';
	} else {
		$tool_content .= "<p>$langNoAssign</p>";

	}
}


// show all the assignments
function show_assignments($message = null)
{
	global $tool_content, $m, $langNoAssign, $langNewAssign;

	$result = db_query("SELECT * FROM assignments ORDER BY id");

	
	$tool_content .="
    <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li><a href='work.php?add=1'>$langNewAssign</a></li>
    </ul>
    </div>
	";
	
	
	
	//$tool_content .= "<p><a href='work.php?add=1'>$langNewAssign</a></p>";
	if (isset($message)) {
		$tool_content .="
	<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p><b>$message</b></p>
							
						</td>
					</tr>
				</tbody>
			</table><br/>";
	}

	if (mysql_num_rows($result)) {

		$tool_content .= <<<cData
    <table width="99%">
    <thead>
    <tr>
      <th width='5'>a/a</th>
      <th class='left'>${m['title']}</th>
      <th width='70'>${m['deadline']}</th>
      <th width='70'>${m['edit']}</th>
      <th width='70'>${m['delete']}</th>
      <th width='100'>${m['activate']} / ${m['deactivate']}</th>
    </tr>
    </thead>
cData;
       $index = 1;
		while ($row = mysql_fetch_array($result)) {
			// Check if assignement contains unevaluatde (incoming) submissions
			$AssignementId = $row['id'];
			$result_s = db_query("SELECT COUNT(*) FROM assignment_submit WHERE assignment_id='$AssignementId' AND grade=''");
			$row_s = mysql_fetch_array($result_s);
			$hasUnevaluatedSubmissions = $row_s[0];
			//echo $hasUnevaluatedSubmissions;

			if(!$row['active']) {
				$visibility_css = " class=\"invisible\" ";
			} else {
				$visibility_css = " ";
			}

			$tool_content .= "
    <tbody>
    <tr ".$visibility_css.">
      <td align='right'>$index.</td>
      <td><a href=\"work.php?id=${row['id']}\" ";

			//	if(!$row['active'])
			//		$tool_content .= "class=\"invisible\" ";
			$tool_content .= " >";

			//			if ($hasUnevaluatedSubmissions)
			//			$tool_content .= "<b>";
			$tool_content .= $row_title = htmlspecialchars($row['title']);
			//			if ($hasUnevaluatedSubmissions)
			//			$tool_content .= "</b>";

			$tool_content .= <<<cData
      </a></td>
      <td $visibility_css align="center">${row['deadline']}</td>
      <td $visibility_css align="center"><a href="work.php?id=${row['id']}&choice=edit"><img src="../../template/classic/img/edit.gif" border="0" alt="${m['edit']}"></a></td>
cData;
			$tool_content .= "<td $visibility_css align=\"center\"><a href='work.php?id=${row['id']}&choice=do_delete' onClick=\"return confirmation('".addslashes($row_title)."');\"><img src=\"../../template/classic/img/delete.gif\" border=\"0\" alt=\"${m['delete']}\"></a></td>
      <td $visibility_css width=\"10%\"align=\"center\">";

			if($row['active']) {
				$deactivate_temp = htmlspecialchars($m['deactivate']);
				$activate_temp = htmlspecialchars($m['activate']);
				$tool_content .= "<a href=\"work.php?choice=disable&id=${row['id']}\">".
				"<img src=\"../../template/classic/img/visible.gif\" border=\"0\" title=\"${deactivate_temp}\"></a>";
			} else {
				$activate_temp = htmlspecialchars($m['activate']);
				$tool_content .= "<a href=\"work.php?choice=enable&id=${row['id']}\">".
				"<img src=\"../../template/classic/img/invisible.gif\" border=\"0\" title=\"${activate_temp}\"></a>";
			}
			$tool_content .= "
      </td>
    </tr>";
	$index++;
}
$tool_content .= '
    <tr>
      <th colspan=\"6\">&nbsp;</th>
    </tr>
    </tbody>
    </table>';
} else {
	$tool_content .= "<p class=\"alert1\">$langNoAssign</p>";

}
}


// submit grade and comment for a student submission
function submit_grade_comments($id, $sid, $grade, $comment)
{
	global $tool_content, $REMOTE_ADDR, $langGrades, $langWorkWrongInput;

	$stupid_user = 0;

	if (!is_numeric($grade)) {
		$tool_content .= $langWorkWrongInput;
		$stupid_user = 1;
	} else {
		db_query("UPDATE assignment_submit SET grade='$grade', grade_comments='$comment',
		grade_submission_date=NOW(), grade_submission_ip='$REMOTE_ADDR'
		WHERE id = '$sid'");
	}
	if (!$stupid_user) {
		show_assignment($id, $langGrades);
	}
}


// submit grades to students
function submit_grades($grades_id, $grades)
{
	global $tool_content, $REMOTE_ADDR, $langGrades, $langWorkWrongInput;

	$stupid_user = 0;

	foreach ($grades as $sid => $grade) {
		$val = mysql_fetch_row(db_query("SELECT grade from assignment_submit WHERE id = '$sid'"));
		if ($val[0] != $grade) {
			if (!is_numeric($grade))
			$stupid_user = 1;
		}
	}

	if (!$stupid_user) {
		foreach ($grades as $sid => $grade) {
			$val = mysql_fetch_row(db_query("SELECT grade from assignment_submit WHERE id = '$sid'"));
			if ($val[0] != $grade) {
				db_query("UPDATE assignment_submit SET grade='$grade',
						grade_submission_date=NOW(), grade_submission_ip='$REMOTE_ADDR'
						WHERE id = '$sid'");
			}
		}
		show_assignment($grades_id, $langGrades);
	} else {
		$tool_content .= $langWorkWrongInput;
	}
}

// functions for downloading
function send_file($id)
{
	global $tool_content, $currentCourseID;
	mysql_select_db($currentCourseID);
	$info = mysql_fetch_array(mysql_query("SELECT * FROM assignment_submit WHERE id = '$id'"));

	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".basename($info['file_name']));
	readfile("$GLOBALS[workPath]/$info[file_path]");
	exit();
}


// Zip submissions to assignment $id and send it to user
function download_assignments($id)
{
	global $tool_content, $workPath;

	$secret = work_secret($id);
	$filename = "$GLOBALS[currentCourseID]_work_$id.zip";
	chdir($workPath);
	create_zip_index("$secret/index.html", $id);
	$zip = new PclZip($filename);
	$zip->create($secret, "work_$id", $secret);
	header("Content-Type: application/x-zip");
	header("Content-Disposition: attachment; filename=$filename");
	readfile($filename);
	unlink($filename);
	exit();
}


// Create an index.html file for assignment $id listing user submissions
// Set $online to TRUE to get an online view (on the web) - else the
// index.html works for the zip file
function create_zip_index($path, $id, $online = FALSE)
{
	global $tool_content, $charset, $m;

	$fp = fopen($path, "w");
	if (!$fp) {
		die("Unable to create assignment index file - aborting");
	}
	fputs($fp, '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">
	</head>
	<body>
		<table border="1" width="95%">
			<thead><tr>
				<th>'.$m['username'].'</th>
				<th>'.$m['am'].'</th>
				<th>'.$m['filename'].'</th>
				<th>'.$m['sub_date'].'</th>
				<th>'.$m['grade'].'</th>
			</tr></thead>');

	$result = db_query("SELECT * FROM assignment_submit
		WHERE assignment_id='$id' ORDER BY id");

	$tool_content .= "<tbody>";

	while ($row = mysql_fetch_array($result)) {
		$filename = basename($row['file_path']);
		fputs($fp, '
			<tr>
				<td>'.uid_to_name($row['uid']).'</td>
				<td>'.uid_to_am($row['uid']).'</td>
				<td align="center"><a href="'.$filename.'">'.
		htmlspecialchars($filename).'</a></td>
				<td align="center">'.$row['submission_date'].'</td>
				<td align="center">'.$row['grade'].'</td>
			</tr>');
		if (trim($row['comments'] != '')) {
			fputs($fp, "
			<tr><td colspan='6'><b>$m[comments]: ".
			"</b>$row[comments]</td></tr>");
		}
		if (trim($row['grade_comments'] != '')) {
			fputs($fp, "
			<tr><td colspan='6'><b>$m[gradecomments]: ".
			"</b>$row[grade_comments]</td></tr>");
		}
		if (!empty($row['group_id'])) {
			fputs($fp, "
			<tr><td colspan='6'>$m[groupsubmit] ".
			"$m[ofgroup] $row[group_id] (".
			group_member_names($row['group_id']).")</td></tr>\n");
		}
	}
	fputs($fp, '
		</tbody></table>
	</body>
</html>
');
	fclose($fp);
}


// Show a simple html page with grades and submissions
function show_plain_view($id)
{
	global $tool_content, $workPath, $charset;
	$secret = work_secret($id);
	create_zip_index("$secret/index.html", $id, TRUE);
	header("Content-Type: text/html; charset=$charset");
	readfile("$workPath/$secret/index.html");
	exit;
}

?>
