<?php
/*
 *
 */

require_once '../../include/libchart/libchart.php';
$usage_defaults = array (
    'u_stats_type' => 'visits',
    'u_interval' => 'daily',
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
$date_where = " (`when` BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";
$date_what  = "DATE_FORMAT(MIN(`when`), '$date_fmt') AS date_start, DATE_FORMAT(MAX(`when`), '$date_fmt') AS date_end ";


switch ($u_interval) {
    case "summary":
        $date_what = '';
        $date_group = '';
    break;
    case "daily":
        $date_what .= ", DATE_FORMAT(`when`, '$date_fmt') AS date ,";
        $date_group = " GROUP BY DATE(`when`) ";
    break;
    case "weekly":
        $date_what .= ", DATE_FORMAT(`when` - INTERVAL WEEKDAY(`when`) DAY, '$date_fmt') AS week_start ".
                      ", DATE_FORMAT(`when` + INTERVAL (6 - WEEKDAY(`when`)) DAY, '$date_fmt') AS week_end ,";
        $date_group = " GROUP BY WEEK(`when`)";
    break;
    case "monthly":
        $date_what .= ", MONTH(`when`) AS month ,";
        $date_group = " GROUP BY MONTH(`when`)";
    break;
    case "yearly":
        $date_what .= ", YEAR(`when`) AS year ,";
        $date_group = "  GROUP BY YEAR(`when`) ";
    break;
}
if ($u_user_id != -1) {
    $user_where = " (id_user = '$u_user_id') ";
} else {
    $user_where = " (1) ";
}


switch ($u_stats_type) {
    case "visits":
    $query = "SELECT ".$date_what." COUNT(*) AS cnt FROM loginout WHERE $date_where AND $user_where AND action='LOGIN' $date_group ORDER BY `when` ASC";
    $result = db_query($query, $mysqlMainDb);
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
    $chart->setTitle($langVisits);

    break;


}
mysql_free_result($result);
$chart_path = 'temp/chart_'.md5(serialize($chart)).'.png';
//$tool_content .= $query."<br />";
$chart->render($webDir.$chart_path);

$tool_content .= '<img src="'.$urlServer.$chart_path.'" />';
$tool_content .= '<p> &nbsp; </p>';
?>
