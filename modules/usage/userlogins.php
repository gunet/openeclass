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


/*
===========================================================================
    usage/userlogins.php
 * @version $Id$
    @last update: 2006-12-27 by Evelthon Prodromou <eprodromou@upnet.gr>
    @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr,
                    Ophelia Neofytou ophelia@ucnet.uoc.gr
==============================================================================
    @Description: Shows logins made by a user or all users of a course, during a specific period.
    Takes data from table 'logins'

==============================================================================
*/

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'include/jscalendar/calendar.php';

$tool_content .= "
<div id='operations_container'>
  <ul id='opslist'>
    <li><a href='favourite.php?course=$course_code&amp;first='>$langFavourite</a></li>
    <li><a href='userlogins.php?course=$course_code&amp;first='>$langUserLogins</a></li>
    <li><a href='userduration.php?course=$course_code'>$langUserDuration</a></li>
    <li><a href='../learnPath/detailsAll.php?course=$course_code&amp;from_stats=1'>$langLearningPaths</a></li>
    <li><a href='group.php?course=$course_code'>$langGroupUsage</a></li>
  </ul>
</div>\n";


$nameTools = $langUserLogins;
$navigation[] = array('url' => 'usage.php?course='.$course_code, 'name' => $langUsage);
$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';




$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $language, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

$usage_defaults = array (
    'u_user_id' => -1,
    'u_date_start' => strftime('%Y-%m-%d', strtotime('now -2 day')),
    'u_date_end' => strftime('%Y-%m-%d', strtotime('now')),
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = $_POST[$key];
    }
}

$date_fmt = '%Y-%m-%d';
$date_where = " (date_time BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";
$date_what  = "DATE_FORMAT(MIN(date_time), '$date_fmt') AS date_start, DATE_FORMAT(MAX(date_time), '$date_fmt') AS date_end ";

if ($u_user_id != -1) {
    $user_where = " (a.user_id = '$u_user_id') ";
} else {
    $user_where = " (1) ";
}

$sql_1 = "SELECT user_id, ip, date_time FROM logins AS a
                 WHERE ".$date_where."
                 AND ".$user_where."
                 AND course_id = $course_id
                 ORDER BY date_time DESC";

$sql_2 = "SELECT a.user_id as user_id, a.nom as nom, a.prenom as prenom, a.username
                 FROM user AS a LEFT JOIN course_user AS b ON a.user_id = b.user_id
                 WHERE b.course_id = $course_id AND ".$user_where;

// Take data from logins
$result_2= db_query($sql_2, $mysqlMainDb);

$users = array();
while ($row = mysql_fetch_assoc($result_2)) {
    $users[$row['user_id']] = $row['nom'].' '.$row['prenom'];
}

$result = db_query($sql_1);
$table_cont = '';
$unknown_users = array();

$k = 0;
while ($row = mysql_fetch_assoc($result)) {
        $known = false;
        if (isset($users[$row['user_id']])) {
                $user = $users[$row['user_id']];
                $known = true;
        } elseif (isset($unknown_users[$row['user_id']])) {
                $user = $unknown_users[$row['user_id']];
        } else {
                $user = uid_to_name($row['user_id']);
                if ($user === false) {
                        $user = $langAnonymous;
                }
                $unknown_users[$row['user_id']] = $user;
        }
        if ($k%2==0) {
                $table_cont .= "<tr class='even'>";
        } else {
                $table_cont .= "<tr class='odd'>";
        }
        $table_cont .= "<td width='1'><img src='$themeimg/arrow.png' title='bullet'></td>
                <td>";
        if ($known) {
                $table_cont .= $user;
        } else {
                $table_cont .= "<span class='red'>".q($user)."</span>";
        }
        $table_cont .= "</td>
                <td align='center'>".$row['ip']."</td>
                <td align='center'>".$row['date_time']."</td>
                </tr>";

        $k++;
}

//Records exist?
if (count($unknown_users) > 0) {
        $tool_content .= "<p>$langAnonymousExplain</p>\n";
}

if ($table_cont) {
        $tool_content .= "
        <table width='99%' class='tbl_alt'>
        <tr>
        <th colspan='4'>$langUserLogins</th>
        </tr>
        <tr>
        <th colspan='2' class='left'>".$langUser."</th>
        <th>".$langAddress."</th>
        <th>".$langLoginDate."</th>
        </tr>";
        $tool_content .= "".$table_cont."";
        $tool_content .= "
        </table>";
}

if (!($table_cont)) {
        $tool_content .= '<p class="alert1">'.$langNoLogins.'</p>';
}

//make form
$start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center',
                 'name'        => 'u_date_start',
                 'value'       => $u_date_start));

    $end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #CAC3B5; text-align: center',
                 'name'        => 'u_date_end',
                 'value'       => $u_date_end));


    $qry = "SELECT LEFT(a.nom, 1) AS first_letter
        FROM user AS a LEFT JOIN course_user AS b ON a.user_id = b.user_id
        WHERE b.course_id = $course_id
        GROUP BY first_letter ORDER BY first_letter";
    $result = db_query($qry, $mysqlMainDb);

    $letterlinks = '';
    while ($row = mysql_fetch_assoc($result)) {
        $first_letter = $row['first_letter'];
        $letterlinks .= '<a href="?course='.$course_code.'&amp;first='.urlencode($first_letter).'">'.$first_letter.'</a> ';
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


    $user_opts = '<option value="-1">'.$langAllUsers."</option>\n";
    $result = db_query($qry, $mysqlMainDb);
    while ($row = mysql_fetch_assoc($result)) {
        if ($u_user_id == $row['user_id']) { $selected = 'selected'; } else { $selected = ''; }
        $user_opts .= '<option '.$selected.' value="'.$row["user_id"].'">'.$row['prenom'].' '.$row['nom']."</option>\n";
    }

$tool_content .= '
<form method="post" action="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'">
<fieldset>
  <legend>'.$langUserLogins.'</legend>

  <table class="tbl">
  <tbody>
  <tr>
    <th>&nbsp;</th>
    <td><b>'.$langCreateStatsGraph.':</b></td>
  </tr>
  <tr>
    <th>'.$langStartDate.':</th>
    <td>'."$start_cal".'</td>
  </tr>
  <tr>
    <th>'.$langEndDate.':</th>
    <td>'."$end_cal".'</td>
  </tr>
  <tr>
    <th valign="top">'.$langUser.':</th>
    <td>'.$langFirstLetterUser.': '.$letterlinks.' <br /><select name="u_user_id">'.$user_opts.'</select></td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td><input type="submit" name="btnUsage" value="'.$langSubmit.'">
        <div><br /><a href="oldStats.php?course='.$course_code.'">'.$langOldStats.'</a></div>
    </td>
  </tr>
  </table>
</fieldset>
</form>';

draw($tool_content, 2, null, $local_head);
