<?
/*
=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
A full copyright notice can be read in "/info/copyright.txt".

Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
Yannis Exidaridis <jexi@noc.uoa.gr>
Alexandros Diamantidis <adia@noc.uoa.gr>

For a full list of contributors, see "credits.txt".

This program is a free software under the terms of the GNU
(General Public License) as published by the Free Software
Foundation. See the GNU License for more details.
The full license can be read in "license.txt".

Contact address: GUnet Asynchronous Teleteaching Group,
Network Operations Center, University of Athens,
Panepistimiopolis Ilissia, 15784, Athens, Greece
eMail: eclassadmin@gunet.gr
==============================================================================
*/

$langFiles = array('registration','usage');
$require_help = TRUE;
$helpTopic = 'Personal Statistics';
include '../../include/baseTheme.php';
include "../auth/auth.inc.php";
$require_valid_uid = TRUE;
$tool_content = "";

check_uid();

$nameTools = $langPersonalStats;

check_guest();


// Chart display added - haniotak
if (!extension_loaded('gd')) {
	$tool_content .= "$langGDRequired";
} else {
	$totalHits = 0;
	require_once '../../include/libchart/libchart.php';
	$sql = "SELECT code, intitule FROM cours";
	$result = db_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$course_codes[] = $row['code'];
		$course_names[$row['code']]=$row['intitule'];
	}
	mysql_free_result($result);
	foreach ($course_codes as $course_code) {
		$sql = "SELECT COUNT(*) AS cnt FROM actions WHERE user_id = '$uid'";
		$result = db_query($sql, $course_code);
		while ($row = mysql_fetch_assoc($result)) {
			$totalHits += $row['cnt'];
			$hits[$course_code] = $row['cnt'];
		}
		mysql_free_result($result);
	}
	$tool_content .= "<p>$langTotalVisitsCourses: $totalHits</p>";
	$chart = new PieChart(500, 300);
	$chart_content=0;
	foreach ($hits as $code => $count) {
        if ($count >0 ){
           $chart_content=1;
		   $chart->addPoint(new Point($course_names[$code], $count));
        }
    }
	$chart->setTitle($langCourseVisits);
	if (!file_exists("../../temp")) {
        mkdir("../../temp", 0777);
    }
	$chart_path = 'temp/chart_'.md5(serialize($chart)).'.png';
	$chart->render($webDir.$chart_path);
	if ($chart_content) {
	   $tool_content .= '<img src="'.$urlServer.$chart_path.'" />';
    }
    $made_chart = true;
}
// End of chart display; chart unlinked at end of script.



$sql = "SELECT * FROM loginout
    WHERE id_user = '".$_SESSION["uid"]."' ORDER by idLog DESC LIMIT 15";

$leResultat = db_query($sql, $mysqlMainDb);
$tool_content .= " <br>
    <table width=\"99%\">
        <thead>
            <tr>
                <th>$langLastVisits</th>
            </tr>
        </thead>
    </table>
    <br>

    <table width=\"99%\">
        <thead>
            <tr>
                <th>$langDate</th>
                <th>$langAction</th>
            </tr>
        </thead>
        <tbody>
            ";
$i = 0;
//$color[]=$color1;
//$color[]=$color2;

$nomAction["LOGIN"] = "<font color=\"#008000\">$langLogIn</font>";
$nomAction["LOGOUT"] = "<font color=\"#FF0000\">$langLogOut</font>";
$i=0;
while ($leRecord = mysql_fetch_array($leResultat)) {
	$when = $leRecord["when"];
	$action = $leRecord["action"];
	if ($i%2==0) {
		$tool_content .= "<tr>";
	} else {
		$tool_content .= "<tr class=\"odd\">";
	}
	$tool_content .= "
    <td>
        ".strftime("%Y-%m-%d %H:%M:%S ", strtotime($when))."
    </td>
    <td>".$nomAction[$action]."</td>
    </tr>";
	$i++;
}

$tool_content .= "</tbody></table>";
draw($tool_content, 1);

// Unlink chart file - haniotak
/*if ($made_chart) {
	while (ob_get_level() > 0) {
  	 ob_end_flush();
	}
	ob_flush();
	flush();
	sleep(5);
	unlink($webDir.$chart_path);
}
*/
?>
