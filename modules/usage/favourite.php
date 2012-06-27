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
    usage/favourite.php
 * @version $Id$
    @last update: 2006-12-27 by Evelthon Prodromou <eprodromou@upnet.gr>
    @authors list: Vangelis Haniotakis haniotak@ucnet.uoc.gr,
                    Ophelia Neofytou ophelia@ucnet.uoc.gr
==============================================================================
    @Description: Creates a pie-chart with the preferences of the users regarding the
    modules of the specific course in a given time period. Also creates a form which is used by the user to specify the
    parameters in order for the chart to be made.

==============================================================================
*/

$require_current_course = true;
$require_course_admin = true;
$require_help	= true;
$helpTopic = 'Usage';
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'include/jscalendar/calendar.php';
require_once 'include/libchart/classes/libchart.php';

$tool_content .= "
<div id='operations_container'>
  <ul id='opslist'>
    <li><a href='favourite.php?course=$course_code&amp;first='>$langFavourite</a></li>
    <li><a href='userlogins.php?course=$course_code&amp;first='>$langUserLogins</a></li>
    <li><a href='userduration.php?course=$course_code'>$langUserDuration</a></li>
    <li><a href='../learnPath/detailsAll.php?course=$course_code&amp;from_stats=1'>$langLearningPaths</a></li>
    <li><a href='group.php?course=$course_code'>$langGroupUsage</a></li>
  </ul>
</div>";


$dateNow = date("d-m-Y / H:i:s",time());
$nameTools = $langFavourite;
$navigation[] = array('url' => 'usage.php?course='.$course_code, 'name' => $langUsage);

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066; padding-left: 15px; padding-right : 15px; }
    .content { position: relative; left: 25px; }';



$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $language, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

    if (!extension_loaded('gd')) {
        $tool_content .= "<p class='caution_small'>$langGDRequired</p>";
    } else {
        $made_chart = true;
        //make chart

        $usage_defaults = array (
            'u_stats_value' => 'visits',
            'u_user_id' => -1,
            'u_date_start' => strftime('%Y-%m-%d', strtotime('now -15 day')),
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
        $user_where = " (user_id = '$u_user_id') ";
    } else {
        $user_where = " (1) ";
    }

    #check if statistics exist
    $chart_content = 0;

    switch ($u_stats_value) {
        case "visits":
            $query = "SELECT module_id, COUNT(*) AS cnt FROM actions
                        WHERE $date_where
                        AND course_id = $course_id
                        AND $user_where GROUP BY module_id";

            $result = db_query($query);
            $chart = new PieChart(600, 300);
            $dataSet = new XYDataSet();
            while ($row = mysql_fetch_assoc($result)) {
                $mid = $row['module_id'];
                if ($mid == MODULE_ID_UNITS) { // course units
                        $dataSet->addPoint(new Point($langCourseUnits, $row['cnt']));
                } else { // other modules
                        $dataSet->addPoint(new Point($modules[$mid]['title'], $row['cnt']));
                }
                $chart->width += 7;
                $chart->setDataSet($dataSet);
                $chart_content = 5;
            }
            $chart->setTitle("$langFavourite");

        break;

        case "duration":
            $query = "SELECT module_id, SUM(duration) AS tot_dur FROM actions
                        WHERE $date_where
                        AND course_id = $course_id
                        AND $user_where GROUP BY module_id";

            $result = db_query($query);

            $chart = new PieChart(600, 300);
            $dataSet = new XYDataSet();
            while ($row = mysql_fetch_assoc($result)) {
                $mid = $row['module_id'];
                if ($mid == MODULE_ID_UNITS) { // course inits
                        $dataSet->addPoint(new Point($langCourseUnits, $row['tot_dur']));
                } else { // other modules
                        $dataSet->addPoint(new Point($modules[$mid]['title'], $row['tot_dur']));
                }
                $chart->width += 7;
                $chart->setDataSet($dataSet);
                $chart_content=5;
            }

            $chart->setTitle("$langFavourite");
            $tool_content .= "\n<br /><small>($langDurationExpl)</small></p>";

        break;
    }
    mysql_free_result($result);
    $chart_path = '/courses/'.$course_code.'/temp/chart_'.md5(serialize($chart)).'.png';

    if (isset($_POST['btnUsage'])) {
        if ($chart_content > 0) {
            $chart->render($webDir.$chart_path);
            $tool_content .= "<p>$langFavouriteExpl</p>\n";
            $tool_content .= '<p class="center"><img src="'.$urlServer.$chart_path.'" /></p>';
         } else {
            $tool_content .= '<p class="alert1">'.$langNoStatistics.'</p>';
        }
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

    $user_opts = '<option value="-1">'.$langAllUsers."</option>\n";
    $result = db_query($qry, $mysqlMainDb);
    while ($row = mysql_fetch_assoc($result)) {
        if ($u_user_id == $row['user_id']) { $selected = 'selected'; } else { $selected = ''; }
        $user_opts .= '<option '.$selected.' value="'.$row["user_id"].'">'.$row['prenom'].' '.$row['nom']."</option>\n";
    }

    $statsValueOptions =
        '<option value="visits" '. (($u_stats_value=='visits')?('selected'):(''))	  .'>'.$langVisits."</option>\n".
        '<option value="duration" '.(($u_stats_value=='duration')?('selected'):('')) .'>'.$langDuration."</option>\n";

    $tool_content .= '
    <form method="post" action="'.$_SERVER['SCRIPT_NAME'].'?course='.$course_code.'">
    <fieldset>
     <legend>'.$langFavourite.'</legend>
     <table class="tbl">
     <tr>
       <td>&nbsp;</td>
       <td class="bold">'.$langCreateStatsGraph.':</td>
     </tr>
     <tr>
       <td>'.$langValueType.':</td>
       <td><select name="u_stats_value">'.$statsValueOptions.'</select></td>
     </tr>
     <tr>
       <td>'.$langStartDate.':</td>
       <td>'."$start_cal".'</td>
     </tr>
     <tr>
       <td>'.$langEndDate.':</td>
       <td>'."$end_cal".'</td>
     </tr>
     <tr>
       <td rowspan="2" valign="top">'.$langUser.':</td>
       <td>'.$langFirstLetterUser.': '.$letterlinks.'</td>
     </tr>
     <tr>
       <td><select name="u_user_id">'.$user_opts.'</select></td>
     </tr>
     <tr>
       <td>&nbsp;</td>
       <td><input type="submit" name="btnUsage" value="'.$langSubmit.'">
           <div><br /><a href="oldStats.php?course='.$course_code.'">'.$langOldStats.'</a></div>
       </td>
     </tr>
     </table>
    </fieldset>
    </form>';
}

draw($tool_content, 2, null, $local_head);
