<?php

/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
                     Yannis Exidaridis <jexi@noc.uoa.gr> 
                     Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	insertMyExercise.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: insertMyExercise.php Revision: 1.14.2.1
	      
	Claroline authors: Piraux Sébastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================        
    @Description: This script lists all available exercises and the course
                  admin can add them to a learning path

    @Comments:
 
    @todo: 
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");

$require_current_course = TRUE;
$langFiles = "learnPath";

/*
 * DB tables definition
 */

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

// exercises table name
$TABLEEXERCISES         = "exercices";

$imgRepositoryWeb = "../../template/classic/img/";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";
$dialogBox = "";
$style = "";

$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);
if ( ! $is_adminOfCourse ) claro_die($langNotAllowed);
$navigation[] = array("url"=>"learningPathAdmin.php", "name"=> $langLearningPathAdmin);
$nameTools = $langInsertMyExerciseToolName;

// $_SESSION
if ( !isset($_SESSION['path_id']) )
{
      claro_die ("<center> Not allowed ! (path_id not set :@ )</center>");
}

mysql_select_db($currentCourseID);

/* ---------------------------------------------------------- */

/*======================================
       CLAROLINE MAIN
  ======================================*/

// see checked exercises to add

$sql = "SELECT *
        FROM `".$TABLEEXERCISES;
$resultex = db_query($sql);

// for each exercise checked, try to add it to the learning path.

while ($listex = mysql_fetch_array($resultex) )
{

    if (isset($_REQUEST['insertExercise']) && isset($_REQUEST['check_'.$listex['id']]) )  //add
    {
        $insertedExercise = $listex['id'];

        // check if a module of this course already used the same exercise
        $sql = "SELECT *
                FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
                WHERE A.`module_id` = M.`module_id`
                  AND A.`path` LIKE \"". (int)$insertedExercise."\"
                  AND M.`contentType` = \"".CTEXERCISE_."\"";

        $query = db_query($sql);

        $num = mysql_numrows($query);

        if($num == 0)
        {
            // select infos about added exercise
            $sql = "SELECT *
                    FROM `".$TABLEEXERCISES."`
                    WHERE `id` = ". (int)$insertedExercise;

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
                    (`name` , `comment`, `contentType`, `launch_data`)
                    VALUES ('".addslashes($exercise['titre'])."' , '".addslashes($comment)."', '".CTEXERCISE_."','')";
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
                     WHERE `module_id` = ". (int)$insertedExercice_id;
            $query = db_query($sql);

            // determine the default order of this Learning path
            $result = db_query("SELECT MAX(`rank`)
                                     FROM `".$TABLELEARNPATHMODULE."`");

            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;
            // finally : insert in learning path
            $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
                    (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
                    VALUES ('". (int)$_SESSION['path_id']."', '".(int)$insertedExercice_id."','".addslashes($langDefaultModuleAddedComment)."', ".$order.",'OPEN')";
            $query = db_query($sql);

            $dialogBox .= $exercise['titre'] ." :  ".$langExInsertedAsModule."<br>";
            $style = "success";
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
                       AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id'];

            $query2 = db_query($sql);
            $num = mysql_numrows($query2);

            if ($num == 0)     // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
            {
                $thisExerciseModule = mysql_fetch_array($query);
                // determine the default order of this Learning path
                $sql = "SELECT MAX(`rank`)
                        FROM `".$TABLELEARNPATHMODULE."`";
                $result = db_query($sql);

                list($orderMax) = mysql_fetch_row($result);
                $order = $orderMax + 1;

                // finally : insert in learning path
                $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
                        (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
                        VALUES (".(int)$_SESSION['path_id'].", ".(int)$thisExerciseModule['module_id'].",'".addslashes($langDefaultModuleAddedComment)."', ".$order.", 'OPEN')";
                $query = db_query($sql);

                // select infos about added exercise
                $sql = "SELECT *
                        FROM `".$TABLEEXERCISES."`
                        WHERE `id` = ". (int)$insertedExercise;

                $result = db_query($sql);
                $exercise = mysql_fetch_array($result);
                $dialogBox .= $exercise['titre']." : ".$langExInsertedAsModule."<br>";
                $style = "success";
            }
            else
            {
                $dialogBox .= $listex['titre']." : ".$langExAlreadyUsed."<br>";
                $style = "caution";
            }
        }
    }
} //end while

//STEP ONE : display form to add an exercise
$tool_content .= display_my_exercises($dialogBox, $style);

//STEP TWO : display learning path content
$tool_content .= claro_disp_tool_title($langPathContentTitle);
$tool_content .= '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';

// display list of modules used by this learning path
$tool_content .= display_path_content();

draw($tool_content, 2, "learnPath");

?>