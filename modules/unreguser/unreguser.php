<?
/*
=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
A full copyright notice can be read in "/info/copyright.txt".

Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
==============================================================================
*/

$require_login = TRUE;
$langFiles = 'unreguser';

include '../../include/baseTheme.php';

$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"../profile/profile.php", "name"=> $langModifProfile);

$tool_content = "";

if (!isset($doit) or $doit != "yes") {
	$tool_content .=  "<table width=99%><tbody>";
	$tool_content .=  "<tr><td class=\"caution\">";

	// admin cannot be deleted
	if ($is_admin) {
		$tool_content .=  "<p><b>$langAdminNo</b></p>";
		$tool_content .=  "<p><a href='../profile/profile.php'>$langBack</a></p>";
		$tool_content .= "</td></tr></tbody></table>";
		draw($tool_content,1);
		exit;
	} else {
		$q = db_query ("SELECT code_cours FROM cours_user WHERE user_id = '$uid'") ;
		if (mysql_num_rows($q) == 0) {
			$tool_content .=  "<p><b>$langConfirm</b></p>";
			$tool_content .=  "<ul class=\"listBullet\">";
			$tool_content .=  "<li>$langYes: ";
			$tool_content .=  "<a href='$_SERVER[PHP_SELF]?u=$uid&doit=yes'>$langDelete</a>";
			$tool_content .=  "</li>";
			$tool_content .=  "<li>$langNo: <a href='../profile/profile.php'>$langBack</a>";
			$tool_content .=  "</li></ul>";
			$tool_content .= "</td></tr></tbody></table>";
		} else {
			$tool_content .=  "<p><b>$langNotice: </b>";
			$tool_content .=  "$langExplain</p>";
			$tool_content .=  "<p><a href='../profile/profile.php'>$langBack</a></p>";
			$tool_content .= "</td></tr></tbody></table>";
		}
	}  //endif is admin
} else {
	if (isset($uid)) {
		$tool_content .=  "<table width=99%><tbody>";
		$tool_content .=  "<tr>";
		$tool_content .=  "<td class=\"success\">";
		db_query("DELETE from user WHERE user_id = '$uid'");
		if (mysql_affected_rows() > 0) {
			$tool_content .=  "<p><b>$langDelSuccess</b></p>";
			$tool_content .=  "<p>$langThanks</p>";
			$tool_content .=  "<br><a href='../../index.php?logout=yes'>$langLogout</a>";
			unset($_SESSION['uid']);
		} else {
			$tool_content .=  "<p>$langError</p>";
			$tool_content .=  "<p><a href='../profile/profile.php'>$langBack</a></p><br>";
			//			exit;
		}
	}

	$tool_content .= "</td></tr></tbody></table>";
}
if (isset($_SESSION['uid'])) {
	draw($tool_content, 1);
} else {
	draw($tool_content, 0);
}
?>

