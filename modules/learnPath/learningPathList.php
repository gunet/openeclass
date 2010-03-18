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
	learningPathList.php
	@last update: 29-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: learningPathList Revision: 1.56

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================
    @Description: This file displays the list of all learning paths available
                  for the course.

                  Display :
                  - Name of tool
                  - Introduction text for learning paths
                  - (admin of course) link to create new empty learning path
                  - (admin of course) link to import (upload) a learning path
                  - list of available learning paths
                  - (student) only visible learning paths
                  - (student) the % of progression into each learning path
                  - (admin of course) all learning paths with
                  - modify, delete, statistics, visibility and order, options

    @Comments:

    @todo:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileManageLib.inc.php");

$require_current_course = TRUE;
$require_help           = TRUE;
$helpTopic              = "Path";

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

define('CLARO_FILE_PERMISSIONS', 0777);

require_once("../../include/baseTheme.php");

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_LP');
/**************************************/

$head_content = "";
$tool_content = "";
$style= "";

if (!add_units_navigation(TRUE)) {
	$nameTools = $langLearningPaths;
}

if (isset($_GET['cmd']) && $_GET['cmd'] == 'export'
	&& isset($_GET['path_id']) && is_numeric($_GET['path_id']) && $is_adminOfCourse)
{
      mysql_select_db($currentCourseID);
      require_once("include/scormExport.inc.php");
      $scorm = new ScormExport((int)$_GET['path_id']);
      if (!$scorm->export())
      {
          $dialogBox = '<b>'.$langScormErrorExport.'</b><br />'."\n".'<ul>'."\n";
          foreach( $scorm->getError() as $error)
          {
              $dialogBox .= '<li>' . $error . '</li>'."\n";
          }
          $dialogBox .= '<ul>'."\n";
      }
} // endif $cmd == export

if ( isset($_GET['cmd']) && $_GET['cmd'] == 'export12'
	&& isset($_GET['path_id']) && is_numeric($_GET['path_id']) && $is_adminOfCourse )
{
      mysql_select_db($currentCourseID);
      require_once("include/scormExport12.inc.php");
      $scorm = new ScormExport((int)$_GET['path_id']);
      if (!$scorm->export())
      {
          $dialogBox = '<b>'.$langScormErrorExport.'</b><br />'."\n".'<ul>'."\n";
          foreach( $scorm->getError() as $error)
          {
              $dialogBox .= '<li>' . $error . '</li>'."\n";
          }
          $dialogBox .= '<ul>'."\n";
      }
} // endif $cmd == export12

mysql_select_db($currentCourseID);

if ($is_adminOfCourse) {
	$head_content .= "<script type='text/javascript'>
          function confirmation (name)
          {
              if (confirm('". clean_str_for_javascript($langConfirmDelete) . " ' + name + '? " . $langModuleStillInPool . "'))
                  {return true;}
              else
                  {return false;}
          }
          </script>";
	$head_content .= "<script type='text/javascript'>
          function scormConfirmation (name)
          {
              if (confirm('". clean_str_for_javascript($langAreYouSureToDeleteScorm) .  "' + name + '?'))
                  {return true;}
              else
                  {return false;}
          }
          </script>";

	if (isset($_REQUEST['cmd'])) {
		// execution of commands
		switch ( $_REQUEST['cmd'] ) {
			// DELETE COMMAND
			case "delete" :
				if (is_dir($webDir."courses/".$currentCourseID."/scormPackages/path_".$_GET['del_path_id']))
				{
					$findsql = "SELECT M.`module_id`
						FROM  `".$TABLELEARNPATHMODULE."` AS LPM, `".$TABLEMODULE."` AS M
						WHERE LPM.`learnPath_id` = ". (int)$_GET['del_path_id']."
						AND ( M.`contentType` = '".CTSCORM_."' OR M.`contentType` = '".CTSCORMASSET_."' OR M.`contentType` = '".CTLABEL_."')
						AND LPM.`module_id` = M.`module_id`";
					$findResult = db_query($findsql);

					// Delete the startAssets
					$delAssetSql = "DELETE FROM `".$TABLEASSET."` WHERE 1=0";

					while ($delList = mysql_fetch_array($findResult))
					{
						$delAssetSql .= " OR `module_id`=". (int)$delList['module_id'];
					}
					db_query($delAssetSql);

					// DELETE the SCORM modules
					$delModuleSql = "DELETE FROM `".$TABLEMODULE."`
					WHERE (`contentType` = '".CTSCORM_."' OR `contentType` = '".CTSCORMASSET_."' OR `contentType` = '".CTLABEL_."') AND (1=0";

					if (mysql_num_rows($findResult)>0)
					{
						mysql_data_seek($findResult,0);
					}
					while ($delList = mysql_fetch_array($findResult))
					{
						$delModuleSql .= " OR `module_id`=". (int)$delList['module_id'];
					}
					$delModuleSql .= ")";
					db_query($delModuleSql);

					// DELETE the directory containing the package and all its content
					$real = realpath($webDir."courses/".$currentCourseID."/scormPackages/path_".$_GET['del_path_id']);
					claro_delete_file($real);

				}   // end of dealing with the case of a scorm learning path.
				else
				{
					$findsql = "SELECT M.`module_id`
						FROM  `".$TABLELEARNPATHMODULE."` AS LPM,
						`".$TABLEMODULE."` AS M
						WHERE LPM.`learnPath_id` = ". (int)$_GET['del_path_id']."
						AND M.`contentType` = '".CTLABEL_."'
						AND LPM.`module_id` = M.`module_id`";
					$findResult = db_query($findsql);
					// delete labels of non scorm learning path
					$delLabelModuleSql = "DELETE FROM `".$TABLEMODULE."` WHERE 1=0";

					while ($delList = mysql_fetch_array($findResult))
					{
						$delLabelModuleSql .= " OR `module_id`=". (int)$delList['module_id'];
					}
					$query = db_query($delLabelModuleSql);
				}

				// delete everything for this path (common to normal and scorm paths) concerning modules, progression and path

				// delete all user progression
				$sql1 = "DELETE FROM `".$TABLEUSERMODULEPROGRESS."`
					WHERE `learnPath_id` = ". (int)$_GET['del_path_id'];
				$query = db_query($sql1);

				// delete all relation between modules and the deleted learning path
				$sql2 = "DELETE FROM `".$TABLELEARNPATHMODULE."`
						WHERE `learnPath_id` = ". (int)$_GET['del_path_id'];
				$query = db_query($sql2);

				// delete the learning path
				$sql3 = "DELETE FROM `".$TABLELEARNPATH."` WHERE `learnPath_id` = ". (int)$_GET['del_path_id'] ;

				$query = db_query($sql3);

				break;
		// ACCESSIBILITY COMMAND
			case "mkBlock" :
			case "mkUnblock" :
				$_REQUEST['cmd'] == "mkBlock" ? $blocking = 'CLOSE' : $blocking = 'OPEN';
				$sql = "UPDATE `".$TABLELEARNPATH."` SET `lock` = '$blocking'
					WHERE `learnPath_id` = ". (int)$_GET['cmdid']."
					AND `lock` != '$blocking'";
				$query = db_query ($sql);
				break;
			// VISIBILITY COMMAND
			case "mkVisibl" :
			case "mkInvisibl" :
				$_REQUEST['cmd'] == "mkVisibl" ? $visibility = 'SHOW' : $visibility = 'HIDE';
				$sql = "UPDATE `".$TABLELEARNPATH."`
					SET `visibility` = '$visibility'
					WHERE `learnPath_id` = ". (int)$_GET['visibility_path_id']."
					AND `visibility` != '$visibility'";
				$query = db_query ($sql);
				break;
			// ORDER COMMAND
			case "moveUp" :
				$thisLearningPathId = (int)$_GET['move_path_id'];
				$sortDirection = "DESC";
				break;
			case "moveDown" :
				$thisLearningPathId = (int)$_GET['move_path_id'];
				$sortDirection = "ASC";
				break;
			// CREATE COMMAND
			case "create" :
				// create form sent
				if( isset($_POST["newPathName"]) && $_POST["newPathName"] != "") {
					// check if name already exists
					$sql = "SELECT `name` FROM `".$TABLELEARNPATH."`
						WHERE `name` = '". mysql_real_escape_string($_POST['newPathName']) ."'";
					$query = db_query($sql);
					$num = mysql_num_rows($query);
					if($num == 0) { // "name" doesn't already exist
						// determine the default order of this Learning path
						$result = db_query("SELECT MAX(`rank`) FROM `".$TABLELEARNPATH."`");
						list($orderMax) = mysql_fetch_row($result);
						$order = $orderMax + 1;
						// create new learning path
						$sql = "INSERT INTO `".$TABLELEARNPATH."` (`name`, `comment`, `rank`)
							VALUES ('". mysql_real_escape_string($_POST['newPathName']) ."','" . mysql_real_escape_string(trim($_POST['newComment']))."',".(int)$order.")";
						$lp_id = db_query($sql);
					} else {
						// display error message
						$dialogBox = $langErrorNameAlreadyExists;
						$style = "caution";
					}
				}
				else { // create form requested
					$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPaths);
					$nameTools = $langCreateNewLearningPath;
					$dialogBox = "
    <form action='$_SERVER[PHP_SELF]' method='POST'>
    <table width='99%' align='left' class='FormData'>
    <tbody>
    <tr>
      <th width='220'>&nbsp;</th>
      <td><b>$langLearningPathData</b></td>
    </tr>
    <tr>
      <th class='left'><label for='newPathName'>$langLearningPathName</label> :</th>
      <td><input type='text' name='newPathName' id='newPathName' size='33' maxlength='255' class='FormData_InputText'></input></td>
    </tr>
    <tr>
      <th class='left'><label for='newComment'>$langComment</label> :</th>
      <td><textarea id='newComment' name='newComment' rows='2' cols='30' class='FormData_InputText'></textarea></td>
    </tr>
    <tr>
      <th class='left'>&nbsp;</th>
      <td><input type='hidden' name='cmd' value='create'><input type='submit' value='$langCreate'></input></td>
    </tr>
    </tbody>
    </table>
    </form>";
					}
				break;
			default:
				break;
		} // end of switch
	} // end of if(isset)
} // end of if

// IF ORDER COMMAND RECEIVED
// CHANGE ORDER
if (isset($sortDirection) && $sortDirection)
{
    $sql = "SELECT `learnPath_id`, `rank`
            FROM `".$TABLELEARNPATH."`
            ORDER BY `rank` $sortDirection";
    $result = db_query($sql);

     // LP = learningPath
     while (list ($LPId, $LPOrder) = mysql_fetch_row($result))
     {
        // STEP 2 : FOUND THE NEXT ANNOUNCEMENT ID AND ORDER.
        //          COMMIT ORDER SWAP ON THE DB

        if (isset($thisLPOrderFound)&&$thisLPOrderFound == true)
        {
            $nextLPId = $LPId;
            $nextLPOrder = $LPOrder;

            // move 1 to a temporary rank
            $sql = "UPDATE `$TABLELEARNPATH`
                    SET `rank` = '-1337'
                    WHERE `learnPath_id` = " . intval($thisLearningPathId);
            db_query($sql);

             // move 2 to the previous rank of 1
             $sql = "UPDATE `$TABLELEARNPATH`
                     SET `rank` = " . intval($thisLPOrder) . "
                     WHERE `learnPath_id` = " . intval($nextLPId);
             db_query($sql);

             // move 1 to previous rank of 2
             $sql = "UPDATE `$TABLELEARNPATH`
                     SET `rank` = " . intval($nextLPOrder) . "
                     WHERE `learnPath_id` = " . intval($thisLearningPathId);
             db_query($sql);
             break;
         }

         // STEP 1 : FIND THE ORDER OF THE ANNOUNCEMENT
         if ($LPId == $thisLearningPathId)
         {
             $thisLPOrder = $LPOrder;
             $thisLPOrderFound = true;
         }
     }
}

// Display links to create and import a learning path
if($is_adminOfCourse) {
	if (isset($dialogBox)) {
		$tool_content .= disp_message_box($dialogBox, $style) ."<br />";
		draw($tool_content, 2, 'learnPath', $head_content);
		exit;
	} else {
		$tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[PHP_SELF]?cmd=create' title='$langCreateNewLearningPath'>$langCreate</a></li>
        <li><a href='importLearningPath.php' title='$langimportLearningPath'>$langImport</a></li>
        <li><a href='detailsAll.php' title='$langTrackAllPathExplanation'>$langProgress</a></li>
        <li><a href='modules_pool.php'>$langLearningObjectsInUse_sort</a></li>
      </ul>
    </div>
    ";
	}
}

// check if there are learning paths available
$l = db_query("SELECT * FROM `$TABLELEARNPATH`");
if ((mysql_num_rows($l) == 0)) {
	$tool_content .= "<p class='alert1'>$langNoLearningPath</p>";
	draw($tool_content, 2, 'learnPath', $head_content);
	exit;
}


$tool_content .= "
    <table width='99%' class='LearnPathSum'>
    <thead>
    <tr class='LP_header'>
      <td width='1%'>&nbsp;</td>
      <td><div align='left'>$langLearningPaths</div></td>\n";


if($is_adminOfCourse) {
     // Titles for teachers
     $tool_content .= "      <td colspan='3' width='20%'><div align='center'>$langAdm</div></td>\n" .
                      "      <td colspan='5' width='20%'><div align='center'>$langActions</div></td>\n";
}
elseif($uid) {
     // display progression only if user is not teacher && not anonymous
     $tool_content .= "      <td colspan='2' width='30%'><div align='center'>$langProgress</div></td>\n";
}
// close title line
$tool_content .= "    </tr>
    </thead>
    <tbody>";

// display invisible learning paths only if user is courseAdmin
if ($is_adminOfCourse) {
    $visibility = "";
}
else {
    $visibility = " AND LP.`visibility` = 'SHOW' ";
}
// check if user is anonymous
if($uid) {
    $uidCheckString = "AND UMP.`user_id` = ". intval($uid);
}
else { // anonymous
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

// list available learning paths
$sql = "SELECT LP.* , MIN(UMP.`raw`) AS minRaw, LP.`lock`
           FROM `$TABLELEARNPATH` AS LP
     LEFT JOIN `$TABLELEARNPATHMODULE` AS LPM
            ON LPM.`learnPath_id` = LP.`learnPath_id`
     LEFT JOIN `$TABLEUSERMODULEPROGRESS` AS UMP
            ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
            $uidCheckString
         WHERE 1=1
             $visibility
      GROUP BY LP.`learnPath_id`
      ORDER BY LP.`rank`";

$result = db_query($sql);

// used to know if the down array (for order) has to be displayed
$LPNumber = mysql_num_rows($result);

$iterator = 1;

$is_blocked = false;
while ($list = mysql_fetch_array($result)) // while ... learning path list
{
    if ($list['visibility'] == 'HIDE') {
        if ($is_adminOfCourse) {
            $style = " class='invisible'";
            $image_bullet = "arrow_red.gif";
        }
        else {
            continue; // skip the display of this file
        }
    }
    else {
        $style="";
        $image_bullet = "arrow_grey.gif";
    }

    $tool_content .= "<tr ".$style.">";

    //Display current learning path name
    if (!$is_blocked) {
        $tool_content .= "
      <td><img src='../../template/classic/img/".$image_bullet."' alt='' /></td>
      <td style='border-right: 1px solid #edecdf;'><a href='learningPath.php?path_id=".$list['learnPath_id']."'".$style.">".htmlspecialchars($list['name'])."</a></td>\n";

        // --------------TEST IF FOLLOWING PATH MUST BE BLOCKED------------------
        // ---------------------(MUST BE OPTIMIZED)------------------------------
        // step 1. find last visible module of the current learning path in DB

        $blocksql = "SELECT `learnPath_module_id`
                     FROM `$TABLELEARNPATHMODULE`
                     WHERE `learnPath_id` = " . intval($list['learnPath_id']) . "
                     AND `visibility` = 'SHOW'
                     ORDER BY `rank` DESC
                     LIMIT 1";
        $resultblock = db_query($blocksql);

        // step 2. see if there is a user progression in db concerning this module of the current learning path
        $number = mysql_num_rows($resultblock);
        if ($number != 0) {
            $listblock = mysql_fetch_array($resultblock);
            $blocksql2 = "SELECT `credit`
                          FROM `$TABLEUSERMODULEPROGRESS`
                          WHERE `learnPath_module_id`= " . intval($listblock['learnPath_module_id']) . "
                          AND `user_id` =" . intval($uid);
            $resultblock2 = db_query($blocksql2);
            $moduleNumber = mysql_num_rows($resultblock2);
        }
        else {
            $moduleNumber = 0;
        }

        //2.1 no progression found in DB
        if (($moduleNumber == 0)  && ($list['lock'] == 'CLOSE')) {
            //must block next path because last module of this path never tried!
            if($uid) {
                if ( !$is_adminOfCourse ) {
                    $is_blocked = true;
                } // never blocked if allowed to edit
            }
            else { // anonymous : don't display the modules that are unreachable
                $iterator++; // trick to avoid having the "no modules" msg to be displayed
                break;
            }
        }

        //2.2. deal with progression found in DB if at leats one module in this path
        if ($moduleNumber!=0) {
            $listblock2 = mysql_fetch_array($resultblock2);
            if (($listblock2['credit']=="NO-CREDIT") && ($list['lock'] == 'CLOSE')) {
                //must block next path because last module of this path not credited yet!
                if($uid) {
                    if (!$is_adminOfCourse) {
                        $is_blocked = true;
                    } // never blocked if allowed to edit
                }
                else { // anonymous : don't display the modules that are unreachable
                    break ;
                }
            }
        }
    }
    else {  //else of !$is_blocked condition , we have already been blocked before, so we continue beeing blocked : we don't display any links to next paths any longer
        $tool_content .= "      <td class='left'><img src='../../template/classic/img/arrow_grey.gif' alt='' /> ".$list['name'].$list['minRaw']."</td>\n";
    }

    // DISPLAY ADMIN LINK-----------------------------------------------------------
    if($is_adminOfCourse) {
        // 5 administration columns

        // LOCK link

        $tool_content .= "      <td style='border-left: 1px solid #edecdf;' align='center'>";

        if ($list['lock'] == 'OPEN') {
            $tool_content .= "<a href='".$_SERVER['PHP_SELF']."?cmd=mkBlock&amp;cmdid=".$list['learnPath_id']."'>"
                  ."<img src='../../template/classic/img/unblock.gif' alt='$langBlock' title='$langBlock' />"
                  ."</a>";
        } else {
            $tool_content .= "<a href='".$_SERVER['PHP_SELF']."?cmd=mkUnblock&amp;cmdid=".$list['learnPath_id']."'>"
            ."<img src='../../template/classic/img/block.gif' alt='$langAltMakeNotBlocking' title='$langAltMakeNotBlocking' />"
            ."</a>";
        }
        $tool_content .= "</td>\n";

        // EXPORT links
        $tool_content .= '      <td align="center"><a href="'.$_SERVER['PHP_SELF'].'?cmd=export&amp;path_id=' . $list['learnPath_id'] . '" >'
            .'<img src="../../template/classic/img/export.gif" alt="'.$langExport2004.'" title="'.$langExport2004.'" /></a>' .""
            .'<a href="' . $_SERVER['PHP_SELF'] . '?cmd=export12&amp;path_id=' . $list['learnPath_id'] . '" >'
            .'<img src="../../template/classic/img/export.gif" alt="'.$langExport12.'" title="'.$langExport12.'" /></a>' .""
            .'</td>' . "\n";

        // statistics links
        $tool_content .= "      <td style='border-right: 1px solid #edecdf;'  align='center'><a href='details.php?path_id=".$list['learnPath_id']."'><img src='../../template/classic/img/statistics.gif' alt='$langTracking' title='$langTracking' /></a></td>\n";


        // VISIBILITY link
        $tool_content .= "      <td align='center'>";
        if ( $list['visibility'] == 'HIDE') {
            $tool_content .= "<a href='".$_SERVER['PHP_SELF']."?cmd=mkVisibl&amp;visibility_path_id=".$list['learnPath_id']."'>"
                  ."<img src='../../template/classic/img/invisible.gif' alt='$langVisible' title='$langVisible' />"
                  ."</a>";
        } else {
            if ($list['lock']=='CLOSE') {
                $onclick = "onClick=\"return confirm('" . clean_str_for_javascript($langAlertBlockingPathMadeInvisible) . "');\"";
            }
            else {
                $onclick = "";
            }

            $tool_content .= "<a href='".$_SERVER['PHP_SELF']."?cmd=mkInvisibl&amp;visibility_path_id=".$list['learnPath_id']."' ".$onclick. " >"
                 ."<img src='../../template/classic/img/visible.gif' alt='$langVisible' title='$langVisible' />"
                 ."</a>";
        }
        $tool_content .= "</td>\n";

        // Modify command / go to other page
        $tool_content .= "      <td align='center'>"
             ."<a href='learningPathAdmin.php?path_id=".$list['learnPath_id']."'>"
             ."<img src='../../template/classic/img/edit.gif' alt='$langModify' title='$langModify' />"
             ."</a>"
             ."</td>\n";

        // DELETE link
        $real = realpath($webDir."courses/".$currentCourseID."/scormPackages/path_".$list['learnPath_id']);

        // check if the learning path is of a Scorm import package and add right popup:
        if (is_dir($real)) {
            $tool_content .=  "      <td align='center'>"
                  ."<a href='".$_SERVER['PHP_SELF']."?cmd=delete&amp;del_path_id=".$list['learnPath_id']."' "
                  ."onClick=\"return scormConfirmation('".clean_str_for_javascript($list['name'])."');\">"
                  ."<img src='../../template/classic/img/delete.gif' alt='$langDelete' title='$langDelete' />"
                  ."</a>"
                  ."</td>\n";

        } else {
            $tool_content .=  "      <td align='center'>"
                  ."<a href='".$_SERVER['PHP_SELF']."?cmd=delete&amp;del_path_id=".$list['learnPath_id']."' "
                  ."onClick=\"return confirmation('".clean_str_for_javascript($list['name'])."');\">"
                  ."<img src='../../template/classic/img/delete.gif' alt='$langDelete' title='$langDelete' />"
                  ."</a>"
                  ."</td>\n";
        }
        // ORDER links

        // DISPLAY MOVE UP COMMAND only if it is not the top learning path
        if ($iterator != 1) {
            $tool_content .= "      <td class='right'>"
                  ."<a href='".$_SERVER['PHP_SELF']."?cmd=moveUp&amp;move_path_id=".$list['learnPath_id']."'>"
                  ."<img src='../../template/classic/img/up.gif' alt='$langUp' title='$langUp' />"
                  ."</a>"
                  ."</td>\n";
        }
        else {
            $tool_content .= "      <td>&nbsp;</td>\n";
        }

        // DISPLAY MOVE DOWN COMMAND only if it is not the bottom learning path
        if($iterator < $LPNumber) {
            $tool_content .= "      <td class='left'>"
                  ."<a href='".$_SERVER['PHP_SELF']."?cmd=moveDown&amp;move_path_id=".$list['learnPath_id']."'>"
                  ."<img src='../../template/classic/img/down.gif' alt='$langDown' title='$langDown' />"
                  ."</a>"
                  ."</td>";
        }
        else {
            $tool_content .= "      <td>&nbsp;</td>";
        }
    }
    elseif($uid) {
        // % progress
        $prog = get_learnPath_progress($list['learnPath_id'], $uid);
        if (!isset($globalprog)) $globalprog = 0;
        if ($prog >= 0) {
            $globalprog += $prog;
        }
        $tool_content .= "<td align='right'>".disp_progress_bar($prog, 1)."</td>\n";
        $tool_content .= "<td align='left'><small> ".$prog."% </small></td>";
    }
    $tool_content .= "
    </tr>";
    $iterator++;
} // end while

if (!$is_adminOfCourse && $iterator != 1 && isset($uid)) {
        // add a blank line between module progression and global progression
        $total = round($globalprog / ($iterator-1));
        $tool_content .= "<tr class='odd'>
                <td colspan='2'><div align='right'><b>$langPathsInCourseProg</b>:</div></td>
                <td><div align='right'>".disp_progress_bar($total, 1)."</div></td>
                <td><div align='left'>$total%</div></td>
                </tr>\n";
}
$tool_content .= "</tbody>\n</table>\n";

draw($tool_content, 2, 'learnPath', $head_content);
