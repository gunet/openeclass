<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                               |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$           |
	  |   English Translation                                                |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
 */
 
 
 // GENERIC

$langHelp="Help";
$langSubmit='Submit';
$langBackAssignment = "Back to work";
$langBack = "Back";

// ------------------
// new messages


$m['title'] = "Title";
$m['description'] = "Description";
$m['activate'] = "Activate";
$m['deactivate'] = "Deactivate";
$m['deadline'] = "Deadline";
$m['username'] = "Username";
$m['filename'] = "Filename";
$m['sub_date'] = "Submission date";
$m['comments'] = "Comments";
$m['gradecomments'] = "Grading comments";
$m['addgradecomments'] = "Add grading comments";
$m['delete'] = "Delete";
$m['edit'] = "Modify";
$m['start_date'] = "Start date";
$m['grade'] = "Grade";
$m['am'] = "Student ID";
$m['yes'] = "Yes";
$m['no'] = "No";
$m['in'] = "in";
$m['today'] = "today";
$m['tomorrow'] = "tomorrow";
$m['expired'] = "has&nbsp;expired";
$m['submitted'] = "Submitted";
$m['select'] = "Selection";
$m['groupsubmit'] = "Submitted on behalf of";
$m['ofgroup'] = "group";
$m['deleted work by user'] = "Deleted previous submission to this
	assignment from file";
$m['deleted work by group'] = "Deleted previous submission by another
	member of your group from file";
$m['by_groupmate'] = 'By another member of your group';
$m['the_file'] = 'The file';
$m['was_submitted'] = 'was submitted.';
$m['group_sub'] = 'Select if you want to submit this file on
	behalf of your group';
$m['group'] = 'group';
$m['already_group_sub'] = 'A file has already been submitted for this
	assignment by another member of your group';
$m['group_or_user'] = 'Assignment type';
$m['group_work'] = 'Group submissions';
$m['user_work'] = 'Individual submissions';
$m['submitted_by_other_member'] = 'This file has been submitted by another
	member of';
$m['your_group'] = 'your group';
$m['this_is_group_assignment'] = 'This is a group assignment. To submit a
	file, please go to';
$m['group_documents'] = 'your group\'s documents,';
$m['select_publish'] = 'and select "Publish".';
$m['noguest'] = 'To submit work to an assignment you must login as a normal
	user, not as a guest.';
$m['one_submission'] = 'One file has been submitted';
$m['more_submissions'] = '%d files have been submitteD';
$m['plainview'] = 'Concise list of submissions and grades';

$langGroupWorkIntro = '
	Below appear the assignments available in this course. Please select
	an assignment to which to submit the file "%s" as group work, and
	add any comments you would like. Please note that if you submit a new
	file when a file has been already been submitted by you or a member of
	your team, the old file will be deleted and replaced by the new.
	Furthermore, no new submissions are allowed when the assignment has
	been graded.';

$langModify="Modify";
$langDelete="Delete";
$langEdit = "Edit";
$langAdd = "Add";
$langEditSuccess = "Successful editing!";
$langEditError = "An error occured during the editing !";
$langNewAssign = "New assignment";
$langDeleted = "Assignment deleted";
$langDelAssign = "Delete Assignment";
$langDelWarn1 = "You are going to delete the assignment";
$langDelSure = "Are you sure you want to delete the assignment?";
$langWorkFile = "File";
$langZipDownload = "Download assignments (in .zip format)";

$langDelWarn2 = "There is a student submission. This file will be deleted!";
$langDelTitle = "Warning!";
$langDelMany1 = "Have submitted";
$langDelMany2 = "students assignments. These files will be deleted!";

$langSubmissions = "Students Submissions";

$langSubmitted = "A file has already been submitted for this assignment";
$langNotice2 = "Submission date";
$langNotice3 = "If you submit another file, the old file will be replaced by the new one.";
$langSubmittedAndGraded = "A submission for this assignment has been uploaded and graded.";
$langSubmissionDescr = "%s, on %s, submitted a file named \"%s\".";
$langEndDeadline = "(deadline)";
$langWEndDeadline = "(Deadline is tomorrrow)";
$langNEndDeadLine = "(Deadline is today)";
$langDays = "days)";
$langDaysLeft = "(left";
$langGrades = "The grades were assigned successfully!";

$langUpload = "Upload succesfull !";
$langUploadError = "An error occured during uploading!";
$langWorkGrade = "Assignment grade";
$langGradeComments = "Grade comments were:";
$langGradeOk = "Submit changes";
$langWorks="Students assignments";
$langNoSubmissions = "No submissions";
$langNoAssign = "No assignments";

$langSubmit = "Work submission";
$langGroupSubmit = "Group work submission";
$langUserOnly = "To submit an assignment you must first log in.";
$langGradeWork = "Grading comments";

// work-old.php messages
$langProfOnly = 'This page is only accessible to this course\'s
	instructor. Please return to the <a href="work.php">student work</a>
	page';
$langWorksOld = 'Old Submissions';
$langOldWork = "<p>There are %d <a href='work_old.php'>old student
	submissions</a>.</p>\n";
$langWorkWrongInput = 'The grade must be a number. Please go back an enter the grade again.';

?>
