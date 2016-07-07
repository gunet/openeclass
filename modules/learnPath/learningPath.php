<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
  learningPath.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: learningPath.php Revision: 1.30

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
  @Description: This script displays the contents of a learning path to
  a user and his progress. If the user is anonymous the
  progress is not displayed at all.

  @Comments:
  ==============================================================================
 */

$require_current_course = TRUE;

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_LP);
/* * *********************************** */

if (isset($_GET['unit'])) {
    $_SESSION['unit'] = intval($_GET['unit']);
}

// $_SESSION
if (isset($_GET['path_id'])) {
    $_SESSION['path_id'] = intval($_GET['path_id']);
} elseif ((!isset($_SESSION['path_id']) || $_SESSION['path_id'] == '')) {
    // if path id not set, redirect user to the list of learning paths
    header("Location: ./index.php?course=$course_code");
    exit();
}

$lp = Database::get()->querySingle("SELECT name, visible FROM lp_learnPath WHERE learnPath_id = ?d AND `course_id` = ?d", $_SESSION['path_id'], $course_id);
$pageName = $lp->name;
$toolName = $langLearningPaths;
if (!add_units_navigation(TRUE)) {
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
}


// permissions (only for the viewmode, there is nothing to edit here )
if ($is_editor) {
        // if the fct return true it means that user is a course manager and than view mode is set to COURSE_ADMIN
        header("Location: ./learningPathAdmin.php?course=$course_code&path_id=" . $_SESSION['path_id']);
        exit();
} else {
        if ($lp->visible == 0) {
            // if the learning path is invisible, don't allow users in it
            header("Location: ./index.php?course=$course_code");
            exit();
        }

        // check for blocked learning path        
	$lps = Database::get()->querySingle("SELECT `learnPath_id`, `rank` FROM lp_learnPath 
                            WHERE learnPath_id = ?d AND course_id = $course_id ORDER BY `rank`", $_SESSION['path_id']);
        $lpaths = Database::get()->queryArray("SELECT `learnPath_id`, `lock` FROM lp_learnPath WHERE course_id = ?d AND `rank` < ?d", $course_id, $lps->rank);
        foreach ($lpaths as $lp) {
                if ($lp->lock == 'CLOSE') {
                    $prog = get_learnPath_progress($lp->learnPath_id, $_SESSION['uid']);                    
                    if ($prog != 0) {                                                    
                        header("Location: ./index.php?course=$course_code");
                    }
                }
        }
}

// main page
if ($uid) {
    $uidCheckString = "AND UMP.`user_id` = " . intval($uid);
} else { // anonymous
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

$sql = "SELECT 
    MIN(LPM.`learnPath_module_id`) as learnPath_module_id,
    MIN(LPM.`parent`) as parent,
	MIN(LPM.`lock`) as `lock`, 
	MIN(M.`module_id`) as module_id,
	MIN(M.`contentType`) as contentType,
	MIN(M.`name`) as name,
	MIN(UMP.`lesson_status`) as lesson_status,
	MIN(UMP.`raw`) as raw,
	MIN(UMP.`scoreMax`) as scoreMax,
	MIN(UMP.`credit`) as credit,
	MIN(A.`path`) as path
        FROM (`lp_module` AS M,
	`lp_rel_learnPath_module` AS LPM)
     LEFT JOIN `lp_user_module_progress` AS UMP
             ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
             " . $uidCheckString . "
     LEFT JOIN `lp_asset` AS A
            ON M.`startAsset_id` = A.`asset_id`
          WHERE LPM.`module_id` = M.`module_id`
            AND LPM.`learnPath_id` = ?d
            AND LPM.`visible` = 1
            AND LPM.`module_id` = M.`module_id`
            AND M.`course_id` = ?d
       GROUP BY LPM.`module_id`
       ORDER BY MIN(LPM.`rank`)";

$fetchedList = Database::get()->queryArray($sql, $_SESSION['path_id'], $course_id);

$tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "index.php?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            )
        ));

if (count($fetchedList) == 0) {
    $tool_content .= "<div class='alert alert-warning'>$langNoModule</div>";
    add_units_navigation();
    draw($tool_content, 2);
    exit();
}

$extendedList = array();
$modar = array();
foreach ($fetchedList as $module) {        
    $modar['learnPath_module_id'] = $module->learnPath_module_id;
    $modar['parent'] = $module->parent;
    $modar['lock'] = $module->lock;
    $modar['module_id'] = $module->module_id;
    $modar['contentType'] = $module->contentType;
    if (empty($module->name) and $module->contentType == 'LINK') {
        $modar['name'] = $module->path;
    } else {
        $modar['name'] = $module->name;    
    }
    $modar['lesson_status'] = $module->lesson_status;
    $modar['raw'] = $module->raw;
    $modar['scoreMax'] = $module->scoreMax;
    $modar['credit'] = $module->credit;
    $modar['path'] = $module->path;
    $extendedList[] = $modar;
}

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$is_blocked = false;
$first_blocked = false;
$moduleNb = 0;

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for ($i = 0; $i < sizeof($flatElementList); $i++) {
    if ($flatElementList[$i]['children'] > $maxDeep) {
        $maxDeep = $flatElementList[$i]['children'];
    }
}

/* ================================================================
  OUTPUT STARTS HERE
  ================================================================ */

// comment
if (commentBox(LEARNINGPATH_, DISPLAY_)) {
    $tool_content .= "
        <div class='row'>
            <div class='col-xs-12'>"
                . commentBox(LEARNINGPATH_, DISPLAY_) .
            "</div>
        </div>
    ";
}

// --------------------------- module table header --------------------------
$tool_content .= "<div class='table-responsive'>";
$tool_content .= "<table class='table-default'>";
$tool_content .= "<tr class='list-header'><th colspan=\"" . ($maxDeep + 2) . "\"><div align=\"left\">&nbsp;&nbsp;<b>" . $langLearningObjects . "</b></div></th>\n";


// show only progress column for authenticated users
if ($uid) {
    $tool_content .= '<th><b>' . $langProgress . '</b></th>' . "\n";
}

$tool_content .= "</tr>\n";

// ------------------ module table list display -----------------------------------
if (!isset($globalProg)) {
    $globalProg = 0;
}

foreach ($flatElementList as $module) {

    if ($module['scoreMax'] > 0 && $module['raw'] > 0) {
        $progress = round($module['raw'] / $module['scoreMax'] * 100);
    } else {
        $progress = 0;
    }

    if ($module['contentType'] == CTEXERCISE_) {
        $passExercise = ($module['credit'] == "CREDIT");
    } else {
        $passExercise = false;
    }

    if ($module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0) {
        if ($module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED') {
            $progress = 100;
            $passExercise = true;
        } else {
            $progress = 0;
            $passExercise = false;
        }
    }

    // display the current module name (and link if allowed)
    $spacingString = "";
    for ($i = 0; $i < $module['children']; $i++) {
        $spacingString .= "<td width='5'>&nbsp;</td>";
    }

    $colspan = ($maxDeep - $module['children'] + 1)+1;
//    if ($module['contentType'] == CTLABEL_) {
//        $colspan++;
//    }

    $tool_content .= "<tr>" . $spacingString . "
      <td colspan=\"" . $colspan . "\" align='left'>";

    //-- if chapter head
    if ($module['contentType'] == CTLABEL_) {
        $tool_content .= '<b>' . htmlspecialchars($module['name']) . '</b>' . "";
    }
    //-- if user can access module
    elseif (!$is_blocked) {
        if ($module['contentType'] == CTEXERCISE_) {
            $moduleImg = 'fa-pencil-square-o';
        } else if ($module['contentType'] == CTLINK_) {
            $moduleImg = "fa-link";
        } else if ($module['contentType'] == CTCOURSE_DESCRIPTION_) {
            $moduleImg = "fa-info-circle";
        } else if ($module['contentType'] == CTMEDIA_ || $module['contentType'] == CTMEDIALINK_) {
            $moduleImg = "fa-film";
        } else {
            $moduleImg = choose_image(basename($module['path']));
        }

        $contentType_alt = selectAlt($module['contentType']);
        $tool_content .= '<span style="vertical-align: middle;">' .
                icon($moduleImg, $contentType_alt) . '</span>&nbsp;' .
                '<a href="viewer.php?course=' . $course_code . '&amp;path_id=' . (int) $_SESSION['path_id'] . '&amp;module_id=' . $module['module_id'] . '">' . htmlspecialchars($module['name']) . '</a>';
        // a module ALLOW access to the following modules if
        // document module : credit == CREDIT || lesson_status == 'completed'
        // exercise module : credit == CREDIT || lesson_status == 'passed'
        // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

        if (($module['lock'] == 'CLOSE') 
            and ($module['credit'] != 'CREDIT' 
            or ($module['lesson_status'] != 'COMPLETED' and $module['lesson_status'] != 'PASSED')))
            {
                    $is_blocked = true; // following modules will be unlinked
            }
    }
    //-- user is blocked by previous module, don't display link
    else {
        if ($module['contentType'] == CTEXERCISE_) {
            $moduleImg = 'fa-pencil-square-o';
        } else if ($module['contentType'] == CTLINK_) {
            $moduleImg = "fa-link";
        } else if ($module['contentType'] == CTCOURSE_DESCRIPTION_) {
            $moduleImg = "fa-info-circle";
        } else if ($module['contentType'] == CTMEDIA_ || $module['contentType'] == CTMEDIALINK_) {
            $moduleImg = "fa-film";
        } else {
            $moduleImg = choose_image(basename($module['path']));
        }

        $tool_content .= '<span style="vertical-align: middle;">' . icon($moduleImg, $contentType_alt) . '</span> ' .
            htmlspecialchars($module['name']);
    }
    $tool_content .= "</td>";

    if ($uid && ($module['contentType'] != CTLABEL_)) {
        // display actions for current module (taking into consideration blocked modules)
//        if (!$is_blocked || !$first_blocked) {
//            $tool_content .= "<td width='18'><a href=\"module.php?course=$course_code&amp;module_id=" . $module['module_id'] . "\">" . icon('fa-line-chart', $langLearningObjectData) . "</a></td>";
//        } else {
//            $tool_content .= "<td></td>";
//        }
//        if ($is_blocked) {
//            $first_blocked = true;
//        }
        // display the progress value for current module
        $tool_content .= '<td align="right" width="120">' . disp_progress_bar($progress, 1) . '</td>' . "\n";
    }
    elseif ($uid && $module['contentType'] == CTLABEL_) {
        $tool_content .= '<td>&nbsp;</td>' . "\n";
    }

    if ($progress > 0) {
        $globalProg = $globalProg + $progress;
    }

    if ($module['contentType'] != CTLABEL_) {
        $moduleNb++; // increment number of modules used to compute global progression except if the module is a title
    }

    $tool_content .= "</tr>";
}


if ($uid && $moduleNb > 0) {
    // add a blank line between module progression and global progression
    $tool_content .= '<tr class="odd">' . "\n"
            . '<th colspan="' . ($maxDeep + 2) . '" align="right"><div align="right">' . $langGlobalProgress . '</div></th>' . "\n"
            . '<th align="right" width="120"><div align="right">'
            . disp_progress_bar(round($globalProg / ($moduleNb)), 1)
            . '</div></th>' . "\n"
            . '</tr>' . "";
}
$tool_content .= "</table></div>";
draw($tool_content, 2);
