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
  scorm.inc.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: scorm.inc.php Revision: 1.12.2.3

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
 */

// change raw if value is a number between 0 and 100
if (isset($_POST['newRaw']) && is_num($_POST['newRaw']) && $_POST['newRaw'] <= 100 && $_POST['newRaw'] >= 0) {
    $sql = "UPDATE `lp_rel_learnPath_module`
			SET `raw_to_pass` = ?d
			WHERE `module_id` = ?d
			AND `learnPath_id` = ?d";
    Database::get()->query($sql, $_POST['newRaw'], $_SESSION['lp_module_id'], $_SESSION['path_id']);

    $dialogBox = $langRawHasBeenChanged;
}


//####################################################################################\\
//############################### DIALOG BOX SECTION #################################\\
//####################################################################################\\
if (!empty($dialogBox)) {
    $tool_content .= $dialogBox;
}

// form to change raw needed to pass the exercise
$sql = "SELECT `lock`, `raw_to_pass`
        FROM `lp_rel_learnPath_module` AS LPM
       WHERE LPM.`module_id` = ?d
         AND LPM.`learnPath_id` = ?d";

$learningPath_module = Database::get()->querySingle($sql, $_SESSION['lp_module_id'], $_SESSION['path_id']);

if (isset($learningPath_module->lock) && $learningPath_module->lock == 'CLOSE' && isset($learningPath_module->raw_to_pass)) { // this module blocks the user if he doesn't complete
    $tool_content .= "\n\n" . '<hr noshade="noshade" size="1" />' . "\n"
            . '<form method="POST" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">' . "\n"
            . '<label for="newRaw">' . $langChangeRaw . '</label>' . "\n"
            . '<input type="text" value="' . htmlspecialchars($learningPath_module->raw_to_pass) . '" name="newRaw" id="newRaw" size="3" maxlength="3" /> % ' . "\n"
            . '<input class="btn btn-primary" type="submit" value="' . $langOk . '" />' . "\n"
            . '</form>' . "\n\n";
}