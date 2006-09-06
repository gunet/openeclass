<?php
/*****************************************************************************
        DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = array('usage', 'admin');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
@include "check_admin.inc";
// Define $nameTools
$nameTools = $langPlatformStats;
// Initialise $tool_content
$tool_content = "";

$tool_content .= "<a href='platformStats.php'>".$langPlatformStats."</a> | ".
             "<a href='usersCourseStats.php'>".$langUsersCourse."</a> | ".
             "<a href='visitsCourseStats.php'>".$langVisitsCourseStats."</a> | ".
             "<a href='oldStats.php'>".$langOldStats."</a>".
          "<p>&nbsp</p>";



require_once "summarizeLogins.php";

$dateNow = date("d-m-Y / H:i:s",time());

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

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang, 'calendar-win2k-2', false);
$local_head = $jscalendar->get_load_files_code();

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
        
        $tool_content .= "<p> $langOldStatsExpl $min_w. </p>";

        //make chart
        require_once '../../include/libchart/libchart.php';
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


    //make form
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


    ob_end_flush();
    ob_flush();
    flush();
    sleep(5);
    unlink ($webDir.$chart_path);
}






#draw($tool_content,3,'admin');

?>
