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
	importFromDocument.php
	@last update: 25-03-2010 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================
    @Description: This script handles importing of SCORM packages
                  from Open eClass document files.
==============================================================================
*/


$require_current_course = TRUE;
$require_prof = TRUE;

require_once("../../include/baseTheme.php");
$tool_content = "";

$navigation[]= array ("url"=>"learningPathList.php", "name"=> $langLearningPaths);
$nameTools = $langimportLearningPath;

mysql_select_db($currentCourseID);



if (isset($_POST) && isset($_POST['selectedDocument'])) {
	require_once("./importLearningPathLib.php");
	
	$filename = basename($_POST['selectedDocument']);
	$srcFile = "../../courses/".$currentCourseID."/document/".$_POST['selectedDocument'];
	$destFile = "../../courses/".$currentCourseID."/temp/".$filename;
	
	copy($srcFile, $destFile);
	
	list($messages, $lpid) = doImport($currentCourseID, $mysqlMainDb, $webDir, filesize($destFile), $filename, true);
	$tool_content .= $messages;
	$tool_content .= "\n<br /><a href=\"importLearningPath.php\">$langBack</a></p>";
	
	unlink($destFile);
}
else {
	$tool_content .= "Error, please try again!";
}


draw($tool_content, 2, "learnPath");
?>