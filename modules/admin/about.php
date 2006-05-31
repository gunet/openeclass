<?php
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
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
	about.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
        @Description: About page for the administrator

 	This script displays information about GUnet e-Class version and about the
 	server running (PHP version, Apache version, MySQL version).

 	The user can : - See the information
                 - Return to main administrator page

 	@Comments: The script is organised in two sections.

 	1) Gather the information
  2) Display them on an HTML page

==============================================================================*/

/*****************************************************************************
		DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = array('admin','about');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
@include "check_admin.inc";
// Define $nameTools
$nameTools = $langVersion;
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Constract a table with all information
$tool_content .= "<table width=\"99%\">
<tbody>
<tr valign=\"top\"><td><br>
<p align=center>".$langAboutText."</p>
<p align=center><b>".$langEclassVersion."</b></p>
<p align=center>".$langHostName."<b>".$SERVER_NAME."</b></p>	
<p align=center>".$langWebVersion."<b>".$SERVER_SOFTWARE."</b></p>";
// Check if we have mysql database to display its information
if (extension_loaded('mysql')) 
	$tool_content .= "<p align=center>$langMySqlVersion<b>".mysql_get_server_info()."</b></p>";
else // If not display message no MySQL
	$tool_content .= "<p align=center font color=\"red\">".$langNoMysql."</p>";
// Close table correctly
$tool_content .="
<br></tbody></td></tr></table>";
// Display link back to index.php
$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>