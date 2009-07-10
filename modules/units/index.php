<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2009  Greek Universities Network - GUnet
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

/*
Units display module	
*/

$require_current_course = true;
include '../../include/baseTheme.php';
include "../../include/lib/fileDisplayLib.inc.php";
$id = intval($_GET['id']);

if ($is_adminOfCourse) {
        $visibility_check = '';
} else {
        $visibility_check = "AND visibility='v'";
}
$tool_content = '';

$q = db_query("SELECT * FROM course_units
               WHERE id=$id AND course_id=$cours_id " . $visibility_check);
if (!$q or mysql_num_rows($q) == 0) {
        $nameTools = $langUnitUnknown;
        draw('', 2, 'units');
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
        $q = db_query("SELECT id, title FROM course_units
                       WHERE course_id = $cours_id
                             AND id <> $id
                             AND `order` $op $info[order]
                             $visibility_check
                       ORDER BY `order` $dir
                       LIMIT 1");
        if ($q and mysql_num_rows($q) > 0) {
                list($q_id, $q_title) = mysql_fetch_row($q);
                $q_title = htmlspecialchars($q_title);
                $link[$i] = "<a href='$_SERVER[PHP_SELF]?id=$q_id'>$arrow1$q_title$arrow2</a>";
        } else {
                $link[$i] = '&nbsp;';
        }
}
if ($is_adminOfCourse) {
        $tool_content .= "<div id='operations_container'><ul id='opslist'>" .
                        "<li><a href='insert.php?type=doc&amp;id=$id' " .
                                "title='$langDocumentAsModule'>$langAdd: $langDocumentAsModuleLabel</a></li>" .
                        "<li><a href='insert.php?type=exercise&amp;id=$id' " .
                                "title='$langExerciseAsModule'>$langExerciseAsModuleLabel</a></li>" .
                        "<li><a href='insert.php?type=text&amp;id=$id' " .
                                "title='$langCourseDescriptionAsModule'>$langCourseDescriptionAsModuleLabel</a></li>" .
                        "</ul></div>\n";
}

$tool_content .= '<table class="unit-navigation"><tr><td class="left">' .
        $link['previous'] . '</td><td class="right">' .
        $link['next'] . "</td></tr></table>\n";

if (!empty($comments)) {
        if (strpos('<', $comments) === false) {
                $tool_content .= "<p>$comments</p>";
        } else {
                $tool_content .= $comments;
        }
}

// Display resources
$req = db_query("SELECT * FROM unit_resources WHERE unit_id = $id ORDER BY `order`");
if (mysql_num_rows($req) > 0) {
        list($max_resource_id) = mysql_fetch_row(db_query("SELECT id FROM unit_resources
                                WHERE unit_id = $id ORDER BY `order` DESC LIMIT 1"));

        $tool_content .= '<table>';
        while ($info = mysql_fetch_array($req)) {
                show_resource($info);
        }
        $tool_content .= '</table>';
}

$tool_content .= '<form class="unit-select" name="unitselect" action="' .
                 $urlServer . 'modules/units/" method="get">' .
                 '<select name="id" onChange="document.unitselect.submit();">';
$q = db_query("SELECT id, title FROM course_units
               WHERE course_id = $cours_id
                     $visibility_check
               ORDER BY `order`", $mysqlMainDb);
while ($info = mysql_fetch_array($q)) {
        $selected = ($info['id'] == $id)? ' selected="1" ': '';
        $tool_content .= "<option value='$info[id]'$selected>" .
                         htmlspecialchars($info['title']) .
                         '</option>';
}
$tool_content .= '</select>';

draw($tool_content, 2, 'units');


function show_resource($info)
{
        global $tool_content;

        switch ($info['type']) {
                case 'doc':
                        $tool_content .= show_doc($info['title'], $info['comments'], $info['res_id'], $info['id']);
                        break;
                default:
                        $tool_content .= "Error! Unknown resource type '$info[type].";
        }
}


function show_doc($title, $comments, $file_id, $resource_id)
{
        global $is_adminOfCourse, $currentCourseID, $langWasDeleted;

        $r = db_query("SELECT * FROM document
	               WHERE id =" . intval($file_id), $GLOBALS['currentCourseID']);
        if (mysql_num_rows($r) == 0) {
                if (!$is_adminOfCourse) {
                        return '';
                }
                $status = 'del';
                $image = '../../template/classic/img/delete.gif';
                $link = "<span class='invisible'>$title ($langWasDeleted)</span>";
        } else {
                $file = mysql_fetch_array($r, MYSQL_ASSOC);
                $status = $file['visibility'];
                $image = '../document/img/' . choose_image('.' . $file['format']);
                $link = "<a href='" . file_url($file['path'], $file['filename']) . "'>$file[filename]</a>";
        }
        if (!empty($comments)) {
                $comment = "<p>$comments</p>";
        } else {
                $comment = "";
        }

        return "<tr><td><img src='$image' /></td><td>$link$comment</td>" .
                ($is_adminOfCourse? actions($resource_id, $status): '') . 
                "</tr>";
}

function actions($resource_id, $status)
{
        global $langEdit, $langDelete, $langVisibility, $langDown,  $langUp;

        static $first = true;

        $icon_vis = ($status == 'v')? 'visible.gif': 'invisible.gif';
        $class_vis = ($status == 'i' or $status == 'del')? ' class="invisible"': '';

        if ($status != 'del') {
                $content = "<td><a href='$_SERVER[PHP_SELF]?edit=$resource_id'>" .
                "<img src='../../template/classic/img/edit.gif' title='$langEdit' /></a></td>";
        } else {
                $content = '<td>&nbsp;</td>';
        }
        $content .= "<td><a href='$_SERVER[PHP_SELF]?del=$resource_id' " .
                                        "onClick=\"return confirmation();\">" .
                                        "<img src='../../template/classic/img/delete.gif' " .
                                        "title='$langDelete'></img></a></td>";
        if ($status != 'del') {
                $content .= "<td><a href='$_SERVER[PHP_SELF]?vis=$resource_id'>" .
                                        "<img src='../../template/classic/img/$icon_vis' " .
                                        "title='$langVisibility'></img></a></td>";
        } else {
                $content .= '<td>&nbsp;</td>';
        }
        if ($resource_id != $GLOBALS['max_resource_id']) {
                $content .= "<td><a href='$_SERVER[PHP_SELF]?down=$resource_id'>" .
                        "<img src='../../template/classic/img/down.gif' title='$langDown'></img></a></td>";
        } else {
                $content .= "<td>&nbsp;</td>";
        }
        if (!$first) {
                $content .= "<td><a href='$_SERVER[PHP_SELF]?up=$resource_id'>" .
                        "<img src='../../template/classic/img/up.gif' title='$langUp'></img></a></td>";
        } else {
                $content .= "<td>&nbsp;</td>";
        }
        $first = false;
        return $content;
}