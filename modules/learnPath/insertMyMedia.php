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

// This script lists all available media and medialinks and the course
// admin cann add them to a learning path

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");

$require_current_course = TRUE;
$require_editor = TRUE;

$TABLELEARNPATH          = "lp_learnPath";
$TABLEMODULE             = "lp_module";
$TABLELEARNPATHMODULE    = "lp_rel_learnPath_module";
$TABLEASSET              = "lp_asset";
$TABLEUSERMODULEPROGRESS = "lp_user_module_progress";

require_once("../../include/baseTheme.php");
require_once("../video/video_functions.php");

$dialogBox = "";

$navigation[] = array("url"=>"learningPathList.php?course=$code_cours", "name"=> $langLearningPath);
$navigation[] = array("url"=>"learningPathAdmin.php?course=$code_cours&amp;path_id=".(int)$_SESSION['path_id'], "name"=> $langAdm);
$nameTools = $langInsertMyMediaToolName;

load_modal_box(true);
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

mysql_select_db($mysqlMainDb);
$iterator = 1;

if (!isset($_POST['maxMediaForm'])) $_POST['maxMediaForm'] = 0;

while ($iterator <= $_POST['maxMediaForm'])
{
    if (isset($_POST['submitInsertedMedia']) && isset($_POST['insertMedia_'.$iterator]))
    {
        // get from DB everything related to the media
        $sql = "SELECT * FROM video WHERE id = '". intval($_POST['insertMedia_'.$iterator]) ."'";
        $row = db_query_get_single_row($sql);

        // check if this media is already a module
        $sql = "SELECT * FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
                         WHERE A.`module_id` = M.`module_id`
                           AND M.`name` LIKE \"" .addslashes($row['title']) ."\"
                           AND M.`comment` LIKE \"" .addslashes($row['description']) ."\"
                           AND A.`path` LIKE \"" .addslashes($row['path']) ."\"
                           AND M.`contentType` = \"".CTMEDIA_."\"";
        $query0 = db_query($sql);
        $num = mysql_numrows($query0);

        if ($num == 0)
        {
            create_new_module($row['title'], $row['description'], $row['path'], CTMEDIA_);

            $dialogBox .= q($row['title'])." : ".$langMediaInsertedAsModule."<br />";
            $style = "success";
        }
        else 
        {
            // check if this is this LP that used this media as a module
            $sql = "SELECT * FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                                  `".$TABLEMODULE."` AS M,
                                  `".$TABLEASSET."` AS A
                             WHERE M.`module_id` =  LPM.`module_id`
                               AND M.`startAsset_id` = A.`asset_id`
                               AND A.`path` = '". addslashes($row['path'])."'
                               AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id'];
            $query2 = db_query($sql);
            $num = mysql_numrows($query2);

            if ($num == 0)
            { // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                    $thisLinkModule = mysql_fetch_array($query0);
                    
                    reuse_module($thisLinkModule['module_id']);
                    
                    $dialogBox .= q($row['title'])." : ".$langMediaInsertedAsModule."<br />";
                    $style = "success";
            }
            else 
            {
                $dialogBox .= q($row['title'])." : ".$langMediaAlreadyUsed."<br />";
                $style = "caution";
            }
        }
    }
        
    if (isset($_POST['submitInsertedMedia']) && isset($_POST['insertMediaLink_'.$iterator]))
    {
        // get from DB everything related to the medialink
        $sql = "SELECT * FROM videolinks WHERE id = '". intval($_POST['insertMediaLink_'.$iterator]) ."'";
        $row = db_query_get_single_row($sql);

        // check if this medialink is already a module
        $sql = "SELECT * FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
                         WHERE A.`module_id` = M.`module_id`
                           AND M.`name` LIKE \"" .addslashes($row['title']) ."\"
                           AND M.`comment` LIKE \"" .addslashes($row['description']) ."\"
                           AND A.`path` LIKE \"" .addslashes($row['url']) ."\"
                           AND M.`contentType` = \"".CTMEDIALINK_."\"";
        $query0 = db_query($sql);
        $num = mysql_numrows($query0);

        if ($num == 0)
        {
            create_new_module($row['title'], $row['description'], $row['url'], CTMEDIALINK_);

            $dialogBox .= q($row['title'])." : ".$langMediaInsertedAsModule."<br />";
            $style = "success";
        }
        else 
        {
            // check if this is this LP that used this medialink as a module
            $sql = "SELECT * FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                                  `".$TABLEMODULE."` AS M,
                                  `".$TABLEASSET."` AS A
                             WHERE M.`module_id` =  LPM.`module_id`
                               AND M.`startAsset_id` = A.`asset_id`
                               AND A.`path` = '". addslashes($row['url'])."'
                               AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id'];
            $query2 = db_query($sql);
            $num = mysql_numrows($query2);

            if ($num == 0)
            { // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                    $thisLinkModule = mysql_fetch_array($query0);
                    
                    reuse_module($thisLinkModule['module_id']);
                    
                    $dialogBox .= q($row['title'])." : ".$langMediaInsertedAsModule."<br />";
                    $style = "success";
            }
            else 
            {
                $dialogBox .= q($row['title'])." : ".$langMediaAlreadyUsed."<br />";
                $style = "caution";
            }
        }
    }

    $iterator++;
}



if (isset($dialogBox) && $dialogBox != "") {
    $tool_content .= "<table width=\"99%\"><tr>";
    $tool_content .= disp_message_box($dialogBox, $style);
    $tool_content .= "</td></tr></table>";
    $tool_content .= "<br />";
}

$tool_content .= showmedia();

$tool_content .= "
    <br />
    <p align=\"right\"><a href=\"learningPathAdmin.php?course=$code_cours&amp;path_id=".(int)$_SESSION['path_id']."\">$langBackToLPAdmin</a>";
draw($tool_content, 2, null, $head_content);


function showmedia()
{
    global $langName, $langSelection, $langAddModulesButton, $code_cours, $themeimg;

    $sqlMedia = "SELECT * FROM video ORDER BY title";
    $sqlMediaLinks = "SELECT * FROM videolinks ORDER BY title";
    
    $resultMedia = db_query($sqlMedia);
    $resultMediaLinks = db_query($sqlMediaLinks);

    $output = "<form action='$_SERVER[PHP_SELF]?course=$code_cours' method='POST'>
               <table width='100%' class='tbl_alt'>
               <tr>
               <th colspan='2'>$langName</th>
               <th width='50'>$langSelection</th>
               </tr>
               <tbody>";
    
    $i=1;
    while ($myrow = mysql_fetch_array($resultMedia))
    {
        list($mediaURL, $mediaPath, $mediaPlay) = media_url($myrow['path']);
                                                    
        $output .= "<tr>
                    <td width='1' valign='top'><img src='$themeimg/arrow.png' border='0'></td>
                    <td align='left' valign='top'>". choose_media_ahref($mediaURL, $mediaPath, $mediaPlay, q($myrow['title']), $myrow['path']) ."
                    <br />
                    <small class='comments'>".q($myrow['description'])."</small></td>";
        $output .= "<td><div align='center'><input type='checkbox' name='insertMedia_".$i."' id='insertMedia_".$i."' value='".$myrow['id']."' /></div></td></tr>";
        $i++;
    }
    
    $j=1;
    while($myrow = mysql_fetch_array($resultMediaLinks))
    {
        $output .= "<tr>
                    <td width='1' valign='top'><img src='$themeimg/arrow.png' border='0'></td>
                    <td align='left' valign='top'>". choose_medialink_ahref(q($myrow['url']), q($myrow['title'])) ."
                    <br />
                    <small class='comments'>".q($myrow['description'])."</small></td>";
        $output .= "<td><div align='center'><input type='checkbox' name='insertMediaLink_".$j."' id='insertMediaLink_".$j."' value='".$myrow['id']."' /></div></td></tr>";
        $j++;
    }

    $output .= "<tr>
                <th colspan='3'>
                <div align='right'>
                  <input type='hidden' name='maxMediaForm' value ='" . ($i+$j-2) ."' />
                  <input type='submit' name='submitInsertedMedia' value='$langAddModulesButton'/>
                </div></th>
                </tr>
                </tbody>
                </table>
                </form>";
    return $output;
}

function create_new_module($title, $description, $path, $contentType)
{
    global $TABLEMODULE, $TABLEASSET, $TABLELEARNPATHMODULE, $cours_id;
    
    // create new module
    $sql = "INSERT INTO `".$TABLEMODULE."`
                    (`course_id`, `name` , `comment`, `contentType`, `launch_data`)
                    VALUES ($cours_id, '". addslashes($title) ."' , '"
                    .addslashes($description) . "', '".$contentType."','')";
    $query = db_query($sql);

    $insertedModule_id = mysql_insert_id();

    // create new asset
    $sql = "INSERT INTO `".$TABLEASSET."`
                    (`path` , `module_id` , `comment`)
                    VALUES ('". addslashes($path)."', "
                    . (int)$insertedModule_id . ", '')";
    $query = db_query($sql);

    $insertedAsset_id = mysql_insert_id();

    $sql = "UPDATE `".$TABLEMODULE."`
            SET `startAsset_id` = " . (int)$insertedAsset_id . "
            WHERE `module_id` = " . (int)$insertedModule_id . "";
    $query = db_query($sql);

    // determine the default order of this Learning path
    $sql = "SELECT MAX(`rank`) FROM `".$TABLELEARNPATHMODULE."`";
    $result = db_query($sql);

    list($orderMax) = mysql_fetch_row($result);
    $order = $orderMax + 1;

    // finally : insert in learning path
    $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
            (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
            VALUES ('". (int)$_SESSION['path_id']."', '".(int)$insertedModule_id."','"
            ."', ".(int)$order.", 'OPEN')";
    $query = db_query($sql);
}

function reuse_module($module_id)
{
    global $TABLELEARNPATHMODULE;
    
    // determine the default order of this Learning path
    $sql = "SELECT MAX(`rank`) FROM `".$TABLELEARNPATHMODULE."`";
    $result = db_query($sql);

    list($orderMax) = mysql_fetch_row($result);
    $order = $orderMax + 1;

    // finally : insert in learning path
    $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
                    (`learnPath_id`, `module_id`, `specificComment`, `rank`,`lock`)
                    VALUES ('". (int)$_SESSION['path_id']."', '"
                    .(int)$module_id."','"
                    ."', ".(int)$order.",'OPEN')";
    $query = db_query($sql);
}
