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
  detailsAll.php
  @last update: 05-12-2006 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: tracking/learnPath_detailsAllPath.php Revision: 1.11

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Gioacchino Poletto <info@polettogioacchino.com>
  ==============================================================================
  @Description: This script displays the stats of all users of a course
  for his progression into the sum of all learning paths of
  the course

  @Comments:

  @todo:
  ==============================================================================
 */

$require_current_course = TRUE;
$require_editor = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'modules/usage/statistics_tools_bar.php';

if (isset($_GET['from_stats']) and $_GET['from_stats'] == 1) { // if we come from statistics
    $toolName = $langUsage;
    $navigation[] = array('url' => '../usage/?course=' . $course_code, 'name' => $langUsage);
    $pageName = "$langLearningPaths - $langTrackAllPathExplanation";
} else {
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
    $pageName = $langTrackAllPathExplanation;
}

// display a list of user and their respective progress
$sql = "SELECT U.`surname`, U.`givenname`, U.`id`
	FROM `user` AS U, `course_user` AS CU
	WHERE U.`id`= CU.`user_id`
	AND CU.`course_id` = $course_id
	ORDER BY U.`surname` ASC";

@$tool_content .= get_limited_page_links($sql, 30, $langPreviousPage, $langNextPage);
$usersList = get_limited_list($sql, 30);

if (isset($_GET['from_stats']) and $_GET['from_stats'] == 1) { // if we come from statistics
    statistics_tools($course_code, "detailsAll", "../usage/");
}

$tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "index.php",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')),false); 

// check if there are learning paths available
$lcnt = Database::get()->querySingle("SELECT COUNT(*) AS count FROM lp_learnPath WHERE course_id = ?d", $course_id)->count;
if ($lcnt == 0) {
    $tool_content .= "<div class='alert alert-warning'>$langNoLearningPath</div>";
    draw($tool_content, 2, null, $head_content);
    exit;
} else {
    $tool_content .= "<div class='alert alert-info'>
           <b>$langSave $langDumpUserDurationToFile: </b><a href='dumpuserlearnpathdetails.php?course=$course_code'>$langcsvenc2</a>&nbsp;
                $langOr&nbsp; <a href='dumpuserlearnpathdetails.php?course=$course_code&amp;enc=1253'>$langcsvenc1</a>          
          </div>";
}

// display tab header
$tool_content .= "
  <div class='table-responsive'>
  <table class='table-default'>
  <tr class='list-header text'>
    <th>$langStudent</th>
    <th width='120'>$langAm</th>
    <th>$langGroup</th>
    <th>$langProgress</th>
  </tr>\n";


// display tab content
$k = 0;
foreach ($usersList as $user) {
    // list available learning paths
    $learningPathList = Database::get()->queryArray("SELECT learnPath_id FROM lp_learnPath WHERE course_id = ?d", $course_id);

    $iterator = 1;
    $globalprog = 0;
    
    $tool_content .= "  <tr>";
    foreach ($learningPathList as $learningPath) {
        // % progress
        $prog = get_learnPath_progress($learningPath->learnPath_id, $user->id);
        if ($prog >= 0) {
            $globalprog += $prog;
        }
        $iterator++;
    }
    $total = round($globalprog / ($iterator - 1));
    $tool_content .= 
            '    <td><a href="detailsUser.php?course=' . $course_code . '&amp;uInfo=' . $user->id . '&amp;uName=' . $user->givenname . '">' .  profile_image($user->id, IMAGESIZE_SMALL, 'img-circle')."&nbsp;". q($user->surname) . ' ' . q($user->givenname) . '</a></td>'
            . '    <td class="text-center">' . q(uid_to_am($user->id)) . '</td>'
            . '    <td class="text-left">' . user_groups($course_id, $user->id) . '</td>'
            . '    <td class="text-right" width=\'120\'>'
            . disp_progress_bar($total, 1)
            . '</td>'
            . '</tr>';
    $k++;
}
// foot of table
$tool_content .= '</table></div>';

draw($tool_content, 2, null, $head_content);
