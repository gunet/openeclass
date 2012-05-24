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
    admin/usersCourseStats.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description: Shows chart with the number of users per course.

==============================================================================
*/

$require_admin = TRUE;
$require_help = true;
$helpTopic = 'Usage';

include '../../include/baseTheme.php';

// Define $nameTools
$nameTools = $langUsersCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$tool_content .= "
  <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li><a href='stateclass.php'>".$langPlatformGenStats."</a></li>
      <li><a href='platformStats.php?first='>".$langVisitsStats."</a></li>
      <li><a href='usersCourseStats.php'>".$langUsersCourse."</a></li>
      <li><a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a></li>
      <li><a href='oldStats.php'>".$langOldStats."</a></li>
      <li><a href='monthlyReport.php'>".$langMonthlyReport."</a>></li>
    </ul>
  </div>";


include('../../include/jscalendar/calendar.php');
if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

   if (!extension_loaded('gd')) {
        $tool_content .= "<p>$langGDRequired</p>";
    } else {
        $made_chart = true;

        //make chart
        require_once '../../include/libchart/libchart.php';
        $query = "SELECT course.title AS name, COUNT(user_id) AS cnt FROM course_user LEFT JOIN course ON ".
            " course.id = course_user.course_id GROUP BY course.id";

        $result = db_query($query);
        $chart = new VerticalBarChart(200, 300);
        while ($row = mysql_fetch_assoc($result)) {
              $chart->addPoint(new Point($row['name'], $row['cnt']));
              $chart->width += 25;
        }
       $chart->setTitle("$langUsersCourse");

       mysql_free_result($result);
       $chart_path = 'temp/chart_'.md5(serialize($chart)).'.png';

        $chart->render($webDir.$chart_path);

        $tool_content .= '<img src="'.$urlServer.$chart_path.'" />';

    }

draw($tool_content, 3, 'admin');
