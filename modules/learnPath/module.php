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
  module.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: module.php Revision: 1.26

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
  @Description: This script provides information on the progress of a
  learning path module and then launches navigation for it.
  It also displays some extra option for the teacher.
  ==============================================================================
 */

$require_current_course = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

$body_action = '';

$pageName = $langLearningObject;
if (!add_units_navigation()) {
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langLearningPaths);
    if ($is_editor) {
        $navigation[] = array('url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'],
            'name' => $langAdm);
    }
}

if (isset($_GET['path_id']) && $_GET['path_id'] != '') {
    $_SESSION['path_id'] = intval($_GET['path_id']);
}
// module_id
if (isset($_GET['module_id']) && $_GET['module_id'] != '') {
    $_SESSION['lp_module_id'] = intval($_GET['module_id']);
}


$lp = Database::get()->querySingle("SELECT name, visible FROM lp_learnPath WHERE learnPath_id = ?d AND `course_id` = ?d", $_SESSION['path_id'], $course_id);
if (!add_units_navigation() && !$is_editor) {
    $navigation[] = array("url" => "learningPath.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'], "name" => $lp->name);
}

if (!$is_editor && $lp->visible == 0) {
    // if the learning path is invisible, don't allow users in it
    header("Location: ./index.php?course=$course_code");
    exit();
}

check_LPM_validity($is_editor, $course_code);

// main page
// FIRST WE SEE IF USER MUST SKIP THE PRESENTATION PAGE OR NOT
// triggers are : if there is no introdution text or no user module progression statistics yet and user is not admin,
// then there is nothing to show and we must enter in the module without displaying this page.

/*
 *  GET INFOS ABOUT MODULE and LEARNPATH_MODULE
 */

// check in the DB if there is a comment set for this module in general
$module = Database::get()->querySingle("SELECT `comment`, `startAsset_id`, `contentType`
        FROM `lp_module`
        WHERE `module_id` = ?d
        AND `course_id` = ?d", $_SESSION['lp_module_id'], $course_id);

if (empty($module->comment) || $module->comment == $langDefaultModuleComment) {
    $noModuleComment = true;
} else {
    $noModuleComment = false;
}


if ($module->startAsset_id == 0) {
    $noStartAsset = true;
} else {
    $noStartAsset = false;
}


// check if there is a specific comment for this module in this path
$learnpath_module = Database::get()->querySingle("SELECT `specificComment`
        FROM `lp_rel_learnPath_module`
        WHERE `module_id` = ?d
        AND `learnPath_id` = ?d", $_SESSION['lp_module_id'], $_SESSION['path_id']);

if (empty($learnpath_module->specificComment) || $learnpath_module->specificComment == $langDefaultModuleAddedComment) {
    $noModuleSpecificComment = true;
} else {
    $noModuleSpecificComment = false;
}

// check in DB if user has already browsed this module

$sql = "SELECT `contentType`,
	`total_time`,
	`session_time`,
	`scoreMax`,
	`raw`,
	`lesson_status`
        FROM `lp_user_module_progress` AS UMP,
             `lp_rel_learnPath_module` AS LPM,
             `lp_module` AS M
        WHERE UMP.`user_id` = '$uid'
          AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
          AND LPM.`learnPath_id` = ?d
          AND LPM.`module_id` = ?d
          AND LPM.`module_id` = M.`module_id`
          AND M.`course_id` = ?d";
$resultBrowsed = Database::get()->querySingle($sql, $_SESSION['path_id'], $_SESSION['lp_module_id'] , $course_id);

$toolName = $langLearningPaths;

if ($module->contentType == CTSCORM_ || $module->contentType == CTSCORMASSET_) {
    $pageName = $langSCORMTypeDesc;
}
if ($module->contentType == CTEXERCISE_) {
    $pageName = $langExerciseAsModuleLabel;
}
if ($module->contentType == CTDOCUMENT_) {
    $pageName = $langDocumentAsModuleLabel;
}
if ($module->contentType == CTLINK_) {
    $pageName = $langLinkAsModuleLabel;
}
if ($module->contentType == CTCOURSE_DESCRIPTION_) {
    $pageName = $langCourseDescriptionAsModuleLabel;
}
if ($module->contentType == CTLABEL_) {
    $pageName = $langModuleOfMyCourseLabel_onom;
}
if ($module->contentType == CTMEDIA_ || $module->contentType == CTMEDIALINK_) {
    $pageName = $langMediaAsModuleLabel;
}
if ($is_editor) {
    $pageName = $langModify . " " . $pageName;
} else {
    $pageName = $langTracking . " " . $pageName;
}

// redirect user to the path browser if needed
if (!$is_editor && !$resultBrowsed && $noModuleComment && $noModuleSpecificComment && !$noStartAsset) {
    header("Location:./viewer.php?course=$course_code");
    exit();
}

//back button
if ($is_editor) {
    $pathBack = "./learningPathAdmin.php";
} else {
    $pathBack = "./learningPath.php";
}
$tool_content .= 
         action_bar(array(
            array('title' => $langBack,
                'url' => $pathBack . "?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label')),false) ;

$tool_content .="
    <div class='panel panel-default lp-module-show'>
        <div class='panel-heading'><h3 class='panel-title'>$langLearningObjectData</h3>
        </div>
        <div class='panel-body'>
            <div class='row'>
                <div class='col-sm-3'>
                    <strong>$langTitle:</strong>
                </div>
                <div class='col-sm-9'>";
                    $cmd = ( isset($_REQUEST['cmd']) && is_string($_REQUEST['cmd']) ) ? (string) $_REQUEST['cmd'] : '';

            if ($cmd == "updateName") {
                $tool_content .= "" . disp_message_box1(nameBox(MODULE_, UPDATE_, $langModify)) . "";
            } else {
                $tool_content .= "" . nameBox(MODULE_, DISPLAY_) . "";
            }
$tool_content .= "</div>
            </div>
            <div class='row'>
                <div class='col-sm-3'>
                    <strong>$langComments:</strong>
                </div>
                <div class='col-sm-9'>";
if ($module->contentType != CTLABEL_) {

    //############################### MODULE COMMENT BOX #################################\\
    //#### COMMENT #### courseAdmin cannot modify this if this is a imported module ####\\
    // this the comment of the module in ALL learning paths
    if ($cmd == "updatecomment") {
        $tool_content .= commentBox(MODULE_, UPDATE_);
    } elseif ($cmd == "delcomment") {
        $tool_content .= "" . commentBox(MODULE_, DELETE_) . "";
    } else {
        $tool_content .= "" . commentBox(MODULE_, DISPLAY_) . "";
    }
    $tool_content .="
      </div>
    </div>";
    $tool_content .="
    <div class='row'>
        <div class='col-sm-3'>
            <strong>$langComments - $langInstructions:<br /><small class='text-muted'>($langModuleComment_inCurrentLP)</small></strong>
        </div>
        <div class='col-sm-9'>";
    //#### ADDED COMMENT #### courseAdmin can always modify this ####\\
    // this is a comment for THIS module in THIS learning path
    if ($cmd == "updatespecificComment") {
        $tool_content .= commentBox(LEARNINGPATHMODULE_, UPDATE_);
    } elseif ($cmd == "delspecificComment") {
        $tool_content .= commentBox(LEARNINGPATHMODULE_, DELETE_);
    } else {
        $tool_content .= commentBox(LEARNINGPATHMODULE_, DISPLAY_);
    }
} //  if($module['contentType'] != CTLABEL_ )
$tool_content .= "</div>
            </div>";
            
//$tool_content .= "<div class='row'>
//                <div class='col-sm-3'>
//                    <strong>$langProgInModuleTitle:</strong>
//                </div>
//                <div class='col-sm-9'>hjgfjhgf
//                </div>
//            </div>
//        ";
                //############################ PROGRESS  AND  START LINK #############################\\

/* Display PROGRESS */

if ($module->contentType != CTLABEL_) { //
    if ($resultBrowsed && $module->contentType != CTLABEL_) {
        $contentType_img = selectImage($resultBrowsed->contentType);
        $contentType_alt = selectAlt($resultBrowsed->contentType);

        if ($resultBrowsed->contentType == CTSCORM_ || $resultBrowsed->contentType == CTSCORMASSET_) {
            $contentDescType = $langSCORMTypeDesc;
        }
        if ($resultBrowsed->contentType == CTEXERCISE_) {
            $contentDescType = $langEXERCISETypeDesc;
        }
        if ($resultBrowsed->contentType == CTDOCUMENT_) {
            $contentDescType = $langDOCUMENTTypeDesc;
        }
        if ($resultBrowsed->contentType == CTLINK_) {
            $contentDescType = $langLINKTypeDesc;
        }
        if ($resultBrowsed->contentType == CTCOURSE_DESCRIPTION_) {
            $contentDescType = $langDescriptionCours;
        }
        if ($resultBrowsed->contentType == CTMEDIA_ || $resultBrowsed->contentType == CTMEDIALINK_) {
            $contentDescType = $langMediaTypeDesc;
        }

        

        //display type of the module
        
        $tool_content .= "<div class='row'>
                            <div class='col-sm-3'>
                                <strong>$langTypeOfModule:</strong>
                            </div>
                            <div class='col-sm-9'>
                                <i class='fa $contentType_img'></i> $contentDescType
                            </div>
                        </div>
                ";
        
        //display total time already spent in the module
        
        $tool_content .= "<div class='row'>
                            <div class='col-sm-3'>
                                <strong>$langTotalTimeSpent:</strong>
                            </div>
                            <div class='col-sm-9'>
                                $resultBrowsed->total_time
                            </div>
                        </div>
                ";

        //display time passed in last session
        
        $tool_content .= "<div class='row'>
                            <div class='col-sm-3'>
                                <strong>$langLastSessionTimeSpent:</strong>
                            </div>
                            <div class='col-sm-9'>
                                $resultBrowsed->session_time
                            </div>
                        </div>
                ";

        //display user best score
        if ($resultBrowsed->scoreMax > 0) {
            $raw = round($resultBrowsed->raw / $resultBrowsed->scoreMax * 100);
        } else {
            $raw = 0;
        }

        $raw = max($raw, 0);

        if (($resultBrowsed->contentType == CTSCORM_ ) && ($resultBrowsed->scoreMax <= 0) && ((($resultBrowsed->lesson_status == "COMPLETED") || ($resultBrowsed->lesson_status == "PASSED") ) || ($resultBrowsed->raw != -1) )) {
            $raw = 100;
        }

        // no sens to display a score in case of a document module
//        if (($resultBrowsed->contentType != CTDOCUMENT_) &&
//                ($resultBrowsed->contentType != CTLINK_) &&
//                ($resultBrowsed->contentType != CTCOURSE_DESCRIPTION_) &&
//                ($resultBrowsed->contentType != CTMEDIA_) &&
//                ($resultBrowsed->contentType != CTMEDIALINK_)
//        ) {
//            $tool_content .= "<div class='row'>
//                            <div class='col-sm-3'>
//                                <strong>$langYourBestScore:</strong>
//                            </div>
//                            <div class='col-sm-9'>
//                                " . disp_progress_bar($raw, 1) . "
//                            </div>
//                        </div>
//                ";
//        }

        //display lesson status
        // display a human readable string ...

        if ($resultBrowsed->lesson_status == "NOT ATTEMPTED") {
            $statusToDisplay = $langNotAttempted;
        } else if ($resultBrowsed->lesson_status == "PASSED") {
            $statusToDisplay = $langPassed;
        } else if ($resultBrowsed->lesson_status == "FAILED") {
            $statusToDisplay = $langFailed;
        } else if ($resultBrowsed->lesson_status == "COMPLETED") {
            $statusToDisplay = $langAlreadyBrowsed;
        } else if ($resultBrowsed->lesson_status == "BROWSED") {
            $statusToDisplay = $langAlreadyBrowsed;
        } else if ($resultBrowsed->lesson_status == "INCOMPLETE") {
            $statusToDisplay = $langNeverBrowsed;
        } else {
            $statusToDisplay = $resultBrowsed->lesson_status;
        }
        
        $tool_content .= "<div class='row'>
                            <div class='col-sm-3'>
                                <strong>$langLessonStatus:</strong>
                            </div>
                            <div class='col-sm-9'>
                                $statusToDisplay
                            </div>
                        </div>
                ";
    } //end display stats

    /* START */
    // check if module.startAssed_id is set and if an asset has the corresponding asset_id
    // asset_id exists ?  for the good module  ?
    $asset = Database::get()->querySingle("SELECT `asset_id`
              FROM `lp_asset`
             WHERE `asset_id` = ?d
               AND `module_id` = ?d", $module->startAsset_id, $_SESSION['lp_module_id']);
    
    $tool_content .="
    <div class='row'>
        <div class='col-sm-3'>
            <strong>$langPreview:</strong>
        </div>
        <div class='col-sm-9'>";
    if ($module->startAsset_id != "" && $asset->asset_id == $module->startAsset_id) {
        $tool_content .= '' . "\n"
                . '        <form action="./viewer.php?course=' . $course_code . '" method="post">' . "\n"
                . '        <input class="btn btn-primary" type="submit" value="' . $langStartModule . '" />' . "\n"
                . '        </form>' . "\n";
    } else {
        $tool_content .= '        <p><center>' . $langNoStartAsset . '</center></p>' . "\n";
    }
}// end if($module['contentType'] != CTLABEL_)
$tool_content .= "</div></div>";
//################################## MODULE NAME BOX #################################\\


//####################################################################################\\
//################################# ADMIN DISPLAY ####################################\\
//####################################################################################\\
/*
  if( $is_editor ) // for teacher only
  {
  switch ($module['contentType'])
  {
  case CTDOCUMENT_ :
  require_once("./include/document.inc.php");
  break;
  case CTEXERCISE_ :
  require_once("./include/exercise.inc.php");
  break;
  case CTSCORM_ :
  require_once("./include/scorm.inc.php");
  break;
  case CTCLARODOC_ :
  case CTLABEL_ :
  case CTCOURSE_DESCRIPTION_ :
  case CTLINK_:
  break;
  }
  } // if ($is_editor)
 */

$tool_content .= "
    </div>
    </div>"
;



draw($tool_content, 2, null, $head_content, $body_action);
