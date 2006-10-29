<?
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	Α full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/
/**
 * Init
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This file is included each and every time by baseTheme.php
 * It initialises variables, includes security checks and serves language switching
 *
 */

//Modify the relative path prefix according to the state of the system
//0: logged in/out screen
//1: user home
//2: used by abou, copyright, contact, manuals, upgrade
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

// header
include "header.php";
// footer
include "footer.php";

// function library
include $relPathLib . "lib/main.lib.php";

//if session isn't started, start it. Needed by the language switch
if (!session_id()) { session_start(); }

// Set some defaults
//NOTE (evelthon) these defaults should be deleted ...
if (!isset($colorLight)) {
	$colorLight	= "#F5F5F5";
}
if (!isset($colorMedium)) {
	$colorMedium = "#004571";
}
if (!isset($colorDark)) {
	$colorDark = "#000066";
}
if (!isset($bannerPath)) {
	$bannerPath = 'images/gunet/banner.jpg';
}

// Set user desired language (Author: Evelthon Prodromou)
if (isset($localize)) {

	switch ($localize) {

		case "en":

			$_SESSION['langswitch'] = "english";
			$_SESSION['langLinkText'] = 'Greek';
			$_SESSION['langLinkURL'] = "?localize=el";
			break;

		case "el":

			$_SESSION['langswitch'] = "greek";
			$_SESSION['langLinkText'] = 'Αγγλικά';
			$_SESSION['langLinkURL'] = "?localize=en";
			break;

		default:
			die("Invalid language parameter passed");
	}

}
// Get configuration variables
if (!isset($webDir)) {
	//path for logged out + logge in
	//    @include($pathOverride . "config/config.php");

	//path for course_home
	@include($relPath . "config/config.php");

	if (!isset($webDir)) {
		include 'not_installed.php';
	}
}

//load the correct language (Author: Evelthon Prodromou)
if (session_is_registered('langswitch')) {
	$language 		= $_SESSION['langswitch'];
	$langChangeLang = $_SESSION['langLinkText'];
	$switchLangURL 	= $_SESSION['langLinkURL'];
}

// Connect to database
$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
if (mysql_version()) mysql_query("SET NAMES greek");
mysql_select_db($mysqlMainDb, $db);

//if the user is logged in, get this preferred language set in his
//profile (Author: Evelthon Prodromou)
if(session_is_registered('uid') && !session_is_registered('langswitch')) {
	$sqlLang= "SELECT lang
                FROM user 
                WHERE user_id='".$_SESSION['uid']."'";
	$result=mysql_query($sqlLang);
	while ($myrow = mysql_fetch_array($result)) {
		if ($myrow[0]== "el") {
			$language = "greek";
			$_SESSION['langLinkText'] = "Αγγλικά";
			$_SESSION['langLinkURL'] = "?localize=en";
		} else {
			$language = "english";
			$_SESSION['langLinkText'] = "Greek";
			$_SESSION['langLinkURL'] = "?localize=el";
		}
	}
}

// Include messages
@ include("$webDir/modules/lang/english/trad4all.inc.php");
@ include("$webDir/modules/lang/$language/trad4all.inc.php");
if (isset($langFiles)) {
	if (is_array($langFiles)) {
		foreach ($langFiles as $f) {
			@ include("$webDir/modules/lang/english/$f.inc.php");
			@ include("$webDir/modules/lang/$language/$f.inc.php");
		}
	} else {
		@ include("$webDir/modules/lang/english/$langFiles.inc.php");
		@ include("$webDir/modules/lang/$language/$langFiles.inc.php");
	}
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
	$toolContent_ErrorExists = $langSessionIsLost;
	$errorMessagePath = "../../";
}

if (isset($require_prof) and $require_prof) {
	if (!check_prof()) {
		$toolContent_ErrorExists = $langCheckProf;
		$errorMessagePath = "../../";
	}
}




// If $require_current_course is true, initialise course settings
// Read properties of current course
if (isset($require_current_course) and $require_current_course) {
	// Work around bug in some versions of PHP - session registered
	// variables aren't immediately available in $HTTP_SESSION_VARS[]
	if (session_is_registered('dbname') && !isset($_SESSION['dbname'])) {
		$HTTP_SESSION['dbname'] = $dbname;
	}
	if (!isset($_SESSION['dbname'])) {

		$toolContent_ErrorExists = $langSessionIsLost;
		$errorMessagePath = "../../";
	} else {
		$dbname = $_SESSION['dbname'];
	}
	$currentCourse = $dbname;

	$result = db_query("
		SELECT code, fake_code, intitule, faculte, 
			titulaires, languageCourse, 
			departmentUrlName, departmentUrl, visible
		FROM cours
		WHERE cours.code='$currentCourse'");

	while ($theCourse = mysql_fetch_array($result)) {
		$fake_code 	= $theCourse["fake_code"];
		$code_cours = $theCourse["code"];
		$intitule 	= $theCourse["intitule"];
		$fac 		= $theCourse["faculte"];
		$titulaires	= $theCourse["titulaires"];
		$languageInterface = $theCourse["languageCourse"];
		$departmentUrl= $theCourse["departmentUrl"];
		$departmentUrlName= $theCourse["departmentUrlName"];
		$visible = $theCourse['visible'];
		// New variables
		$currentCourseCode				= $fake_code ;
		$currentCourseID				= $code_cours;
		$currentCourseName				= $intitule;
		$currentCourseDepartment		= $fac;
		$currentCourseTitular 			= $titulaires;
		$currentCourseLanguage			= $languageInterface;
		$currentCourseDepartmentUrl		= $departmentUrl;
		$currentCourseDepartmentUrlName	= $departmentUrlName;
	}

	if (!isset($code_cours) or empty($code_cours)) {
		$toolContent_ErrorExists = $langLessonDoesNotExist;
		$errorMessagePath = "../../";
	}

	$fac_lower = strtolower($fac);

	// Check for course visibility by current user
	$statut = 0;
	if (isset($uid)) {
		$res2 = mysql_query("
			SELECT statut FROM cours_user
			WHERE code_cours = '$dbname' AND user_id='$uid'");
		if ($row = mysql_fetch_row($res2)) {
			$statut = $row[0];
		}
	}

	if ($visible != 2) {
		if (!$uid) {

			$toolContent_ErrorExists = $langNoAdminAccess;
			$errorMessagePath = "../../";

		} elseif ($visible == 1) {
			if ($statut == 0) {

				$toolContent_ErrorExists = $langLoginRequired;
				$errorMessagePath = "../../";

			}
		}
	}

	# force a specific interface language
	if (!empty($language_override)) {
		$languageInterface = $language_override;
	}

	// If course language is different from global language,
	// include more messages
	if ($language != $languageInterface) {
		@ include("$webDir/modules/lang/$languageInterface/trad4all.inc.php");
		if (is_array($langFiles)) {
			foreach ($langFiles as $f) {
				@ include("$webDir/modules/lang/$languageInterface/$f.inc.php");
			}
		} else {
			@ include("$webDir/modules/lang/$languageInterface/$langFiles.inc.php");
		}
		$language = $languageInterface ;
	}
}



// We try to meet here all condition we can give access to admin of a course
// When all script use $is_adminOfCourse, it's easier to implement
// multi-level admin access

// actually a prof has $status 1 or 2

// the system admin has uid=1
if ($uid == 1) {
	$is_adminOfCourse = TRUE;
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

?>