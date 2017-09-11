<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


/* ===========================================================================
  startModule.php
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
  ==============================================================================
 */

$require_current_course = true;
require_once '../../../include/init.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/document/doc_init.php';

$TABLEUSERMODULEPROGRESS = "lp_user_module_progress";
$clarolineRepositoryWeb = $urlServer . "courses/" . $course_code;
doc_init();

function directly_pass_lp_module($table, $userid, $lpmid) {
    global $course_id;
    
    // if credit was already set this query changes nothing else it update the query made at the beginning of this script
    $sql = "UPDATE `" . $table . "`
               SET `credit` = 1,
                   `raw` = 100,
                   `lesson_status` = 'completed',
                   `scoreMin` = 0,
                   `scoreMax` = 100
             WHERE `user_id` = ?d
               AND `learnPath_module_id` = ?d";
    Database::get()->query($sql, $userid, $lpmid);
    triggerLPGame($course_id, $userid, $_SESSION['path_id'], LearningPathEvent::UPDPROGRESS);
}

if (isset($_GET['viewModule_id']) && !empty($_GET['viewModule_id'])) {
    $_SESSION['lp_module_id'] = intval($_GET['viewModule_id']);
}

check_LPM_validity($is_editor, $course_code, true, true);

// SET USER_MODULE_PROGRESS IF NOT SET
if ($uid) { // if not anonymous
    // check if we have already a record for this user in this module
    $num = Database::get()->querySingle("SELECT COUNT(LPM.`learnPath_module_id`) AS count
	        FROM `lp_user_module_progress` AS UMP, `lp_rel_learnPath_module` AS LPM
	       WHERE UMP.`user_id` = ?d
	         AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
	         AND LPM.`learnPath_id` = ?d
	         AND LPM.`module_id` = ?d", $uid, $_SESSION['path_id'], $_SESSION['lp_module_id'])->count;

    $learnPathModuleId = Database::get()->querySingle("SELECT `learnPath_module_id`
	      FROM `lp_rel_learnPath_module`
	     WHERE `learnPath_id` = ?d
	       AND `module_id` = ?d", $_SESSION['path_id'], $_SESSION['lp_module_id'])->learnPath_module_id;

    // if never intialised : create an empty user_module_progress line
    if ($num == 0) {
        Database::get()->query("INSERT INTO `lp_user_module_progress`
	            ( `user_id` , `learnPath_id` , `learnPath_module_id`, `lesson_location`, `suspend_data` )
	            VALUES (?d , ?d, ?d, '', '')", $uid, $_SESSION['path_id'], $learnPathModuleId);
        triggerLPGame($course_id, $uid, $_SESSION['path_id'], LearningPathEvent::UPDPROGRESS);
    }
}  // else anonymous : record nothing !
// Get info about launched module
$module = Database::get()->querySingle("SELECT `contentType`, `startAsset_id`, `name`
        FROM `lp_module`
       WHERE `module_id` = ?d
         AND `course_id` = ?d", $_SESSION['lp_module_id'], $course_id);

$assetPath = Database::get()->querySingle("SELECT `path` FROM `lp_asset` WHERE `asset_id` = ?d", $module->startAsset_id)->path;

// Get path of file of the starting asset to launch
switch ($module->contentType) {
    case CTDOCUMENT_ :
        if ($uid) { // Directly pass this module
            directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int) $uid, (int) $learnPathModuleId);
        } // else anonymous : record nothing
        $file_url = file_url($assetPath);
        $play_url = file_playurl($assetPath);

        $furl = $file_url;
        if (MultimediaHelper::isSupportedMedia($module->name)) {
            $furl = $play_url;
        }

        $moduleStartAssetPage = $furl;
        $_SESSION['FILE_PHP__LP_MODE'] = true;
        break;

    case CTEXERCISE_ :
        // clean session vars of exercise
        unset($_SESSION['objExercise']);
        unset($_SESSION['objQuestion']);
        unset($_SESSION['objAnswer']);
        unset($_SESSION['questionList']);
        unset($_SESSION['exerciseResult']);
        unset($_SESSION['exeStartTime']);

        $moduleStartAssetPage = "showExercise.php?course=$course_code&amp;exerciseId=" . $assetPath;
        break;
    case CTSCORMASSET_ :
        if ($uid) { // Directly pass this module
            directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int) $uid, (int) $learnPathModuleId);
        } // else anonymous : record nothing
    // Don't break, we need to execute the following SCORM code
    case CTSCORM_ :
        // real scorm content method
        $startAssetPage = $assetPath;
        $modulePath = "path_" . $_SESSION['path_id'];
        $moduleStartAssetPage = $clarolineRepositoryWeb . "/scormPackages/" . $modulePath . $startAssetPage;
        break;
    case CTCLARODOC_ :
        break;
    case CTCOURSE_DESCRIPTION_ :
        if ($uid) { // Directly pass this module
            directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int) $uid, (int) $learnPathModuleId);
        } // else anonymous : record nothing

        $moduleStartAssetPage = "showCourseDescription.php?course=$course_code";
        break;
    case CTLINK_ :
        if ($uid) { // Directly pass this module
            directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int) $uid, (int) $learnPathModuleId);
        } // else anonymous : record nothing

        $moduleStartAssetPage = $assetPath;
        break;
    case CTMEDIA_ :
        if ($uid) {
            directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int) $uid, (int) $learnPathModuleId);
        }

        if (MultimediaHelper::isSupportedFile($assetPath)) {
            $moduleStartAssetPage = "showMedia.php?course=$course_code&amp;id=" . $assetPath . "&amp;viewModule_id=$_SESSION[lp_module_id]";
        } else {
            $moduleStartAssetPage = htmlspecialchars($urlServer
                    . "modules/video/index.php?course=$course_code&action=download&id=" . $assetPath
                    , ENT_QUOTES);
        }
        break;
    case CTMEDIALINK_ :
        if ($uid) {
            directly_pass_lp_module($TABLEUSERMODULEPROGRESS, (int) $uid, (int) $learnPathModuleId);
        }

        if (MultimediaHelper::isEmbeddableMedialink($assetPath)) {
            $moduleStartAssetPage = "showMediaLink.php?course=$course_code&amp;id=" . urlencode($assetPath) . "&amp;viewModule_id=$_SESSION[lp_module_id]";
        } else {
            $moduleStartAssetPage = $assetPath;
        }        
        break;
} // end switch

$unitParam = isset($_GET['unit'])? "&amp;unit=$_GET[unit]": '';

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Frameset//EN''http://www.w3.org/TR/html4/frameset.dtd'>
<html><head>";

// add the update frame if this is a SCORM module
if ($module->contentType == CTSCORM_ || $module->contentType == CTSCORMASSET_) {
    require_once("scormAPI.inc.php");
    echo "<frameset border='0' rows='0,56,*' frameborder='0'>
		<frame src='updateProgress.php?course=$course_code$unitParam' name='upFrame'>";
} else {
    echo "<frameset border='0' rows='50,*' frameborder='0'>";
}

echo "<frame src='../viewer_toc.php?course=$course_code$unitParam' name='tocFrame' scrolling='no' />";
echo "<frameset border='0' cols='250,*' frameborder='0' id='colFrameset'>";
echo "<frame src='../toc.php?course=$course_code$unitParam' name='tocleftFrame'>";
echo "<frame src='$moduleStartAssetPage' name='scoFrame'>";
echo "</frameset>";
echo "</frameset>";
echo "<noframes>";
echo "<body>";
echo $langBrowserCannotSeeFrames;
echo "</body></noframes></html>";
