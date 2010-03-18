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
	detailsUserPath.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: tracking/lp_modules_details.php Revision: 1.20
==============================================================================
    @Description: This script presents the student's progress for a learning
                  path to the teacher.

    @Comments:

    @todo:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");
$require_current_course = TRUE;
$require_prof = TRUE;

$TABLECOURSUSER	        = "cours_user";
$TABLEUSER              = "user";
$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$imgRepositoryWeb       = "../../template/classic/img/";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";

$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPaths);
$navigation[] = array("url"=>"details.php?path_id=".$_REQUEST['path_id'], "name"=> $langStatsOfLearnPath);
$nameTools = $langTrackUser;

if( empty($_REQUEST['uInfo']) || empty($_REQUEST['path_id']) )
{
	header("Location: ./learningPathList.php");
	exit();
}


// get infos about the user
$sql = "SELECT `nom` AS `lastname`, `prenom` as `firstname`, `email`
        FROM `".$TABLEUSER."`
       WHERE `user_id` = ". (int)$_REQUEST['uInfo'];
$uDetails = db_query_get_single_row($sql);

mysql_select_db($currentCourseID);

// get infos about the learningPath
$sql = "SELECT `name`
        FROM `".$TABLELEARNPATH."`
       WHERE `learnPath_id` = ". (int)$_REQUEST['path_id'];
$LPresult = mysql_fetch_row(db_query($sql));
$LPname = $LPresult[0];

//### PREPARE LIST OF ELEMENTS TO DISPLAY #################################
$sql = "SELECT LPM.`learnPath_module_id`, LPM.`parent`,
	LPM.`lock`, M.`module_id`,
	M.`contentType`, M.`name`,
	UMP.`lesson_status`, UMP.`raw`,
	UMP.`scoreMax`, UMP.`credit`,
	UMP.`session_time`, UMP.`total_time`, A.`path`
	FROM (`".$TABLELEARNPATHMODULE."` AS LPM, `".$TABLEMODULE."` AS M)
	LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
		ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
		AND UMP.`user_id` = ". (int)$_REQUEST['uInfo']."
	LEFT JOIN `".$TABLEASSET."` AS A
		ON M.`startAsset_id` = A.`asset_id`
	WHERE LPM.`module_id` = M.`module_id`
		AND LPM.`learnPath_id` = ". (int)$_REQUEST['path_id']."
		AND LPM.`visibility` = 'SHOW'
		AND LPM.`module_id` = M.`module_id`
	GROUP BY LPM.`module_id`
	ORDER BY LPM.`rank`";

$moduleList = db_query_fetch_all($sql);

$extendedList = array();
foreach( $moduleList as $module )
{
	$extendedList[] = $module;
}

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$moduleNb = 0;
$globalProg = 0;
$global_time = "0000:00:00";

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for ( $i = 0 ; $i < sizeof($flatElementList) ; $i++ )
{
	if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
}


// -------------------- table header ----------------------------
$tool_content .= '
    <table width="99%" class="LearnPathSum">'."\n"
	.'    <tbody>'."\n"
          // ------------------- some user details --------------------------
	.'    <tr class="odd">'."\n"
	.'      <td colspan="'.($maxDeep+1).'" class="left"><small><b>'.$langLearnPath.'</b>:&nbsp;'.$LPname.'</small></td>'."\n"
	.'      <td colspan="'.($maxDeep+3).'" class="right"><small><b>'.$langStudent.'</b>: '.$uDetails['lastname'].' '.$uDetails['firstname'].' ('.$uDetails['email'].')</small></td>'."\n"
	.'    </tr>'."\n"
	.'    <tr>'."\n"
	.'      <th width="30%" colspan="'.($maxDeep+1).'">'.$langLearningObjects.'</th>'."\n"
	.'      <th width="15%">'.$langLastSessionTimeSpent.'</th>'."\n"
	.'      <th width="15%">'.$langTotalTimeSpent.'</th>'."\n"
	.'      <th width="15%">'.$langLessonStatus.'</th>'."\n"
	.'      <th width="25%" colspan="2">'.$langProgress.'</th>'."\n"
	.'    </tr>'."\n";

// ---------------- display list of elements ------------------------
foreach ($flatElementList as $module)
{
	if( $module['scoreMax'] > 0 )
	{
		$progress = @round($module['raw']/$module['scoreMax']*100);
	}
	else
	{
		$progress = 0;
	}

	if ( $module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0 )
	{
		if ( $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
		{
			$progress = 100;
		}
		else
		{
			$progress = 0;
		}
	}

	// display the current module name
	$spacingString = '';
	for($i = 0; $i < $module['children']; $i++)
	$spacingString .= '      <td width="5">&nbsp;</td>'."\n";
	$colspan = $maxDeep - $module['children']+1;

	$tool_content .= '    <tr align="center">'."\n".$spacingString.'      <td colspan="'.$colspan.'" align="left">';
	//-- if chapter head
	if ( $module['contentType'] == CTLABEL_ )
	{
		$tool_content .= '      <b>'.$module['name'].'</b>';
	}
	//-- if user can access module
	else
	{
		if($module['contentType'] == CTEXERCISE_ )
			$moduleImg = "exercise_on.gif";
		else if($module['contentType'] == CTLINK_ )
        		$moduleImg = "links_on.gif";
        else if($module['contentType'] == CTCOURSE_DESCRIPTION_ )
        	$moduleImg = "description_on.gif";
		else
		$moduleImg = choose_image(basename($module['path']));
		$contentType_alt = selectAlt($module['contentType']);
		$tool_content .= '<img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" title="'.$contentType_alt.'" border="0" /> <small>'.$module['name'].'</small>';
	}

		$tool_content .= '</td>'."\n";

		if ($module['contentType'] == CTSCORM_)
		{
			$session_time = preg_replace("/\.[0-9]{0,2}/", "", $module['session_time']);
			$total_time = preg_replace("/\.[0-9]{0,2}/", "", $module['total_time']);
			$global_time = addScormTime($global_time,$total_time);
		}
		elseif($module['contentType'] == CTLABEL_ || $module['contentType'] == CTEXERCISE_)
		{
			$session_time = $module['session_time'];
			$total_time = $module['total_time'];
		}
		else
		{
			// if no progression has been recorded for this module
			// leave
			if($module['lesson_status'] == "")
			{
			$session_time = "&nbsp;";
			$total_time = "&nbsp;";
			}
			else // columns are n/a
			{
			$session_time = "-";
			$total_time = "-";
			}
		}
		//-- session_time
		$tool_content .= '      <td><small>'.$session_time.'<small></td>'."\n";
		//-- total_time
		$tool_content .= '      <td><small>'.$total_time.'</small></td>'."\n";
		//-- status
		$tool_content .= '      <td><small>';
		if($module['contentType'] == CTEXERCISE_ && $module['lesson_status'] != "" ) {
			if ($module['lesson_status']=="NOT ATTEMPTED") {
				$tool_content .= $langNotAttempted;
			}
			else if ($module['lesson_status']=="PASSED") {
				$tool_content .= $langPassed;
			}
			else if ($module['lesson_status']=="FAILED") {
				$tool_content .= $langFailed;
			}
			else if ($module['lesson_status']=="COMPLETED") {
				$tool_content .= $langAlreadyBrowsed;
			}
			else if ($module['lesson_status']=="BROWSED") {
				$tool_content .= $langAlreadyBrowsed;
			}
			else if ($module['lesson_status']=="INCOMPLETE") {
				$tool_content .= $langNeverBrowsed;
			}
			else {
				$tool_content .= strtolower($module['lesson_status']);
			}
		}
		else {
			if ($module['lesson_status']=="NOT ATTEMPTED") {
				$tool_content .= $langNotAttempted;
			}
			else if ($module['lesson_status']=="PASSED") {
				$tool_content .= $langPassed;
			}
			else if ($module['lesson_status']=="FAILED") {
				$tool_content .= $langFailed;
			}
			else if ($module['lesson_status']=="COMPLETED") {
				$tool_content .= $langAlreadyBrowsed;
			}
			else if ($module['lesson_status']=="BROWSED") {
				$tool_content .= $langAlreadyBrowsed;
			}
			else if ($module['lesson_status']=="INCOMPLETE") {
				$tool_content .= $langNeverBrowsed;
			}
			else {
				$tool_content .= strtolower($module['lesson_status']);
			}
		}
		$tool_content .= '</small></td>'."\n";
		//-- progression
		if($module['contentType'] != CTLABEL_ )
		{
			// display the progress value for current module
			$tool_content .= '<td align="right">'.disp_progress_bar($progress, 1).'</td>'."\n";
			$tool_content .= '<td align="left"><small>&nbsp;'.$progress.'%</small></td>'."\n";
		}
		else // label
		{
		$tool_content .= '      <td colspan="2">&nbsp;</td>'."\n";
		}

		if ($progress > 0)
		{
		$globalProg += $progress;
		}
		if($module['contentType'] != CTLABEL_)
			$moduleNb++; // increment number of modules used to compute global progression except if the module is a title

		$tool_content .= '    </tr>'."\n";
}
//$tool_content .= '</tbody>'."\n".'<tfoot>'."\n";

if ($moduleNb == 0)
{
	$tool_content .= '    <tr class="odd">'."\n".'<td align="center" colspan="7">'.$langNoModule.'</td>'."\n".'    </tr>'."\n";
}
elseif($moduleNb > 0)
{
	// display global stats
	$tool_content .= '    <tr class="odd">'."\n"
		.'      <td colspan="'.($maxDeep+1).'">&nbsp;</td>'."\n"
		.'      <td align="right">'.(($global_time != "0000:00:00")? $langTimeInLearnPath : '&nbsp;').'</td>'."\n"
		.'      <td align="center">'.(($global_time != "0000:00:00")? preg_replace("/\.[0-9]{0,2}/", "", $global_time) : '&nbsp;').'</td>'."\n"
		.'<td align="right"><small>'.$langGlobalProgress.'</small></td>'."\n"
		.'<td align="right">'
		.disp_progress_bar(round($globalProg / ($moduleNb) ), 1)
		.'</td>'."\n"
		.'      <td align="left"><small>&nbsp;'.round($globalProg / ($moduleNb) ) .'%</small></td>'."\n"
		.'    </tr>';
}
$tool_content .= "\n".'    </tbody>'."\n".'    </table>'."\n";

draw($tool_content, 2, "learnPath", $head_content);
?>
