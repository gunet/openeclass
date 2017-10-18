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
require_once 'include/lib/textLib.inc.php';
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
                    'level' => 'primary',
                    'show' => (get_mail_ver_status($uid) == EMAIL_VERIFIED) and (!empty($_SESSION['courses']))),
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
                        'url' => $urlAppend . "modules/message/index.php?upload=1&amp;id=$id",
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
            $providers .= "<div><span class='tag'>$langProviderConnectWith&nbsp;:&nbsp;</span>" . $providers_text . "</div>";
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
    if (get_config('eportfolio_enable')) {
        $eportfolio_html = "<div class='row'>
                                <div class='col-xs-12'>
                                    <div>
                                        <a href='".$urlServer."main/eportfolio/index.php?id=$id&token=".token_generate("eportfolio" . $id)."'>$langUserePortfolio</a>
                                    </div>
                                </div>
                        </div>";
        } else {
            $eportfolio_html = '';
        }
    $tool_content .= "
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
                            $eportfolio_html
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
        <div class='col-xs-12 col-sm-10 col-sm-offset-2'>
            <div class='profile-pers-info'><span class='tag'>$langFaculty : </span>";
            $departments = $user->getDepartmentIds($id);
                $i = 1;
                foreach ($departments as $dep) {
                    $br = ($i < count($departments)) ? '<br/>' : '';
                    $tool_content .= $tree->getFullPath($dep) . $br;
                    $i++;
                }
        $tool_content .= "</div>
            <div class='profile-pers-info'>
                <span class='tag'>$langProfileMemberSince : </span><span class='tag-value'>" . nice_format($userdata->registered_at, true) . "</span>
            </div>
        </div>
    </div>";
       
    // get completed certificates with public url
    $sql = Database::get()->queryArray("SELECT course_title, cert_title, cert_issuer, cert_id, assigned, identifier "
                                        . "FROM certified_users "
                                        . "WHERE user_fullname = ?s", uid_to_name($uid, 'fullname'));
    
    if (count($sql) > 0) {
        $tool_content .= "<div class='col-sm-10 col-sm-offset-2' style='padding-top:20px;'><h4>$langMyCertificates</h4></div>";
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='badge-container'>";                
        $tool_content .= "<div class='clearfix'>";
        foreach ($sql as $key => $certificate) {            
            $tool_content .= "<div class='col-xs-12 col-sm-4 col-xl-2'>";
            $tool_content .= "<a style='display:inline-block; width: 100%' <a href='../out.php?i=$certificate->identifier'>";
            $tool_content .= "<div class='certificate_panel' style='width:210px; height:120px;'>
                    <h4 class='certificate_panel_title' style='font-size:15px;  margin-top:2px;'>$certificate->cert_title</h4>
                    <div style='font-size:10px;'>" . claro_format_locale_date('%A, %d %B %Y', strtotime($certificate->assigned)) . "</div>
                    <div class='certificate_panel_issuer' style='font-size:11px;'>$certificate->cert_issuer</div>";                    
            $tool_content .= "</a>";            
            $tool_content .= "<div class='certificate_panel_state'>
                <i class='fa fa-check-circle fa-inverse state_success'></i>
            </div>";
            $tool_content .= "</div>";
            $tool_content .= "</div>";
        }                    
        $tool_content .= "</div></div></div>";
    }
        
    //get completed badges
    $gameQ = "SELECT a.*, b.title,"
            . " b.description, b.issuer, b.active, b.created, b.id, b.course_id"
            . " FROM user_badge a "
            . " JOIN badge b ON (a.badge = b.id) "
            . " WHERE a.user = ?d "
            . "AND a.completed = 1 "            
            . "AND b.active = 1 "
            . "AND b.bundle != -1 "
            . "AND (b.expires IS NULL OR b.expires > NOW())";
    $sql2 = Database::get()->queryArray($gameQ, $uid);            
    if (count($sql2) > 0) {
        $tool_content .= "<div class='col-sm-10 col-sm-offset-2' style='padding-bottom:30px;'><h4>$langBadges</h4></div>";
        $tool_content .= "<div class='row'>";
        $tool_content .= "<div class='badge-container'>";
        $tool_content .= "<div class='clearfix'>";
        foreach ($sql2 as $key => $badge) {
            $badge_filename = Database::get()->querySingle("SELECT filename FROM badge_icon WHERE id = 
                                                 (SELECT icon FROM badge WHERE id = ?d)", $badge->id)->filename;
            $tool_content .= "<div class='col-xs-6 col-sm-4'>";
            $tool_content .= "<a href='../../modules/progress/index.php?course=".course_id_to_code($badge->course_id)."&amp;badge_id=$badge->badge&amp;u=$badge->user' style='display: block; width: 100%'>
                <img class='center-block' src='$urlServer" . BADGE_TEMPLATE_PATH . "$badge_filename' width='100' height='100'>
                <h5 class='text-center' style='padding-top: 10px;'>
                    " . ellipsize($badge->title, 40) . "
                </h5>";            
            $tool_content .= "</a></div>";                                       
        }
        $tool_content .= "</div></div></div>";
    }
        
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

    if ($level == ACCESS_USERS) { // if we have allowed it
        return true;
    } elseif ($_SESSION['status'] == USER_TEACHER) { // if we are teacher
        return true;
    } elseif (isset($_GET['course'])) {
        $c = $_GET['course'];
        if ($_SESSION['courses'][$c] == USER_TEACHER) { // if we are course teacher
          return true;
        }
    } else {
        return false;
    }
}
