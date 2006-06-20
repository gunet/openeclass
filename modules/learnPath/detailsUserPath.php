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
	detailsUserPath.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
==============================================================================        
    @Description:

 	@Comments:
 
  	@todo: 
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");
$require_current_course = TRUE;
$langFiles = "learnPath";

$TABLECOURSUSER	        = "cours_user";
$TABLEUSER              = "user";
$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$imgRepositoryWeb       = "../../images/";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";

$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);
if (! $is_adminOfCourse ) claro_die($langNotAllowed);
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
$lpDetails = db_query_get_single_row($sql);

//### PREPARE LIST OF ELEMENTS TO DISPLAY #################################

$sql = "SELECT LPM.`learnPath_module_id`,
			LPM.`parent`,
			LPM.`lock`,
			M.`module_id`,
			M.`contentType`,
			M.`name`,
			UMP.`lesson_status`, UMP.`raw`,
			UMP.`scoreMax`, UMP.`credit`,
			UMP.`session_time`, UMP.`total_time`,
			A.`path`
			FROM (
			`".$TABLELEARNPATHMODULE."` AS LPM,
			`".$TABLEMODULE."` AS M
			)
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

//### SOME USER DETAILS ###########################################
$tool_content .= ucfirst(strtolower($langUser)).': <br />'."\n"
	.'<ul>'."\n"
	.'<li>'.$langLastName.': '.$uDetails['lastname'].'</li>'."\n"
	.'<li>'.$langFirstName.': '.$uDetails['firstname'].'</li>'."\n"
	.'<li>'.$langEmail.': '.$uDetails['email'].'</li>'."\n"
	.'</ul>'."\n\n";

//### TABLE HEADER ################################################
$tool_content .= '<br />'."\n"
	.'<table width="99%" border="0" cellspacing="2">'."\n"
	.'<thead>'."\n"
	.'<tr align="center" valign="top">'."\n"
	.'<th colspan="'.($maxDeep+1).'">'.$langModule.'</th>'."\n"
	.'<th>'.$langLastSessionTimeSpent.'</th>'."\n"
	.'<th>'.$langTotalTimeSpent.'</th>'."\n"
	.'<th>'.$langLessonStatus.'</th>'."\n"
	.'<th colspan="2">'.$langProgress.'</th>'."\n"
	.'</tr>'."\n"
	.'</thead>'."\n"
	.'<tbody>'."\n\n";

//### DISPLAY LIST OF ELEMENTS #####################################
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
	$spacingString .= '<td width="5">&nbsp;</td>';
	$colspan = $maxDeep - $module['children']+1;

	$tool_content .= '<tr align="center">'."\n".$spacingString.'<td colspan="'.$colspan.'" align="left">';
	//-- if chapter head
	if ( $module['contentType'] == CTLABEL_ )
	{
		$tool_content .= '<b>'.$module['name'].'</b>';
	}
	//-- if user can access module
	else
	{
		if($module['contentType'] == CTEXERCISE_ )
		$moduleImg = "quiz.png";
		else
		$moduleImg = choose_image(basename($module['path']));

		$contentType_alt = selectAlt($module['contentType']);
		$tool_content .= '<img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" border="0" />'.$module['name'];

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
		$tool_content .= '<td>'.$session_time.'</td>'."\n";
		//-- total_time
		$tool_content .= '<td>'.$total_time.'</td>'."\n";
		//-- status
		$tool_content .= '<td>';
		if($module['contentType'] == CTEXERCISE_ && $module['lesson_status'] != "" ) 
		$tool_content .= strtolower($module['lesson_status']);
		else
		$tool_content .= strtolower($module['lesson_status']);
		$tool_content .= '</td>'."\n";
		//-- progression
		if($module['contentType'] != CTLABEL_ )
		{
			// display the progress value for current module
			
			$tool_content .= '<td align="right">'.claro_disp_progress_bar($progress, 1).'</td>'."\n";
			$tool_content .= '<td align="left"><small>&nbsp;'.$progress.'%</small></td>'."\n";
		}
		else // label
		{
		$tool_content .= '<td colspan="2">&nbsp;</td>'."\n";
		}
		
		if ($progress > 0)
		{
		$globalProg += $progress;
		}
		
		if($module['contentType'] != CTLABEL_) 
			$moduleNb++; // increment number of modules used to compute global progression except if the module is a title
		
		$tool_content .= '</tr>'."\n\n";
}
$tool_content .= '</tbody>'."\n".'<tfoot>'."\n";

if ($moduleNb == 0)
{
		$tool_content .= '<tr><td align="center" colspan="6">'.$langNoModule.'</td></tr>';
}
elseif($moduleNb > 0)
{
		// add a blank line between module progression and global progression
		$tool_content .= '<tr><td colspan="'.($maxDeep+6).'">&nbsp;</td></tr>'."\n";
		// display global stats
		$tool_content .= '<tr>'."\n".'<small>'."\n"
			.'<td colspan="'.($maxDeep+1).'">&nbsp;</td>'."\n"
			.'<td align="right">'.(($global_time != "0000:00:00")? $langTimeInLearnPath : '&nbsp;').'</td>'."\n"
			.'<td align="center">'.(($global_time != "0000:00:00")? preg_replace("/\.[0-9]{0,2}/", "", $global_time) : '&nbsp;').'</td>'."\n"
			.'<td align="right">'.$langGlobalProgress.'</td>'."\n"
			.'<td align="right">'
			.claro_disp_progress_bar(round($globalProg / ($moduleNb) ), 1)
			.'</td>'."\n"
			.'<td align="left"><small>&nbsp;'.round($globalProg / ($moduleNb) ) .'%</small></td>'."\n"
			.'</tr>';
}
$tool_content .= "\n".'</tfoot>'."\n".'</table>'."\n";


draw($tool_content, 2, "learnPath", $head_content);

?>
