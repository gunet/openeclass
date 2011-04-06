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

$require_login = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"../profile/profile.php", "name"=> $langModifProfile);

$tool_content = "";

if (!isset($_GET['doit']) or $_GET['doit'] != "yes") {
	$tool_content .=  "
        <div class='caution'>";

	// admin cannot be deleted
	if ($is_admin) {
		$tool_content .=  "$langAdminNo</div>";
		$tool_content .=  "<p class='right'><a href='../profile/profile.php'>$langBack</a></p>";

		draw($tool_content,1);
		exit;
	} else {
		$q = db_query ("SELECT code FROM cours, cours_user
			WHERE cours.cours_id = cours_user.cours_id
			AND user_id = '$uid' LIMIT 1") ;
		if (mysql_num_rows($q) == 0) {
			$tool_content .=  "        <p><b>$langConfirm</b></p>\n";
			$tool_content .=  "        <ul class=\"listBullet\">\n";
			$tool_content .=  "          <li>$langYes: ";
			$tool_content .=  "<a href='$_SERVER[PHP_SELF]?doit=yes'>$langDelete</a>";
			$tool_content .=  "</li>\n";
			$tool_content .=  "          <li>$langNo: <a href='../profile/profile.php'>$langBack</a>";
			$tool_content .=  "</li>\n        </ul>";
			$tool_content .= "</td>\n        </tr>\n        </table>\n";
		} else {
			$tool_content .=  "        <p><b>$langNotice: </b>";
			$tool_content .=  "$langExplain</p>\n";
			$tool_content .=  "        <p class='right'><a href='../profile/profile.php'>$langBack</a></p>\n";
			$tool_content .= "</td>\n        </tr>\n        </table>\n";
		}
	}  //endif is admin
} else {
	if (isset($uid)) {
		$tool_content .=  "        <table class='tbl'>\n";
		$tool_content .=  "        <tr>\n";
		$tool_content .=  "          <td class=\"success\">\n";
		db_query("DELETE from user WHERE user_id = '$uid'");
		if (mysql_affected_rows() > 0) {
			$tool_content .=  "        <p><b>$langDelSuccess</b></p>\n";
			$tool_content .=  "        <p>$langThanks</p>\n";
			$tool_content .=  "        <br><a href='../../index.php?logout=yes'>$langLogout</a>";
			unset($_SESSION['uid']);
		} else {
			$tool_content .=  "        <p>$langError</p>\n";
			$tool_content .=  "        <p class='right'><a href='../profile/profile.php'>$langBack</a></p>\n        <br />\n";
			//			exit;
		}
	}
	$tool_content .= "</td>\n        </tr>\n        </table>\n";
}
if (isset($_SESSION['uid'])) {
	draw($tool_content, 1);
} else {
	draw($tool_content, 0);
}
?>

