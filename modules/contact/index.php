<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$require_current_course = TRUE;

$require_help = TRUE;
$helpTopic = 'Contact';
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$nameTools = $langContactProf;

// Initialise $tool_content
$tool_content = "";

$userdata = mysql_fetch_array(db_query("SELECT nom, prenom, email FROM user WHERE user_id=$uid", $mysqlMainDb));

if (empty($userdata['email'])) {
	if ($uid) {
		$tool_content .= sprintf('<p>'.$langEmailEmpty.'</p>', $urlServer.'modules/profile/profile.php');
		
	} else {
		$tool_content .= sprintf('<p>'.$langNonUserContact.'</p>', $urlServer);
	}
} elseif (isset($_POST['content'])) {
	$content = trim($_POST['content']);
	if (empty($content)) {
		$tool_content .= "<p>$langEmptyMessage</p>";
		$tool_content .= form();
	} else {
		$tool_content .= email_profs($currentCourse, $content,
			"$userdata[prenom] $userdata[nom]",
			$userdata['email']);
	}
} else {
	$tool_content .= form();
}

draw($tool_content, 2, 'admin');


// display form
function form() 
{
  $ret = "<p>$GLOBALS[langContactMessage]</p>
  <table width=99% align=center><tr><td>
  <form method='post' action='$_SERVER[PHP_SELF]'>
  <FIELDSET style='PADDING-RIGHT: 7px; PADDING-LEFT: 7px; PADDING-BOTTOM: 7px; PADDING-TOP: 7px'>
  <LEGEND>$GLOBALS[langIntroMessage]</LEGEND>
     <table border='0' align=center width='90%' cellspacing='2' cellpadding=1 >
     <tr>
        <td align=center>
        <textarea class=auth_input name='content' rows='20' cols='80'></textarea></td>
     </tr>
     <tr>
        <td align=center><input type='submit' name='submit' value='$GLOBALS[langSendMessage]'></td>
     </tr>
     </table>
  </FIELDSET>
  </form>
  </td></tr></table>";

return $ret;
}

// send email
function email_profs($course, $content, $from_name, $from_address)
{

	$ret = "<p>$GLOBALS[langSendingMessage]</p>";

	$profs = db_query("SELECT user.email AS email, user.nom AS nom,
		user.prenom AS prenom
		FROM cours_user JOIN user ON user.user_id = cours_user.user_id
		WHERE code_cours='$course' AND cours_user.statut=1");

	$message = sprintf($GLOBALS['langContactIntro'],
		$from_name, $from_address, $content);
	$subject = "$GLOBALS[langHeaderMessage] ($course - $GLOBALS[intitule])";

	while ($prof = mysql_fetch_array($profs)) {
		$to_name = $prof['prenom'].' '.$prof['nom'];
		$ret .= "<p><img src=../../images/teacher.gif> $to_name</p><br>\n";
		if (!send_mail($from_name,
			$GLOBALS['emailAdministrator'],
			$to_name,
			$prof['email'],
			$subject,
			$message,
			$GLOBALS['charset'],
			'Reply-To: '.qencode($from_name, $GLOBALS['charset']).
			     " <$from_address>")) {
			     $ret .= "<p>$GLOBALS[langErrorSendingMessage]</p>\n";
		}
	}
return $ret;
}
?>
