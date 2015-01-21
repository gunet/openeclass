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
       
        $head_content .= "
            <script>
            $(function() {
                $('#revealPass')
                    .mousedown(function() {
                        $('#pass').attr('type', 'text');
                    })
                    .mouseup(function() {
                        $('#pass').attr('type', 'password');
                    })
            });            
            </script>
        ";
        $tool_content .= "
        <div class='row margin-top-fat'>
            <div class='col-md-12 remove-gutter'>
                <div class='jumbotron jumbotron-login'>
                    <div class='row'>
                        ";
        if (!get_config('dont_display_login_form')) {
                        $tool_content .= "<div class='login-form col-xs-12 col-sm-6 col-md-5 col-lg-4 pull-right'>
                            <h2>$langUserLogin</h2>
                                <form  action='$urlSecure' method='post'>
                                    <div class='form-group'>
                                        <input autofocus type='text' name='uname' placeholder='$langUsername'><label class='col-xs-2 col-sm-2 col-md-2'><i class='fa fa-user'></i></label>
                                    </div>
                                    <div class='form-group'>
                                        <input type='password' id='pass' name='pass' placeholder='$langPass'><i id='revealPass' class='fa fa-eye' style='margin-left:-20px;color:black;'></i>&nbsp&nbsp<label class='col-xs-2 col-sm-2 col-md-2'><i class='fa fa-lock'></i></label>
                                    </div>
                                    <button type='submit' name='submit' class='btn btn-login'>$langEnter</button>
                                </form>
                            <div class='login-settings row'>
                                <div class='text-center'>
                                      <a href='modules/auth/lostpass.php'>$lang_forgot_pass</a>
                                    </div>
                                <hr>";
                                if (!empty($shibboleth_link) or !empty($cas_link)) {
                                    $tool_content .= "<div class='alt_login text-center'>
                                        <span>$langAlternateLogin:</span> ";
                                            if (!empty($cas_link)) { $tool_content.= "<span>$cas_link</span>"; }
                                            if (!empty($shibboleth_link)) { $tool_content.= "<span>$shibboleth_link</span>"; }
                                         $tool_content .= "</div>";
                                }
                    $tool_content .= "</div>";
                    if (!empty($warning)) { $tool_content.= "<br><span>$warning</span>"; }
                    $tool_content .= "</div>";
        }
        $tool_content .= "</div>
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
        $ann_content .= "<h4>$langAnnouncements <a href='${urlServer}rss.php' style='padding-left:5px;'>
                    <i class='fa fa-rss-square'></i>
                    </a></h4><ul class='front-announcements'>";
        $numOfAnnouncements = sizeof($announceArr);
        for ($i = 0; $i < $numOfAnnouncements; $i++) {
            $aid = $announceArr[$i]->id;
            $ann_content .= "
                    <li>
                    <div><a class='announcement-title' href='modules/announcements/main_ann.php?aid=$aid'>" . q($announceArr[$i]->title) . "</a></div>
                    <span class='announcement-date'>- " . claro_format_locale_date($dateFormatLong, strtotime($announceArr[$i]->date)) . " -</span>
            " . standard_text_escape(ellipsize_html($announceArr[$i]->body, 500, "<div class='announcements-more'><a href='modules/announcements/main_ann.php?aid=$aid'>$langMore &hellip;</a></div>"))."</li>";
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
    $tool_content .= "<div class='panel'>
               <div class='panel-body'>
                   <i class='fa fa-group space-after-icon'></i> &nbsp;$langOnlineUsers: $online_users
               </div>
           </div>";
    if (!isset($openCoursesExtraHTML)) {
        $openCoursesExtraHTML = '';
        setOpenCoursesExtraHTML();
    }
    if (get_config('opencourses_enable')) {
            $tool_content .= "<div class='panel opencourses'>
                    <div class='panel-body'>
                        $openCoursesExtraHTML
                    </div>
                </div>
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
                        <img class='img-responsive center-block' src='$themeimg/open_eclass_banner.png'>
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
    draw($tool_content, 0, null, $rss_link.$head_content);
}
