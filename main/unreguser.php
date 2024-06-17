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
require_once '../include/baseTheme.php';
require_once 'include/log.class.php';

$toolName = $langMyProfile;
$pageName = $langUnregUser;
$navigation[] = array('url' => 'profile/profile.php', 'name' => $langModifyProfile);

if (!isset($_POST['doit'])) {
    // admin cannot be deleted
    if ($is_admin) {
        $tool_content .= "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langAdminNo</span></div></div>";
        draw($tool_content, 1);
        exit;
    } else {
        $q = Database::get()->querySingle("SELECT code, visible FROM course, course_user
			WHERE course.id = course_user.course_id
                AND course.visible != " . COURSE_INACTIVE . "
                AND user_id = ?d LIMIT 1", $uid);
        if (!$q) {
            $tool_content .= "
            <div class='col-12'>
            <div class='form-wrapper form-edit rounded mt-4'>
                <form class='form-horizontal' method='post' action='$_SERVER[SCRIPT_NAME]'>

                  <div class='form-group'>
                    <div class='col-sm-12 control-label-notes'>
                      $langConfirm
                    </div>
                  </div>

                  <div class='form-group mt-4'>
                    <div class='d-inline-flex align-items-center'>
                      <label class='pe-2'>$langYes:</label>
                      <button class='btn deleteAdminBtn' name='doit'><i class='fa fa-trash-o'></i> $langUnregUser</button>
                    </div>
                  </div>

                  <div class='form-group mt-4'>
                    <div class='d-inline-flex align-items-center'>
                      <label class='pe-2'>$langNo:</label>
                      <a href='{$urlAppend}main/profile/display_profile.php' class='btn btn-sm btn-outline-secondary'><i class='fa fa-reply'></i> $langCancel</a>
                    </div>
                  </div>
                </form>
              </div></div>";
        } else {
            $tool_content .= "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langNote:</br>$langExplain</span></div></div>";
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

        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
        session_destroy();
        $uid = 0;

        $action_bar = action_bar(array(
            array('title' => $langLogout,
                'url' => "../index.php?logout=yes",
                'icon' => 'fa-sign-out',
                'level' => 'primary-label')));
        $tool_content .= $action_bar;
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langDelSuccess</br>$langThanks</span></div></div>";
    }
}
if (isset($_SESSION['uid'])) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 0);
}
