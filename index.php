<?php
session_start();
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
 * Index
 *
 * @version $Id$
 *
 * @abstract This file serves as the home page of eclass when the user
 * is not logged in.
 *
 */

/***************************************************************
*               HOME PAGE OF ECLASS		               *
****************************************************************
*/
define('INDEX_START', 1);
define('HIDE_TOOL_TITLE', 1);
$guest_allowed = true;
$path2add = 0;
include "include/baseTheme.php";
include "include/CAS/CAS.php";
include "modules/auth/auth.inc.php";
require_once 'modules/video/video_functions.php';
load_modal_box();
//$homePage is used by baseTheme.php to parse correctly the breadcrumb
$homePage = true;
$tool_content = "";
// first check
// check if we can connect to database. If not then eclass is most likely not installed
if (isset($mysqlServer) and isset($mysqlUser) and isset($mysqlPassword)) {
	$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
	if (mysql_version()) db_query("SET NAMES utf8");
}
if (!$db) {
	include "include/not_installed.php";
}

// unset system that records visitor only once by course for statistics
include('include/action.php');
if (isset($dbname)) {        
        $action = new action();
        $action->record('MODULE_ID_UNITS', 'exit');
}
unset($dbname);

// second check
// can we select a database? if not then there is some sort of a problem
if (isset($mysqlMainDb)) $selectResult = mysql_select_db($mysqlMainDb,$db);
if (!isset($selectResult)) {
	include "include/not_installed.php";
}

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
        db_query("INSERT INTO loginout (loginout.id_user,
                loginout.ip, loginout.when, loginout.action)
                VALUES ($uid, '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGOUT')");
	if (isset($_SESSION['cas_uname'])) { // if we are CAS user
		define('CAS', true);
	}
	foreach(array_keys($_SESSION) as $key) {
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
        include("${webDir}modules/lang/$language/common.inc.php");
        $extra_messages = "${webDir}/config/$language.inc.php";
        if (file_exists($extra_messages)) {
                include $extra_messages;
        } else {
                $extra_messages = false;
        }
        include("${webDir}modules/lang/$language/messages.inc.php");
        if ($extra_messages) {
                include $extra_messages;
        }

}

//----------------------------------------------------------------
// if login succesful display courses lists
// --------------------------------------------------------------
if ($uid AND !isset($_GET['logout'])) {
        if (check_guest()) {
                // if the user is a guest send him straight to the corresponding lesson
                $guestSQL = db_query("SELECT code FROM cours_user, cours
                                      WHERE cours.cours_id = cours_user.cours_id AND
                                            user_id = $uid", $mysqlMainDb);
                if (mysql_num_rows($guestSQL) > 0) {
                        $sql_row = mysql_fetch_row($guestSQL);
                        $dbname = $sql_row[0];
                        $_SESSION['dbname'] = $dbname;
                        header("Location: {$urlServer}courses/$dbname/index.php");
                        exit;
                } else { // if course was deleted stop guest account
                        $warning = "<br><font color='red'>$langInvalidGuestAccount</font><br>";
                        include "include/logged_out_content.php";
                        draw($tool_content, 0);
                }
        }
	$nameTools = $langWelcomeToPortfolio;
	$require_help = true;
	$helpTopic = 'Portfolio';

        if ($_SESSION['user_perso_active']) {
                // if the user is not a guest, load personalized view
                include "include/logged_in_content.php";
                draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
	} else {
		// load classic view
		include "include/classic.php";
		draw($tool_content, 1, null, $head_content);
	}
} else {
	$require_help = true;
        $helpTopic = "Init";
        $rss_link = "<link rel='alternate' type='application/rss+xml' title='RSS-Feed' href='" .
                    $urlServer . "rss.php'>";
	include "include/logged_out_content.php";
	draw($tool_content, 0, null, $rss_link);
}
