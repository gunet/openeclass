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
	startModule.php
	@last update: 29-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: startModule.php Revision: 1.21.2.1

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This script is the main page loaded when user start viewing
                  a module in the browser. We define here the frameset
                  containing the launcher module (SCO if it is a SCORM
                  conformant one) and a frame to update the user's progress.


    @Comments:

    @todo:
==============================================================================
*/


$require_current_course = TRUE;
require_once("../../../config/config.php");
require_once("../../../include/init.php");
require_once '../../../include/lib/fileDisplayLib.inc.php';

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$clarolineRepositoryWeb = $urlServer."courses/".$currentCourseID;

// lib of this tool
require_once("../../../include/lib/learnPathLib.inc.php");
mysql_select_db($currentCourseID);

function directly_pass_lp_module($table, $userid, $lpmid) {
	// if credit was already set this query changes nothing else it update the query made at the beginning of this script
	$sql = "UPDATE `".$table."`
				SET `credit` = 1,
					`raw` = 100,
					`lesson_status` = 'completed',
					`scoreMin` = 0,
					`scoreMax` = 100
				WHERE `user_id` = " . (int)$userid . "
				AND `learnPath_module_id` = ". (int)$lpmid;
	db_query($sql);
}

if(isset ($_GET['viewModule_id']) && $_GET['viewModule_id'] != '')
	$_SESSION['lp_module_id'] = $_GET['viewModule_id'];

// SET USER_MODULE_PROGRESS IF NOT SET
if($uid) // if not anonymous
{
	// check if we have already a record for this user in this module
	$sql = "SELECT COUNT(LPM.`learnPath_module_id`)
	        FROM `".$TABLEUSERMODULEPROGRESS."` AS UMP, `".$TABLELEARNPATHMODULE."` AS LPM
	       WHERE UMP.`user_id` = '" . (int)$uid . "'
	         AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
	         AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
	         AND LPM.`module_id` = ". (int)$_SESSION['lp_module_id'];
	$num = db_query_get_single_value($sql);

	$sql = "SELECT `learnPath_module_id`
	        FROM `".$TABLELEARNPATHMODULE."`
	       WHERE `learnPath_id` = ". (int)$_SESSION['path_id']."
	         AND `module_id` = ". (int)$_SESSION['lp_module_id'];
	$learnPathModuleId = db_query_get_single_value($sql);

	// if never intialised : create an empty user_module_progress line
	if( !$num || $num == 0 )
	{
	    $sql = "INSERT INTO `".$TABLEUSERMODULEPROGRESS."`
	            ( `user_id` , `learnPath_id` , `learnPath_module_id`, `lesson_location`, `suspend_data` )
	            VALUES ( '" . (int)$uid . "' , ". (int)$_SESSION['path_id']." , ". (int)$learnPathModuleId.",'', '')";
	    db_query($sql);
	}
}  // else anonymous : record nothing !

// Get info about launched module
$sql = "SELECT `contentType`,`startAsset_id`
          FROM `".$TABLEMODULE."`
         WHERE `module_id` = ". (int)$_SESSION['lp_module_id'];

$module = db_query_get_single_row($sql);

$sql = "SELECT `path` FROM `".$TABLEASSET."`
              WHERE `asset_id` = ". (int)$module['startAsset_id'];

$assetPath = db_query_get_single_value($sql);

// Get path of file of the starting asset to launch
switch ($module['contentType'])
{
	case CTDOCUMENT_ :
		if($uid) { // Directly pass this module
			directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int)$uid, (int)$learnPathModuleId);
		} // else anonymous : record nothing
                $moduleStartAssetPage = file_url($assetPath);
		break;

	case CTEXERCISE_ :
		// clean session vars of exercise
		unset($_SESSION['objExercise']);
		unset($_SESSION['objQuestion']);
		unset($_SESSION['objAnswer']);
		unset($_SESSION['questionList']);
		unset($_SESSION['exerciseResult']);
		unset($_SESSION['exeStartTime'	]);

		$moduleStartAssetPage = "showExercise.php?exerciseId=".$assetPath;
		break;
	case CTSCORMASSET_ :
		if($uid) { // Directly pass this module
			directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int)$uid, (int)$learnPathModuleId);
		} // else anonymous : record nothing
		// Don't break, we need to execute the following SCORM code
	case CTSCORM_ :
		// real scorm content method
		$startAssetPage = $assetPath;
		$modulePath     = "path_".$_SESSION['path_id'];
		$moduleStartAssetPage = $clarolineRepositoryWeb."/scormPackages/".$modulePath.$startAssetPage;
		break;
	case CTCLARODOC_ :
		break;
	case CTCOURSE_DESCRIPTION_ :
		if($uid) { // Directly pass this module
			directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int)$uid, (int)$learnPathModuleId);
		} // else anonymous : record nothing

		$moduleStartAssetPage = "showCourseDescription.php";
		break;
	case CTLINK_ :
		if($uid) { // Directly pass this module
			directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int)$uid, (int)$learnPathModuleId);
		} // else anonymous : record nothing

		$moduleStartAssetPage = $assetPath;
		break;
} // end switch

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Frameset//EN''http://www.w3.org/TR/html4/frameset.dtd'>
<html><head>";

// add the update frame if this is a SCORM module
if ($module['contentType'] == CTSCORM_ || $module['contentType'] == CTSCORMASSET_) {
	require_once("scormAPI.inc.php");
	echo "<frameset border='0' rows='0,75,*' frameborder='no'>
		<frame src='updateProgress.php' name='upFrame'>";
} else {
	echo "<frameset border='0' rows='95,*' frameborder='no'>";
}

echo "<frame src='../viewer_toc.php' name='tocFrame' scrolling='no' />";
echo "<frameset border='0' cols='180,*' frameborder='0'>";
echo "<frame src='../toc.php' name='tocleftFrame'>";
echo "<frame src='$moduleStartAssetPage' name='scoFrame'>";
echo "</frameset>"; 
echo "</frameset>";
echo "<noframes>";
echo "<body>";
echo $langBrowserCannotSeeFrames;
echo "</body></noframes></html>";
?>
