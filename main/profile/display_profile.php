<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


$require_login = true;
$require_valid_uid = TRUE;
include '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';

$tree = new Hierarchy();
$user = new User();

$nameTools = $langUserProfile;

$userdata = array();

if (isset($_GET['id']) and isset($_GET['token'])) {
    $id = intval($_GET['id']);
    if (!token_validate($id, $_GET['token'], 3600)) {
        forbidden($_SERVER['REQUEST_URI']);
    }
} else {
    $navigation[] = array('url' => 'profile.php', 'name' => $langModifyProfile);
    $id = $uid;
}

$userdata = Database::get()->querySingle("SELECT surname, givenname, email, phone, am,
                                            has_icon, description, password,
                                            email_public, phone_public, am_public
                                        FROM user
                                        WHERE id = ?d", $id);

if ($userdata) {
    $auth = array_search($userdata->password, $auth_ids);
    if (!$auth) {
        $auth = 1;
    }    
    if ($auth != 1) {
        $allow_password_change = false;
    } else {
        $allow_password_change = true;
    }    
    if ($uid == $id) {
        $passurl = $urlSecure . 'main/profile/password.php';
        $tool_content .= 
                action_bar(array(
                    array('title' => $langModifyProfile,
                        'url' => "profile.php",
                        'icon' => 'fa-edit',
                        'level' => 'primary'),
                    array('title' => $langChangePass,
                        'url' => $passurl,
                        'icon' => 'fa-key',
                        'show' => $allow_password_change,
                        'level' => 'primary'),
                    array('title' => $langEmailUnsubscribe,
                        'url' => "emailunsubscribe.php",
                        'icon' => 'fa-envelope',
                        'level' => 'primary'),
                    array('title' => $langUnregUser,
                        'url' => "../unreguser.php",
                        'icon' => 'fa-times',
                        'button-class'=>'btn-danger',
                        'level' => 'primary')
                    ));    
    } 
    $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
            <div class='row'>
                <div class='col-md-12'>
                    " . profile_image($id, IMAGESIZE_LARGE, 'img-responsive') . "
                </div>
            </div>
            <div class='row'>
                <div class='col-md-12'>
                    " . q("$userdata->givenname $userdata->surname") . "
                </div>
            </div>";
            if (!empty($userdata->email) and allow_access($userdata->email_public)) {
                $tool_content .= "<div class='row'>
                                    <div class='col-md-12'>$langEmail:
                                    " . mailto($userdata->email) . "
                                </div>
                            </div>";
            }
            if (!empty($userdata->am) and allow_access($userdata->am_public)) {
                $tool_content .= "<div class='row'>
                                    <div class='col-md-12'>$langAm:
                                    " . q($userdata->am) . "
                                </div>
                            </div>";
            }
            if (!empty($userdata->phone) and allow_access($userdata->phone_public)) {
                $tool_content .= "<div class='row'>
                                    <div class='col-md-12'>$langPhone:
                                    " . q($userdata->phone) . "
                                </div>
                            </div>";
            }
            $tool_content .= "
            <div class='row'>
                <div class='col-md-12'>";
                    $departments = $user->getDepartmentIds($id);
                        $i = 1;
                        foreach ($departments as $dep) {
                            $br = ($i < count($departments)) ? '<br/>' : '';
                            $tool_content .= $tree->getFullPath($dep) . $br;
                            $i++;
                        }
                $tool_content .= "</div>
            </div>";
                if (!empty($userdata->description)) {
                    $tool_content .= "<div class='row'>
                                        <div class='col-md-12'>
                                            ".standard_text_escape($userdata->description)."</div></div>";
                }
$tool_content .= "</div>
        </div>";
}

draw($tool_content, 1);

function allow_access($level) {
    global $uid, $status;

    if ($level == ACCESS_USERS and $uid > 0) {
        return true;
    } elseif ($level == ACCESS_PROFS and $status = 1) {
        return true;
    } else {
        return false;
    }
}
