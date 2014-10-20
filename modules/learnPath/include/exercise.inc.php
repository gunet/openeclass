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


/* * ===========================================================================
  exercise
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: exercise.inc.php Revision: 1.14.2.3

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
 */

if (isset($cmd) && $cmd = "raw") {
    // change raw if value is a number between 0 and 100
    if (isset($_POST['newRaw']) && is_num($_POST['newRaw']) && $_POST['newRaw'] <= 100 && $_POST['newRaw'] >= 0) {
        $sql = "UPDATE `lp_rel_learnPath_module`
				SET `raw_to_pass` = ?d
				WHERE `module_id` = ?d
				AND `learnPath_id` = ?d";
        Database::get()->query($sql, $_POST['newRaw'], $_SESSION['lp_module_id'], $_SESSION['path_id']);

        $dialogBox = $langRawHasBeenChanged;
    }
}


$tool_content .= '<hr noshade="noshade" size="1" />';

//####################################################################################\\
//############################### DIALOG BOX SECTION #################################\\
//####################################################################################\\
if (!empty($dialogBox)) {
    $tool_content .= disp_message_box($dialogBox);
}

// form to change raw needed to pass the exercise
$sql = "SELECT `lock`, `raw_to_pass`
        FROM `lp_rel_learnPath_module` AS LPM
       WHERE LPM.`module_id` = ?d
         AND LPM.`learnPath_id` = ?d";

$learningPath_module = Database::get()->querySingle($sql, $_SESSION['lp_module_id'], $_SESSION['path_id']);

// if this module blocks the user if he doesn't complete
if (isset($learningPath_module->lock) && $learningPath_module->lock == 'CLOSE' && isset($learningPath_module->raw_to_pass)) {
    $tool_content .= '<form method="POST" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">' . "\n"
            . '<label for="newRaw">' . $langChangeRaw . '</label>' . "\n"
            . '<input type="text" value="' . htmlspecialchars($learningPath_module->raw_to_pass) . '" name="newRaw" id="newRaw" size="3" maxlength="3" /> % ' . "\n"
            . '<input type="hidden" name="cmd" value="raw" />' . "\n"
            . '<input class="btn btn-primary" type="submit" value="' . $langOk . '" />' . "\n"
            . '</form>' . "\n\n";
}

// display current exercise info and change comment link
$sql2 = "SELECT `E`.`id` AS `exerciseId`, `M`.`name`
        FROM `lp_module` AS `M`,
             `lp_asset`  AS `A`,
             `exercise` AS `E`
       WHERE `A`.`module_id` = M.`module_id`
         AND `M`.`module_id` = ?d
         AND `M`.`course_id` = ?d
         AND `E`.`id` = `A`.`path`";
$module = Database::get()->querySingle($sql2, $_SESSION['lp_module_id'], $course_id);

if ($module) {
    $tool_content .= "\n\n" . '<h4>' . $langExerciseInModule . ' :</h4>' . "\n"
            . '<p>' . "\n"
            . htmlspecialchars($module->name)
            . '&nbsp;' . "\n"
            . '<a href="../exercise/admin.php?course=' . $course_code . '&amp;exerciseId=' . $module->exerciseId . '">'
            . '<img src="' . $imgRepositoryWeb . 'edit.png" border="0" alt="' . $langModify . '" title="' . $langModify . '" />'
            . '</a>' . "\n"
            . '</p>' . "\n";

    $tool_content .= '<hr noshade="noshade" size="1" />';
} // else sql error, do nothing except in debug mode, where claro_sql_query_fetch_all will show the error