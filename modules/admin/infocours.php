<?php
/**=============================================================================
       	GUnet eClass 2.0 
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

/*****************************************************************************
		DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Initialize some variables
$searchurl = "";

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
if(!isset($_GET['c'])) { die(); }
// Define $nameTools
$nameTools = $langCourseInfo;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "listcours.php", "name" => $langListCours);
$navigation[] = array("url" => "editcours.php?c=".htmlspecialchars($_GET['c']), "name" => $langCourseEdit);
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Define $searchurl to go back to search results
if (isset($search) && ($search=="yes")) {
	$searchurl = "&search=yes";
}
// Update cours basic information
if (isset($submit))  {
  // Get faculte ID and faculte name for $faculte
  // $faculte example: 12--Tmima 1
  list($facid, $facname) = split("--", $faculte);
  // Update query
	$sql = mysql_query("UPDATE cours SET faculte='$facname', titulaires='$titulaires', intitule='$intitule', faculteid='$facid' WHERE code='".mysql_real_escape_string($_GET['c'])."'");
	// Some changes happened
	if (mysql_affected_rows() > 0) {
		$sql = mysql_query("UPDATE cours_faculte SET faculte='$facname', facid='$facid' WHERE code='".mysql_real_escape_string($_GET['c'])."'");
		$tool_content .= "<p>".$langCourseEditSuccess."</p>";
	}
	// Nothing updated
	else {
		$tool_content .= "<p>".$langNoChangeHappened."</p>";
	}

}
// Display edit form for course basic information
else {
	// Get course information
	$row = mysql_fetch_array(mysql_query("SELECT * FROM cours WHERE code='".mysql_real_escape_string($_GET['c'])."'"));
	// Constract the edit form
	$tool_content .= "<form action=".$_SERVER['PHP_SELF']."?c=".htmlspecialchars($_GET['c'])."".$searchurl." method=\"post\">";	
	$tool_content .= "<table width=\"99%\"><caption>".$langCourseInfoEdit."</caption><tbody>";
	$tool_content .= "<tr>
    <td width=\"3%\" nowrap><b>".$langDepartment.":</b></td>
    <td><select name=\"faculte\">\n";
  // Construct select object for facultes
	$resultFac=mysql_query("SELECT id,name FROM faculte ORDER BY number");

	while ($myfac = mysql_fetch_array($resultFac)) {	
		if($myfac['id'] == $row['faculteid']) 
			$tool_content .= "<option value=\"".$myfac['id']."--".$myfac['name']."\" selected>$myfac[name]</option>";
		else 
			$tool_content .= "<option value=\"".$myfac['id']."--".$myfac['name']."\">$myfac[name]</option>";
	}
	$tool_content .= "</select>
    </td>
  </tr>";  
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>".$langCourseCode.":</b></td>
    <td><i>".$row['code']."</i></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>".$langTitle.":</b></td>
    <td><input type=\"text\" name=\"intitule\" value=\"".$row['intitule']."\" size=\"60\"></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><b>".$langTeacher.":</b></td>
    <td><input type=\"text\" name=\"titulaires\" value=\"".$row['titulaires']."\" size=\"60\"></td>
  </tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><br><input type='submit' name='submit' value='$langModify'></td>
  </tr>";
	$tool_content .= "</tbody></table></form>\n";
}
// If course selected go back to editcours.php
if (isset($_GET['c'])) {
	$tool_content .= "<center><p><a href=\"editcours.php?c=".htmlspecialchars($_GET['c'])."".$searchurl."\">".$langBack."</a></p></center>";
}
// Else go back to index.php directly
else {
	$tool_content .= "<center><p><a href=\"index.php\">".$langBackAdmin."</a></p></center>";
}

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>
