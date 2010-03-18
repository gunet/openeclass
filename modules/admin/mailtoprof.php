<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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
	mailtoprof.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Send mail to the users of the platform

 	This script allows the administrator to send a message by email to all
 	users or just the professors of the platform

 	The user can : - Send a message by email to all users or just the pofessors
                 - Return to main administrator page

 	@Comments: The script is organised in three sections.

  1) Write message and select where to send it
  2) Try to send the message by email
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
// Include functions needed to send email
include('../../include/sendMail.inc.php');
// Define $nameTools
$nameTools=$sendinfomail;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/

// Send email after form post
if (isset($_POST['submit']) && ($_POST['body_mail'] != "") && ($_POST['submit'] == $langSend)) {
	// Where to send the email
	if ($_POST['sendTo'] == "0") {
		// All users
		$sql = mysql_query("SELECT DISTINCT email FROM user");
	} elseif ($_POST['sendTo'] == "1") {
		// Only professors
		$sql = mysql_query("SELECT DISTINCT email FROM user where statut='1'");
	} else { die(); } // invalid sendTo var

	// Send email to all addresses
	while ($m = mysql_fetch_array($sql)) {
		$to = $m[0];
		$emailsubject = $infoabouteclass;
		$emailbody = "".$_POST['body_mail']."

$langManager $siteName
$administratorName $administratorSurname
$langTel $telephone
$langEmail : $emailhelpdesk
";
		if (!send_mail('', '', '', $to,
			$emailsubject, $emailbody, $charset)) {
				$tool_content .= "<p class=\"caution_small\">".$langEmailNotSend." ".$to."!</p>";
		}
	}
	// Display result and close table correctly
	$tool_content .= "<p class=\"success_small\">$emailsuccess</p>";
} else {
        // Display form to administrator
        $tool_content .= "
<form action='$_SERVER[PHP_SELF]' method='post'>
  <table class='FormData'>
  <tbody>
  <tr>
    <th class='left'>$typeyourmessage</th>
	<td><textarea class='auth_input' name='body_mail' rows='10' cols='60'></textarea></td>
  </tr>
  <tr>
    <th class='left'>$langSendMessageTo</th>
    <td><select name='sendTo'>
          <option value='1'>$langProfOnly</option>
          <option value='0'>$langToAllUsers</option>
        </select>
    </td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td><input type=\"submit\" name=\"submit\" value=\"$langSend\"></input></td>
  </tr>
  </tbody>
  </table>
</form>";

}
// Display link back to index.php
$tool_content .= "<p>&nbsp;</p><p align=\"right\"><a href=\"index.php\">".$langBack."</a></p>";

/*****************************************************************************
		DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>
