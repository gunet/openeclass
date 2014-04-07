<?php
/* ========================================================================
 * Open eClass 2.9
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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
Units display module	
*/
if (!defined('HIDE_TOOL_TITLE')) {
        define('HIDE_TOOL_TITLE', 1);
}
$require_current_course = true;
$require_help = true;
$helpTopic = 'AddCourseUnits';
require_once '../../include/baseTheme.php';
require_once '../../include/lib/fileDisplayLib.inc.php';
require_once '../../include/action.php';
require_once 'functions.php';
require_once '../document/doc_init.php';
require_once '../../include/lib/modalboxhelper.class.php';
require_once '../../include/lib/multimediahelper.class.php';

$action = new action();
$action->record('MODULE_ID_UNITS');

mysql_select_db($mysqlMainDb);

if (isset($_REQUEST['id'])) {
	$id = intval($_REQUEST['id']);
}
$lang_editor = langname_to_code($language);
load_js('tools.js');
ModalBoxHelper::loadModalBox(true);

if (isset($_REQUEST['edit_submit'])) {
        units_set_maxorder();
        $tool_content .= handle_unit_info_edit();
}

$form = process_actions();

// check if we are trying to access a protected resource directly
$access = db_query_get_single_value("SELECT public FROM course_units WHERE id = $id");
if (!resource_access(1, $access)) {
    $tool_content .= "<p class='caution'>$langForbidden</p>";
    draw($tool_content, 2, null, $head_content);
    exit;    
}

if ($is_editor) {
	$tool_content .= "&nbsp;<div id='operations_container'>
		<form name='resinsert' action='{$urlServer}modules/units/insert.php' method='get'><input type='hidden' name='course' value='$code_cours'/>
		<select name='type' onChange='document.resinsert.submit();'>
			<option>-- $langAdd --</option>
			<option value='doc'>$langInsertDoc</option>
			<option value='exercise'>$langInsertExercise</option>
			<option value='text'>$langInsertText</option>
			<option value='link'>$langInsertLink</option>
			<option value='lp'>$langLearningPath1</option>
			<option value='video'>$langInsertVideo</option>
			<option value='forum'>$langInsertForum</option>
			<option value='ebook'>$langInsertEBook</option>
			<option value='work'>$langInsertWork</option>
			<option value='wiki'>$langInsertWiki</option>
		</select>
		<input type='hidden' name='id' value='$id'>
		<input type='hidden' name='course' value='$code_cours'>
		</form>
		</div>".
		$form; 
}

if ($is_editor) {
        $visibility_check = '';
} else {
        $visibility_check = "AND visibility='v'";
}
if (isset($id) and $id !== false) {
        $q = db_query("SELECT * FROM course_units
		                WHERE id = $id AND course_id=$cours_id " . $visibility_check);
} else {
	$q = false;
}
if (!$q or mysql_num_rows($q) == 0) {
        $nameTools = $langUnitUnknown;
	$tool_content .= "<p class='caution'>$langUnknownResType</p>";
        draw($tool_content, 2, null, $head_content);
        exit;
}
$info = mysql_fetch_array($q);
$nameTools = htmlspecialchars($info['title']);
$comments = trim($info['comments']);

// Links for next/previous unit
foreach (array('previous', 'next') as $i) {
        if ($i == 'previous') {
                $op = '<=';
                $dir = 'DESC';
                $arrow1 = '« ';
                $arrow2 = '';
        } else {
                $op = '>=';
                $dir = '';
                $arrow1 = '';
                $arrow2 = ' »';
        }
        
        if (isset($_SESSION['uid']) and (isset($_SESSION['status'][$currentCourse]) and $_SESSION['status'][$currentCourse])) {
            $access_check = "";
        } else {
            $access_check = "AND public = 1";
        }
        
        $q = db_query("SELECT id, title, public FROM course_units
                       WHERE course_id = $cours_id
                             AND id <> $id
                             AND `order` $op $info[order]
                             AND `order` >= 0
                             $visibility_check
                             $access_check
                       ORDER BY `order` $dir
                       LIMIT 1");
        if ($q and mysql_num_rows($q) > 0) {
                list($q_id, $q_title) = mysql_fetch_row($q);
                $q_title = htmlspecialchars($q_title);
                $link[$i] = "$arrow1<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=$q_id'>$q_title</a>$arrow2";
        } else {
                $link[$i] = '&nbsp;';
        }
}

if ($is_editor) {
        $comment_edit_link = "<td valign='top' width='20'><a href='info.php?course=$code_cours&amp;edit=$id&amp;next=1'><img src='$themeimg/edit.png' title='' alt='' /></a></td>";
        $units_class = 'tbl';
} else {
        $units_class = 'tbl';
        $comment_edit_link = '';
}

$tool_content .= "<table class='$units_class' width='99%'>";
if ($link['previous'] != '&nbsp;' or $link['next'] != '&nbsp;') {
$tool_content .= "
    <tr class='odd'>
      <td class='left'>" .  $link['previous'] . '</td>
      <td class="right">' .  $link['next'] . "</td>
    </tr>";
}
$tool_content .= "
    <tr>
      <td colspan='2' class='unit_title'>$nameTools</td>
    </tr>
    </table>\n";


if (!empty($comments)) {
        $tool_content .= "
    <table class='tbl' width='99%'>
    <tr class='even'>
      <td>$comments</td>
      $comment_edit_link
    </tr>
    </table>";
}

show_resources($id);

$tool_content .= '
  <form name="unitselect" action="' .  $urlServer . 'modules/units/" method="get"><input type="hidden" name="course" value="'.$code_cours.'"/>';
$tool_content .="
    <table width='99%' class='tbl'>
     <tr class='odd'>
       <td class='right'>".$langCourseUnits.":&nbsp;</td>
       <td width='50' class='right'>".
                 "<select name='id' onChange='document.unitselect.submit();'>";
$q = db_query("SELECT id, title FROM course_units
               WHERE course_id = $cours_id AND `order` > 0
                     $visibility_check
               ORDER BY `order`", $mysqlMainDb);
while ($info = mysql_fetch_array($q)) {
        $selected = ($info['id'] == $id)? ' selected ': '';
        $tool_content .= "<option value='$info[id]'$selected>" .
                         htmlspecialchars(ellipsize($info['title'], 40)) .
                         '</option>';
}
$tool_content .= "</select>
       </td>
     </tr>
    </table>
 </form>";

draw($tool_content, 2, null, $head_content);

