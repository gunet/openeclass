<?php

/*
Header
*/

require_once("../../include/lib/learnPathLib.inc.php");

$require_current_course = TRUE;
$langFiles              = "learnPath";

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

//$TABLEDOCUMENT          = "document";

require_once("../../include/baseTheme.php");
$tool_content = "";

$nameTools = $langInsertMyDescToolName;
$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);
$navigation[] = array("url"=>"learningPathAdmin.php", "name"=> $langLearningPathAdmin);

if ( ! $is_adminOfCourse ) die($langNotAllowed);

// $_SESSION
if ( !isset($_SESSION['path_id']) )
{
      die ("<center> Not allowed ! (path_id not set :@ )</center>");
}


mysql_select_db($currentCourseID);

/*======================================*/

// TODO: check if course description is already in the pool of modules
// and if it is, use that instead of adding it as new

// SQL Checks
// check if a module of this course already used the same document
$sql = "SELECT *
	FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
	WHERE A.`module_id` = M.`module_id`
	AND M.`contentType` = \"".CTCOURSE_DESCRIPTION_."\"";
$query = db_query($sql);
$num = mysql_numrows($query);

if ($num == 0)
{
	// create new module
	// TODO: name goes from langWhatever
	$sql = "INSERT INTO `".$TABLEMODULE."`
		(`name`, `contentType`)
		VALUES ('Course Description', '".CTCOURSE_DESCRIPTION_."' )";
	$query = db_query($sql);

	$insertedModule_id = mysql_insert_id();

	// create new asset
	$sql = "INSERT INTO `".$TABLEASSET."`
		(`path` , `module_id`, `comment` )
		VALUES ('', " . (int)$insertedModule_id . ", '' )";
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
		(`learnPath_id`, `module_id`, `rank`, `lock`)
		VALUES ('". (int)$_SESSION['path_id']."', '". (int)$insertedModule_id."',
		" . (int)$order . ", 'OPEN')";
	$query = db_query($sql);

	$tool_content .= "done";
}
else 
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
		(`learnPath_id`, `module_id`, `rank`, `lock`)
		VALUES ('". (int)$_SESSION['path_id']."', '".(int)$thisDocumentModule['module_id']."',
		" . (int)$order . ", 'OPEN')";
	$query = db_query($sql);

	$tool_content .= "done2";
}
 
draw($tool_content, 2, "learnPath");

?>
