<?php 

/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
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
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
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
$require_help = TRUE;
$helpTopic = 'Work';
$local_style = 'textarea, input { font-size: 10pt; font-family: monospace; }';

//include('../../include/init.php');

include '../../include/baseTheme.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_ASSIGN');
/**************************************/

$tool_content = "";

include('work_functions.php');

$workPath = $webDir."courses/".$currentCourseID."/work";

if (isset($get)) {
	send_file($get);
}

if (isset($download)) {
	include "../../include/pclzip/pclzip.lib.php";
	download_assignments($download);
}

$nameTools = $langWorks;
mysql_select_db($currentCourseID);

include('../../include/lib/fileUploadLib.inc.php');
include('../../include/lib/fileManageLib.inc.php');

if ($language == 'greek')
        $lang_editor='gr';
else
        $lang_editor='en';

$head_content = <<<hContent
<script type="text/javascript">
  _editor_url = '$urlAppend/include/htmlarea/';
  _css_url='$urlAppend/css/';
  _image_url='$urlAppend/include/htmlarea/images/';
  _editor_lang = '$lang_editor';
</script>
<script type="text/javascript" src='$urlAppend/include/htmlarea/htmlarea.js'></script>

<script type="text/javascript">
var editor = null;

function initEditor() {

  var config = new HTMLArea.Config();
  config.height = '180px';
  config.hideSomeButtons(" showhelp undo redo popupeditor ");

  editor = new HTMLArea("ta",config);

  // comment the following two lines to see how customization works
  editor.generate();
  return false;
}

</script>
hContent;

$body_action = "onload=\"initEditor()\"";


//-------------------------------------------
// main program
//-------------------------------------------


if ($is_adminOfCourse) {
	if (isset($grade_comments)) {
		submit_grade_comments($assignment, $submission, $grade, $comments);
	} elseif (isset($add)) {
		$nameTools = $langNewAssign;
		$navigation[] = array("url"=>"work.php", "name"=> $langWorks);
		//begin_page();
		new_assignment();
	} elseif (isset($sid)) {
		show_submission($sid);
	} elseif (isset($new_assign)) {
		//begin_page();
		add_assignment($title, $comments, $desc, "$fyear-$fmonth-$fday",
			$group_submissions);
		show_assignments();
	} elseif (isset($grades)) {
		submit_grades($grades_id, $grades);
	} elseif (isset($id)) {
		if (isset($choice)) {
			if ($choice == 'disable') {
				//begin_page();
				db_query("UPDATE assignments SET active = '0' WHERE id = '$id'");
				show_assignments();
			} elseif ($choice == 'enable') {
				//begin_page();
				db_query("UPDATE assignments SET active = '1' WHERE id = '$id'");
				show_assignments();
			} elseif ($choice == 'delete') {
				show_delete_assignment($id);
			} elseif ($choice == "do_delete") {
				$navigation[] = array("url"=>"work.php", "name"=> $langWorks);
				//begin_page($langDelAssign);
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
		//begin_page();
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
		//begin_page();
		show_student_assignments();
	}
}

//end_page();
draw($tool_content, 2, 'work', $head_content, $body_action);

//-------------------------------------
// end of main program
//-------------------------------------

// Show details of a student's submission to professor
function show_submission($sid)
{
	global $tool_content, $langWorks, $langSubmissionDescr, $langNotice3;

	$nameTools = $langWorks;
	$navigation[] = array("url"=>"work.php", "name"=> $langWorks);
	//begin_page();

	if ($sub = mysql_fetch_array(db_query("SELECT * FROM assignment_submit WHERE id = '$sid'"))) {
//		printf("<p>$langSubmissionDescr",
//			uid_to_name($sub['uid']),
//			$sub['submission_date'],
//			"<a href='$GLOBALS[urlServer]$GLOBALS[currentCourseID]".
//				"/work/$sub[file_path]'>$sub[file_name]</a>");
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
		('$title', '$desc', '$comments', '$deadline', NOW(), '$secret',
			'$group_submissions')");
	mkdir("$workPath/$secret",0777);
}



function submit_work($id) {
	
	global $tool_content, $workPath, $uid, $stud_comments, $group_sub, $REMOTE_ADDR,
		$langUpload, $langBack, $langWorks, $langUploadError, $currentCourseID;

	$res = db_query("SELECT title FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);
	$nav[] = array("url"=>"work.php?id=$id", "name"=> $row['title']);
	//begin_page($GLOBALS['langEdit'], $nav);

	delete_submissions_by_uid($uid, -1, $id);
	
	$local_name = greek_to_latin(uid_to_name($uid));
	$am = mysql_fetch_array(db_query("SELECT am FROM user WHERE user_id = '$uid'"));
	if (!empty($am[0])) {
		$local_name = "$local_name $am[0]";
	}
	$local_name = replace_dangerous_char($local_name);
	$secret = work_secret($id);
	$filename = "$secret/$local_name.".extension($_FILES['userfile']['name']);
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/$filename")) {
		$tool_content .= "<br><p>$langUpload</p>";
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
	} else {
		$tool_content .= "<p>$langUploadError</p>";
	}
	$tool_content .= "<p><center><a href='work.php'>$langBack</a></center></p>";	
}


//  assignment - prof view only 
function new_assignment()
{
	global $tool_content, $m, $langAdd;
	global $urlAppend;
	global $desc;

	$day	= date("d");
	$month	= date("m");
	$year	= date("Y");

	$tool_content .= "<form action=\"work.php\" method=\"post\"><table  width=\"95%\"><tbody><tr><td>".
		$m['title'].":</td><td><input type=\"text\" name=\"title\" size=\"55\"></td></tr><tr><td>".
		$m['description'].":</td><td>".
		"<textarea id=\"ta\" name=\"desc\" value=\"$desc\" style=\"width:100%\" rows=\"20\" cols=\"80\">";

	if ($desc)
		$tool_content .= $desc;

	$tool_content .=	"</textarea></td></tr><tr><td>".$m['comments'].":</td><td>".
		"<textarea name=\"comments\" rows=\"5\" cols=\"55\"></textarea></td></tr><tr><td>".
		$m['deadline'].":</td><td>";

	date_form($day, $month, $year);
	
	$tool_content .= "</td></tr><tr><td>".$m['group_or_user'].
		":</td><td><input type=\"radio\" name=\"group_submissions\" value=\"0\" checked=\"1\">".
		$m['user_work']."<br><input type=\"radio\" name=\"group_submissions\" value=\"1\">".
		$m['group_work']."</td></tr><tr><td colspan=2>".
		"<input type=\"submit\" name=\"new_assign\" value=\"$langAdd\"></tr></tbody></table></form>";

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
	
	$res = db_query("SELECT * FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);
	$nav[] = array("url"=>"work.php?id=$id", "name"=> $row['title']);
	//begin_page($langEdit, $nav);

	$deadline = explode('-', $row['deadline']);
	$day = $deadline[2];
	$month = $deadline[1];
	$year = $deadline[0];
//////////////////////////////////////////////////////////////////////////////////////

$tool_content .= <<<cData
	<form action="work.php" method="post">
	<input type="hidden" name="id" value="$id">
	<input type="hidden" name="choice" value="do_edit">
	<table width="95%" ><tbody>
	<tr><td>${m['title']}:</td>
	<td><input type="text" name="title" size="55" value="${row['title']}" style="width: 100%"></td></tr>
<tr><td>${m['description']}:</td>
	<td>
<textarea id="ta" name="desc" value="${row['description']}" style="width:100%" rows="20" cols="60">
${row['description']}</textarea>
	</td></tr>
	<tr><td>${m['comments']}:</td>
	<td><textarea name="comments" rows="5" cols="65">${row['comments']}</textarea></td></tr>
<tr><td>${m['deadline']}:</td><td>

cData;

	date_form($day, $month, $year);

	$tool_content .= "</td></tr><tr><td>".$m['group_or_user'].":</td><td>".
		"<input type=\"radio\" name=\"group_submissions\" value=\"0\"";
	
	if ($row['group_submissions'] == '0')
		$tool_content .= " checked=\"1\" >";
	
	$tool_content .= $m['user_work']."<br><input type=\"radio\" name=\"group_submissions\" value=\"1\" >";
	
	if ($row['group_submissions'] != '0')
		$tool_content .= "checked=\"1\" >";		

	$tool_content .= $m['group_work']."</td></tr><tr><td colspan=2>".
		"<input type=\"submit\" name=\"do_edit\" value=\"".$langEdit."\"></td></tr></tbody></table>";	
//////////////////////////////////////////////////////////////////////////////////////
	$tool_content .= "<p><center><a href='work.php'>$langBack</a></center></p>";
}

// edit assignment
function edit_assignment($id)
{
	global $tool_content, $langBackAssignment, $langEditSuccess, $langEditError, $langWorks, $langEdit;
	
	$nav[] = array("url"=>"work.php", "name"=> $langWorks);
	$nav[] = array("url"=>"work.php?id=$id", "name"=> $_POST['title']);
	//begin_page($langEdit, $nav);

	if (db_query("UPDATE assignments SET title='$_POST[title]',
		description='$_POST[desc]', group_submissions='$_POST[group_submissions]',
		comments='$_POST[comments]', deadline='$_POST[fyear]-$_POST[fmonth]-$_POST[fday]' WHERE id='$id'")) {
		$tool_content .= "<p><center>$langEditSuccess</p></center>";
	} else {
		$tool_content .= "<p><center>$langEditError</p></center>";	
	}
	$tool_content .= "<p><center><a href='work.php?id=$id'>$langBackAssignment \"$_POST[title]\"</a></center></p>";
}

// show delete confirmation
function show_delete_assignment($id) 
{
	
	global $tool_content, $langDelAssign, $langDelWarn1, $langDelSure, $langDelWarn2, $langDelTitle;
	global $langDelMany1, $langDelMany2, $langWorks, $m;

	$info = mysql_fetch_array(db_query("SELECT * FROM assignments
		WHERE id = '$id'"));
	$subs = mysql_num_rows(db_query("SELECT * FROM assignment_submit
		WHERE assignment_id = '$id'"));

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);
	$nav[] = array("url"=>"work.php?id=$id", "name"=> $info['title']);
	//begin_page($langDelAssign, $nav);

	$tool_content .= "<h4>".$langDelAssign."</h4><p>".$langDelWarn1." ".$info['title'].". ".$langDelSure." </p>";
	
	if ($subs > 0) {
		$tool_content .= "<p><strong>".$langDelTitle."</strong><br>";
		if ($subs == 1)
			$tool_content .= $langDelWarn2;
		else
			$tool_content .= "$langDelMany1 $subs $langDelMany2";
	    }
	$tool_content .= "<p><a href='work.php?id=$id&choice=do_delete'>".$m['yes']."<a> | <a href='work.php'>".$m['no']."<a></p>\n";
}


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
	
	$tool_content .= "<p>".$langDeleted."</p><p><a href=\"work.php\">".$langBack."</a></p>";
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
	//begin_page($row['title'], $nav);
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
	$tool_content .= "<p><center><a href='work.php'>$langBack</a></center></p>";
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
			<table width="95%"><tbody>
			<tr><td colspan='2'><b>${langSendFile}</b></th></tr>
			<tr><td>${langWorkFile}:</td><td><input type="file" name="userfile"></td></tr>
			<tr><td>${m['comments']} :</td><td><textarea name="stud_comments" rows="5"
				cols="55"></textarea></td></tr>
			<tr><td colspan='2'><input type="submit" value="${langSubmit}" name="work_submit"></td></tr>
			</tbody></table>
		</form>
cData;

	}
}


// Print a box with the details of an assignment
function assignment_details($id, $row)
{
	global $tool_content, $m, $langDaysLeft, $langDays, $langWEndDeadline, $langWEndDeadline, $langNEndDeadline, $langEndDeadline;
	global $color2, $langDelAssign, $is_adminOfCourse;

	$tool_content .= "<h4>$m[title]: $row[title]</h4>\n";
	if ($is_adminOfCourse) {
		$tool_content .= "<p align='right'><a href=\"work.php?id=$id&choice=delete\">$langDelAssign</a></p>\n";
	}
	$tool_content .= "<dl style='background: $color2; padding: 1em;'><dt>$m[description]:</dt><dd>$row[description]</dd>
	";
	if (!empty($row['comments'])) {
		$tool_content .= "<dt>$m[comments]:</dt><dd>$row[comments]</dd>";
	}
	$tool_content .= "<dt>$m[start_date]:</dt><dd>$row[submission_date]</dd>
		<dt>$m[deadline]:</dt><dd>$row[deadline] ";
	if ($row['days'] > 1) {
		$tool_content .= "$langDaysLeft $row[days] $langDays</dd>";
	} elseif ($row['days'] < 0) {
		$tool_content .= "$langEndDeadline</dd>";
	} elseif ($row['days'] == 1) {
		$tool_content .= "$langWEndDeadline</dd>";
	} else {
		$tool_content .= "$langNEndDeadline</dd>";
	}
	$tool_content .= "<dt>$m[group_or_user]:</dt><dd>";
	if ($row['group_submissions'] == '0') {
		$tool_content .= "$m[user_work]</dd>";
	} else {
		$tool_content .= "$m[group_work]</dd>";
	}
	$tool_content .= "</dl>";
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
	global $langEndDeadline, $langWEndDeadline, $langNEndDeadline, $langDays, $langDaysLeft, $langZipDownload, $langGradeOk;
	global $color1, $color2, $colorMedium, $currentCourseID, $webDir, $urlServer;
	
	$res = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"work.php", "name"=> $langWorks);
	//begin_page($row['title'], $nav);
  
	if ($message) {
		$tool_content .= "<p class='forms'>$message</p>";
	} else {
		assignment_details($id, $row);
	}

$tool_content .= "<h4>".$langSubmissions."</h4>";

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
			$tool_content .= sprintf("<p>$m[more_submissions]</p>\n", $num_results);
		}
		
// Print pie chart for grade distribution /////////////////////////////////////////////////////////
$tool_content .= "\n\n<!-- BEGIN GRAPH -->\n\n";
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
					
			$tool_content .= '<table width="95%"><tr><td><img src="'.$urlServer.$chart_path.'" /></td></tr></tbody></table>';
		}
$tool_content .= "\n\n<!-- END GRAPH -->\n\n<br>";		
// end of pie chart /////////////////////////////////////////////////////////////////////////////////

				$result = db_query("SELECT *
					FROM `$GLOBALS[code_cours]`.assignment_submit AS assign,
					`$mysqlMainDb`.user AS user
					WHERE assign.assignment_id='$id' AND user.user_id = assign.uid
					ORDER BY $order $rev");

			$tool_content .= <<<cData
				<form action="work.php" method="post">
				<input type="hidden" name="grades_id" value="${id}">
				<table border="0" align="center" cellpadding="2" cellspacing="0" width="80%"><tbody>
					<tr bgcolor="${colorMedium}">
cData;

				sort_link($m['username'], 'nom', 'align="left"');
				sort_link($m['am'], 'am');
	
	  		$tool_content .= "<th>".$m['filename']."</th>";

				sort_link($m['sub_date'], 'date');
				sort_link($m['grade'], 'grade');
				$tool_content .= "</tr>";
				
		$i = 0;
		while ($row = mysql_fetch_array($result)) {
			$color = (($i++) % 2)? $color1: $color2;
			$uid_2_name = uid_to_name($row['uid']);
			$stud_am = mysql_fetch_array(db_query("SELECT am from $mysqlMainDb.user WHERE user_id = '$row[uid]'"));
			$tool_content .= <<<cData
				<tr bgcolor="${color}">
				<td>${uid_2_name}</td>
					<td align="center">${stud_am[0]}</td>
					<td align="center"><a href="work.php?get=${row['id']}">${row['file_name']}</a></td>
				<td align="center">${row['submission_date']}</td>
					<td align="center"><input type="text" value="${row['grade']} " maxlength="3" size="3"
						name="grades[${row['id']}]"></td>
				</tr>
cData;

				if (trim($row['comments'] != '')) {
						$tool_content .= "<tr bgcolor='$color'><td colspan='5'><b>$m[comments]: ".
							"</b>$row[comments]</td></tr>\n";
				}
				if (!empty($row['group_id'])) {
						$tool_content .= "<tr bgcolor='$color'><td colspan='5'><b>$m[groupsubmit] ".
							"<a href='../group/group_space.php?userGroupId=$row[group_id]'>".
							"$m[ofgroup] $row[group_id]</a></b></td></tr>\n";
				}
				if (trim($row['grade_comments'] != '')) {
					$tool_content .= "<tr bgcolor='$color'><td colspan='5'><b>$m[gradecomments]:</b> ".
							htmlspecialchars($row['grade_comments']).
							" <a href='grade_edit.php?assignment=$id&submission=$row[id]'>".
							"($m[edit])</a></td></tr>\n";
				} else {
						$tool_content .= "<tr bgcolor='$color'><td colspan='5'>".
							"<a href='grade_edit.php?assignment=$id&submission=$row[id]'>".
							$m['addgradecomments']."</a></td></tr>\n";
				}
		} 
		$tool_content .= <<<cData
			<tr><td colspan="5" align="right"><input type="submit" name="submit_grades" value="${langGradeOk}"></td></tr>	
    	</tbody></table>
			</form>
cData;
		/* echo "<p><a href=\"work.php?choice=plain&id=$id\">$m[plainview]</a></p>"; */
		$tool_content .= "<p><a href=\"work.php?download=$id\">$langZipDownload</a></p>";
	} else {
		$tool_content .= "<p>$langNoSubmissions</p>";
	}
	$tool_content .= "<p><center><a href='work.php'>$langBack</a></center></p>";
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
			<table border="0" align="center" cellpadding="2" cellspacing="2" width="95%"><tbody>
				<tr bgcolor="#E6E6E6">
					<th align="center">${m['title']}</th>
			  <th align="center">
			${m['deadline']}
			  </th>
			  <th align="center" width="10%">
				${m['submitted']}
			  </th>
			  <th align="center" width="10%">
			${m['grade']}
			  </th>
			</tr>
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
	$tool_content .= '<tbody></table>';
	} else {
		$tool_content .= "<p>$langNoAssign</p>";
	
	}
}


// show all the assignments 
function show_assignments()
{
	global $tool_content, $m, $langNoAssign, $langNewAssign;

	old_work_check();

	$result = db_query("SELECT * FROM assignments ORDER BY id");

	$tool_content .= "<p align='right'><a href='work.php?add=1'>$langNewAssign</a></p>";
	
	if (mysql_num_rows($result)) {
		
		$tool_content .= <<<cData
			<table border="0" align="center" cellpadding="2" cellspacing="2" width="95%">
				<thead><tr>
					<th align="center">${m['title']}</th>
			  <th align="center">
				${m['deadline']}
			  </th>
			  <th align="center">
			  	${m['edit']}
			  </th>	
			  <th align="center">
			  	${m['delete']}
			  </th>	
			  <th align="center">
				${m['activate']} / ${m['deactivate']}
			  </th>
			</tr></thead>
cData;

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

$tool_content .= "<tbody><tr ".$visibility_css."><td><a href=\"work.php?id=${row['id']}\" "; 
	
//	if(!$row['active']) 
//		$tool_content .= "class=\"invisible\" ";
	$tool_content .= " >";

	if ($hasUnevaluatedSubmissions) 
		$tool_content .= "<b>";
  $tool_content .= htmlspecialchars($row['title']);
	if ($hasUnevaluatedSubmissions) 
		$tool_content .= "</b>";
       
	$tool_content .= <<<cData
		</a></td>
    <td $visibility_css align="center">${row['deadline']}</td>
    <td $visibility_css align="center"><a href="work.php?id=${row['id']}&choice=edit"><img src="../../template/classic/img/edit.gif" border="0" alt="${m['edit']}"></a></td>
    <td $visibility_css align="center"><a href="work.php?id=${row['id']}&choice=delete"><img src="../../template/classic/img/delete.gif" border="0" alt="${m['delete']}"></a></td>
    <td $visibility_css width="10%" align="center">
cData;
    if($row['active']) {
    	$deactivate_temp = htmlspecialchars($m['deactivate']);
    	$activate_temp = htmlspecialchars($m['activate']);
    	$tool_content .= "<a href=\"work.php?choice=disable&id=${row['id']}\">".
	  		"<img src=\"../../template/classic/img/visible.gif\" border=\"0\" alt=\"${deactivate_temp}\"></a>";
    } else {
    	$activate_temp = htmlspecialchars($m['activate']);
    	$tool_content .= "<a href=\"work.php?choice=enable&id=${row['id']}\">".
	  		"<img src=\"../../template/classic/img/invisible.gif\" border=\"0\" alt=\"${activate_temp}\"></a>";
    }
		$tool_content .= "</td></tr>";
	}
	$tool_content .= '</tbody></table>';
	} else {
		$tool_content .= "<p>$langNoAssign</p>";
	
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
				if (!is_numeric($grade)) {
					$tool_content .= $langWorkWrongInput;
					$stupid_user = 1;
				} else {
					db_query("UPDATE assignment_submit SET grade='$grade', 
						grade_submission_date=NOW(), grade_submission_ip='$REMOTE_ADDR'
						WHERE id = '$sid'");
				}
			}
		}
		if (!$stupid_user)
			show_assignment($grades_id, $langGrades);
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

// Check for old assignments and show link to work-old.php
function old_work_check()
{
	global $tool_content, $langOldWork;
	$work = mysql_fetch_array(db_query("SELECT COUNT(*) FROM work"));
	if ($work[0] > 0) {
		printf($langOldWork, $work[0]);
	}
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
