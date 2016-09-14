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


$require_login = true;
$require_valid_uid = TRUE;
include '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'modules/admin/custom_profile_fields_functions.php';

$tree = new Hierarchy();
$user = new User();

$toolName = $langMyProfile;

if (isset($_GET['id']) and isset($_GET['token'])) {
    $data['id'] = intval($_GET['id']);
    if (!token_validate($data['id'], $_GET['token'], 3600)) {
        forbidden($_SERVER['REQUEST_URI']);
    }
    $pageName = $langUserProfile;
} else {
    $data['id'] = $uid;
}

$data['userdata'] = Database::get()->querySingle("SELECT surname, givenname, username, email, status, phone, am, registered_at,
                                            has_icon, description, password,
                                            email_public, phone_public, am_public
                                        FROM user
                                        WHERE id = ?d", $data['id']);

if ($data['userdata']) {
    $auth = array_search($data['userdata']->password, $auth_ids);
    if (!$auth) {
        $auth = 1;
    }
    if ($auth != 1) {
        $allow_password_change = false;
    } else {
        $allow_password_change = true;
    }
    if ($uid == $data['id']) {
        $passurl = $urlServer . 'main/profile/password.php';
        $data['action_bar'] =
            action_bar(array(
                array('title' => $langEditProfile,
                    'url' => "profile.php",
                    'icon' => 'fa-edit',
                    'level' => 'primary-label'),
                array('title' => $langChangePass,
                    'url' => $passurl,
                    'icon' => 'fa-key',
                    'show' => $allow_password_change,
                    'level' => 'primary-label'),
                array('title' => $langEmailUnsubscribe,
                    'url' => "emailunsubscribe.php",
                    'icon' => 'fa-envelope',
                    'level' => 'primary',
                    'show' => (get_mail_ver_status($uid) == EMAIL_VERIFIED) and (!empty($_SESSION['courses']))),
                array('title' => $langUnregUser,
                    'url' => "../unreguser.php",
                    'icon' => 'fa-times',
                    'level' => 'primary')
                ));
    } else {
        if (get_config('dropbox_allow_personal_messages')) {
            $data['action_bar'] =
                action_bar(array(
                    array('title' => $langProfileSendMail,
                        'url' => $urlAppend . "modules/message/index.php?upload=1&amp;id=$data[id]",
                        'icon' => 'fa-envelope',
                        'level' => 'primary-label')
                    ));
        }
    }

    // hybridauth providers information. available only for the current user.
    $data['authFullName'] = $authFullName;
    if ($data['id'] == $uid) {
        $data['extAuthList'] = Database::get()->queryArray("SELECT auth.auth_id, auth_name FROM auth, user_ext_uid
            WHERE auth.auth_id = user_ext_uid.auth_id AND user_ext_uid.user_id = ?d", $data['id']);
    }

    $data['profile_img'] = profile_image($data['id'], IMAGESIZE_LARGE, 'img-responsive img-circle');


    if (!empty($data['userdata']->description)) {
        $tool_content .= "<div id='profile-about-me' class='row'>
                            <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>
                            <h4>$langProfileAboutMe</h4><div>
                                ".standard_text_escape($data['userdata']->description)."</div></div></div>";
    }
        $tool_content .= "
    <div id='profile-departments' class='row'>
        <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>            
            <div><span class='tag'>$langFaculty : </span>";
            $departments = $user->getDepartmentIds($data['id']);
                $i = 1;
                foreach ($departments as $dep) {
                    $br = ($i < count($departments)) ? '<br/>' : '';
                    $tool_content .= $tree->getFullPath($dep) . $br;
                    $i++;
                }
        $tool_content .= "</div>
            <div>
                <span class='tag'>$langProfileMemberSince : </span><span class='tag-value'>".$data['userdata']->registered_at."</span>
            </div>
        </div>
    </div>";
//render custom profile fields content
$tool_content .= render_profile_fields_content(array('user_id' => $data['id']));
$tool_content .= "</div>
        </div>
    </div>
</div>";
}

$data['menuTypeID'] = 1;
view('main.profile.index', $data);

/**
 * check access to user profiles
 * @global type $status
 * @param type $level
 * @return boolean
 */

function allow_access($level) {
        
    if ($level == ACCESS_USERS) {        
        return true;
    } elseif ($level == ACCESS_PROFS and $_SESSION['status'] == USER_TEACHER) {        
        return true;
    } else {
        return false;
    }
}
