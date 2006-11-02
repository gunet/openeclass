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
$langFiles = array('admin','addadmin','registration');
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langNomPageAddHtPass;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";
// Initialize the incoming variables
$delete = isset($_GET['delete'])?$_GET['delete']:'';
$aid = isset($_GET['aid'])?$_GET['aid']:'';
$encodeLogin = isset($_POST['encodeLogin'])?$_POST['encodeLogin']:'';
/*****************************************************************************
		MAIN BODY
******************************************************************************/
if(!empty($encodeLogin)) 	// Check if a username has been posted
{
	$res = mysql_query("SELECT user_id FROM user WHERE username='$encodeLogin'");		// Search username in database
	if (mysql_num_rows($res) == 1) 	// Check that username exists
	{
		// If username exists insert userid to admin table and make the user administrator
		$row = mysql_fetch_row($res);
		if (mysql_query("INSERT INTO admin VALUES('$row[0]')")) 
			$tool_content .= "<p>$langUser $encodeLogin $langWith  id='$row[0]' $langDone</p>";
		else // If mysql_query failed print message
			$tool_content .= "<p>$langError</p>";
	} 
	else 
	{
		// If username does not exist in database, inform user about the result
		$tool_content .= "<p>$langUser $encodeLogin $langNotFound.</p>";
		$tool_content .= printform($langLogin);		// Display form again
	}
} 
else 	// No form post has been done
{ 
	// Display form
	$tool_content .= printform($langLogin);
}

// delete the admin
if((!empty($delete)) && ($delete=='1') && (!empty($aid)) && ($aid!='1'))
{
	if(!$r=db_query("DELETE FROM admin WHERE admin.idUser='".$aid."'"))
	{
		$tool_content .= "<center><br />Η διαγραφή του διαχειριστή με id:".$aid." δεν είναι εφικτή<br /></center>";
	}
}

// Display the list of admins
if($r1=db_query("SELECT user_id,prenom,nom,username FROM user,admin WHERE user.user_id=admin.idUser ORDER BY user_id"))
{
	$tool_content .= "<br /><center><table width=\"80%\"><thead><tr>
	<th scope=\"col\">ID</th>
	<th scope=\"col\">".$langSurname." - ".$langName."</th>
	<th scope=\"col\">".$langUsername."</th>";
	$tool_content .= "<th scope=\"col\">".$langActions."</th>";
	$tool_content .= "</tr></thead><tbody>";
	while($row = mysql_fetch_array($r1))
	{
		$tool_content .= "<tr>";
		$tool_content .= "<td>".htmlspecialchars($row['user_id'])."</td>".
		"<td>".htmlspecialchars($row['prenom'])." " .htmlspecialchars($row['nom'])."</td>".
		"<td>".htmlspecialchars($row['username'])."</td>";
		if($row['user_id']!=1)
		{
			$tool_content .= "<td><a href=\"addadmin.php?delete=1&aid=".$row['user_id']."\">Διαγραφή</a></td>";
		}
		$tool_content .= "</tr>";
	}
	$tool_content .= "</tbody></table></center><br />";
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
