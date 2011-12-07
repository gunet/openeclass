<?php
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
 * Init
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This file is included each and every time by baseTheme.php
 * It initialises variables, includes security checks and serves language switching
 *
 */

if(function_exists("date_default_timezone_set")) { // only valid if PHP > 5.1
	date_default_timezone_set("Europe/Athens");
}

//Modify the relative path prefix according to the state of the system
//0: logged in/out screen
//1: user home
//2: used by about, copyright, contact, manuals, upgrade
//else: everything else (modules)
//(Author: Evelthon Prodromou)
if (isset($path2add) && $path2add == 0) {
	$relPathLib = "include/";
	$relPath = "";
} elseif (isset($path2add) && $path2add == 1) {
	$relPathLib = "";
	$relPath = "../../";
} elseif (isset($path2add) && $path2add == 2) {
	$relPathLib = "";
	$relPath = "../";
} elseif (isset($path2add) && $path2add == 3) {
	$relPathLib = "";
	$relPath = "../../../";
} else {
	$relPathLib = "";
	$relPath = "../../";
}


//------------------------------------
// include the following necessary files
// ---------------------------------

// function library
include $relPathLib . "lib/main.lib.php";
//if session isn't started, start it. Needed by the language switch
if (!session_id()) { session_start(); }

header('Content-Type: text/html; charset=UTF-8');

// Set user desired language (Author: Evelthon Prodromou)
if (isset($_REQUEST['localize'])) {
	$_SESSION['langswitch'] = $language = langcode_to_name($_REQUEST['localize']);
}

$active_ui_languages = array('el', 'en', 'es');

// Get configuration variables
//path for course_home
unset($webDir);
@include($relPath . "config/config.php");
if (!isset($webDir)) {
	include 'not_installed.php';
	die("Unable to find configuration file, please contact the system administrator");
}

// HTML Purifier
require_once $relPathLib . 'htmlpurifier-4.3.0-standalone/HTMLPurifier.standalone.php';
require_once $relPathLib . 'HTMLPurifier_Filter_MyIframe.php';
$purifier = new HTMLPurifier();
$purifier->config->set('Cache.SerializerPath', $webDir . 'courses/temp');
$purifier->config->set('Attr.AllowedFrameTargets', array('_blank'));
$purifier->config->set('HTML.SafeObject', true);
$purifier->config->set('Output.FlashCompat', true);
$purifier->config->set('HTML.FlashAllowFullScreen', true);
$purifier->config->set('Filter.Custom', array( new HTMLPurifier_Filter_MyIframe() ));

// PHP Math Publisher
include $relPathLib . 'phpmathpublisher/mathpublisher.php';

define('PCLZIP_TEMPORARY_DIR', $webDir.'courses/temp/');

// Set active user interface languages
$native_language_names = array();
foreach ($active_ui_languages as $langcode) {
	if (isset($native_language_names_init[$langcode])) {
		$native_language_names[$langcode] = $native_language_names_init[$langcode];
	}
}

if (!isset($urlSecure)) {
	$urlSecure = $urlServer;
}

// load the correct language (Author: Evelthon Prodromou)
if (isset($_SESSION['langswitch'])) {
	$language = $_SESSION['langswitch'];
}

// Connect to database
@$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
if (!$db) {
	include 'not_installed.php';
}
if (mysql_version()) db_query('SET NAMES utf8');
mysql_select_db($mysqlMainDb, $db);

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

// Make sure personalized profile setting is set (true or false)
if (!isset($persoIsActive)) {
	$persoIsActive = false;
}

// Make sure that the $uid variable isn't faked
if (isset($_SESSION['uid'])) {
	$uid = $_SESSION['uid'];
} else {
	$uid = 0;
}

// check if we are admin or power user or manageuser_user
if (isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
	$is_admin = true;
        $is_power_user = true;
        $is_usermanage_user = true;
} elseif (isset($_SESSION['is_power_user']) and $_SESSION['is_power_user']) {
	$is_power_user = true;
        $is_usermanage_user = true;
	$is_admin = false;
} elseif (isset($_SESSION['is_usermanage_user']) and $_SESSION['is_usermanage_user']) {
        $is_usermanage_user = true;
        $is_power_user = false;        
	$is_admin = false;
} else {
	$is_admin = false;
	$is_power_user = false;              
        $is_usermanage_user = false;
}

if (!isset($_SESSION['theme'])) {
	$_SESSION['theme'] = get_config('theme');
	if (empty($_SESSION['theme'])) {
		$_SESSION['theme'] = 'classic';
	}
}
$theme = $_SESSION['theme'];
$themeimg = $urlAppend . '/template/' . $theme . '/img';
if (isset($require_login) and $require_login and !$uid) {
	// to langLoginRequired einai ligo akyro?
	$toolContent_ErrorExists = caution($langSessionIsLost);
	$errorMessagePath = "../../";
}

if (isset($require_admin) && $require_admin) {	
	if (!($is_admin)) {    
		$toolContent_ErrorExists = caution($langCheckAdmin);
		$errorMessagePath = "../../";
	}
}

if (isset($require_power_user) && $require_power_user) {        
	if (!($is_admin or $is_power_user)) {
		$toolContent_ErrorExists = caution($langCheckPowerUser);
		$errorMessagePath = "../../";
	} 
}

if (isset($require_usermanage_user) && $require_usermanage_user) {        
	if (!($is_admin or $is_power_user or $is_usermanage_user)) {
		$toolContent_ErrorExists = caution($langCheckUserManageUser);
		$errorMessagePath = "../../";
	} 
}

if (!isset($guest_allowed) || $guest_allowed != true) {
	if (check_guest()){
		$toolContent_ErrorExists = caution($langCheckGuest);
		$errorMessagePath = "../../";
	}
}

if (isset($_SESSION['mail_verification_required']) && !isset($mail_ver_excluded) ) {
	// don't redirect to mail verification on logout
	if (!isset($_GET['logout'])) {
		header("Location:" . $urlServer . "modules/auth/mail_verify_change.php");
	} 
}

// Restore saved old_dbname function
function restore_dbname_override($do_unset = false)
{
	if (defined('old_dbname')) {
		$_SESSION['dbname'] = old_dbname;
	} elseif ($do_unset) {
		unset($_SESSION['dbname']);
	}
}

// Temporary dbname override
if (isset($_GET['course'])) {
	if (isset($_SESSION['dbname'])) {
		define('old_dbname', $_SESSION['dbname']);
	}
	$_SESSION['dbname'] = $_GET['course'];
}
register_shutdown_function('restore_dbname_override');

// If $require_current_course is true, initialise course settings
// Read properties of current course
if (isset($require_current_course) and $require_current_course) {
	if (!isset($_SESSION['dbname'])) {
		$toolContent_ErrorExists = caution($langSessionIsLost);
		$errorMessagePath = "../../";
	} else {
		$currentCourse = $dbname = $_SESSION['dbname'];
		$result = db_query("SELECT cours_id, cours.code, 
                                        fake_code, intitule, faculte.name AS faculte,
                                        titulaires, languageCourse, departmentUrlName, departmentUrl, visible
                                        FROM cours, faculte
                                        WHERE cours.faculteid = faculte.id AND
                                        cours.code=" . autoquote($dbname));

		if (!$result or mysql_num_rows($result) == 0) {
			restore_dbname_override(true);
			header('Location: ' . $urlServer);
			exit;
		}

		while ($theCourse = mysql_fetch_array($result)) {
 			$cours_id = $theCourse['cours_id'];
			$fake_code = $theCourse['fake_code'];
			$code_cours = $theCourse['code'];
			$intitule = $theCourse['intitule'];
			$fac = $theCourse['faculte'];
			$titulaires = $theCourse['titulaires'];
			$languageInterface = $theCourse['languageCourse'];
			$departmentUrl= $theCourse['departmentUrl'];
			$departmentUrlName= $theCourse['departmentUrlName'];
			$visible = $theCourse['visible'];
			// New variables
			$currentCourseCode = $fake_code ;
			$currentCourseID = $code_cours;
			$currentCourseName = $intitule;
			$currentCourseDepartment = $fac;
			$currentCourseTitular = $titulaires;
			$currentCourseLanguage = $languageInterface;
			$currentCourseDepartmentUrl = $departmentUrl;
			$currentCourseDepartmentUrlName = $departmentUrlName;
		}

		if (!isset($code_cours) or empty($code_cours)) {
			$toolContent_ErrorExists = caution($langLessonDoesNotExist);
			$errorMessagePath = "../../";
		}

		$fac_lower = strtolower($fac);

		// Check for course visibility by current user
		$statut = 0;
		// The admin can see all courses as adminOfCourse
		if ($is_admin) {
			$statut = 1;
		} else {
			$res2 = db_query("SELECT statut FROM cours_user
                                                WHERE user_id = $uid AND
                                                cours_id = $cours_id");
			if ($res2 and mysql_num_rows($res2) > 0) {
				list($statut) = mysql_fetch_row($res2);
			}
		}
                
		if ($visible != COURSE_OPEN) {                                                                        
			if (!$uid) {
				$toolContent_ErrorExists = caution($langNoAdminAccess);
				$errorMessagePath = "../../";
			} elseif ($statut == 0 and ($visible == COURSE_REGISTRATION or $visible == COURSE_CLOSED)) {
				$toolContent_ErrorExists = caution($langLoginRequired);
				$errorMessagePath = "../../";
			} elseif ($statut == 5 and $visible == COURSE_INACTIVE) {                                
                                $toolContent_ErrorExists = caution($langCheckProf);
				$errorMessagePath = "../../";
                        }
		}
	}
	# force a specific interface language
	if (!empty($currentCourseLanguage)) {
		$languageInterface = $currentCourseLanguage;
		// If course language is different from global language,
		// include more messages
		if ($language != $languageInterface) {
			$language = $languageInterface;
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
	}
}

// actually a prof has $status 1 
// the system admin has rights to all courses
if ($is_admin) {
	$is_course_admin = TRUE;
	if (isset($currentCourse)) {
		$_SESSION['status'][$currentCourse] = 1;
	}
} else {
	$is_course_admin = FALSE;
}

$is_editor = FALSE;
if (isset($_SESSION['status'])) {
	$status = $_SESSION['status'];
	if (isset($currentCourse)) {
		if (check_editor()) { // chech if user is editor of course
			$is_editor = TRUE;
		}
		if (@$status[$currentCourse] == 1) { // check if user is admin of course
			$is_course_admin = TRUE;
			$is_editor = TRUE;
		}            	
	}
} else {
	unset($status);
}

if (isset($require_course_admin) and $require_course_admin) {
	if (!$is_course_admin) {
		$toolContent_ErrorExists = caution($langCheckCourseAdmin);
		$errorMessagePath = "../../";
	}
}

if (isset($require_editor) and $require_editor) {
	if (!$is_editor) {
		$toolContent_ErrorExists = caution($langCheckProf);
		$errorMessagePath = "../../";
	}
}
    
// Temporary student view
if (isset($_SESSION['saved_statut'])) {
	$statut = 5;
	$is_course_admin = false;
	$is_editor = false;
	if (isset($currentCourse)) {
		$_SESSION['status'][$currentCourse] = 5;
	}
}

//Security check:: Users that do not have Professor access for a course must not
//be able to access inactive tools.
if (isset($currentCourse) && file_exists($module_ini_dir = getcwd() . "/module.ini.php") 
        && !$is_editor && @$ignore_module_ini != true) {
	include($module_ini_dir);

	if (!check_guest()) {
		if (isset($_SESSION['uid']) and $_SESSION['uid']) {
			$result = db_query("SELECT `id` FROM accueil
                                        WHERE visible=1
                                        ORDER BY rubrique", $currentCourse);
		} else {
			$result = db_query("SELECT `id` FROM accueil
                                        WHERE visible=1 AND lien NOT LIKE '%/user.php'
                                        ORDER BY rubrique", $currentCourse);
		}
	} else {
		$result = db_query("SELECT `id` FROM `accueil`
			WHERE `visible` = 1
			AND (
			`id` = 1 or
			`id` = 2 or
			`id` = 3 or
			`id` = 4 or
			`id` = 7 or
			`id` = 10 or
			`id` = 20)
			ORDER BY rubrique
			", $currentCourse);
	}

	$publicModules = array();
	while ($moduleIDs = mysql_fetch_array($result)) {
		array_push($publicModules, (int)$moduleIDs["id"]);
	}

	if (!in_array($module_id, $publicModules, true)) {
		$toolContent_ErrorExists = caution($langCheckPublicTools);
		$errorMessagePath = "../../";
	}
}

set_glossary_cache();

$tool_content = $head_content = '';

function caution($s)
{
	return '<p class="alert1"' . $s . '</p>';
}
