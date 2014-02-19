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

$require_login = true;
$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'User';

require_once '../../include/baseTheme.php';
require_once 'modules/admin/admin.inc.php';
require_once 'include/log.php';
load_js('tools.js');

define('COURSE_USERS_PER_PAGE', 10);

$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 0;

$nameTools = $langAdminUsers;


$countUser = Database::get()->querySingle("SELECT COUNT(user.id) as count FROM course_user, user WHERE course_user.course_id = ?d AND course_user.user_id = user.id AND user.status = ?d ", $course_id, USER_STUDENT)->count;

$teachers = $students = $visitors = 0;


$limit_sql = '';


// show help link and link to Add new user, search new user and management page of groups
$tool_content .= "

<div id='operations_container'>
  <ul id='opslist'>
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;search=1'>$langAttendanceManagement</a></li>
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langAttendanceAddActivity</a></li>
  </ul>
</div>";





if ($is_editor) { } //check an einai admin sto mathima


if(isset($_GET['addActivity'])){
    
    $tool_content .= "niceeee....";
}
else{
    // display number of users
    $tool_content .= "
<div class='info'><b>$langTotal</b>: <span class='grey'><b>$countUser </b><em>$langStudents &nbsp;</em></span><br />
  <b>$langDumpUser $langCsv</b>: 1. <a href='dumpuser.php?course=$course_code'>$langcsvenc2</a>
       2. <a href='dumpuser.php?course=$course_code&amp;enc=1253'>$langcsvenc1</a>
  </div>";



// display navigation links if course users > COURSE_USERS_PER_PAGE
    if ($countUser > COURSE_USERS_PER_PAGE and !isset($_GET['all'])) {
        $limit_sql = "LIMIT $limit, " . COURSE_USERS_PER_PAGE;
        $tool_content .= show_paging($limit, COURSE_USERS_PER_PAGE, $countUser, $_SERVER['SCRIPT_NAME'], $search_params, TRUE);
    }

    if (isset($_GET['all'])) {
        $extra_link = '&amp;all=true';
    } else {
        $extra_link = '&amp;limit=' . $limit;
    }


    $tool_content .= "
<table width='100%' class='tbl_alt custom_list_order'>
<tr>
  <th width='1'>$langID</th>
  <th><div align='left'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ord=s$extra_link'>$langSurnameName</a></div></th>
  <th class='center' width='160'>$langGroup</th>
  <th class='center' width='90'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ord=rd$extra_link'>$langRegistrationDateShort</a></th>
  <th class='center'>$langRole</th>
  <th class='center'>$langAttendanceΑbsences</th>
  <th class='center'>$langAttendanceEdit</th>
</tr>";


// Numerating the items in the list to show: starts at 1 and not 0
    $i = $limit + 1;
    $ord = isset($_GET['ord']) ? $_GET['ord'] : '';

    switch ($ord) {
        case 's': $order_sql = 'ORDER BY surname';
            break;
        case 'e': $order_sql = 'ORDER BY email';
            break;
        case 'am': $order_sql = 'ORDER BY am';
            break;
        case 'rd': $order_sql = 'ORDER BY course_user.reg_date DESC';
            break;
        default: $order_sql = 'ORDER BY user.status, editor DESC, tutor DESC, surname, givenname';
            break;
    }

    DataBase::get()->queryFunc("SELECT user.id as userID, user.surname , user.givenname, user.email,
                           user.am, user.has_icon, course_user.status as courseUserStatus,
                           course_user.tutor, course_user.editor, course_user.reviewer, course_user.reg_date
                           FROM course_user, user
                           WHERE `user`.id = `course_user`.`user_id`
                           AND `course_user`.`course_id` = ?d
                           AND user.status = ?d 
                           $order_sql $limit_sql", function($myrow) use(&$tool_content, $course_id, &$i, $langAm) {

        // bi colored table
        if ($i % 2 == 0) {
            $tool_content .= "<tr class='odd'>";
        } else {
            $tool_content .= "<tr class='even'>";
        }
        // show public list of users
        $am_message = empty($myrow->am) ? '' : ("<div class='right'>($langAm: " . q($myrow->am) . ")</div>");
        $tool_content .= "
            <td class='smaller right'>$i.</td>\n" .
                "<td class='smaller'>ONOMA TEMP"
                //. display_user($myrow->userID) . 
                . "&nbsp;&nbsp;(" . mailto($myrow->email) . ")  $am_message</td>\n";
        $tool_content .= "\n" .
                "<td class='smaller' width='150'>GROUP TEMP"
                //. user_groups($course_id, $myrow->id) . 
                . "</td>\n" .
                "<td class='smaller center'>";
        if ($myrow->reg_date == '0000-00-00') {
            $tool_content .= $langUnknownDate;
        } else {
            $tool_content .= nice_format($myrow->reg_date);
        }
        $tool_content .= "</td>";
        $tool_content .= "<td class='$class center' width='30'>";

        // tutor right
        if ($myrow->tutor == '1') {
            $tool_content .= "tutor - ";
        }
        // editor right
        if ($myrow->editor == '1') {
            $tool_content .= "editor";
        }

        $tool_content .= "</td>";

        $tool_content .= "<td class='center'>3</td>";
        $tool_content .= "<td class='center'>επεξεργασία</td>";

        $i++;
    }, $course_id, USER_STUDENT, $order_sql);

    $tool_content .= "</table>";
}//check an einai othoni provolhs


draw($tool_content, 2, null, $head_content);




  