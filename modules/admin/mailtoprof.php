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
// Set the langfiles needed
$langFiles = array('gunet','registration','admin');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
@include('check_admin.inc');
// Include functions needed to send email
include('../../include/sendMail.inc.php');
// Define $nameTools
$nameTools=$sendinfomail;
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
		MAIN BODY
******************************************************************************/
// Constract a table for display
$tool_content .= "<table width=\"99%\"><tbody><tr><td>";
// Send email after form post
if (isset($submit) && ($body_mail!="")) {
	// Where to send the email
	if ($sendTo=="0") {
		// All users
		$sql=mysql_query("SELECT DISTINCT email FROM user");
	} elseif ($sendTo=="1") {
		// Only professors
		$sql=mysql_query("SELECT DISTINCT email FROM user where statut='1'");
	}
	// Send email to all addresses
	while ($m = mysql_fetch_array($sql)) {
		$to = $m[0];
		$emailsubject = $infoabouteclass;
		$emailbody = "$body_mail 

$langManager $siteName
$administratorName $administratorSurname
Τηλ. $telephone
$langEmail : $emailAdministrator
";
		if (!send_mail($siteName, $emailAdministrator, '', $to,
			$emailsubject, $emailbody, $charset)) {
				$tool_content .= "<h4>Σφάλμα κατά την αποστολή e-mail στη διεύθυνση '$to'!</h4>";
		}
	}
	// Display result and close table correctly
	$tool_content .= "<h4>$emailsuccess</h4></td></tr><tbody></table>";
}
// Display form to administrator
else {
	// Constract form
	$tool_content .= "<h5>".$typeyourmessage."</h5>";
	$tool_content .= "<form action=\"".$_SERVER[PHP_SELF]."\" method=\"post\">
<textarea name=\"body_mail\" rows=\"10\" cols=\"60\"></textarea>
<br><br>
Αποστολή μηνύματος προς <select name=\"sendTo\">
<option value=\"0\">Όλους τους χρήστες</option>
<option value=\"1\">Μόνο στους εκπαιδευτές</option></select><br><br><input type=\"submit\" name=\"submit\" value=\"Αποστολή\"></input>
</form></td></tr></tbody></table>";

}
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