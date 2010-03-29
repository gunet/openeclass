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
	insertMyDoc.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: insertMyDoc.php Revision: 1.18.2.1

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This script lists all available documents and the course
                  admin can add them to a learning path

    @Comments:

    @todo:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");
require_once("../../include/lib/fileManageLib.inc.php");
require_once("../../include/lib/textLib.inc.php");

$require_current_course = TRUE;
$require_prof = TRUE;
$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";
$imgRepositoryWeb       = "../../template/classic/img/";
$dbTable                = "document";
$TABLEDOCUMENT          = "document";

require_once("../../include/baseTheme.php");
$tool_content = "";
$pwd = getcwd();

$courseDir   = "courses/".$currentCourseID."/document";
$baseWorkDir = $webDir.$courseDir;
$InfoBox = "";
$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPath);
$navigation[] = array("url"=>"learningPathAdmin.php", "name"=> $langAdm);
$nameTools = $langInsertMyDocToolName;

mysql_select_db($currentCourseID);

// FUNCTION NEEDED TO BUILD THE QUERY TO SELECT THE MODULES THAT MUST BE AVAILABLE

// 1)  We select first the modules that must not be displayed because
// as they are already in this learning path



function buildRequestModules() {

 global $TABLELEARNPATHMODULE;
 global $TABLEMODULE;

 $firstSql = "SELECT `module_id` FROM `".$TABLELEARNPATHMODULE."` AS LPM
              WHERE LPM.`learnPath_id` = ". (int)$_SESSION['path_id'];

 $firstResult = db_query($firstSql);
 // 2) We build the request to get the modules we need
 $sql = "SELECT M.*
         FROM `".$TABLEMODULE."` AS M
         WHERE 1 = 1";

 while ($list=mysql_fetch_array($firstResult))
 {
    $sql .=" AND M.`module_id` != ". (int)$list['module_id'];
 }
  return $sql;
}

// -------------------------- documents list ----------------

// evaluate how many form could be sent
if (!isset($dialogBox)) $dialogBox = "";
if (!isset($style)) $style = "";

$iterator = 0;

if (!isset($_REQUEST['maxDocForm'])) $_REQUEST['maxDocForm'] = 0;

while ($iterator <= $_REQUEST['maxDocForm'])
{
    $iterator++;
    if (isset($_REQUEST['submitInsertedDocument']) && isset($_POST['insertDocument_'.$iterator]) )
    {
        $insertDocument = str_replace('..', '',$_POST['insertDocument_'.$iterator]);
        $filenameDocument = $_POST['filenameDocument_'.$iterator];
        $sourceDoc = $baseWorkDir.$insertDocument;

        if ( check_name_exist($sourceDoc) ) // source file exists ?
        {
            // check if a module of this course already used the same document
            $sql = "SELECT *
                    FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
                    WHERE A.`module_id` = M.`module_id`
                      AND A.`path` LIKE \"". addslashes($insertDocument)."\"
                      AND M.`contentType` = \"".CTDOCUMENT_."\"";
            $query = db_query($sql);
            $num = mysql_numrows($query);
            $basename = substr($insertDocument, strrpos($insertDocument, '/') + 1);

            if($num == 0)
            {
                // create new module
                $sql = "INSERT INTO `".$TABLEMODULE."`
                        (`name` , `comment`, `contentType`, `launch_data`)
                        VALUES ('". addslashes($filenameDocument) ."' , '". addslashes($langDefaultModuleComment) . "', '".CTDOCUMENT_."','')";
                $query = db_query($sql);
                $insertedModule_id = mysql_insert_id();

                // create new asset
                $sql = "INSERT INTO `".$TABLEASSET."`
                        (`path` , `module_id` , `comment`)
                        VALUES ('". addslashes($insertDocument)."', " . (int)$insertedModule_id . ", '')";
                $query = db_query($sql);
                $insertedAsset_id = mysql_insert_id();

                $sql = "UPDATE `".$TABLEMODULE."`
                        SET `startAsset_id` = " . (int)$insertedAsset_id . "
                        WHERE `module_id` = " . (int)$insertedModule_id . "";
                $query = db_query($sql);

                // determine the default order of this Learning path
                $sql = "SELECT MAX(`rank`)
                        FROM `".$TABLELEARNPATHMODULE."`";
                $result = db_query($sql);
                list($orderMax) = mysql_fetch_row($result);
                $order = $orderMax + 1;

                // finally : insert in learning path
                $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
                        (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
                        VALUES ('". (int)$_SESSION['path_id']."', '".(int)$insertedModule_id."','".addslashes($langDefaultModuleAddedComment)."', ".(int)$order.", 'OPEN')";
                $query = db_query($sql);
                $addedDoc = $filenameDocument;
                $InfoBox = $addedDoc ." ".$langDocInsertedAsModule."<br>";
                $style = "success";
                $tool_content .= "<table width=\"99%\"><tr>";
                $tool_content .= disp_message_box($InfoBox, $style);
                $tool_content .= "</td></tr></table>";
                $tool_content .= "<br />";
            }
            else
            {
                // check if this is this LP that used this document as a module
                $sql = "SELECT * FROM `".$TABLELEARNPATHMODULE."` AS LPM,
                             `".$TABLEMODULE."` AS M,
                             `".$TABLEASSET."` AS A
                        WHERE M.`module_id` =  LPM.`module_id`
                          AND M.`startAsset_id` = A.`asset_id`
                          AND A.`path` = '". addslashes($insertDocument)."'
                          AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id'];
                $query2 = db_query($sql);
                $num = mysql_numrows($query2);
                if ($num == 0)     // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
                {
                    $thisDocumentModule = mysql_fetch_array($query);
                    // determine the default order of this Learning path
                    $sql = "SELECT MAX(`rank`)
                            FROM `".$TABLELEARNPATHMODULE."`";
                    $result = db_query($sql);

                    list($orderMax) = mysql_fetch_row($result);
                    $order = $orderMax + 1;
                    // finally : insert in learning path
                    $sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
                            (`learnPath_id`, `module_id`, `specificComment`, `rank`,`lock`)
                            VALUES ('". (int)$_SESSION['path_id']."', '". (int)$thisDocumentModule['module_id']."','".addslashes($langDefaultModuleAddedComment)."', ".(int)$order.",'OPEN')";
                    $query = db_query($sql);
                    $addedDoc =  $filenameDocument;
                    $InfoBox = $addedDoc ." ".$langDocInsertedAsModule."<br>";
                    $style = "success_small";
                    $tool_content .= "<table width=\"99%\"><tr>";
                    $tool_content .= disp_message_box($InfoBox, $style);
                    $tool_content .= "</td></tr></table>";
                    $tool_content .= "<br />";
                }
                else
                {
                    $InfoBox = "<b>$filenameDocument</b>: ".$langDocumentAlreadyUsed."<br>";
                    $style = "caution_small";
                    $tool_content .= "<table width=\"99%\"><tr>";
                    $tool_content .= disp_message_box($InfoBox, $style);
                    $tool_content .= "</td></tr></table>";
                    $tool_content .= "<br />";
                }
            }
        }
    }
}

/*======================================
  DEFINE CURRENT DIRECTORY
 ======================================*/

if (isset($_REQUEST['openDir']) ) // $newDirPath is from createDir command (step 2) and $uploadPath from upload command
{
    $curDirPath = $_REQUEST['openDir'];
}
else
{
    $curDirPath="";
}

if ($curDirPath == "/" || $curDirPath == "\\" || strstr($curDirPath, ".."))
{
    $curDirPath =""; // manage the root directory problem
}

$d = mysql_fetch_array(db_query("SELECT filename FROM $TABLEDOCUMENT WHERE path='$curDirPath'"));
$curDirName = $d['filename'];
$parentDir  = dirname($curDirPath);

if ($parentDir == "/" || $parentDir == "\\")
{
        $parentDir =""; // manage the root directory problem
}

/*======================================
        READ CURRENT DIRECTORY CONTENT
  ======================================*/

/*--------------------------------------
  SEARCHING FILES & DIRECTORIES INFOS
              ON THE DB
  --------------------------------------*/

/* Search infos in the DB about the current directory the user is in */
$sql = "SELECT *
        FROM `".$TABLEDOCUMENT."`
        WHERE `path` LIKE \"". addslashes($curDirPath) ."/%\"
        AND `path` NOT LIKE \"". addslashes($curDirPath) ."/%/%\"";
$result = db_query($sql);
$attribute = array();

while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $attribute['path'      ][] = $row['path'      ];
    $attribute['visibility'][] = $row['visibility'];
    $attribute['comment'   ][] = $row['comment'   ];
    $attribute['filename'  ][] = $row['filename'  ];
}

/*--------------------------------------
  LOAD FILES AND DIRECTORIES INTO ARRAYS
  --------------------------------------*/
chdir(realpath($baseWorkDir.$curDirPath));
$handle = opendir(".");

define('A_DIRECTORY', 1);
define('A_FILE',      2);

$fileList = array();

while ($file = readdir($handle))
{
    if ($file == "." || $file == "..")
    {
        continue; // Skip current and parent directories
    }

    $fileList['name'][] = $file;

    if(is_dir($file))
    {
        $fileList['type'][] = A_DIRECTORY;
        $fileList['size'][] = false;
        $fileList['date'][] = false;
    }
    elseif(is_file($file))
    {
        $fileList['type'][] = A_FILE;
        $fileList['size'][] = filesize($file);
        $fileList['date'][] = filectime($file);
    }

    /*
     * Make the correspondance between
     * info given by the file system
     * and info given by the DB
     */

    if (!isset($dirNameList)) $dirNameList = array();
    $keyDir = sizeof($dirNameList)-1;

    if (isset($attribute))
    {
        if (isset($attribute['path']))
        {
            $keyAttribute = array_search($curDirPath."/".$file, $attribute['path']);
        }
        else
        {
            $keyAttribute = false;
        }
    }

    if ($keyAttribute !== false)
    {
        $fileList['comment'   ][] = $attribute['comment'   ][$keyAttribute];
        $fileList['visibility'][] = $attribute['visibility'][$keyAttribute];
        $fileList['filename'  ][] = $attribute['filename'  ][$keyAttribute];
    }
    else
    {
        $fileList['comment'   ][] = false;
        $fileList['visibility'][] = false;
        $fileList['filename'  ][] = false;
    }
} // end while ($file = readdir($handle))

/*
 * Sort alphabetically the File list
 */

if ($fileList)
{
    array_multisort($fileList['type'], $fileList['name'],
                    $fileList['size'], $fileList['date'],
                    $fileList['comment'],$fileList['visibility'],
                    $fileList['filename']);
}

closedir($handle);
unset($attribute);


// display list of available documents
$tool_content .= display_my_documents($dialogBox, $style) ;

	$tool_content .= "
    <br />
    <p align=\"right\"><a href=\"learningPathAdmin.php\">$langBackToLPAdmin</p>";

//################################## MODULES LIST ####################################\\

//$tool_content .= "<br />";
//$tool_content .= disp_tool_title($langPathContentTitle);
//$tool_content .= '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';

// display list of modules used by this learning path
//$tool_content .= display_path_content();
chdir($pwd);
draw($tool_content, 2, "learnPath");
