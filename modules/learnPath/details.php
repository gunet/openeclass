<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

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
$require_prof = TRUE;

$TABLECOURSUSER	        = "cours_user";
$TABLEUSER              = "user";
$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";

$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPaths);
$nameTools = $langStatsOfLearnPath;

// path id can not be empty, return to the list of learning paths
if( empty($_REQUEST['path_id']) )
{
	header("Location: ./learningPathList.php");
	exit();
}

mysql_select_db($currentCourseID);
$path_id = (int) $_REQUEST['path_id'];

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
	$tool_content .= '    <table width="99%" class="LearnPathSum">'."\n\n"
		.'    <tbody>'."\n"
		.'    <tr class="odd">'."\n"
		.'      <td colspan="4" class="left">'.$langLearnPath.': <b>';
	$tool_content .= disp_tool_title($titleTab);
	$tool_content .= '</b></td>'."\n"
		.'    </tr>'."\n"
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
	           $tool_content .= "\n    <tr>";
	        } else {
	           $tool_content .= "\n    <tr class=\"odd\">";
            }
		$tool_content .= ''."\n"
		.'      <td width="1"><img src="../../template/classic/img/arrow_grey.gif" alt="bullet" title="bullet" border="0"></td>'."\n"
		.'      <td><a href="detailsUserPath.php?uInfo='.$user['user_id'].'&amp;path_id='.$path_id.'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
		.'      <td align="right">'
		.disp_progress_bar($lpProgress, 1)
		.'</td>'."\n"
		.'      <td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
		.'    </tr>'."\n";
		$k++;
	}
	// foot of table
	$tool_content .= '    </tbody>'."\n\n".'    </table>'."\n\n";
}

draw($tool_content, 2, "learnPath", $head_content);
?>
