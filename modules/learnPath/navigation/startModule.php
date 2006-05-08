<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 * @subpackage navigation
 *
  * This script is the main page loaded when user start viewing a module in the browser.
  * We define here the frameset containing the launcher module (SCO if it is a SCORM conformant one)
  * and a top and bottom frame to display the claroline banners.
  * If the module is an exercise of claroline, no frame is created,
  * we redirect to exercise_submit.php page in a path mode
  *
  */

/*======================================
       CLAROLINE MAIN
  ======================================*/

$require_current_course = TRUE;
$langFiles              = "learnPath";
require("../../../config/config.php");
require("../../../include/init.php");

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$clarolineRepositoryWeb = $urlServer.$currentCourseID;

// lib of this tool
include("../../../include/lib/learnPathLib.inc.php");
include("../claro_main.lib.php");
mysql_select_db($currentCourseID);

if(isset ($_GET['viewModule_id']) && $_GET['viewModule_id'] != '')
	$_SESSION['module_id'] = $_GET['viewModule_id'];

// SET USER_MODULE_PROGRESS IF NOT SET
if($uid) // if not anonymous
{
	// check if we have already a record for this user in this module
	$sql = "SELECT COUNT(LPM.`learnPath_module_id`)
	        FROM `".$TABLEUSERMODULEPROGRESS."` AS UMP, `".$TABLELEARNPATHMODULE."` AS LPM
	       WHERE UMP.`user_id` = '" . (int)$uid . "'
	         AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
	         AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
	         AND LPM.`module_id` = ". (int)$_SESSION['module_id'];
	$num = claro_sql_query_get_single_value($sql);

	$sql = "SELECT `learnPath_module_id`
	        FROM `".$TABLELEARNPATHMODULE."`
	       WHERE `learnPath_id` = ". (int)$_SESSION['path_id']."
	         AND `module_id` = ". (int)$_SESSION['module_id'];
	$learnPathModuleId = claro_sql_query_get_single_value($sql);

	// if never intialised : create an empty user_module_progress line
	if( !$num || $num == 0 )
	{
	    $sql = "INSERT INTO `".$TABLEUSERMODULEPROGRESS."`
	            ( `user_id` , `learnPath_id` , `learnPath_module_id`, `lesson_location`, `suspend_data` )
	            VALUES ( '" . (int)$uid . "' , ". (int)$_SESSION['path_id']." , ". (int)$learnPathModuleId.",'', '')";
	    claro_sql_query($sql);
	}
}  // else anonymous : record nothing !


// Get info about launched module

$sql = "SELECT `contentType`,`startAsset_id`
          FROM `".$TABLEMODULE."`
         WHERE `module_id` = ". (int)$_SESSION['module_id'];

$module = claro_sql_query_get_single_row($sql);

$sql = "SELECT `path`
               FROM `".$TABLEASSET."`
              WHERE `asset_id` = ". (int)$module['startAsset_id'];

$assetPath = claro_sql_query_get_single_value($sql);

// Get path of file of the starting asset to launch

$withFrames = false;

switch ($module['contentType'])
{
	case CTDOCUMENT_ :
		if($uid)
		{ 
		    // if credit was already set this query changes nothing else it update the query made at the beginning of this script
		    $sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."`
		               SET `credit` = 1,
		                   `raw` = 100,
		                   `lesson_status` = 'completed',
		                   `scoreMin` = 0,
		                   `scoreMax` = 100
		             WHERE `user_id` = " . (int)$uid . "
		               AND `learnPath_module_id` = ". (int)$learnPathModuleId;

		    claro_sql_query($sql);
		} // else anonymous : record nothing

		$startAssetPage = urlencode($assetPath);
        if ( strstr($_SERVER['SERVER_SOFTWARE'], 'Apache') 
              && (isset($secureDocumentDownload) && $secureDocumentDownload == true)
            )
        { 
            // slash argument method - only compatible with Apache
            // str_replace("%2F","/",urlencode($startAssetPage)) is used to avoid problems with accents in filename.
            $moduleStartAssetPage = $clarolineRepositoryWeb.'/document'.str_replace('%2F','/',$startAssetPage);
        }
        else
        {
            // question mark argument method, for IIS ...
            $moduleStartAssetPage = $clarolineRepositoryWeb.'/document'.str_replace('%2F','/',$startAssetPage);
        }
  		$withFrames = true;
		break;

	case CTEXERCISE_ :
		// clean session vars of exercise
		unset($_SESSION['objExercise']);
		unset($_SESSION['objQuestion']);
		unset($_SESSION['objAnswer']);
		unset($_SESSION['questionList']);
		unset($_SESSION['exerciseResult']);
		unset($_SESSION['exeStartTime'	]);
		session_unregister('objExercise');
		session_unregister('objQuestion');
		session_unregister('objAnswer');
		session_unregister('questionList');
		session_unregister('exerciseResult');
		session_unregister('exeStartTime');

		$_SESSION['inPathMode'] = true;
		$startAssetpage = $clarolineRepositoryWeb."exercice/exercice_submit.php";
		$moduleStartAssetPage = $startAssetpage."?exerciseId=".$assetPath;
		break;
	case CTSCORM_ :
		// real scorm content method
		$startAssetPage = $assetPath;
		$modulePath     = "path_".$_SESSION['path_id'];
		$moduleStartAssetPage = $clarolineRepositoryWeb."/scormPackages/".$modulePath.$startAssetPage;
		break;
	case CTCLARODOC_ :
		break;
	case CTCOURSE_DESCRIPTION_ :
		if($uid)
		{ 
		    // if credit was already set this query changes nothing else it update the query made at the beginning of this script
		    $sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."`
		               SET `credit` = 1,
		                   `raw` = 100,
		                   `lesson_status` = 'completed',
		                   `scoreMin` = 0,
		                   `scoreMax` = 100
		             WHERE `user_id` = " . (int)$uid . "
		               AND `learnPath_module_id` = ". (int)$learnPathModuleId;

		    claro_sql_query($sql);
		} // else anonymous : record nothing
		
		$moduleStartAssetPage = "showCourseDescription.php";
		
		$withFrames = true;
		break;
} // end switch

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<html>

  <head>
  
<?php

   // add the update frame if this is a SCORM module   
   if ( $module['contentType'] == CTSCORM_ )
   {
      
      include("scormAPI.inc.php");
      echo "<frameset border='0' cols='0,20%,80%' frameborder='no'>
            <frame src='updateProgress.php' name='upFrame'>";
      
   }
   else
   {
      echo "<frameset border='0' cols='20%,80%' frameborder='yes'>";
   }
?>
    <frame src="tableOfContent.php" name="tocFrame" />
    <frame src="<?php echo $moduleStartAssetPage; ?>" name="scoFrame">

    </frameset>
  <noframes>
<body>
<?php
  echo $langBrowserCannotSeeFrames;
?>
   </body>
</noframes>
</html>
