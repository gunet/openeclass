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
if (isset($_REQUEST['id'])) {
	$id = intval($_REQUEST['id']);
}
$tool_content = $head_content = '';

$head_content .= '<script type="text/javascript">
function confirmation () {
        if (confirm("'.$langConfirmDelete.'"))
                {return true;}
        else
                {return false;}
}
</script>';


// Process resource actions
if (isset($_REQUEST['edit'])) {
	$res_id = intval($_GET['edit']);
	if ($id = check_admin_unit_resource($res_id)) {
                if ($language == 'greek')
                        $lang_editor = 'el';
                else
                        $lang_editor = 'en';

                $head_content .= "<script type='text/javascript'>
_editor_url  = '$urlAppend/include/xinha/';
_editor_lang = '$lang_editor';
</script>
<script type='text/javascript' src='$urlAppend/include/xinha/XinhaCore.js'></script>
<script type='text/javascript' src='$urlAppend/include/xinha/my_config.js'></script>";
		edit_res($res_id);
	}
}  elseif(isset($_REQUEST['edit_res_submit'])) { // edit resource
	$res_id = intval($_REQUEST['resource_id']);	
	if ($id = check_admin_unit_resource($res_id)) {
		$restitle = autoquote(trim($_REQUEST['restitle']));
                $rescomments = autoquote(trim($_REQUEST['rescomments']));
		$result = db_query("UPDATE unit_resources SET
				title = $restitle,
				comments = $rescomments
				WHERE unit_id = $id AND id = $res_id");
	}
	$tool_content .= "<p class='success_small'>$langResourceUnitModified</p>";
} elseif(isset($_REQUEST['del'])) { // delete resource from course unit
	$res_id = intval($_GET['del']);
	if ($id = check_admin_unit_resource($res_id)) {
		db_query("DELETE FROM unit_resources WHERE id = '$res_id'", $mysqlMainDb);
		$tool_content .= "<p class='success_small'>$langResourceCourseUnitDeleted</p>";
	}
} elseif (isset($_REQUEST['vis'])) { // modify visibility in text resources only 
	$res_id = intval($_REQUEST['vis']);
	if ($id = check_admin_unit_resource($res_id)) {
		$sql = db_query("SELECT `visibility` FROM unit_resources WHERE id=$res_id");
		list($vis) = mysql_fetch_row($sql);
		$newvis = ($vis == 'v')? 'i': 'v';
		db_query("UPDATE unit_resources SET visibility = '$newvis' WHERE id = $res_id");
	}
} elseif (isset($_REQUEST['down'])) { // change order down
	$res_id = intval($_REQUEST['down']);
	if ($id = check_admin_unit_resource($res_id)) {
		$sql = db_query("SELECT `order` FROM unit_resources WHERE id='$res_id' AND unit_id='$id'");
		list($current) = mysql_fetch_row($sql);
		$sql = db_query("SELECT id, `order` FROM unit_resources 
				WHERE `order` > '$current' AND unit_id = $id ORDER BY `order` LIMIT 1");
		list($next_id, $next) = mysql_fetch_row($sql);
		db_query("UPDATE unit_resources SET `order` = $next WHERE id = $res_id AND unit_id = $id");
		db_query("UPDATE unit_resources SET `order` = $current WHERE id = $next_id AND unit_id = $id");
	}
} elseif (isset($_REQUEST['up'])) { // change order up
	$res_id = intval($_REQUEST['up']);
	if ($id = check_admin_unit_resource($res_id)) {
		$sql = db_query("SELECT `order` FROM unit_resources WHERE id='$res_id' AND unit_id='$id'");
		list($current) = mysql_fetch_row($sql);
		$sql = db_query("SELECT id, `order` FROM unit_resources 
				WHERE `order` < '$current' AND unit_id = $id ORDER BY `order` DESC LIMIT 1");
		list($prev_id, $prev) = mysql_fetch_row($sql);
		db_query("UPDATE unit_resources SET `order` = $prev WHERE id = $res_id AND unit_id = $id");
		db_query("UPDATE unit_resources SET `order` = $current WHERE id = $prev_id AND unit_id = $id");
	}
}

if ($is_adminOfCourse) {
        $visibility_check = '';
} else {
        $visibility_check = "AND visibility='v'";
}
$q = db_query("SELECT * FROM course_units
               WHERE id = $id AND course_id=$cours_id " . $visibility_check);
if (!$q or mysql_num_rows($q) == 0) {
        $nameTools = $langUnitUnknown;
        draw('', 2, 'units', $head_content);
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
                                "title='$langInsertText'>$langInsertText</a></li>" .
			"<li><a href='insert.php?type=lp&amp;id=$id' " .
                                "title='$langLearningPath'>$langLearningPath</a></li>" .
			"<li><a href='insert.php?type=video&amp;id=$id' " .
                                "title='$langVideo'>$langVideo</a></li>" .
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

show_resources($id);

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
$tool_content .= '</select></form>';

draw($tool_content, 2, 'units', $head_content);


//------------------------------------
// list of functions
//------------------------------------

// Check that a specified resource id belongs to a resource in the
// current course, and that the user is an admin in this course.
// Return the id of the unit or false if user is not an admin 
function check_admin_unit_resource($resource_id)
{
	global $cours_id, $is_adminOfCourse;
	
	if ($is_adminOfCourse) {
		$q = db_query("SELECT course_units.id FROM course_units,unit_resources WHERE
			course_units.course_id = $cours_id AND course_units.id = unit_resources.unit_id
			AND unit_resources.id = $resource_id");
		if (mysql_num_rows($q) > 0) {
			list($unit_id) = mysql_fetch_row($q);
			return $unit_id;
		}
	}
	return false;
}

// Display resources for unit with id=$id
function show_resources($unit_id)
{
	global $tool_content, $max_resource_id;
	$req = db_query("SELECT * FROM unit_resources WHERE unit_id = $unit_id ORDER BY `order`");
	if (mysql_num_rows($req) > 0) {
		list($max_resource_id) = mysql_fetch_row(db_query("SELECT id FROM unit_resources
                                WHERE unit_id = $unit_id ORDER BY `order` DESC LIMIT 1"));
		$tool_content .= "<table class='resources'>";
		while ($info = mysql_fetch_array($req)) {
			show_resource($info);
		}	
		$tool_content .= "</table>\n";
	}
}


function show_resource($info)
{
        global $tool_content, $langUnknownResType, $is_adminOfCourse;
	
        if ($info['visibility'] == 'i' and !$is_adminOfCourse) {
                return;
        }
        switch ($info['type']) {
                case 'doc':
                        $tool_content .= show_doc($info['title'], $info['comments'], $info['id'], $info['res_id']);
                        break;
		case 'text':
                        $tool_content .= show_text($info['title'], $info['comments'], $info['id'], $info['visibility']);
                        break;
		case 'lp':
                        $tool_content .= show_lp($info['title'], $info['comments'], $info['id'], $info['res_id']);
                        break;
		case 'video':
                        $tool_content .= show_video($info['title'], $info['comments'], $info['id'], $info['res_id']);
                        break;
                default:
                        $tool_content .= $langUnknownResType;
        }
}


// display resource documents
function show_doc($title, $comments, $resource_id, $file_id)
{
        global $is_adminOfCourse, $currentCourseID, $langWasDeleted,
               $visibility_check, $urlServer, $id;

        $title = htmlspecialchars($title);
        $r = db_query("SELECT * FROM document
	               WHERE id =" . intval($file_id) ." $visibility_check", $GLOBALS['currentCourseID']);
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
                if ($file['format'] == '.dir') {
                        $image = '../../template/classic/img/folder.gif';
                        $link = "<a href='{$urlServer}modules/document/document.php?openDir=$file[path]&amp;unit=$id'>$title</a>";
                } else {
                        $image = '../document/img/' .
                                choose_image('.' . $file['format']);
                        $link = "<a href='" . file_url($file['path'], $file['filename']) . "'>$title</a>";
                }
        }
	$class_vis = ($status == 'i' or $status == 'del')? ' class="invisible"': '';
        if (!empty($comments)) {
                $comment = "<tr><td>&nbsp;</td><td>$comments</td>";
        } else {
                $comment = "";
        }
        return "<tr$class_vis><th><img src='$image' /></th><td>$link</td>" .
                ($is_adminOfCourse? actions($resource_id, $status): '') . 
                '</tr>' . $comment;
}


// display resource text
function show_text($title, $comments, $resource_id, $visibility)
{
        global $is_adminOfCourse, $mysqlMainDb, $tool_content;

        $class_vis = ($visibility == 'i')? ' class="invisible"': '';
        $imagelink = "<img src='../../template/classic/img/description_" .
			($visibility == 'i'? 'off': 'on') . ".gif' />";
        if (!empty($comments)) {
                $comment_box = "<tr$class_vis><td>&nbsp;</td><td>$comments</td>";
        } else {
                $comment_box = "";
        }
        $tool_content .= "<tr$class_vis><th>$imagelink</th><td>$title</td>" .
		($is_adminOfCourse? actions($resource_id, $visibility): '') .
                '</tr>' . $comment_box;
}

// display resource learning path
function show_lp($title, $comments, $resource_id, $lp_id)
{
	global $id, $tool_content, $mysqlMainDb, $urlServer, $is_adminOfCourse,
               $langWasDeleted, $currentCourseID;

	$comment_box = $class_vis = $imagelink = $link = '';
        $title = htmlspecialchars($title);
	$r = db_query("SELECT * FROM lp_learnPath WHERE learnPath_id = $lp_id",
                      $currentCourseID);
	if (mysql_num_rows($r) == 0) { // check if lp was deleted
		if (!$is_adminOfCourse) {
			return '';
		} else {
			$status = 'del';
			$imagelink = "<img src='../../template/classic/img/delete.gif' />";
			$link = "<span class='invisible'>$title ($langWasDeleted)</span>";
		}
	} else {
                $lp = mysql_fetch_array($r, MYSQL_ASSOC);
		$status = ($lp['visibility'] == 'SHOW')? 'v': 'i';
		$link = "<a href='${urlServer}modules/learnPath/learningPath.php?path_id=$lp_id&amp;unit=$id'>$title</a>";
		$imagelink = "<img src='../../template/classic/img/lp_" .
			($status == 'i'? 'off': 'on') . ".gif' />";
	}
        if ($status != 'v' and !$is_adminOfCourse) {
			return '';
        }
        if (!empty($comments)) {
                $comment_box = "<tr><td>&nbsp;</td><td>$comments</td>";
        }
        $class_vis = ($status == 'i' or $status == 'del')?
                ' class="invisible"': '';
	return "<tr$class_vis><th>$imagelink</th><td>$link</td>" .
		($is_adminOfCourse? actions($resource_id, $status): '') . 
		'</tr>' . $comment_box;
}


// display resource video
function show_video($title, $comments, $resource_id, $video_id)
{
        global $is_adminOfCourse, $mysqlMainDb, $tool_content;

        $class_vis = ($visibility == 'i')? ' class="invisible"': '';
        if (!empty($comments)) {
                $comment_box = "<tr$class_vis><td>&nbsp;</td><td>$comments</td>";
        } else {
                $comment_box = "";
        }
        $imagelink = "<img src='../../template/classic/img/description_" .
			($visibility == 'i'? 'off': 'on') . ".gif' />";
        $tool_content .= "<tr$class_vis><th>$imagelink</th><td>$title</td>" .
		($is_adminOfCourse? actions($resource_id, $visibility): '') .
                '</tr>' . $comment_box;
}

// resource actions
function actions($resource_id, $status)
{
        global $langEdit, $langDelete, $langVisibility, $langDown, $langUp, $mysqlMainDb;

        static $first = true;

        $icon_vis = ($status == 'v')? 'visible.gif': 'invisible.gif';
	list($res_type) = mysql_fetch_array(db_query("SELECT type FROM unit_resources WHERE id='$resource_id'", $mysqlMainDb));
	
        if ($status != 'del') {
                $content = "<td><a href='$_SERVER[PHP_SELF]?edit=$resource_id'>" .
                "<img src='../../template/classic/img/edit.gif' title='$langEdit' /></a></td>";
        } else {
                $content = '<td>&nbsp;</td>';
        }
        $content .= "<td><a href='$_SERVER[PHP_SELF]?del=$resource_id'" .
                                        " onClick=\"return confirmation();\">" .
                                        "<img src='../../template/classic/img/delete.gif' " .
                                        "title='$langDelete'></img></a></td>";
	 
	if ($status != 'del') {
		if ($res_type == 'text') { 
			$content .= "<td><a href='$_SERVER[PHP_SELF]?vis=$resource_id'>" .
                                        "<img src='../../template/classic/img/$icon_vis' " .
                                        "title='$langVisibility'></img></a></td>";
		} else {
			$content .= "<td>&nbsp;</td>";
		}
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


// edit resource
function edit_res($resource_id) 
{
	global $tool_content, $id, $urlServer, $langTitle, $langDescr, $langModify;
	 
        $sql = db_query("SELECT id, title, comments FROM unit_resources WHERE id='$resource_id'");
        $ru = mysql_fetch_array($sql);
        $restitle = " value='" . htmlspecialchars($ru['title'], ENT_QUOTES) . "'";
        $rescomments = $ru['comments'];
        $resource_id = $ru['id'];

	$tool_content .= "<form method='post' action='${urlServer}modules/units/'>";
	$tool_content .= "<input type='hidden' name='id' value='$id'>";
	$tool_content .= "<input type='hidden' name='resource_id' value='$resource_id'>";
	$tool_content .= "<table class='FormData'><tbody>
	<tr><th width='150' class='left'>$langTitle:</th>
	<td><input type='text' name='restitle' size='50' maxlength='255' $restitle class='FormData_InputText'></td></tr>
        <tr><th class='left'>$langDescr:</th><td>
        <table class='xinha_editor'><tr><td><textarea id='xinha' name='rescomments'>$rescomments</textarea></td></tr>
        </table></td></tr>
        <tr><th>&nbsp;</th>
	<td><input type='submit' name='edit_res_submit' value='$langModify'></td></tr>
	</tbody></table>
	</form>";
}
