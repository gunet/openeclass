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
	about.php
	@last update: 03-05-2006 by Vagelis Pitsiougas
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
        @Description: About page for eclass version

 	This script displays information about eclass version and 
 	server.

 	The user can : - navigate through files and directories.
                       - upload a file
                       - delete, copy a file or a directory
                       - edit properties & content (name, comments, 
			 html content)

 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
   	2) Define the directory to display
  	3) Read files and directories from the directory defined in part 2
  	4) Display all of that on an HTML page
 
  	@todo: eliminate code duplication between
 	document/document.php, scormdocument.php
==============================================================================
*/


$langFiles = array('admin','about');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = $langVersion;
// Initialise $tool_content
$tool_content = "";
	
$tool_content .= "<tr><td><table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"100%\">
<tr valign=\"top\" bgcolor=\"".$color2."\">
<td><br><font size=\"2\" face=\"arial, helvetica\">
<p align=center>".$langAboutText."</p>
<p align=center><b>".$langEclassVersion."</b></p>
<p align=center>".$langHostName."<b>".$SERVER_NAME."</b></p>	
<p align=center>".$langWebVersion."<b>".$SERVER_SOFTWARE."</b></p>";

if (extension_loaded('mysql')) 
	$tool_content .= "<p align=center>$langMySqlVersion<b>".mysql_get_server_info()."</b></p>";
else 
	$tool_content .= "<p align=center font color=\"red\">".$langNoMysql."</p>";

$tool_content .="
</font><br>
</td></tr></table>";

draw($tool_content,3);
?>