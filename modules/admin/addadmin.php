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
	addadmin.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================        
        @Description: Add a user to administrators

 	This script allows the administrator of the platform to search for a user
 	and make him administrator too.

 	The user can : - Search for a user and automatically make him administrator
                 - Return to main administrator page

 	@Comments: The script is organised in three sections.

 	1) Give a username and post form
  2) Search username in database and give administrator rights if found
  3) Display all on an HTML page

==============================================================================*/

/*****************************************************************************
		DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = array('admin','addadmin');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
check_admin();
// Define $nameTools
$nameTools = $langNomPageAddHtPass;
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Check if a username has been posted
if (isset($encodeLogin)) {
	// Search username in database
	$res = mysql_query("SELECT user_id FROM user WHERE username='$encodeLogin'");
	// Check that username exists
	if (mysql_num_rows($res) == 1) {
		// If username exists insert userid to admin table
		// and make the user administrator
		$row = mysql_fetch_row($res);
		if (mysql_query("INSERT INTO admin VALUES('$row[0]')")) 
			$tool_content .= "<p>$langUser $encodeLogin $langWith  id='$row[0]' $langDone</p>";
		 else // If mysql_query failed print message
			$tool_content .= "<p>$langError</p>";
	} else {
		// If username does not exist in database
		// Inform user about the result
		$tool_content .= "<p>$langUser $encodeLogin $langNotFound.</p>";
		// Display form again
		$tool_content .= printform($langLogin);
	}
} else { // No form post has been done
	// Display form
	$tool_content .= printform($langLogin);
}
// Display link back to index.php
$tool_content .= "<center><p><a href='index.php'>$langBack</a></p></center>";

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');

/*****************************************************************************
		FUNCTIONS
******************************************************************************/

/*****************************************************************************
	 			function draw
****************************************************************************** 
  This method constracts a simple form where the administrator searches for
  a user by username to give user administrator permissions
  printform($message) 
  $tool_content: (String) The string to display for username

  @returns
  $ret: (String) The constracted form
******************************************************************************/
function printform ($message) { 
	global $langAdd, $langInsertUserInfo;
	// Initialize $ret
	$ret = "";
	// Constract the display form
	$ret .= "<form method='post' name='makeadmin' action='$_SERVER[PHP_SELF]'>";
	$ret .= "<table width=\"99%\"><caption>".$langInsertUserInfo."</caption><tbody>
	<tr><td width=\"3%\" nowrap>".$message."</td><td><input type='text' name='encodeLogin' size='20' maxlength='30'></td></tr>
	<tr><td colspan=\"2\"><input type='submit' name='crypt' value='$langAdd'></td></tr></tbody></table></form>";
	// Return $ret
	return $ret;
}

?>