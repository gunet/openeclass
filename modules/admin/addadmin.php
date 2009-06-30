<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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
	$res = mysql_query("SELECT user_id FROM user WHERE username='".mysql_real_escape_string($encodeLogin)."'");		// Search username in database
	if (mysql_num_rows($res) == 1) 	// Check that username exists
	{
		// If username exists insert userid to admin table and make the user administrator
		$row = mysql_fetch_row($res);
		if (mysql_query("INSERT INTO admin VALUES('$row[0]')"))
			$tool_content .= "<p class=\"success_small\">$langTheUser ".htmlspecialchars($encodeLogin)." $langWith  id='$row[0]' $langDone</p>";
		else // If mysql_query failed print message
			$tool_content .= "<p class=\"success_small\">$langErrorAddAdmin</p>";
	}
	else
	{
		// If username does not exist in database, inform user about the result
		$tool_content .= "<p class=\"caution_small\">$langTheUser ".htmlspecialchars($encodeLogin)." $langNotFound.</p>";
		$tool_content .= printform($langUsername);		// Display form again
	}
}
else 	// No form post has been done
{
	// Display form
	$tool_content .= printform($langUsername);
}

// delete the admin
if((!empty($delete)) && ($delete=='1') && (!empty($aid)) && ($aid!='1'))
{
	if(!$r=db_query("DELETE FROM admin WHERE admin.idUser='".$aid."'"))
	{
		$tool_content .= "<center><br />$langDeleteAdmin".$aid." $langNotFeasible  <br /></center>";
	}
}

// Display the list of admins
if($r1=db_query("SELECT user_id,prenom,nom,username FROM user,admin WHERE user.user_id=admin.idUser ORDER BY user_id"))
{
	$tool_content .= "
<p>
  <table width=\"99%\">
  <thead>
  <tr>
    <th class=\"left\">ID</th>
    <th class=\"left\">".$langSurname." - ".$langName."</th>
	<th class=\"left\">".$langUsername."</th>
    <th>".$langActions."</th>
  </tr>
  </thead>
  <tbody>";
	while($row = mysql_fetch_array($r1))
	{
		$tool_content .= "\n  <tr>";
		$tool_content .= "\n    <td>".htmlspecialchars($row['user_id'])."</td>".
		"\n    <td>".htmlspecialchars($row['prenom'])." " .htmlspecialchars($row['nom'])."</td>".
		"\n    <td>".htmlspecialchars($row['username'])."</td>";
		if($row['user_id']!=1)
		{
			$tool_content .= "\n    <td align=\"center\"><a href=\"addadmin.php?delete=1&aid=".$row['user_id']."\">$langDelete</a></td>";
		} else {
			$tool_content .= "\n    <td align=\"center\">---</td>";
        }
		$tool_content .= "\n  </tr>";
	}
	$tool_content .= "\n  </tbody>\n  </table>\n</p>\n<br />";
}
// Display link back to index.php
$tool_content .= "<p class=\"right\"><a href='index.php'>$langBack</a></p>";

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3);

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
	global $langAdd, $langInsertUserInfo,$langDeleteAdmin,$langNotFeasible;
	// Initialize $ret
	$ret = "";
	// Constract the display form
	$ret .= "
  <form method='post' name='makeadmin' action='$_SERVER[PHP_SELF]'>";
	$ret .= "
  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <tbody>
  <tr>
    <th width=\"220\">&nbsp;</th>
    <td><b>".$langInsertUserInfo."</b></td>
  </tr>
  <tr>
    <th class=\"left\">".$message."</th>
    <td><input type='text' name='encodeLogin' size='20' maxlength='30'></td>
  </tr>
  <tr>
    <th class=\"left\">&nbsp;</th>
    <td><input type='submit' name='crypt' value='$langAdd'></td>
  </tr>
  </tbody>
  </tbody>
  </table>
</form>";
	// Return $ret
	return $ret;
}

?>
