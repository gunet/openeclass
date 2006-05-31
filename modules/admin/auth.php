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
	auth.php
	@last update: 31-05-2006 by Stratos Karatzidis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
        @Description: Platform Authentication Methods and their settings

 	This script displays the alternative methods of authentication 
	and their settings.

 	The admin can: - choose a method and define its settings


==============================================================================
*/

// LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
$langFiles = array('admin','about');
include '../../include/baseTheme.php';
include_once '../auth/auth.inc.php';
@include "check_admin.inc";		// check if user is administrator
$nameTools = "Πιστοποίηση Χρηστών";		// Define $nameTools

$tool_content = "";			// Initialise $tool_content

$auth = get_auth_id();
	
$tool_content .= "<table width=\"99%\">
<tr><td>";

$tool_content .= "<form name=\"authmenu\" method=\"post\" action=\"auth_process.php\">
Επιλέξτε τον τρόπο πιστοποίησης χρηστών:<br /><br />
<input type=\"radio\" name=\"auth\" value=\"1\"";if($auth==1) $tool_content .= " checked"; $tool_content .= "\">ECLASS<br />
<input type=\"radio\" name=\"auth\" value=\"2\"";if($auth==2) $tool_content .= " checked"; $tool_content .= "\">POP3<br />
<input type=\"radio\" name=\"auth\" value=\"3\"";if($auth==3) $tool_content .= " checked"; $tool_content .= "\">IMAP<br />
<input type=\"radio\" name=\"auth\" value=\"4\"";if($auth==4) $tool_content .= " checked"; $tool_content .= "\">LDAP<br />
<input type=\"radio\" name=\"auth\" value=\"5\"";if($auth==5) $tool_content .= " checked"; $tool_content .= "\">EXTERNAL DB<br /><br />";
$tool_content .= "<input type=\"submit\" name=\"submit\" value=\"Συνέχεια\"><br />";
$tool_content .= "</form><br />";
$tool_content .="<br /></td></tr></table>";

draw($tool_content,3);
?>