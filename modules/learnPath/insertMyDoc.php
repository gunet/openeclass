<?php

/*
Header
*/

/* TODO
- Modules ? (search source code)
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");
require_once("../../include/lib/fileManageLib.inc.php");

$require_current_course = TRUE;
$langFiles              = "learnPath";

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$imgRepositoryWeb       = "../../images/";

$dbTable                = "document";
$TABLEDOCUMENT          = "document";

require_once("../../include/baseTheme.php");
$tool_content = "";
$pwd = getcwd();

$courseDir   = "courses/".$currentCourseID."/document";
//$moduleDir   = $_course['path']."/modules";
$baseWorkDir = $webDir.$courseDir;
//$moduleWorkDir = $coursesRepositorySys.$moduleDir;

$nameTools = $langInsertMyDocToolName;
$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);
$navigation[] = array("url"=>"learningPathAdmin.php", "name"=> $langLearningPathAdmin);

$is_AllowedToEdit = $is_adminOfCourse;

if ( ! $is_AllowedToEdit ) die($langNotAllowed);

// $_SESSION
if ( !isset($_SESSION['path_id']) )
{
      die ("<center> Not allowed ! (path_id not set :@ )</center>");
}

mysql_select_db($currentCourseID);


/*======================================
       CLAROLINE MAIN
 ======================================*/

// FUNCTION NEEDED TO BUILD THE QUERY TO SELECT THE MODULES THAT MUST BE AVAILABLE

// 1)  We select first the modules that must not be displayed because
// as they are already in this learning path

function buildRequestModules()
{

 global $TABLELEARNPATHMODULE;
 global $TABLEMODULE;

 $firstSql = "SELECT `module_id`
              FROM `".$TABLELEARNPATHMODULE."` AS LPM
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

 /** To find which module must displayed we can also proceed  with only one query.
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

//####################################################################################\\
//################################ DOCUMENTS LIST ####################################\\
//####################################################################################\\

// FORM SENT
/*
 *
 * SET THE DOCUMENT AS A MODULE OF THIS LEARNING PATH
 *
 */

// evaluate how many form could be sent
if (!isset($dialogBox)) $dialogBox = "";

$iterator = 0;

if (!isset($_REQUEST['maxDocForm'])) $_REQUEST['maxDocForm'] = 0; 

while ($iterator <= $_REQUEST['maxDocForm'])
{
    $iterator++;

    if (isset($_REQUEST['submitInsertedDocument']) && isset($_POST['insertDocument_'.$iterator]) )
    {
        $insertDocument = str_replace('..', '',$_POST['insertDocument_'.$iterator]);
        
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
                        VALUES ('". addslashes($basename) ."' , '". addslashes($langDefaultModuleComment) . "', '".CTDOCUMENT_."','')";
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
                
                $addedDoc = $basename;

                $dialogBox .= $addedDoc ." ".$langDocInsertedAsModule."<br>";
            }
            else
            {
                // check if this is this LP that used this document as a module
                $sql = "SELECT *
                        FROM `".$TABLELEARNPATHMODULE."` AS LPM,
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
                     
                    $addedDoc =  $basename;

                    $dialogBox .= $addedDoc ." ".$langDocInsertedAsModule."<br>";
                }
                else
                {
                    $dialogBox .= $basename." : ".$langDocumentAlreadyUsed."<br>";
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
    /*
     * NOTE: Actually, only one of these variables is set.
     * By concatenating them, we eschew a long list of "if" statements
     */
}
else
{
    $curDirPath="";
}

if ($curDirPath == "/" || $curDirPath == "\\" || strstr($curDirPath, ".."))
{
    $curDirPath =""; // manage the root directory problem

    /*
     * The strstr($curDirPath, "..") prevent malicious users to go to the root directory
     */
}

$curDirName = basename($curDirPath);
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
}

/*--------------------------------------
  LOAD FILES AND DIRECTORIES INTO ARRAYS
  --------------------------------------*/
@chdir (realpath($baseWorkDir.$curDirPath))
or die("<center>
        <b>Wrong directory !</b>
        <br /> Please contact your platform administrator.</center>");
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
    }
    else
    {
        $fileList['comment'   ][] = false;
        $fileList['visibility'][] = false;
    }
} // end while ($file = readdir($handle))

/*
 * Sort alphabetically the File list
 */

if ($fileList)
{
    array_multisort($fileList['type'], $fileList['name'],
                    $fileList['size'], $fileList['date'],
                    $fileList['comment'],$fileList['visibility']);
}

/*----------------------------------------
        CHECK BASE INTEGRITY
--------------------------------------*/

if (isset($attribute))
{
    /*
     * check if the number of DB records is greater
     * than the numbers of files attributes previously given
     */

    if ( isset($attribute['path']) && isset($fileList['comment']) 
         && ( sizeof($attribute['path']) > (sizeof($fileList['comment']) + sizeof($fileList['visibility'])) ) )
    {
        /* SEARCH DB RECORDS WICH HAVE NOT CORRESPONDANCE ON THE DIRECTORY */
        foreach( $attribute['path'] as $chekinFile)
        {
            if (isset($dirNameList) && in_array(basename($chekinFile), $dirNameList))
                continue;
            elseif (isset($fileNameList) && $fileNameList && in_array(basename($chekinFile), $fileNameList))
                continue;
            else
                $recToDel[]= $chekinFile; // add chekinFile to the list of records to delete
        }

        /* BUILD THE QUERY TO DELETE DEPRECATED DB RECORDS */
        $nbrRecToDel = sizeof ($recToDel);
        $queryClause = "";
        
        for ($i=0; $i < $nbrRecToDel ;$i++)
        {
            $queryClause .= "path LIKE \"". addslashes($recToDel[$i]) ."%\"";
            if ($i < $nbrRecToDel-1) 
            {
                $queryClause .=" OR ";
            }
        }

        $sql = "DELETE
                FROM `".$dbTable."`
                WHERE ".$queryClause;
        db_query($sql);

        $sql = "DELETE
                FROM `".$dbTable."`
                WHERE `comment` LIKE ''
                  AND `visibility` LIKE 'v'";
        db_query($sql);

        /* The second query clean the DB 'in case of' empty records (no comment an visibility=v)
           These kind of records should'nt be there, but we never know... */

    }
} // end if (isset($attribute))

closedir($handle);
unset($attribute);

// display list of available documents

$tool_content .= display_my_documents($dialogBox) ;

//####################################################################################\\
//################################## MODULES LIST ####################################\\
//####################################################################################\\

$tool_content .= claro_disp_tool_title($langPathContentTitle);
$tool_content .= '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';

// display list of modules used by this learning path
$tool_content .= display_path_content();

chdir($pwd);
draw($tool_content, 2);

?>