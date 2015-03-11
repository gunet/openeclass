<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
  detailsUser.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: tracking/userLog.php Revision: 1.37

  Claroline authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
  Hugues Peeters    <peeters@ipm.ucl.ac.be>
  Christophe Gesche <gesche@ipm.ucl.ac.be>
  Sebastien Piraux  <piraux_seb@hotmail.com>
  ==============================================================================
  @Description: This script presents the student's progress for all
  learning paths available in a course to the teacher.

  Only the Learning Path specific code was ported and
  modified from the original claroline file.

  @Comments:

  @todo:
  ==============================================================================
 */

$require_current_course = TRUE;
$require_editor = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langLearningPaths);
$navigation[] = array('url' => "detailsAll.php?course=$course_code", 'name' => $langTrackAllPathExplanation);
$toolName = $langTrackUser;

// user info can not be empty, return to the list of details
if (empty($_REQUEST['uInfo'])) {
    header("Location: ./detailsAll.php?course=$course_code");
    exit();
}

// check if user is in this course
$rescnt = Database::get()->querySingle("SELECT COUNT(*) AS count
            FROM `course_user` as `cu` , `user` as `u`
            WHERE `cu`.`user_id` = `u`.`id`
            AND `cu`.`course_id` = ?d
            AND `u`.`id` = ?d", $course_id, $_REQUEST['uInfo'])->count;




if ($rescnt == 0) {
    header("Location: ./detailsAll.php?course=$course_code");
    exit();
}

//$trackedUser = $results[0];

//$nameTools = $trackedUser['surname'] . " " . $trackedUser['givenname'];
/*
  $tool_content .= ucfirst(strtolower($langUser)).': <br />'."\n"
  .'<ul>'."\n"
  .'<li>'.$langLastName.': '.$trackedUser['surname'].'</li>'."\n"
  .'<li>'.$langName.': '.$trackedUser['givenname'].'</li>'."\n"
  .'<li>'.$langEmail.': ';
  if( empty($trackedUser['email']) )	$tool_content .= $langNoEmail;
  else 			$tool_content .= $trackedUser['email'];

  $tool_content .= '</li>'."\n"
  .'</ul>'."\n"
  .'</p>'."\n";
 */

// get list of learning paths of this course
// list available learning paths
$lpList = Database::get()->queryArray("SELECT name, learnPath_id
			 FROM lp_learnPath 
                        WHERE course_id = ?d
		     ORDER BY `rank`", $course_id);

$tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));      
// table header
$tool_content .= '<div class="table-responsive"><table class="table-default">' . "\n"
        . '      <tr class="list-header text-left">' . "\n"
        . '        <th>' . $langLearningPath . '</th>' . "\n"
        . '        <th>' . $langProgress . '</th>' . "\n"
        . '      </tr>' . "\n";
if (count($lpList) == 0) {
    $tool_content .= '    <tr>' . "\n"
            . '        <td colspan="2" class="text-center">' . $langNoLearningPath . '</td>' . "\n"
            . '      </tr>' . "\n";
} else {
    // display each learning path with the corresponding progression of the user
    foreach ($lpList as $lpDetails) {
        $tool_content .= "<tr>";
        $lpProgress = get_learnPath_progress($lpDetails->learnPath_id, $_GET['uInfo']);
        $tool_content .= '' . "\n"
                . '        <td><a href="detailsUserPath.php?course=' . $course_code . '&amp;uInfo=' . $_GET['uInfo'] . '&amp;path_id=' . $lpDetails->learnPath_id . '">' . htmlspecialchars($lpDetails->name) . '</a></td>' . "\n"
                . '        <td align="right" width="120">' . ""
                . disp_progress_bar($lpProgress, 1)
                . '</td>' . "\n"
                . '      </tr>' . "\n";
    }
}
$tool_content .= '      </table></div>' . "\n";

draw($tool_content, 2, null, $head_content);

