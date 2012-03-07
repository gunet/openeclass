<?php
/* ========================================================================
 * Open eClass 2.5
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
	learningPathAdmin.php
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: learningPathAdmin.php Revision: 1.40.2.1

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This file is available only to the course admin

                  It allow course admin to :
                  - change learning path name
                  - change learning path comment
                  - links to
                    - create empty module
                    - use document as module
                    - use exercice as module
                    - use link as module
                    - use course description as module
                    - re-use a module of the same course
                  - remove modules from learning path (it doesn't delete it ! )
                  - change locking , visibility, order
                  - access to config page of modules in this learning path

    @Comments:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");

$require_current_course = TRUE;

$TABLELEARNPATH         = 'lp_learnPath';
$TABLEMODULE            = 'lp_module';
$TABLELEARNPATHMODULE   = 'lp_rel_learnPath_module';
$TABLEASSET             = 'lp_asset';
$TABLEUSERMODULEPROGRESS= 'lp_user_module_progress';

require_once '../../include/baseTheme.php';

$body_action = '';
$dialogBox = '';

if (!add_units_navigation()) {
	$nameTools = $langAdm;
	$navigation[] = array("url"=>"learningPathList.php?course=$code_cours", "name"=> $langLearningPaths);
}

// $_SESSION
if ( isset($_GET['path_id']) && $_GET['path_id'] > 0 )
{
      $_SESSION['path_id'] = (int) $_GET['path_id'];
}

// get user out of here if he is not allowed to edit
if ( !$is_editor )
{
    if ( isset($_SESSION['path_id']) )
    {
        header("Location: ./learningPath.php?course=$code_cours&path_id=".$_SESSION['path_id']);
    }
    else
    {
        header("Location: ./learningPathList.php?course=$code_cours");
    }
    exit();
}

mysql_select_db($currentCourseID);

load_js('tools.js');

$cmd = ( isset($_REQUEST['cmd']) )? $_REQUEST['cmd'] : '';

switch($cmd)
{
    // MODULE DELETE
    case "delModule" :
        //--- BUILD ARBORESCENCE OF MODULES IN LEARNING PATH
        $sql = "SELECT M.*, LPM.*
                FROM `".$TABLEMODULE."` AS M, `".$TABLELEARNPATHMODULE."` AS LPM
                WHERE M.`module_id` = LPM.`module_id`
                AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
                ORDER BY LPM.`rank` ASC";
        $result = db_query($sql);

        $extendedList = array();
        while ($list = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $extendedList[] = $list;
        }

        //-- delete module cmdid and his children if it is a label
        // get the modules tree ( cmdid module and all its children)
        //$temp[0] = get_module_tree( build_element_list($extendedList, 'parent', 'learnPath_module_id'), $_REQUEST['cmdid'] , 'learnPath_module_id');
        $temp[0] = get_module_tree( build_element_list($extendedList, 'parent', 'learnPath_module_id'), $_REQUEST['cmdid'] , 'learnPath_module_id');
        // delete the tree
        delete_module_tree($temp);

        break;

    // VISIBILITY COMMAND
    case "mkVisibl" :
    case "mkInvisibl" :
        $cmd == "mkVisibl" ? $visibility = 'SHOW' : $visibility = 'HIDE';
        //--- BUILD ARBORESCENCE OF MODULES IN LEARNING PATH
        $sql = "SELECT M.*, LPM.*
                FROM `".$TABLEMODULE."` AS M, `".$TABLELEARNPATHMODULE."` AS LPM
                WHERE M.`module_id` = LPM.`module_id`
                AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id'] ."
                ORDER BY LPM.`rank` ASC";
        $result = db_query($sql);

        $extendedList = array();
        while ($list = mysql_fetch_array($result, MYSQL_ASSOC))
        {
          $extendedList[] = $list;
        }

        //-- set the visibility for module cmdid and his children if it is a label
        // get the modules tree ( cmdid module and all its children)
        $temp[0] = get_module_tree( build_element_list($extendedList, 'parent', 'learnPath_module_id'), $_REQUEST['cmdid'] );
        // change the visibility according to the new father visibility
        set_module_tree_visibility( $temp, $visibility);

        break;

    // ACCESSIBILITY COMMAND
    case "mkBlock" :
    case "mkUnblock" :
        $cmd == "mkBlock" ? $blocking = 'CLOSE' : $blocking = 'OPEN';
        $sql = "UPDATE `".$TABLELEARNPATHMODULE."`
                SET `lock` = '$blocking'
                WHERE `learnPath_module_id` = ". (int)$_REQUEST['cmdid']."
                AND `lock` != '$blocking'";
        $query = db_query ($sql);
        break;

    // ORDER COMMAND
    case "changePos" :
        // changePos form sent
        if( isset($_POST["newPos"]) && $_POST["newPos"] != "")
        {
            // get order of parent module
            $sql = "SELECT *
                    FROM `".$TABLELEARNPATHMODULE."`
                    WHERE `learnPath_module_id` = ". (int)$_REQUEST['cmdid'];
            $temp = db_query_fetch_all($sql);
            $movedModule = $temp[0];

            // if origin and target are the same ... cancel operation
            if ($movedModule['learnPath_module_id'] == $_POST['newPos'])
            {
                $dialogBox .= $langWrongOperation;
            }
            else
            {
                //--
                // select max order
                // get the max rank of the children of the new parent of this module
                $sql = "SELECT MAX(`rank`)
                        FROM `".$TABLELEARNPATHMODULE."`
                        WHERE `parent` = ". (int)$_POST['newPos'];

                $result = db_query($sql);

                list($orderMax) = mysql_fetch_row($result);
                $order = $orderMax + 1;

                // change parent module reference in the moved module and set order (added to the end of target group)
                $sql = "UPDATE `".$TABLELEARNPATHMODULE."`
                        SET `parent` = ". (int)$_POST['newPos'].",
                            `rank` = " . (int)$order . "
                        WHERE `learnPath_module_id` = ". (int)$_REQUEST['cmdid'];
                $query = db_query($sql);
                $dialogBox .= "<p class=\"success\">$langModuleMoved</p>";
            }

        }
        else  // create form requested
        {
            // create elementList
            $sql = "SELECT M.*, LPM.*
                    FROM `".$TABLEMODULE."` AS M, `".$TABLELEARNPATHMODULE."` AS LPM
                    WHERE M.`module_id` = LPM.`module_id`
                      AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
                      AND M.`contentType` = \"".CTLABEL_."\"
                    ORDER BY LPM.`rank` ASC";
            $result = db_query($sql);
            $i=0;
            $extendedList = array();
            while ($list = mysql_fetch_array($result))
            {
                // this array will display target for the "move" command
                // so don't add the module itself build_element_list will ignore all childre so that
                // children of the moved module won't be shown, a parent cannot be a child of its own children
                if ( $list['learnPath_module_id'] != $_REQUEST['cmdid'] ) $extendedList[] = $list;
            }

            // build the array that will be used by thebuild_nested_select_menu function
            $elementList = array();
            $elementList = build_element_list($extendedList, 'parent', 'learnPath_module_id');

            $topElement['name'] = $langRoot;
            $topElement['value'] = 0;    // value is required by claro_nested_build_select_menu
            if (!is_array($elementList)) $elementList = array();
            array_unshift($elementList,$topElement);

            // get infos about the moved module
            $sql = "SELECT M.`name`
                    FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                         `".$TABLEMODULE."` AS M
                    WHERE LPM.`module_id` = M.`module_id`
                      AND LPM.`learnPath_module_id` = ". (int)$_REQUEST['cmdid'];
            $temp = db_query_fetch_all($sql);
            $moduleInfos = $temp[0];

            $displayChangePosForm = true; // the form code comes after name and comment boxes section
        }
        break;

    case "moveUp" :
        $thisLPMId = $_REQUEST['cmdid'];
        $sortDirection = "DESC";
        break;

    case "moveDown" :
        $thisLPMId = $_REQUEST['cmdid'];
        $sortDirection = "ASC";
        break;

    case "createLabel" :
        // create form sent
        if( isset($_REQUEST["newLabel"]) && trim($_REQUEST["newLabel"]) != "")
        {
            // determine the default order of this Learning path ( a new label is a root child)
            $sql = "SELECT MAX(`rank`)
                    FROM `".$TABLELEARNPATHMODULE."`
                    WHERE `parent` = 0";
            $result = db_query($sql);

            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;

            // create new module
            $sql = "INSERT INTO `".$TABLEMODULE."`
                   (`name`, `comment`, `contentType`, `launch_data`)
                   VALUES ('". addslashes($_POST['newLabel']) ."','', '".CTLABEL_."','')";
            $query = db_query($sql);

            // request ID of the last inserted row (module_id in $TABLEMODULE) to add it in $TABLELEARNPATHMODULE
            $thisInsertedModuleId = mysql_insert_id();

            // create new learning path module
            $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
                   (`learnPath_id`, `module_id`, `specificComment`, `rank`, `parent`)
                   VALUES ('". (int)$_SESSION['path_id']."', '". (int)$thisInsertedModuleId."','', " . (int)$order . ", 0)";
            $query = db_query($sql);
        }
        else  // create form requested
        {
            $displayCreateLabelForm = true; // the form code comes after name and comment boxes section
        }
        break;

     default:
        break;

}

// IF ORDER COMMAND RECEIVED
// CHANGE ORDER

if (isset($sortDirection) && $sortDirection)
{

    // get list of modules with same parent as the moved module
    $sql = "SELECT LPM.`learnPath_module_id`, LPM.`rank`
            FROM (`".$TABLELEARNPATHMODULE."` AS LPM, `".$TABLELEARNPATH."` AS LP)
              LEFT JOIN `".$TABLELEARNPATHMODULE."` AS LPM2 ON LPM2.`parent` = LPM.`parent`
            WHERE LPM2.`learnPath_module_id` = ". (int)$thisLPMId."
              AND LPM.`learnPath_id` = LP.`learnPath_id`
              AND LP.`learnPath_id` = ". (int)$_SESSION['path_id']."
            ORDER BY LPM.`rank` $sortDirection";

    $listModules  = db_query_fetch_all($sql);

    // LP = learningPath
    foreach( $listModules as $module)
    {
        // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
        //          COMMIT ORDER SWAP ON THE DB

        if (isset($thisLPMOrderFound) && $thisLPMOrderFound == true)
        {

            $nextLPMId = $module['learnPath_module_id'];
            $nextLPMOrder =  $module['rank'];

            $sql = "UPDATE `".$TABLELEARNPATHMODULE."`
                    SET `rank` = \"" . (int)$nextLPMOrder . "\"
                    WHERE `learnPath_module_id` =  \"" . (int)$thisLPMId . "\"";
            db_query($sql);

            $sql = "UPDATE `".$TABLELEARNPATHMODULE."`
                    SET `rank` = \"" . (int)$thisLPMOrder . "\"
                    WHERE `learnPath_module_id` =  \"" . (int)$nextLPMId . "\"";
            db_query($sql);

            break;
        }

        // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT
        if ($module['learnPath_module_id'] == $thisLPMId)
        {
            $thisLPMOrder = $module['rank'];
            $thisLPMOrderFound = true;
        }
    }
}
// select details of learning path to display

$sql = "SELECT *
        FROM `".$TABLELEARNPATH."`
        WHERE `learnPath_id` = ". (int)$_SESSION['path_id'];
$query = db_query($sql);
$LPDetails = mysql_fetch_array($query);

$tool_content .="
 
  <fieldset>
  <legend>$langLearningPathData</legend>
    <table width=\"100%\" class=\"tbl\">";


//############################ LEARNING PATH NAME BOX ################################\\
$tool_content .="
    <tr>
      <th width=\"70\">$langTitle:</th>";

if ($cmd == "updateName")
{
    $tool_content .= disp_message_box(nameBox(LEARNINGPATH_, UPDATE_, $langModify));
}
else
{
    $tool_content .= "
      <td>".nameBox(LEARNINGPATH_, DISPLAY_);
}

$tool_content .= "
      </td>
    </tr>";

//############################ LEARNING PATH COMMENT BOX #############################\\
$tool_content .="
    <tr>
      <th width=\"90\">$langComments:</th>
      <td>";
if ($cmd == "updatecomment")
{
    $tool_content .= commentBox(LEARNINGPATH_, UPDATE_);
    $head_content .= disp_html_area_head("insertCommentBox");
    $body_action = "onload=\"initEditor()\"";
} elseif ($cmd == "delcomment" ) {
    $tool_content .= commentBox(LEARNINGPATH_, DELETE_);
} else {
    $tool_content .= commentBox(LEARNINGPATH_, DISPLAY_);
}

$tool_content .= "</td>
    </tr>
    </table>
    </fieldset>


    <fieldset>
    <legend>$langLearningPathConfigure</legend>
    <table width=\"100%\" class=\"tbl\">";

// -------------------- create label -------------------
if (isset($displayCreateLabelForm) && $displayCreateLabelForm)
{
    $tool_content .= "
    <tr>
      <th width=\"200\">$langLabel:</th>
      <td>
        <form action=\"".$_SERVER['PHP_SELF']."?course=$code_cours\" method=\"post\">
          <label for=\"newLabel\">".$langNewLabel.": </label>&nbsp;
          <input type=\"text\" name=\"newLabel\" id=\"newLabel\" maxlength=\"255\" / size=\"30\" >
          <input type=\"hidden\" name=\"cmd\" value=\"createLabel\" />
          <input type=\"submit\" value=\"".$langCreate."\" />
        </form>
      </td>
    </tr>";
}




// --------------- learning path course admin links ------------------------------

if (!isset($displayCreateLabelForm))
{

    $tool_content .="
    <tr>
      <th width=\"200\">$langLabel:</th>
      <td><a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=createLabel\">".$langCreate."</a></td>
    </tr>";
}
    $tool_content .="
    <tr>
      <th rowspan=\"2\">$langLearningObjects:</th>
      <td>";
    $tool_content .= "$langAdd: <a href=\"insertMyDoc.php?course=$code_cours\" title=\"$langDocumentAsModule\">".
            $langDocumentAsModuleLabel."</a> | <a href=\"insertMyExercise.php?course=$code_cours\" title=\"$langExerciseAsModule\">".
            $langExerciseAsModuleLabel."</a> | <a href=\"insertMyLink.php?course=$code_cours\" title=\"$langLinkAsModule\">".
            $langLinkAsModuleLabel."</a> | <a href=\"insertMyMedia.php?course=$code_cours\" title=\"$langMediaAsModule\">".
            $langMediaAsModuleLabel."</a> | <a href=\"insertMyDescription.php?course=$code_cours\" title=\"$langCourseDescriptionAsModule\">".
            $langCourseDescriptionAsModuleLabel."</a>
      </td>
    </tr>";

    $tool_content .="
    <tr>
      <td>$langReuse: <a href=\"insertMyModule.php?course=$code_cours\" title=\"$langModuleOfMyCourse\">".$langModuleOfMyCourse."</a>
      </td>
    </tr>";

$tool_content .="
    </table>
    </fieldset>";

if (isset($displayChangePosForm) && $displayChangePosForm)
{
    $dialogBox = "
    <table class=\"tbl\">
    <tr>
      <th>".$langMove.":</th>
      <td>
        <form action=\"".$_SERVER['PHP_SELF']."?course=$code_cours\" method=\"post\">\"<b>".$moduleInfos['name']."</b>\" &nbsp;".$langTo.":&nbsp;&nbsp;";
    // build select input - $elementList has been declared in the previous big cmd case
    $dialogBox .= build_nested_select_menu("newPos",$elementList);
    $dialogBox .= "
         <input type=\"hidden\" name=\"cmd\" value=\"changePos\" />
         <input type=\"hidden\" name=\"cmdid\" value=\"".$_REQUEST['cmdid']."\" />
         <input type=\"submit\" value=\"".$langOk."\" />
        </form>
      </td>
    </tr>
    </table>";
}


//####################################################################################\\
//############################### DIALOG BOX SECTION #################################\\
//####################################################################################\\

if (isset($dialogBox) && $dialogBox!="")
{
    $tool_content .= $dialogBox;
}


$tool_content .="
     <p class='sub_title1'>$langLearningPathStructure:</p>
     ";

//  -------------------------- learning path list content ----------------------------
$sql = "SELECT M.*, LPM.*, A.`path`
        FROM (`".$TABLEMODULE."` AS M,
             `".$TABLELEARNPATHMODULE."` AS LPM)
        LEFT JOIN `".$TABLEASSET."` AS A ON M.`startAsset_id` = A.`asset_id`
        WHERE M.`module_id` = LPM.`module_id`
          AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
        ORDER BY LPM.`rank` ASC";

$result = db_query($sql);

if (mysql_num_rows($result) == 0) {
	$tool_content .= "<p class='alert1'>$langNoModule</p>";
	draw($tool_content, 2, null, $head_content, $body_action);
	exit;
}

$extendedList = array();
while ($list = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $extendedList[] = $list;
}

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module

$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));
$i = 0;

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
{
    if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
}

// -------------------------- learning path list header ----------------------------
$tool_content .="
    <table width=\"99%\" class=\"tbl_alt\">
    <tr>
      <th colspan=\"".($maxDeep+1)."\"><div align=\"left\">&nbsp;".$langContents."</div></th>
      <th width='50'><div align=\"center\">".$langBlock."</div></th>
      <th width='80' colspan=\"3\"><div align='center'>".$langMove."</div></th>
      <th width='50' colspan=\"3\"><div align=\"center\">".$langActions."</div></th>
    </tr>";

// -------------------- LEARNING PATH LIST DISPLAY ---------------------------------
$ind=1;
foreach ($flatElementList as $module)
{
    //-------------visibility-----------------------------
    if ( $module['visibility'] == 'HIDE' )
    {
        if ($is_editor)
        {
            $style=" class=\"invisible\"";
            $image_bullet = "off";
        }
        else
        {
            continue; // skip the display of this file
        }
    }
    else
    {
        $style="";
        $image_bullet = "on";
    }
    $spacingString = "";
    for($i = 0; $i < $module['children']; $i++)
           $spacingString .= "
      <td width='5'>&nbsp;</td>";

    $colspan = $maxDeep - $module['children']+1;

     if ($ind%2 == 0) {
         $classvis = 'class="even"';
     } else {
         $classvis = 'class="odd"';
     }


    $tool_content .= "
    <tr $classvis ".$style.">".$spacingString."
      <td colspan=\"".$colspan."\">&nbsp;&nbsp;&nbsp;";

    if ($module['contentType'] == CTLABEL_) // chapter head
    {
        $tool_content .= "<font ".$style." style=\"font-weight: bold\">".htmlspecialchars($module['name'])."</font>";
    }
    else // module
    {
        if($module['contentType'] == CTEXERCISE_ )
            $moduleImg = "exercise_$image_bullet.png";
        else if($module['contentType'] == CTLINK_ )
        	$moduleImg = "links_$image_bullet.png";
        else if($module['contentType'] == CTCOURSE_DESCRIPTION_ )
        	$moduleImg = "description_$image_bullet.png";
        else if($module['contentType'] == CTDOCUMENT_ )
        	$moduleImg = "docs_$image_bullet.png";
        else if ($module['contentType'] == CTMEDIA_ || $module['contentType'] == CTMEDIALINK_)
                $moduleImg = "videos_$image_bullet.png";
        else
            $moduleImg = choose_image(basename($module['path']));

        $contentType_alt = selectAlt($module['contentType']);
        $tool_content .= "<span style=\"vertical-align: middle;\"><img src=\"".$themeimg.'/'.$moduleImg."\" alt=\"".$contentType_alt."\" title=\"".$contentType_alt."\" border=\"0\" /></span>&nbsp;<a href=\"viewer.php?course=$code_cours&amp;path_id=".(int)$_SESSION['path_id']."&amp;module_id=".$module['module_id']."\"".$style.">". htmlspecialchars($module['name']). "</a>";
    }
    $tool_content .= "</td>"; // end of td of module name

    // LOCK
    $tool_content .= "<td width='10' class='center'>";

    if ($module['contentType'] == CTLABEL_)
    {
        $tool_content .= "&nbsp;";
    }
    elseif ( $module['lock'] == 'OPEN')
    {
        $tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=mkBlock&amp;cmdid=".$module['learnPath_module_id']."\">
	<img src=\"".$themeimg."/bullet_unblock.png\" alt=\"$langBlock\" title=\"$langBlock\" border=0 /></a>";
    }
    elseif( $module['lock'] == 'CLOSE')
    {
        $tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=mkUnblock&amp;cmdid=".$module['learnPath_module_id']."\">
	<img src=\"".$themeimg."/bullet_block.png\" alt=\"$langAltMakeNotBlocking\" title=\"$langAltMakeNotBlocking\" border=0 /></a>";
    }
    $tool_content .= "</td>";

    // ORDER COMMANDS
    // DISPLAY CATEGORY MOVE COMMAND
    	$tool_content .= "<td width='10' class='center'>
	<a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=changePos&amp;cmdid=".$module['learnPath_module_id']."\">
	<img src=\"".$themeimg."/move.png\" alt=\"$langMove\" title=\"$langMove\" border=0 /></a></td>";

    // DISPLAY MOVE UP COMMAND only if it is not the top learning path
    if ($module['up'])
    {
        $tool_content .= "<td width='10' align=\"right\">
	<a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=moveUp&amp;cmdid=".$module['learnPath_module_id']."\">
	<img src=\"".$themeimg."/up.png\" alt=\"$langUp\" title=\"$langUp\" border=0 /></a></td>";
    }
    else
    {
        $tool_content .= "<td width='10'>&nbsp;</td>";
    }

    // DISPLAY MOVE DOWN COMMAND only if it is not the bottom learning path
    if ($module['down'])
    {
        $tool_content .= "<td width='10'>
	<a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=moveDown&amp;cmdid=".$module['learnPath_module_id']."\">
	<img src=\"".$themeimg."/down.png\" alt=\"$langDown\" title=\"$langDown\" border=0 /></a></td>";
    }
    else
    {
        $tool_content .= "<td width='10'>&nbsp;</td>";
    }

    // Modify command / go to other page
    $tool_content .= "
      <td width='10'><a href=\"module.php?course=$code_cours&amp;module_id=".$module['module_id']."\"><img src=\"".$themeimg."/edit.png\" border=0 alt=\"".$langModify."\" title=\"".$langModify."\" /></a></td>";

    // DELETE ROW
   //in case of SCORM module, the pop-up window to confirm must be different as the action will be different on the server
    $tool_content .= "
      <td width='10'><a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=delModule&amp;cmdid=".$module['learnPath_module_id']."\" ".
         "onClick=\"return confirmation('".clean_str_for_javascript($langAreYouSureToRemove." ".$module['name'])." ? ";

    if ($module['contentType'] == CTSCORM_ || $module['contentType'] == CTSCORMASSET_)
        $tool_content .= clean_str_for_javascript($langAreYouSureToRemoveSCORM);
    elseif ( $module['contentType'] == CTLABEL_ )
        $tool_content .= clean_str_for_javascript($langAreYouSureToRemoveLabel);
    else
        $tool_content .= clean_str_for_javascript($langAreYouSureToRemoveStd);

    $tool_content .=   "');\"><img src=\"".$themeimg."/delete.png\" border=0 alt=\"".$langRemove."\" title=\"".$langRemove."\" /></a></td>";

    // VISIBILITY
    $tool_content .= "<td width='10'>";

    if ($module['visibility'] == 'HIDE') {
        $tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=mkVisibl&amp;cmdid=".$module['module_id']."\"><img src=\"".$themeimg."/invisible.png\" alt=\"$langVisible\" title=\"$langVisible\" border=\"0\" /></a>";
    }
    else
    {
        if( $module['lock'] == 'CLOSE' )
        {
            $onclick = "onClick=\"return confirmation('".clean_str_for_javascript($langAlertBlockingMakedInvisible)."');\"";
        }
        else
        {
            $onclick = "";
        }
        $tool_content .= "<a href=\"".$_SERVER['PHP_SELF']."?course=$code_cours&amp;cmd=mkInvisibl&amp;cmdid=".$module['module_id']."\" ".$onclick. " ><img src=\"".$themeimg."/visible.png\" alt=\"$langVisible\" title=\"$langVisible\" border=0 /></a>";
    }

    $tool_content .= "</td>";
    $tool_content .= "</tr>";

$ind++;
} // end of foreach

$tool_content .= "</table><br />";
draw($tool_content, 2, null, $head_content, $body_action);
