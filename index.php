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

// check if we can connect to database. If not then eclass is most likely not installed
if (isset($mysqlServer) and isset($mysqlUser) and isset($mysqlPassword) && !Database::get()) {
    require_once 'include/not_installed.php';
}

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
    $rss_link = "<link rel='alternate' type='application/rss+xml' title='RSS-Feed' href='" .
            $urlServer . "rss.php'>";

    $tool_content .= "
        <div class='col-md-7'>
                $langInfoAbout
        </div>";

    $announceArr = Database::get()->queryArray("SELECT `id`, `date`, `title`, `body`, `order` FROM `admin_announcement`
            WHERE `visible` = 1
                    AND lang=?s
                    AND (`begin` <= NOW() or `begin` IS null)
                    AND (NOW() <= `end` or `end` IS null)
            ORDER BY `order` DESC", $language);

    if ($announceArr && sizeof($announceArr) > 0) {
        $tool_content .= "
                <br />
                <table width='100%' class='tbl_alt'>
                <tr>
                  <th colspan='2'>$langAnnouncements <a href='${urlServer}rss.php'>
                    <img src='$themeimg/feed.png' alt='RSS Feed' title='RSS Feed' />
                    </a>
                  </th>
                </tr>";

        $numOfAnnouncements = sizeof($announceArr);
        for ($i = 0; $i < $numOfAnnouncements; $i++) {
            $aid = $announceArr[$i]->id;
            $tool_content .= "
                        <tr>
                          <td width='1'><img style='border:0px;' src='$themeimg/arrow.png' alt='' /></td>
                          <td>
                        <b><a href='modules/announcements/main_ann.php?aid=$aid'>" . q($announceArr[$i]->title) . "</a></b>
                                &nbsp;<span class='smaller'>(" . claro_format_locale_date($dateFormatLong, strtotime($announceArr[$i]->date)) . ")</span>
                        " . standard_text_escape(ellipsize_html($announceArr[$i]->body, 500, "<strong>&nbsp;<a href='modules/announcements/main_ann.php?aid=$aid'>... <span class='smaller'>[$langMore]</span></a></strong>")) . "
                        </td>
                      </tr>";
        }
        $tool_content .= "</table>";
    }

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

    $tool_content .= "</div>

    ";
    if (!get_config('dont_display_login_form')) {
        $tool_content .= "


        <div class='row add-gutter margin-bottom-thin'>
            <div class='col-md-12'>
                <h1 class='login-heading'>Open eClass - Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης</h1>
            </div>
        </div>

        <div class='row'>
            <div class='col-md-12'>
                <div class='jumbotron jumbotron-login'>
                    <div class='row'>
                        <div class='hidden-xs col-sm-7 col-md-7'>
                            <img class='graphic' src='$themeimg/indexlogo.png'/>
                        </div>
                        <form class='login-form col-xs-12 col-sm-5 col-md-5 col-lg-4 pull-right'>
                            <h2>Σύνδεση Χρήστη</h2>
                            <div class='form-group'>
                                <input autofocus type='email' class='col-xs-10 col-sm-10 col-md-10' id='inputEmail' placeholder='Όνομα χρήστη''><label class='col-xs-2 col-sm-2 col-md-2' for='inputEmail'><i class='fa fa-user'></i></label>
                            </div>
                            <div class='form-group'>
                                <input type='password' class='col-xs-10 col-sm-10 col-md-10' id='inputPassword' placeholder='Κωδικός''><label class='col-xs-2 col-sm-2 col-md-2' for='inputPassword'><i class='fa fa-lock'></i></label>
                            </div>
                            <div class='login-settings row'>
                                <div class='checkbox pull-left'>
                                  <label><input type='checkbox'><span>Θυμήσου με</span></label>
                                </div>
                                <div class='link pull-right'>
                                  <label><a href='#''>Ξεχάσατε το συνθηματικό σας;</a></label>
                                </div>                          
                            </div>
                            <button type='submit' class='btn btn-login'>Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class='row'>
                <div class='col-md-7 deleteThisClass'>COL-MD-8</div>
                <div class='col-md-5 deleteThisClass'>COL-MD-4</div>
        </div>



        <div class='row'>
        <div class='panel col-md-6'>
                
                <form action='$urlSecure' method='post'>
                 <table width='100%' class='tbl'>
                 <tr>
                   <th class='LoginHead'><b>$langUserLogin </b></th>
                 </tr>
                 <tr>
                   <td class='LoginData'>
                   $langUsername <br />
                   <input class='Login' name='uname' size='17' /><br />
                   $langPass <br />
                   <input class='Login' name='pass' type = 'password' size = '17' autocomplete='off' /><br /><br />
                   <input class='Login' name='submit' type = 'submit' size = '17' value = '" . q($langEnter) . "' autocomplete='off' /><br />
                   $warning</td></tr>
                   <tr><td><p class='smaller'><a href='modules/auth/lostpass.php'>$lang_forgot_pass</a></p>
                   </td>
                 </tr>";
        if (!empty($shibboleth_link) or !empty($cas_link)) {
            $tool_content .= "<tr><th class='LoginHead'><b>$langAlternateLogin </b></th></tr>";
        }
        $tool_content .= "<tr><td class='LoginData'>
                   $shibboleth_link
                   $cas_link</td></tr>";
        $online_users = getOnlineUsers();
        if ($online_users > 0) {
            $tool_content .= "<th class='LoginHead'><br />$langOnlineUsers: $online_users</th>";
        }
        $tool_content .= "</table></form>
                
        </div>";
    }

    $tool_content .= "<div id='extra'>{%ECLASS_HOME_EXTRAS_RIGHT%}</div>";

    draw($tool_content, 0, null, $rss_link);
}
