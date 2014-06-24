<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
include '../include/baseTheme.php';
require_once 'include/log.php';

$nameTools = $langUnregUser;
$navigation[] = array("url" => "profile/profile.php", "name" => $langModifyProfile);

if (!isset($_GET['doit']) or $_GET['doit'] != "yes") {
    // admin cannot be deleted
    if ($is_admin) {
        $tool_content .= "<div class='caution'>$langAdminNo";
        $tool_content .= "<br /><a href='../profile/profile.php'>$langBack</a></div>";
        draw($tool_content, 1);
        exit;
    } else {
        $q = Database::get()->querySingle("SELECT code, visible FROM course, course_user
			WHERE course.id = course_user.course_id
                        AND course.visible != " . COURSE_INACTIVE . "
			AND user_id = ?d LIMIT 1", $uid);        
        if (!$q) {
            $tool_content .= "<p><b>$langConfirm</b></p>";
            $tool_content .= "<ul class='listBullet'>";
            $tool_content .= "<li>$langYes: ";
            $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?doit=yes'>$langDelete</a>";
            $tool_content .= "</li>";
            $tool_content .= "<li>$langNo: <a href='profile/profile.php'>$langBack</a>";
            $tool_content .= "</li></ul>";
            $tool_content .= "</td></tr></table>";
        } else {
            $tool_content .= "<div class='caution'><b>$langNotice: </b> ";
            $tool_content .= "$langExplain<br />";
            $tool_content .= "<span class='right'><a href='profile/profile.php'>$langBack</a></span></div>\n";
        }
    }  //endif is admin
} else {
    if (isset($uid)) {
        $un = uid_to_name($uid, 'username');
        $n = uid_to_name($uid);
        deleteUser($id, false);
        // action logging
        Log::record(0, 0, LOG_DELETE_USER, array('uid' => $uid,
                                                 'username' => $un,
                                                 'name' => $n));
        unset($_SESSION['uid']);
        $tool_content .= "<div class='success'><b>$langDelSuccess</b><br />";
        $tool_content .= "$langThanks";
        $tool_content .= "<br /><a href='../index.php?logout=yes'>$langLogout</a></div>";
    }
}
if (isset($_SESSION['uid'])) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}