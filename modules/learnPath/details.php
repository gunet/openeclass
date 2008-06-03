<?php
/*=============================================================================
       	GUnet eClass 2.0
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

$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPath);
if (!$is_adminOfCourse) claro_die($langNotAllowed);
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
	$tool_content .= "    <p><b>$langLearningPath</b>: ";
	$tool_content .= claro_disp_tool_title($titleTab);
	$tool_content .= "</p>";
	mysql_select_db($mysqlMainDb);

	// display a list of user and their respective progress
	$sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
		FROM `".$TABLEUSER."` AS U,
		`".$TABLECOURSUSER."` AS CU
		WHERE U.`user_id`= CU.`user_id`
		AND CU.`code_cours` = '". addslashes($currentCourseID) ."'
		ORDER BY U.`nom` ASC";

	@$tool_content .= get_limited_page_links($sql, 30, $langPreviousPage, $langNextPage);

	$usersList = get_limited_list($sql, 30);
	mysql_select_db($currentCourseID);

	// display tab header
	$tool_content .= '    <table width="99%">'."\n\n"
		.'    <thead>'."\n"
		.'    <tr>'."\n"
		.'      <th colspan="2" class="left">'.$langStudent.'</th>'."\n"
		.'      <th colspan="2" width="25%">'.$langProgress.'</th>'."\n"
		.'    </tr>'."\n"
		.'    </thead>'."\n\n"
		.'    <tbody>'."\n";

	// display tab content
	foreach ($usersList as $user)
	{
		$lpProgress = get_learnPath_progress($path_id,$user['user_id']);
		$tool_content .= '    <tr>'."\n"
		.'      <td width="1"><img src="../../template/classic/img/bullet_bw.gif" alt="bullet" title="bullet" border="0"></td>'."\n"
		.'      <td><a href="detailsUserPath.php?uInfo='.$user['user_id'].'&amp;path_id='.$path_id.'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
		.'      <td align="right">'
		.claro_disp_progress_bar($lpProgress, 1)
		.'</td>'."\n"
		.'      <td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
		.'    </tr>'."\n";
	}
	// foot of table
	$tool_content .= '    </tbody>'."\n\n".'    </table>'."\n\n";
}

draw($tool_content, 2, "learnPath", $head_content);
?>
