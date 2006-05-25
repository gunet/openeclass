<?php

/*
Header, Copyright, etc ...
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");
require_once("../../include/lib/fileManageLib.inc.php");
require_once("../../include/lib/fileUploadLib.inc.php");

$require_current_course = TRUE;
$langFiles              = "learnPath";

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$TABLEQUIZTEST          = "exercices";
$dbTable                = $TABLEASSET; // for old functions of document tool

$imgRepositoryWeb       = "../../images/";

require_once("../../include/baseTheme.php");
$tool_content = "";

$nameTools = $langModule;
$is_AllowedToEdit = $is_adminOfCourse;
$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);
if ( $is_AllowedToEdit )
{
    $navigation[]= array ("url"=>"learningPathAdmin.php", "name"=> $langLearningPath);
}
else
{
    $navigation[]= array ("url"=>"learningPath.php", "name"=> $langLearningPath);
}

// $_SESSION
// path_id
if ( isset($_GET['path_id']) && $_GET['path_id'] != '' )
{
    $_SESSION['path_id'] = $_GET['path_id'];
}
// module_id
if ( isset($_GET['module_id']) && $_GET['module_id'] != '')
{
    $_SESSION['module_id'] = $_GET['module_id'];
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
        WHERE `module_id` = ". (int)$_SESSION['module_id'];

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
        WHERE `module_id` = ". (int)$_SESSION['module_id'];

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
          AND LPM.`module_id` = ". (int)$_SESSION['module_id']."
          AND LPM.`module_id` = M.`module_id`
             ";
$resultBrowsed = db_query_get_single_row($sql);

// redirect user to the path browser if needed
if( !$is_AllowedToEdit
	&& ( !is_array($resultBrowsed) || !$resultBrowsed || count($resultBrowsed) <= 0 )
	&& $noModuleComment
	&& $noModuleSpecificComment
	&& !$noStartAsset
	)
{
    header("Location:./viewer.php");
    exit();
}

//####################################################################################\\
//################################## MODULE NAME BOX #################################\\
//####################################################################################\\

$cmd = ( isset($_REQUEST['cmd']) )? $_REQUEST['cmd'] : '';

$tool_content .= "<div id=\"tool_operations\"><span class=\"operation\">";

if ( $cmd == "updateName" )
{
    $tool_content .= nameBox(MODULE_, UPDATE_);
}
else
{
    $tool_content .= nameBox(MODULE_, DISPLAY_);
}
$tool_content .= "<br />";
if($module['contentType'] != CTLABEL_ )
{
    //####################################################################################\\
    //############################### MODULE COMMENT BOX #################################\\
    //####################################################################################\\
    //#### COMMENT #### courseAdmin cannot modify this if this is a imported module ####\\
    // this the comment of the module in ALL learning paths
    if ( $cmd == "updatecomment" )
    {
        $tool_content .= commentBox(MODULE_, UPDATE_);
    }
    elseif ($cmd == "delcomment" )
    {
        $tool_content .= commentBox(MODULE_, DELETE_);
    }
    else
    {
        $tool_content .= commentBox(MODULE_, DISPLAY_);
    }
$tool_content .= "<br />";
    //#### ADDED COMMENT #### courseAdmin can always modify this ####\\
    // this is a comment for THIS module in THIS learning path
    if ( $cmd == "updatespecificComment" )
    {
        $tool_content .= commentBox(LEARNINGPATHMODULE_, UPDATE_);
    }
    elseif ($cmd == "delspecificComment" )
    {
        $tool_content .= commentBox(LEARNINGPATHMODULE_, DELETE_);
    }
    else
    {
        $tool_content .= commentBox(LEARNINGPATHMODULE_, DISPLAY_);
    }
$tool_content .= "<br />";
} //  if($module['contentType'] != CTLABEL_ )


//back button
if ($is_AllowedToEdit)
{
	$pathBack = "./learningPathAdmin.php";
}
else
{
	$pathBack = "./learningPath.php";
}

$tool_content .= '<small><a href="'.$pathBack.'"><< '.$langBackModule.'</a></small>'."\n\n";
$tool_content .= "</span></div>";
//####################################################################################\\
//############################ PROGRESS  AND  START LINK #############################\\
//####################################################################################\\

/* Display PROGRESS */

if($module['contentType'] != CTLABEL_) //
{ 
    if( $resultBrowsed && count($resultBrowsed) > 0 && $module['contentType'] != CTLABEL_)
    {
        $contentType_img = selectImage($resultBrowsed['contentType']);
        $contentType_alt = selectAlt($resultBrowsed['contentType']);

        if ($resultBrowsed['contentType']== CTSCORM_   ) { $contentDescType = $langSCORMTypeDesc;    }
        if ($resultBrowsed['contentType']== CTEXERCISE_ ) { $contentDescType = $langEXERCISETypeDesc; }
        if ($resultBrowsed['contentType']== CTDOCUMENT_ ) { $contentDescType = $langDOCUMENTTypeDesc; }

		$tool_content .= '<strong>'.$langProgInModuleTitle.'</strong><br /><br />'."\n\n"
			.'<table align="center">'."\n"
			.'<thead>'."\n"
			.'<tr>'."\n"
			.'<th>'.$langInfoProgNameTitle.'</th>'."\n"
			.'<th>'.$langPersoValue.'</th>'."\n"
			.'</tr>'."\n"
			.'</thead>'."\n\n"
			.'<tbody>'."\n\n";

        //display type of the module
		$tool_content .= '<tr>'."\n"
            .'<td>'.$langTypeOfModule.'</td>'."\n"
			.'<td><img src="'.$imgRepositoryWeb.$contentType_img.'" alt="'.$contentType_alt.'" border="0" />'.$contentDescType.'</td>'."\n"
			.'</tr>'."\n\n";

        //display total time already spent in the module
		$tool_content .= '<tr>'."\n"
			.'<td>'.$langTotalTimeSpent.'</td>'."\n"
			.'<td>'.$resultBrowsed['total_time'].'</td>'."\n"
			.'</tr>'."\n\n";

        //display time passed in last session
		$tool_content .= '<tr>'."\n"
			.'<td>'.$langLastSessionTimeSpent.'</td>'."\n"
			.'<td>'.$resultBrowsed['session_time'].'</td>'."\n"
			.'</tr>'."\n\n";
			
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
            &&  (  ( ($resultBrowsed['lesson_status'] == "COMPLETED") || ($resultBrowsed['lesson_status'] == "PASSED") ) || ($resultBrowsed['raw'] != -1) ) )
        {
			$raw = 100;
        }

        // no sens to display a score in case of a document module
        if (($resultBrowsed['contentType'] != CTDOCUMENT_))
        {
			$tool_content .= '<tr>'."\n"
				.'<td>'.$langYourBestScore.'</td>'."\n"
				.'<td>'.claro_disp_progress_bar($raw, 1).' '.$raw.'%</td>'."\n"
				.'</tr>'."\n\n";
        }

        //display lesson status

        // document are just browsed or not, but not completed or passed...

        if (($resultBrowsed['contentType']== CTDOCUMENT_))
        {
            if ($resultBrowsed['lesson_status']=="COMPLETED")
            {
                $statusToDisplay = $langAlreadyBrowsed;
            }
            else
            {
                $statusToDisplay = $langNeverBrowsed;
            }
        }
        else
        {
            $statusToDisplay = $resultBrowsed['lesson_status'];
        }
		$tool_content .= '<tr>'."\n"
			.'<td>'.$langLessonStatus.'</td>'."\n"
			.'<td>'.$statusToDisplay.'</td>'."\n"
			.'</tr>'."\n\n"
			.'</tbody>'."\n\n"
			.'</table>'."\n\n";

    } //end display stats

    /* START */
    // check if module.startAssed_id is set and if an asset has the corresponding asset_id
    // asset_id exists ?  for the good module  ?
    $sql = "SELECT `asset_id`
              FROM `".$TABLEASSET."`
             WHERE `asset_id` = ". (int)$module['startAsset_id']."
               AND `module_id` = ". (int)$_SESSION['module_id'];

	$asset = db_query_get_single_row($sql);

    if( $module['startAsset_id'] != "" && $asset['asset_id'] == $module['startAsset_id'] )
    {

		$tool_content .= '<center>'."\n"
			.'<form action="./viewer.php" method="post">'."\n"
			.'<input type="submit" value="'.$langStartModule.'" />'."\n"
			.'</form>'."\n"
			.'</center>'."\n\n";
    }
    else
    {
        $tool_content .= '<p><center>'.$langNoStartAsset.'</center></p>'."\n";
    }
}// end if($module['contentType'] != CTLABEL_) 
// if module is a label, only allow to change its name.
  
//####################################################################################\\
//################################# ADMIN DISPLAY ####################################\\
//####################################################################################\\

if( $is_AllowedToEdit ) // for teacher only
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
            break;
        case CTLABEL_ :
            break;
        case CTLINK_:
        	break;
    }
} // if ($is_AllowedToEdit)

draw($tool_content, 2, "learnPath");

?>
