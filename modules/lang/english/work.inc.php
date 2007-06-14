<?
/*
      +----------------------------------------------------------------------+
      | GUnet eClass 2.0                                                     |
      | Asychronous Teleteaching Platform                                    |
      +----------------------------------------------------------------------+
      | Copyright (c) 2003-2007  GUnet                                       |
      +----------------------------------------------------------------------+
      |                                                                      |
      | GUnet eClass 2.0 is an open platform distributed in the hope that    |
      | it will be useful (without any warranty), under the terms of the     |
      | GNU License (General Public License) as published by the Free        |
      | Software Foundation. The full license can be read in "license.txt".  |
      |                                                                      |
      | Main Developers Group: Costas Tsibanis <k.tsibanis@noc.uoa.gr>       |
      |                        Yannis Exidaridis <jexi@noc.uoa.gr>           |
      |                        Alexandros Diamantidis <adia@noc.uoa.gr>      |
      |                        Tilemachos Raptis <traptis@noc.uoa.gr>        |
      |                                                                      |
      | For a full list of contributors, see "credits.txt".                  |
      |                                                                      |
      +----------------------------------------------------------------------+
      | Contact address: Asynchronous Teleteaching Group (eclass@gunet.gr),  |
      |                  Network Operations Center, University of Athens,    |
      |                  Panepistimiopolis Ilissia, 15784, Athens, Greece    |
      +----------------------------------------------------------------------+
*/



$langSubmit='Submit';
$langBackAssignment = "Back to work";

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
	an assignment to which to submit the file as group work, and
	add any comments you would like. Please note that if you submit a new
	file when a file has been already been submitted by you or a member of
	your team, the old file will be deleted and replaced by the new.
	Furthermore, no new submissions are allowed when the assignment has
	been graded.';

$langModify="Modify";
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

$langWorkWrongInput = 'The grade must be a number. Please go back an enter the grade again.';
$langWarnForSubmissions = "If any assignments were submitted, they will be deleted";
$langAssignmentActivated = "The assignment was activated";
$langAssignmentDeactivated = "The assignment was deactivated";
$langSaved = "The assignment details were saved";
?>
