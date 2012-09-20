<?php 
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_help = TRUE;
$helpTopic = 'courseTools';
$require_login = true;
include '../../include/baseTheme.php';
require_once '../../include/lib/fileUploadLib.inc.php';

$nameTools = $langToolManagement;
add_units_navigation(TRUE);

load_js('tools.js');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
			 $langNoPgTitle . '";</script>';

if (isset($_GET['action'])) {
        $action = intval($_GET['action']);
}

if (isset($_REQUEST['toolStatus']) ) {
        if (isset($_POST['toolStatActive'])) {
                $tool_stat_active = $_POST['toolStatActive'];
        }
        if (isset($tool_stat_active)) {
                $loopCount = count($tool_stat_active);
        } else {
                $loopCount = 0;
        }
        $i = 0;
        $publicTools = array();
        $tool_id = null;
        while ($i< $loopCount) {
                if (!isset($tool_id)) {
                        $tool_id = " (`id` = " . intval($tool_stat_active[$i]) .")" ;
                }
                else {
                        $tool_id .= " OR (`id` = " . intval($tool_stat_active[$i]) .")" ;
                }
                $i++;
        }

        //get the state of the agenda tool and store it in a session var. It is used to insert or delete
        //all events of the current lesson from the agenda table in the main db, used by eclass personalised
        //This way, if a lesson's agenda is inactive, any contents it might have are not diplayed in the
        //personalised interface
        $prevAgendaStateSQL = "SELECT `visible` FROM `accueil`WHERE `id` = 1";
        $res = db_query($prevAgendaStateSQL, $dbname);
        $prevAgendaStateRow = mysql_fetch_row($res);

        //reset all tools
        db_query("UPDATE `accueil` SET `visible` = 0", $dbname);

        //and activate the ones the professor wants active, if any
        if ($loopCount >0) {
                db_query("UPDATE accueil SET visible = 1 WHERE $tool_id", $dbname);
        }
        db_query("UPDATE `accueil` SET `visible` = 2 WHERE define_var = 'MODULE_ID_UNITS'", $dbname);

        if (isset($tool_stat_active) && is_array($tool_stat_active)) {
                if (in_array(1, $tool_stat_active)) {
                        //if the agenda module is set to active
                        if ($prevAgendaStateRow[0] != 1) {
                                //and the agenda module was not active before, we need to parse the events to the main agenda table (main database)
                                $sql = 'SELECT id, titre, contenu, day, hour, lasting
                                        FROM  agenda WHERE CONCAT(titre,contenu) != \'\'
                                        AND DATE_FORMAT(day,\'%Y %m %d\') >= \''.date("Y m d").'\'';

                                //  Get all agenda events from each table & parse them to arrays
                                $mysql_query_result = db_query($sql, $currentCourseID);

                                $event_counter=0;
                                while ($myAgenda = mysql_fetch_array($mysql_query_result)) {
                                        $lesson_agenda[$event_counter]['id']            = $myAgenda[0];
                                        $lesson_agenda[$event_counter]['title']         = $myAgenda[1];
                                        $lesson_agenda[$event_counter]['content']       = $myAgenda[2];
                                        $lesson_agenda[$event_counter]['date']          = $myAgenda[3];
                                        $lesson_agenda[$event_counter]['time']          = $myAgenda[4];
                                        $lesson_agenda[$event_counter]['duree']         = $myAgenda[5];
                                        $lesson_agenda[$event_counter]['lesson_code']   = $currentCourseID;
                                        $event_counter++;
                                }

                                for ($j=0; $j <$event_counter; $j++) {
                                        db_query("INSERT INTO agenda (lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
                                                VALUES ('".$lesson_agenda[$j]['id']."',
                                        '".$lesson_agenda[$j]['title']."',
                                        '".$lesson_agenda[$j]['content']."',
                                        '".$lesson_agenda[$j]['date']."',
                                        '".$lesson_agenda[$j]['time']."',
                                        '".$lesson_agenda[$j]['duree']."',
                                        '".$lesson_agenda[$j]['lesson_code']."'
                                )", $mysqlMainDb);
                                }
                        }
                } else {
                        //if the agenda module is set to inactive
                        if ($prevAgendaStateRow[0] != 0) {
                                //and the agenda module was active before, we need to delete this lesson's events
                                //from the main agenda table (main database)

                                $perso_sql= "DELETE FROM $mysqlMainDb.agenda 
                                        WHERE lesson_code= '$currentCourseID'";
                                db_query($perso_sql, $mysqlMainDb);
                        }
                }
        }
}


if (isset($_POST['delete'])) {
        $delete = intval($_POST['delete']);
        $sql = "SELECT lien, define_var FROM accueil WHERE `id` = ". $delete ." ";
        $result = db_query($sql, $dbname);
        while ($res = mysql_fetch_row($result)){
                if($res[1] == "HTML_PAGE") {
                        $link = explode(" ", $res[0]);
                        $path = substr($link[0], 6);
                        $file2Delete = $webDir . $path;
                        @unlink($file2Delete);
                }
        }
        $sql = "DELETE FROM `accueil` WHERE `id` = " . $_POST['delete'] ." ";
        db_query($sql, $dbname);
        unset($sql);
        $tool_content .= "<p class='success'>$langLinkDeleted</p>";
}

if (isset($_POST['submit'])) {
        // Add external link        
        $link = isset($_POST['link'])?$_POST['link']:'';
        $name_link = isset($_POST['name_link'])?$_POST['name_link']:'';
        if ((trim($link) == 'http://') or (trim($link) == 'ftp://')
                        or empty($link) or empty($name_link))  {
                $tool_content .= "<p class='caution'>$langInvalidLink<br /><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=true'>$langHome</a></p><br />";
                draw($tool_content, 2, null, $head_content);
                exit();
        }
        $res = db_query('SELECT MAX(`id`) FROM `accueil`', $dbname);
        list($mID) = mysql_fetch_row($res);
        if ($mID < 101) $mID = 101;
        else $mID = $mID + 1;
        $link = autoquote($link);
        $name_link = autoquote($name_link);
        db_query("INSERT INTO accueil VALUES ($mID, $name_link, $link, 'external_link', 1, 0, $link, '')");
        $tool_content .= "<p class='success'>$langLinkAdded</p>";
        
} elseif (isset($_GET['action'])) { // add external link
        $nameTools = $langAddExtLink;
        $navigation[]= array ('url' => 'course_tools.php?course='.$code_cours, 'name' => $langToolManagement);
        $helpTopic = 'Module';
        $tool_content .=  "
          <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;action=true'>
            <fieldset>
            <legend>$langExplanation_4</legend>
            <table width='100%' class='tbl'>
            <tr>
              <th>$langLink:</th>
              <td><input type='text' name='link' size='50' value='http://'></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <th>$langName:</th>
              <td><input type='Text' name='name_link' size='50'></td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <th>&nbsp;</th>
              <td><input type='submit' name='submit' value='$langAdd'></td>
              <td>&nbsp;</td>
            </tr>
            </table>
            </fieldset>
          </form>";
        draw($tool_content, 2, null, $head_content);
        exit();
}

$toolArr = getSideMenu(2);

if (is_array($toolArr)) {
        $externalLinks = array(); // array used to populate the external tools table afterwards
        for ($i = 0; $i <= 1; $i++){
                $toolSelection[$i] = '';
                $numOfTools = count($toolArr[$i][1]);
                for ($j = 0; $j < $numOfTools; $j++) {
                        if ($toolArr[$i][4][$j] < 100) {
                                $class = '';
                        } else {
                                // External links that are not admin tools
                                $class = ' class="emphasised"';
                                array_push($externalLinks,
                                           array('text' => $toolArr[$i][1][$j],
                                                 'id' => $toolArr[$i][4][$j]));
                        } 
                        $toolSelection[$i] .= "<option$class value='" . $toolArr[$i][4][$j] . "'>" .
                                              $toolArr[$i][1][$j] . "</option>\n";

                }
        }
}

//output tool content
$tool_content .= "
<div id='operations_container'>
  <ul id='opslist'>    
    <li><a href='course_tools.php?course=$code_cours&amp;action=true'>$langAddExtLink</a></li>
  </ul>
</div>";

$tool_content .= <<<tForm
<form name="courseTools" action="$_SERVER[SCRIPT_NAME]?course=$code_cours" method="post" enctype="multipart/form-data">
<table class="tbl_border" width="100%">
<tr>
<th width="45%" class="center">$langInactiveTools</th>
<th width="10%" class="center">$langMove</th>
<th width="45%" class="center">$langActiveTools</th>
</tr>
<tr>
<td class="center">
<select class='invisible_alt' name="toolStatInactive[]" id='inactive_box' size='17' multiple>\n$toolSelection[1]</select>
</td>
<td class="center">
<input type="button" onClick="move('inactive_box','active_box')" value="   >>   " /><br/>
<input type="button" onClick="move('active_box','inactive_box')" value="   <<   " />
</td>
<td class="center">
<select name="toolStatActive[]" id='active_box' size='17' multiple>\n$toolSelection[0]</select>
</td>
</tr>
<tr>
<td>&nbsp;</td>
<td class="center">
<input type=submit value="$langSubmitChanges" name="toolStatus" onClick="selectAll('active_box',true)" />
</td>
<td>&nbsp;</td>
</tr>
</table>
</form>
tForm;

$extToolsCount = count($externalLinks) ;
if ($extToolsCount > 0)  {
        // show table to edit/delete external links
        $tool_content .= "<br/>
                <table class='tbl_alt' width='100%'>
                <tr>
                  <th>&nbsp;</th>
                  <th colspan='2'>$langOperations</th>
                </tr>
                <tr>
                  <th>&nbsp;</th>
                  <th><div align='left'>$langTitle</div></th>
                  <th width='20'>$langDelete</th>
                </tr>";
        for ($i=0; $i < $extToolsCount; $i++) {
                if ($i%2 == 0) {
                        $tool_content .= "<tr class='even'>";
                } elseif ($i%2 == 1) {
                        $tool_content .= "<tr class='odd'>";
                }
                $tool_content .= "<th width='1'>
                        <img src='$themeimg/external_link_on.png' title='$langTitle' /></th>
                        <td class='left'>{$externalLinks[$i]['text']}</td>
                        <td align='center'><form method='post' action='course_tools.php?course=$code_cours'>
                           <input type='hidden' name='delete' value='{$externalLinks[$i]['id']}' />
                           <input type='image' src='$themeimg/delete.png' name='delete_button' 
                                  onClick=\"return confirmation('" .
                                            js_escape("$langDeleteLink {$externalLinks[$i]['text']}?") .
                                       "');\" title='$langDelete' /></form></td>
                     </tr>";
        }
        $tool_content .= "</table>";
}
draw($tool_content, 2, null, $head_content);