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
		$tool_content .= email_profs($cours_id, $content,
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
  $ret = "
  <form method='post' action='$_SERVER[PHP_SELF]'>

  <table class=\"FormData\" width=\"99%\" align=\"left\">
  <tbody>
  <tr>
    <th>&nbsp;</th>
    <td>$GLOBALS[langContactMessage]</td>
  </tr>
  <tr>
    <th class=\"left\">$GLOBALS[langIntroMessage]</th>
    <td><textarea class=auth_input name='content' rows='10' cols='80'></textarea></td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td><input type='submit' name='submit' value='$GLOBALS[langSendMessage]' /></td>
  </tr>
  </tbody>
  </table>

  </form>";

return $ret;
}

// send email
function email_profs($cours_id, $content, $from_name, $from_address)
{
        $q = db_query("SELECT fake_code FROM cours WHERE cours_id = $cours_id");
        list($fake_code) = mysql_fetch_row($q);

	$ret = "<p>$GLOBALS[langSendingMessage]</p>";

	$profs = db_query("SELECT user.email AS email, user.nom AS nom,
		user.prenom AS prenom
		FROM cours_user JOIN user ON user.user_id = cours_user.user_id
		WHERE cours_id = $cours_id AND cours_user.statut=1");

	$message = sprintf($GLOBALS['langContactIntro'],
		$from_name, $from_address, $content);
	$subject = "$GLOBALS[langHeaderMessage] ($fake_code - $GLOBALS[intitule])";

	while ($prof = mysql_fetch_array($profs)) {
		$to_name = $prof['prenom'].' '.$prof['nom'];
		$ret .= "<p><img src=../../template/classic/img/teacher.gif> $to_name</p><br>\n";
		if (!send_mail($from_name,
                               $from_address,
                               $to_name,
                               $prof['email'],
                               $subject,
                               $message,
                               $GLOBALS['charset'])) {
                        $ret .= "<p>$GLOBALS[langErrorSendingMessage]</p>\n";
		}
	}
return $ret;
}
