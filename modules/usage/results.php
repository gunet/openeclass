<?php
/*
 * Created on 1 Ιουν 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once '../../include/libchart/libchart.php';
$usage_defaults = array (
    'u_stats_type' => 'visits',
    'u_interval' => 'daily',
    'u_user_id' => -1,
    'u_module_id' => -1,
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
switch ($u_interval) {
    case "summary":
        $date_group = '';
    break;
    case "daily":
        $date_what .= ", DATE_FORMAT(date_time, '$date_fmt') AS date ";
        $date_group = " DATE(date_time) ";
    break;
    case "weekly":
        $date_what .= ", DATE_FORMAT(date_time - INTERVAL WEEKDAY(date_time) DAY, '$date_fmt') AS week_start ".
                      ", DATE_FORMAT(date_time + INTERVAL (6 - WEEKDAY(date_time)) DAY, '$date_fmt') AS week_end ";
        $date_group = " WEEK(date_time)";
    break;
    case "monthly":
        $date_what .= ", MONTH(date_time) AS month ";
        $date_group = " MONTH(date_time)";
    break;
    case "yearly":
        $date_what .= ", YEAR(date_time) AS year ";
        $date_group = " YEAR(date_time) ";
    break;
}
if ($u_user_id != -1) {
    $user_where = " (user_id = '$u_user_id') ";
} else {
    $user_where = " (1) ";
}

if ($u_module_id != -1) {
    $mod_where = " (module_id = '$u_module_id') ";
} else {
    $mod_where = " (1) ";
}

switch ($u_stats_type) {
    case "visits":
    $query = "SELECT ".$date_what.", COUNT(*) AS cnt FROM actions WHERE $date_where AND $user_where AND $mod_where GROUP BY ".$date_group." ORDER BY date_time ASC";
    $result = db_query($query, $currentCourseID);
    $chart = new VerticalChart(200, 300);
    switch ($u_interval) {
        case "summary":
            while ($row = mysql_fetch_assoc($result)) {
                $chart->addPoint(new Point("Summary", $row['cnt']));
                $chart->width += 25;
            }
        break;
        case "daily":
            while ($row = mysql_fetch_assoc($result)) {
                $chart->addPoint(new Point($row['date'], $row['cnt']));
                $chart->width += 25;
            }
        break;
        case "weekly":
            while ($row = mysql_fetch_assoc($result)) {
                $chart->setLabelMarginBottom(110);
                $chart->setLabelMarginRight(80);
                $chart->addPoint(new Point($row['week_start'].' - '.$row['week_end'], $row['cnt']));
                $chart->width += 25;
            }
        break;
        case "monthly":
            while ($row = mysql_fetch_assoc($result)) {
                $chart->addPoint(new Point($langMonths[$row['month']], $row['cnt']));
                $chart->width += 25;
            }
        break;
        case "yearly":
            while ($row = mysql_fetch_assoc($result)) {
                $chart->addPoint(new Point($row['year'], $row['cnt']));
                $chart->width += 25;
            }
        break;
    }
    $chart->setTitle("Visits");

    break;
    case "favourite":
        $query = "SELECT ".$date_what.", accueil.rubrique AS name, COUNT(*) AS cnt FROM actions LEFT JOIN accueil ON actions.module_id = accueil.id WHERE $date_where AND $user_where AND $mod_where GROUP BY module_id";
        $result = db_query($query, $currentCourseID);
        $chart = new PieChart(500, 300);
        while ($row = mysql_fetch_assoc($result)) {
            $chart->addPoint(new Point($row['name'], $row['cnt']));
        }
    $chart->setTitle("Favourite Modules");
    break;
}
mysql_free_result($result);
$chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';
//$tool_content .= $query."<br />";
$chart->render($webDir.$chart_path);

$tool_content .= '<img src="'.$urlServer.$chart_path.'" />';
?>
