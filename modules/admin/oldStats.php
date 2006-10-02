<?php
/*
===========================================================================
    admin/oldStats.php
    @last update: 23-09-2006
    @authors list: ophelia neofytou
==============================================================================
    @Description:  Shows statistics older than two months that concern the number of visits
        on the platform for a time period.
        Note: Information for old statistics is taken from table 'loginout_summary' where
        cummulative monthly data are stored.

==============================================================================
*/
// Set the langfiles needed
$langFiles = array('usage', 'admin');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
@include "check_admin.inc";
// Define $nameTools
$nameTools = $langOldStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";

$tool_content .=  "<a href='statClaro.php'>".$langPlatformGenStats."</a> <br> ".
                "<a href='platformStats.php'>".$langVisitsStats."</a> <br> ".
             "<a href='usersCourseStats.php'>".$langUsersCourse."</a> <br> ".
             "<a href='visitsCourseStats.php'>".$langVisitsCourseStats."</a> <br> ".
              "<a href='oldStats.php'>".$langOldStats."</a> <br> ".
               "<a href='monthlyReport.php'>".$langMonthlyReport."</a>".
          "<p>&nbsp</p>";


//move data from table 'loginout' to 'loginout_summary' if older than two months
require_once "summarizeLogins.php";



include('../../include/jscalendar/calendar.php');
if ($language == 'greek') {
    $lang = 'el';
} else if ($language == 'english') {
    $lang = 'en';
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-win2k-2', false);
$local_head = $jscalendar->get_load_files_code();



//$min_w is the min date in 'loginout'. Statistics older than $min_w will be shown.
$query = "SELECT MIN(`when`) as min_when FROM loginout";
$result = db_query($query, $mysqlMainDb);
while ($row = mysql_fetch_assoc($result)) {
    $min_when = strtotime($row['min_when']);
}
$min_w = date("d-m-Y", $min_when);



if (!extension_loaded('gd')) {
    $tool_content .= "<p>$langGDRequired</p>";
} else {
    $made_chart = true;

    $tool_content .= "<p> $langOldStatsLoginsExpl $min_w. </p>";

    /*****************************************
      start making chart
     *******************************************/
     require_once '../../include/libchart/libchart.php';

     //default values for chart
     $usage_defaults = array (
            'u_date_start' => strftime('%Y-%m-%d', strtotime('now -4 month')),
            'u_date_end' => strftime('%Y-%m-%d', strtotime('now -1 month')),
      );

     foreach ($usage_defaults as $key => $val) {
         if (!isset($_POST[$key])) {
             $$key = $val;
         } else {
             $$key = $_POST[$key];
         }
     }

    $date_fmt = '%Y-%m-%d';
    $date_where = " (start_date BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";

   
    $query = "SELECT MONTH(start_date) AS month, YEAR(start_date) AS year, SUM(login_sum) AS visits FROM loginout_summary ".
            " WHERE $date_where GROUP BY MONTH(start_date)";

    $result = db_query($query, $mysqlMainDb);

    $chart = new VerticalChart(200, 300);

    //add points to chart
    while ($row = mysql_fetch_assoc($result)) {
        $mont = $langMonths[$row['month']];
        $chart->addPoint(new Point($mont." - ".$row['year'], $row['visits']));
        $chart->width += 25;
    }

    $chart->setTitle("$langOldStats");


    mysql_free_result($result);
    $chart_path = 'temp/chart_'.md5(serialize($chart)).'.png';
   
    $chart->render($webDir.$chart_path);

    $tool_content .= '<img src="'.$urlServer.$chart_path.'" />';

    /********************************************************
       Start making the form for choosing start and end date
    ********************************************************/
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


    $tool_content .= '
    <form method="post">
    &nbsp;&nbsp;
        <table>

        <tr>
            <td>'.$langStartDate.'</td>
            <td>'."$start_cal".'</td>
        </tr>
        <tr>
            <td>'.$langEndDate.'</td>
            <td>'."$end_cal".'</td>
        </tr>
        
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="btnUsage" value="'.$langSubmit.'"></td>
        </tr>
        </table>
    </form>';

}


draw($tool_content, 3, 'admin', $local_head, '');


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
