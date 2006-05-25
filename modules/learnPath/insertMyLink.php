<?php

/*
Header
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");

$require_current_course = TRUE;
$langFiles              = "learnPath";

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$dbname                 = $_SESSION['dbname'];
$tbl_link               = "liens";

$imgRepositoryWeb       = "../../images/";

require_once("../../include/baseTheme.php");
$tool_content = "";
$dialogBox = "";

$nameTools = $langInsertMyLinkToolName;
$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);
$navigation[] = array("url"=>"learningPathAdmin.php", "name"=> $langLearningPathAdmin);

if ( ! $is_adminOfCourse ) die($langNotAllowed);

// $_SESSION
if ( !isset($_SESSION['path_id']) )
{
      die ("<center> Not allowed ! (path_id not set :@ )</center>");
}


mysql_select_db($currentCourseID);

$iterator = 1;

if (!isset($_POST['maxLinkForm'])) $_POST['maxLinkForm'] = 0; 

while ($iterator <= $_POST['maxLinkForm']) {
	if (isset($_POST['submitInsertedLink']) && isset($_POST['insertLink_'.$iterator])) {

		// get from DB everything related to the link
		$sql = "SELECT * FROM `".$tbl_link."` WHERE `id` = \"" 
			.$_POST['insertLink_'.$iterator] ."\"";
		$row = db_query_get_single_row($sql);
		
		// check if this link is already a module
		$sql = "SELECT *
        		FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
        		WHERE A.`module_id` = M.`module_id`
        		AND M.`name` LIKE \"" .addslashes($row['titre']) ."\"
        		AND M.`comment` LIKE \"" .addslashes($row['description']) ."\"
        		AND A.`path` LIKE \"" .addslashes($row['url']) ."\"
        		AND M.`contentType` = \"".CTLINK_."\"";
		$query0 = db_query($sql);
        $num = mysql_numrows($query0);
        
        if ($num == 0) { 
			// create new module
			$sql = "INSERT INTO `".$TABLEMODULE."`
					(`name` , `comment`, `contentType`, `launch_data`)
					VALUES ('". addslashes($row['titre']) ."' , '"
					.addslashes($row['description']) . "', '".CTLINK_."','')";
			$query = db_query($sql);

			$insertedModule_id = mysql_insert_id();

			// create new asset
			$sql = "INSERT INTO `".$TABLEASSET."`
					(`path` , `module_id` , `comment`)
					VALUES ('". addslashes($row['url'])."', " 
					. (int)$insertedModule_id . ", '')";
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
					VALUES ('". (int)$_SESSION['path_id']."', '".(int)$insertedModule_id."','" 
					."', ".(int)$order.", 'OPEN')";
			$query = db_query($sql);
			
			$dialogBox .= $row['titre']." : ".$langLinkInsertedAsModule."<br />";
			$style = "success";
        } 
        else { 
        	// check if this is this LP that used this document as a module
        	$sql = "SELECT *
					FROM `".$TABLELEARNPATHMODULE."` AS LPM,
						`".$TABLEMODULE."` AS M,
						`".$TABLEASSET."` AS A
					WHERE M.`module_id` =  LPM.`module_id`
					AND M.`startAsset_id` = A.`asset_id`
					AND A.`path` = '". addslashes($row['url'])."'
					AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id'];
			$query2 = db_query($sql);
			$num = mysql_numrows($query2);
			
			if($num == 0) { // used in another LP but not in this one, so reuse the module id reference instead of creating a new one
				
				$thisLinkModule = mysql_fetch_array($query0);
				// determine the default order of this Learning path
				$sql = "SELECT MAX(`rank`)
						FROM `".$TABLELEARNPATHMODULE."`";
				$result = db_query($sql);

				list($orderMax) = mysql_fetch_row($result);
				$order = $orderMax + 1;
				
				// finally : insert in learning path
				$sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
						(`learnPath_id`, `module_id`, `specificComment`, `rank`,`lock`)
						VALUES ('". (int)$_SESSION['path_id']."', '"
						.(int)$thisLinkModule['module_id']."','"
						."', ".(int)$order.",'OPEN')";
				$query = db_query($sql);
				
				$dialogBox .= $row['titre']." : ".$langLinkInsertedAsModule."<br />";
				$style = "success";
			}
			else {
				$dialogBox .= $row['titre']." : ".$langLinkAlreadyUsed."<br />";
				$style = "caution";
			}
        } 

	} 
	
	$iterator++;
} 


if (isset($dialogBox) && $dialogBox != "") {
    $tool_content .= "<div id=\"tool_operations\"><span class=\"operation\">";
    $tool_content .= claro_disp_message_box($dialogBox, $style);
    $tool_content .= "</span></div>";
}

$tool_content .= showlinks($tbl_link, $dbname);

$tool_content .= "<br /><div id=\"tool_operations\"><span class=\"operation\">";
$tool_content .= claro_disp_tool_title($langPathContentTitle);
$tool_content .= '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';
// display list of modules used by this learning path
$tool_content .= display_path_content();
$tool_content .= "</span></div>";


draw($tool_content, 2, "learnPath");


function showlinks($tbl_link, $dbname)
{
	global $langComment;
	global $langAddModule;
	global $langName;
	global $langAddModulesButton;
	
	$sqlLinks = "SELECT * FROM `".$tbl_link."` ORDER BY ordre DESC";
	$result = db_query($sqlLinks, $dbname);
	$numberoflinks=mysql_num_rows($result);

	$output = "";
	$output .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">';
	$output .= "<table width=\"99%\">";
	$output .= "<thead><tr>";
	$output .= "<th>$langAddModule</th>";
	$output .= "<th>$langName</th>";
	$output .= "<th>$langComment</th></tr></thead>";
	$output .= "<tbody>";
	$i=1;
	while ($myrow = mysql_fetch_array($result))
	{
		$myrow[3] = parse_tex($myrow[3]);
		$output .= 	"<tr>
		<td align=\"center\">
		<input type=\"checkbox\" name=\"insertLink_".$i."\" id=\"insertLink_".$i."\" 
		value=\"$myrow[0]\" />
		</td>

		<td>
        <a href=\"../link/link_goto.php?link_id=".$myrow[0]."&link_url=".urlencode($myrow[1])."\" target=\"_blank\">
        <img src=\"../../images/links.gif\" border=\"0\">&nbsp;
        ".$myrow[2]."</a>\n
		</td><td>".$myrow[3]."";
	
		$output .= 	"</td></tr>";
		$i++;
	}

	$output .=  "</td></tr>";
	$output .= '<tr>'
			."<td colspan=\"3\" align=\"left\">"
			."<input type=\"hidden\" name=\"maxLinkForm\" value =\"" .($i-1) ."\" />"
			."<input type=\"submit\" name=\"submitInsertedLink\"" 
			."value=\"$langAddModulesButton\" />"
			."</td>"
			."</tr>";
	$output .=  "</tbody></table></form>";	

	return $output;
}

?>