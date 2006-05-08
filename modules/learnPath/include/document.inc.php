<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 *
 */
// document browser vars
$TABLEDOCUMENT = $_course['dbNameGlu']."document";


// Update infos about asset
$sql = "SELECT `path`
         FROM `".$TABLEASSET."`
        WHERE `module_id` = ". (int)$_SESSION['module_id'];
$assetPath = claro_sql_query_get_single_value($sql);

$baseServDir = $webDir;
$courseDir = "$currentCourseID/document";
$baseWorkDir = $baseServDir.$courseDir;
$file = $baseWorkDir.$assetPath;
$fileSize = format_file_size(filesize($file));
$fileDate = format_date(filectime($file));


//####################################################################################\\
//######################## DISPLAY DETAILS ABOUT THE DOCUMENT ########################\\
//####################################################################################\\
echo "\n\n".'<hr noshade="noshade" size="1" />'."\n\n"
	.'<h4>'.$langDocumentInModule.'</h4>'."\n\n"
	.'<table class="claroTable" width="100%" border="0" cellspacing="2">'."\n"
	.'<thead>'."\n"
	.'<tr class="headerX" bgcolor="#e6e6e6">'."\n"
	.'<th>'.$langFileName.'</th>'."\n"
    .'<th>'.$langSize.'</th>'."\n"
    .'<th>'.$langDate.'</th>'."\n"
	.'</tr>'."\n"
	.'</thead>'."\n"
	.'<tbody>'."\n"
	.'<tr align="center">'."\n"
	.'<td align="left">'.basename($file).'</td>'."\n"
    .'<td>'.$fileSize.'</td>'."\n"
    .'<td>'.$fileDate.'</td>'."\n"
	.'</tr>'."\n"
	.'</tbody>'."\n"
	.'</table>'."\n";
?>