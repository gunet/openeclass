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
	insertMyExercise.php
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: insertMyExercise.php Revision: 1.14.2.1

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This script lists all available exercises and the course
                  admin can add them to a learning path
============================================================================== */


$require_current_course = TRUE;
$require_editor = TRUE;

// DB tables definition
$TABLELEARNPATH          = "lp_learnPath";
$TABLEMODULE             = "lp_module";
$TABLELEARNPATHMODULE    = "lp_rel_learnPath_module";
$TABLEASSET              = "lp_asset";
$TABLEUSERMODULEPROGRESS = "lp_user_module_progress";
// exercises table name
$TABLEEXERCISE           = "exercise";

include '../../include/baseTheme.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

$messBox = '';

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

$navigation[] = array('url' => "learningPathList.php?course=$course_code", 'name' => $langLearningPath);
$navigation[] = array('url' => "learningPathAdmin.php?course=$course_code&amp;path_id=".(int)$_SESSION['path_id'], 'name' => $langAdm);
$nameTools = $langInsertMyExerciseToolName;

// see checked exercises to add
$sql = "SELECT * FROM `".$TABLEEXERCISE."` WHERE course_id = $course_id AND active = 1";
$resultex = db_query($sql);

// for each exercise checked, try to add it to the learning path.
while ($listex = mysql_fetch_array($resultex) )
{
    if (isset($_REQUEST['insertExercise']) && isset($_REQUEST['check_'.$listex['id']]) )  //add
    {
        $insertedExercise = $listex['id'];

        // check if a module of this course already used the same exercise
        $sql = "SELECT * FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
                WHERE A.`module_id` = M.`module_id`
                  AND A.`path` LIKE \"". (int)$insertedExercise."\"
                  AND M.`contentType` = \"".CTEXERCISE_."\"
                  AND M.`course_id` = $course_id";

        $query = db_query($sql);
        $num = mysql_numrows($query);

        if($num == 0)
        {
            // select infos about added exercise
            $sql = "SELECT * FROM `".$TABLEEXERCISE."` WHERE `course_id` = $course_id AND `id` = ". (int)$insertedExercise;

            $result = db_query($sql);
            $exercise = mysql_fetch_array($result);

            if( !empty($exercise['description']) ) {
            	$comment = $exercise['description'];
            }
            else {
            	$comment = $langDefaultModuleComment;
            }

            // create new module
            $sql = "INSERT INTO `".$TABLEMODULE."`
                    (`course_id`, `name` , `comment`, `contentType`, `launch_data`)
                    VALUES ($course_id, '".addslashes($exercise['title'])."' , '".addslashes($comment)."', '".CTEXERCISE_."','')";
            $query = db_query($sql);
            $insertedExercice_id = mysql_insert_id();

            // create new asset
            $sql = "INSERT INTO `".$TABLEASSET."`
                    (`path` , `module_id` , `comment`)
                    VALUES ('". (int)$insertedExercise."', ". (int)$insertedExercice_id ." , '')";
            $query = db_query($sql);
            $insertedAsset_id = mysql_insert_id();
            $sql = "UPDATE `".$TABLEMODULE."`
                       SET `startAsset_id` = ". (int)$insertedAsset_id."
                     WHERE `module_id` = ". (int)$insertedExercice_id ."
                     AND `course_id` = $course_id";
            $query = db_query($sql);

            // determine the default order of this Learning path
            $result = db_query("SELECT MAX(`rank`) FROM `".$TABLELEARNPATHMODULE."` WHERE `learnPath_id` = ". (int)$_SESSION['path_id']);
            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;
            // finally : insert in learning path
            $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
                    (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
                    VALUES ('". (int)$_SESSION['path_id']."', '".(int)$insertedExercice_id."','".addslashes($langDefaultModuleAddedComment)."', ".$order.",'OPEN')";
            $query = db_query($sql);

            $messBox .= "<tr>". disp_message_box($exercise['title'] ." :  ".$langExInsertedAsModule."<br>", "success") . "</td></tr>";
        }
        else    // exercise is already used as a module in another learning path , so reuse its reference
        {
            // check if this is this LP that used this exercise as a module
            $sql = "SELECT *
                      FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                           `".$TABLEMODULE."` AS M,
                           `".$TABLEASSET."` AS A
                     WHERE M.`module_id` =  LPM.`module_id`
                       AND M.`startAsset_id` = A.`asset_id`
                       AND A.`path` = ". (int)$insertedExercise."
                       AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id'] ."
                       AND M.`course_id` = $course_id";

            $query2 = db_query($sql);
            $num = mysql_numrows($query2);

            if ($num == 0)     // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
            {
                $thisExerciseModule = mysql_fetch_array($query);
                // determine the default order of this Learning path
                $sql = "SELECT MAX(`rank`) FROM `".$TABLELEARNPATHMODULE."` WHERE `learnPath_id` = ". (int)$_SESSION['path_id'];
                $result = db_query($sql);

                list($orderMax) = mysql_fetch_row($result);
                $order = $orderMax + 1;
                // finally : insert in learning path
                $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
                        (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
                        VALUES (".(int)$_SESSION['path_id'].", ".(int)$thisExerciseModule['module_id'].",'".addslashes($langDefaultModuleAddedComment)."', ".$order.", 'OPEN')";
                $query = db_query($sql);

                // select infos about added exercise
                $sql = "SELECT * FROM `".$TABLEEXERCISE."` WHERE `course_id` = $course_id AND `id` = ". (int)$insertedExercise;

                $result = db_query($sql);
                $exercise = mysql_fetch_array($result);
                $messBox .= "<tr>". disp_message_box($exercise['title']." : ".$langExInsertedAsModule."<br>", "success") ."</td></tr>";
            }
            else {
                $messBox .= "<tr>". disp_message_box($listex['title']." : ".$langExAlreadyUsed."<br>", "caution") ."</td></tr>";
            }
        }
    } // end if request
} //end while

$tool_content .= "<table width='100%' class='tbl_alt'>";
$tool_content .= $messBox;
$tool_content .= "</table><br />";

//STEP ONE : display form to add an exercise
$tool_content .= display_my_exercises("", "");

//STEP TWO : display learning path content
//$tool_content .= disp_tool_title($langPathContentTitle);
//$tool_content .= '<a href="learningPathAdmin.php?course=$course_code">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';

// display list of modules used by this learning path
//$tool_content .= display_path_content();

$tool_content .= "
    <p align='right'><a href='learningPathAdmin.php?course=$course_code&amp;path_id=".(int)$_SESSION['path_id']."'>$langBackToLPAdmin</p>";

draw($tool_content, 2, null, $head_content);

