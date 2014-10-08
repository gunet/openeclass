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

define('HIDE_TOOL_TITLE', 1);
$guest_allowed = true;

require_once 'include/baseTheme.php';
require_once 'include/CAS/CAS.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/phpass/PasswordHash.php';

// $homePage is used by baseTheme.php to parse correctly the breadcrumb
$homePage = true;

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
if ($uid AND !isset($_GET['logout'])) {
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
    // check for shibboleth authentication
    $shibboleth_link = "";
    $shibactive = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_name='shibboleth'");
    if ($shibactive) {
	    if ($shibactive->auth_default == 1) {
     	   $shibboleth_link = "<a href='{$urlSecure}secure/index.php'>$langShibboleth</a><br />";
	    }
	}

    // check for CAS authentication
    $cas_link = "";
    $casactive = Database::get()->querySingle("SELECT auth_default FROM auth WHERE auth_name='cas'");
    if ($casactive) {
    	if ($casactive->auth_default == 1) {
        	$cas_link = "<a href='{$urlServer}secure/cas.php'>$langViaCAS</a><br />";
    	}
    }
   
    if (!get_config('dont_display_login_form')) {
        $tool_content .= "


        <div class='row margin-top-fat'>
            <div class='col-md-12 remove-gutter'>
                <div class='jumbotron jumbotron-login'>
                    <div class='row'>
                        <div class='hidden-xs col-sm-7 col-md-7' style='position: static;'>
                            <img class='graphic' src='$themeimg/indexlogo.png'/>
                        </div>                        
                        <form class='login-form col-xs-12 col-sm-7 col-md-5 col-lg-4 pull-right' action='$urlSecure' method='post'>
                            <h2>$langUserLogin</h2>
                            <div class='form-group'>
                                <input autofocus type='text' name='uname' placeholder='$langUsername'><label class='col-xs-2 col-sm-2 col-md-2'><i class='fa fa-user'></i></label>
                            </div>
                            <div class='form-group'>
                                <input type='password' name='pass' placeholder='$langPass'><label class='col-xs-2 col-sm-2 col-md-2'><i class='fa fa-lock'></i></label>
                            </div>
                            <div class='login-settings row'>";
                                /*<div class='checkbox pull-left'>
                                  <label><input type='checkbox'><span>Θυμήσου με</span></label>
                                </div>";*/
        if (!empty($shibboleth_link) or !empty($cas_link)) {
            $tool_content .= "<div class='link-pull-right'>                
                    <label>$langAlternateLogin</label>
                    <label>$shibboleth_link</label>
                    <label>$cas_link</label>
                 </div";
        }
        $tool_content .= "<div class='link pull-left'>
                                  <label><a href='modules/auth/lostpass.php'>$lang_forgot_pass</a></label>
                                </div>                          
                            </div>
                            <button type='submit' name='submit' class='btn btn-login'>$langEnter</button>
                                <p>$warning</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>";
        $rss_link = "<link rel='alternate' type='application/rss+xml' title='RSS-Feed' href='" .
            $urlServer . "rss.php'>";

    $announceArr = Database::get()->queryArray("SELECT `id`, `date`, `title`, `body`, `order` FROM `admin_announcement`
                                                WHERE `visible` = 1
                                                        AND lang=?s
                                                        AND (`begin` <= NOW() or `begin` IS null)
                                                        AND (NOW() <= `end` or `end` IS null)
                                                ORDER BY `order` DESC", $language);
    $ann_content = '';
    if ($announceArr && sizeof($announceArr) > 0) {
        $ann_content .= "<h4>$langAnnouncements</h4> <a href='${urlServer}rss.php'>
                    <img src='$themeimg/feed.png' alt='RSS Feed' title='RSS Feed' />
                    </a>";
        $numOfAnnouncements = sizeof($announceArr);
        for ($i = 0; $i < $numOfAnnouncements; $i++) {
            $aid = $announceArr[$i]->id;
            $ann_content .= "<b><a href='modules/announcements/main_ann.php?aid=$aid'>" . q($announceArr[$i]->title) . "</a></b>
                    &nbsp;<span class='smaller'>(" . claro_format_locale_date($dateFormatLong, strtotime($announceArr[$i]->date)) . ")</span>
            " . standard_text_escape(ellipsize_html($announceArr[$i]->body, 500, "<strong>&nbsp;<a href='modules/announcements/main_ann.php?aid=$aid'>... <span class='smaller'>[$langMore]</span></a></strong>")) . "<br>";
        }        
    }

        $tool_content .= "

        <div class='row'>

            <div class='col-md-8'>
                <div class='panel padding'>
                    $langInfoAbout
                </div>
                <div class='panel padding'>
                    $ann_content
                </div>
            </div>
            
            <div class='col-md-4'>

            ";


        $online_users = getOnlineUsers();
        $tool_content .= "

                <div class='panel padding'>
                    <i class='fa fa-group space-after-icon'></i>$langOnlineUsers: $online_users
                </div>

                <div class='panel padding'>
                    <a href='http://opencourses.gr'>
                        <img src='$themeimg/open_courses_bnr.png'>
                    </a>
                </div>
                <div class='panel padding'>
                    <a href='http://www.openeclass.org/'>
                        <img src='$themeimg/open_eclass_bnr.png'>
                    </a>
                </div>


            </div>
        </div>";

    }


    draw($tool_content, 0, null, $rss_link);
}
