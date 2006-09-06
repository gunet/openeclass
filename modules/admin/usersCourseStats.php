<?php

/*
===========================================================================
    admin/usersCourseStats.php
    @last update: 
    @authors list: 
==============================================================================
    @Description: 

==============================================================================
*/


$langFiles 				= array('usage', 'admin');
$require_help 			= true;
$helpTopic 				= 'Usage';

include '../../include/baseTheme.php';

@include "check_admin.inc";
// Define $nameTools
$nameTools = $langPlatformStats;  ###ti 'n' auto;

$tool_content = '';
$tool_content .= "<a href='platformStats.php'>".$langPlatformStats."</a> | ".
             "<a href='usersCourseStats.php'>".$langUsersCourse."</a> | ".
             "<a href='visitsCourseStats.php'>".$langVisitsCourseStats."</a> | ".
              "<a href='oldStats.php'>".$langOldStats."</a>".
          "<p>&nbsp</p>";

$tool_content .= "<p> </p>";

$dateNow = date("d-m-Y / H:i:s",time());
$nameTools = $langUsage;
$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';


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
    ob_end_flush();
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}

?>
