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
  document.inc.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: insertMyExercise.php Revision: 1.9.2.2

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
 */

/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 *
 */
// Update infos about asset
$assetPath = Database::get()->querySingle("SELECT `path` FROM `lp_asset`
        WHERE `module_id` = ?d", $_SESSION['lp_module_id'])->path;

$fileName = Database::get()->querySingle("SELECT `filename` FROM `document`
        WHERE `path` LIKE ?s", $assetPath)->filename;

$baseServDir = $webDir;
$courseDir = "/courses/" . $course_code . "/document";
$baseWorkDir = $baseServDir . $courseDir;
$file = $baseWorkDir . $assetPath;
$fileSize = format_file_size(filesize($file));
$fileDate = nice_format(date('Y-m-d', filectime($file)));


//####################################################################################\\
//######################## DISPLAY DETAILS ABOUT THE DOCUMENT ########################\\
//####################################################################################\\
$tool_content .= "\n\n" . '<hr noshade="noshade" size="1" />' . "\n\n"
        . '<h4>' . $langDocumentInModule . '</h4>' . "\n\n"
        . '<table width="99%">' . "\n"
        . '<thead>' . "\n"
        . '<tr>' . "\n"
        . '<th>' . $langFileName . '</th>' . "\n"
        . '<th>' . $langSize . '</th>' . "\n"
        . '<th>' . $langDate . '</th>' . "\n"
        . '</tr>' . "\n"
        . '</thead>' . "\n"
        . '<tbody>' . "\n"
        . '<tr align="center">' . "\n"
        . '<td align="left">' . $fileName . '</td>' . "\n"
        . '<td>' . $fileSize . '</td>' . "\n"
        . '<td>' . $fileDate . '</td>' . "\n"
        . '</tr>' . "\n"
        . '</tbody>' . "\n"
        . '</table>' . "\n";
