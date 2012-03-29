<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


/*===========================================================================
	infocours.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Edit basic information of a course

 	This script allows the administrator to edit the basic information of a
 	selected course

 	The user can : - Edit the basic information of a course
                 - Return to edit course list

 	@Comments: The script is organised in four sections.

  1) Gather basic course information
  2) Edit that information
  3) Update course
  4) Display all on an HTML page

==============================================================================*/

$require_power_user = true;
// Include baseTheme
include '../../include/baseTheme.php';
if(!isset($_GET['c'])) { die(); }

require_once('../../include/lib/course.class.php');
require_once('../../include/lib/hierarchy.class.php');

$course = new course();
$tree = new hierarchy();

// Define $nameTools
$nameTools = $langCourseInfo;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listcours.php', 'name' => $langListCours);
$navigation[] = array('url' => 'editcours.php?c='.q($_GET['c']), 'name' => $langCourseEdit);
// Initialise $tool_content
$tool_content = "";

// Update cours basic information
if (isset($_POST['submit']))  {
        $departments = isset($_POST['department']) ? $_POST['department'] : array();
	// Update query
	db_query("UPDATE cours SET titulaires = ".autoquote($_POST['titulaires']).",
                                   intitule = ".autoquote($_POST['intitule'])."
                               WHERE code = ".autoquote($_GET['c']));
        $course->refresh(course_code_to_id($_GET['c']), null, $departments);
	
	$tool_content .= "<p class='success'>$langModifDone</p>
                <p>&laquo; <a href='editcours.php?c=$_GET[c]'>$langBack</a></p>";
}
// Display edit form for course basic information
else {
        $sql = "SELECT cours.code, cours.intitule, cours.titulaires, cours.cours_id
		  FROM cours
		 WHERE cours.code = '".mysql_real_escape_string($_GET['c'])."'";
        $row = mysql_fetch_array(db_query($sql));
	$tool_content .= "
	<form action=".$_SERVER['PHP_SELF']."?c=".htmlspecialchars($_GET['c'])." method='post'>
	<fieldset>
	<legend>".$langCourseInfoEdit."</legend>
<table width='100%' class='tbl'><tr><th>$langFaculty</th><td>";
        $tool_content .= $tree->buildCourseHtmlSelect('name="department[]"', $course->getDepartmentIds($row['cours_id']));
	$tool_content .= "</td></tr>
	<tr>
	  <th width='150'>".$langCourseCode.":</th>
	  <td><i>".$row['code']."</i></td>
	</tr>
	<tr>
	  <th>".$langTitle.":</b></th>
	  <td><input type='text' name='intitule' value='".$row['intitule']."' size='60'></td>
	</tr>
	<tr>
	  <th>".$langTeacher.":</th>
	  <td><input type='text' name='titulaires' value='".$row['titulaires']."' size='60'></td>
	</tr>
	<tr>
	  <th>&nbsp;</th>
	  <td class='right'><input type='submit' name='submit' value='$langModify'></td>
	</tr>
	</tbody>
	</table>
	</form></fieldset>\n";
}
// If course selected go back to editcours.php
if (isset($_GET['c'])) {
	$tool_content .= "<p align='right'><a href='editcours.php?c=".htmlspecialchars($_GET['c'])."'>".$langBack."</a></p>";
}
// Else go back to index.php directly
else {
	$tool_content .= "<p align='right'><a href=\"index.php\">".$langBackAdmin."</a></p>";
}
draw($tool_content, 3);
