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

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';

ModalBoxHelper::loadModalBox();
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

$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langLearningPath);
$navigation[] = array("url" => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'], "name" => $langAdm);
$toolName = $langInsertMyModulesTitle;
$tool_content .= 
         action_bar(array(
            array('title' => $langBack,
                'url' => "learningPathAdmin.php?course=$course_code&amp;path_id=" . (int) $_SESSION['path_id'],
                'icon' => 'fa-reply',
                'level' => 'primary-label'))) ;


// FUNCTION NEEDED TO BUILD THE QUERY TO SELECT THE MODULES THAT MUST BE AVAILABLE
// 1)  We select first the modules that must not be displayed because
// as they are already in this learning path
function buildRequestModules() {
    global $course_id;

    $firstSql = "SELECT LPM.`module_id`
              FROM `lp_rel_learnPath_module` AS LPM
              WHERE LPM.`learnPath_id` = ?d";

    $firstResult = Database::get()->queryArray($firstSql, $_SESSION['path_id']);

    // 2) We build the request to get the modules we need

    $sql = "SELECT M.*, A.`path`
         FROM `lp_module` AS M
           LEFT JOIN `lp_asset` AS A ON M.`startAsset_id` = A.`asset_id`
         WHERE M.`contentType` != \"SCORM\"
           AND M.`contentType` != \"SCORM_ASSET\"
           AND M.`contentType` != \"LABEL\"
           AND M.`course_id` = " . intval($course_id);

    foreach ($firstResult as $list) {
        $sql .=" AND M.`module_id` != " . intval($list->module_id);
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
}

//end function
//COMMAND ADD SELECTED MODULE(S):

if (isset($_REQUEST['cmdglobal']) && ($_REQUEST['cmdglobal'] == 'add')) {
    // select all 'addable' modules of this course for this learning path
    $result = Database::get()->queryArray(buildRequestModules());
    $atLeastOne = FALSE;
    $nb = 0;
    foreach ($result as $list) {
        // see if check box was checked
        if (isset($_REQUEST['check_' . $list->module_id]) && $_REQUEST['check_' . $list->module_id]) {
            // find the order place where the module has to be put in the learning path
            $order = 1 + intval(Database::get()->querySingle("SELECT MAX(`rank`) AS max
                    FROM `lp_rel_learnPath_module`
                    WHERE learnPath_id = ?d", $_SESSION['path_id'])->max);

            //create and call the insertquery on the DB to add the checked module to the learning path
            Database::get()->query("INSERT INTO `lp_rel_learnPath_module`
                          (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`, `visible` )
                          VALUES (?d, ?d, '', ?d, 'OPEN', 1)", $_SESSION['path_id'], $list->module_id, $order);

            $atleastOne = TRUE;
            $nb++;
        }
    }
    Session::Messages($langDocInsertedAsModule);
    redirect_to_home_page('modules/learnPath/learningPathAdmin.php?course='.$course_code);
} //end if ADD command
//STEP ONE : display form to add module of the course that are not in this path yet
// this is the same SELECT as "select all 'addable' modules of this course for this learning path"
// **BUT** normally there is less 'addable' modules here than in the first one

$result = Database::get()->queryArray(buildRequestModules());

$tool_content .= '<form name="addmodule" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmdglobal=add">' . "\n\n";
$tool_content .= '<table class="table-default">'
        . '<tr>'
        . '<th class="text-left">'.$langLearningModule.'</th>'
        . '<th width="10"><div align="center">'
        . $langSelection
        . '</div></th>'
        . '</tr>';

// Display available modules

$atleastOne = FALSE;


foreach ($result as $list) {

    //CHECKBOX, NAME, RENAME, COMMENT
    if ($list->contentType == CTEXERCISE_) {
        $moduleImg = 'fa-pencil-square-o';
    } else if ($list->contentType == CTLINK_) {
        $moduleImg = 'fa-link';
    } else if ($list->contentType == CTCOURSE_DESCRIPTION_) {
        $moduleImg = 'fa-info-circle';
    } else if ($list->contentType == CTMEDIA_ || $list->contentType == CTMEDIALINK_) {
        $moduleImg = 'fa-film';
    } else {
        $moduleImg = choose_image(basename($list->path));
    }

    $contentType_alt = selectAlt($list->contentType);

    $tool_content .= '<tr>' . "\n"
            . '<td align="left">' . "\n"
            . '<label for="check_' . $list->module_id . '" >' . icon($moduleImg, $contentType_alt) . '&nbsp;<b>' . $list->name . '</b></label>' . "\n";

    // COMMENT
    if ($list->comment != null) {
        $tool_content .= '<br /> <br />' . "\n"
                . '<em>' . $langComments . '</em>: <br />' . $list->comment . '' . "\n";
    }
    $tool_content .= '</td>'
            . '<td align="center">'
            . '<input type="checkbox" name="check_' . $list->module_id . '" id="check_' . $list->module_id . '">' . "\n"
            . '</td>'
            . '</tr>';

    $atleastOne = TRUE;
}//end while another module to display

if (!$atleastOne) {
    $tool_content .= '<tr>'
            . '<td colspan="2" align="center">'
            . $langNoMoreModuleToAdd
            . '</td>'
            . '</tr>';
}

// Display button to add selected modules

if ($atleastOne) {
    $tool_content .= '<tr>'
            . '<th colspan="2"><div align="right">' . "\n"
            . '<button class="btn btn-primary" value="' . $langAddOneModuleButton . '" />'.$langAddOneModuleButton.'</button>' . "\n"
            . '<input type="hidden" name="cmdglobal" value="add"></div>' . ""
            . '</th>'
            . '</tr>';
}

$tool_content .= "</table></form>";

//####################################################################################\\
//################################## MODULES LIST ####################################\\
//####################################################################################\\
// display subtitle
//$tool_content .= disp_tool_title($langPathContentTitle);
// display back link to return to the LP administration
// display list of modules used by this learning path
//$tool_content .= display_path_content();

draw($tool_content, 2, null, $head_content);
