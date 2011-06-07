<?php
/* ========================================================================
 * Open eClass 2.4
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
	viewer_toc.php
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

if($uid)
	$uidCheckString = "AND UMP.`user_id` = ". (int)$uid;
else // anonymous
   $uidCheckString = "AND UMP.`user_id` IS NULL ";

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
$moduleNb = 0;

// get the name of the learning path
$sql = "SELECT `name`
      FROM `".$TABLELEARNPATH."`
      WHERE `learnPath_id` = '". (int)$_SESSION['path_id']."'";

$lpName = db_query_get_single_value($sql);
$learnPath =  '<strong>'.$lpName.'</strong>';

$previous = ""; // temp id of previous module, used as a buffer in foreach
$previousModule = ""; // module id that will be used in the previous link
$nextModule = ""; // module id that will be used in the next link

foreach ($flatElementList as $module)
{
	if ( $module['contentType'] == CTEXERCISE_ )
		$passExercise = ($module['credit']=='CREDIT');
	else
		$passExercise = false;

	if ( $module['contentType'] == CTSCORM_ )
	{
		if ( $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
			$passExercise = true;
		else
			$passExercise = false;
	}

	// spacing col
	if ( !$is_blocked )
	{
		if($module['contentType'] != CTLABEL_) // chapter head
		{
			// bold the title of the current displayed module
			if( $_SESSION['lp_module_id'] == $module['module_id'] )
			{
				$previousModule = $previous;
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
				$is_blocked = true; // following modules will be unlinked
			else // anonymous : don't display the modules that are unreachable
				break;
		}
	}

	if($module['contentType'] != CTLABEL_ )
		$moduleNb++; // increment number of modules used to compute global progression except if the module is a title

	// used in the foreach the remember the id of the previous module_id
	// don't remember if label...
	if ($module['contentType'] != CTLABEL_ )
		$previous = $module['module_id'];

} // end of foreach ($flatElementList as $module)

$prevNextString = "";
// display previous and next links only if there is more than one module
if ( $moduleNb > 1 )
{
	$imgPrevious = '<img src="'.$imgRepositoryWeb.'lp/back.png" alt="'.$langPrevious.'" title="'.$langPrevious.'">';
	$imgNext = '<img src="'.$imgRepositoryWeb.'lp/next.png" alt="'.$langNext.'" title="'.$langNext.'">';

	if( $previousModule != '' )
		$prevNextString .= '<a href="navigation/viewModule.php?course='.$code_cours.'&amp;viewModule_id='.$previousModule.'" target="scoFrame">'.$imgPrevious.'</a>';
	else
		$prevNextString .=  $imgPrevious;
	$prevNextString .=  '&nbsp;';

	if( $nextModule != '' )
		$prevNextString .=  '<a href="navigation/viewModule.php?course='.$code_cours.'&amp;viewModule_id='.$nextModule.'" target="scoFrame">'.$imgNext.'</a>';
	else
		$prevNextString .=  $imgNext;
}

//  set redirection link
$returl = ($is_adminOfCourse) ? 'learningPathAdmin' : 'learningPath';


echo '<html>'."\n"
    .'<head>'."\n"
    .'<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">'."\n"
    .'<link href="lp.css" rel="stylesheet" type="text/css" />'."\n"
    .'</head>'."\n"
    .'<body>'."\n"
    .'<div class="header">'."\n"
    .'<div class="tools">'."\n";
	
echo '<div class="lp_right">'.$prevNextString
	.'&nbsp;<a href="navigation/viewModule.php?course='.$code_cours.'&amp;go='.$returl.'" target="scoFrame"><img src="'.$imgRepositoryWeb.'lp/nofullscreen.png" alt="'.$langQuitViewer.'" title="'.$langQuitViewer.'" /></a>
	</div>';

echo "<div class='lp_left'><a href=\"". $urlAppend ."/courses/". $currentCourseID ."/\" target='_top'><strong>$currentCourseName</strong></a></div>";

echo "<div class='clear'></div>";

echo "<div class='logo'><img src=\"".$imgRepositoryWeb."lp/logo_openeclass.png\" alt='' title='' /></div>";

echo "<div class='lp_right_grey'>$learnPath";
if($uid) {
	$lpProgress = get_learnPath_progress((int)$_SESSION['path_id'],$uid);
	echo ": ". disp_progress_bar($lpProgress, 1) ."&nbsp;". $lpProgress ."%";
}
echo "</div>";

echo "</div></div></body></html>";

?>
