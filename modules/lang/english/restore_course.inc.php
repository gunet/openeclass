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



// restore_course.php

$langAdmin = "Administration Tools";
$langRequest1 = "Click on \"Browse\" to search for the backup file of the course
	you want to restore. Then click on \"Submit\".";
$langSend = "Submit";
$langRestore = "Restore";

$langRequest2 = "If the backup file you wish to restore is too large and can't
	be uploaded, you can enter the path of a directory on the server where
	it can be found.";
$langRestoreStep1 = "1. Restore a course from a file or directory.";
$langDescribe = "The backup of a course is found in a compressed file or
	a directory and consists of four parts";
$langDescribe1 = "A descriptive file";
$langDescribe2 = "The documents subdirectory";
$langDescribe3 = "An SQL file to reconstruct the course database";
$langDescribe4 = "An SQL file containing main database data.";
$langFileNotFound = "File not found.";

$langFileSent = "File uploaded";
$langFileSentName = "File name:";
$langFileSentSize = "File size:";
$langFileSentType = "File type:";
$langFileSentTName = "Temporary name:";
$langFileUnzipping = "File is being decompressed";
$langEndFileUnzip = "Decompression ended";
$langLesFound = "Courses found in the file:";
$langLesFiles = "Course files:";

$langInvalidCode = "Invalid course code";
$langCopyFiles = "Course files copied to";
$langCourseExists = "A course with this code already exists!";
$langUserExists = "User already exists: ";
$langUserExists2 = "Name:";

$langInfo1 = "The course backup you uploaded contains the following
	course information.";
$langInfo2 = "You can change the course code as well as all other
	information (eg. description, professor, etc.)";
$langWarning = "<em><font color='red'>WARNING!</font></em> If you don't select to add course users and course backup copy includes tools with informations concerning users (e.g. 'Student Papers', 'Dropbox' or 'Users group') then these informations will <b>NOT</b> be restored.";

$langCourseCode = "Code";
$langCourseLang = "Language";
$langCourseTitle = "Title";
$langCourseDesc = "Description";
$langCourseFac = "Department";
$langCourseOldFac = "Old department";
$langCourseVis = "Access type";
$langCourseProf = "Professor";
$langCourseType = "Pre/postgraduate";

$langUsersWillAdd = "Add course users";
$langUserPrefix = "Add a prefix to usernames";
$langOk = "OK";
$langErrorLang = "Error! No languages found !";

?>
