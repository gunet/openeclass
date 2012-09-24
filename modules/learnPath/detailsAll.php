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
$require_editor = TRUE;

$TABLELEARNPATH         = "lp_learnPath";
$TABLECOURSUSER	        = "cours_user";
$TABLEUSER              = "user";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

require_once("../../include/baseTheme.php");
if (isset($_GET['from_stats']) and $_GET['from_stats'] == 1) { // if we come from statistics
        $navigation[] = array('url' => '../usage/usage.php?course='.$code_cours, 'name' => $langUsage);
        $nameTools = "$langLearningPaths - $langTrackAllPathExplanation";
} else {
        $navigation[] = array("url"=>"learningPathList.php?course=$code_cours", "name"=> $langLearningPaths);
        $nameTools = $langTrackAllPathExplanation;
}

if (isset($_GET['from_stats']) and $_GET['from_stats'] == 1) { // if we come from statistics
        $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
            <li><a href='../usage/favourite.php?course=$code_cours&amp;first='>$langFavourite</a></li>
            <li><a href='../usage/userlogins.php?course=$code_cours&amp;first='>$langUserLogins</a></li>
            <li><a href='../usage/userduration.php?course=$code_cours'>$langUserDuration</a></li>
            <li><a href='detailsAll.php?course=$code_cours&amp;from_stats=1'>$langLearningPaths</a></li>
            <li><a href='../usage/group.php?course=$code_cours'>$langGroupUsage</a></li>
          </ul>
        </div>";                
} else {
        $tool_content .= "
          <div id='operations_container'>
            <ul id='opslist'>
              <li>$langDumpUserDurationToFile: <a href='dumpuserlearnpathdetails.php?course=$code_cours'>$langcsvenc2</a></li>
              <li><a href='dumpuserlearnpathdetails.php?course=$code_cours&amp;enc=1253'>$langcsvenc1</a></li>
            </ul>
          </div>";
}

// check if there are learning paths available
$l = db_query("SELECT * FROM `$TABLELEARNPATH`", $currentCourseID);
if ((mysql_num_rows($l) == 0)) {        
	$tool_content .= "<p class='alert1'>$langNoLearningPath</p>";
	draw($tool_content, 2, null, $head_content);
	exit;
} else {
        $tool_content .= "<div class='info'>
           <b>$langDumpUserDurationToFile: </b>1. <a href='dumpuserlearnpathdetails.php?course=$code_cours'>$langcsvenc2</a>
                2. <a href='dumpuserlearnpathdetails.php?course=$code_cours&amp;enc=1253'>$langcsvenc1</a>          
          </div>";
}

// display a list of user and their respective progress
$sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
	FROM $mysqlMainDb.`$TABLEUSER` AS U, $mysqlMainDb.`$TABLECOURSUSER` AS CU
	WHERE U.`user_id`= CU.`user_id`
	AND CU.`cours_id` = $cours_id
	ORDER BY U.`nom` ASC";

@$tool_content .= get_limited_page_links($sql, 30, $langPreviousPage, $langNextPage);
$usersList = get_limited_list($sql, 30);

	
// display tab header
$tool_content .= "
  <table width='99%' class='tbl_alt'>
  <tr>
    <th>&nbsp;</th>
    <th class='left'><div align='left'>$langStudent</div></th>
    <th width='120'>$langAm</th>
    <th>$langGroup</th>
    <th colspan='2'>$langProgress&nbsp;&nbsp;</th>
  </tr>\n";

mysql_select_db($currentCourseID);

// display tab content
$k = 0;
foreach ($usersList as $user)
{
	// list available learning paths
	$sql = "SELECT LP.`learnPath_id` FROM `$currentCourseID`.lp_learnPath AS LP";

	$learningPathList = db_query_fetch_all($sql);
	$iterator = 1;
	$globalprog = 0;
	if ($k%2 == 0) {
		$tool_content .= "<tr class=\"even\">\n";
	} else {
		$tool_content .= "<tr class=\"odd\">\n";
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
		
        $total = round($globalprog/($iterator-1));
        $tool_content .= '    <td width="1"><img src="'.$themeimg.'/arrow.png" alt=""></td>'."\n"
        .'    <td><a href="detailsUser.php?course='.$code_cours.'&amp;uInfo='.$user['user_id'].'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
        .'    <td class="center">'.q(uid_to_am($user['user_id'])).'</td>'."\n"
        .'    <td align="center">'.user_groups($cours_id, $user['user_id']).'</td>'."\n"
        .'    <td class="right" width=\'120\'>'
        .disp_progress_bar($total, 1)
        .'</td>'."\n"
        .'    <td align="left" width=\'10\'>'.$total.'%</td>'."\n"
        .'</tr>'."\n";
	
	$k++;
}
// foot of table
$tool_content .= '</table>'."\n\n";

draw($tool_content, 2, null, $head_content);