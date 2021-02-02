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
require_once 'include/lib/textLib.inc.php';
require_once 'modules/admin/custom_profile_fields_functions.php';

$tree = new Hierarchy();
$user = new User();

$toolName = $langMyProfile;

$userdata = array();
$action_bar_content = '';

if (isset($_GET['id']) and isset($_GET['token'])) {
    $id = intval($_GET['id']);
    if (!token_validate($id, $_GET['token'], 3600)) {
        forbidden($_SERVER['REQUEST_URI']);
    }
    $toolName = $langUserProfile;
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
        $action_bar_content =
            action_bar(array(
                array('title' => $langModify,
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
                    'show' => (get_mail_ver_status($uid) == EMAIL_VERIFIED) and (!empty($_SESSION['courses'])))
                ));
    } else {
        if (get_config('dropbox_allow_personal_messages')) {
            $action_bar_content =
                action_bar(array(
                    array('title' => $langProfileSendMail,
                        'url' => $urlAppend . "modules/message/index.php?upload=1&amp;id=$id",
                        'icon' => 'fa-envelope',
                        'level' => 'primary-label',
                        'button-class' => 'btn-success')
                    ));
        }
    }

        $action_bar_unreg =
                action_bar(array(
                    array('title' => $langUnregUser,
                        'url' => "../unreguser.php",
                        'icon' => 'fa-times',
                        'level' => 'primary-label',
                        'button-class' => 'btn-danger')
                    ));

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

        $action_bar_blog_portfolio =
                action_bar(array(
                    array('title' => $langUserBlog,
                        'url' => "../../modules/blog/index.php?user_id=$id&token=" . token_generate("personal_blog" . $id) . "",
                        'icon' => 'fa-columns',
                        'level' => 'primary-label',
                        'button-class' => 'btn-success',
                        'show' => get_config('personal_blog')),
                    array('title' => $langMyePortfolio,
                        'url' => "../eportfolio/index.php?id=$id&token=" . token_generate("eportfolio" . $id) . "",
                        'icon' => 'fa-briefcase',
                        'level' => 'primary-label',
                        'button-class' => 'btn-success',
                        'show' => get_config('eportfolio_enable')
                    )));

            $tool_content .= "<div class='row'>
                <div class='col-xs-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='inner-heading clearfix'>
                            $action_bar_content
                        </div>
                        <div class='row'>
                            <div class='col-sm-6'>
                                <div class='row'>
                                    <div class='col-xs-4'>
                                        <div id='profile-avatar'>" . profile_image($id, IMAGESIZE_LARGE, 'img-responsive img-circle') . "</div>
                                    </div>
                                    <div class='col-xs-8'>
                                        <div class='profile-name'>" . q("$userdata->givenname $userdata->surname") . "</div>
                                        <div class='not_visible'><strong>" . q($userdata->username) . "</strong></div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                $action_bar_blog_portfolio
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-sm-6'>
                                <div class='profile-content-panel'>
                                    <div class='profile-content-panel-title'>
                                        $langProfilePersInfo
                                    </div>
                                    <div class='profile-content-panel-text'>";
                                        // user email
                                        if (!empty($userdata->email) and allow_access($userdata->email_public)) {
                                            $tool_content .= "<div style='line-height:26px;'>
                                            <span style='font-weight: bold; color: #888;'>
                                                $langEmail:
                                            </span>";
                                            $tool_content .= mailto($userdata->email);
                                            $tool_content .= $providers;
                                            $tool_content .= "</div>";
                                        }
                                        // user phone
                                        if (!empty($userdata->phone) and allow_access($userdata->phone_public)) {
                                            $tool_content .= "<div style='line-height:26px;'>
                                                    <span style='font-weight: bold; color: #888;'>
                                                        $langPhone:
                                                    </span>";
                                            $tool_content .= q($userdata->phone);$tool_content .= "</div>";
                                        }
                                        $tool_content .= "<div style='line-height:26px;'>
                                        <span style='font-weight: bold; color: #888;'>
                                            $langStatus:
                                        </span>";
                                        if ($userdata->status == USER_TEACHER) {
                                            $tool_content .= $langTeacher;
                                        } else {
                                            $tool_content .= $langStudent;
                                        }
                                        $tool_content .= "</div>";
                                        // user 'am'
                                        if (!empty($userdata->am) and allow_access($userdata->am_public)) {
                                            $tool_content .= "<div style='line-height:26px;'>
                                            <span style='font-weight: bold; color: #888;'>
                                                $langAm:
                                            </span>";
                                            $tool_content .= q($userdata->am);
                                            $tool_content .= "</div>";
                                        }
                                        $tool_content .= "<div style='line-height:26px;'>
                                            <span style='font-weight: bold; color: #888;'>
                                                $langFaculty:
                                            </span>";
                                            $departments = $user->getDepartmentIds($id);
                                            $i = 1;
                                            foreach ($departments as $dep) {
                                                $br = ($i < count($departments)) ? '<br/>' : '';
                                                $tool_content .= $tree->getFullPath($dep) . $br;
                                                $i++;
                                            }

                                        $tool_content .= "</div>
                                        <div style='line-height:26px;'>
                                            <span style='font-weight: bold; color: #888;'>
                                                $langProfileMemberSince:
                                            </span>" . nice_format($userdata->registered_at, true) . "
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='profile-content-panel'>
                                    <div class='profile-content-panel-title'>
                                        $langProfileAboutMe
                                    </div>
                                    <div class='profile-content-panel-text'>
                                        <p>";
                                        if (!empty($userdata->description)) {
                                            $tool_content .= standard_text_escape($userdata->description);
                                        }
                                        $tool_content .= "</p>
                                    </div>
                                </div>
                            </div>
                        </div>";
                        // custom profile fields
                        $tool_content .= render_profile_fields_content(array('user_id' => $id));
                    $tool_content .= "</div>
                </div>
            </div>
        </div>";

        // get completed certificates with public url
        $sql = Database::get()->queryArray("SELECT course_title, cert_title, cert_issuer, cert_id, assigned, identifier "
                                            . "FROM certified_users "
                                            . "WHERE user_fullname = ?s", uid_to_name($uid, 'fullname'));

        if (count($sql) > 0) {
            $tool_content .= "<div class='panel panel-default'>
                              <div class='panel-body'>";
            $tool_content .= "<div class='col-sm-10' style='padding-top:20px;'><h4>$langMyCertificates</h4></div>";
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
            $tool_content .= "</div></div>";
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
            $tool_content .= "<div class='panel panel-default'>
                              <div class='panel-body'>";
            $tool_content .= "<div class='col-sm-10' style='padding-bottom:30px;'><h4>$langBadges</h4></div>";
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
            $tool_content .= "</div></div>";
        }

        if ($uid == $id) {

            if (get_config('activate_privacy_policy_text') and get_config('activate_privacy_policy_consent')) {
                $consent = Database::get()->querySingle('SELECT * FROM user_consent WHERE user_id = ?d', $uid);
                $consentButtons = [
                    "<button type='submit' class='btn btn-success btn-block' name='accept_policy' value='yes'>$langAccept</button>",
                    "<button type='submit' class='btn btn-danger btn-block' name='accept_policy' value='no'>$langRejectRequest</button>"];
                if ($consent) {
                    if ($consent->has_accepted) {
                        $consent_text = $langYouHaveConsentedToPrivacyPolicy;
                        $consentButtons = $consentButtons[1];
                    } else {
                        $consent_text = $langYouHaveRejectedPrivacyPolicy;
                        $consentButtons = $consentButtons[0];
                    }
                    $consentDate = claro_format_locale_date($dateTimeFormatLong, strtotime($consent->ts));
                } else {
                    $consent_text = $langYouHaveNotConsentedToPrivacyPolicy;
                    $consentButtons = implode('<br>', $consentButtons);
                    $consentDate = '';
                }
                $policyUrl = $urlAppend .'info/privacy_policy.php';
                $consent_text = preg_replace(['/{(.*)}/', '/%date/'], ["<a href='$policyUrl'>\$1</a>", $consentDate], $consent_text);
                $tool_content .= "
            <div id='privacyPolicySection' class='row'>
                <div class='col-xs-12'>
                    <div class='panel panel-default'>
                        <div class='panel-body'>
                            <div class='row'>
                                <div class='col-sm-8'>
                                    <div class='profile-content-panel-title'>
                                        $langPrivacyPolicy
                                    </div>
                                    <div class='profile-content-panel-text'>
                                        $consent_text
                                    </div>
                                </div>
                            <div class='col-sm-4'>
                                <form method='post' action='{$urlAppend}main/portfolio.php'>
                                    <input type='hidden' name='next' value='profile'>
                                    $consentButtons
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
        }

        $tool_content .= "<div class='row'>
            <div class='col-xs-12'>
                <div class='panel panel-default'>
                    <div class='panel-body'>
                        <div class='row'>
                            <div class='col-sm-8'>
                                <div class='profile-content-panel-title'>
                                    $langUnregUser
                                </div>
                                <div class='profile-content-panel-text'>
                                    $langExplain
                                </div>
                            </div>
                            <div class='col-sm-4'>
                                $action_bar_unreg
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>";
    }
}
draw($tool_content, 1, 'profile');

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
