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

$toolName = $langMyProfile;
$pageName = $langUnregUser;
$navigation[] = array("url" => "profile/profile.php", "name" => $langModifyProfile);

if (!isset($_GET['doit']) or $_GET['doit'] != "yes") {
    // admin cannot be deleted
    if ($is_admin) {
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "profile/display_profile.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
        $tool_content .= "<div class='alert alert-danger'>$langAdminNo</div>";
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
        } else {
            $tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "profile/profile.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));
            $tool_content .= "<div class='alert alert-danger'>$langNotice:</br> ";
            $tool_content .= "$langExplain</div>";
        }
    }  //endif is admin
} else {
    if (isset($uid)) {
        $un = uid_to_name($uid, 'username');
        $n = uid_to_name($uid);
        deleteUser($uid, false);
        // action logging
        Log::record(0, 0, LOG_DELETE_USER, array('uid' => $uid,
            'username' => $un,
            'name' => $n));
        unset($_SESSION['uid']);
        $tool_content .= action_bar(array(
            array('title' => $langLogout,
                'url' => "../index.php?logout=yes",
                'icon' => 'fa-sign-out',
                'level' => 'primary-label')));
        $tool_content .= "<div class='alert alert-success'>$langDelSuccess</br>$langThanks</div>";
    }
}
if (isset($_SESSION['uid'])) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}
