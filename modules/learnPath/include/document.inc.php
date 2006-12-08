<?php

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
                     Yannis Exidaridis <jexi@noc.uoa.gr> 
                     Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	document.inc.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: insertMyExercise.php Revision: 1.9.2.2
	      
	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================        
    @Description:

    @Comments:
 
    @todo: 
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
$assetPath = db_query_get_single_value($sql);

$sql = "SELECT `filename`
         FROM `".$TABLEDOCUMENT."`
        WHERE `path` LIKE \"" .addslashes($assetPath) ."\"";
$fileName = db_query_get_single_value($sql);

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
