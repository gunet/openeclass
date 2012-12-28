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
    admin/visitsCourseStats.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description:  Shows statistics conserning the number of visits on courses for a time period.
        Statistics can be shown for a specific course or for all courses.

==============================================================================
*/

$require_admin = true;

require_once '../../include/baseTheme.php';

$nameTools = $langVisitsCourseStats;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='stateclass.php'>$langPlatformGenStats</a></li>
      <li><a href='platformStats.php?first='>$langVisitsStats</a></li>
      <li><a href='oldStats.php'>$langOldStats</a></li>
      <li><a href='monthlyReport.php'>$langMonthlyReport</a></li>
    </ul>
  </div>";

require_once 'include/jscalendar/calendar.php';
$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $language, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

if (!extension_loaded('gd')) {
    $tool_content .= "<p>$langGDRequired</p>";
} else {
    $made_chart = true;

  /********************************************
    start making the chart
  *********************************************/
    require_once 'include/libchart/classes/libchart.php';

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
                $$key = q($_POST[$key]);
            }
    }

    $date_fmt = '%Y-%m-%d';
    $date_where = "(day BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";
    $date_what  = "";


    #see if chart has content
    $chart_content=0;

    switch ($u_interval) {
            case "summary":
                $date_what = '';
                $date_group = '';
            break;
            case "daily":
                $date_what .= " DATE_FORMAT(day, '$date_fmt') AS date, ";
                $date_group = " GROUP BY day ";
            break;
            case "weekly":
                $date_what .= " DATE_FORMAT(day - INTERVAL WEEKDAY(day) DAY, '$date_fmt') AS week_start ".
                      ", DATE_FORMAT(day + INTERVAL (6 - WEEKDAY(day)) DAY, '$date_fmt') AS week_end, ";
                $date_group = " GROUP BY WEEK(day) ";
            break;
            case "monthly":
                $date_what .= " MONTH(day) AS month, ";
                $date_group = " GROUP BY MONTH(day) ";
            break;
            case "yearly":
                $date_what .= " YEAR(day) AS year, ";
                $date_group = " GROUP BY YEAR(day) ";
            break;
            default:
                $date_what = '';
                $date_group = '';
            break;
    }


    if ($u_course_id == -1) {
     //show chart for all courses
           $qry1 = "SELECT id FROM course";
           $res1 = db_query($qry1);

           $point = array();
           while ($row1 = mysql_fetch_assoc($res1)) {
                $cid = $row1['id'];
                $query = "SELECT ".$date_what." SUM(hits) AS cnt FROM actions_daily
                        WHERE course_id = $cid AND $date_where $date_group
                        ORDER BY day ASC";
                $result = db_query($query);

                switch ($u_interval) {
                    case "summary":
                        while ($row = mysql_fetch_assoc($result)) {
                               if (array_key_exists($langSummary, $point)) {
                                $point[$langSummary] += $row['cnt'];
                              } else {
                                 $point[$langSummary] = $row['cnt'];
                              }
                        }
                    break;
                    case "daily":
                        while ($row = mysql_fetch_assoc($result)) {
                            if (array_key_exists($row['date'], $point)) {
                                $point[$row['date']] += $row['cnt'];
                              } else {
                                 $point[$row['date']] = $row['cnt'];
                              }
                        }
                    break;
                    case "weekly":
                        while ($row = mysql_fetch_assoc($result)) {
                            $week = $row['week_start'].' - '.$row['week_end'];
                            if (array_key_exists($week, $point)) {
                                $point[$week] += $row['cnt'];
                              } else {
                                 $point[$week] = $row['cnt'];
                              }
                        }
                    break;
                    case "monthly":
                        while ($row = mysql_fetch_assoc($result)) {
                            $month = $langMonths[$row['month']];
                            if (array_key_exists($month, $point)) {
                                $point[$month] += $row['cnt'];
                              } else {
                                 $point[$month] = $row['cnt'];
                              }
                        }
                    break;
                    case "yearly":
                        while ($row = mysql_fetch_assoc($result)) {
                            $year = $row['year'];
                            if (array_key_exists($year, $point)) {
                                $point[$year] += $row['cnt'];
                            } else {
                                $point[$year] = $row['cnt'];
                            }
                        }
                    break;
            }
            if ($result !== false)
                mysql_free_result($result);
        }

        if ($u_interval != "monthly") {
            ksort($point);
        }

        $chart = new VerticalBarChart();
        $dataSet = new XYDataSet();
        //add points to chart
        while ($newp = current($point)){
                $dataSet->addPoint(new Point(key($point), $newp));
                $chart->width += 25;
                $chart->setDataSet($dataSet);
                next($point);
                $chart_content=1;
        }
        $chart->setTitle($langVisits);
        if ($res1 !== false)
            mysql_free_result($res1);

} else {    //show chart for a specific course
        $cid = course_code_to_id($u_course_id);
        $query = "SELECT ".$date_what." SUM(hits) AS cnt FROM actions_daily
                WHERE course_id = $cid AND $date_where $date_group ORDER BY day ASC";
        $result = db_query($query);

        $chart = new VerticalBarChart();
        $dataSet = new XYDataSet();

        switch ($u_interval) {
            case "summary":
                while ($row = mysql_fetch_assoc($result)) {
                        $dataSet->addPoint(new Point($langSummary, $row['cnt']));
                        $chart->width += 25;
                        $chart->setDataSet($dataSet);
                        $chart_content=1;
                }
            break;
            case "daily":
                while ($row = mysql_fetch_assoc($result)) {
                        $dataSet->addPoint(new Point($row['date'], $row['cnt']));
                        $chart->width += 25;
                        $chart->setDataSet($dataSet);
                        $chart_content=1;
                }
            break;
            case "weekly":
                while ($row = mysql_fetch_assoc($result)) {
                        $dataSet->addPoint(new Point($row['week_start'].' - '.$row['week_end'], $row['cnt']));
                        $chart->width += 25;
                        $chart->setDataSet($dataSet);
                        $chart_content=1;
                }
            break;
            case "monthly":
                while ($row = mysql_fetch_assoc($result)) {
                        $dataSet->addPoint(new Point($langMonths[$row['month']], $row['cnt']));
                        $chart->width += 25;
                        $chart->setDataSet($dataSet);
                        $chart_content=1;
                }
            break;
            case "yearly":
                while ($row = mysql_fetch_assoc($result)) {
                        $dataSet->addPoint(new Point($row['year'], $row['cnt']));
                        $chart->width += 25;
                        $chart->setDataSet($dataSet);
                        $chart_content=1;
                }
            break;
        }
        $chart->setTitle($langVisits);
        if ($result !== false)
            mysql_free_result($result);
    }

    if (!file_exists('courses/temp')) {
        mkdir('courses/temp', 0777);
    }
    //check if there are statistics to show
    if ($chart_content) {
        $chart_path = 'courses/temp/chart_'.md5(serialize($chart)).'.png';
        $chart->render($webDir.'/'.$chart_path);
        $tool_content .= '
        <table class="FormData" width="99%" align="left">
        <tbody>
        <tr>
        <td valign="top"><img src="'.$urlServer.$chart_path.'" /></td>
        </tr>
        </tbody>
        </table>';
    } else {
        $tool_content .= "<div class='alert1'>$langNoStatistics</div>";
     }
$tool_content .= '<br />';

/*************************************************************************
   Form for determining time period, time interval and course
***************************************************************************/

    //calendar for determining start and end date
    $start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => '',
                 'name'        => 'u_date_start',
                 'value'       => $u_date_start));

    $end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => '',
                 'name'        => 'u_date_end',
                 'value'       => $u_date_end));

    //possible courses
    $qry = "SELECT LEFT(title, 1) AS first_letter FROM course
            GROUP BY first_letter ORDER BY first_letter";
    $result = db_query($qry, $mysqlMainDb);
    $letterlinks='';
    while ($row = mysql_fetch_assoc($result)) {
        $first_letter = $row['first_letter'];
        $letterlinks .= '<a href="?first='.$first_letter.'">'.$first_letter.'</a> ';
    }

    if (isset($_GET['first'])) {
            $firstletter = $_GET['first'];
            $qry = "SELECT code, title FROM course
                           WHERE LEFT(title,1) = '".mysql_real_escape_string($firstletter)."'";
    } else {
            $qry = "SELECT code, title FROM course";
    }

    $cours_opts = '<option value="-1">'.$langAllCourses."</option>\n";
    $result = db_query($qry, $mysqlMainDb);
    while ($row = mysql_fetch_assoc($result)) {
        if ($u_course_id == $row['code']) {
                $selected = 'selected';
        } else {
                $selected = '';
        }
        $cours_opts .= '<option '.$selected.' value="'.$row["code"].'">'.$row['title']."</option>\n";
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
draw($tool_content, 3, null, $local_head);
