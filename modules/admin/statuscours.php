<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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
	statuscours.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
        @Description: Edit status of a course

 	This script allows the administrator to edit the status of a selected
 	course

 	The user can : - Edit the status of a course
                 - Return to edit course list

 	@Comments: The script is organised in four sections.

  1) Get course status information
  2) Edit that information
  3) Update course status
  4) Display all on an HTML page
  
==============================================================================*/

/*****************************************************************************
		DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = array('course_info', 'create_course', 'opencours','admin');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
check_admin();
// Define $nameTools
$nameTools = $langCourseEdit;
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Initialize some variables
$searchurl = "";

// Define $searchurl to go back to search results
if (isset($search) && ($search=="yes")) {
	$searchurl = "&search=yes";
}
// Update course status
if (isset($submit))  {
  // Update query
	$sql = mysql_query("UPDATE cours SET visible='$formvisible' WHERE code='$c'");
	// Some changes occured
	if (mysql_affected_rows() > 0) {
		$tool_content .= "<p>".$langCourseStatusChangedSuccess."</p>";
	}
	// Nothing updated
	else {
		$tool_content .= "<p>".$langNoChangeHappened."</p>";
	}

}
// Display edit form for course status
else {
	// Get course information
	$row = mysql_fetch_array(mysql_query("SELECT * FROM cours WHERE code='$c'"));
	$visible = $row['visible'];
	$visibleChecked[$visible]="checked";
	// Constract edit form
	$tool_content .= "<form action=".$_SERVER['PHP_SELF']."?c=".$c."".$searchurl." method=\"post\">";
	$tool_content .= "<table width=\"99%\"><caption>".$langCourseStatusChange."</caption><tbody>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><i>$langConfTip</i></td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><input type=\"radio\" name=\"formvisible\" value=\"2\"".@$visibleChecked[2]."></td>
    <td>".$langPublic."</td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><input type=\"radio\" name=\"formvisible\" value=\"1\"".@$visibleChecked[1]."></td>
    <td>".$langPrivOpen."</td>
  </tr>";
	$tool_content .= "  <tr>
    <td width=\"3%\" nowrap><input type=\"radio\" name=\"formvisible\" value=\"0\"".@$visibleChecked[0]."></td>
    <td>".$langPrivate."</td>
  </tr>";
	$tool_content .= "  <tr>
    <td colspan=\"2\"><br><input type='submit' name='submit' value='$langModify'></td>
  </tr>";
	$tool_content .= "</tbody></table></form>\n";
}
// If course selected go back to editcours.php
if (isset($c)) {
	$tool_content .= "<center><p><a href=\"editcours.php?c=".$c."".$searchurl."\">".$langReturn."</a></p></center>";
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