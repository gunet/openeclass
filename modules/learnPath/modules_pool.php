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
	modules_pool.php
	@last update: 29-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: modules_pool.php Revision: 1.32

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This is the page where the list of modules of the course
                  present on the platform can be browsed
                  user allowed to edit the course can
                  delete the modules form this page

    @Comments:

    @todo:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");

$require_current_course = TRUE;
$require_prof = TRUE;

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$imgRepositoryWeb = "../../template/classic/img/";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";
$body_action = "";

$navigation[]= array ("url"=>"learningPathList.php", "name"=> $langLearningPaths);

$nameTools = $langLearningObjectsInUse;
mysql_select_db($currentCourseID);
$head_content .= "<script>
        function confirmation (name)
        {
            if (confirm(\"".clean_str_for_javascript($langAreYouSureDeleteModule)."\"+ name))
                {return true;}
            else
                {return false;}
        }
        </script>";



// display use explication text
$tool_content .= "<p>".$langUseOfPool."</p><br />";

// HANDLE COMMANDS:
$cmd = ( isset($_REQUEST['cmd']) && is_string($_REQUEST['cmd']) )? (string)$_REQUEST['cmd'] : '';

switch( $cmd )
{
    // MODULE DELETE
    case "eraseModule" :
        if (isset($_GET['cmdid']) && is_numeric($_GET['cmdid']) ) {
			// used to physically delete the module  from server
			require_once("../../include/lib/fileManageLib.inc.php");

			$moduleDir   = "courses/".$currentCourseID."/modules";
			$moduleWorkDir = $webDir.$moduleDir;

			// delete all assets of this module
			$sql = "DELETE FROM `".$TABLEASSET."`
				WHERE `module_id` = ". (int)$_GET['cmdid'];
			db_query($sql);

			// delete from all learning path of this course but keep there id before
			$sql = "SELECT * FROM `".$TABLELEARNPATHMODULE."`
				WHERE `module_id` = ". (int)$_GET['cmdid'];
			$result = db_query($sql);

			$sql = "DELETE FROM `".$TABLELEARNPATHMODULE."`
				WHERE `module_id` = ". (int)$_GET['cmdid'];
			db_query($sql);

			// delete the module in modules table
			$sql = "DELETE FROM `".$TABLEMODULE."`
				WHERE `module_id` = ". (int)$_GET['cmdid'];
			db_query($sql);

			//delete all user progression concerning this module
			$sql = "DELETE FROM `".$TABLEUSERMODULEPROGRESS."`
				WHERE 1=0 ";

			while ($list = mysql_fetch_array($result))
			{
				$sql.=" OR `learnPath_module_id`=". (int)$list['learnPath_module_id'];
			}
			db_query($sql);

			// delete directory and it content
			claro_delete_file($moduleWorkDir."/module_".(int)$_GET['cmdid']);
        }
        break;

    // COMMAND RENAME :
    //display the form to enter new name
    case "rqRename" :
    	if (isset($_GET['module_id']) && is_numeric($_GET['module_id']) ) {
			//get current name from DB
			$query= "SELECT `name` FROM `".$TABLEMODULE."`
				WHERE `module_id` = '". (int)$_GET['module_id']."'";
			$result = db_query($query);
			$list = mysql_fetch_array($result);

			$tool_content .= disp_message_box("
				<form method=\"post\" name=\"rename\" action=\"".$_SERVER['PHP_SELF']."\">
                <table width=\"99%\" class=\"FormData\"><tbody><tr><th class=\"left\" width=\"220\">
                    <label for=\"newName\">".$langInsertNewModuleName."</label> :</th><td>
				    <input type=\"text\" size=\"40\" class=\"auth_input\"name=\"newName\" id=\"newName\" value=\"".htmlspecialchars($list['name'])."\"></input></td><td class=\"right\">
				    <input type=\"submit\" value=\"".$langImport."\" name=\"submit\">
                    <input type=\"hidden\" name=\"cmd\" value=\"exRename\">
				    <input type=\"hidden\" name=\"module_id\" value=\"".(int)$_GET['module_id']."\">
                </td></tr></thead></table>
				</form>")."";
        }
        break;

     //try to change name for selected module
    case "exRename" :
        //check if newname is empty
        if( isset($_POST["newName"]) && is_string($_POST["newName"])
        	&& $_POST["newName"] != "" && isset($_POST['module_id'])
        	&& is_numeric($_POST['module_id']) )
        {
            //check if newname is not already used in another module of the same course
            $sql="SELECT `name`
                  FROM `".$TABLEMODULE."`
                  WHERE `name` = '". mysql_real_escape_string($_POST['newName'])."'
                    AND `module_id` != '". (int)$_POST['module_id']."'";

            $query = db_query($sql);
            $num = mysql_numrows($query);
            if($num == 0 ) // "name" doesn't already exist
            {
                // if no error occurred, update module's name in the database
                $query="UPDATE `".$TABLEMODULE."`
                        SET `name`= '". mysql_real_escape_string($_POST['newName'])."'
                        WHERE `module_id` = '". (int)$_POST['module_id']."'";

                $result = db_query($query);
            }
            else
            {
                $tool_content .= disp_message_box($langErrorNameAlreadyExists, "caution");
                $tool_content .= "<br />";
            }
        }
        else
        {
            $tool_content .= disp_message_box($langErrorEmptyName, "caution");
            $tool_content .= "<br />";
        }
        break;

    //display the form to modify the comment
    case "rqComment" :
        if (isset($_GET['module_id']) && is_numeric($_GET['module_id']) )
        {
            //get current comment from DB
            $query="SELECT `comment`
                    FROM `".$TABLEMODULE."`
                    WHERE `module_id` = '". (int)$_GET['module_id']."'";
            $result = db_query($query);
            $comment = mysql_fetch_array($result);

            if( isset($comment['comment']) )
            {

                $tool_content .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n"
                    .'    <table width="99%" class="FormData"><tbody><tr><th class="left" width="160">'.$langComments.' :</th><td>'."\n"
                    .disp_html_area('comment', $comment['comment'], 2, 40)
                    ."<input type=\"hidden\" name=\"cmd\" value=\"exComment\">\n"
                    ."<input type=\"hidden\" name=\"module_id\" value=\"".(int)$_GET['module_id']."\">\n"
                    ."<input type=\"submit\" value=\"".$langImport."\">\n"
                    ."</td></tr></tbody></table>\n"
                    ."</form>\n";

                 $head_content .= disp_html_area_head("comment");

                 $body_action = "onload=\"initEditor()\"";
            }
            else
            {
            	$tool_content .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">\n"
                    .'<table><tr><td valign="top">'."\n"
                    .disp_html_area('comment', '', 2, 60)
                    ."</td></tr></table>\n"
                    ."<input type=\"hidden\" name=\"cmd\" value=\"exComment\">\n"
                    ."<input type=\"hidden\" name=\"module_id\" value=\"".(int)$_GET['module_id']."\">\n"
                    ."<input type=\"submit\" value=\"".$langOk."\">\n"
                    ."<br /><br />\n"
                    ."</form>\n";

                 $head_content .= disp_html_area_head("comment");
                 $body_action = "onload=\"initEditor()\"";
            }
        } // else no module_id
        break;

    //make update to change the comment in the database for this module
    case "exComment":
        if( isset($_POST['comment']) && is_string($_POST['comment'])
        	&& isset($_POST['module_id']) && is_numeric($_POST['module_id']) )
        {
            $sql = "UPDATE `".$TABLEMODULE."`
                    SET `comment` = \"". mysql_real_escape_string($_POST['comment']) ."\"
                    WHERE `module_id` = '". (int)$_POST['module_id']."'";
            db_query($sql);
        }
        break;
}




$sql = "SELECT M.*, count(M.`module_id`) AS timesUsed
        FROM `".$TABLEMODULE."` AS M
          LEFT JOIN `".$TABLELEARNPATHMODULE."` AS LPM ON LPM.`module_id` = M.`module_id`
        WHERE M.`contentType` != \"".CTSCORM_."\"
          AND M.`contentType` != \"".CTSCORMASSET_."\"
          AND M.`contentType` != \"".CTLABEL_."\"
        GROUP BY M.`module_id`
        ORDER BY M.`name` ASC, M.`contentType`ASC, M.`accessibility` ASC";

$result = db_query($sql);
$atleastOne = false;

$query_num_results = db_query($sql);
$num_results = mysql_numrows($query_num_results);


if (!$num_results == 0) {

$tool_content .= "
    <table width=\"99%\" class=\"LearnPathSum\">
    <thead>
    <tr class=\"LP_header\">
      <td colspan=\"2\"><div align=\"left\"><b>".$langLearningObjects."</b></div></td>
      <td width=\"10%\"><div align=\"right\"><b>".$langTools."</b>&nbsp;&nbsp;&nbsp;</div></td>\n";
$tool_content .="    </tr>\n".
      "    </thead>\n".
      "    <tbody>";
}
// Display modules of the pool of this course

while ($list = mysql_fetch_array($result))
{
    //DELETE , RENAME, COMMENT

    $contentType_img = selectImage($list['contentType']);
    $contentType_alt = selectAlt($list['contentType']);
    $tool_content .= "
    <tr>
      <td align=\"left\" width=\"1%\" valign=\"top\"><img src=\"".$imgRepositoryWeb.$contentType_img."\" alt=\"".$contentType_alt."\" title=\"".$contentType_alt."\" /></td>
      <td align=\"left\"><b>".$list['name']."</b>";

    if ( $list['comment'] )
    {
        $tool_content .= "<br /><small style=\"color: #a19b99;\"><b>$langComments</b>: ".$list['comment']."</small>";
    }

    $tool_content .= "</td>
      <td align='right'><a href=\"".$_SERVER['PHP_SELF']."?cmd=eraseModule&amp;cmdid=".$list['module_id']."\" onClick=\"return confirmation('".clean_str_for_javascript($list['name'] . $langUsedInLearningPaths . $list['timesUsed'])."');\"><img src=\"".$imgRepositoryWeb."delete.gif\" border=\"0\" alt=\"".$langDelete."\" title=\"".$langDelete."\" /></a>&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqRename&amp;module_id=".$list['module_id']."\"><img src=\"".$imgRepositoryWeb."edit.gif\" border=0 alt=\"$langRename\" title=\"$langRename\" /></a>&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?cmd=rqComment&amp;module_id=".$list['module_id']."\"><img src=\"".$imgRepositoryWeb."comment.gif\" border=0 alt=\"$langComment\" title=\"$langComment\" /></a></td>\n";
    $tool_content .= "    </tr>";

    $atleastOne = true;
} //end while another module to display

$tool_content .= "
    </tbody>\n    </table>";

if ($atleastOne == false) {
    $tool_content .= "
      <p class=\"alert1\">".$langNoModule."</p>";
}


draw($tool_content, 2, "learnPath", $head_content, $body_action);

?>
