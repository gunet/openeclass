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

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action_stats = new action();
$action_stats->record('MODULE_ID_EBOOK');
/**************************************/

mysql_select_db($mysqlMainDb);

$nameTools = $langEBook;

if ($is_adminOfCourse) {
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

        if (isset($_GET['down'])) {
                move_order('link', 'id', intval($_GET['down']), 'order', 'down', "course_id = $cours_id");
        } elseif (isset($_GET['up'])) {
                move_order('link', 'id', intval($_GET['up']), 'order', 'up', "course_id = $cours_id");
        }
}

$q = db_query("SELECT * FROM `ebook` WHERE course_id = $cours_id ORDER BY `order`");

if (mysql_num_rows($q) == 0) {
        $tool_content .= "<p class='alert1'>$langNoEBook</p>\n";
} else {
        $tool_content .= "<ul>\n";
        while ($r = mysql_fetch_array($q)) {
               $tool_content .= "<li><a href='show.php/$currentCourseID/" . urlencode($r['id']) .
                               "/'>" . q($r['title']) . "</a>" . tools($r['id']) . "</li>\n";
        }
        $tool_content .= "</ul>\n";
}

draw($tool_content, 2, '', $head_content);

function tools($id)
{
        global $is_adminOfCourse;

        if (!$is_adminOfCourse) {
                return '';
        } else {
                return " <a href='edit.php?id=$id'>EDIT</a> <a href='delete.php?id=$id'>DELETE</a> <a href='index.php?up=$id'>UP</a> <a href='index.php?down=$id'>DOWN</a> ";
        }       
}
