<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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
	listcours.php
	@last update: 11-07-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: List courses

 	This script allows the administrator list all available courses, or some
 	of them if a search is obmitted

 	The user can : - See basic information about courses
 	               - Go to users of a course
 	               - Go to delete course page
 	               - Go to edit course page
                 - Return to main administrator page
                 - A paging navigation has been added

 	@Comments: The script is organised in three sections.

  1) Select courses from database (all or some of them based on search)
  2) Display basic information about selected courses
  3) Display all on an HTML page

==============================================================================*/

$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';

$nameTools = $langListCours;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$caption = "";
// Initialize some variables
$searchurl = "";
// Manage list limits
$countcourses = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS cnt FROM cours"));
$fulllistsize = $countcourses['cnt'];

define ('COURSES_PER_PAGE', 15);

$limit = isset($_GET['limit'])?$_GET['limit']:0;

// Display Actions Toolbar
$tool_content .= "
    <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='searchcours.php'>$langSearchCourses</a></li>
    </ul>
    </div>";


// A search has been submitted
if (isset($_GET['search']) && $_GET['search'] == "yes") {
	$searchurl = "&search=yes";
	// Search from post form
	if (isset($_POST['search_submit'])) 	{
		$searchtitle = $_SESSION['searchtitle'] = $_POST['formsearchtitle'];
		$searchcode = $_SESSION['searchcode'] = $_POST['formsearchcode'];
		$searchtype = $_SESSION['searchtype'] = $_POST['formsearchtype'];
		$searchfaculte = $_SESSION['searchfaculte'] = $_POST['formsearchfaculte'];
	}
	// Search from session
	else {
		$searchtitle = $_SESSION['searchtitle'];
		$searchcode = $_SESSION['searchcode'];
		$searchtype = $_SESSION['searchtype'];
		$searchfaculte = $_SESSION['searchfaculte'];
	}
	// Search for courses
	$searchcours=array();
	if(!empty($searchtitle)) {
		$searchcours[] = "intitule LIKE '%".mysql_escape_string($searchtitle)."%'";
	}
	if(!empty($searchcode)) {
		$searchcours[] = "code LIKE '%".mysql_escape_string($searchcode)."%'";
	}
	if ($searchtype != "-1") {
		$searchcours[] = "visible = '".mysql_escape_string($searchtype)."'";
	}
	if($searchfaculte != "0") {
		$searchcours[] = "faculte = '".mysql_escape_string($searchfaculte)."'";
	}
	$query=join(' AND ',$searchcours);
	if (!empty($query)) {
		$sql = mysql_query("SELECT faculte, code, intitule, titulaires, visible, cours_id FROM cours 
			WHERE $query ORDER BY faculte");
		$caption .= "$langFound ".mysql_num_rows($sql)." $langCourses ";
	} else {
		$sql = mysql_query("SELECT faculte, code, intitule,titulaires, visible, cours_id FROM cours 
				ORDER BY faculte");
		$caption .= "$langFound ".mysql_num_rows($sql)." $langCourses ";
	}
}
// Normal list, no search, select all courses
else {
	$a = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours"));
	$caption .= "".$langManyExist.": <b>".$a[0]." $langCourses</b>";
	$sql = mysql_query("SELECT faculte, code, intitule, titulaires, visible, cours_id FROM cours 
			ORDER BY faculte,code LIMIT ".$limit.",".COURSES_PER_PAGE."");
        
        //$tool_content .= "<p class='success'>".$caption."</p>";
	if ($fulllistsize > COURSES_PER_PAGE ) {
		// Display navigation in pages
		$tool_content .= show_paging($limit, COURSES_PER_PAGE, $fulllistsize, "$_SERVER[PHP_SELF]");
	}
}

$key=mysql_num_rows($sql);
if ($key==0) {
  $tool_content .= "<p>dsdsds</p>";

} else {
// Construct course list table
$tool_content .= "
    <table class=\"tbl_alt\" width=\"99%\">
    <tr>
      <th colspan='7'>
        <div align=\"right\">".$caption."</div>
      </th>
    </tr>
    <tr>
     <th scope=\"col\" width='1' class=\"odd\">&nbsp;</th>
     <th scope=\"col\" class=\"odd\"><div align=\"left\">".$langCourseCode."<br />".$langTeacher."</div></th>
     <th scope=\"col\" width=\"1\" class=\"odd\">".$langCourseVis."</th>
     <th scope=\"col\" width=\"220\" class=\"odd\"><div align=\"left\">".$langFaculty."</div></th>
     <th scope=\"col\" width=\"10\" class=\"odd\">".$langUsers."</th>
     <th scope=\"col\" width=\"30\" colspan='2' class=\"odd\">".$langActions."</th>
    </tr>";

$k = 0;
for ($j = 0; $j < mysql_num_rows($sql); $j++) {
	$logs = mysql_fetch_array($sql);
	if ($k%2 == 0) {
		$tool_content .= "
    <tr class=\"even\">";
	} else {
		$tool_content .= "
    <tr class=\"odd\">";
	}

	$tool_content .= "
      <td width='1'>
	<img style='margin-top:4px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet' /></td>
      <td><a href='{$urlServer}courses/$logs[code]/'><b>".htmlspecialchars($logs[2])."</b>
	</a> (".htmlspecialchars($logs[1]).")<br /><i>".$logs[3]."</i>
      </td>
      <td align='center'>";
	// Define course type
	switch ($logs[4]) {
		case 2:
			$tool_content .= "<img src='../../template/classic/img/OpenCourse.gif' title='$langOpenCourse' />";
			break;
		case 1:
			$tool_content .= "<img src='../../template/classic/img/Registration.gif' title='$langRegCourse' />";
			break;
		case 0:
			$tool_content .= "<img src='../../template/classic/img/ClosedCourse.gif' title='$langClosedCourse' />";
			break;
	}
	$tool_content .= "
      </td>
      <td>".htmlspecialchars($logs[0])."</td>";
	// Add links to course users, delete course and course edit
	$tool_content .= "
      <td align='center'><a href='listusers.php?c=".$logs['cours_id']."'>
	<img src='../../template/classic/img/user_list.gif' title='$langUsers' /></a>
      </td>
      <td align=\"center\" width='10'><a href='delcours.php?c=".$logs['cours_id']."'>
	<img src='../../template/classic/img/delete.gif' title='$langDelete'></a>
      </td>
      <td align=\"center\" width='20'><a href='editcours.php?c=".$logs[1]."".$searchurl."'>
	<img src='../../template/classic/img/edit.png' title='$langEdit'></a>
      </td>";
	$k++;
}
// Close table correctly
$tool_content .= "
    </tr>
    </table>";
// If a search is started display link to search page
if (isset($_GET['search']) && $_GET['search'] == "yes") {
	$tool_content .= "\n    <p align='right'><a href='searchcours.php'>".$langReturnSearch."</a></p>";
} elseif ($fulllistsize > COURSES_PER_PAGE) {
	// Display navigation in pages
	$tool_content .= show_paging($limit, COURSES_PER_PAGE, $fulllistsize, "$_SERVER[PHP_SELF]");
}

}
// Display link to index.php
$tool_content .= "\n    <p align='right'><a href='index.php'>".$langBack."</a></p>";
draw($tool_content,3);
