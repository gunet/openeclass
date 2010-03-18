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
	detailsAll.php
	@last update: 05-12-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: tracking/learnPath_detailsAllPath.php Revision: 1.11

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Gioacchino Poletto <info@polettogioacchino.com>
==============================================================================
    @Description: This script displays the stats of all users of a course
                  for his progression into the sum of all learning paths of
                  the course

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
$nameTools = $langTrackAllPathExplanation;

// display a list of user and their respective progress
$sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
	FROM `$TABLEUSER` AS U, `$TABLECOURSUSER` AS CU
	WHERE U.`user_id`= CU.`user_id`
	AND CU.`cours_id` = $cours_id
	ORDER BY U.`nom` ASC";

@$tool_content .= get_limited_page_links($sql, 30, $langPreviousPage, $langNextPage);
$usersList = get_limited_list($sql, 30);

$tool_content .= "<table width='99%' align='left' class='Users_Operations'>
	<thead><tr>
	<td>&nbsp;<b>$langDumpUserDurationToFile:</b>
        &nbsp;&nbsp;1.&nbsp;<a href='dumpuserlearnpathdetails.php'>$langcsvenc2</a>
        &nbsp;&nbsp;2.&nbsp;<a href='dumpuserlearnpathdetails.php?enc=1253'>$langcsvenc1</a>
        </td></tr>
	</thead>
	</table>";
	
// display tab header
$tool_content .= "<table width='99%' class='LearnPathSum'><thead><tr>
	<th class='left'>&nbsp;</th>
	<th class='left'><div align='left'>$langStudent</div></th>
	<th class='left'>$langAm</th>
	<th width='15%' align='center'>$langGroup</th>
	<th colspan='2' width='30%'><div align='center'>$langProgress&nbsp;&nbsp;</div></th>
	</tr></thead><tbody>";

mysql_select_db($currentCourseID);

// display tab content
$k=0;
foreach ($usersList as $user)
{
	// list available learning paths
	$sql = "SELECT LP.`learnPath_id` FROM `".$TABLELEARNPATH."` AS LP";

	$learningPathList = db_query_fetch_all($sql);

	$iterator = 1;
	$globalprog = 0;
	if ($k%2 == 0) {
		$tool_content .= "\n    <tr>";
	} else {
		$tool_content .= "\n    <tr class=\"odd\">";
	}
	foreach($learningPathList as $learningPath)
	{
		// % progress
		$prog = get_learnPath_progress($learningPath['learnPath_id'], $user['user_id']);
		if ($prog >= 0)
		{
			$globalprog += $prog;
		}
		$iterator++;
	}
	if($iterator == 1)
	{
		$tool_content .= '<td align="center" colspan="8">'.$langNoLearningPath.'</td></tr>'."\n";
	}
	else
	{
		$total = round($globalprog/($iterator-1));
		$tool_content .= '<td width="1"><img src="../../template/classic/img/arrow_grey.gif" alt="bullet" title="bullet" border="0"></td>'."\n"
		.'<td><a href="detailsUser.php?uInfo='.$user['user_id'].'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
		.'<td>'.uid_to_am($user['user_id']).'</td>'
		.'<td align="center">'.gid_to_name(user_group($user['user_id'])).'</td>'
		.'<td align="right">'
		.disp_progress_bar($total, 1)
		.'</td>'."\n"
		.'<td align="left"><small>'.$total.'%</small></td>'."\n"
		.'</tr>'."\n";
	}
	$k++;
}

// foot of table
$tool_content .= '</tbody>'."\n".'</table>'."\n\n";
draw($tool_content, 2, "learnPath", $head_content);
?>
