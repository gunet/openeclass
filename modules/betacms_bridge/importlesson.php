<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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
/*===========================================================================
	import.php
	@last update: 09-01-2010 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: 

    @Comments:
==============================================================================
*/
$require_admin = TRUE;
require_once("../../include/baseTheme.php");
require_once("../admin/admin.inc.php");
$nameTools = $langBrowseBCMSRepo;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$tool_content = "";

require_once("include/bcms.inc.php");
session_start();

if (isset($_GET['id']) && isset($_SESSION[BETACMSREPO])) {
	$repo = $_SESSION[BETACMSREPO];
	$coId = $_GET['id'];
	
	$co = getLesson($repo, $coId);
	
	destroyContentObjectInSession();
	putContentObjectInSession($co);
	
	// redirect to create course
	$tool_content .= $GLOBALS['langBetaCMSRedirectAfterImport'].
		" <a href='../create_course/create_course.php'>".
		$GLOBALS['langBetaCMSRedirectHere']."...</a>";
	
	$redirect = $urlServer;
	if ( !substr( $urlServer, strlen( $urlServer ) - strlen( "/" ) ) === "/" ) {
		$redirect .= "/";
	}
	
	$head_content = '
		<script type="text/javascript">
			<!--//
			parent.window.location.href="'.$urlServer.'modules/create_course/create_course.php";
			//-->
		</script>';
}
else {
	$tool_content .= "<p class=\"caution_small\">$langEmptyFields</p>
			<br/><br/><p align=\"right\"><a href='browserepo.php'>$langAgain</a></p>";
}

draw($tool_content, 3, null, $head_content);


// HELPER FUNCTIONS

function destroyContentObjectInSession() {
	// an yparxei hdh apo prin, sbhsto
	unset($_SESSION[IMPORT_FLAG]);
	unset($_SESSION[IMPORT_FLAG_INITIATED]);
	unset($_SESSION[IMPORT_ID]);
	unset($_SESSION[IMPORT_INTITULE]);
	unset($_SESSION[IMPORT_DESCRIPTION]);
	unset($_SESSION[IMPORT_COURSE_KEYWORDS]);
	unset($_SESSION[IMPORT_COURSE_ADDON]);
	unset($_SESSION[IMPORT_UNITS]);
	unset($_SESSION[IMPORT_UNITS_SIZE]);
	unset($_SESSION[IMPORT_SCORMFILES]);
	unset($_SESSION[IMPORT_SCORMFILES_SIZE]);
	unset($_SESSION[IMPORT_DOCUMENTFILES]);
	unset($_SESSION[IMPORT_DOCUMENTFILES_SIZE]);
	
	return;
}

function putContentObjectInSession($obj) {
	$_SESSION[IMPORT_FLAG] = true;
	$_SESSION[IMPORT_FLAG_INITIATED] = false;
	$_SESSION[IMPORT_ID] = $obj[KEY_ID];
	$_SESSION[IMPORT_INTITULE] = $obj[KEY_TITLE];
	$_SESSION[IMPORT_DESCRIPTION] = $obj[KEY_DESCRIPTION];
	$_SESSION[IMPORT_COURSE_KEYWORDS] = $obj[KEY_KEYWORDS];
	$_SESSION[IMPORT_COURSE_ADDON] = $GLOBALS['langBetaCMSCopyright'].": " .$obj[KEY_COPYRIGHT] ." "
		.$GLOBALS['langBetaCMSAuthors'].": " .$obj[KEY_AUTHORS] ." "
		.$GLOBALS['langBetaCMSProject'].": " .$obj[KEY_PROJECT] ." "
		.$GLOBALS['langBetaCMSComments'].": " .$obj[KEY_COMMENTS];
	$_SESSION[IMPORT_UNITS] = $obj[KEY_UNITS];
	$_SESSION[IMPORT_UNITS_SIZE] = $obj[KEY_UNITS_SIZE];
	$_SESSION[IMPORT_SCORMFILES] = $obj[KEY_SCORMFILES];
	$_SESSION[IMPORT_SCORMFILES_SIZE] = $obj[KEY_SCORMFILES_SIZE];
	$_SESSION[IMPORT_DOCUMENTFILES] = $obj[KEY_DOCUMENTFILES];
	$_SESSION[IMPORT_DOCUMENTFILES_SIZE] = $obj[KEY_DOCUMENTFILES_SIZE];
	
	return;
}
?>