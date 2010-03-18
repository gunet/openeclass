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
	learningPath.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: learningPath.php Revision: 1.30

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This script displays the contents of a learning path to
                  a user and his progress. If the user is anonymous the
                  progress is not displayed at all.

    @Comments:

    @todo:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");

$require_current_course = TRUE;

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$imgRepositoryWeb       = "../../template/classic/img/";

require_once("../../include/baseTheme.php");

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_LP');
/**************************************/

$tool_content = "";

if (isset($_GET['unit'])) {
	$_SESSION['unit'] = intval($_GET['unit']); 
}

// $_SESSION
if (isset($_GET['path_id']) && $_GET['path_id'] > 0)
{
    $_SESSION['path_id'] = intval($_GET['path_id']);
}
elseif((!isset($_SESSION['path_id']) || $_SESSION['path_id'] == ""))
{
    // if path id not set, redirect user to the home page of learning path
    header("Location: ./learningPathList.php");
    exit();
}

$l = db_query("SELECT name FROM $TABLELEARNPATH WHERE learnPath_id = '".(int)$_SESSION['path_id']."'", $currentCourseID);
$lpname = mysql_fetch_array($l);
$nameTools = $lpname['name'];
if (!add_units_navigation(TRUE)) {
	$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPaths);
}


// permissions (only for the viewmode, there is nothing to edit here )
if ( $is_adminOfCourse )
{
    // if the fct return true it means that user is a course manager and than view mode is set to COURSE_ADMIN
    header("Location: ./learningPathAdmin.php?path_id=".$_SESSION['path_id']);
    exit();
}

mysql_select_db($currentCourseID);

// main page
if ($uid) {
    $uidCheckString = "AND UMP.`user_id` = ".$uid;
}
else // anonymous
{
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

$sql = "SELECT LPM.`learnPath_module_id`, LPM.`parent`,
	LPM.`lock`, M.`module_id`,
	M.`contentType`, M.`name`,
	UMP.`lesson_status`, UMP.`raw`,
	UMP.`scoreMax`, UMP.`credit`, A.`path`
        FROM (`".$TABLEMODULE."` AS M,
	`".$TABLELEARNPATHMODULE."` AS LPM)
     LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
             ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
             ".$uidCheckString."
     LEFT JOIN `".$TABLEASSET."` AS A
            ON M.`startAsset_id` = A.`asset_id`
          WHERE LPM.`module_id` = M.`module_id`
            AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
            AND LPM.`visibility` = 'SHOW'
            AND LPM.`module_id` = M.`module_id`
       GROUP BY LPM.`module_id`
       ORDER BY LPM.`rank`";

if (mysql_num_rows(db_query($sql)) == 0)  {
	$tool_content .= "<p class='alert1'>$langNoModule</p>";
	add_units_navigation();
	draw($tool_content, 2, "learnPath");
	exit;
}


$extendedList = db_query_fetch_all($sql);

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$is_blocked = false;
$moduleNb = 0;

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for( $i = 0 ; $i < sizeof($flatElementList) ; $i++ )
{
	if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
}

/*================================================================
                      OUTPUT STARTS HERE
 ================================================================*/

// comment
if (commentBox(LEARNINGPATH_, DISPLAY_)) {
$tool_content .= "
  <table width=\"99%\" class=\"LearnPathSum\">
  <tbody>
  <tr>
    <th><div align=\"left\">".$langComments."&nbsp;".$langLearningPath1.":</div></th>
  </tr>
  <tr class=\"odd\">
    <td><small>".commentBox(LEARNINGPATH_, DISPLAY_)."</small></td>
  </tr>
  </tbody>
  </table>
  <br />";
}

// --------------------------- module table header --------------------------
$tool_content .= "
    <table width=\"99%\" class=\"LearnPathSum\">
    <tbody>";
$tool_content .= "
    <tr>
      <th colspan=\"".($maxDeep+1)."\"><div align=\"left\"><b>".$langLearningObjects."</b></div></th>\n";


// show only progress column for authenticated users
if ($uid) {
    $tool_content .= '      <th colspan="2" width="25%"><b>'.$langProgress.'</b></th>'."\n";
}

$tool_content .= "    </tr>\n";

// ------------------ module table list display -----------------------------------
if (!isset($globalProg)) $globalProg = 0;

foreach ($flatElementList as $module)
{
    if( $module['scoreMax'] > 0 && $module['raw'] > 0 )
    {
        $progress = round($module['raw']/$module['scoreMax']*100);
    }
    else
    {
        $progress = 0;
    }

    if ( $module['contentType'] == CTEXERCISE_ )
    {
        $passExercise = ($module['credit'] == "CREDIT");
    }
    else
    {
        $passExercise = false;
    }

    if ( $module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0)
    {
        if ( $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
        {
            $progress = 100;
            $passExercise = true;
        }
        else
        {
            $progress = 0;
            $passExercise = false;
        }
    }

    // display the current module name (and link if allowed)
    $spacingString = "";
    for($i = 0; $i < $module['children']; $i++)
    {
        $spacingString .= "\n      <td width=\"5\">&nbsp;</td>";
    }

    $colspan = $maxDeep - $module['children']+1;

    $tool_content .= "    <tr>".$spacingString."
      <td colspan=\"".$colspan."\" align=\"left\">";

    //-- if chapter head
    if ( $module['contentType'] == CTLABEL_ )
    {
        $tool_content .= '<b>'.htmlspecialchars($module['name']).'</b>'."";
    }
    //-- if user can access module
    elseif ( !$is_blocked )
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
        $tool_content .= '<span style="vertical-align: middle;"><img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" title="'.$contentType_alt.'" border="0" /></span>&nbsp;'
        .'<a href="module.php?module_id='.$module['module_id'].'">'.htmlspecialchars($module['name']).'</a>'."";
        // a module ALLOW access to the following modules if
        // document module : credit == CREDIT || lesson_status == 'completed'
        // exercise module : credit == CREDIT || lesson_status == 'passed'
        // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

        if( $module['lock'] == 'CLOSE' && $module['credit'] != 'CREDIT'
            && $module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED'
            && !$passExercise
          )
        {
            if($uid)
            {
                $is_blocked = true; // following modules will be unlinked
            }
            else // anonymous : don't display the modules that are unreachable
            {
                break ;
            }
        }
    }
    //-- user is blocked by previous module, don't display link
    else
    {
        if($module['contentType'] == CTEXERCISE_ )
            $moduleImg = 'exercise_on.gif';
        else if($module['contentType'] == CTLINK_ )
        	$moduleImg = "links_on.gif";
        else if($module['contentType'] == CTCOURSE_DESCRIPTION_ )
        	$moduleImg = "description_on.gif";
       else
            $moduleImg = choose_image(basename($module['path']));

        $tool_content .= '<span style="vertical-align: middle;"><img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" title="'.$contentType_alt.'" border="0" /></span>'." "
             .htmlspecialchars($module['name']);
    }
    $tool_content .= '</td>'."\n";

    if( $uid && ($module['contentType'] != CTLABEL_) )
    {
        // display the progress value for current module
        $tool_content .= '      <td align="right">'.disp_progress_bar ($progress, 1).'</td>'."\n"
        	.'      <td align="left">'
			.'<small>&nbsp;'.$progress.'%</small>'
			.'</td>'."\n";
    }
    elseif( $uid && $module['contentType'] == CTLABEL_ )
    {
        $tool_content .= '      <td colspan="2">&nbsp;</td>'."\n";
    }

    if ($progress > 0)
    {
        $globalProg =  $globalProg+$progress;
    }

    if($module['contentType'] != CTLABEL_)
        $moduleNb++; // increment number of modules used to compute global progression except if the module is a title

    $tool_content .= '    </tr>'."\n";
}




if($uid && $moduleNb > 0) {
    // add a blank line between module progression and global progression
    $tool_content .= '    <tr class="odd">'."\n"
		.'      <td colspan="'.($maxDeep+1).'" align="right"><small><b>'.$langGlobalProgress.'</b></small></td>'."\n"
		.'      <td align="right">'
        .disp_progress_bar(round($globalProg / ($moduleNb) ), 1 )
		.'</td>'."\n"
		.'      <td align="left">'
		.'<small>&nbsp;'.round($globalProg / ($moduleNb) ) .'%</small>'
		.'</td>'."\n"
		.'    </tr>'."\n\n"
		.'    </tbody>'."\n\n";
}
$tool_content .= '    </table>'."\n\n";
draw($tool_content, 2, "learnPath");
?>
