<?php

/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/
$require_login = TRUE;
$guest_allowed = true;
include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');
$nameTools = "Αποστολή ενημερωτικού email στον διδάσκοντα";
$tool_content = "";

if (isset($_POST["submit"])) {
//	$tool_content .= "<table width=\"99%\"><tbody><tr><td>";

	$sql=db_query("SELECT * FROM cours_user WHERE cours_id = (SELECT cours_id FROM cours WHERE code = "  . quote($_POST['cc']) . ")");

	while ($m = mysql_fetch_array($sql)) {

		$sql1 = db_query("SELECT email FROM user WHERE user_id='".$m["user_id"]."'");
		$m1 = mysql_fetch_array($sql1);
		$to = $m1["email"];
		$emailsubject = "Εγγραφή σε κλειστό μάθημα";
		$emailbody = "Ο φοιτητής με τα παρακάτω στοιχεία επιθυμεί την εγγραφή του στο μάθημά σας:

$lastname $firstname
Email: $email

Σχόλια: $body";
$errorExists = false;
		if (!send_mail('', '', '', $to,
		$emailsubject, $emailbody, $charset)) {
			$tool_content .= "
				<table width=\"99%\">
	<tbody>
	<tr>
	<td class=\"caution\">'$langEmailNotSend' '$to'!
	</td>
	</tr>
	</tbody>";
			$errorExists = true;
		}
	}
	if (@!$errorExists) {
		$tool_content .= "
	<table width=\"99%\">
	<tbody>
	<tr>
	<td class=\"success\">$emailsuccess
	</td>
	</tr>
	</tbody>
	</table>";
	}
}
else
{
	$sql = "SELECT * FROM user WHERE user_id='".$uid."'";
	$res = mysql_query($sql);
	$row = mysql_fetch_array($res);

	if (!isset($_GET['fc'])) {
		$_GET['fc'] = "";
	}
	$_GET['fc'] = htmlspecialchars($_GET['fc']);

	if (!isset($_GET['cc'])) {
		$_GET['cc'] = "";
	}
	$_GET['cc'] = htmlspecialchars($_GET['cc']);
	$tool_content .= "<p><a href=\"courses.php?fc=".$_GET['fc']."\">$langReturn</a></p>";
	$tool_content .= "<form action=\"".$_SERVER['PHP_SELF']."?fc=".$_GET['fc']."\" method=\"post\">
<table width=\"99%\"><thead>";
	$tool_content .= "<tr><th>Όνομα:</th><td><input type=\"text\" name=\"firstname\" value=\"".$row["nom"]."\" readonly></td></tr>";
	$tool_content .= "<tr><th>Επίθετο:</th><td><input type=\"text\" name=\"lastname\" value=\"".$row["prenom"]."\" readonly></td></tr>";
	$tool_content .= "<tr><th>Email:</th><td><input type=\"text\" name=\"email\" value=\"".$row["email"]."\" readonly></td></tr>";
	$tool_content .= "<th>Σχόλια:</th><td><textarea rows=\"6\" cols=\"40\" name=\"body\"></textarea></td></tr>";
	$tool_content .= "</thead></table>
	<br/>
	<input type=\"submit\" name=\"submit\" value=\"Αποστολή\"><input type=\"hidden\" name=\"cc\" value=\"".$_GET['cc']."\">
	</form>";

}

draw($tool_content,1,'admin');

