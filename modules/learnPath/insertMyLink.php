<?php
/* ========================================================================
 * Open eClass 2.5
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
$require_editor = TRUE;

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

require_once("../../include/baseTheme.php");

$dialogBox = "";

$navigation[] = array("url"=>"learningPathList.php?course=$code_cours", "name"=> $langLearningPath);
$navigation[] = array("url"=>"learningPathAdmin.php?course=$code_cours&amp;path_id=".(int)$_SESSION['path_id'], "name"=> $langAdm);
$nameTools = $langInsertMyLinkToolName;

mysql_select_db($mysqlMainDb);
$iterator = 1;

if (!isset($_POST['maxLinkForm'])) $_POST['maxLinkForm'] = 0;

while ($iterator <= $_POST['maxLinkForm']) {
	if (isset($_POST['submitInsertedLink']) && isset($_POST['insertLink_'.$iterator])) {

		// get from DB everything related to the link
		$sql = "SELECT * FROM `$mysqlMainDb`.link WHERE course_id = $cours_id AND `id` = \""
			. intval($_POST['insertLink_'.$iterator]) ."\"";
		$row = db_query_get_single_row($sql);

		// check if this link is already a module
		$sql = "SELECT * FROM `".$TABLEMODULE."` AS M, `".$TABLEASSET."` AS A
        		WHERE A.`module_id` = M.`module_id`
        		AND M.`name` LIKE \"" .addslashes($row['title']) ."\"
        		AND M.`comment` LIKE \"" .addslashes($row['description']) ."\"
        		AND A.`path` LIKE \"" .addslashes($row['url']) ."\"
        		AND M.`contentType` = \"".CTLINK_."\"
        		AND M.`course_id` = $cours_id";
		$query0 = db_query($sql);
        $num = mysql_numrows($query0);

        if ($num == 0) {
			// create new module
			$sql = "INSERT INTO `".$TABLEMODULE."`
					(`course_id`, `name` , `comment`, `contentType`, `launch_data`)
					VALUES ($cours_id, '". addslashes($row['title']) ."' , '"
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
				WHERE `module_id` = " . (int)$insertedModule_id . "
				AND `course_id` = $cours_id";
			$query = db_query($sql);

			// determine the default order of this Learning path
			$sql = "SELECT MAX(`rank`) FROM `".$TABLELEARNPATHMODULE."` WHERE `learnPath_id` = ". (int)$_SESSION['path_id'];
			$result = db_query($sql);

			list($orderMax) = mysql_fetch_row($result);
			$order = $orderMax + 1;

			// finally : insert in learning path
			$sql = "INSERT INTO `".$TABLELEARNPATHMODULE."`
				(`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock`)
				VALUES ('". (int)$_SESSION['path_id']."', '".(int)$insertedModule_id."','"
				."', ".(int)$order.", 'OPEN')";
			$query = db_query($sql);

			$dialogBox .= q($row['title'])." : ".$langLinkInsertedAsModule."<br />";
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
				AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id'] ."
				AND M.`course_id` = $cours_id";
			$query2 = db_query($sql);
			$num = mysql_numrows($query2);

			if($num == 0) { // used in another LP but not in this one, so reuse the module id reference instead of creating a new one

				$thisLinkModule = mysql_fetch_array($query0);
				// determine the default order of this Learning path
				$sql = "SELECT MAX(`rank`)
					FROM `".$TABLELEARNPATHMODULE."`
					WHERE `learnPath_id` = ". (int)$_SESSION['path_id'];
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

				$dialogBox .= q($row['title'])." : ".$langLinkInsertedAsModule."<br />";
				$style = "success";
			}
			else {
				$dialogBox .= q($row['title'])." : ".$langLinkAlreadyUsed."<br />";
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

$tool_content .= showlinks();
//$tool_content .= "<br />";
//$tool_content .= disp_tool_title($langPathContentTitle);
//$tool_content .= '<a href="learningPathAdmin.php?course=$code_cours">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';
// display list of modules used by this learning path
//$tool_content .= display_path_content();

	$tool_content .= "
    <br />
    <p align=\"right\"><a href=\"learningPathAdmin.php?course=$code_cours&amp;path_id=".(int)$_SESSION['path_id']."\">$langBackToLPAdmin</a>";
draw($tool_content, 2);


function showlinks()
{
	global $langComment, $langAddModule, $langName, $langSelection,
               $langAddModulesButton, $cours_id, $mysqlMainDb, $code_cours,
               $themeimg;

        $sqlLinks = "SELECT * FROM `$mysqlMainDb`.link
                              WHERE course_id = $cours_id ORDER BY `order` DESC";
	$result = db_query($sqlLinks);
	$numberoflinks=mysql_num_rows($result);

    $output = "
<form action='$_SERVER[PHP_SELF]?course=$code_cours' method='POST'>
                      <table width='100%' class='tbl_alt'>
                    
                      <tr>
                        <th colspan='2'>$langName</th>
                        <th width='50'>$langSelection</th>
                      </tr>
                      
                      <tbody>";
	$i=1;
	while ($myrow = mysql_fetch_array($result))
	{
		$myrow[3] = parse_tex($myrow[3]);
		$output .= 	"
    <tr>
      <td width='1' valign='top'><img src='$themeimg/links_on.png' border='0'></td>
      <td align='left' valign='top'><a href='../link/link_goto.php?course=$code_cours&amp;link_id=".$myrow[0]."&amp;link_url=".urlencode($myrow[1])."' target='_blank'>".q($myrow[2])."</a>
      <br />
      <small class='comments'>".q($myrow[3])."</small></td>";
		$output .= 	"
      <td><div align='center'><input type='checkbox' name='insertLink_".$i."' id='insertLink_".$i."' value='$myrow[0]' /></div></td>
    </tr>";
		$i++;
	}
	$output .= "
    <tr>
      <th colspan='3'>
        <div align='right'>
          <input type='hidden' name='maxLinkForm' value ='" . ($i-1) ."' />
          <input type='submit' name='submitInsertedLink' value='$langAddModulesButton'/>
        </div></th>
      </tr>
    </tbody>
    </table>
    </form>";
	return $output;
}
