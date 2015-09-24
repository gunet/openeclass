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

$userdata = array();

if (isset($_GET['id']) and isset($_GET['token'])) {
    $id = intval($_GET['id']);
    if (!token_validate($id, $_GET['token'], 3600)) {
        forbidden($_SERVER['REQUEST_URI']);
    }
    $pageName = $langUserProfile;
} else {
    $id = $uid;
}

$userdata = Database::get()->querySingle("SELECT surname, givenname, username, email, status, phone, am, registered_at,
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
        $passurl = $urlServer . 'main/profile/password.php';
        $tool_content .= 
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
                    'level' => 'primary'),
                array('title' => $langUnregUser,
                    'url' => "../unreguser.php",
                    'icon' => 'fa-times',
                    'level' => 'primary')
                ));    
    } else {
        if (get_config('dropbox_allow_personal_messages')) {
            $tool_content .= 
                action_bar(array(
                    array('title' => $langProfileSendMail,
                        'url' => "../../modules/dropbox/index.php?upload=1&id=$id",
                        'icon' => 'fa-envelope',
                        'level' => 'primary-label')
                    ));
        }
    }

    // hybridauth providers information. available only for the current user.
    $providers = '';
    if ($id == $uid) {
        $providers_text = '';
        $extAuthList = Database::get()->queryArray("SELECT auth.auth_id, auth_name FROM auth, user_ext_uid
            WHERE auth.auth_id = user_ext_uid.auth_id AND user_ext_uid.user_id = ?d", $id);
        foreach ($extAuthList as $item) {
            $fullName = q($authFullName[$item->auth_id]);
            $providers_text .= " <span class='tag-value'><img src='$themeimg/{$item->auth_name}.png' alt=''>&nbsp;$fullName</span>";
        }
        if (!empty($providers_text)) {
            $providers .= "</div><span class='tag'>$langProviderConnectWith&nbsp;:&nbsp;</span>" . $providers_text . "</div>";
        }
    }
    
    if (get_config('personal_blog')) {
        $perso_blog_html = "<div class='row'>
                                <div class='col-xs-12'>
                                    <div>
                                        <a href='".$urlServer."modules/blog/index.php?user_id=$id'>$langUserBlog</a>
                                    </div>
                                </div>
                            </div>";
    } else {
        $perso_blog_html = '';
    }
    /*if (!empty($userdata->email) and allow_access($userdata->email_public)) { // E-mail
        $tool_content .= "<div class='profile-pers-info'><span class='tag'>$langEmail :</span> <span class='tag-value'>" . mailto($userdata->email) . "</span></div>";}
    if (!empty($userdata->phone) and allow_access($userdata->phone_public)) { // Phone Number
        $tool_content .= "<div class='profile-pers-info'><span class='tag'>$langPhone :</span> <span class='tag-value'>" . q($userdata->phone) . "</span></div>";}
    if (!empty($userdata->am) and allow_access($userdata->am_public)) { // Register Number
        $tool_content .= "<div class='profile-pers-info-data'><span class='tag'>$langAm :</span> <span class='tag-value'>" . q($userdata->am) . "</span></div>";}
    *//*$tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
            <div class='row'>
                <div class='col-xs-12 col-sm-2'>
                    <div id='profile-avatar'>" . profile_image($id, IMAGESIZE_LARGE, 'img-responsive img-circle') . "</div>
                </div>
                <div class='col-xs-12 col-sm-10 profile-pers-info'>
                    <div class='profile-pers-info-name'>" . q("$userdata->givenname $userdata->surname") . "</div>"; // Name & Surname
                    if (!empty($userdata->email) and allow_access($userdata->email_public)) { // E-mail
                        $tool_content .= "<div class='profile-pers-info'><span class='tag'>$langEmail :</span> <span class='tag-value'>" . mailto($userdata->email) . "</span></div>";}
                    if (!empty($userdata->phone) and allow_access($userdata->phone_public)) { // Phone Number
                        $tool_content .= "<div class='profile-pers-info'><span class='tag'>$langPhone :</span> <span class='tag-value'>" . q($userdata->phone) . "</span></div>";}
                    if (!empty($userdata->am) and allow_access($userdata->am_public)) { // Register Number
                        $tool_content .= "<div class='profile-pers-info-data'><span class='tag'>$langAm :</span> <span class='tag-value'>" . q($userdata->am) . "</span></div>";}
    */$tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='panel panel-default'>
                <div class='panel-body'>
                    <div id='pers_info' class='row'>
                        <div class='col-xs-12 col-sm-2'>
                            <div id='profile-avatar'>" . profile_image($id, IMAGESIZE_LARGE, 'img-responsive img-circle') . "</div>
                        </div>
                        <div class='col-xs-12 col-sm-10 profile-pers-info'>
                            <div class='row profile-pers-info-name'>
                                <div class='col-xs-12'>
                                    <div>" . q("$userdata->givenname $userdata->surname") . "</div>
                                    <div class='not_visible'>(".q($userdata->username).")</div>
                                </div>
                            </div>
                            $perso_blog_html
                            <div class='row'>
                                <div class='col-xs-6'>
                                    <h4>$langProfilePersInfo</h4>
                                    <div class='profile-pers-info'>
                                        <span class='tag'>$langEmail :</span>";
                if (!empty($userdata->email) and allow_access($userdata->email_public)) { 
                    $tool_content .= " <span class='tag-value'>" . mailto($userdata->email) . "</span>";
                } else {
                    $tool_content .= " <span class='tag-value not_visible'> - $langProfileNotAvailable - </span>";
                }
                $tool_content .= "</div>
                                  <div class='profile-pers-info'>
                                    <span class='tag'>$langPhone :</span>";
                if (!empty($userdata->phone) and allow_access($userdata->phone_public)) { // Phone Number
                    $tool_content .= " <span class='tag-value'>" . q($userdata->phone) . "</span>";

                } else {
                    $tool_content .= " <span class='tag-value not_visible'> - $langProfileNotAvailable - </span>";
                }
                $tool_content .= "</div>
                                  <div class='profile-pers-info'><span class='tag'>$langStatus :</span>";
                if (!empty($userdata->status)) { // Status
                    $message_status = (q($userdata->status)==1)?$langTeacher:$langStudent;
                    $tool_content .= " <span class='tag-value'>$message_status</span>";

                } else {
                    $tool_content .= " <span class='tag-value not_visible'> - $langProfileNotAvailable - </span>";
                }
                $tool_content .= "</div>
                                  <div class='profile-pers-info-data'>
                                    <span class='tag'>$langAm :</span>";
                if (!empty($userdata->am) and allow_access($userdata->am_public)) {

                    $tool_content .= " <span class='tag-value'>" . q($userdata->am) . "</span>";

                } else {
                    $tool_content .= " <span class='tag-value not_visible'> - $langProfileNotAvailable - </span>";

                }
                $tool_content .= $providers;
                $tool_content .= "</div>
                        </div> <!-- end of col-xs-6 -->
                    </div> <!-- end of row -->
                </div> <!-- end of col-xs-12 profile-pers-info -->
            </div> <!-- end of pers_info row -->";
    if (!empty($userdata->description)) {
        $tool_content .= "<div id='profile-about-me' class='row'>
                            <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>
                            <h4>$langProfileAboutMe</h4><div>
                                ".standard_text_escape($userdata->description)."</div></div></div>";
    }
        $tool_content .= "
    <div id='profile-departments' class='row'>
        <div class='col-xs-12 col-md-10 col-md-offset-2 profile-pers-info'>            
            <div><span class='tag'>$langHierarchyNode : </span>";
            $departments = $user->getDepartmentIds($id);
                $i = 1;
                foreach ($departments as $dep) {
                    $br = ($i < count($departments)) ? '<br/>' : '';
                    $tool_content .= $tree->getFullPath($dep) . $br;
                    $i++;
                }
        $tool_content .= "</div>
            <div>
                <span class='tag'>$langProfileMemberSince : </span><span class='tag-value'>$userdata->registered_at</span>
            </div>
        </div>
    </div>";
//render custom profile fields content
$tool_content .= render_profile_fields_content(array('user_id' => $id));
$tool_content .= "</div>
        </div>
    </div>
</div>";
}
draw($tool_content, 1);

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
