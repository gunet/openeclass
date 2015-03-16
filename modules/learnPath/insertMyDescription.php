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
  insertMyDescription.php
  @last update: 30-06-2006 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
  ==============================================================================
  @Description: This script lets the course
  admin to add the course description to a learning path

  @Comments:

  @todo:
  ==============================================================================
 */


$require_current_course = TRUE;
$require_editor = TRUE;

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPaths);
$navigation[] = array("url" => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'], "name" => $langAdm);
$toolName = $langInsertMyDescToolName;

$tool_content .= 
         action_bar(array(
            array('title' => $langBack,
                'url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'],
                'icon' => 'fa-reply',
                'level' => 'primary-label'))) ;

/* ====================================== */
// TODO: check if course description is already in the pool of modules
// and if it is, use that instead of adding it as new
// SQL Checks
// check if a module of this course already used the same document
$thisDocumentModule = Database::get()->querySingle("SELECT * FROM `lp_module` AS M, `lp_asset` AS A
	WHERE A.`module_id` = M.`module_id`
	AND M.`course_id` = ?d
	AND M.`contentType` = ?s", $course_id, CTCOURSE_DESCRIPTION_);

if (!$thisDocumentModule) {
    // create new module
    $insertedModule_id = Database::get()->query("INSERT INTO `lp_module`
		(`course_id`, `name`, `contentType`, `comment`, `launch_data`)
		VALUES (?d, ?s, ?s, '', '')", $course_id, $langCourseDescription, CTCOURSE_DESCRIPTION_)->lastInsertID;

    // create new asset
    $insertedAsset_id = Database::get()->query("INSERT INTO `lp_asset`
		(`path` , `module_id`, `comment` )
		VALUES ('', ?d, '' )", $insertedModule_id)->lastInsertID;

    Database::get()->query("UPDATE `lp_module`
	SET `startAsset_id` = ?d
	WHERE `module_id` = ?d
	AND `course_id` = ?d", $insertedAsset_id, $insertedModule_id, $course_id);

    // determine the default order of this Learning path
    $order = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max
		FROM `lp_rel_learnPath_module`
		WHERE `learnPath_id` = ?d", $_SESSION['path_id'])->max);

    // finally : insert in learning path
    Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
		(`learnPath_id`, `module_id`, `rank`, `lock`, `visible`, `specificComment`)
		VALUES (?d, ?d, ?d, 'OPEN', 1, '')", $_SESSION['path_id'], $insertedModule_id, $order);
} else {
    // check if this is this LP that used this course description as a module
    $sql = "SELECT COUNT(*) AS count FROM `lp_rel_learnPath_module` AS LPM,
		`lp_module` AS M,
		`lp_asset` AS A
		WHERE M.`module_id` =  LPM.`module_id`
		AND M.`startAsset_id` = A.`asset_id`
		AND M.`course_id` = ?d
		AND LPM.`learnPath_id` = ?d
		AND M.`contentType` = ?s";
    $num = Database::get()->querySingle($sql, $course_id, $_SESSION['path_id'], CTCOURSE_DESCRIPTION_)->count;

    if ($num == 0) { // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
        // determine the default order of this Learning path
        $order = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max
			FROM `lp_rel_learnPath_module`
			WHERE `learnPath_id` = ?d", $_SESSION['path_id'])->max);
        
        // finally : insert in learning path
        Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
			(`learnPath_id`, `module_id`, `rank`, `lock`, `visible`, `specificComment`)
			VALUES (?d, ?d, ?d, 'OPEN', 1, '')", $_SESSION['path_id'], $thisDocumentModule->module_id, $order);
    }
}

$tool_content .= "<div class='alert alert-success'>";
$tool_content .= disp_tool_title($langLinkInsertedAsModule);
$tool_content .= "</div>";

draw($tool_content, 2);
