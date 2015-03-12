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
  detailsUserPath.php
  @last update: 30-06-2006 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: tracking/lp_modules_details.php Revision: 1.20
  ==============================================================================
  @Description: This script presents the student's progress for a learning
  path to the teacher.

  @Comments:

  @todo:
  ==============================================================================
 */

$require_current_course = TRUE;
$require_editor = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
$navigation[] = array("url" => "details.php?course=$course_code&amp;path_id=" . $_REQUEST['path_id'], "name" => $langStatsOfLearnPath);
$pageName = $langTrackUser;

if (empty($_REQUEST['uInfo']) || empty($_REQUEST['path_id'])) {
    header("Location: ./index.php?course=$course_code");
    exit();
}

// get infos about the user
$uDetails = Database::get()->querySingle("SELECT surname, givenname, email 
    FROM `user`
    WHERE id = ?d", $_REQUEST['uInfo']);

// get infos about the learningPath
$LPname = Database::get()->querySingle("SELECT `name`
        FROM `lp_learnPath`
        WHERE `learnPath_id` = ?d
        AND `course_id` = ?d", $_REQUEST['path_id'], $course_id)->name;

//### PREPARE LIST OF ELEMENTS TO DISPLAY #################################
$sql = "SELECT LPM.`learnPath_module_id`, LPM.`parent`,
	LPM.`lock`, M.`module_id`,
	M.`contentType`, M.`name`,
	UMP.`lesson_status`, UMP.`raw`,
	UMP.`scoreMax`, UMP.`credit`,
	UMP.`session_time`, UMP.`total_time`, A.`path`
	FROM (`lp_rel_learnPath_module` AS LPM, `lp_module` AS M)
	LEFT JOIN `lp_user_module_progress` AS UMP
		ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
		AND UMP.`user_id` = ?d
	LEFT JOIN `lp_asset` AS A
		ON M.`startAsset_id` = A.`asset_id`
	WHERE LPM.`module_id` = M.`module_id`
		AND LPM.`learnPath_id` = ?d
		AND LPM.`visible` = 1
		AND LPM.`module_id` = M.`module_id`
		AND M.`course_id` = ?d
	GROUP BY LPM.`module_id`
	ORDER BY LPM.`rank`";
$moduleList = Database::get()->queryArray($sql, $_REQUEST['uInfo'], $_REQUEST['path_id'], $course_id);

$extendedList = array();
$modar = array();
foreach ($moduleList as $module) {
    $modar['learnPath_module_id'] = $module->learnPath_module_id;
    $modar['parent'] = $module->parent;
    $modar['lock'] = $module->lock;
    $modar['module_id'] = $module->module_id;
    $modar['contentType'] = $module->contentType;
    $modar['name'] = $module->name;
    $modar['lesson_status'] = $module->lesson_status;
    $modar['raw'] = $module->raw;
    $modar['scoreMax'] = $module->scoreMax;
    $modar['credit'] = $module->credit;
    $modar['session_time'] = $module->session_time;
    $modar['total_time'] = $module->total_time;
    $modar['path'] = $module->path;
    $extendedList[] = $modar;
}

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$moduleNb = 0;
$globalProg = 0;
$global_time = "0000:00:00";

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for ($i = 0; $i < sizeof($flatElementList); $i++) {
    if ($flatElementList[$i]['children'] > $maxDeep) {
        $maxDeep = $flatElementList[$i]['children'];
    }
}

$tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "detailsUser.php?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')),false);

$tool_content .= "
        <div class='row margin-bottom-thin'>
            <div class='col-xs-12'>
                <div>
                    <b>$langLearnPath:</b> <span class='text-muted'>$LPname</span>
                </div>
                <div>
                    <b>$langStudent:</b> <span class='text-muted'>".q($uDetails->surname) . "&nbsp;" . q($uDetails->givenname) . " (" . q($uDetails->email).")</span>
                </div>
            </div>
        </div>
        ";
// -------------------- table header ----------------------------
$tool_content .= '<div class="table-responsive">
    <table class="table-default">' . "\n"
        // ------------------- some user details --------------------------
        . '    <tr class="list-header">' . "\n"
        . '      <th colspan="' . ($maxDeep + 1) . '">' . $langLearningObjects . '</th>' . "\n"
        . '      <th>' . $langLastSessionTimeSpent . '</th>' . "\n"
        . '      <th>' . $langTotalTimeSpent . '</th>' . "\n"
        . '      <th>' . $langLessonStatus . '</th>' . "\n"
        . '      <th>' . $langProgress . '</th>' . "\n"
        . '    </tr>' . "\n";

// ---------------- display list of elements ------------------------
foreach ($flatElementList as $module) {
    if ($module['scoreMax'] > 0) {
        $progress = @round($module['raw'] / $module['scoreMax'] * 100);
    } else {
        $progress = 0;
    }

    if ($module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0) {
        if ($module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED') {
            $progress = 100;
        } else {
            $progress = 0;
        }
    }

    // display the current module name
    $spacingString = '';
    for ($i = 0; $i < $module['children']; $i++) {
        $spacingString .= '      <td width="5">&nbsp;</td>' . "\n";
    }
    $colspan = $maxDeep - $module['children'] + 1;

    $tool_content .= '    <tr>' . "\n" . $spacingString . '      <td colspan="' . $colspan . '" align="left">';
    //-- if chapter head
    if ($module['contentType'] == CTLABEL_) {
        $tool_content .= '      <b>' . q($module['name']) . '</b>';
    }
    //-- if user can access module
    else {
        if ($module['contentType'] == CTEXERCISE_) {
            $moduleImg = 'fa-pencil-square-o';
        } elseif ($module['contentType'] == CTLINK_) {
            $moduleImg = 'fa-link';
        } elseif ($module['contentType'] == CTCOURSE_DESCRIPTION_) {
            $moduleImg = 'fa-info-circle';
        } elseif ($module['contentType'] == CTMEDIA_ or $module['contentType'] == CTMEDIALINK_) {
            $moduleImg = 'fa-film';
        } else {
            $moduleImg = choose_image(basename($module['path']));
        }
        $contentType_alt = selectAlt($module['contentType']);
        $tool_content .= icon($moduleImg, $contentType_alt) . q($module['name']) . '</small>';
    }

    $tool_content .= '</td>' . "\n";

    if ($module['contentType'] == CTSCORM_) {
        $session_time = preg_replace("/\.[0-9]{0,2}/", "", $module['session_time']);
        $total_time = preg_replace("/\.[0-9]{0,2}/", "", $module['total_time']);
        $global_time = addScormTime($global_time, $total_time);
    } elseif ($module['contentType'] == CTLABEL_ || $module['contentType'] == CTEXERCISE_) {
        $session_time = $module['session_time'];
        $total_time = $module['total_time'];
    } else {
        // if no progression has been recorded for this module
        // leave
        if ($module['lesson_status'] == "") {
            $session_time = "&nbsp;";
            $total_time = "&nbsp;";
        } else { // columns are n/a
            $session_time = "-";
            $total_time = "-";
        }
    }
    //-- session_time
    $tool_content .= '      <td>' . $session_time . '</td>' . "\n";
    //-- total_time
    $tool_content .= '      <td>' . $total_time . '</td>' . "\n";
    //-- status
    $tool_content .= '      <td>';
    if ($module['contentType'] == CTEXERCISE_ && $module['lesson_status'] != "") {
        if ($module['lesson_status'] == "NOT ATTEMPTED") {
            $tool_content .= $langNotAttempted;
        } else if ($module['lesson_status'] == "PASSED") {
            $tool_content .= $langPassed;
        } else if ($module['lesson_status'] == "FAILED") {
            $tool_content .= $langFailed;
        } else if ($module['lesson_status'] == "COMPLETED") {
            $tool_content .= $langAlreadyBrowsed;
        } else if ($module['lesson_status'] == "BROWSED") {
            $tool_content .= $langAlreadyBrowsed;
        } else if ($module['lesson_status'] == "INCOMPLETE") {
            $tool_content .= $langNeverBrowsed;
        } else {
            $tool_content .= strtolower($module['lesson_status']);
        }
    } else {
        if ($module['lesson_status'] == "NOT ATTEMPTED") {
            $tool_content .= $langNotAttempted;
        } else if ($module['lesson_status'] == "PASSED") {
            $tool_content .= $langPassed;
        } else if ($module['lesson_status'] == "FAILED") {
            $tool_content .= $langFailed;
        } else if ($module['lesson_status'] == "COMPLETED") {
            $tool_content .= $langAlreadyBrowsed;
        } else if ($module['lesson_status'] == "BROWSED") {
            $tool_content .= $langAlreadyBrowsed;
        } else if ($module['lesson_status'] == "INCOMPLETE") {
            $tool_content .= $langNeverBrowsed;
        } else {
            $tool_content .= strtolower($module['lesson_status']);
        }
    }
    $tool_content .= '</td>' . "\n";
    //-- progression
    if ($module['contentType'] != CTLABEL_) {
        // display the progress value for current module
        $tool_content .= '<td align="right" width="120">' . disp_progress_bar($progress, 1) . '</td>' . "\n";
    } else { // label
        $tool_content .= '      <td>&nbsp;</td>' . "\n";
    }

    if ($progress > 0) {
        $globalProg += $progress;
    }
    if ($module['contentType'] != CTLABEL_) {
        $moduleNb++; // increment number of modules used to compute global progression except if the module is a title
    }

    $tool_content .= '    </tr>' . "\n";
}

if ($moduleNb == 0) {
    $tool_content .= '    <tr class="odd">' . "\n" . '<td align="center" colspan="5">' . $langNoModule . '</td>' . "\n" . '    </tr>' . "\n";
} elseif ($moduleNb > 0) {
    // display global stats
    $tool_content .= '    <tr class="odd">' . "\n"
            . '      <th colspan="' . ($maxDeep + 1) . '">&nbsp;</th>' . "\n"
            . '      <th align="right">' . (($global_time != "0000:00:00") ? $langTimeInLearnPath : '&nbsp;') . '</th>' . "\n"
            . '      <th align="center">' . (($global_time != "0000:00:00") ? preg_replace("/\.[0-9]{0,2}/", "", $global_time) : '&nbsp;') . '</th>' . "\n"
            . '<th align="right"><small>' . $langGlobalProgress . '</small></th>' . "\n"
            . '<th align="right">'
            . disp_progress_bar(round($globalProg / ($moduleNb)), 1)
            . '</th>' . "\n"
            . '    </tr>';
}
$tool_content .= "\n</table></div>\n";

draw($tool_content, 2, null, $head_content);

