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

/**
 *      @file index.php
 * 	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
 * 	based on Claroline version 1.7 licensed under GPL
 * 	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
 *
 * 	      original file: learningPathList Revision: 1.56
 *
 * 	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
 *                     Lederer Guillaume <led@cerdecam.be>
 *      @description: This file displays the list of all learning paths available
 *                 for the course.
 *
 *                 Display :
 *                  - Name of tool
 *                  - Introduction text for learning paths
 *                  - (admin of course) link to create new empty learning path
 *                  - (admin of course) link to import (upload) a learning path
 *                  - list of available learning paths
 *                 - (student) only visible learning paths
 *                  - (student) the % of progression into each learning path
 *                  - (admin of course) all learning paths with
 *                  - modify, delete, statistics, visibility and order, options
 */
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = "Path";

define('CLARO_FILE_PERMISSIONS', 0777);

include "../../include/baseTheme.php";
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_LP);
/* * *********************************** */
require_once 'include/log.php';

$style = "";

if (!add_units_navigation(TRUE)) {
    $toolName = $langLearningPaths;
}

if (isset($_GET['cmd']) and $_GET['cmd'] == 'export' and isset($_GET['path_id']) and is_numeric($_GET['path_id']) and $is_editor) {

    require_once "include/scormExport.inc.php";

    $scorm = new ScormExport(intval($_GET['path_id']));
    if (!$scorm->export()) {
        $dialogBox = '<b>' . $langScormErrorExport . '</b><br />' . "\n" . '<ul>' . "\n";
        foreach ($scorm->getError() as $error) {
            $dialogBox .= '<li>' . $error . '</li>' . "\n";
        }
        $dialogBox .= '<ul>' . "\n";
    }
} // endif $cmd == export

if (isset($_GET['cmd']) and $_GET['cmd'] == 'export12' and isset($_GET['path_id']) and is_numeric($_GET['path_id']) and $is_editor) {

    require_once "include/scormExport12.inc.php";

    $scorm = new ScormExport(intval($_GET['path_id']));
    if (!$scorm->export()) {
        $dialogBox = '<b>' . $langScormErrorExport . '</b><br />' . "\n" . '<ul>' . "\n";
        foreach ($scorm->getError() as $error) {
            $dialogBox .= '<li>' . $error . '</li>' . "\n";
        }
        $dialogBox .= '<ul>' . "\n";
    }
} // endif $cmd == export12

if (isset($_GET['cmd']) and $_GET['cmd'] == 'exportIMSCP'
        and isset($_GET['path_id']) and is_numeric($_GET['path_id']) and $is_editor) {

    require_once "include/IMSCPExport.inc.php";

    $imscp = new IMSCPExport(intval($_GET['path_id']), $language);
    if (!$imscp->export()) {
        $dialogBox = '<b>' . $langScormErrorExport . '</b><br />' . "\n" . '<ul>' . "\n";
        foreach ($imscp->getError() as $error) {
            $dialogBox .= '<li>' . $error . '</li>' . "\n";
        }
        $dialogBox .= '<ul>' . "\n";
    }
}

if ($is_editor) {
    $head_content .= "<script type='text/javascript'>
          function confirmation (name)
          {
              if (confirm('" . clean_str_for_javascript($langConfirmDelete) . "' + name + '. ' + '" . $langModuleStillInPool . "'))
                  {return true;}
              else
                  {return false;}
          }
          </script>";
    $head_content .= "<script type='text/javascript'>
          function scormConfirmation (name)
          {
              if (confirm('" . clean_str_for_javascript($langAreYouSureToDeleteScorm) . "' + name + ''))
                  {return true;}
              else
                  {return false;}
          }
          </script>";

    if (isset($_REQUEST['cmd'])) {
        // execution of commands
        switch ($_REQUEST['cmd']) {
            // DELETE COMMAND
            case "delete" :
                if (is_dir($webDir . "/courses/" . $course_code . "/scormPackages/path_" . $_GET['del_path_id'])) {
                    $findsql = "SELECT M.`module_id`
						FROM  `lp_rel_learnPath_module` AS LPM, `lp_module` AS M
						WHERE LPM.`learnPath_id` = ?d
						AND ( M.`contentType` = ?s OR M.`contentType` = ?s OR M.`contentType` = ?s)
						AND LPM.`module_id` = M.`module_id`
						AND M.`course_id` = ?d";
                    $findResult = Database::get()->queryArray($findsql, $_GET['del_path_id'], CTSCORM_, CTSCORMASSET_, CTLABEL_, $course_id);

                    // Delete the startAssets
                    $delAssetSql = "DELETE FROM `lp_asset` WHERE 1=0";
                    // DELETE the SCORM modules
                    $delModuleSql = "DELETE FROM `lp_module`
					WHERE (`contentType` = ?s OR `contentType` = ?s OR `contentType` = ?s) AND (1=0";

                    foreach ($findResult as $delList) {
                        $delAssetSql .= " OR `module_id`= " . intval($delList->module_id);
                        $delModuleSql .= " OR (`module_id`= " . intval($delList->module_id) . " AND `course_id` = " . intval($course_id) . " )";
                    }
                    Database::get()->query($delAssetSql);

                    $delModuleSql .= ")";
                    Database::get()->query($delModuleSql, CTSCORM_, CTSCORMASSET_, CTLABEL_);

                    // DELETE the directory containing the package and all its content
                    $real = realpath($webDir . "/courses/" . $course_code . "/scormPackages/path_" . $_GET['del_path_id']);
                    claro_delete_file($real);
                } else { // end of dealing with the case of a scorm learning path.
                    $findsql = "SELECT M.`module_id`
						FROM  `lp_rel_learnPath_module` AS LPM,
						`lp_module` AS M
						WHERE LPM.`learnPath_id` = ?d
						AND M.`contentType` = ?s
						AND LPM.`module_id` = M.`module_id`
						AND M.`course_id` = ?d";
                    $findResult = Database::get()->queryArray($findsql, $_GET['del_path_id'], CTLABEL_, $course_id);
                    // delete labels of non scorm learning path
                    $delLabelModuleSql = "DELETE FROM `lp_module` WHERE 1=0";

                    foreach ($findResult as $delList) {
                        $delLabelModuleSql .= " OR (`module_id`=" . intval($delList->module_id) . " AND `course_id` = " . intval($course_id) . " )";
                    }
                    Database::get()->query($delLabelModuleSql);
                }

                // delete everything for this path (common to normal and scorm paths) concerning modules, progress and path
                // delete all user progression
                Database::get()->query("DELETE FROM `lp_user_module_progress` WHERE `learnPath_id` = ?d", $_GET['del_path_id']);
                // delete all relation between modules and the deleted learning path
                Database::get()->query("DELETE FROM `lp_rel_learnPath_module` WHERE `learnPath_id` = ?d", $_GET['del_path_id']);

                // delete the learning path
                $lp_name = Database::get()->querySingle("SELECT name FROM `lp_learnPath` 
                                                                WHERE `learnPath_id` = ?d
                                                                AND `course_id` = ?d", $_GET['del_path_id'], $course_id)->name;
                Database::get()->query("DELETE FROM `lp_learnPath` 
                                                WHERE `learnPath_id` = ?d
                                                AND `course_id` = ?d", $_GET['del_path_id'], $course_id);
                Log::record($course_id, MODULE_ID_LP, LOG_DELETE, array('name' => $lp_name));

                break;
            // ACCESSIBILITY COMMAND
            case "mkBlock" :
            case "mkUnblock" :
                $blocking = ($_REQUEST['cmd'] == "mkBlock") ? 'CLOSE' : 'OPEN';
                Database::get()->query("UPDATE `lp_learnPath` SET `lock` = ?s
					WHERE `learnPath_id` = ?d
					AND `lock` != ?s
					AND `course_id` = ?d", $blocking, $_GET['cmdid'], $blocking, $course_id);
                break;
            // VISIBILITY COMMAND
            case "mkVisibl" :
            case "mkInvisibl" :
                $visibility = ($_REQUEST['cmd'] == "mkVisibl") ? 1 : 0;
                Database::get()->query("UPDATE `lp_learnPath`
					SET `visible` = ?d
					WHERE `learnPath_id` = ?d
					AND `visible` != ?d
					AND `course_id` = ?d", $visibility, $_GET['visibility_path_id'], $visibility, $course_id);
                break;
            // ORDER COMMAND
            case "moveUp" :
                $thisLearningPathId = intval($_GET['move_path_id']);
                $sortDirection = "DESC";
                break;
            case "moveDown" :
                $thisLearningPathId = intval($_GET['move_path_id']);
                $sortDirection = "ASC";
                break;
            // CREATE COMMAND
            case "create" :
                // create form sent
                if (isset($_POST["newPathName"]) && $_POST["newPathName"] != "") {
                    // check if name already exists
                    $num = Database::get()->querySingle("SELECT COUNT(`name`) AS count FROM `lp_learnPath`
						WHERE `name` = ?s
						AND `course_id` = ?d", $_POST['newPathName'], $course_id)->count;
                    if ($num == 0) { // "name" doesn't already exist
                        // determine the default order of this Learning path
                        $order = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max FROM `lp_learnPath` WHERE `course_id` = ?d", $course_id)->max);
                        // create new learning path
                        $lp_id = Database::get()->query("INSERT INTO `lp_learnPath` (`course_id`, `name`, `comment`, `visible`, `rank`)
							VALUES (?d, ?s, ?s, 1, ?d)", $course_id, $_POST['newPathName'], $_POST['newComment'], $order)->lastInsertID;
                        Log::record($course_id, MODULE_ID_LP, LOG_INSERT, array('id' => $lp_id,
                            'name' => $_POST['newPathName'],
                            'comment' => $_POST['newComment']));
                    } else {
                        $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
                        $pageName = $langCreateNewLearningPath;
                        $dialogBox = action_bar(array(
                            array('title' => $langBack,
                                'url' => "index.php?course=$course_code",
                                'icon' => 'fa-reply',
                                'level' => 'primary-label'
                            )
                        ));
                        // display error message
                        $dialogBox .= "<div class='alert alert-warning'>$langErrorNameAlreadyExists</div>";
                        $style = "caution";
                        $dialogBox .= "<div class='form-wrapper'><form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='POST'>
                        <div class='form-group'>
                            <label for='newPathName' class='col-sm-2 control-label'>$langLearningPathName:</label>
                            <div class='col-sm-10'>
                              <input name='newPathName' type='text' class='form-control' id='newPathName'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='newComment' class='col-sm-2 control-label'>$langDescr:</label>
                            <div class='col-sm-10'>
                              <input name='newComment' type='text' class='form-control' id='newComment'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>
                              <input type='hidden' name='cmd' value='create'>".
                                    form_buttons(array(
                                        array(
                                            'text' => $langSave,
                                            'value' => $langCreate
                                        ),
                                        array(
                                            'href' => "index.php?course=$course_code",
                                        )
                                    ))
                                    ."</div>
                            </div>                        
                        </form></div>";
                    }
                } else { // create form requested
                    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
                    $pageName = $langCreateNewLearningPath;                  
                    $dialogBox = action_bar(array(
                        array('title' => $langBack,
                            'url' => "index.php?course=$course_code",
                            'icon' => 'fa-reply',
                            'level' => 'primary-label'
                        )
                    ));
                    $dialogBox .= "<div class='form-wrapper'><form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='POST'>
                        <div class='form-group'>
                            <label for='newPathName' class='col-sm-2 control-label'>$langLearningPathName:</label>
                            <div class='col-sm-10'>
                              <input name='newPathName' type='text' class='form-control' id='newPathName'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='newComment' class='col-sm-2 control-label'>$langDescr:</label>
                            <div class='col-sm-10'>
                              <input name='newComment' type='text' class='form-control' id='newComment'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>
                              <input type='hidden' name='cmd' value='create'>".
                                    form_buttons(array(
                                        array(
                                            'text' => $langSave,
                                            'value' => $langCreate
                                        ),
                                        array(
                                            'href' => "index.php?course=$course_code",
                                        )
                                    ))
                                    ."</div>
                        </div>                        
                        </form></div>";
                }
                break;
            default:
                break;
        } // end of switch
    } // end of if(isset)
} // end of if
// IF ORDER COMMAND RECEIVED
// CHANGE ORDER
if (isset($sortDirection) && $sortDirection) {
    $result = Database::get()->queryArray("SELECT `learnPath_id`, `rank`
            FROM `lp_learnPath`
            WHERE `course_id` = ?d
            ORDER BY `rank` $sortDirection", $course_id);

    // LP = learningPath
    foreach ($result as $LP) {
        // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
        //          COMMIT ORDER SWAP ON THE DB

        if (isset($thisLPOrderFound) && $thisLPOrderFound == true) {
            $nextLPId = $LP->learnPath_id;
            $nextLPOrder = $LP->rank;

            // move 1 to a temporary rank
            Database::get()->query("UPDATE `lp_learnPath`
                    SET `rank` = '-1337'
                    WHERE `learnPath_id` = ?d
                    AND `course_id` = ?d", $thisLearningPathId, $course_id);

            // move 2 to the previous rank of 1
            Database::get()->query("UPDATE `lp_learnPath`
                     SET `rank` = ?d
                     WHERE `learnPath_id` = ?d
                     AND `course_id` = ?d", $thisLPOrder, $nextLPId, $course_id);

            // move 1 to previous rank of 2
            Database::get()->query("UPDATE `lp_learnPath`
                     SET `rank` = ?d
                     WHERE `learnPath_id` = ?d
                     AND `course_id` = ?d", $nextLPOrder, $thisLearningPathId, $course_id);
            break;
        }

        // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT
        if ($LP->learnPath_id == $thisLearningPathId) {
            $thisLPOrder = $LP->rank;
            $thisLPOrderFound = true;
        }
    }
}

// Display links to create and import a learning path
if ($is_editor) {
    if (isset($dialogBox)) {
        $tool_content .= disp_message_box($dialogBox, $style) . "<br />";
        draw($tool_content, 2, null, $head_content);
        exit;
    } else {
        $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div id='operations_container'>" .
                action_bar(array(
                    array('title' => $langCreate,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;cmd=create",
                        'icon' => 'fa-plus-circle',
                        'level' => 'primary-label',
                        'button-class' => 'btn-success'),
                    array('title' => $langimportLearningPathSort,
                        'url' => "importLearningPath.php?course=$course_code",
                        'icon' => 'fa-upload',
                        'level' => 'primary-label',
                        'button-class' => 'btn-success'),
                    array('title' => $langTrackAllPathExplanation,
                        'url' => "detailsAll.php?course=$course_code",
                        'icon' => 'fa-line-chart',
                        'level' => 'secondary'),
                    array('title' => $langLearningObjectsInUse_sort,
                        'url' => "modules_pool.php?course=$course_code",
                        'icon' => 'fa-book',
                        'level' => 'secondary'))) .
                "</div>
            </div>
        </div>";
    }
}

// check if there are learning paths available
$l = Database::get()->querySingle("SELECT COUNT(*) AS count FROM `lp_learnPath` WHERE `course_id` = ?d", $course_id)->count;
if ($l == 0) {
    $tool_content .= "<div class='alert alert-warning'>$langNoLearningPath</div>";
    draw($tool_content, 2, null, $head_content);
    exit();
}

$tool_content .= "
<div class='table-responsive'>    
    <table class='table-default'>
    <tr class='list-header'>
      <th><div align='left'>$langLearningPaths</div></th>\n";

if ($is_editor) {
    // Titles for teachers
    $tool_content .= "      <th><div align='center'>" . icon('fa-gears') . "</div></th>\n";
} elseif ($uid) {
    // display progression only if user is not teacher && not anonymous
    $tool_content .= "      <th colspan='2' width='50'><div align='center'>$langProgress</div></th>\n";
}
// close title line
$tool_content .= "    </tr>\n";

// display invisible learning paths only if user is courseAdmin
if ($is_editor) {
    $visibility = "";
} else {
    $visibility = " AND LP.`visible` = 1 ";
}
// check if user is anonymous
if ($uid) {
    $uidCheckString = "AND UMP.`user_id` = " . intval($uid);
} else { // anonymous
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

// list available learning paths
$sql = "SELECT LP.* , MIN(UMP.`raw`) AS minRaw, LP.`lock`
           FROM `lp_learnPath` AS LP
     LEFT JOIN `lp_rel_learnPath_module` AS LPM
            ON LPM.`learnPath_id` = LP.`learnPath_id`
     LEFT JOIN `lp_user_module_progress` AS UMP
            ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
            $uidCheckString
         WHERE 1=1
             $visibility
         AND LP.`course_id` = ?d
      GROUP BY LP.`learnPath_id`
      ORDER BY LP.`rank`";

$result = Database::get()->queryArray($sql, $course_id);

// used to know if the down array (for order) has to be displayed
$LPNumber = count($result);

$iterator = 1;

$is_blocked = false;
$allow = false;
$ind = 1;
foreach ($result as $list) { // while ... learning path list
    
    if ($list->visible == 0) {
        if ($is_editor) {
            $style = " class='not_visible'";
        } else {
            continue; // skip the display of this file
        }
    } else {
        if ($ind % 2 == 0) {
            $style = 'class="even"';
        } else {
            $style = 'class="odd"';
        }
    }

    //$is_blocked = $list->lock == 'CLOSE'? true : false;
    
    $tool_content .= "    <tr " . $style . ">";

    //Display current learning path name
    if (!$is_blocked) {
        // locate 1st module of current learning path
        $modulessql = "SELECT M.`module_id`
                FROM (`lp_module` AS M,
                      `lp_rel_learnPath_module` AS LPM)
                WHERE M.`module_id` = LPM.`module_id`
                  AND LPM.`learnPath_id` = ?d
                  AND M.`contentType` <> ?s
                  AND M.`course_id` = ?d
                ORDER BY LPM.`rank` ASC";
        $resultmodules = Database::get()->queryArray($modulessql, $list->learnPath_id, CTLABEL_, $course_id);
        
        $play_img = "<i class='fa fa-play-circle' style='font-size:20px;'></i>";
        
        if(!$is_editor){ // If is student
//            if ($list->lock == 'CLOSE'){ // If is student and LP is closed
//                $play_url = "<a href='javascript:void(0)' class='restrict_learn_path' data-toggle='modal' data-target='#restrictlp'>".htmlspecialchars($list->name)."</a>".intval($is_blocked);
//                if (count($resultmodules) > 0) { // If there are modules
//                    $play_button = "<i class='fa fa-minus-circle' style='font-size:20px';></i>";
//                } else {
//                    $play_button = $play_img;
//                }
//            } else { // If is student and LP is open
                $play_url = "<a href='learningPath.php?course=".$course_code."&amp;path_id=".$list->learnPath_id."'>" . htmlspecialchars($list->name) . "</a>";
                if (count($resultmodules) > 0) { // If there are modules
                    $play_button = "<a href='viewer.php?course=$course_code&amp;path_id=" . $list->learnPath_id . "&amp;module_id=" . $resultmodules[0]->module_id . "'>$play_img</a>";
                } else { // If there are no modules
                    $play_button = $play_img;
                }
            //}
        } else { // If is admin
//            if ($list->lock == 'CLOSE'){ // If is admin and LP is closed
//                $play_url = "<a href='learningPath.php?course=".$course_code."&amp;path_id=".$list->learnPath_id."'>" . htmlspecialchars($list->name) . "</a>";
//                if (count($resultmodules) > 0) { // If there are modules
//                    $play_button = "<a href='viewer.php?course=$course_code&amp;path_id=" . $list->learnPath_id . "&amp;module_id=" . $resultmodules[0]->module_id . "'><i class='fa fa-minus-circle text-danger' style='font-size:20px';></i>&nbsp;&nbsp;$play_img</a>";
//                } else {
//                    $play_button = $play_img;
//                }  
//            } else { // If is admin and LP is open
                $play_url = "<a href='learningPath.php?course=".$course_code."&amp;path_id=".$list->learnPath_id."'>" . htmlspecialchars($list->name) . "</a>";
                if (count($resultmodules) > 0) { // If there are modules
                    $play_button = "<a href='viewer.php?course=$course_code&amp;path_id=" . $list->learnPath_id . "&amp;module_id=" . $resultmodules[0]->module_id . "'>$play_img</a>";
                } else {
                    $play_button = $play_img;
                }
            //}
        }

        $tool_content .= "
      <td>$play_url<span class='pull-right'>$play_button</span></td>\n";

        // --------------TEST IF FOLLOWING PATH MUST BE BLOCKED------------------
        // ---------------------(MUST BE OPTIMIZED)------------------------------
        // step 1. find last visible module of the current learning path in DB

        $blocksql = "SELECT `learnPath_module_id`
                     FROM `lp_rel_learnPath_module`
                     WHERE `learnPath_id` = ?d
                     AND `visible` = 1
                     ORDER BY `rank` DESC
                     LIMIT 1";
        $resultblock = Database::get()->queryArray($blocksql, $list->learnPath_id);

        // step 2. see if there is a user progression in db concerning this module of the current learning path
        $number = count($resultblock);
        if ($number != 0) {
            $listblock = $resultblock[0];
            $blocksql2 = "SELECT `credit`
                          FROM `lp_user_module_progress`
                          WHERE `learnPath_module_id`= ?d
                          AND `learnPath_id` = ?d
                          AND `user_id` = ?d";
            $resultblock2 = Database::get()->queryArray($blocksql2, $listblock->learnPath_module_id, $list->learnPath_id, $uid);
            $moduleNumber = count($resultblock2);
        } else {
            $moduleNumber = 0;
        }
        
        //2.1 no progression found in DB
            if (($moduleNumber == 0) && ( isset($result[$ind]) && ($result[$ind]->lock == 'CLOSE'))) {
                //must block next path because last module of this path never tried!
                if ($uid) {
                    $is_blocked = true;
                } else { // anonymous : don't display the modules that are unreachable
                    $iterator++; // trick to avoid having the "no modules" msg to be displayed
                    break;
                }
            }

            //2.2. deal with progression found in DB if at leats one module in this path
            if ($moduleNumber != 0) {
                $listblock2 = $resultblock2[0];
                if (($listblock2->credit == "NO-CREDIT") && (isset($result[$ind]) && $result[$ind]->lock == 'CLOSE')) {
                    //must block next path because last module of this path not credited yet!
                    if ($uid) {
                        $is_blocked = true;
                    } else { // anonymous : don't display the modules that are unreachable
                        break;
                    }
                }
            }
        
    } else {  //else of !$is_blocked condition , we have already been blocked before, so we continue beeing blocked : we don't display any links to next paths any longer
        if(!$is_editor){
            $tool_content .= "<td><a href='javascript:void(0)' class='restrict_learn_path' data-toggle='modal' data-target='#restrictlp'>".htmlspecialchars($list->name)."</a>"/* .$list['minRaw'] */ . "<span class='pull-right'><i class='fa fa-minus-circle' style='font-size:20px';></i></span></td>\n";
        } else { // if is editor he can access the learning path even if it is restricted
            $tool_content .=  "<td><a href='learningPath.php?course=".$course_code."&amp;path_id=".$list->learnPath_id."'>" . htmlspecialchars($list->name) . "</a><span class='pull-right'><i class='fa fa-minus-circle' style='font-size:20px';></i>&nbsp;&nbsp;$play_button</span></td>\n";
        }
    }

    // DISPLAY ADMIN LINK-----------------------------------------------------------
    if ($is_editor) {
        // 5 administration columns
        // LOCK link


        $is_real_dir = is_dir(realpath($webDir . "/courses/" . $course_code . "/scormPackages/path_" . $list->learnPath_id));

        $tool_content .= "      <td class='option-btn-cell'>" .
                action_button(array(
                    array('title' => $langEditChange,
                        'url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . $list->learnPath_id,
                        'icon' => 'fa-edit'),
                    // VISIBILITY link
                    array('title' => !$list->visible == 0? $langViewHide : $langViewShow,
                        'url' => !$list->visible == 0? $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;cmd=mkInvisibl&amp;visibility_path_id=" . $list->learnPath_id : $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;cmd=mkVisibl&amp;visibility_path_id=" . $list->learnPath_id,
                        'icon' => !$list->visible == 0? 'fa-eye-slash': 'fa-eye'),
                    array('title' => $list->lock == 'OPEN'? $langBlock : $langNoBlock,
                        'url' => $list->lock == 'OPEN'? $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;cmd=mkBlock&amp;cmdid=" . $list->learnPath_id : $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;cmd=mkUnblock&amp;cmdid=" . $list->learnPath_id,
                        'icon' => $list->lock == 'OPEN'? 'fa-minus-circle' : 'fa-play-circle',
                        'show' => !($ind == 1)),
                    array('title' => $langUp,
                        'level' => 'primary',
                        'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;cmd=moveUp&amp;move_path_id=" . $list->learnPath_id,
                        'icon' => 'fa-arrow-up',
                        'disabled' => $iterator == 1),
                    array('title' => $langDown,
                        'level' => 'primary',
                        'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;cmd=moveDown&amp;move_path_id=" . $list->learnPath_id,
                        'icon' => 'fa-arrow-down',
                        'disabled' => $iterator >= $LPNumber),
                    array('title' => $langTracking,
                        'url' => "details.php?course=$course_code&amp;path_id=" . $list->learnPath_id,
                        'icon' => 'fa-line-chart'),
                    array('title' => $langExport2004,
                        'url' => $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=export&amp;path_id=' . $list->learnPath_id,
                        'icon' => 'fa-download'),
                    array('title' => $langExport12,
                        'url' => $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=export12&amp;path_id=' . $list->learnPath_id,
                        'icon' => 'fa-download'),
                    array('title' => $langExportIMSCP,
                        'url' => $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=exportIMSCP&amp;path_id=' . $list->learnPath_id,
                        'icon' => 'fa-download'),
                    array('title' => $langDelete,
                        'url' => $_SERVER['SCRIPT_NAME'] . "?course=$course_code&amp;cmd=delete&amp;del_path_id=" . $list->learnPath_id,
                        'icon' => 'fa-times',
                        'class' => 'delete',
                        'confirm' => $is_real_dir ? ($langAreYouSureToDeleteScorm . " \"" . $list->name)."\"" : $langDelete)               
                )) .
                "</td>\n";
    } elseif ($uid) {
        // % progress
        $prog = get_learnPath_progress($list->learnPath_id, $uid);
        if (!isset($globalprog)) {
            $globalprog = 0;
        }
        if ($prog >= 0) {
            $globalprog += $prog;
        }
        $tool_content .= "<td class='text-right' width='120'>" . disp_progress_bar($prog, 1) . "</td>\n";
    }
    $tool_content .= "</tr>\n";
    $iterator++;
    $ind++;
} // end while

if (!$is_editor && $iterator != 1 && $uid) {
    // add a blank line between module progression and global progression
    $total = round($globalprog / ($iterator - 1));
    $tool_content .= "
    <tr>
      <th><div align='right'><b>$langPathsInCourseProg</b>:</div></th>
      <th><div align='right'>" . disp_progress_bar($total, 1) . "</div></th>
    </tr>\n";
}
$tool_content .= "\n     </table></div>\n";
$tool_content .= "<div class='modal fade' id='restrictlp' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-body'>".
        $langRestrictedLPath
      ."</div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
      </div>
    </div>
  </div>
</div>";

draw($tool_content, 2, null, $head_content);
