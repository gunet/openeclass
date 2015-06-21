<?php

session_start();
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

/*
 * @file index.php
 *
 * @abstract This file serves as the home page of eclass when the user
 * is not logged in.
 *
 */

// Handle alias of .../courses/<CODE>/... to index.php for course homes
if (preg_match('|/courses/([a-zA-Z_-]+\d+)/[^/]*$|', $_SERVER['REQUEST_URI'], $matches)) {
	$dbname = $matches[1];
	if (!@chdir('courses/' . $dbname)) {
		header('HTTP/1.0 404 Not Found');
		echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL ',$_SERVER['REQUEST_URI'],' was not found on this server.</p>
</body></html>
';
		exit;
	}
	$_SESSION['dbname'] = $dbname;
	require_once '../../modules/course_home/course_home.php';
    exit;
}

define('HIDE_TOOL_TITLE', 1);
$guest_allowed = true;

require_once 'include/baseTheme.php';
require_once 'include/CAS/CAS.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/phpass/PasswordHash.php';

// unset system that records visitor only once by course for statistics
require_once 'include/action.php';
if (isset($dbname)) {
    $action = new action();
    $action->record('MODULE_ID_UNITS', 'exit');
}
unset($dbname);

// if we try to login... then authenticate user.
$warning = '';
if (isset($_SESSION['shib_uname'])) {
    // authenticate via shibboleth
    shib_cas_login('shibboleth');
} elseif (isset($_SESSION['cas_uname']) && !isset($_GET['logout'])) {
    // authenticate via cas
    shib_cas_login('cas');
} else {
    // normal authentication
    process_login();
}

if (isset($_SESSION['uid'])) {
    $uid = $_SESSION['uid'];
} else {
    $uid = 0;
}

if (isset($_GET['logout']) and $uid) {
    Database::get()->query("INSERT INTO loginout (loginout.id_user,
                loginout.ip, loginout.when, loginout.action)
                VALUES (?d, ?s, NOW(), 'LOGOUT')", $uid, $_SERVER['REMOTE_ADDR']);
    if (isset($_SESSION['cas_uname'])) { // if we are CAS user
        define('CAS', true);
    }
    foreach (array_keys($_SESSION) as $key) {
        unset($_SESSION[$key]);
    }
    session_destroy();
    $uid = 0;
    if (defined('CAS')) {
        $cas = get_auth_settings(7);
        if (isset($cas['cas_ssout']) and intval($cas['cas_ssout']) === 1) {
            phpCAS::client(SAML_VERSION_1_1, $cas['cas_host'], intval($cas['cas_port']), $cas['cas_context'], FALSE);
            phpCAS::logoutWithRedirectService($urlServer);
        }
    }
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
if (!$upgrade_begin and $uid and !isset($_GET['logout'])) {
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
    // if user is not guest redirect him to portfolio
    header("Location: {$urlServer}main/portfolio.php");
} else {
    // check authentication methods
    $authLink = array();
    if (!$upgrade_begin) {
        $extAuthMethods = array('cas', 'shibboleth');
        $loginFormEnabled = false;
        $q = Database::get()->queryArray("SELECT auth_name, auth_default, auth_title
                FROM auth WHERE auth_default <> 0
                ORDER BY auth_default DESC, auth_id");
        foreach ($q as $l) {
            $extAuth = in_array($l->auth_name, $extAuthMethods);
            if ($extAuth) {
                $authLink[] = array(
                    'showTitle' => true,
                    'class' => 'login-option login-option-sso',
                    'title' => empty($l->auth_title)? "$langLogInWith<br>{$l->auth_name}": q(getSerializedMessage($l->auth_title)),
                    'html' => "<a class='btn btn-default btn-login' href='{$urlServer}secure/" .
                              ($l->auth_name == 'cas'? 'cas.php': '') . "'>$langEnter</a><br>");
            } elseif (!$loginFormEnabled) {
                $loginFormEnabled = true;
                $authLink[] = array(
                    'showTitle' => false,
                    'class' => 'login-option',
                    'title' => empty($l->auth_title)? "$langLogInWith<br>Credentials": q(getSerializedMessage($l->auth_title)),
                    'html' => "<form action='$urlServer' method='post'>
                             <div class='form-group'>
                                <label for='uname' class='sr-only'>$langUsername</label>
                                <input type='text' id='uname' name='uname' placeholder='$langUsername'><span class='col-xs-2 col-sm-2 col-md-2 fa fa-user'></span>
                             </div>
                             <div class='form-group'>
                                <label for='pass' class='sr-only'>$langPass</label>
                                <input type='password' id='pass' name='pass' placeholder='$langPass'><span id='revealPass' class='fa fa-eye' style='margin-left: -20px; color: black;'></span>&nbsp;&nbsp;<span class='col-xs-2 col-sm-2 col-md-2 fa fa-lock'></span>
                             </div>
                             <button type='submit' name='submit' class='btn btn-login'>$langEnter</button>
                           </form>
                           <div class='text-right'>
                             <a href='modules/auth/lostpass.php'>$lang_forgot_pass</a>
                           </div>");
            }
        }

        $head_content .= "
      <script>
        $(function() {
            $('#revealPass').mousedown(function () {
                $('#pass').attr('type', 'text');
            }).mouseup(function () {
                $('#pass').attr('type', 'password');
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
        $tool_content .= "
                        <div class='col-xs-12 col-sm-6 col-md-5 col-lg-4 pull-right login-form'>
                          <div class='wrapper-login-option'>";
        
        $show_seperator = count($authLink) > 1;
        foreach ($authLink as $i => $l) {
            $tool_content .= "<div class='$l[class]'>
                                <h2>$langUserLogin</h2>
                                <div>" . ($l['showTitle']? "<span class='head-text' style='font-size:14px;'>$l[title]</span>": '') .
                                   $l['html'] . "
                                </div>";
            if ($show_seperator) {
                $tool_content .= "
                                <div class='login-settings row'>
                                  <div class='or-separator'><span>$langOr</span></div>
                                  <div class='alt_login text-center'>
                                    <span>";
                foreach ($authLink as $j => $otherAuth) {
                    if ($j != $i) {
                        $tool_content .= "<button type='button' data-target='$j' class='option-btn-login hide'>$otherAuth[title]</button>";
                    }
                }
                $tool_content .= "
                                    </span>
                                  </div>
                                </div>";
            }
            $tool_content .= "</div>";
        }

        $tool_content .= "
                          </div>
                        </div>";
    }
    $tool_content .= "
                    </div>
                </div>
            </div>
        </div>";

    $announceArr = Database::get()->queryArray("SELECT `id`, `date`, `title`, `body`, `order` FROM `admin_announcement`
                                                WHERE `visible` = 1
                                                        AND lang=?s
                                                        AND (`begin` <= NOW() or `begin` IS null)
                                                        AND (NOW() <= `end` or `end` IS null)
                                                ORDER BY `order` DESC", $language);

    $ann_content = '';
    if ($announceArr && sizeof($announceArr) > 0) {
        $ann_content .= "<ul class='front-announcements'>";
        $numOfAnnouncements = sizeof($announceArr);
        for ($i = 0; $i < $numOfAnnouncements; $i++) {
            $aid = $announceArr[$i]->id;
            $ann_content .= "
                    <li>
                    <div><a class='announcement-title' href='modules/announcements/main_ann.php?aid=$aid'>" . q($announceArr[$i]->title) . "</a></div>
                    <span class='announcement-date'>- " . claro_format_locale_date($dateFormatLong, strtotime($announceArr[$i]->date)) . " -</span>
            " . standard_text_escape(ellipsize_html("<div class='announcement-main'>".$announceArr[$i]->body."</div>", 500, "<div class='announcements-more'><a href='modules/announcements/main_ann.php?aid=$aid'>$langMore &hellip;</a></div>"))."</li>";
        }
    }

    $tool_content .= "<div class='row'>
        <div class='col-md-8'>";
        $tool_content .= "<div class='panel'>
            <div class='panel-body'>
                $langInfoAbout
            </div>
        </div>";

        // display admin announcements
        if(!empty($ann_content)) {
            $tool_content .= "<h3 class='content-title'>$langAnnouncements <a href='${urlServer}rss.php' style='padding-left:5px;'>
                    <i class='fa fa-rss-square'></i>
                    </a></h3>";
            $tool_content .= "<div class='panel'>
                            <div class='panel-body'>";
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
                <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id796936702' target=_blank><img src='$themeimg/appstore.png' class='img-responsive center-block' alt='Available on the App Store'></a>
                </div>
                <div class='col-xs-6'>
                <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank><img src='$themeimg/playstore.png' class='img-responsive center-block' alt='Available on the Play Store'></a>
                </div>
                </div></div>
            </div>";
    }

        $tool_content .= "</div>
        </div>";
    draw($tool_content, 0, null, $head_content);
}
