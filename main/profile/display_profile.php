<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
require_once 'modules/progress/process_functions.php';

$data['tree'] = new Hierarchy();
$data['user'] = new User();

$toolName = $langMyProfile;

if (isset($_GET['id']) and isset($_GET['token'])) {
    $data['id'] = $id = intval($_GET['id']);
    if (!token_validate($data['id'], $_GET['token'], 3600)) {
        forbidden($_SERVER['REQUEST_URI']);
    }
    $toolName = $langUserProfile;
} else {
    $data['id'] = $id = $uid;
}

$data['action_bar_blog_portfolio'] = $data['action_bar'] = $data['action_bar_unreg'] = '';
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
                array('title' => $langUserBlog,
                    'url' => "../../modules/blog/index.php?user_id=$uid&token=" . token_generate("personal_blog" . $id) . "",
                    'icon' => 'fa-columns',
                    'level' => 'primary-label',
                    'button-class' => 'submitAdminBtn mb-2 me-2 rounded-pill',
                    'show' => get_config('personal_blog')),
                array('title' => $langMyePortfolio,
                    'url' => "../eportfolio/index.php?id=$uid&token=" . token_generate("eportfolio" . $id) . "",
                    'icon' => 'fa-briefcase',
                    'level' => 'primary-label',
                    'button-class' => 'submitAdminBtn mb-2 me-2 rounded-pill',
                    'show' => get_config('eportfolio_enable')),
                array('title' => $langChangePass,
                    'url' => $passurl,
                    'icon' => 'fa-key',
                    'button-class' => 'submitAdminBtn mb-2 me-2 rounded-pill',
                    'show' => $allow_password_change,
                    'level' => 'primary-label'),
                array('title' => "$langNotifyActions $langsOfCourses",
                    'url' => "emailunsubscribe.php",
                    'icon' => 'fa-envelope',
                    'button-class' => 'submitAdminBtn mb-2 me-2 rounded-pill',
                    'level' => 'primary-label',
                    'show' => (get_mail_ver_status($uid) == EMAIL_VERIFIED) and (!empty($_SESSION['courses']))),
                array('title' => $langModProfile,
                    'url' => "profile.php",
                    'icon' => 'fa-edit',
                    'button-class' => 'submitAdminBtn mb-2 me-2 rounded-pill',
                    'level' => 'primary-label')
                // array('title' => $langUnregUser,
                //     'url' => $urlAppend."main/unreguser.php",
                //     'icon' => 'fa-trash-o',
                //     'button-class' => 'deleteAdminBtn mb-2 me-2 rounded-pill',
                //     'level' => 'primary-label')
                ));


        // $data['action_bar_blog_portfolio'] =
        //     action_bar(array(
                // array('title' => $langUserBlog,
                //     'url' => "../../modules/blog/index.php?user_id=$uid&token=" . token_generate("personal_blog" . $id) . "",
                //     'icon' => 'fa-columns',
                //     'level' => 'primary-label',
                //     'button-class' => 'btn-success',
                //     'show' => get_config('personal_blog')),
                // array('title' => $langMyePortfolio,
                //     'url' => "../eportfolio/index.php?id=$uid&token=" . token_generate("eportfolio" . $id) . "",
                //     'icon' => 'fa-briefcase',
                //     'level' => 'primary-label',
                //     'button-class' => 'btn-success',
                //     'show' => get_config('eportfolio_enable')
                //)));

        $data['action_bar_unreg'] =
            action_bar(array(
                array('title' => $langUnregUser,
                    'url' => "../unreguser.php",
                    'icon' => 'fa-trash-o',
                    'level' => 'primary-label',
                    'button-class' => 'btn-danger')
                ));
    } else {
        if (get_config('dropbox_allow_personal_messages')) {
            $data['action_bar'] =
                action_bar(array(
                    array('title' => $langProfileSendMail,
                        'url' => $urlAppend . "modules/message/index.php?upload=1&amp;id=$data[id]",
                        'icon' => 'fa-envelope',
                        'button-class' => 'submitAdminBtn mb-2 me-2 rounded-pill',
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

    $data['profile_img'] = profile_image($data['id'], IMAGESIZE_LARGE, 'img-responsive img-circle img-profile img-public-profile');
    

    $data['cert_completed'] = Database::get()->queryArray("SELECT cert_title,identifier,template_id,cert_issuer,assigned "
                                        . "FROM certified_users "
                                        . "WHERE user_fullname = ?s OR user_id = ?d", uid_to_name($uid, 'fullname'), $uid);

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
    $data['badge_completed'] = Database::get()->queryArray($gameQ, $uid);

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

    global $id;

    if ($id == $_SESSION['uid']) { // if we are current user
        return true;
    } else if ($level == ACCESS_USERS) { // if we have allowed it
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
