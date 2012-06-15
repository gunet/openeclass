<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
	insertMyModule.php
	@last update: 29-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: insertMyModule.php Revision: 1.22

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This script lists all available modules and the course
                  admin can add them to a learning path

    @Comments:

    @todo:
==============================================================================
*/

$require_current_course = TRUE;
$require_editor = TRUE;

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/video/video_functions.php';

load_modal_box();
$head_content .= <<<EOF
<script type='text/javascript'>
$(document).ready(function() {

    $('tr').click(function(event) {
        if (event.target.type !== 'checkbox') {
            $(':checkbox', this).trigger('click');
        }
    });

});
</script>
EOF;

$navigation[]= array ("url"=>"learningPathList.php?course=$course_code", "name"=> $langLearningPath);
$navigation[]= array ("url"=>"learningPathAdmin.php?course=$course_code&amp;path_id=".(int)$_SESSION['path_id'], "name"=> $langAdm);
$nameTools = $langInsertMyModulesTitle;

// FUNCTION NEEDED TO BUILD THE QUERY TO SELECT THE MODULES THAT MUST BE AVAILABLE

// 1)  We select first the modules that must not be displayed because
// as they are already in this learning path

function buildRequestModules()
{

 global $TABLELEARNPATHMODULE;
 global $TABLEMODULE;
 global $TABLEASSET, $langLearningModule, $langSelection, $langComments, $course_id;

 $firstSql = "SELECT LPM.`module_id`
              FROM `".$TABLELEARNPATHMODULE."` AS LPM
              WHERE LPM.`learnPath_id` = ". (int)$_SESSION['path_id'];

 $firstResult = db_query($firstSql);

 // 2) We build the request to get the modules we need

 $sql = "SELECT M.*, A.`path`
         FROM `".$TABLEMODULE."` AS M
           LEFT JOIN `".$TABLEASSET."` AS A ON M.`startAsset_id` = A.`asset_id`
         WHERE M.`contentType` != \"SCORM\"
           AND M.`contentType` != \"SCORM_ASSET\"
           AND M.`contentType` != \"LABEL\"
           AND M.`course_id` = $course_id";

 while ($list=mysql_fetch_array($firstResult))
 {
    $sql .=" AND M.`module_id` != ". (int)$list['module_id'];
 }


 /* To find which module must displayed we can also proceed  with only one query.
  * But this implies to use some features of MySQL not available in the version 3.23, so we use
  * two differents queries to get the right list.
  * Here is how to proceed with only one

  $query = "SELECT *
             FROM `".$TABLEMODULE."` AS M
             WHERE NOT EXISTS(SELECT * FROM `".$TABLELEARNPATHMODULE."` AS TLPM
             WHERE TLPM.`module_id` = M.`module_id`)";
 */

  return $sql;

}//end function

//COMMAND ADD SELECTED MODULE(S):

if (isset($_REQUEST['cmdglobal']) && ($_REQUEST['cmdglobal'] == 'add'))
{

    // select all 'addable' modules of this course for this learning path

    $result = db_query(buildRequestModules());
    $atLeastOne = FALSE;
    $nb=0;
    while ($list = mysql_fetch_array($result))
    {
        // see if check box was checked
        if (isset($_REQUEST['check_'.$list['module_id']]) && $_REQUEST['check_'.$list['module_id']])
        {
            // find the order place where the module has to be put in the learning path
            $sql = "SELECT MAX(`rank`)
                    FROM `".$TABLELEARNPATHMODULE."`
                    WHERE learnPath_id = " . (int)$_SESSION['path_id'];
            $result2 = db_query($sql);

            list($orderMax) = mysql_fetch_row($result2);
            $order = $orderMax + 1;

            //create and call the insertquery on the DB to add the checked module to the learning path

            $insertquery="INSERT INTO `".$TABLELEARNPATHMODULE."`
                          (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock` )
                          VALUES (". (int)$_SESSION['path_id'].", ". (int)$list['module_id'].", '',".$order.", 'OPEN')";
            db_query($insertquery);

            $atleastOne = TRUE;
            $nb++;
        }
    }

} //end if ADD command

//STEP ONE : display form to add module of the course that are not in this path yet
// this is the same SELECT as "select all 'addable' modules of this course for this learning path"
// **BUT** normally there is less 'addable' modules here than in the first one

$result = db_query(buildRequestModules());

$tool_content .= '    <form name="addmodule" action="'.$_SERVER['PHP_SELF'].'?course='.$course_code.'&amp;cmdglobal=add">'."\n\n";
$tool_content .= '    <table width="100%" class="tbl_alt">'."\n"
       .'    <tr>'."\n"
       .'      <th><div align="left">'
       .$langLearningModule
       .'</div></th>'."\n"
       .'      <th width="10"><div align="center">'
       .$langSelection
       .'</div></th>'."\n"
       .'    </tr>'."\n";

// Display available modules


$atleastOne = FALSE;

$ind=1;
while ($list=mysql_fetch_array($result))
{
     if ($ind%2 == 0) {
         $style = 'class="even"';
     } else {
         $style = 'class="odd"';
     }


    //CHECKBOX, NAME, RENAME, COMMENT
    if($list['contentType'] == CTEXERCISE_ )
        $moduleImg = "exercise_on.png";
    else if($list['contentType'] == CTLINK_ )
        $moduleImg = "links_on.png";
    else if($list['contentType'] == CTCOURSE_DESCRIPTION_ )
       	$moduleImg = "description_on.png";
    else if ($list['contentType'] == CTMEDIA_ || $list['contentType'] == CTMEDIALINK_)
       	$moduleImg = "videos_on.png";
    else
        $moduleImg = choose_image(basename($list['path']));

    $contentType_alt = selectAlt($list['contentType']);

    $tool_content .= '    <tr '.$style.'>'."\n"
        .'      <td align="left">'."\n"
        .'        <label for="check_'.$list['module_id'].'" ><img src="'.$themeimg.'/'.$moduleImg.'" alt="'.$contentType_alt.'" />&nbsp;<b>'.$list['name'].'</b></label>'."\n";

    // COMMENT
    if ($list['comment'] != null)
    {
        $tool_content .= '     <br /> <br />'."\n"
            .'        <em>'.$langComments.'</em>: <br />'.$list['comment'].''."\n";
    }
    $tool_content .= '      </td>'."\n"
        .'      <td align="center">'."\n"
        .'        <input type="checkbox" name="check_'.$list['module_id'].'" id="check_'.$list['module_id'].'">'."\n"
        .'      </td>'."\n"
        .'    </tr>'."\n";

    $atleastOne = TRUE;

    $ind++;
}//end while another module to display

//$tool_content .= '    </tbody>'."\n".'    <tfoot>'."\n";

if ( !$atleastOne )
{
    $tool_content .= '    <tr>'."\n"
        .'      <td colspan="2" align="center">'
        .$langNoMoreModuleToAdd
        .'</td>'."\n"
        .'    </tr>'."\n";
}

// Display button to add selected modules

if ( $atleastOne )
{
    $tool_content .= '    <tr>'."\n"
        .'      <th colspan="2"><div align="right">'."\n"
        .'        <input type="submit" value="'.$langReuse.'" />'."\n"
        .'        <input type="hidden" name="cmdglobal" value="add"></div>'."\n"
        .'      </th>'."\n"
        .'    </tr>'."\n";
}

$tool_content .= "\n".'    </table>'."\n".'    </form>';
$tool_content .= "<p align=\"right\"><a href=\"learningPathAdmin.php?course=$course_code&amp;path_id=".(int)$_SESSION['path_id']."\">$langBackToLPAdmin</a></p>";
//####################################################################################\\
//################################## MODULES LIST ####################################\\
//####################################################################################\\

//$tool_content .= "<br />";
// display subtitle
//$tool_content .= disp_tool_title($langPathContentTitle);
// display back link to return to the LP administration
// display list of modules used by this learning path
//$tool_content .= display_path_content();

draw($tool_content, 2, null, $head_content);
