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
  insertMyExercise.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: insertMyExercise.php Revision: 1.14.2.1

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
  @Description: This script lists all available exercises and the course
  admin can add them to a learning path
  ============================================================================== */

$require_current_course = TRUE;
$require_editor = TRUE;

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/exercise/exercise.class.php';
require_once 'modules/exercise/question.class.php';

ModalBoxHelper::loadModalBox();
$head_content .= <<<EOF
<script type='text/javascript'>
$(document).ready(function() {

    $('tr').click(function(event) {
        if (event.target.type !== 'checkbox') {
            $(':checkbox', this).trigger('click');
        }
    });

});
</script>
EOF;

$messBox = '';
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langLearningPath);
$navigation[] = array('url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . intval($_SESSION['path_id']), 'name' => $langAdm);
$toolName = $langInsertMyExerciseToolName;
$tool_content .= 
         action_bar(array(
            array('title' => $langBack,
                'url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'],
                'icon' => 'fa-reply',
                'level' => 'primary-label'))) ;

// see checked exercises to add
$resultex = Database::get()->queryArray("SELECT * FROM exercise WHERE course_id = ?d AND active = 1", $course_id);

// for each exercise checked, try to add it to the learning path.
foreach ($resultex as $listex) {
    if (isset($_REQUEST['insertExercise']) && isset($_REQUEST['check_' . $listex->id])) {  //add
        $insertedExercise = $listex->id;
        
        // check if the exercise is compatible with LP
        $incompatible = false;
        $objExercise = new Exercise();
        $objExercise->read($insertedExercise);
        $questionList = $objExercise->selectQuestionList();
        foreach ($questionList as $questionId) {
            $objQuestion = new Question();
            $objQuestion->read($questionId);
            if ($objQuestion->selectType() == 6) {
                $incompatible = true;
                break;
            }
        }
        if ($incompatible) {
            $messBox .= "<p>" . disp_message_box1(q($listex->title) . " : " . $langExIncompatibleWithLP . "<br>", "caution") . "</p>";
            continue;
        }

        // check if a module of this course already used the same exercise
        $exerciseModuleFrom = "FROM lp_module AS M, lp_asset AS A
            WHERE A.module_id = M.module_id
              AND A.path LIKE ?s
              AND M.contentType = ?s
              AND M.course_id = ?d";

        $num = Database::get()->querySingle("SELECT COUNT(*) AS count " . $exerciseModuleFrom, $insertedExercise, CTEXERCISE_, $course_id)->count;
        $thisExerciseModule = Database::get()->querySingle("SELECT * " . $exerciseModuleFrom, $insertedExercise, CTEXERCISE_, $course_id);
        // determine the default order of this Learning path
        $order = 1 + intval(Database::get()->querySingle("SELECT MAX(rank) AS max
             FROM lp_rel_learnPath_module
            WHERE learnPath_id = ?d", $_SESSION['path_id'])->max);
        $exercise = Database::get()->querySingle("SELECT * FROM exercise WHERE course_id = ?d AND id = ?d", $course_id, $insertedExercise);

        if ($num == 0) {
            $comment = ($exercise && !empty($exercise->description)) ? $exercise->description : $langDefaultModuleComment;

            // create new module
            $insertedExercice_id = Database::get()->query("INSERT INTO lp_module
                (course_id, name, comment, contentType, launch_data)
                VALUES (?d, ?s, ?s, ?s, ?s) ", $course_id, $exercise->title, $comment, CTEXERCISE_, '')->lastInsertID;

            // create new asset
            $insertedAsset_id = Database::get()->query("INSERT INTO lp_asset
                (path, module_id, comment)
                VALUES (?s, ?d, ?s)", $insertedExercise, $insertedExercice_id, '')->lastInsertID;

            Database::get()->query("UPDATE lp_module
                  SET startAsset_id = ?d
                WHERE module_id = ?d
                  AND course_id = ?d", $insertedAsset_id, $insertedExercice_id, $course_id);

            insertInLearningPath($insertedExercice_id, $order);
            Session::Messages($langInsertedAsModule, 'alert-info');
            redirect_to_home_page('modules/learnPath/learningPathAdmin.php?course=' . $course_code);
        } else {
            // exercise is already used as a module in another learning path , so reuse its reference
            // check if this is this LP that used this exercise as a module
            $num = Database::get()->querySingle("SELECT COUNT(*) AS count
                 FROM lp_rel_learnPath_module AS LPM,
                      lp_module AS M,
                      lp_asset AS A
                WHERE M.module_id = LPM.module_id
                  AND M.startAsset_id = A.asset_id
                  AND A.path = ?s
                  AND LPM.learnPath_id = ?d
                  AND M.course_id = ?d", $insertedExercise, $_SESSION['path_id'], $course_id)->count;

            if ($num == 0) {
                // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                insertInLearningPath($thisExerciseModule->module_id, $order);
                Session::Messages($langInsertedAsModule, 'alert-info');
                redirect_to_home_page('modules/learnPath/learningPathAdmin.php?course=' . $course_code);
            } else {                
                Session::Messages($langAlreadyUsed, 'alert-warning');
                redirect_to_home_page('modules/learnPath/learningPathAdmin.php?course=' . $course_code);
            }
        }
    } // end if request
} //end while

$tool_content .= display_my_exercises("", "");

draw($tool_content, 2, null, $head_content);

/**
 * @brief insert in LP
 * @global type $langDefaultModuleAddedComment
 * @param type $module_id
 * @param type $rank
 */
function insertInLearningPath($module_id, $rank) {
    global $langDefaultModuleAddedComment;

    // finally : insert in learning path
    Database::get()->query("INSERT INTO lp_rel_learnPath_module
        (learnPath_id, module_id, specificComment, `rank`, `lock`, `visible`)
        VALUES (?d, ?d, ?s, ?d, ?s, ?d)", $_SESSION['path_id'], $module_id, $langDefaultModuleAddedComment, $rank, 'OPEN', 1);
}
