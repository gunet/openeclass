<?php

/*
Header, Copyright, etc ...
*/

/*  TODO
Same page:
- Understand Blocking and hrefs
*/

include("../../include/lib/learnPathLib.inc.php");
include("claro_main.lib.php");

$require_current_course = TRUE;
$langFiles              = "learnPath";
$require_help           = TRUE;
$helpTopic              = "LearnPath";

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

define('CLARO_FILE_PERMISSIONS', 0777);

include("../../include/init.php");

$nameTools = $langLearningPathList;
$is_AllowedToEdit = $is_adminOfCourse;
$lpUid = $uid;

if ( $cmd == 'export' )
{
      mysql_select_db($currentCourseID);
      include("include/scormExport.inc.php");
      $scorm = new ScormExport($_REQUEST['path_id']);
      if ( !$scorm->export() )
      {
          $dialogBox = '<b>Error exporting SCORM package</b><br />'."\n".'<ul>'."\n";
          foreach( $scorm->getError() as $error)
          {
              $dialogBox .= '<li>' . $error . '</li>'."\n";
          }
          $dialogBox .= '<ul>'."\n";
      }
} // endif $cmd == export

begin_page();

echo "</td></tr></table>";
mysql_select_db($currentCourseID);


echo "<script>
          function confirmation (name)
          {
              if (confirm('". clean_str_for_javascript($langAreYouSureToDelete) . " ' + name + '? " . $langModuleStillInPool . "'))
                  {return true;}
              else
                  {return false;}
          }
          </script>";
echo "<script>
          function scormConfirmation (name)
          {
              if (confirm('". clean_str_for_javascript($langAreYouSureToDeleteScorm) .  "' + name + '?'))
                  {return true;}
              else
                  {return false;}
          }
          </script>";

// execution of commands
switch ( $cmd ) {
    // DELETE COMMAND
    case "delete" :
            if (is_dir($webDir.$currentCourseID."/scormPackages/path_".$_GET['del_path_id']))
            {
                $findsql = "SELECT M.`module_id`
                            FROM  `".$TABLELEARNPATHMODULE."` AS LPM,
                                      `".$TABLEMODULE."` AS M
                            WHERE LPM.`learnPath_id` = ". (int)$_GET['del_path_id']."
                              AND 
                                    ( M.`contentType` = '".CTSCORM_."'
                                      OR
                                      M.`contentType` = '".CTLABEL_."'
                                    )
                              AND LPM.`module_id` = M.`module_id`
                                ";
                $findResult = mysql_query($findsql);

                // Delete the startAssets

                $delAssetSql = "DELETE
                                FROM `".$TABLEASSET."`
                                WHERE 1=0
                               ";

                while ($delList = mysql_fetch_array($findResult))
                {
                    $delAssetSql .= " OR `module_id`=". (int)$delList['module_id'];
                }

                mysql_query($delAssetSql);

                // DELETE the SCORM modules

                $delModuleSql = "DELETE
                                 FROM `".$TABLEMODULE."`
                                 WHERE (`contentType` = '".CTSCORM_."' OR `contentType` = '".CTLABEL_."')
                                 AND (1=0
                                 ";

                if (mysql_num_rows($findResult)>0)
                {
                    mysql_data_seek($findResult,0);
                }

                while ($delList = mysql_fetch_array($findResult))
                {
                    $delModuleSql .= " OR `module_id`=". (int)$delList['module_id'];
                }
                $delModuleSql .= ")";

                mysql_query($delModuleSql);

                // DELETE the directory containing the package and all its content
                $real = realpath($webDir.$currentCourseID."/scormPackages/path_".$_GET['del_path_id']);
                claro_delete_file($real);

            }   // end of dealing with the case of a scorm learning path.
            else
            {
                $findsql = "SELECT M.`module_id`
                                 FROM  `".$TABLELEARNPATHMODULE."` AS LPM,
                                      `".$TABLEMODULE."` AS M
                                 WHERE LPM.`learnPath_id` = ". (int)$_GET['del_path_id']."
                                 AND M.`contentType` = '".CTLABEL_."'
                                 AND LPM.`module_id` = M.`module_id`
                                 ";
                //echo $findsql;
                $findResult = mysql_query($findsql);
                // delete labels of non scorm learning path
                $delLabelModuleSql = "DELETE
                                     FROM `".$TABLEMODULE."`
                                     WHERE 1=0
                                  ";
                  
                while ($delList = mysql_fetch_array($findResult))
                {
                    $delLabelModuleSql .= " OR `module_id`=". (int)$delList['module_id'];
                }
                $query = mysql_query($delLabelModuleSql);
            }
            
            // delete everything for this path (common to normal and scorm paths) concerning modules, progression and path

            // delete all user progression
            $sql1 = "DELETE
                       FROM `".$TABLEUSERMODULEPROGRESS."`
                       WHERE `learnPath_id` = ". (int)$_GET['del_path_id'];
            $query = mysql_query($sql1);

            // delete all relation between modules and the deleted learning path
            $sql2 = "DELETE
                       FROM `".$TABLELEARNPATHMODULE."`
                       WHERE `learnPath_id` = ". (int)$_GET['del_path_id'];
            $query = mysql_query($sql2);

            // delete the learning path
            $sql3 = "DELETE
                          FROM `".$TABLELEARNPATH."`
                          WHERE `learnPath_id` = ". (int)$_GET['del_path_id'] ;

            $query = mysql_query($sql3);

            break;
      
      // ACCESSIBILITY COMMAND
      case "mkBlock" :
      case "mkUnblock" :
            $cmd == "mkBlock" ? $blocking = 'CLOSE' : $blocking = 'OPEN';
            $sql = "UPDATE `".$TABLELEARNPATH."`
                    SET `lock` = '$blocking'
                    WHERE `learnPath_id` = ". (int)$_GET['cmdid']."
                      AND `lock` != '$blocking'";
            $query = mysql_query ($sql);
            break;
                        
      // VISIBILITY COMMAND
      case "mkVisibl" :
      case "mkInvisibl" :      
            $cmd == "mkVisibl" ? $visibility = 'SHOW' : $visibility = 'HIDE';
            $sql = "UPDATE `".$TABLELEARNPATH."`
                       SET `visibility` = '$visibility'
                     WHERE `learnPath_id` = ". (int)$_GET['visibility_path_id']."
                       AND `visibility` != '$visibility'";
            $query = mysql_query ($sql);
            break;

      // ORDER COMMAND
      case "moveUp" :
            $thisLearningPathId = $_GET['move_path_id'];
            $sortDirection = "DESC";
            break;
      case "moveDown" :
            $thisLearningPathId = $_GET['move_path_id'];
            $sortDirection = "ASC";
            break;
            
      // CREATE COMMAND
      case "create" :
            // create form sent
            if( isset($_POST["newPathName"]) && $_POST["newPathName"] != "") {
                // check if name already exists
                $sql = "SELECT `name`
                         FROM `".$TABLELEARNPATH."`
                        WHERE `name` = '". addslashes($_POST['newPathName']) ."'";
                $query = mysql_query($sql);
                $num = mysql_numrows($query);
                if($num == 0 ) { // "name" doesn't already exist
                    // determine the default order of this Learning path
                    $result = mysql_query("SELECT MAX(`rank`)
                                               FROM `".$TABLELEARNPATH."`");

                    list($orderMax) = mysql_fetch_row($result);
                    $order = $orderMax + 1;
                    
                    // create new learning path
                    $sql = "INSERT
                              INTO `".$TABLELEARNPATH."`
                                     (`name`, `comment`, `rank`)
                              VALUES ('". addslashes($_POST['newPathName']) ."','" . addslashes(trim($_POST['newComment']))."',".(int)$order.")";
                    $lp_id = mysql_query($sql);
                }
                else {
                    // display error message
                    echo $langErrorNameAlreadyExists;
                }
            }
            else { // create form requested
                        echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"POST\">\n"
                              ."<h4>".$langCreateNewLearningPath."</h4>\n"
                              ."<label for=\"newPathName\">".$langLearningPathName."</label><br />\n"
                              ."<input type=\"text\" name=\"newPathName\" id=\"newPathName\" maxlength=\"255\"></input><br /><br />\n"
                              ."<label for=\"newComment\">".$langComment."</label><br />\n"
                              ."<textarea id=\"newComment\" name=\"newComment\" rows=\"2\" cols=\"50\"></textarea><br />\n"
                              ."<input type=\"hidden\" name=\"cmd\" value=\"create\">\n"
                              ."<input type=\"submit\" value=\"".$langOk."\"></input>\n"
                              ."</form>\n\n";
            }
            break;
}

// IF ORDER COMMAND RECEIVED
// CHANGE ORDER
if (isset($sortDirection) && $sortDirection)
{
    $sql = "SELECT `learnPath_id`, `rank`
            FROM `".$TABLELEARNPATH."`
            ORDER BY `rank` $sortDirection";
    $result = mysql_query($sql);

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
            $sql = "UPDATE `".$TABLELEARNPATH."`
                    SET `rank` = \"-1337\"
                    WHERE `learnPath_id` =  \"" . (int)$thisLearningPathId . "\"";
            mysql_query($sql);

             // move 2 to the previous rank of 1
             $sql = "UPDATE `".$TABLELEARNPATH."`
                     SET `rank` = \"" . (int)$thisLPOrder . "\"
                     WHERE `learnPath_id` =  \"" . (int)$nextLPId . "\"";
             mysql_query($sql);

             // move 1 to previous rank of 2
             $sql = "UPDATE `".$TABLELEARNPATH."`
                             SET `rank` = \"" . (int)$nextLPOrder . "\"
                           WHERE `learnPath_id` =  \"" . (int)$thisLearningPathId . "\"";
             mysql_query($sql);

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

if (isset($dialogBox)) {
echo $dialogBox;
}

// Display links to create and import a learning path
if($is_adminOfCourse) {
?>
      <p>
      <a href="<?php echo $_SERVER['PHP_SELF'] ?>?cmd=create"><?php echo $langCreateNewLearningPath; ?></a> |
      <a href="importLearningPath.php"><?php echo $langimportLearningPath; ?></a> |
      <a href="modules_pool.php"><?php echo $langModulesPoolToolName; ?></a> |
      <a href="learnPath_detailsAllPath.php"><?php echo $langTrackAllPath; ?></a>
      </p>
<?php
}

echo "<table class=\"claroTable emphaseLine\" width=\"100%\" border=\"0\" cellspacing=\"2\">
 <thead>
 <tr class=\"headerX\" align=\"center\" valign=\"top\" bgcolor=\"#E6E6E6\">
  <th>".$langLearningPath."</th>";

if($is_adminOfCourse) {
     // Titles for teachers
     echo "<th>".$langModify."</th>"
            ."<th>".$langDelete."</th>"
            ."<th>".$langBlock."</th>"
            ."<th>".$langVisibility."</th>"
            ."<th colspan=\"2\">".$langOrder."</th>"
            ."<th>".$langExport."</th>"
            ."<th>".$langTracking."</th>";
}
elseif($lpUid) {
   // display progression only if user is not teacher && not anonymous
   echo "<th colspan=\"2\">".$langProgress."</th>";
}
// close title line
echo "</tr>\n</thead>\n<tbody>";

// display invisible learning paths only if user is courseAdmin
if ($is_adminOfCourse) {
    $visibility = "";
}
else {
    $visibility = " AND LP.`visibility` = 'SHOW' ";
}
// check if user is anonymous
if($lpUid) {
    $uidCheckString = "AND UMP.`user_id` = ". (int)$lpUid;
}
else { // anonymous
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

// list available learning paths
$sql = "SELECT LP.* , MIN(UMP.`raw`) AS minRaw, LP.`lock`
           FROM `".$TABLELEARNPATH."` AS LP
     LEFT JOIN `".$TABLELEARNPATHMODULE."` AS LPM
            ON LPM.`learnPath_id` = LP.`learnPath_id`
     LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
            ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
            ".$uidCheckString."
         WHERE 1=1
             ".$visibility."
      GROUP BY LP.`learnPath_id`
      ORDER BY LP.`rank`";

$result = mysql_query($sql);

// used to know if the down array (for order) has to be displayed
$LPNumber = mysql_num_rows($result);
$iterator = 1;

$is_blocked = false;
while ( $list = mysql_fetch_array($result) ) // while ... learning path list
{
    if ( $list['visibility'] == 'HIDE' ) {
        if ($is_adminOfCourse) {
            $style=" class=\"invisible\"";
        }
        else {
            continue; // skip the display of this file
        }
    }
    else {
        $style="";
    }

    echo "<tr align=\"center\"".$style.">";

    //Display current learning path name

    if ( !$is_blocked ) {
        echo "<td align=\"left\"><a href=\"learningPath.php?path_id="
            .$list['learnPath_id']."\"".$style."><img src=\"../../images/learnpath.gif\" alt=\"\"
            border=\"0\" />  ".htmlspecialchars($list['name'])."</a></td>";

        // --------------TEST IF FOLLOWING PATH MUST BE BLOCKED------------------
        // ---------------------(MUST BE OPTIMIZED)------------------------------

        // step 1. find last visible module of the current learning path in DB

        $blocksql = "SELECT `learnPath_module_id`
                     FROM `".$TABLELEARNPATHMODULE."`
                     WHERE `learnPath_id`=". (int)$list['learnPath_id']."
                     AND `visibility` = \"SHOW\"
                     ORDER BY `rank` DESC
                     LIMIT 1
                    ";

        //echo $blocksql;

        $resultblock = mysql_query($blocksql);

        // step 2. see if there is a user progression in db concerning this module of the current learning path

        $number = mysql_num_rows($resultblock);
        if ($number != 0) {
            $listblock = mysql_fetch_array($resultblock);
            $blocksql2 = "SELECT `credit`
                          FROM `".$TABLEUSERMODULEPROGRESS."`
                          WHERE `learnPath_module_id`=". (int)$listblock['learnPath_module_id']."
                          AND `user_id`='". (int)$lpUid."'
                         ";

            $resultblock2 = mysql_query($blocksql2);
            $moduleNumber = mysql_num_rows($resultblock2);
        }
        else {
            //echo "no module in this path!";
            $moduleNumber = 0;
        }
        
        //2.1 no progression found in DB

        if (($moduleNumber == 0)  && ($list['lock'] == 'CLOSE')) {
            //must block next path because last module of this path never tried!

            if($lpUid) {
                if ( !$is_AllowedToEdit ) {
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
                if($lpUid) {
                    if ( !$is_AllowedToEdit ) {
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
        echo "<td align=\"left\"> <img src=\"../../images/learnpath.gif\" alt=\"\"
                    border=\"0\" /> ".$list['name'].$list['minRaw']."</td>\n";
    }

    // DISPLAY ADMIN LINK-----------------------------------------------------------

    if($is_adminOfCourse) {
        // 5 administration columns

        // Modify command / go to other page
        echo "<td>\n",
             "<a href=\"learningPathAdmin.php?path_id=".$list['learnPath_id']."\">\n",
             "<img src=\"../../images/edit.gif\" border=\"0\" alt=\"$langModify\" />\n",
             "</a>\n",
             "</td>\n";

        // DELETE link
        $real = realpath($webDir.$currentCourseID."/scormPackages/path_".$list['learnPath_id']);

        // check if the learning path is of a Scorm import package and add right popup:

        if (is_dir($real)) {
            echo  "<td>\n",
                  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=delete&del_path_id=".$list['learnPath_id']."\" ",
                  "onClick=\"return scormConfirmation('",clean_str_for_javascript($list['name']),"');\">\n",
                  "<img src=\"../../images/delete.gif\" border=\"0\" alt=\"$langDelete\" />\n",
                  "</a>\n",
                  "</td>\n";

        }
        else {
            echo  "<td>\n",
                  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=delete&del_path_id=".$list['learnPath_id']."\" ",
                  "onClick=\"return confirmation('",clean_str_for_javascript($list['name']),"');\">\n",
                  "<img src=\"../../images/delete.gif\" border=\"0\" alt=\"$langDelete\" />\n",
                  "</a>\n",
                  "</td>\n";
        }

        // LOCK link

        echo "<td>";

        if ( $list['lock'] == 'OPEN') {
            echo  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=mkBlock&cmdid=".$list['learnPath_id']."\">\n",
                  "<img src=\"../../images/unblock.gif\" alt=\"$langBlock\" border=\"0\">\n",
                  "</a>\n";
        }
        else {
            echo  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=mkUnblock&cmdid=".$list['learnPath_id']."\">\n",
                  "<img src=\"../../images/block.gif\" alt=\"$langAltMakeNotBlocking\" border=\"0\">\n",
                  "</a>\n";
        }
        echo  "</td>\n";

        // VISIBILITY link

        echo  "<td>\n";

        if ( $list['visibility'] == 'HIDE') {
            echo  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=mkVisibl&visibility_path_id=".$list['learnPath_id']."\">\n",
                  "<img src=\"../../images/invisible.gif\" alt=\"$langAltMakeVisible\" border=\"0\" />\n",
                  "</a>";
        }
        else {
            if ($list['lock']=='CLOSE') {
                $onclick = "onClick=\"return confirm('" . clean_str_for_javascript($langAlertBlockingPathMadeInvisible) . "');\"";
            }
            else {
                $onclick = "";
            }

            echo "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=mkInvisibl&visibility_path_id=".$list['learnPath_id']."\" ",$onclick, " >\n",
                 "<img src=\"../../images/visible.gif\" alt=\"$langMakeInvisible\" border=\"0\" />\n",
                 "</a>\n";
        }
        echo  "</td>\n";

        // ORDER links

        // DISPLAY MOVE UP COMMAND only if it is not the top learning path
        if ($iterator != 1) {
            echo  "<td>\n",
                  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=moveUp&move_path_id=".$list['learnPath_id']."\">\n",
                  "<img src=\"../../images/up.gif\" alt=\"$langAltMoveUp\" border=\"0\" />\n",
                  "</a>\n",
                  "</td>\n";
        }
        else {
            echo "<td>&nbsp;</td>\n";
        }

        // DISPLAY MOVE DOWN COMMAND only if it is not the bottom learning path
        if($iterator < $LPNumber) {
            echo  "<td>\n",
                  "<a href=\"",$_SERVER['PHP_SELF'],"?cmd=moveDown&move_path_id=".$list['learnPath_id']."\">\n",
                  "<img src=\"../../images/down.gif\" alt=\"$langMoveDown\" border=\"0\" />\n",
                  "</a>\n",
                  "</td>\n";
        }
        else {
            echo "<td>&nbsp;</td>\n";
        }
        
        // EXPORT links
        echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?cmd=export&amp;path_id=' . $list['learnPath_id'] . '" >'
            .'<img src="../../images/export.gif" alt="' . $langExport . '" border="0"></a></td>' . "\n";
        
        // statistics links
        echo "<td>\n
          <a href=\"".$clarolineRepositoryWeb."tracking/learnPath_details.php?path_id=".$list['learnPath_id']."\">
          <img src=\"../../images/statistics.gif\" border=\"0\" alt=\"$langTracking\" />
          </a>
          </td>\n";
    }
    elseif($lpUid) {
        // % progress
        $prog = get_learnPath_progress($list['learnPath_id'], $lpUid);
        if (!isset($globalprog)) $globalprog = 0;
        if ($prog >= 0) {
            $globalprog += $prog;
        }
        echo "<td align=\"right\">".claro_disp_progress_bar($prog, 1)."</td>";
        echo "<td align=\"left\">
              <small> ".$prog."% </small>
              </td>";
    }
    echo "</tr>";
    $iterator++;

} // end while

echo "</tbody>\n<tfoot>";

if( $iterator == 1 ) {
      echo "<tr><td align=\"center\" colspan=\"8\">".$langNoLearningPath."</td></tr>";
}
elseif (!$is_adminOfCourse && $iterator != 1 && $lpUid) {
    // add a blank line between module progression and global progression
    echo "<tr><td colspan=\"3\">&nbsp;</td></tr>";
    $total = round($globalprog/($iterator-1));
    echo "<tr>
          <td align =\"right\">
          ".$langPathsInCourseProg." :
          </td>
          <td align=\"right\" >".
          claro_disp_progress_bar($total, 1).
          "</td>
          <td align=\"left\">
          <small> ".$total."% </small>
          </td>
          </tr>
          ";
}
echo "</tfoot>\n";
echo "</table>\n";

?>

</body>
</html>
