<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$require_login = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langUnregUser;
$navigation[]= array ("url"=>"../profile/profile.php", "name"=> $langModifyProfile);

if (!isset($_GET['doit']) or $_GET['doit'] != "yes") {

	// admin cannot be deleted
	if ($is_admin) {
		$tool_content .=  "<div class='caution'>$langAdminNo";
		$tool_content .=  "<br /><a href='../profile/profile.php'>$langBack</a></div>";
		draw($tool_content, 1);
		exit;
	} else {
		$q = db_query ("SELECT code, visible FROM cours, cours_user
			WHERE cours.cours_id = cours_user.cours_id
                        AND cours.visible != ".COURSE_INACTIVE."
			AND user_id = '$uid' LIMIT 1") ;
		if (mysql_num_rows($q) == 0) {
			$tool_content .=  "<p><b>$langConfirm</b></p>\n";
			$tool_content .=  "<ul class=\"listBullet\">\n";
			$tool_content .=  "<li>$langYes: ";
			$tool_content .=  "<a href='$_SERVER[SCRIPT_NAME]?doit=yes'>$langDelete</a>";
			$tool_content .=  "</li>\n";
			$tool_content .=  "<li>$langNo: <a href='../profile/profile.php'>$langBack</a>";
			$tool_content .=  "</li>\n        </ul>";
			$tool_content .= "</td>\n        </tr>\n        </table>\n";
		} else {
			$tool_content .=  "<div class='caution'><b>$langNotice: </b> ";
			$tool_content .=  "$langExplain<br />\n";
			$tool_content .=  "<span class='right'><a href='../profile/profile.php'>$langBack</a></span></div>\n";
		}
	}  //endif is admin
} else {
	if (isset($uid)) {
                // unregister user from inactive courses (if any)
                 db_query("DELETE from cours_user WHERE user_id = $uid");
                 db_query("DELETE FROM group_members WHERE user_id = $uid");
                 // finally delete user
		 db_query("DELETE from user WHERE user_id = $uid");
              
		if (mysql_affected_rows() > 0) {
			$tool_content .=  "<div class=\"success\"><b>$langDelSuccess</b><br />\n";
			$tool_content .=  "$langThanks\n";
			$tool_content .=  "<br /><a href='../../index.php?logout=yes'>$langLogout</a></div>";
			unset($_SESSION['uid']);
		} else {
			$tool_content .=  "<p>$langError</p>\n";
			$tool_content .=  "<p class='right'><a href='../profile/profile.php'>$langBack</a></p>\n        <br />\n";
                        $tool_content .=  "</div>\n";			
		}
	}
}
if (isset($_SESSION['uid'])) {
	draw($tool_content, 1);
} else {
	draw($tool_content, 0);
}