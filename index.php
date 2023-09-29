<?php

session_start();
/* ========================================================================
 * Open eClass 3.11
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
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

/*
 * @file index.php
 *
 * @abstract This file serves as the home page of eclass when the user
 * is not logged in.
 *
 */

if (isset($_POST['admin_login'])) {
    define('MAINTENANCE_PAGE', true);
}

// Handle alias of .../courses/<CODE>/... to index.php for course homes
if (preg_match('|/courses/([a-zA-Z0-9_-]+)/[^/]*$|', $_SERVER['REQUEST_URI'], $matches)) {
    $dbname = $matches[1];
    if (!is_dir('courses/' . $dbname)) {
        header('HTTP/1.0 404 Not Found');
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
                <html><head><title>404 Not Found</title></head><body>
                <h1>Not Found</h1>
                <p>The requested URL ',htmlspecialchars($_SERVER['REQUEST_URI']),' was not found on this server.</p>
                </body></html>';
        exit;
    }
    $_SESSION['dbname'] = $dbname;
    chdir('modules/course_home');
    require_once '../../modules/course_home/course_home.php';
    exit;
}

define('HIDE_TOOL_TITLE', 1);
$guest_allowed = true;

require_once 'include/baseTheme.php';
require_once 'modules/auth/login_form.inc.php';
require_once 'include/sendMail.inc.php';

// unset system that records visitor only once by course for statistics
require_once 'include/action.php';

load_js('trunk8');
if (isset($dbname)) {
    $action = new action();
    $action->record(MODULE_ID_UNITS, 'exit');
}
unset($dbname);

if (isset($_SESSION['uid'])) {
    $uid = $_SESSION['uid'];
} else {
    $uid = 0;
}

// if we try to login... then authenticate user.
$warning = '';

if(isset($_SESSION['hybridauth_callback'])) {
    switch($_SESSION['hybridauth_callback']) {
        case 'login':
            $_GET['provider'] = $_SESSION['hybridauth_provider'] ?? '';
            break;
        case 'profile':
            $provider = $_SESSION['hybridauth_provider'] ?? '';
            header('Location: /main/profile/profile.php?action=connect&provider='.$provider.'&'.$_SERVER['QUERY_STRING']);
            exit;
        case 'auth_test':
            $provider = $_SESSION['hybridauth_provider'] ?? '';
            header('Location: /modules/admin/auth_test.php?auth='.$provider.'&'.$_SERVER['QUERY_STRING']);
            exit;
    }
}

if (isset($_SESSION['shib_uname'])) {
    // authenticate via shibboleth
    shib_cas_login('shibboleth');
} elseif (isset($_SESSION['cas_uname'])) {
    // authenticate via cas
    shib_cas_login('cas');
} elseif (isset($_GET['provider'])) {
    //hybridauth authentication (Facebook, Twitter, Google, Yahoo, Live, LinkedIn)
    hybridauth_login();
} else {
    // normal authentication
    process_login();
}

// if the user logged in include the correct language files
// in case he has a different language set in his/her profile
if (isset($language)) {
    // include_messages
    include "lang/$language/common.inc.php";
    $extra_messages = "config/{$language_codes[$language]}.inc.php";
    if (file_exists($extra_messages)) {
        include $extra_messages;
    } else {
        $extra_messages = false;
    }
    include "lang/$language/messages.inc.php";
    if ($extra_messages) {
        include $extra_messages;
    }
}

// check if we are guest user
if (!$upgrade_begin and $uid) {
    if (check_guest()) {
        // if the user is a guest send him straight to the corresponding lesson
        $guest = Database::get()->querySingle("SELECT code FROM course_user, course
                                      WHERE course.id = course_user.course_id AND
                                            user_id = ?d", $uid);
        if ($guest) {
            $dbname = $guest->code;
            $_SESSION['dbname'] = $dbname;
            header("Location: {$urlServer}courses/$dbname/index.php");
            exit;
        }
    }

    // ----------------- sso transition ------------------
    if (get_config('sso_transition')) {
        require_once 'modules/auth/transition/Transition.class.php';
        $auth_transition = new Transition($uid);
        if ($uid == 1) { // exception for admin users
            header("Location: {$urlServer}main/portfolio.php");
        } else {
            if ($auth_transition->get_sso_exception_status() == SSO_TRANSITION_EXCEPTION_APPROVED
                or !$auth_transition->user_needs_transition()) {
                    header("Location: {$urlServer}main/portfolio.php");
            } else {
                header("Location: {$urlServer}modules/auth/transition/auth_transition.php");
            }
        }
    } else { // end of sso transition
        // normal login
        header("Location: {$urlServer}main/portfolio.php");
    }
} else {
    // redirect to landing page if defined
    $homepageSet = get_config('homepage');
    if ($homepageSet == 'external' and ($landingUrl = get_config('landing_url'))) {
        header('Location: ' . $landingUrl);
        exit;
    } elseif ($homepageSet == 'toolbox') {
        redirect_to_home_page('main/toolbox.php');
    }

    if (!$upgrade_begin) {
        $head_content .= "
      <script>
        $(document).ready(function(){

            $('.announcement-main').each(function() {
                $(this).trunk8({
                    lines: '2',
                    fill: '&hellip;<div class=\"announcements-more\"><a href=\"main/system_announcements.php?an_id=' +
                        $(this).data('id') + '\">$langMore</a></div>'
                });
            })
        });
      </script>
      <link rel='alternate' type='application/rss+xml' title='RSS-Feed' href='{$urlServer}rss.php'>";
    }

    $tool_content .= "$warning
        <div class='row margin-top-fat'>
            <div class='col-md-12 remove-gutter'>
                <div class='jumbotron jumbotron-login'>
                    <div class='row'>";

    if (!($upgrade_begin or get_config('dont_display_login_form'))) {
        $tool_content .= login_form();
    }
    $tool_content .= "
                    </div>
                </div>
            </div>
        </div>";

    $announceArr = Database::get()->queryArray("SELECT `id`, `date`, `begin`, `title`, `body`, `order` FROM `admin_announcement`
                                                WHERE `visible` = 1
                                                        AND lang=?s
                                                        AND (`begin` <= NOW() or `begin` IS null)
                                                        AND (NOW() <= `end` or `end` IS null)
                                                ORDER BY `date` DESC LIMIT 3", $language);

    $ann_content = '';
    if ($announceArr && sizeof($announceArr) > 0) {
        $numOfAnnouncements = sizeof($announceArr);
        for ($i = 0; $i < $numOfAnnouncements; $i++) {
            $aid = $announceArr[$i]->id;
            if (!is_null($announceArr[$i]->begin) && ($announceArr[$i]->date <= $announceArr[$i]->begin)) {
                $ann_date = $announceArr[$i]->begin;
            } else {
                $ann_date = $announceArr[$i]->date;
            }
            $ann_content .= "
                    <li>
                    <div><a class='announcement-title' href='main/system_announcements.php?an_id=$aid'>" . q($announceArr[$i]->title) . "</a></div>
                    <span class='announcement-date'>- " . format_locale_date(strtotime($ann_date)) . " -</span>
                    <div class='announcement-main' data-id='$aid'>" . $announceArr[$i]->body . "</div></li>";
        }
    }

    $tool_content .= "<div class='row'>
                            <div class='col-md-8'>";

    if (get_config('homepage_intro')) {
        $info_message = get_config('homepage_intro');
    } elseif (get_config('defaultHomepageIntro')) {
        $info_message = get_config('defaultHomepageIntro');
    } else {
        $info_message = $langInfoAbout;
    }

    $tool_content .= "<div class='panel'>
        <div class='panel-body'>" . $info_message . "</div>
    </div>";


        // display admin announcements
        if(!empty($ann_content)) {
            $tool_content .= "<h3 class='content-title'><a href='{$urlServer}main/system_announcements.php'>$langAnnouncements</a> <a href='{$urlServer}rss.php' style='padding-left:5px;'>
                    <span class='fa fa-rss-square'></span>
                    </a></h3>";
            $tool_content .= "<div class='panel'>
                            <div class='panel-body'><ul class='front-announcements'>";
            $tool_content .= $ann_content;
            $tool_content .= "</ul></div></div>";
        }
        $tool_content .= "</div>";
        $tool_content .= "<div class='col-md-4'>";

    // display extras right
    if (isset($langExtrasRight) and !empty($langExtrasRight)) {
        $tool_content .= "<div class='panel'>
            <div class='panel-body'>$langExtrasRight
                </a>
            </div>
        </div>";
    }
    // display online users
    $online_users = getOnlineUsers();
    if ($online_users > 0) {
        $tool_content .= "<div class='panel'>
               <div class='panel-body'>
                   <span class='fa fa-group space-after-icon'></span> &nbsp;$langOnlineUsers: $online_users
               </div>
           </div>";
    }
    if (!isset($openCoursesExtraHTML)) {
        $openCoursesExtraHTML = '';
        setOpenCoursesExtraHTML();
    }
    if (get_config('opencourses_enable')) {
        if ($openCoursesExtraHTML) {
            $tool_content .= "<div class='panel opencourses'>
                    <div class='panel-body'>
                        $openCoursesExtraHTML
                    </div>
                </div>";
        }
        $tool_content .= "
                <div class='panel opencourses-national'>
                <a href='http://opencourses.gr' target='_blank'>
                    $langNationalOpenCourses
                 </a>
                </div>";
    }

        $tool_content .= "
            <div class='panel' id='openeclass-banner'>
                <div class='panel-body'>
                    <a href='http://www.openeclass.org/' target='_blank'>
                        <img class='img-responsive center-block' src='$themeimg/open_eclass_banner.png' alt='Open eClass Banner'>
                    </a>
                </div>
            </div>";

        if (get_config('enable_mobileapi')) {
            $tool_content .= "<div class='panel mobile-apps'>
                <div class='panel-body'>
                <div class='row'>
                <div class='col-xs-6'>
                    <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank><img src='$themeimg/appstore.png' class='img-responsive center-block' alt='Available on the App Store'></a>
                </div>
                <div class='col-xs-6'>
                    <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass3' target=_blank><img src='$themeimg/playstore.png' class='img-responsive center-block' alt='Available on the Play Store'></a>
                </div>
                </div></div>
            </div>";
    }

        $tool_content .= "</div>
        </div>";

    draw($tool_content, 0, null, $head_content);
}
