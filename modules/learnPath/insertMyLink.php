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


/* ===========================================================================
  insertMyLink.php
  @last update: 30-06-2006 by Thanos Kyritsis
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
  ==============================================================================
  @Description: This script lists all available links and the course
  admin can add them to a learning path

  @Comments:

  @todo:
  ==============================================================================
 */


$require_current_course = TRUE;
$require_editor = TRUE;

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

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

$dialogBox = "";

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPath);
$navigation[] = array("url" => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'], "name" => $langAdm);
$toolName = $langInsertMyLinkToolName;

$iterator = 1;

if (!isset($_POST['maxLinkForm'])) {
    $_POST['maxLinkForm'] = 0;
}

while ($iterator <= $_POST['maxLinkForm']) {
    if (isset($_POST['submitInsertedLink']) && isset($_POST['insertLink_' . $iterator])) {

        // get from DB everything related to the link
        $row = Database::get()->querySingle("SELECT * FROM link WHERE course_id = ?d AND `id` = ?d", $course_id, $_POST['insertLink_' . $iterator]);

        // check if this link is already a module
        $sql = "SELECT * FROM `lp_module` AS M, `lp_asset` AS A
        		WHERE A.`module_id` = M.`module_id`
        		AND M.`name` LIKE ?s
        		AND M.`comment` LIKE ?s
        		AND A.`path` LIKE ?s
        		AND M.`contentType` = ?s
        		AND M.`course_id` = ?d";
        $thisLinkModule = Database::get()->querySingle($sql, $row->title, $row->description, $row->url, CTLINK_, $course_id);

        if (!$thisLinkModule) {
            // create new module
            $insertedModule_id = Database::get()->query("INSERT INTO `lp_module`
					(`course_id`, `name` , `comment`, `contentType`, `launch_data`)
					VALUES (?d, ?s, ?s, ?s,'')", $course_id, $row->title, $row->description, CTLINK_)->lastInsertID;

            // create new asset
            $insertedAsset_id = Database::get()->query("INSERT INTO `lp_asset`
					(`path` , `module_id` , `comment`)
					VALUES (?s, ?s, '')", $row->url, $insertedModule_id)->lastInsertID;

            Database::get()->query("UPDATE `lp_module`
				SET `startAsset_id` = ?d
				WHERE `module_id` = ?d
				AND `course_id` = ?d", $insertedAsset_id, $insertedModule_id, $course_id);

            // determine the default order of this Learning path
            $order = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max FROM `lp_rel_learnPath_module` WHERE `learnPath_id` = ?d", $_SESSION['path_id'])->max);

            // finally : insert in learning path
            Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
				(`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`, `visible`)
				VALUES (?d, ?d, '', ?d, 'OPEN', 1)", $_SESSION['path_id'], $insertedModule_id, $order);

            $dialogBox .= "<div class='alert alert-success'>".q($row->title) . " : " . $langLinkInsertedAsModule . "</div>";
        } else {
            // check if this is this LP that used this document as a module
            $sql = "SELECT COUNT(*) AS count FROM `lp_rel_learnPath_module` AS LPM,
				`lp_module` AS M,
				`lp_asset` AS A
				WHERE M.`module_id` =  LPM.`module_id`
				AND M.`startAsset_id` = A.`asset_id`
				AND A.`path` = ?s
				AND LPM.`learnPath_id` = ?d
				AND M.`course_id` = ?d";
            $num = Database::get()->querySingle($sql, $row->url, $_SESSION['path_id'], $course_id)->count;

            if ($num == 0) { // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                // determine the default order of this Learning path
                $order = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max
					FROM `lp_rel_learnPath_module`
					WHERE `learnPath_id` = ?d", $_SESSION['path_id'])->max);

                // finally : insert in learning path
                Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
					(`learnPath_id`, `module_id`, `specificComment`, `rank`,`lock`, `visible`)
					VALUES (?d, ?d, '', ?d,'OPEN', 1)", $_SESSION['path_id'], $thisLinkModule->module_id, $order);

                $dialogBox .= "<div class='alert alert-success'>".q($row->title) . " : " . $langLinkInsertedAsModule . "</div>";                
            } else {
                $dialogBox .= "<div class='alert alert-warning'>".q($row->title) . " : " . $langLinkAlreadyUsed . "</div>";
            }
        }
    }
    $iterator++;
}

if (isset($dialogBox) && $dialogBox != "") {    
    $tool_content .= $dialogBox;
}

$tool_content .= 
         action_bar(array(
            array('title' => $langBack,
                'url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'],
                'icon' => 'fa-reply',
                'level' => 'primary-label'))) ;
$tool_content .= showlinks();

draw($tool_content, 2, null, $head_content);

/**
 * @brief display links
 * @global type $langName
 * @global type $langSelection
 * @global type $langAddModulesButton
 * @global type $course_id
 * @global type $course_code
 * @global type $themeimg
 * @return string
 */
function showlinks() {
    global $langName, $langSelection, $langAddModulesButton, $course_id, $course_code, $langNoLinksExist;

    $result = Database::get()->queryArray("SELECT * FROM link WHERE course_id = ?d ORDER BY `order` DESC", $course_id);

    $output = "<form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='POST'>
                    <div class='table-responsive'>
                      <table class='table-default'>
                      <thead><tr class='list-header'>
                        <th>$langName</th>
                        <th width='50'>$langSelection</th>
                      </tr></thead>
                      <tbody>";

    $i = 1;

    if ( empty($result) ){

        $output .= "
            <tr>
                <td class='text-grey' align='center'>$langNoLinksExist</td>
                <td></td>
            </tr>
        ";

    } else {

        foreach ($result as $myrow) {
            $output .= "
            <tr>                
            <td align='left' valign='top'>";
            if (empty($myrow->title)) {
                $output .= "<a href='" . q($myrow->url) . "' target='_blank'>" . q($myrow->url) . "</a>";
            } else {
                $output .= "<a href='" . q($myrow->url) . "' target='_blank'>" . q($myrow->title) . "</a>";
            }
            $output .= "<br><small class='comments'>" . $myrow->description . "</small></td>";
            $output .= "<td><div align='center'><input type='checkbox' name='insertLink_" . $i . "' id='insertLink_" . $i . "' value='" . $myrow->id . "' /></div></td>
            </tr>";
            $i++;
        }
    }
    $output .= "</tbody>
        <tfooter>
        <tr>
            <th colspan='2'>
                <div align='right'>
                <input type='hidden' name='maxLinkForm' value ='" . ($i - 1) . "' />
                <input class='btn btn-primary' type='submit' name='submitInsertedLink' value='$langAddModulesButton'/>
                </div>
            </th>
        </tr>
        </tfooter>
        
        </table></div>
        </form>";
    return $output;
}
