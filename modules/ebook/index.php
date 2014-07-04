<?php

/* ========================================================================
 * Open eClass 3.0
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


$require_current_course = true;
$require_help = true;
$helpTopic = 'EBook';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileManageLib.inc.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action_stats = new action();
$action_stats->record(MODULE_ID_EBOOK);
/* * *********************************** */

$nameTools = $langEBook;

if ($is_editor) {
    $tool_content .= "
   <div id='operations_container'>
     <ul id='opslist'>
       <li><a href='index.php?course=$course_code&amp;create=1'>$langCreate</a>
     </ul>
   </div>";

    if (isset($_POST['delete']) or isset($_POST['delete_x'])) {
        $id = intval($_POST['id']);
        $r = Database::get()->querySingle("SELECT title FROM ebook WHERE course_id = ?d AND id = ?d", $course_id, $id);
        if ($r) {
            $title = $r->title;
            Database::get()->query("DELETE FROM ebook_subsection WHERE section_id IN
                                         (SELECT id FROM ebook_section WHERE ebook_id = ?d)", $id);
            Database::get()->query("DELETE FROM ebook_section WHERE ebook_id = ?d", $id);
            Database::get()->query("DELETE FROM ebook WHERE id = ?d", $id);
            $basedir = $webDir . 'courses/' . $course_code . '/ebook/' . $id;
            my_delete($basedir);
            Database::get()->query("DELETE FROM document WHERE
                                 subsystem = " . EBOOK . " AND
                                 subsystem_id = ?d AND
                                 course_id = ?d", $id, $course_id);
            $tool_content .= "\n    <p class='success'>" . q(sprintf($langEBookDeleted, $title)) . "</p>";
        }
    } elseif (isset($_GET['create'])) {
        $tool_content .= "
   <form method='post' action='create.php?course=$course_code' enctype='multipart/form-data'>
     <fieldset>
     <legend>$langUpload</legend>

     <table width='100%' class='tbl'>
     <tr>
       <th>$langTitle:</th>
       <td><input type='text' name='title' size='53' /></td></tr>
     <tr>
       <th>$langZipFile:</th>
       <td><input type='file' name='file' size='53' /></td>
     </tr>
     <tr>
       <th>&nbsp;</th>
       <td class='right'><input type='submit' name='submit' value='$langSend' /></td>
     </tr>
     </table>
     </fieldset>
   </form>";
    } elseif (isset($_GET['down'])) {
        move_order('ebook', 'id', intval($_GET['down']), 'order', 'down', "course_id = $course_id");
    } elseif (isset($_GET['up'])) {
        move_order('ebook', 'id', intval($_GET['up']), 'order', 'up', "course_id = $course_id");
    } elseif (isset($_GET['vis'])) {
        Database::get()->query("UPDATE ebook SET visible = NOT visible
                                 WHERE course_id = ?d AND
                                       id = ?d", $course_id, $_GET['vis']);
    }
}

if ($is_editor) {
    $visibility_check = '';
} else {
    $visibility_check = "AND visible = 1 AND ebook_subsection.id IS NOT NULL";
}
$q = Database::get()->queryArray("SELECT ebook.id, ebook.title, visible, MAX(ebook_subsection.id) AS sid
                      FROM ebook LEFT JOIN ebook_section ON ebook.id = ebook_id
                           LEFT JOIN ebook_subsection ON ebook_section.id = section_id
                      WHERE course_id = ?d
                            $visibility_check
                      GROUP BY ebook.id
                      ORDER BY `order`", $course_id);

if (!$q) {
    $tool_content .= "\n    <p class='alert1'>$langNoEBook</p>\n";
} else {
    $tool_content .= "
     <script type='text/javascript' src='../auth/sorttable.js'></script>
     <table width='100%' class='sortable' id='t1'>
     <tr>
       <th colspan='2'><div align='left'>$langEBook</div></th>" .
            ($is_editor ?
                    "<th width='70' colspan='2' class='center'>$langActions</th>" :
                    '') . "
     </tr>\n";

    $k = 0;
    $num = count($q);
    foreach ($q as $r) {
        $vis_class = $r->visible ? '' : 'invisible';
        if (is_null($r->sid)) {
            $title_link = q($r->title) . ' <i>(' . $langEBookNoSections . ')</i>';
        } else {
            $title_link = "<a href='show.php/$course_code/$r->id/'>" .
                    q($r->title) . "</a>";
        }
        $warning = is_null($r->sid) ? " <i>($langInactive)</i>" : '';
        $tool_content .= "
     <tr" . odd_even($k, $vis_class) . ">
       <td width='16' valign='top'>
          <img style='padding-top:3px;' src='$themeimg/arrow.png' alt='' /></td>
       <td>$title_link</td>" .
                tools($r->id, $r->title, $k, $num, $r->visible) . "
     </tr>\n";
        $k++;
    }
    $tool_content .= "
     </table>\n";
}

draw($tool_content, 2, null, $head_content);

function tools($id, $title, $k, $num, $vis) {
    global $is_editor, $langModify, $langDelete, $langMove, $langDown, $langUp, $langEBookDelConfirm,
    $course_code, $themeimg, $langVisibility;

    if (!$is_editor) {
        return '';
    } else {
        $icon_vis = $vis ? 'visible.png' : 'invisible.png';
        $num--;
        return "\n        <td width='60' class='center'>\n<form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>\n" .
                "<input type='hidden' name='id' value='$id' />\n<a href='edit.php?course=$course_code&amp;id=$id'>" .
                "<img src='$themeimg/edit.png' alt='$langModify' title='$langModify' />" .
                "</a>&nbsp;<input type='image' src='$themeimg/delete.png'
                                         alt='$langDelete' title='$langDelete' name='delete' value='$id'
                                         onclick=\"javascript:if(!confirm('" .
                js_escape(sprintf($langEBookDelConfirm, $title)) . "')) return false;\" />" .
                "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;vis=$id'>
                           <img src='$themeimg/$icon_vis' alt='$langVisibility' title='$langVisibility'></a>
                        </form></td><td class='right' width='40'>" .
                (($k < $num) ? "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;down=$id'>
                                      <img class='displayed' src='$themeimg/down.png'
                                           title='$langMove $langDown' alt='$langMove $langDown' /></a>" :
                        '') .
                (($k > 0) ? "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;up=$id'>
                                   <img class='displayed' src='$themeimg/up.png'
                                        title='$langMove $langUp' alt='$langMove $langUp' /></a>" :
                        '') . "</td>\n";
    }
}
