<?php

/*
===========================================================================
    admin/usersCourseStats.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description: Shows chart with the number of users per course.

==============================================================================
*/


$langFiles 				= array('usage', 'admin');
$require_help 			= true;
$helpTopic 				= 'Usage';

include '../../include/baseTheme.php';

@include "check_admin.inc";
// Define $nameTools
$nameTools = $langUsersCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$tool_content = '';
$tool_content .=  "<a href='statClaro.php'>".$langPlatformGenStats."</a> <br> ".
                "<a href='platformStats.php'>".$langVisitsStats."</a> <br> ".
             "<a href='usersCourseStats.php'>".$langUsersCourse."</a> <br> ".
             "<a href='visitsCourseStats.php'>".$langVisitsCourseStats."</a> <br> ".
              "<a href='oldStats.php'>".$langOldStats."</a> <br> ".
               "<a href='monthlyReport.php'>".$langMonthlyReport."</a>".
          "<p>&nbsp</p>";

$tool_content .= "<p> </p>";



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
        
        $query = "SELECT cours.intitule AS name, count(user_id) AS cnt FROM cours_user LEFT JOIN cours ON ".
            " cours.code = cours_user.code_cours GROUP BY code_cours";
   
        $result = db_query($query, $mysqlMainDb);
   
        $chart = new VerticalChart(200, 300);
   
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

if ($made_chart) {
		while (ob_get_level() > 0) {
  	   ob_end_flush();
  	}
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}

?>
