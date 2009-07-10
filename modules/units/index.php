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

$id = intval($_GET['id']);

if ($is_adminOfCourse) {
        $visibility_check = '';
} else {
        $visibility_check = "AND visibility='v'";
}

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
$tool_content = '<table class="unit-navigation"><tr><td class="left">' .
        $link['previous'] . '</td><td class="right">' .
        $link['next'] . "</td></tr></table>\n";

if (!empty($comments)) {
        if (strpos('<', $comments) === false) {
                $tool_content .= "<p>$comments</p>";
        } else {
                $tool_content .= $comments;
        }
}

if ($is_adminOfCourse) {
        $tool_content .= "<p>$langAdd: <a href='insert.php?type=doc&amp;id=$id' title='$langDocumentAsModule'>$langDocumentAsModuleLabel</a> | <a href='insert.php?type=exercise&amp;id=$id' title='$langExerciseAsModule'>$langExerciseAsModuleLabel</a> | <a href='insert.php?type=text&amp;id=$id' title='$langCourseDescriptionAsModule'>$langCourseDescriptionAsModuleLabel</a></p>";
}

$tool_content .= '<form class="unit-select" name="unitselect" action="' .
                 $urlServer . 'modules/units/" method="get">' .
                 '<select name="id" onChange="document.unitselect.submit();">';
$q = db_query("SELECT id, title FROM course_units
               WHERE course_id = $cours_id
                     $visibility_check
               ORDER BY `order`");
while ($info = mysql_fetch_array($q)) {
        $selected = ($info['id'] == $id)? ' selected="1" ': '';
        $tool_content .= "<option value='$info[id]'$selected>" .
                         htmlspecialchars($info['title']) .
                         '</option>';
}
$tool_content .= '</select>';

draw($tool_content, 2, 'units');

