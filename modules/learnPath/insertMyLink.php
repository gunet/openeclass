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

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");

$require_current_course = TRUE;
$require_prof = TRUE;

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$tbl_link               = "liens";
$imgRepositoryWeb       = "../../template/classic/img/";

require_once("../../include/baseTheme.php");
$tool_content = "";
$dialogBox = "";

$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPath);
$navigation[] = array("url"=>"learningPathAdmin.php", "name"=> $langAdm);
$nameTools = $langInsertMyLinkToolName;

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
		$sql = "SELECT * FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
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

			$dialogBox .= $row['titre']." : ".$langLinkInsertedAsModule."<br />";
			$style = "success";
        }
        else {
        	// check if this is this LP that used this document as a module
        	$sql = "SELECT * FROM `".$TABLELEARNPATHMODULE."` AS LPM,
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
    $tool_content .= "<table width=\"99%\"><tr>";
    $tool_content .= disp_message_box($dialogBox, $style);
    $tool_content .= "</td></tr></table>";
    $tool_content .= "<br />";
}

$tool_content .= showlinks($tbl_link);
//$tool_content .= "<br />";
//$tool_content .= disp_tool_title($langPathContentTitle);
//$tool_content .= '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';
// display list of modules used by this learning path
//$tool_content .= display_path_content();

	$tool_content .= "
    <br />
    <p align=\"right\"><a href=\"learningPathAdmin.php\">$langBackToLPAdmin</a>";
draw($tool_content, 2, "learnPath");


function showlinks($tbl_link)
{
	global $langComment;
	global $langAddModule;
	global $langName, $langSelection;
	global $langAddModulesButton;

	$sqlLinks = "SELECT * FROM `".$tbl_link."` ORDER BY ordre DESC";
	$result = db_query($sqlLinks);
	$numberoflinks=mysql_num_rows($result);

	$output = "";
	$output .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">';
	$output .= "
    <table width=\"99%\" class=\"LearnPathSum\">
    <thead>
    <tr align=\"center\" class=\"LP_header\">
      <td width=\"1%\">&nbsp;</td>
      <td><div align=\"left\">$langName</div></td>
      <td width=\"20%\"><div align=\"center\">$langSelection</div></td>
    </tr>
    </thead>
    <tbody>";
	$i=1;
	while ($myrow = mysql_fetch_array($result))
	{
		$myrow[3] = parse_tex($myrow[3]);
		$output .= 	"
    <tr>
      <td valign=\"top\"><img src=\"../../template/classic/img/links_on.gif\" border=\"0\"></td>
      <td align=\"left\" valign=\"top\"><a href=\"../link/link_goto.php?link_id=".$myrow[0]."&link_url=".urlencode($myrow[1])."\" target=\"_blank\">".$myrow[2]."</a>
      <br />
      <small class=\"comments\">".$myrow[3]."</small></td>";
		$output .= 	"
      <td><div align=\"center\"><input type=\"checkbox\" name=\"insertLink_".$i."\" id=\"insertLink_".$i."\" value=\"$myrow[0]\" /></div></td>
    </tr>";
		$i++;
	}
	$output .= "
    <tr>
      <td colspan=\"2\">&nbsp;</td>
      <td align=\"right\">
        <input type=\"hidden\" name=\"maxLinkForm\" value =\"" .($i-1) ."\" />
        <input type=\"submit\" name=\"submitInsertedLink\" value=\"$langAddModulesButton\" class=\"LP_button\"/>
      </td>
    </tr>
    </tbody>
    </table>
    </form>";
	return $output;
}

?>
