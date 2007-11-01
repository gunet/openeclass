<?php
session_start();
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
	editcours.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
        @Description: Show all information of a course and give links to edit

 	This script allows the administrator to see all available information of
 	a course and select other links to edit that information

 	The user can : - See all available course information
 	               - Select a link to edit some information
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Gather course information
  2) Embed available choices
  3) Display all on an HTML page
  
  @todo: Create a valid link for course statistics

==============================================================================*/

/*****************************************************************************
		DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = array('admin','gunet');
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';

if (isset($_GET['c'])) {
	$c = $_GET['c'];
	$_SESSION['c_temp']=$c;
}

if(!isset($c))
	$c=$_SESSION['c_temp'];


// Define $nameTools
$nameTools = $langCourseEdit;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "listcours.php", "name" => $langListCours);
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/

// Initialize some variables
$searchurl = "";

// Manage order of display list
if (isset($ord)) {
	switch ($ord) {
		case "s":
			$order = "b.statut"; break;
		case "n":
			$order = "a.nom"; break;
		case "p":
			$order = "a.prenom"; break;
		case "u":
			$order = "a.username"; break;
		default:
			$order = "b.statut"; break;
	}
} else {
	$order = "b.statut";
}
// A course has been selected
if (isset($c)) {
	// Define $searchurl to go back to search results
	if (isset($search) && ($search=="yes")) {
		$searchurl = "&search=yes";
	}
	// Get information about selected course
	$sql = mysql_query(
		"SELECT * FROM cours WHERE code = '".mysql_real_escape_string($c)."'");
	$row = mysql_fetch_array($sql);
	// Display course information and link to edit
	$tool_content .= "<table width=\"99%\"><caption>".$langCourseInfo." (<a href=\"infocours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langChange."</a>)</caption><tbody>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>".$langDepartment.":</b></td>
    <td>".$row['faculte']."</td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>".$langCode.":</b></td>
    <td>".$row['code']."</td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>".$langTitle.":</b></td>
    <td>".$row['intitule']."</td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>".$langTutor.":</b></td>
    <td>".$row['titulaires']."</td>
</tr>";
	$tool_content .= "</tbody></table><br>\n";
	// Display course quota and link to edit
	$tool_content .= "<table width=\"99%\"><caption>".$langQuota." (<a href=\"quotacours.php?c=".htmlspecialchars($c).$searchurl."\">".$langChange."</a>)</caption><tbody>";
	// Get information about course quota
	$q = mysql_fetch_array(mysql_query("SELECT code,intitule,doc_quota,video_quota,group_quota,dropbox_quota 
			FROM cours WHERE code='".mysql_real_escape_string($c)."'"));
	$tool_content .= "  <tr>
    <td colspan=\"2\"><i>$langTheCourse <b>$q[intitule]</b> $langMaxQuota</i><br></td>
  </tr>";			
	$dq = $q['doc_quota'] / 1000000;
	$vq = $q['video_quota'] / 1000000;
	$gq = $q['group_quota'] / 1000000;
	$drq = $q['dropbox_quota'] / 1000000;
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap>$langLegend <b>$langDocument</b>:</td>
    <td>".$dq." Mb.</td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap>$langLegend <b>$langVideo</b>:</td>
    <td>".$vq." Mb.</td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap>$langLegend <b>$langGroup</b>:</td>
    <td>".$gq." Mb.</td>
</tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap>$langLegend <b>$langDropbox</b>:</td>
    <td>".$drq." Mb.</td>
</tr>";
	$tool_content .= "</tbody></table><br>\n";
	// Display course type and link to edit
	$tool_content .= "<table width=\"99%\"><caption>".$langCourseStatus." (<a href=\"statuscours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langChange."</a>)</caption><tbody>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>".$langCurrentStatus.":</b></td>
    <td>";
	switch ($row['visible']) {
	case 2:
		$tool_content .= "Ανοιχτό";
		break;
	case 1:
		$tool_content .= "Απαιτείται Εγγραφή";
		break;
	case 0:
		$tool_content .= "Κλειστό";
		break;
	}	
    $tool_content .= "</td>
</tr></tbody></table><br>\n";
	// Display other available choices
	$tool_content .= "<table width=\"99%\"><caption>".$langOtherActions."</caption><tbody>";
	// Users list
	$tool_content .= "  <tr>
    <td><a href=\"listusers.php?c=".htmlspecialchars($c)."\">".$langListUsersActions."</a></td>
  </tr>";
  // Register unregister users
	$tool_content .= "  <tr>
    <td><a href=\"addusertocours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langQuickAddDelUserToCours."</a></td>
  </tr>";
  // Course statistics
	$tool_content .= "  <tr>
    <td>".$langStatsCourse."</td>
  </tr>";
  // Backup course
	$tool_content .= "  <tr>
    <td><a href=\"../course_info/archive_course.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langTakeBackup."<a/></td>
  </tr>";
  // Delete course
	$tool_content .= "  <tr>
    <td><a href=\"delcours.php?c=".htmlspecialchars($c)."".$searchurl."\">".$langCourseDelFull."</a></td>
  </tr>";
	$tool_content .= "</tbody></table>";

	// If a search is on display link to go back to listcours with search results
	if (isset($search) && ($search=="yes")) {
		$tool_content .= "<br><center><p><a href=\"listcours.php?search=yes\">".$langReturnToSearch."</a></p></center>";
	}
	// Display link to go back to listcours.php
	$tool_content .= "<br><center><p><a href=\"listcours.php\">".$langReturn."</a></p></center>";
}
// If $c is not set we have a problem
else {
	// Print an error message
	$tool_content .= "<br><center><p>Παρουσιάστηκε σφάλμα στην επιλογή μαθήματος!</p></center>";
	// Display link to go back to listcours.php
	$tool_content .= "<br><center><p><a href=\"listcours.php\">Επιστροφή</a></p></center>";
}

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3, 'admin');
?>
