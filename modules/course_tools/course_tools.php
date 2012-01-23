<?php
/* ========================================================================
 * Open eClass 2.4
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



/*
 * Course Tools Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component offers several operations regarding a course's tools.
 * The course administrator can:
 * 1. Activate/De-activate tools
 * 2. Upload external html page
 * 3. Add external links
 * 4. Delete the last two
 *
 */

$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_help = TRUE;
$helpTopic = 'courseTools';
$require_login = true;
include '../../include/baseTheme.php';

$nameTools = $langToolManagement;
add_units_navigation(TRUE);

$head_content .= "<script type='text/javascript' src='$urlAppend/js/tools.js'></script>\n";


if (isset($_GET['action'])) {
        $action = intval($_GET['action']);
} else {
        $action = 0;
}
if (isset($_REQUEST['toolStatus']) ) {
        if(isset($_POST['toolStatActive'])) $tool_stat_active = $_POST['toolStatActive'];

        if (isset($tool_stat_active)) {
                $loopCount = count($tool_stat_active);
        } else  {
                $loopCount = 0;
        }
        $i =0;
        $publicTools = array();
        $tool_id = null;
        while ($i< $loopCount) {
                if (!isset($tool_id)) {
                        $tool_id = " (`id` = " . $tool_stat_active[$i] .")" ;
                }
                else {
                        $tool_id .= " OR (`id` = " . $tool_stat_active[$i] .")" ;
                }
                $i++;
        }

        //reset all tools
        db_query("UPDATE `accueil` SET `visible` = 0", $dbname);

        //and activate the ones the professor wants active, if any
        if ($loopCount >0) {
                db_query("UPDATE accueil SET visible = 1 WHERE $tool_id", $dbname);
        }
        db_query("UPDATE `accueil` SET `visible` = 2 WHERE define_var = 'MODULE_ID_UNITS'", $dbname);
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
        $tool_content .= "<p class=\"success\">$langLinkDeleted</p>";
}

if (isset($_POST['submit'])) {
        // Add external link
        if ($action == 2) {
                $link = isset($_POST['link'])?$_POST['link']:'';
                $name_link = isset($_POST['name_link'])?$_POST['name_link']:'';
                if ((trim($link) == 'http://') or (trim($link) == 'ftp://')
                                or empty($link) or empty($name_link))  {
                        $tool_content .= "<p class='caution'>$langInvalidLink<br /><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;action=2'>$langHome</a></p><br />";
                        draw($tool_content, 2);
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
        } elseif ($action == 1) { 
                $updir = "$webDir/courses/$currentCourseID/page"; //path to upload directory
                $size = "20971520"; //file size is 20M (1024x1024x20)
                if (isset($_FILES['file']['name']) && is_uploaded_file($_FILES['file']['tmp_name'])
                    && ($_FILES['file']['size'] < "$size") and (!empty($_POST['link_name']))) {

                        $tmpfile = $_FILES['file']['tmp_name'];
                        $file_name = $_FILES['file']['name'];
                        @copy("$tmpfile", "$updir/$file_name")
                                or die("<p>$langCouldNot</p></tr>");

                        $sql = 'SELECT MAX(`id`) FROM `accueil` ';
                        $res = db_query($sql,$dbname);
                        while ($maxID = mysql_fetch_row($res)) {
                                $mID = $maxID[0];
                        }

                        if($mID < 101) $mID = 101;
                        else $mID = $mID+1;

                        $link_name = quote($_POST['link_name']);
                        $lien = quote("../../courses/$currentCourse/page/$file_name");
                        db_query("INSERT INTO accueil VALUES (
                                        $mID,
                                        $link_name,
                                        $lien,
                                        'external_link',
                                        '1',
                                        '0',
                                        '',
                                        'HTML_PAGE'
                                        )", $currentCourse);
                        $tool_content .= "  <p class='success'>$langOkSent</p>\n";
                } else {
                        $tool_content .= "  <p class='caution'>$langTooBig<br />\n";
                        $tool_content .= "  <a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;action=1'>$langHome</a></p>\n  <br />\n";
                        draw($tool_content, 2);
                }
        }
} elseif ($action == 1) { // upload html file
        $nameTools = $langUploadPage;
        $navigation[]= array ("url"=>"course_tools.php?course=$code_cours", "name"=> $langToolManagement);
        $helpTopic = 'Import';

        $tool_content .= "\n 
            <form method='post' action='$_SERVER[PHP_SELF]?course=$code_cours&amp;submit=yes&action=1' enctype='multipart/form-data'>
              <div class='info'><p>$langExplanation_0</p>
              <p>$langExplanation_3</p></div>

              <fieldset>
              <legend>$langExplanation_1</legend> 
                                <table class='tbl'>
              <tr>
                <th width='170'>$langSendPage</th>
                <td><input type='file' name='file' size='35' accept='text/html'></td>
                <td class='right'>&nbsp;</td>
              </tr>
              <tr>
                <th>$langPgTitle</th>
                <td><input type='Text' name='link_name' size='40'></td>
                <td class='right smaller'>$langExplanation_2</td>
              </tr>
              <tr>
                <th>&nbsp;</th>
                <td colspan='2' class='right'><input type='Submit' name='submit' value='$langAdd'></td>
              </tr>
              </table>
              </fieldset>

            </form>
                                <div class='right smaller'>$langNoticeExpl</div>'";
        draw($tool_content, 2);
        exit();
} elseif ($action == 2) { // add external link
        $nameTools = $langAddExtLink;
        $navigation[]= array ('url' => 'course_tools.php?course='.$code_cours, 'name' => $langToolManagement);
        $helpTopic = 'Module';
        $tool_content .=  "
          <form method='post' action='$_SERVER[PHP_SELF]?course=$code_cours&amp;action=2'>
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
        draw($tool_content, 2);
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
    <li><a href='course_tools.php?course=$code_cours&amp;action=1'>$langUploadPage</a></li>
    <li><a href='course_tools.php?course=$code_cours&amp;action=2'>$langAddExtLink</a></li>
  </ul>
</div>";

$tool_content .= <<<tForm
<form name="courseTools" action="$_SERVER[PHP_SELF]?course=$code_cours" method="post" enctype="multipart/form-data">
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
        $tool_content .= "
        <br/>
                <table class='tbl_alt' width='100%'>
                <tr>
                  <th>&nbsp;</th>
                  <th colspan='2'>$langOperations</th>
                </tr>
                <tr>
                  <th>&nbsp;</th>
                  <th><div align='left'>$langTitle</div></th>
                  <th width='20'>$langDelete</th>
                </tr>\n";
        for ($i=0; $i < $extToolsCount; $i++) {
                if ($i % 2==0) {
                        $tool_content .= "                        <tr class='even'>\n";
                } elseif ($i % 2 == 1) {
                        $tool_content .= "                        <tr class='odd'>\n";
                }
                $tool_content .= "                          <th width='1'>
                        <img src='$themeimg/external_link_on.png' title='$langTitle' /></th>
                        <td class='left'>{$externalLinks[$i]['text']}</td>
                        <td align='center'><form method='post' action='course_tools.php?course=$code_cours'>
                           <input type='hidden' name='delete' value='{$externalLinks[$i]['id']}' />
                           <input type='image' src='$themeimg/delete.png' name='delete_button' 
                                  onClick=\"return confirmation('" .
                                            js_escape("$langDeleteLink {$externalLinks[$i]['text']}?") .
                                       "');\" title='$langDelete' /></form></td>
                     </tr>\n";
        }
        $tool_content .= "                        </table>\n";
}
draw($tool_content, 2, null, $head_content);

