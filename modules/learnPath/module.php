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
	module.php
	@last update: 29-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: module.php Revision: 1.26

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This script provides information on the progress of a
                  learning path module and then launches navigation for it.
                  It also displays some extra option for the teacher.

    @Comments:

    @todo:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");
require_once("../../include/lib/fileManageLib.inc.php");
require_once("../../include/lib/fileUploadLib.inc.php");

$require_current_course = TRUE;

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$TABLEQUIZTEST          = "exercices";
$dbTable                = $TABLEASSET; // for old functions of document tool

$imgRepositoryWeb       = "../../template/classic/img/";

require_once("../../include/baseTheme.php");
$tool_content = "";
$head_content = "";
$body_action = "";

$nameTools = $langLearningObject;
if (!add_units_navigation()) {
	$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPaths);
	if ($is_adminOfCourse)
	{
	$navigation[]= array ("url"=>"learningPathAdmin.php", "name"=> $langAdm);
	}
	else
	{
	$navigation[]= array ("url"=>"learningPath.php", "name"=> $langAdm);
	}
}


if ( isset($_GET['path_id']) && $_GET['path_id'] != '' )
{
    $_SESSION['path_id'] = $_GET['path_id'];
}
// module_id
if ( isset($_GET['module_id']) && $_GET['module_id'] != '')
{
    $_SESSION['lp_module_id'] = $_GET['module_id'];
}

mysql_select_db($currentCourseID);

// main page
// FIRST WE SEE IF USER MUST SKIP THE PRESENTATION PAGE OR NOT
// triggers are : if there is no introdution text or no user module progression statistics yet and user is not admin,
// then there is nothing to show and we must enter in the module without displaying this page.

/*
 *  GET INFOS ABOUT MODULE and LEARNPATH_MODULE
 */

// check in the DB if there is a comment set for this module in general

$sql = "SELECT `comment`, `startAsset_id`, `contentType`
        FROM `".$TABLEMODULE."`
        WHERE `module_id` = ". (int)$_SESSION['lp_module_id'];

$module = db_query_get_single_row($sql);

if( empty($module['comment']) || $module['comment'] == $langDefaultModuleComment )
{
  	$noModuleComment = true;
}
else
{
   $noModuleComment = false;
}


if( $module['startAsset_id'] == 0 )
{
    $noStartAsset = true;
}
else
{
    $noStartAsset = false;
}


// check if there is a specific comment for this module in this path
$sql = "SELECT `specificComment`
        FROM `".$TABLELEARNPATHMODULE."`
        WHERE `module_id` = ". (int)$_SESSION['lp_module_id'];

$learnpath_module = db_query_get_single_row($sql);

if( empty($learnpath_module['specificComment']) || $learnpath_module['specificComment'] == $langDefaultModuleAddedComment )
{
	$noModuleSpecificComment = true;
}
else
{
    $noModuleSpecificComment = false;
}

// check in DB if user has already browsed this module

$sql = "SELECT `contentType`,
	`total_time`,
	`session_time`,
	`scoreMax`,
	`raw`,
	`lesson_status`
        FROM `".$TABLEUSERMODULEPROGRESS."` AS UMP,
             `".$TABLELEARNPATHMODULE."` AS LPM,
             `".$TABLEMODULE."` AS M
        WHERE UMP.`user_id` = '$uid'
          AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
          AND LPM.`learnPath_id` = ".(int)$_SESSION['path_id']."
          AND LPM.`module_id` = ". (int)$_SESSION['lp_module_id']."
          AND LPM.`module_id` = M.`module_id`
             ";
$resultBrowsed = db_query_get_single_row($sql);

        if ($module['contentType']== CTSCORM_ || $module['contentType']== CTSCORMASSET_) { $nameTools = "$langModify $langSCORMTypeDesc"; }
        if ($module['contentType']== CTEXERCISE_ ) { $nameTools = "$langModify $langExerciseAsModuleLabel"; }
        if ($module['contentType']== CTDOCUMENT_ ) { $nameTools = "$langModify $langDocumentAsModuleLabel"; }
        if ($module['contentType']== CTLINK_ ) { $nameTools = "$langModify $langLinkAsModuleLabel"; }
        if ($module['contentType']== CTCOURSE_DESCRIPTION_ ) { $nameTools = "$langModify $langCourseDescriptionAsModuleLabel"; }
        if ($module['contentType']== CTLABEL_ ) { $nameTools = "$langModify $langModuleOfMyCourseLabel_onom"; }



// redirect user to the path browser if needed
if( !$is_adminOfCourse
	&& ( !is_array($resultBrowsed) || !$resultBrowsed || count($resultBrowsed) <= 0 )
	&& $noModuleComment
	&& $noModuleSpecificComment
	&& !$noStartAsset
	)
{
    header("Location:./viewer.php");
    exit();
}

$tool_content .="
    <table width=\"99%\" class=\"FormData\">
    <tbody>
    <tr>
      <th width=\"220\" class=\"left\" height=\"32\"></th>
      <td class=\"left\"><b>$langLearningObjectData</b></td>
    </tr>";
//################################## MODULE NAME BOX #################################\\
$tool_content .="
    <tr>
      <th class=\"left\" height=\"32\">$langTitle :</th>
      <td>";
      $cmd = ( isset($_REQUEST['cmd']) && is_string($_REQUEST['cmd']) )? (string)$_REQUEST['cmd'] : '';

if ($cmd == "updateName")
{
    $tool_content .= "".disp_message_box1(nameBox(MODULE_, UPDATE_, $langModify))."";
}
else
{
    $tool_content .= "".nameBox(MODULE_, DISPLAY_)."";
}

$tool_content .= "
      </td>
    </tr>";
$tool_content .="
    <tr>
      <th class=\"left\" height=\"32\">$langComments :</th>
      <td class=\"left\">";
if($module['contentType'] != CTLABEL_ )
{

    //############################### MODULE COMMENT BOX #################################\\
    //#### COMMENT #### courseAdmin cannot modify this if this is a imported module ####\\
    // this the comment of the module in ALL learning paths
    if ( $cmd == "updatecomment" )
    {
        $tool_content .= "".commentBox(MODULE_, UPDATE_)."";
        $head_content .= disp_html_area_head("insertCommentBox");
		$body_action = "onload=\"initEditor()\"";
    }
    elseif ($cmd == "delcomment" )
    {
        $tool_content .= "".commentBox(MODULE_, DELETE_)."";
    }
    else
    {
        $tool_content .= "".commentBox(MODULE_, DISPLAY_)."";
    }
$tool_content .="
      </td>
    </tr>";
$tool_content .="
    <tr>
      <th class=\"left\" height=\"32\">$langComments - $langInstructions:<br /><small>($langModuleComment_inCurrentLP)</small></th>
      <td class=\"left\">";
    //#### ADDED COMMENT #### courseAdmin can always modify this ####\\
    // this is a comment for THIS module in THIS learning path
    if ( $cmd == "updatespecificComment" )
    {
        $tool_content .= "".commentBox(LEARNINGPATHMODULE_, UPDATE_)."";
        $head_content .= disp_html_area_head("insertCommentBox");
		$body_action = "onload=\"initEditor()\"";
    }
    elseif ($cmd == "delspecificComment" )
    {
        $tool_content .= "".commentBox(LEARNINGPATHMODULE_, DELETE_)."";
    }
    else
    {
        $tool_content .= "".commentBox(LEARNINGPATHMODULE_, DISPLAY_)."";
    }
} //  if($module['contentType'] != CTLABEL_ )

$tool_content .= "
      </td>
    </tr>";
    $tool_content .="
    <tr>
      <th class=\"left\">$langProgInModuleTitle :</th>
      <td>";
//############################ PROGRESS  AND  START LINK #############################\\

/* Display PROGRESS */

if($module['contentType'] != CTLABEL_) //
{

    if( $resultBrowsed && count($resultBrowsed) > 0 && $module['contentType'] != CTLABEL_)
    {
        $contentType_img = selectImage($resultBrowsed['contentType']);
        $contentType_alt = selectAlt($resultBrowsed['contentType']);

        if ($resultBrowsed['contentType']== CTSCORM_ || $resultBrowsed['contentType']== CTSCORMASSET_ ) { $contentDescType = $langSCORMTypeDesc;    }
        if ($resultBrowsed['contentType']== CTEXERCISE_ ) { $contentDescType = $langEXERCISETypeDesc; }
        if ($resultBrowsed['contentType']== CTDOCUMENT_ ) { $contentDescType = $langDOCUMENTTypeDesc; }
        if ($resultBrowsed['contentType']== CTLINK_ ) { $contentDescType = $langLINKTypeDesc; }
        if ($resultBrowsed['contentType']== CTCOURSE_DESCRIPTION_ ) { $contentDescType = $langDescriptionCours; }


		$tool_content .= ''."\n\n"
			.'        <table class="LearnPathSum">'."\n"
			.'        <thead>'."\n"
			.'        <tr class="LP_header">'."\n"
			.'          <td><div align="center">'.$langInfoProgNameTitle.'</div></td>'."\n"
			.'          <td>&nbsp;</td>'."\n"
			.'          <td><div align="center">'.$langPersoValue.'</div></td>'."\n"
			.'        </tr>'."\n"
			.'        </thead>'."\n\n"
			.'        <tbody>'."\n\n";

        //display type of the module
		$tool_content .= '        <tr>'."\n"
            .'          <td>'.$langTypeOfModule.'</td>'."\n"
            .'          <td>:</td>'."\n"
			.'          <td align="right"><img src="'.$imgRepositoryWeb.$contentType_img.'" alt="'.$contentType_alt.'" title="'.$contentType_alt.'" border="0" />&nbsp;&nbsp;'.$contentDescType.'</td>'."\n"
			.'        </tr>'."\n\n";

        //display total time already spent in the module
		$tool_content .= '        <tr>'."\n"
			.'          <td>'.$langTotalTimeSpent.'</td>'."\n"
            .'          <td>:</td>'."\n"
			.'          <td align="right">'.$resultBrowsed['total_time'].'</td>'."\n"
			.'        </tr>'."\n\n";

        //display time passed in last session
		$tool_content .= '        <tr>'."\n"
			.'          <td>'.$langLastSessionTimeSpent.'</td>'."\n"
            .'          <td>:</td>'."\n"
			.'          <td align="right">'.$resultBrowsed['session_time'].'</td>'."\n"
			.'        </tr>'."\n\n";

        //display user best score
        if ($resultBrowsed['scoreMax'] > 0)
        {
		$raw = round($resultBrowsed['raw']/$resultBrowsed['scoreMax']*100);
        }
        else
        {
		$raw = 0;
        }

        $raw = max($raw, 0);

        if (($resultBrowsed['contentType'] == CTSCORM_ ) && ($resultBrowsed['scoreMax'] <= 0)
            &&  ((($resultBrowsed['lesson_status'] == "COMPLETED") || ($resultBrowsed['lesson_status'] == "PASSED") ) || ($resultBrowsed['raw'] != -1) ) )
        {
		$raw = 100;
        }

        // no sens to display a score in case of a document module
        if ( ($resultBrowsed['contentType'] != CTDOCUMENT_) &&
             ($resultBrowsed['contentType'] != CTLINK_) &&
             ($resultBrowsed['contentType'] != CTCOURSE_DESCRIPTION_)
           )
        {
		$tool_content .= '<tr>'."\n"
			.'          <td>'.$langYourBestScore.'</td>'."\n"
            .'          <td>:</td>'."\n"
			.'          <td>'.disp_progress_bar($raw, 1).' '.$raw.'%</td>'."\n"
			.'        </tr>'."\n\n";
        }

        //display lesson status

        // display a human readable string ...

		if ($resultBrowsed['lesson_status']=="NOT ATTEMPTED") {
			$statusToDisplay = $langNotAttempted;
		}
		else if ($resultBrowsed['lesson_status']=="PASSED") {
			$statusToDisplay = $langPassed;
		}
		else if ($resultBrowsed['lesson_status']=="FAILED") {
			$statusToDisplay = $langFailed;
		}
		else if ($resultBrowsed['lesson_status']=="COMPLETED") {
			$statusToDisplay = $langAlreadyBrowsed;
		}
		else if ($resultBrowsed['lesson_status']=="BROWSED") {
			$statusToDisplay = $langAlreadyBrowsed;
		}
		else if ($resultBrowsed['lesson_status']=="INCOMPLETE") {
			$statusToDisplay = $langNeverBrowsed;
		}
        else {
            $statusToDisplay = $resultBrowsed['lesson_status'];
        }

		$tool_content .= '        <tr>'."\n"
			.'          <td>'.$langLessonStatus.'</td>'."\n"
            .'          <td>:</td>'."\n"
			.'          <td align="right">'.$statusToDisplay.'</td>'."\n"
			.'        </tr>'."\n\n"
			.'        </tbody>'."\n\n"
			.'        </table>'."\n\n";

    } //end display stats

    /* START */
    // check if module.startAssed_id is set and if an asset has the corresponding asset_id
    // asset_id exists ?  for the good module  ?
    $sql = "SELECT `asset_id`
              FROM `".$TABLEASSET."`
             WHERE `asset_id` = ". (int)$module['startAsset_id']."
               AND `module_id` = ". (int)$_SESSION['lp_module_id'];

	$asset = db_query_get_single_row($sql);
$tool_content .= "
      </td>
    </tr>";
    $tool_content .="
    <tr>
      <th class=\"left\">$langPreview :</th>
      <td>";
    if( $module['startAsset_id'] != "" && $asset['asset_id'] == $module['startAsset_id'] )
    {
	$tool_content .= ''."\n"
		.'        <form action="./viewer.php" method="post">'."\n"
		.'        <input type="submit" value="'.$langStartModule.'" />'."\n"
		.'        </form>'."\n";
    }
    else
    {
        $tool_content .= '        <p><center>'.$langNoStartAsset.'</center></p>'."\n";
    }
}// end if($module['contentType'] != CTLABEL_)
// if module is a label, only allow to change its name.
$tool_content .= "
      </td>
    </tr>";
//####################################################################################\\
//################################# ADMIN DISPLAY ####################################\\
//####################################################################################\\
/*
if( $is_adminOfCourse ) // for teacher only
{
    switch ($module['contentType'])
    {
        case CTDOCUMENT_ :
            require_once("./include/document.inc.php");
            break;
        case CTEXERCISE_ :
            require_once("./include/exercise.inc.php");
            break;
        case CTSCORM_ :
            require_once("./include/scorm.inc.php");
            break;
        case CTCLARODOC_ :
        case CTLABEL_ :
        case CTCOURSE_DESCRIPTION_ :
        case CTLINK_:
       	break;
    }
} // if ($is_adminOfCourse)
*/

    $tool_content .= "
    </tbody>
    </table>";
//back button
if ($is_adminOfCourse)
{
	$pathBack = "./learningPathAdmin.php";
}
else
{
	$pathBack = "./learningPath.php";
}
		$tool_content .= "
    <br />
    <p align=\"right\"><a href=\"".$pathBack."\">".$langBackToLPAdmin."</p>";
draw($tool_content, 2, "learnPath", $head_content, $body_action);
?>
