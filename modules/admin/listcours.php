<?php
/**=============================================================================
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

/**===========================================================================
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

/*****************************************************************************
		DEAL WITH  BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Other includes
include 'admin.inc.php';
// Define $nameTools
$nameTools = $langListCours;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";
$caption = "";
/*****************************************************************************
		MAIN BODY
******************************************************************************/

// Initialize some variables
$searchurl = "";

// Manage list limits
$countcourses = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS cnt FROM cours"));
$fulllistsize = $countcourses['cnt'];
$listsize = 50;
$limit = isset($_GET['limit'])?$_GET['limit']:0;

// A search has been submitted
if (isset($search) && $search=="yes") {
	$searchurl = "&search=yes";
	// Search from post form
	if (isset($search_submit)) {
		$searchtitle = $formsearchtitle;
		session_register('searchtitle');
		$searchcode = $formsearchcode;
		session_register('searchcode');
		$searchtype = $formsearchtype;
		session_register('searchtype');
		$searchfaculte = $formsearchfaculte;
		session_register('searchfaculte');
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
		$searchcours[] = "intitule LIKE '".mysql_escape_string($searchtitle)."%'";
	}
	if(!empty($searchcode)) {
		$searchcours[] = "code LIKE '".mysql_escape_string($searchcode)."%'";
	}
	if ($searchtype!="-1") {
		$searchcours[] = "visible = '".mysql_escape_string($searchtype)."'";
	}
	if($searchfaculte!="0") {
		$searchcours[] = "faculte = '".mysql_escape_string($searchfaculte)."'";
	}
	$query=join(' AND ',$searchcours);
	if (!empty($query)) {
		$sql=mysql_query("SELECT faculte, code, intitule,titulaires,visible FROM cours WHERE $query ORDER BY faculte");
		$caption .= "$langFound ".mysql_num_rows($sql)." $langCourses ";
	} else {
		$sql=mysql_query("SELECT faculte, code, intitule,titulaires,visible FROM cours ORDER BY faculte");
		$caption .= "$langFound ".mysql_num_rows($sql)." $langCourses ";			
	}
}
// Normal list, no search, select all courses
else {
	$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours"));		
	$caption .= "".$langManyExist." ".$a[0]." $langCourses";
	$sql = mysql_query("SELECT faculte, code, intitule,titulaires,visible FROM cours ORDER BY faculte,code LIMIT ".$limit.",".$listsize."");
	
	if ($fulllistsize > $listsize ) {
		// Display navigation in pages
		$tool_content .= show_paging($limit, $listsize, $fulllistsize, "listcours.php");
	}
}
// Construct cours list table
$tool_content .= "<table width=\"99%\"><caption>".$caption."</caption>
<thead>
  <tr>
    <th scope=\"col\">".$langDepartment."</th>
    <th scope=\"col\">".$langCourseCode."</th>
    <th scope=\"col\">".$langTitle." (".$langTeacher.")</th>
    <th scope=\"col\">".$langCourseVis."</th>
    <th scope=\"col\">".$langUsers."</th>
    <th scope=\"col\" colspan='2'>".$langActions."</th>
  </tr>
</thead><tbody>\n";

for ($j = 0; $j < mysql_num_rows($sql); $j++) {
	$logs = mysql_fetch_array($sql);
	$tool_content .= "  <tr>\n";
	 for ($i = 0; $i < 2; $i++) {
	 	$tool_content .= "    <td width=\"500\">".htmlspecialchars($logs[$i])."</td>\n";
	}
	$tool_content .= "    <td width='500'>".htmlspecialchars($logs[2])." (".$logs[3].")</td>\n";
	// Define course type
	switch ($logs[4]) {
	case 2:
		$tool_content .= "    <td>$langOpenCourse</td>\n";
		break;
	case 1:
		$tool_content .= "    <td>$langRegCourse</td>\n";
		break;
	case 0:
		$tool_content .= "    <td>$langClosedCourse</td>\n";
		break;
	}
	// Add links to course users, delete course and course edit
	$tool_content .= "    <td><a href=\"listusers.php?c=".$logs[1]."\">".$langUsers."</a></td>
    <td><a href=\"delcours.php?c=".$logs[1]."\"><img src='../../images/delete.gif' title='$langDelete' border='0'></img></a></td>
    <td><a href=\"editcours.php?c=".$logs[1]."".$searchurl."\"><img src='../../images/edit.gif' title='$langEdit' border='0'></img></a></td>\n";
}
// Close table correctly
$tool_content .= "</tbody></table>\n";
// If a search is started display link to search page
if (isset($search) && $search=="yes") {
	$tool_content .= "<br><center><p><a href=\"searchcours.php\">".$langReturnSearch."</a></p></center>";
} elseif ($fulllistsize > $listsize) {
	// Display navigation in pages
	$tool_content .= show_paging($limit, $listsize, $fulllistsize, "listcours.php");
}
// Display link to index.php	
$tool_content .= "<br><center><p><a href=\"index.php\">".$langBack."</a></p></center>";

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3);
?>
