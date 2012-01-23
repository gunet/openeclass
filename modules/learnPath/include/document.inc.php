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


/**===========================================================================
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
// document browser vars
$TABLEDOCUMENT = "document";


// Update infos about asset
$sql = "SELECT `path`
         FROM `".$TABLEASSET."`
        WHERE `module_id` = ". (int)$_SESSION['lp_module_id'];
$assetPath = db_query_get_single_value($sql, $mysqlMainDb);

$sql = "SELECT `filename`
         FROM `".$TABLEDOCUMENT."`
        WHERE `path` LIKE \"" .addslashes($assetPath) ."\"";
$fileName = db_query_get_single_value($sql, $mysqlMainDb);

$baseServDir = $webDir;
$courseDir = "courses/".$currentCourseID."/document";
$baseWorkDir = $baseServDir.$courseDir;
$file = $baseWorkDir.$assetPath;
$fileSize = format_file_size(filesize($file));
$fileDate = format_date(filectime($file));


//####################################################################################\\
//######################## DISPLAY DETAILS ABOUT THE DOCUMENT ########################\\
//####################################################################################\\
$tool_content .= "\n\n".'<hr noshade="noshade" size="1" />'."\n\n"
	.'<h4>'.$langDocumentInModule.'</h4>'."\n\n"
	.'<table width="99%">'."\n"
	.'<thead>'."\n"
	.'<tr>'."\n"
	.'<th>'.$langFileName.'</th>'."\n"
    .'<th>'.$langSize.'</th>'."\n"
    .'<th>'.$langDate.'</th>'."\n"
	.'</tr>'."\n"
	.'</thead>'."\n"
	.'<tbody>'."\n"
	.'<tr align="center">'."\n"
	.'<td align="left">'.$fileName.'</td>'."\n"
    .'<td>'.$fileSize.'</td>'."\n"
    .'<td>'.$fileDate.'</td>'."\n"
	.'</tr>'."\n"
	.'</tbody>'."\n"
	.'</table>'."\n";
?>
