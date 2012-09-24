<?php
/* ========================================================================
 * Open eClass 2.6
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


/*===========================================================================
	details.php
	@last update: 05-12-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: tracking/learnPath_details.php Revision: 1.19

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
==============================================================================
    @Description: This script displays the stats of all users of a course
                  for his progression into the chosen learning path

    @Comments:

    @todo:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
$require_current_course = TRUE;
$require_editor = TRUE;

$TABLECOURSUSER	        = "cours_user";
$TABLEUSER              = "user";
$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

require_once("../../include/baseTheme.php");

$navigation[] = array("url"=>"learningPathList.php?course=$code_cours", "name"=> $langLearningPaths);
$nameTools = $langStatsOfLearnPath;

// path id can not be empty, return to the list of learning paths
if( empty($_REQUEST['path_id']) )
{
	header("Location: ./learningPathList.php?course=$code_cours");
	exit();
}

mysql_select_db($currentCourseID);
$path_id = intval($_REQUEST['path_id']);

// get infos about the learningPath
$sql = "SELECT `name` FROM `".$TABLELEARNPATH."` WHERE `learnPath_id` = ". (int)$path_id;
$learnPathName = db_query_get_single_value($sql);

if( $learnPathName )
{
	// display title
	$titleTab['subTitle'] = htmlspecialchars($learnPathName);
	mysql_select_db($mysqlMainDb);

	// display a list of user and their respective progress
	$sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
		FROM `$TABLEUSER` AS U,
		     `$TABLECOURSUSER` AS CU
		WHERE U.`user_id` = CU.`user_id`
		AND CU.`cours_id` = $cours_id
		ORDER BY U.`nom` ASC";

	@$tool_content .= get_limited_page_links($sql, 30, $langPreviousPage, $langNextPage);

	$usersList = get_limited_list($sql, 30);
	mysql_select_db($currentCourseID);

	// display tab header
	$tool_content .= ''."\n\n"
		.'      <p>'.$langLearnPath.': <b>';
	$tool_content .= disp_tool_title($titleTab);
	$tool_content .= '</b></p>'."\n"
		.'    <table width="99%" class="tbl_alt">'."\n"
		.'    <tr>'."\n"
		.'      <th>&nbsp;</th>'."\n"
		.'      <th><div align="left">'.$langStudent.'</div></th>'."\n"
		.'      <th colspan="2" width="25%">'.$langProgress.'</th>'."\n"
		.'    </tr>'."\n";

	// display tab content
	$k=0;
	foreach ($usersList as $user)
	{
		$lpProgress = get_learnPath_progress($path_id,$user['user_id']);
			if ($k%2==0) {
	           $tool_content .= "\n    <tr class=\"even\">";
	        } else {
	           $tool_content .= "\n    <tr class=\"odd\">";
            }
		$tool_content .= ''."\n"
		.'      <td width="1"><img src="'.$themeimg.'/arrow.png" alt=""></td>'."\n"
		.'      <td><a href="detailsUserPath.php?course='.$code_cours.'&amp;uInfo='.$user['user_id'].'&amp;path_id='.$path_id.'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
		.'      <td align="right">'
		.disp_progress_bar($lpProgress, 1)
		.'</td>'."\n"
		.'      <td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
		.'    </tr>'."\n";
		$k++;
	}
	// foot of table
	$tool_content .= '    '."\n\n".'    </table>'."\n\n";
}

draw($tool_content, 2, null, $head_content);
