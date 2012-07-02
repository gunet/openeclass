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
$require_course_admin = true;
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/jscalendar/calendar.php';
require_once 'include/log.php';

$nameTools = $langUsersLog;
$navigation[] = array('url' => 'index.php?course='.$course_code, 'name' => $langUsage);

$logtype = isset($_POST['logtype'])? intval($_POST['logtype']): '0';
$u_user_id = isset($_REQUEST['u_user_id'])?intval($_REQUEST['u_user_id']):'-1';
$u_module_id = isset($_POST['u_module_id'])? intval($_POST['u_module_id']): '-1';
$u_date_start = isset($_POST['u_date_start'])? $_POST['u_date_start']: strftime('%Y-%m-%d', strtotime('now -15 day'));
$u_date_end = isset($_POST['u_date_end'])? $_POST['u_date_end']: strftime('%Y-%m-%d', strtotime('now +1 day'));

if (isset($_POST['submit'])) {
    $log = new Log();
    $log->display($course_id, $u_user_id, $u_module_id, $logtype, $u_date_start, $u_date_end);
}

//----------------------- jscalendar -----------------------------
$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $language, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();
$start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d'),
           array('style'       => 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center',
                 'name'        => 'u_date_start',
                 'value'       => $u_date_start));

    $end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d'),
           array('style'       => 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center',
                 'name'        => 'u_date_end',
                 'value'       => $u_date_end));

$qry = "SELECT LEFT(a.nom, 1) AS first_letter
        FROM user AS a LEFT JOIN course_user AS b ON a.user_id = b.user_id
        WHERE b.course_id = $course_id
        GROUP BY first_letter ORDER BY first_letter";
$result = db_query($qry);

$letterlinks = '';
while ($row = mysql_fetch_assoc($result)) {
        $first_letter = $row['first_letter'];
        $letterlinks .= '<a href="?course='.$course_code.'&amp;first='.$first_letter.'">'.$first_letter.'</a> ';
}

if (isset($_GET['first'])) {
        $firstletter = mysql_real_escape_string($_GET['first']);
        $qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, b.statut
                FROM user AS a LEFT JOIN course_user AS b ON a.user_id = b.user_id
                WHERE b.course_id = $course_id AND LEFT(a.nom,1) = '$firstletter'";
} else {
        $qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, b.statut
        FROM user AS a LEFT JOIN course_user AS b ON a.user_id = b.user_id
        WHERE b.course_id = $course_id";
}

$user_opts = "<option value='-1'>$langAllUsers</option>";
$result = db_query($qry);
while ($row = mysql_fetch_assoc($result)) {
        if ($u_user_id == $row['user_id']) {
                $selected = 'selected';
        } else {
                $selected = '';
        }
        $user_opts .= '<option '.$selected.' value="'.$row["user_id"].'">'.$row['prenom'].' '.$row['nom']."</option>\n";
}
$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
        <fieldset>
        <legend>$langUsersLog</legend>
        <table class='tbl'>
        <tr>
        <td>&nbsp;</td>
        <td class='bold'>$langCreateStatsGraph:</td>
        </tr>
        <th class='left'>$langLogModules :</th>
        <td><select name='u_module_id'>";
        $tool_content .= "<option value='-1'>$langAllModules</option>";
        foreach ($modules as $m => $mid) {
                $extra = '';
                if ($u_module_id == $m) {
                        $extra = 'selected';
                }
                $tool_content .= "<option value=".$m." $extra>".$mid['title']."</option>";
        }
        $tool_content .= "</select></td></tr>
        <tr><th class='left'>$langLogTypes :</th>
         <td>";
        $log_types = array('0' => $langAllActions,
                           '1' => $langInsert,
                           '2' => $langModify,
                           '3' => $langDelete);
        $tool_content .= selection($log_types, 'logtype', $logtype);
        $tool_content .= "</td></tr>
        <tr>
        <th class='left'>$langStartDate :</th>
        <td>$start_cal</td>
        </tr>
        <tr>
        <th class='left'>$langEndDate :</th>
        <td>$end_cal</td>
        </tr>
        <tr>
        <th class='left' rowspan='2' valign='top'>$langUser:</td>
        <td>$langFirstLetterUser : $letterlinks </td>
        </tr>
        <tr>
        <td><select name='u_user_id'>$user_opts</select></td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        <td><input type='submit' name='submit' value='$langSubmit'>
        </td>
        </tr>
        </table>
        </fieldset>
        </form>";

draw($tool_content, 2, null, $local_head);
