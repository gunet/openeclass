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
  viewer_toc.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: navigation/tableOfContent.php Revision: 1.30

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
  @Description: Script for displaying a navigation bar to the users when
  they are browsing a learning path

  @Comments:
  ==============================================================================
 */

$require_current_course = TRUE;
require_once '../../include/init.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
// The following is added for statistics purposes
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_LP);
/* * *********************************** */

//  set redirection link
$returl = "navigation/viewModule.php?course=$course_code&amp;go=" .
        ($is_editor ? 'learningPathAdmin' : 'learningPath');

if ($uid) {
    $uidCheckString = "AND UMP.`user_id` = $uid";
} else { // anonymous
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

// get the list of available modules
$sql = "SELECT LPM.`learnPath_module_id` ,
	LPM.`parent`,
	LPM.`lock`,
          M.`module_id`,
          M.`contentType`,
          M.`name`,
        UMP.`lesson_status`, UMP.`raw`,
        UMP.`scoreMax`, UMP.`credit`,
          A.`path`
       FROM (`lp_rel_learnPath_module` AS LPM,
             `lp_module` AS M)
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
    ORDER BY LPM.`rank`";
$moduleList = Database::get()->queryArray($sql, $_SESSION['path_id'], $course_id);

$extendedList = array();
$modar = array();
foreach ($moduleList as $module) {
    $modar['name'] = $module->name;
    $modar['contentType'] = $module->contentType;
    $modar['learnPath_module_id'] = $module->learnPath_module_id;
    $modar['parent'] = $module->parent;
    $modar['path'] = $module->path;
    $modar['lock'] = $module->lock;
    $modar['module_id'] = $module->module_id;
    $modar['lesson_status'] = $module->lesson_status;
    $modar['raw'] = $module->raw;
    $modar['scoreMax'] = $module->scoreMax;
    $modar['credit'] = $module->credit;
    $extendedList[] = $modar;
}

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$is_blocked = false;
$moduleNb = 0;

// get the name of the learning path
$sql = "SELECT `name`
          FROM `lp_learnPath`
         WHERE `learnPath_id` = ?d
           AND `course_id` = ?d";
$lpName = Database::get()->querySingle($sql, $_SESSION['path_id'], $course_id)->name;

$previous = ""; // temp id of previous module, used as a buffer in foreach
$previousModule = ""; // module id that will be used in the previous link
$nextModule = ""; // module id that will be used in the next link

foreach ($flatElementList as $module) {
    // spacing col
    if (!$is_blocked or $is_editor) {
        if ($module['contentType'] != CTLABEL_) { // chapter head
            // bold the title of the current displayed module
            if ($_SESSION['lp_module_id'] == $module['module_id']) {
                $previousModule = $previous;
            }
            // store next value if user has the right to access it
            if ($previous == $_SESSION['lp_module_id']) {
                $nextModule = $module['module_id'];
            }
        }
        // a module ALLOW access to the following modules if
        // document module : credit == CREDIT || lesson_status == 'completed'
        // exercise module : credit == CREDIT || lesson_status == 'passed'
        // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

        if (($module['lock'] == 'CLOSE')
                && ( $module['credit'] != 'CREDIT'
                || ( $module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED'))) {
            $is_blocked = true; // following modules will be unlinked
        }
    }

    if ($module['contentType'] != CTLABEL_) {
        $moduleNb++; // increment number of modules used to compute global progression except if the module is a title
    }

// used in the foreach the remember the id of the previous module_id
    // don't remember if label...
    if ($module['contentType'] != CTLABEL_) {
        $previous = $module['module_id'];
    }
} // end of foreach ($flatElementList as $module)

$prevNextString = "";
// display previous and next links only if there is more than one module
if ($moduleNb > 1) {
    $imgPrevious = '<img src="' . $themeimg . '/lp/back.png" alt="' . $langPrevious . '" title="' . $langPrevious . '">';
    $imgNext = '<img src="' . $themeimg . '/lp/next.png" alt="' . $langNext . '" title="' . $langNext . '">';

    if ($previousModule != '') {
        $prevNextString .= '<a href="navigation/viewModule.php?course=' . $course_code . '&amp;viewModule_id=' . $previousModule . '" target="scoFrame">' . $imgPrevious . '</a>';
    } else {
        $prevNextString .= $imgPrevious;
    }
    $prevNextString .= '&nbsp;';

    if ($nextModule != '') {
        $prevNextString .= '<a href="navigation/viewModule.php?course=' . $course_code . '&amp;viewModule_id=' . $nextModule . '" target="scoFrame">' . $imgNext . '</a>';
    } else {
        $prevNextString .= $imgNext;
    }
}

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html>
<head><title>-</title>
    <meta http-equiv='Content-Type' content='text/html; charset=$charset'>
    <link href='{$urlAppend}template/$theme/lp.css' rel='stylesheet' type='text/css' />
</head>
<body>
<div class='header'>
    <div class='tools'>
    <div class='lp_right'>$prevNextString&nbsp;<a href='$returl' target='_top'>
        <img src='$themeimg/lp/nofullscreen.png' alt='$langQuitViewer' title='$langQuitViewer' /></a></div>
    <div class='lp_left'>
        <a href='{$urlAppend}courses/$course_code' target='_top' title='" .
 q($currentCourseName) . "'>" . q(ellipsize($currentCourseName, 35)) . "</a> &#187;
        <a href='{$urlAppend}modules/learnPath/index.php?course=$course_code' target='_top'>
                $langLearningPaths</a> &#187;
        <a href='$returl' title='" . q($lpName) . "' target='_top'>" . q(ellipsize($lpName, 40)) . "</a></div>
    <div class='clear'></div>
    <div class='logo'><img src='$themeimg/lp/logo_openeclass.png' alt='' title='' /></div>
    <div class='lp_right_grey'>";
if ($uid) {
    $lpProgress = get_learnPath_progress((int) $_SESSION['path_id'], $uid);
    echo $langProgress . ': ' . disp_progress_bar($lpProgress, 1) . "&nbsp;" . $lpProgress . "%";
}
echo "</div></div></div></body></html>";
