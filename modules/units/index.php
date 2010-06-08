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

/*
Units display module	
*/

$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'AddCourseUnitscontent';
include '../../include/baseTheme.php';
include '../../include/lib/fileDisplayLib.inc.php';
include '../../include/action.php';
include '../../include/phpmathpublisher/mathpublisher.php';
include 'functions.php';

$action = new action();
$action->record('MODULE_ID_UNITS');
mysql_select_db($mysqlMainDb);

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

if (isset($_REQUEST['edit_submit'])) {
        units_set_maxorder();
        $tool_content .= handle_unit_info_edit();
}

process_actions();

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
                             AND `order` >= 0
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
                        "<li>$langAdd: <a href='insert.php?type=doc&amp;id=$id'>$langInsertDoc</a></li>" .
                        "<li><a href='insert.php?type=exercise&amp;id=$id'>$langInsertExercise</a></li>" .
                        "<li><a href='insert.php?type=text&amp;id=$id'>$langInsertText</a></li>" .
			"<li><a href='insert.php?type=link&amp;id=$id'>$langInsertLink</a></li>" .
			"<li><a href='insert.php?type=lp&amp;id=$id'>$langLearningPath1</a></li>" .
			"<li><a href='insert.php?type=video&amp;id=$id'>$langInsertVideo</a></li>" .
			"<li><a href='insert.php?type=forum&amp;id=$id'>$langInsertForum</a></li>" .
			"<li><a href='insert.php?type=work&amp;id=$id'>$langInsertWork</a></li>" .
			"<li><a href='insert.php?type=wiki&amp;id=$id'>$langInsertWiki</a></li>" .
                        "</ul></div>\n";
}

if ($is_adminOfCourse) {
        $comment_edit_link = "<td valign='top' width='3%'><a href='info.php?edit=$id&amp;next=1'><img src='../../template/classic/img/edit.gif' title='' alt='' /></a></td>";
} else {
        $comment_edit_link = '';
}

$tool_content .= '<table class="unit-navigation"><tr><td class="left">' .
                 $link['previous'] . '</td><td class="right">' .
                 $link['next'] . "</td></tr></table>\n";

if (!empty($comments)) {
        $tool_content .= "<table class='resources' width='99%'><tbody><tr>$comment_edit_link<td>$comments</td></tr></tbody></table>";
}

show_resources($id);

$tool_content .= '<form class="unit-select" name="unitselect" action="' .
                 $urlServer . 'modules/units/" method="get">' .
                 '<table align="left"><tbody><tr><th class="left">'.$langCourseUnits.':&nbsp;</th><td>'.
                 '<select class="auth_input" name="id" onChange="document.unitselect.submit();">';
$q = db_query("SELECT id, title FROM course_units
               WHERE course_id = $cours_id AND `order` > 0
                     $visibility_check
               ORDER BY `order`", $mysqlMainDb);
while ($info = mysql_fetch_array($q)) {
        $selected = ($info['id'] == $id)? ' selected="1" ': '';
        $tool_content .= "<option value='$info[id]'$selected>" .
                         htmlspecialchars(ellipsize($info['title'], 40)) .
                         '</option>';
}
$tool_content .= '</select></td></tr></tbody></table></form>';

draw($tool_content, 2, 'units', $head_content);

