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
	viewer_toc.php
	@last update: 05-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: navigation/tableOfContent.php Revision: 1.30

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: Script for displaying a navigation bar to the users when
                  they are browsing a learning path

    @Comments:

    @todo:
==============================================================================
*/

$require_current_course = TRUE;

require_once("../../config/config.php");
require_once("../../include/init.php");

/*
 * DB tables definition
 */
$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$imgRepositoryWeb = "../../template/classic/img/";
/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_LP');
/**************************************/

// lib of this tool
require_once("../../include/lib/learnPathLib.inc.php");

//lib of document tool
require_once("../../include/lib/fileDisplayLib.inc.php");

mysql_select_db($currentCourseID);
echo '<html>'."\n"
    .'<head>'."\n"
    .'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">'."\n"
    .'<link href="../../template/classic/tool_content.css" rel="stylesheet" type="text/css" />'."\n"
    .'<link href="./tool.css" rel="stylesheet" type="text/css" />'."\n"
    .'</head>'."\n"
    .'<body style="margin: 2px;">'."\n";


if($uid)
{
	$uidCheckString = "AND UMP.`user_id` = ". (int)$uid;
}
else // anonymous
{
   $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

// get the list of available modules
$sql = "SELECT LPM.`learnPath_module_id` ,
	LPM.`parent`,
	LPM.`lock`,
            M.`module_id`,
            M.`contentType`,
            M.`name`,
            UMP.`lesson_status`, UMP.`raw`,
            UMP.`scoreMax`, UMP.`credit`,
            A.`path`
         FROM (`".$TABLELEARNPATHMODULE."` AS LPM,
              `".$TABLEMODULE."` AS M)
   LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
           ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
           ".$uidCheckString."
   LEFT JOIN `".$TABLEASSET."` AS A
          ON M.`startAsset_id` = A.`asset_id`
        WHERE LPM.`module_id` = M.`module_id`
          AND LPM.`learnPath_id` = '" . (int)$_SESSION['path_id'] ."'
          AND LPM.`visibility` = 'SHOW'
          AND LPM.`module_id` = M.`module_id`
     GROUP BY LPM.`module_id`
     ORDER BY LPM.`rank`";

$extendedList = db_query_fetch_all($sql);

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$is_blocked = false;
$atleastOne = false;
$moduleNb = 0;

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
{
	if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
}

$moduleNameLength = 255; // size of 'name' to display in the list, the string will be partially displayed if it is more than $moduleNameLength letters long

// get the name of the learning path
$sql = "SELECT `name`
      FROM `".$TABLELEARNPATH."`
      WHERE `learnPath_id` = '". (int)$_SESSION['path_id']."'";

$lpName = db_query_get_single_value($sql);

$learnPath =  '<strong>'.wordwrap($lpName,$moduleNameLength,' ',1).'</strong>';

$previous = ""; // temp id of previous module, used as a buffer in foreach
$previousModule = ""; // module id that will be used in the previous link
$nextModule = ""; // module id that will be used in the next link

foreach ($flatElementList as $module)
{
	if($module['contentType'] == CTEXERCISE_ )
		$moduleImg = 'exercise_on.gif';
	else if($module['contentType'] == CTLINK_ )
		$moduleImg = "links_on.gif";
	else if($module['contentType'] == CTCOURSE_DESCRIPTION_ )
		$moduleImg = "description_on.gif";
	else
		$moduleImg = choose_image(basename($module['path']));

	$contentType_alt = selectAlt($module['contentType']);
	if( $module['scoreMax'] > 0 && $module['raw'] > 0)
	{
		$progress = @round($module['raw']/$module['scoreMax']*100);
	}
	else
	{
		$progress = 0;
	}

	if ( $module['contentType'] == CTEXERCISE_ )
	{
		$passExercise = ($module['credit']=='CREDIT');
	}
	else
	{
		$passExercise = false;
	}

	if ( $module['contentType'] == CTSCORM_ )
	{
		if ( $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
		{
			// commenting due to bug. Progress is calculated correctyl above
			//$progress = 100;
			$passExercise = true;
		}
		else
		{
			// commenting due to bug. Progress is calculated correctyl above
			//$progress = 0;
			$passExercise = false;
		}
	}

	// display the current module name (and link if allowed)
	$spacingString = '';

	for($i = 0; $i < $module['children']; $i++) $spacingString .= '<td>&nbsp;</td>';

	$colspan = $maxDeep - $module['children']+1;

	// spacing col
	if ( !$is_blocked )
	{
		if($module['contentType'] != CTLABEL_) // chapter head
		{
			if ( strlen($module['name']) > $moduleNameLength)
				$displayedName = substr($module['name'],0,$moduleNameLength)."...";
			else
				$displayedName = $module['name'];

			// bold the title of the current displayed module
			if( $_SESSION['lp_module_id'] == $module['module_id'] )
			{
				$sql = "SELECT M.`name`
				         FROM `".$TABLELEARNPATHMODULE."` AS LPM,
				         `".$TABLEMODULE."` AS M
				         WHERE LPM.`learnPath_module_id` = '" .(int)$module['parent'] ."'
				           AND LPM.`module_id` = M.`module_id`
				           AND LPM.`learnPath_id` = '" . (int)$_SESSION['path_id'] ."'
				       ";
				$currentLabel = db_query_get_single_value($sql);

				$currentName = '<img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" title="'.$contentType_alt.'" border="0" /> '.$displayedName;
				$displayedName = '<b>'.$displayedName.'</b>';
				$previousModule = $previous;

				if($module['credit'] == 'CREDIT' || $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
				{
					$imagePassed = '&nbsp;<img src="'.$imgRepositoryWeb.'tick1.gif" alt="'.$module['lesson_status'].'" title="'.$module['lesson_status'].'" />';
				}
			}
			// store next value if user has the right to access it
			if( $previous == $_SESSION['lp_module_id'] )
			{
				$nextModule = $module['module_id'];
			}
		}
        // a module ALLOW access to the following modules if
        // document module : credit == CREDIT || lesson_status == 'completed'
        // exercise module : credit == CREDIT || lesson_status == 'passed'
        // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

		if( $module['lock'] == 'CLOSE' && $module['credit'] != 'CREDIT' && $module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED' && !$passExercise )
		{
			if($uid)
			{
				$is_blocked = true; // following modules will be unlinked
			}
			else // anonymous : don't display the modules that are unreachable
			{
				$atleastOne = true; // trick to avoid having the "no modules" msg to be displayed
				break ;
			}
		}

	}
	else
	{
		if($module['contentType'] != CTLABEL_)
		{
			if ( strlen($module['name']) > $moduleNameLength)
				$displayedName = substr($module['name'],0,$moduleNameLength).'...';
			else
				$displayedName = $module['name'];
		}
	}

	if (!isset($globalProg)) $globalProg = 0;

	if ($progress > 0)
	{
		$globalProg =  $globalProg+$progress;
	}

	if($module['contentType'] != CTLABEL_ )
	{
		$moduleNb++; // increment number of modules used to compute global progression except if the module is a title
	}

	$atleastOne = true;

	// used in the foreach the remember the id of the previous module_id
	// don't remember if label...
	if ($module['contentType'] != CTLABEL_ )
		$previous = $module['module_id'];

} // end of foreach ($flatElementList as $module)

$prevNextString = ""/*' - '.$currentName.*//*"&nbsp;&nbsp;&nbsp;"*/;

// display previous and next links only if there is more than one module
if ( $moduleNb > 1 )
{
	$imgPrevious = '<img src="'.$imgRepositoryWeb.'previous.gif" border="0" title="'.$langPrevious.'">';
	$imgNext = '<img src="'.$imgRepositoryWeb.'next.gif" border="0" title="'.$langNext.'">';

	if( $previousModule != '' )
	{
		$prevNextString .= '<a href="navigation/viewModule.php?viewModule_id='.$previousModule.'" target="scoFrame">'.$imgPrevious.'</a>';
	}
	else
	{
		$prevNextString .=  $imgPrevious;
	}
	$prevNextString .=  '&nbsp;&nbsp;';

	if( $nextModule != '' )
	{
		$prevNextString .=  '<a href="navigation/viewModule.php?viewModule_id='.$nextModule.'" target="scoFrame">'.$imgNext.'</a>';
	}
	else
	{
		$prevNextString .=  $imgNext;
		$endOfSteps = $langEndOfSteps;
	}
}

//  set redirection link
if ( $is_adminOfCourse )
	$returl = 'learningPathAdmin';
else
	$returl = 'learningPath';

	echo '<table width="99%" align="left" class="LP_Operations">'
    .'<thead>'
    .'<tr>'
    .'<td height="32" width="1"><div align="right">&nbsp;&nbsp;<b>'.$langCommands.'</b>: </div></td>'
    .'<td><div align="left">&nbsp;&nbsp;'
    .$prevNextString
    ."&nbsp;&nbsp;"
	."<a href=\"navigation/viewModule.php?go=".$returl."\" target=\"scoFrame\">"
	.'<img src="'.$imgRepositoryWeb.'scormrestart.jpg" border="0" title="'.$langQuitViewer.'">'
	."</a>"
	.'&nbsp;&nbsp;'
	.'<a href="viewer.php?fullscreen=1" target="_top">'
	.'<img src="'.$imgRepositoryWeb.'scormfullscreen.jpg" border="0" title="'.$langFullScreen.'">'
	.'</a>'
	.'&nbsp;&nbsp;'
	.'<a href="viewer.php?fullscreen=0" target="_top">'
	.'<img src="'.$imgRepositoryWeb.'scormexitfullscreen.jpg" border="0" title="'.$langInFrames.'">'
	.'</a>&nbsp;&nbsp;&nbsp;'."\n"
    .'&nbsp;</div></td>'
    .'<td><div align="center">';

      if($uid) {
	$lpProgress = get_learnPath_progress((int)$_SESSION['path_id'],$uid);
	echo '<small>'
		.$langGlobalProgress
		.disp_progress_bar($lpProgress, 1)
		."&nbsp;".$lpProgress."%"
		.'</small>'."\n"; 
}
      echo'</div></td>'
    .'</tr>'
    .'</thead>'
    .'</table>'
	.'<br /><br />';


echo '<table align="center" width="99%">'
    .'<thead>'
    .'<tr>'
    .'<td>'
    .$learnPath.": ";
if(isset($currentLabel))
	echo "<strong>".$currentLabel."</strong> - ";
if(isset($currentName))
	echo $currentName;
if(isset($imagePassed))
	echo $imagePassed;
echo '</td>'
    .'</tr>'
    .'</thead>';

echo '</table>'
    .'</body></html>'."\n"
     ;

?>
