<?php

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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
	details.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================        
    @Description:

 	@Comments:
 
  	@todo: 
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
$require_current_course = TRUE;
$langFiles = "learnPath";

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


$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);
if (! $is_adminOfCourse ) claro_die($langNotAllowed);
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
$sql = "SELECT `name` 
		FROM `".$TABLELEARNPATH."`
		WHERE `learnPath_id` = ". (int)$path_id;

$learnPathName = db_query_get_single_value($sql);

if( $learnPathName )
{
	// display title
	//$titleTab['mainTitle'] = $nameTools;
	$titleTab['subTitle'] = htmlspecialchars($learnPathName);
	$tool_content .= claro_disp_tool_title($titleTab);

	mysql_select_db($mysqlMainDb);

	// display a list of user and their respective progress    
	$sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
			FROM `".$TABLEUSER."` AS U, 
					`".$TABLECOURSUSER."` AS CU
			WHERE U.`user_id`= CU.`user_id`
			AND CU.`code_cours` = '". addslashes($currentCourseID) ."'";

	$usersList = db_query_fetch_all($sql);
	mysql_select_db($currentCourseID);

	// display tab header
	$tool_content .= '<table width="99%" border="0" cellspacing="2">'."\n\n"
		.'<thead>'."\n"
		.'<tr align="center" valign="top">'."\n"
		.'<th>'.$langStudent.'</th>'."\n"
		.'<th colspan="2">'.$langProgress.'</th>'."\n"
		.'</tr>'."\n"
		.'</thead>'."\n\n"
		.'<tbody>'."\n\n";

	// display tab content
	foreach ( $usersList as $user )
	{
		$lpProgress = get_learnPath_progress($path_id,$user['user_id']);
		$tool_content .= '<tr>'."\n"
			.'<td><a href="detailsUserPath.php?uInfo='.$user['user_id'].'&amp;path_id='.$path_id.'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
			.'<td align="right">'
			.claro_disp_progress_bar($lpProgress, 1)
			.'</td>'."\n"
			.'<td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
			.'</tr>'."\n\n";
	}
	// foot of table
	$tool_content .= '</tbody>'."\n\n".'</table>'."\n\n";
}


draw($tool_content, 2, "learnPath", $head_content);

?>
