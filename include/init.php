<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
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
if (isset($path2add) && $path2add == 0){
	$relPathLib = "include/";
	$relPath = "";
} elseif (isset($path2add) && $path2add == 1) {
	$relPathLib = "";
	$relPath = "../../";
} elseif (isset($path2add) && $path2add == 2) {
	$relPathLib = "";
	$relPath = "../";
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

// Get configuration variables
if (!isset($webDir)) {
	//path for course_home
	@include($relPath . "config/config.php");
	if (!isset($webDir)) {
		include 'not_installed.php';
                die("Unable to find configuration file, please contact the system administrator");
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
if (mysql_version()) mysql_query("SET NAMES utf8");
mysql_select_db($mysqlMainDb, $db);

// Include messages
include("${webDir}modules/lang/$language/common.inc.php");
if (file_exists("${webDir}/config/$language.inc.php")) {
        // include extra messages if any to override common.inc.php
        define('LANG_OVERRIDE', "${webDir}/config/$language.inc.php");
	include LANG_OVERRIDE;
}
include("${webDir}modules/lang/$language/messages.inc.php");
if (defined('LANG_OVERRIDE')) {
        // re-include extra messages to override messages.inc.php
        include LANG_OVERRIDE;
}

// Make sure that the $uid variable isn't faked
if (isset($_SESSION['uid'])) {
	$uid = $_SESSION['uid'];
} else {
	$uid = 0;
}

if (isset($_SESSION["is_admin"]) and $_SESSION["is_admin"])
	$is_admin = TRUE;
else
	$is_admin = FALSE;

if (isset($require_login) and $require_login and !$uid) {
	$toolContent_ErrorExists = $langLoginRequired;
	$errorMessagePath = "../../";
}

if (isset($require_admin) && $require_admin) {
	if(!check_admin()) {
		$toolContent_ErrorExists = $langCheckAdmin;
		$errorMessagePath = "../../";
	}
}

if (!isset($guest_allowed) || $guest_allowed!= true){
	if (check_guest()){
		$toolContent_ErrorExists = $langCheckGuest;
		$errorMessagePath = "../../";
	}
}



// If $require_current_course is true, initialise course settings
// Read properties of current course
if (isset($require_current_course) and $require_current_course) {
	if (!isset($_SESSION['dbname'])) {
		$toolContent_ErrorExists = $langSessionIsLost;
		$errorMessagePath = "../../";
	} else {
		$currentCourse = $dbname = $_SESSION['dbname'];
		$result = db_query("SELECT cours_id, code, fake_code, intitule, faculte,
                                           titulaires, languageCourse,
                                           departmentUrlName, departmentUrl, visible
                                    FROM cours WHERE cours.code='$dbname'");

                if (!$result or mysql_num_rows($result) == 0) {
                        if (defined('old_dbname')) {
                                $_SESSION['dbname'] = old_dbname;
                        } else {
                                unset($_SESSION['dbname']);
                        }
                        header('Location: ' . $urlServer);
                        exit;
                }

		while ($theCourse = mysql_fetch_array($result)) {
                        $cours_id = $theCourse["cours_id"];
			$fake_code = $theCourse["fake_code"];
			$code_cours = $theCourse["code"];
			$intitule = $theCourse["intitule"];
			$fac = $theCourse["faculte"];
			$titulaires = $theCourse["titulaires"];
			$languageInterface = $theCourse["languageCourse"];
			$departmentUrl= $theCourse["departmentUrl"];
			$departmentUrlName= $theCourse["departmentUrlName"];
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
			$toolContent_ErrorExists = $langLessonDoesNotExist;
			$errorMessagePath = "../../";
		}

		$fac_lower = strtolower($fac);

		// Check for course visibility by current user
		$statut = 0;
		if (isset($uid)) {
                        // The admin can see all courses as adminOfCourse
                        if ($uid == 1) {
                                $statut = 1;
                        } else {
        			$res2 = db_query("SELECT statut FROM cours_user
                                                  WHERE user_id = $uid AND
                                                        cours_id = $cours_id");
        			if ($res2 and mysql_num_rows($res2) > 0) {
	        			list($statut) = mysql_fetch_row($res2);
		        	}
                        }
		}

		if ($visible != 2) {
			if (!$uid) {
				$toolContent_ErrorExists = $langNoAdminAccess;
				$errorMessagePath = "../../";
			} elseif ($statut == 0 and ($visible == 1 or $visible == 0)) {
				$toolContent_ErrorExists = $langLoginRequired;
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
			  $language = $languageInterface ;
			// Include messages
			include("$webDir/modules/lang/$language/common.inc.php");
			include("$webDir/modules/lang/$language/messages.inc.php");
			if (file_exists("${webDir}/config/$language.inc.php")) { // include extra messages if any
				include "${webDir}/config/$language.inc.php";
			}
		}
	}
}



// We try to meet here all condition we can give access to admin of a course
// When all script use $is_adminOfCourse, it's easier to implement
// multi-level admin access

// actually a prof has $status 1 or 2

// the system admin has uid=1
if ($uid == 1) {
	$is_adminOfCourse = TRUE;
        if (isset($currentCourse)) {
               $_SESSION['status'][$currentCourse] = 1;
        }
} else {
	$is_adminOfCourse = FALSE;
}
if (isset($_SESSION['status'])) {
	$status = $_SESSION['status'];
	if (isset($currentCourse) and
	(@$status[$currentCourse] == 1 or @$status[$currentCourse] == 2)) {
		$is_adminOfCourse = TRUE;
	}
} else {
	unset($status);
}


if (isset($require_prof) and $require_prof) {
	if (!check_prof()) {
		$toolContent_ErrorExists = $langCheckProf;
		$errorMessagePath = "../../";
	}
}

// Temporary student view
if (isset($_SESSION['saved_statut'])) {
        $statut = 5;
	$is_adminOfCourse = false;
        if (isset($currentCourse)) {
               $_SESSION['status'][$currentCourse] = 5;
        }
}


//Security check:: Users that do not have Professor access for a course must not
//be able to access inactive tools.
if(isset($currentCourse) && file_exists($module_ini_dir = getcwd() . "/module.ini.php") && !$is_adminOfCourse && @$ignore_module_ini != true) {
	include($module_ini_dir);

	if (!check_guest()) {
		if (isset($_SESSION['uid']) and $_SESSION['uid']) {
			$result = db_query("
                    select `id` from accueil
                    where visible=1
                    ORDER BY rubrique", $currentCourse);
		} else {
			$result = db_query("
                    select `id` from accueil
                    where visible=1 AND lien NOT LIKE '%/user.php'
                    ORDER BY rubrique", $currentCourse);
		}
	} else {
		$result = db_query("
			SELECT `id` FROM `accueil`
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
		$toolContent_ErrorExists = $langCheckPublicTools;
		$errorMessagePath = "../../";
	}
}
