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
if (preg_match('|/courses/([a-zA-Z_-]*\d+)/[^/]*$|', $_SERVER['REQUEST_URI'], $matches)) {
	$dbname = $matches[1];
	if (!@chdir('courses/' . $dbname)) {
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
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/sendMail.inc.php';

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
} elseif (isset($_GET['provider'])) {
    //hybridauth authentication (Facebook, Twitter, Google, Yahoo, Live, LinkedIn)
    hybridauth_login();
} else {
    // normal authentication
    process_login();
}
$data['warning'] = $warning;

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
    
    // include HubridAuth libraries
    require_once 'modules/auth/methods/hybridauth/config.php';
	require_once 'modules/auth/methods/hybridauth/Hybrid/Auth.php';
	$config = get_hybridauth_config();
    $hybridauth = new Hybrid_Auth( $config );
    $hybridauth->logoutAllProviders();
    
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
    $hybridLinkId = null;
    $hybridProviders = array();
    $authLinks = array();
    if (!$upgrade_begin) {
        $loginFormEnabled = false;
        $q = Database::get()->queryArray("SELECT auth_id, auth_name, auth_default, auth_title
                FROM auth WHERE auth_default <> 0
                ORDER BY auth_default DESC, auth_id");
        foreach ($q as $l) {
            if (in_array($l->auth_name, $extAuthMethods)) {
                $authLinks[] = array(
                    'showTitle' => true,
                    'class' => 'login-option login-option-sso',
                    'title' => empty($l->auth_title)? "$langLogInWith<br>{$l->auth_name}": q(getSerializedMessage($l->auth_title)),
                    'html' => "<a class='btn btn-default btn-login' href='" . $urlServer .
                              ($l->auth_name == 'cas'? 'modules/auth/cas.php': 'secure/') . "'>$langEnter</a><br>");
            } elseif (in_array($l->auth_name, $hybridAuthMethods)) { 
                $hybridProviders[] = $l->auth_name;
                if (is_null($hybridLinkId)) {
                    $authLinks[] = array(
                        'showTitle' => true,
                        'class' => 'login-option',
                        'title' => $langViaSocialNetwork);
                    $hybridLinkId = count($authLinks) - 1;
                }
            } elseif (!$loginFormEnabled) {
                $loginFormEnabled = true;
                $authLinks[] = array(
                    'showTitle' => false,
                    'class' => 'login-option',
                    'title' => empty($l->auth_title)? "$langLogInWith<br>Credentials": q(getSerializedMessage($l->auth_title)),
                    'html' => "<form action='$urlServer' method='post'>
                             <div class='form-group'>
                                <label for='uname' class='sr-only'>$langUsername</label>
                                <input type='text' id='uname' name='uname' placeholder='$langUsername' autofocus><span class='col-xs-2 col-sm-2 col-md-2 fa fa-user'></span>
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

        if (count($hybridProviders)) {
            $authLinks[$hybridLinkId]['html'] = '<div>';
            $beginHybridHTML = true;
            foreach ($hybridProviders as $provider) {
                if ($beginHybridHTML) {
                    $beginHybridHTML = false;
                } else {
                    $authLinks[$hybridLinkId]['html'] .= '<br>';
                }
                $authLinks[$hybridLinkId]['html'] .=
                    "<a href='{$urlServer}index.php?provider=$provider'><img src='$themeimg/$provider.png' alt='Sign-in with $provider' style='margin-right: 0.5em;'>" . ucfirst($provider) . "</a>";
            }
            $authLinks[$hybridLinkId]['html'] .= '</div>';
        }
        $data['authLinks'] = $authLinks;
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
    
    if (!($upgrade_begin or get_config('dont_display_login_form'))) {        
        if (count($authLinks) > 3) {
            // home page login form with more than 3 buttons not supported
            $data['authLinks'] = array($authLinks[0]);
        } 

    }

    $data['announcements'] = Database::get()->queryArray("SELECT `id`, `date`, `title`, `body`, `order` FROM `admin_announcement`
                                                WHERE `visible` = 1
                                                        AND lang=?s
                                                        AND (`begin` <= NOW() or `begin` IS null)
                                                        AND (NOW() <= `end` or `end` IS null)
                                                ORDER BY `order` DESC", $language);
    $data['dateFormatLong'] = $dateFormatLong;

    $home_main_area = new \Widgets\WidgetArea(HOME_PAGE_MAIN);
    $data['home_main_area_widgets'] = '';
    foreach ($home_main_area->getWidgets() as $key => $widget) {
        $data['home_main_area_widgets'] .= $widget->run($key);
    }   

    // display extras right
    $data['extra_right'] = $langExtrasRight ?:false;

    // display online users
    $data['online_users'] = getOnlineUsers();

    if (!isset($openCoursesExtraHTML)) {
        $openCoursesExtraHTML = '';
        setOpenCoursesExtraHTML();
        $data['openCoursesExtraHTML'] = $openCoursesExtraHTML;
    }
    
    $home_page_sidebar = new \Widgets\WidgetArea(HOME_PAGE_SIDEBAR);
    $data['home_page_sidebar_widgets'] = '';
    foreach ($home_page_sidebar->getWidgets() as $key => $widget) {
        $data['home_page_sidebar_widgets'] .= $widget->run($key);
    }  
    $data['menuTypeID'] = 0;
    
    view('home.index', $data);
}
