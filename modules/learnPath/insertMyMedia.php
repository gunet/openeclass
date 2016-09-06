<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
// admin can add them to a learning path

$require_current_course = TRUE;
$require_editor = TRUE;

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';

$dialogBox = '';

$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langLearningPath);
$navigation[] = array('url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'], 'name' => $langAdm);
$toolName = $langInsertMyMediaToolName;
$tool_content .= 
         action_bar(array(
            array('title' => $langBack,
                'url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'],
                'icon' => 'fa-reply',
                'level' => 'primary-label'))) ;

ModalBoxHelper::loadModalBox(true);
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

$iterator = 1;

if (!isset($_POST['maxMediaForm'])) {
    $_POST['maxMediaForm'] = 0;
}

while ($iterator <= $_POST['maxMediaForm']) {
    if (isset($_POST['submitInsertedMedia']) && isset($_POST['insertMedia_' . $iterator])) {
        // get from DB everything related to the media
        $video = Database::get()->querySingle("SELECT * FROM video WHERE id = ?d", intval($_POST['insertMedia_' . $iterator]));

        // check if this media is already a module
        $sql = "SELECT * FROM `lp_module` AS M, `lp_asset` AS A
                         WHERE A.`module_id` = M.`module_id`
                           AND M.`name` LIKE ?s
                           AND M.`comment` LIKE ?s
                           AND A.`path` LIKE ?s
                           AND M.`contentType` = ?s";
        $thisLinkModule = Database::get()->querySingle($sql, $video->title, $video->description, $video->path, CTMEDIA_);

        if ($thisLinkModule) {
            // check if this is this LP that used this media as a module
            $sql = "SELECT COUNT(*) AS count FROM `lp_rel_learnPath_module` AS LPM,
                                  `lp_module` AS M,
                                  `lp_asset` AS A
                             WHERE M.`module_id` =  LPM.`module_id`
                               AND M.`startAsset_id` = A.`asset_id`
                               AND A.`path` = ?s
                               AND LPM.`learnPath_id` = ?d";
            $num = Database::get()->querySingle($sql, $video->path, $_SESSION['path_id'])->count;

            if ($num == 0) { // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                reuse_module($thisLinkModule->module_id);               
                Session::Messages($langInsertedAsModule, 'alert-info');

            } else {                
                Session::Messages($langAlreadyUsed, 'alert-warning');
            }
        } else {
            create_new_module($video->title, $video->description, $video->path, CTMEDIA_);           
            Session::Messages($langInsertedAsModule, 'alert-info');
        }
    }

    if (isset($_POST['submitInsertedMedia']) && isset($_POST['insertMediaLink_' . $iterator])) {
        // get from DB everything related to the medialink
        $videolink = Database::get()->querySingle("SELECT * FROM videolink WHERE id = ?d", intval($_POST['insertMediaLink_' . $iterator]));

        // check if this medialink is already a module
        $sql = "SELECT * FROM `lp_module` AS M, `lp_asset` AS A
                         WHERE A.`module_id` = M.`module_id`
                           AND M.`name` LIKE ?s
                           AND M.`comment` LIKE ?s
                           AND A.`path` LIKE ?s
                           AND M.`contentType` = ?s";
        $thisLinkModule = Database::get()->querySingle($sql, $videolink->title, $videolink->description, $videolink->url, CTMEDIALINK_);

        if ($thisLinkModule) {
            // check if this is this LP that used this medialink as a module
            $sql = "SELECT COUNT(*) AS count FROM `lp_rel_learnPath_module` AS LPM,
                                  `lp_module` AS M,
                                  `lp_asset` AS A
                             WHERE M.`module_id` =  LPM.`module_id`
                               AND M.`startAsset_id` = A.`asset_id`
                               AND A.`path` = ?s
                               AND LPM.`learnPath_id` = ?d";
            $num = Database::get()->querySingle($sql, $videolink->url, $_SESSION['path_id'])->count;

            if ($num == 0) { // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                reuse_module($thisLinkModule->module_id);               
                Session::Messages($langInsertedAsModule, 'alert-info');
                redirect_to_home_page('modules/learnPath/learningPathAdmin.php?course=' . $course_code);
            } else {                
                Session::Messages($langAlreadyUsed);
                redirect_to_home_page('modules/learnPath/learningPathAdmin.php?course=' . $course_code);
            }
        } else {
            create_new_module($videolink->title, $videolink->description, $videolink->url, CTMEDIALINK_);
            Session::Messages($langInsertedAsModule, 'alert-info');
            redirect_to_home_page('modules/learnPath/learningPathAdmin.php?course=' . $course_code);
        }
    }
    $iterator++;
}

$tool_content .= showmedia();
draw($tool_content, 2, null, $head_content);

/**
 * @brief display multimedia files
 * @global type $langName
 * @global type $langSelection
 * @global type $langAddModulesButton
 * @global type $course_code
 * @return string
 */
function showmedia() {
    global $langName, $langSelection, $langAddModulesButton, $course_code, $course_id, $langNoVideo;
        
    $output = "<form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='POST'>
               <div class='table-responsive'>
               <table class='table-default'>
               <thead>
               <tr class='list-header'>
               <th>$langName</th>
               <th width='50'>$langSelection</th>
               </tr>
               </thead>
               <tbody>";

    $i = 1;
    $j = 1;
    $resultMedia = Database::get()->queryArray("SELECT * FROM video WHERE visible = 1 AND course_id = ?d ORDER BY title", $course_id);
    $resultMediaLinks = Database::get()->queryArray("SELECT * FROM videolink WHERE visible = 1 AND course_id = ?d ORDER BY title", $course_id);
    
    if (empty($resultMedia) && empty($resultMediaLinks)){
        $output .= "
            <tr>
                <td class='text-grey' align='center'>$langNoVideo</td>
                <td></td>
            </tr>
        ";
    }
    
    foreach ($resultMedia as $myrow) {
        $vObj = MediaResourceFactory::initFromVideo($myrow);

        $output .= "<tr>                    
                    <td align='text-left'>" . MultimediaHelper::chooseMediaAhref($vObj) . "
                    <br />
                    <small class='comments'>" . q($myrow->description) . "</small></td>";
        $output .= "<td><div align='center'><input type='checkbox' name='insertMedia_" . $i . "' id='insertMedia_" . $i . "' value='" . $myrow->id . "' /></div></td></tr>";
        $i++;
    }
    foreach ($resultMediaLinks as $myrow) {
        $vObj = MediaResourceFactory::initFromVideoLink($myrow);
        $output .= "<tr>                    
                    <td align='left' valign='top'>" . MultimediaHelper::chooseMedialinkAhref($vObj) . "
                    <br />
                    <small class='comments'>" . q($myrow->description) . "</small></td>";
        $output .= "<td><div align='center'><input type='checkbox' name='insertMediaLink_" . $j . "' id='insertMediaLink_" . $j . "' value='" . $myrow->id . "' /></div></td></tr>";
        $j++;
    }

    $output .= "
                </tbody>
                <tfooter>
                <tr>
                <th colspan='3'>
                <div align='right'>
                  <input type='hidden' name='maxMediaForm' value ='" . ($i + $j - 2) . "' />
                  <input class='btn btn-primary' type='submit' name='submitInsertedMedia' value='$langAddModulesButton'/>
                </div></th>
                </tr>
                </tfooter>
                </table>
                </div>
                </form>";
    return $output;
}

/**
 * @brief create new lp module
 * @global type $course_id
 * @param type $title
 * @param type $description
 * @param type $path
 * @param type $contentType
 */
function create_new_module($title, $description, $path, $contentType) {
    global $course_id;

    // create new module
    $insertedModule_id = Database::get()->query("INSERT INTO `lp_module`
                    (`course_id`, `name` , `comment`, `contentType`, `launch_data`)
                    VALUES (?d, ?s, ?s, ?s,'')", $course_id, $title, $description, $contentType)->lastInsertID;

    // create new asset
    $insertedAsset_id = Database::get()->query("INSERT INTO `lp_asset`
                    (`path` , `module_id` , `comment`)
                    VALUES (?s, ?d, '')", $path, $insertedModule_id)->lastInsertID;

    Database::get()->query("UPDATE `lp_module`
            SET `startAsset_id` = ?d
            WHERE `module_id` = ?d", $insertedAsset_id, $insertedModule_id);

    // determine the default order of this Learning path
    $order = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max FROM `lp_rel_learnPath_module`")->max);

    // finally : insert in learning path
    Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
            (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`, `visible`)
            VALUES (?d, ?d, '', ?d, 'OPEN', 1)", $_SESSION['path_id'], $insertedModule_id, $order);
}

/**
 * @brief reuse lp module
 * @param type $module_id
 */
function reuse_module($module_id) {
    // determine the default order of this Learning path
    $order = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max FROM `lp_rel_learnPath_module`")->max);

    // finally : insert in learning path
    Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
                    (`learnPath_id`, `module_id`, `specificComment`, `rank`,`lock`, `visible`)
                    VALUES (?d, ?d, '', ?d,'OPEN', 1)", $_SESSION['path_id'], $module_id, $order);
}
