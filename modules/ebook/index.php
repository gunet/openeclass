<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

$require_current_course = true;
$require_help = true;
$helpTopic = 'EBook';
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/fileManageLib.inc.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action_stats = new action();
$action_stats->record('MODULE_ID_EBOOK');
/**************************************/

mysql_select_db($mysqlMainDb);

$nameTools = $langEBook;

if ($is_adminOfCourse) {
        $tool_content .= "<div id='operations_container'><ul id='opslist'>
                             <li><a href='index.php?create=1'>$langCreate</a></ul></div>";

        if (isset($_POST['delete']) or isset($_POST['delete.x'])) {
                $id = intval($_POST['id']);
                $r = db_query("SELECT title FROM ebook WHERE course_id = $cours_id AND id = $id");
                if (mysql_num_rows($r) > 0) {
                        list($title) = mysql_fetch_row($r);
                        db_query("DELETE FROM ebook WHERE course_id = $cours_id AND id = $id");
                        $basedir = $webDir . 'courses/' . $currentCourseID . '/ebook/' . $id;
                        my_delete($basedir);
                        $tool_content .= "<p class='success'>" . q(sprintf($langEBookDeleted, $title)) . "</p>";
                }
        } elseif (isset($_GET['create'])) {
                $tool_content .= "<form method='post' action='upload.php' enctype='multipart/form-data'>
                                     <fieldset><legend>$langUpload</legend>
                                        <table width='99%' class='tbl'>
                                           <tr><th>$langTitle:</th>
                                                    <td><input type='text' name='title' size='53' /></td></tr>
                                           <tr><th>$langZipFile:</th>
                                               <td><input type='file' name='file' size='53' /></td></tr>
                                           <tr><th>&nbsp;</th>
                                               <td><input type='submit' name='submit' value='$langSend' /></td></tr>
                                  </table></fieldset></form>";
        } elseif (isset($_GET['down'])) {
                move_order('ebook', 'id', intval($_GET['down']), 'order', 'down', "course_id = $cours_id");
        } elseif (isset($_GET['up'])) {
                move_order('ebook', 'id', intval($_GET['up']), 'order', 'up', "course_id = $cours_id");
        }
}

$q = db_query("SELECT * FROM `ebook` WHERE course_id = $cours_id ORDER BY `order`");

if (mysql_num_rows($q) == 0) {
        $tool_content .= "<p class='alert1'>$langNoEBook</p>\n";
} else {
        $tool_content .= "<table width='99%' class='tbl_alt'>
                             <tr><th>&nbsp;</th>
                                 <th>$langEBook</th>" .
                                 ($is_adminOfCourse? "<th width='70'>$langActions</th>
                                                      <th width='70'>$langMove</th>\n":
                                                     '') .
                             "</tr>\n";

        $k = 0;
        $num = mysql_num_rows($q);
        while ($r = mysql_fetch_array($q)) {
                $tool_content .= "<tr" . odd_even($k) . "><td width='1' valign='top'>" .
                                 "<img style='padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' " .
                                 " alt='' /></td><td><a href='show.php/$currentCourseID/" . urlencode($r['id']) .
                                 "/'>" . q($r['title']) . "</a></td>" . tools($r['id'], $r['title'], $k, $num) . "</tr>\n";
                $k++;
        }
        $tool_content .= "</table>\n";
}

draw($tool_content, 2, '', $head_content);

function tools($id, $title, $k, $num)
{
        global $is_adminOfCourse, $langModify, $langDelete, $langDown, $langUp, $langEBookDelConfirm;

        if (!$is_adminOfCourse) {
                return '';
        } else {
                $num--;
                return "\n<td width='70' class='right'>\n<form action='$_SERVER[PHP_SELF]' method='post'>\n" .
                       "<input type='hidden' name='id' value='$id' />\n<a href='edit.php?id=$id'>" .
                       "<img src='../../template/classic/img/edit.gif' alt='$langModify' title='$langModify' />" .
                       "</a>&nbsp;<input type='image' src='../../template/classic/img/delete.gif'
                                         alt='$langDelete' title='$langDelete' name='delete' value='$id'
                                         onclick=\"javascript:if(!confirm('".
                       js_escape(sprintf($langEBookDelConfirm, $title)) ."')) return false;\" />" .
                       "</form></td><td>" .
                       (($k < $num)? "<a href='$_SERVER[PHP_SELF]?down=$id'>
                                      <img class='displayed' src='../../template/classic/img/down.gif'
                                           title='$langDown' alt='$langDown' /></a>":
                                     '') . '&nbsp;' .
                       (($k > 0)? "<a href='$_SERVER[PHP_SELF]?up=$id'>
                                   <img class='displayed' src='../../template/classic/img/up.gif'
                                        title='$langUp' alt='$langUp' /></a>":
                                  '') . '</td>';
        }
}
