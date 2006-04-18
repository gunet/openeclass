<?

/*
 +----------------------------------------------------------------------+
 | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
 | Copyright (c) 2003 GUNet                                             |
 +----------------------------------------------------------------------+
 | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
 |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
 |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
 |                                                                      |
 | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
 |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
 |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
 +----------------------------------------------------------------------+
 | Standard header included by all e-class files                        |
 | Defines standard functions and validates variables                   |
 +----------------------------------------------------------------------+
*/


//------------------------------------
// include the following necessary files
// ---------------------------------

// header
include "header.php";
// footer
include "footer.php";
// function library
include "lib/main.lib.php";

if (!session_id()) { session_start(); }

@include "../../config/config.php";

// Set some defaults 
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

// Get configuration variables
if (!isset($webDir)) {
    @include('../config/config.php');
		if (!isset($webDir)) {
			die("Unable to open configuration file,
			please contact the system administrator");
		}
}


// Connect to database
$db = mysql_connect($mysqlServer, $mysqlUser, $mysqlPassword);
if (mysql_version()) mysql_query("SET NAMES greek");
mysql_select_db($mysqlMainDb, $db);

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
	exit("Session is lost. Please go back to <a href='../index.php' "."target='_top'>course homepage</a> 
and refresh");
}

if (isset($require_prof) and $require_prof) {
	if (!check_prof()) 
		exit("You are not allowed to proceed this action.
		Please go back to <a href='../index.php' "."target='_top'>course homepage</a> and refresh");
}




// If $require_current_course is true, initialise course settings
// Read properties of current course
if (isset($require_current_course) and $require_current_course) {
	// Work around bug in some versions of PHP - session registered
	// variables aren't immediately available in $HTTP_SESSION_VARS[]
	if (session_is_registered('dbname') and
	    !isset($_SESSION['dbname'])) {
				$HTTP_SESSION['dbname'] = $dbname;
	}
	if (!isset($_SESSION['dbname'])) {
		exit("Session is lost. Please go back to <a href='../index.php' ".
			"target='_top'>course homepage</a> and refresh");
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
		exit("This course doesn't exist");
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
			begin_page();
			echo $langNoAdminAccess;
			end_page();
			exit;
		} elseif ($visible == 1) {
			if ($statut == 0) {
				begin_page();
				echo $langLoginRequired;
				end_page();
				exit;
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
		($status[$currentCourse] == 1 or $status[$currentCourse] == 2)) {
		$is_adminOfCourse = TRUE;
	}
} else {
	unset($status);
}




?>
