<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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


/**
 * @file index.php
 * @brief Units display module
 */

define('HIDE_TOOL_TITLE', 1);
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'AddCourseUnitscontent';

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/action.php';
require_once 'functions.php';
require_once 'modules/document/doc_init.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_REQUEST['id'])) {
    $id = intval($_REQUEST['id']);
}

$lang_editor = $language;
load_js('tools.js');
ModalBoxHelper::loadModalBox(true);

if (isset($_REQUEST['edit_submit'])) {
    units_set_maxorder();
    $tool_content .= handle_unit_info_edit();
}

$form = process_actions();

// check if we are trying to access a protected resource directly
/*
$access = Database::get()->querySingle("SELECT public FROM course_units WHERE id = ?d", $id);
if ($access) {
    if (!resource_access(1, $access->public)) {
        $tool_content .= "<p class='caution'>$langForbidden</p>";
        draw($tool_content, 2, null, $head_content);
        exit;    
    }
}
*/

if ($is_editor) {
    $tool_content .= "&nbsp;<div id='operations_container'>
		<form name='resinsert' action='{$urlServer}modules/weeks/insert.php' method='get'>
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
                        <option value='poll'>$langInsertPoll</option>
			<option value='wiki'>$langInsertWiki</option>                            
		</select>
		<input type='hidden' name='id' value='$id'>
		<input type='hidden' name='course' value='$course_code'>
		</form>
		</div>" .
            $form;
}

if ($is_editor) {
    $visibility_check = '';
} else {
    $visibility_check = "AND visible=1";
}
if (isset($id) and $id !== false) {
    //$info = Database::get()->querySingle("SELECT * FROM course_units WHERE id = ?d AND course_id = ?d $visibility_check", $id, $course_id);
    $info = Database::get()->querySingle("SELECT * FROM course_weekly_view WHERE id = ?d AND course_id = ?d $visibility_check", $id, $course_id);
}
if (!$info) {
    $nameTools = $langUnitUnknown;
    $tool_content .= "<p class='caution'>$langUnknownResType</p>";
    draw($tool_content, 2, null, $head_content);
    exit;
} else {
    $nameTools = htmlspecialchars($info->title);
    $comments = trim($info->comments);
}

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
    
    $q = Database::get()->querySingle("SELECT id, start_week, finish_week FROM course_weekly_view
                       WHERE course_id = ?d
                             AND id <> ?d
                             $visibility_check
                             
                       ORDER BY start_week
                       LIMIT 1", $course_id, $id);
    if ($q) {
        $q_id = $q->id;
        //$q_title = htmlspecialchars($q->title);
        $q_title = $langFrom . " " . nice_format($q->start_week) . " $langUntil " .nice_format($q->finish_week);
        $link[$i] = "$arrow1<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$q_id'>$q_title</a>$arrow2";
    } else {
        $link[$i] = '&nbsp;';
    }
}

if ($is_editor) {
    $comment_edit_link = "<td valign='top' width='20'><a href='info.php?course=$course_code&amp;edit=$id&amp;next=1'><img src='$themeimg/edit.png' title='' alt='' /></a></td>";
    $units_class = 'tbl';
} else {
    $units_class = 'tbl';
    $comment_edit_link = '';
}

$tool_content .= "<table class='$units_class' width='99%'>";
if ($link['previous'] != '&nbsp;' or $link['next'] != '&nbsp;') {
    $tool_content .= "
    <tr class='odd'>
      <td class='left'>" . $link['previous'] . '</td>
      <td class="right">' . $link['next'] . "</td>
    </tr>";
}
$tool_content .= "<tr><td colspan='2' class='unit_title'>$nameTools</td></tr></table>";


if (!empty($comments)) {
    $tool_content .= "<table class='tbl' width='99%'>
        <tr class='even'>
          <td>$comments</td>
          $comment_edit_link
        </tr>
        </table>";
}

show_resourcesWeeks($id);

$tool_content .= '<form name="unitselect" action="' . $urlServer . 'modules/weeks/" method="get">';
$tool_content .="
    <table width='99%' class='tbl'>
     <tr class='odd'>
       <td class='right'>" . $langWeeks . ":&nbsp;</td>
       <td width='50' class='right'>" .
        "<select name='id' onChange='document.unitselect.submit();'>";

$q = Database::get()->queryArray("SELECT id, start_week, finish_week, title FROM course_weekly_view
               WHERE course_id = ?d $visibility_check", $course_id);
foreach ($q as $info) {
    $selected = ($info->id == $id) ? ' selected ' : '';
    $tool_content .= "<option value='$info->id'$selected>" .
            nice_format($info->start_week)." ... " . nice_format($info->finish_week) ."</option>";
}
$tool_content .= "</select>
       </td>
     </tr>
    </table>
 </form>";

draw($tool_content, 2, null, $head_content);