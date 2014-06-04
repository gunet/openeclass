<?php
/* ========================================================================
 * Open eClass 2.9
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

/**
 * @file infocours.php
 * @brief Edit basic information of a course
 */

$require_power_user = true;

// Include baseTheme
include '../../include/baseTheme.php';

if(!isset($_GET['c'])) { die(); }
// Define $nameTools
$nameTools = $langCourseInfo;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'searchcours.php', 'name' => $langSearchCourse);
$navigation[] = array('url' => 'editcours.php?c='.q($_GET['c']), 'name' => $langCourseEdit);

// Update cours basic information
if (isset($_POST['submit']))  {
	$department = intval($_POST['department']);
	$facname = find_faculty_by_id($department);
	// Update query
	db_query("UPDATE cours SET titulaires = ". quote($_POST['titulaires']) .",
                                   intitule = ". quote($_POST['intitule']) .",
                                   faculteid = $department
                               WHERE code = ". quote($_GET['c']));
	
	$tool_content .= "<p class='success'>$langModifDone</p>
                <p>&laquo; <a href='editcours.php?c=$_GET[c]'>$langBack</a></p>";
}
// Display edit form for course basic information
else {
	$row = mysql_fetch_array(db_query("SELECT * FROM cours WHERE code='".mysql_real_escape_string($_GET['c'])."'"));
	$tool_content .= "
	<form action=".$_SERVER['SCRIPT_NAME']."?c=".htmlspecialchars($_GET['c'])." method='post'>
	<fieldset>
	<legend>".$langCourseInfoEdit."</legend>
<table width='100%' class='tbl'><tr><th>$langFaculty</th><td>";
	$tool_content .= list_departments($row['faculteid']);
	$tool_content .= "</td></tr>
	<tr>
	  <th width='150'>".$langCourseCode.":</th>
	  <td><i>".$row['code']."</i></td>
	</tr>
	<tr>
	  <th>".$langTitle.":</b></th>
	  <td><input type='text' name='intitule' value='". q($row['intitule']) ."' size='60'></td>
	</tr>
	<tr>
	  <th>".$langTeacher.":</th>
	  <td><input type='text' name='titulaires' value='". q($row['titulaires']) ."' size='60'></td>
	</tr>
	<tr>
	  <th>&nbsp;</th>
	  <td class='right'><input type='submit' name='submit' value='".q($langModify)."'></td>
	</tr>
	</tbody>
	</table>
	</form></fieldset>\n";
}
// If course selected go back to editcours.php
if (isset($_GET['c'])) {
	$tool_content .= "<p align='right'><a href='editcours.php?c=".q($_GET['c'])."'>".$langBack."</a></p>";
}
// Else go back to index.php directly
else {
	$tool_content .= "<p align='right'><a href=\"index.php\">".$langBackAdmin."</a></p>";
}
draw($tool_content, 3);
