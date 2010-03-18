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
===========================================================================
    admin/visitsCourseStats.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description:  Shows statistics conserning the number of visits on courses for a time period.
        Statistics can be shown for a specific course or for all courses.

==============================================================================
*/

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langVisitsCourseStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";
$tool_content .= "
  <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li><a href='stateclass.php'>".$langPlatformGenStats."</a></li>
      <li><a href='platformStats.php?first='>".$langVisitsStats."</a></li>
      <li><a href='oldStats.php'>".$langOldStats."</a></li>
      <li><a href='monthlyReport.php'>".$langMonthlyReport."</a></li>
    </ul>
  </div>";


include('../../include/jscalendar/calendar.php');
if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

if (!extension_loaded('gd')) {
    $tool_content .= "<p>$langGDRequired</p>";
} else {
    $made_chart = true;

  /********************************************
    start making the chart
  *********************************************/
    require_once '../../include/libchart/libchart.php';

    //default values for chart
    $usage_defaults = array (
        'u_interval' => 'daily',
        'u_course_id' => -1,
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
    $date_where = "(date_time BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";
    $date_what  = "DATE_FORMAT(MIN(date_time), '$date_fmt') AS date_start, DATE_FORMAT(MAX(date_time), '$date_fmt') AS date_end ";


    #see if chart has content
    $chart_content=0;

    switch ($u_interval) {
            case "summary":
                $date_what = '';
                $date_group = '';
            break;
            case "daily":
                $date_what .= ", DATE_FORMAT(date_time, '$date_fmt') AS date, ";
                $date_group = " GROUP BY DATE(date_time) ";
            break;
            case "weekly":
                $date_what .= ", DATE_FORMAT(date_time - INTERVAL WEEKDAY(date_time) DAY, '$date_fmt') AS week_start ".
                      ", DATE_FORMAT(date_time + INTERVAL (6 - WEEKDAY(date_time)) DAY, '$date_fmt') AS week_end, ";
                $date_group = " GROUP BY WEEK(date_time)";
            break;
            case "monthly":
                $date_what .= ", MONTH(date_time) AS month, ";
                $date_group = " GROUP BY MONTH(date_time)";
            break;
            case "yearly":
                $date_what .= ", YEAR(date_time) AS year, ";
                $date_group = "GROUP BY YEAR(date_time) ";
            break;
    }


    if ($u_course_id == -1) {
     //show chart for all courses
           $qry1 = "SELECT DISTINCT(code) as code from cours";
           $res1 = db_query($qry1, $mysqlMainDb);

           $point = array();
           while ($row1 = mysql_fetch_assoc($res1)) {
                $cours = $row1['code'];

                $query = "SELECT ".$date_what." COUNT(*) AS cnt FROM actions ".
                    " WHERE $date_where  $date_group ORDER BY date_time ASC";
                $result = db_query($query, $cours);

                switch ($u_interval) {
                    case "summary":
                        while ($row = mysql_fetch_assoc($result)) {
                               if (array_key_exists('summary', $point)) {
                                $point['summary'] += $row['cnt'];
                              }
                            else {
                                 $point['summary'] = $row['cnt'];
                              }
                        }
                    break;
                    case "daily":
                        while ($row = mysql_fetch_assoc($result)) {
                            if (array_key_exists($row['date'], $point)) {
                                $point[$row['date']] += $row['cnt'];
                              }
                            else {
                                 $point[$row['date']] = $row['cnt'];
                              }
                        }
                    break;
                    case "weekly":
                        while ($row = mysql_fetch_assoc($result)) {
                            $week = $row['week_start'].' - '.$row['week_end'];
                            if (array_key_exists($week, $point)) {
                                $point[$week] += $row['cnt'];
                              }
                            else {
                                 $point[$week] = $row['cnt'];
                              }
                        }
                    break;
                    case "monthly":
                        while ($row = mysql_fetch_assoc($result)) {
                            $month = $langMonths[$row['month']];
                            if (array_key_exists($month, $point)) {
                                $point[$month] += $row['cnt'];
                              }
                            else {
                                 $point[$month] = $row['cnt'];
                              }
                        }
                    break;
            }
            mysql_free_result($result);
        }

        if ($u_interval != "monthly") {
            ksort($point);
        }

        $chart = new VerticalChart(200, 300);

        //add points to chart
        while ($newp = current($point)){
                $chart->addPoint(new Point(key($point), $newp));
                $chart->width += 25;
                next($point);

                $chart_content=1;
        }
            $chart->setTitle($langVisits);
           mysql_free_result($res1);
    }


    else {    //show chart for a specific course

        $query = "SELECT ".$date_what." COUNT(*) AS cnt FROM actions ".
            " WHERE $date_where $date_group ORDER BY date_time ASC";
        $result = db_query($query, $u_course_id);

        $chart = new VerticalChart(200, 300);

        switch ($u_interval) {
            case "summary":
                while ($row = mysql_fetch_assoc($result)) {
                        $chart->addPoint(new Point($langSummary, $row['cnt']));
                        $chart->width += 25;
                        $chart_content=1;
                }
            break;
            case "daily":
                while ($row = mysql_fetch_assoc($result)) {
                        $chart->addPoint(new Point($row['date'], $row['cnt']));
                        $chart->width += 25;
                        $chart_content=1;
                }
            break;
            case "weekly":
                while ($row = mysql_fetch_assoc($result)) {
                        $chart->setLabelMarginBottom(110);
                        $chart->setLabelMarginRight(80);
                        $chart->addPoint(new Point($row['week_start'].' - '.$row['week_end'], $row['cnt']));
                        $chart->width += 25;
                        $chart_content=1;
                }
            break;
            case "monthly":
                while ($row = mysql_fetch_assoc($result)) {
                    $chart->addPoint(new Point($langMonths[$row['month']], $row['cnt']));
                    $chart->width += 25;
                    $chart_content=1;
                }
            break;
            case "yearly":
                while ($row = mysql_fetch_assoc($result)) {
                    $chart->addPoint(new Point($row['year'], $row['cnt']));
                    $chart->width += 25;
                    $chart_content=1;
                }
            break;
        }

        $chart->setTitle($langVisits);

        mysql_free_result($result);

    }

    if (!file_exists("../../courses/temp")) {
        mkdir("../../courses/temp", 0777);
    }

    $chart_path = 'courses/temp/chart_'.md5(serialize($chart)).'.png';

    $chart->render($webDir.$chart_path);

    //check if there are statistics to show
    if ($chart_content) {
  $tool_content .= '
  <table class="FormData" width="99%" align="left">
  <tbody>
  <tr>
    <th width="220"  class="left">'.$langVisits.' :</th>
    <td valign="top"><img src="'.$urlServer.$chart_path.'" /></td>
  </tr>
  </tbody>
  </table>';
        $tool_content .= '';
} elseif (isset($btnUsage) and $chart_content == 0) {
  $tool_content .= '
  <table class="FormData" width="99%" align="left">
  <tbody>
  <tr>
    <th width="220" class="left">'.$langVisits.' :</th>
    <td valign="top">'.$langNoStatistics.'</td>
  </tr>
  </tbody>
  </table>';
    }
    $tool_content .= '<br />';


/*************************************************************************
   making the Form for determining time period, time interval and course
***************************************************************************/

    //calendar for determining start and end date
    $start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'u_date_start',
                 'value'       => $u_date_start));

    $end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name'        => 'u_date_end',
                 'value'       => $u_date_end));




    //possible courses
    $qry = "SELECT LEFT(intitule, 1) AS first_letter FROM cours
            GROUP BY first_letter ORDER BY first_letter";
    $result = db_query($qry, $mysqlMainDb);
    $letterlinks='';
    while ($row = mysql_fetch_assoc($result)) {
        $first_letter = $row['first_letter'];
        $letterlinks .= '<a href="?first='.$first_letter.'">'.$first_letter.'</a> ';
    }

    if (isset($_GET['first'])) {
        $firstletter = $_GET['first'];
        $qry = "SELECT code, intitule
                FROM cours WHERE LEFT(intitule,1) = '".mysql_real_escape_string($firstletter)."'";
    } else {
        $qry = "SELECT code, intitule FROM cours";
    }

    $cours_opts = '<option value="-1">'.$langAllCourses."</option>\n";
    $result = db_query($qry, $mysqlMainDb);
    while ($row = mysql_fetch_assoc($result)) {
        if ($u_course_id == $row['code']) { $selected = 'selected'; } else { $selected = ''; }
        $cours_opts .= '<option '.$selected.' value="'.$row["code"].'">'.$row['intitule']."</option>\n";
    }


    //possible time intervals
    $statsIntervalOptions =
            '<option value="daily"   '.(($u_interval=='daily')?('selected'):(''))  .' >'.$langDaily."</option>\n".
            '<option value="weekly"  '.(($u_interval=='weekly')?('selected'):('')) .'>'.$langWeekly."</option>\n".
            '<option value="monthly" '.(($u_interval=='monthly')?('selected'):('')).'>'.$langMonthly."</option>\n".
            '<option value="yearly"  '.(($u_interval=='yearly')?('selected'):('')) .'>'.$langYearly."</option>\n".
            '<option value="summary" '.(($u_interval=='summary')?('selected'):('')).'>'.$langSummary."</option>\n";

    //form
     $tool_content .= '
  <form method="post">
  <table class="FormData" width="99%" align="left">
  <tbody>
  <tr>
    <th width="220" class="left">'.$langStartDate.'</th>
    <td>'."$start_cal".'</td>
  </tr>
  <tr>
    <th class="left">'.$langEndDate.'</th>
    <td>'."$end_cal".'</td>
  </tr>
  <tr>
    <th class="left">'.$langFirstLetterCourse.'</th>
    <td>'.$letterlinks.'</td>
  </tr>
  <tr>
    <th class="left">'.$langCourse.'</th>
    <td><select name="u_course_id">'.$cours_opts.'</select></td>
  </tr>
  <tr>
    <th class="left">'.$langInterval.'</th>
    <td><select name="u_interval">'.$statsIntervalOptions.'</select></td>
  </tr>
  <tr>
    <th class="left">&nbsp;</th>
    <td><input type="submit" name="btnUsage" value="'.$langSubmit.'"></td>
  </tr>
  </tbody>
  </table>
  </form>';
    }


draw($tool_content, 3, 'admin', $local_head, '');

/*
if ($made_chart) {

        while (ob_get_level() > 0) {
         ob_end_flush();
      }
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}
*/

?>
