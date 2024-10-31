<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */


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
          AND M.`course_id` = ?d
        ORDER BY UMP.`raw` DESC, UMP.`attempt` DESC LIMIT 1";
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
                'level' => 'primary')),false) . "
    <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
        <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
            <h3 class='mb-0'>$langLearningObjectData</h3>
        </div>
        <div class='card-body'>
        <ul class='list-group list-group-flush'>
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                <div class='col-md-3 col-12'>
                    <div class='title-default'>$langTitle</div>
                </div>
                <div class='col-md-9 col-12 title-default-line-height'>";
if (isset($_REQUEST['cmd']) and $_REQUEST['cmd'] == 'updateName') {
    $tool_content .= nameBox(MODULE_, UPDATE_, $langModify);
} else {
    $tool_content .= nameBox(MODULE_, DISPLAY_);
}
$tool_content .= "</div>
                </div>
            </li>";


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
            $contentDescType = $langLink;
        }
        if ($resultBrowsed->contentType == CTCOURSE_DESCRIPTION_) {
            $contentDescType = $langDescriptionCours;
        }
        if ($resultBrowsed->contentType == CTMEDIA_ || $resultBrowsed->contentType == CTMEDIALINK_) {
            $contentDescType = $langMediaTypeDesc;
        }



        //display type of the module

        $tool_content .= "
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langTypeOfModule</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                <i class='fa $contentType_img'></i> $contentDescType
                            </div>
                        </div>
                    </li>
                ";

        //display total time already spent in the module

        $tool_content .= "
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langTotalTimeSpent</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                $resultBrowsed->total_time
                            </div>
                        </div>
                    </li>
                ";

        //display time passed in last session

        $tool_content .= "
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langLastSessionTimeSpent</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                $resultBrowsed->session_time
                            </div>
                        </div>
                    </li>
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

        $tool_content .= "
                    <li class='list-group-item element'>
                        <div class='row row-cols-1 row-cols-md-2 g-1'>
                            <div class='col-md-3 col-12'>
                                <div class='title-default'>$langLessonStatus</div>
                            </div>
                            <div class='col-md-9 col-12 title-default-line-height'>
                                $statusToDisplay
                            </div>
                        </div>
                    </li>
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
    <li class='list-group-item element'>
        <div class='row row-cols-1 row-cols-md-2 g-1'>
            <div class='col-md-3 col-12'>
                <div class='title-default'>$langPreview</div>
            </div>
            <div class='col-md-9 col-12 title-default-line-height'>";
        if ($module->startAsset_id != "" && $asset->asset_id == $module->startAsset_id) {
            $tool_content .= "<form action='./viewer.php?course=$course_code' method='post'> 
                                <input class='btn submitAdminBtn' type='submit' value='$langStartModule'>
                            </form>";
        } else {
            $tool_content .= "<p style='align:center;'>$langNoStartAsset</p>";
        }
        $tool_content .= "
            </div>
        </div>
    </li>";
}// end if($module['contentType'] != CTLABEL_)

$tool_content .= "</ul></div></div>";

draw($tool_content, 2, null, $head_content);
