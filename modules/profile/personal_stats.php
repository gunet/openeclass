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


$require_help = TRUE;
$helpTopic = 'Personal Statistics';
include '../../include/baseTheme.php';
include "../auth/auth.inc.php";
$require_valid_uid = TRUE;

check_uid();

$nameTools = $langPersonalStats;

check_guest();

// Chart display added - haniotak
if (!extension_loaded('gd')) {
	$tool_content .= "$langGDRequired";
} else {
	$totalHits = 0;
        $totalDuration = 0;
	require_once '../../include/libchart/libchart.php';
        $sql = "SELECT a.code code, a.intitule intitule
                FROM cours AS a LEFT JOIN cours_user AS b
                     ON a.cours_id = b.cours_id
                WHERE b.user_id = '$uid' ORDER BY a.intitule";
	$result = db_query($sql);
	if (mysql_num_rows($result) > 0) {  // found courses ?
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
			$sql = "SELECT SUM(duration) FROM actions WHERE user_id = '$uid'";
			$result = db_query($sql, $course_code);
			list($duration[$course_code]) = mysql_fetch_row($result);
                        $totalDuration += $duration[$course_code];
			mysql_free_result($result);
		}

		$chart = new PieChart(600, 300);
		$dataSet = new XYDataSet();
		$chart_content=0;
		foreach ($hits as $code => $count) {
			if ($count >0 ){
				$chart_content=5;
				$dataSet->addPoint(new Point($course_names[$code], $count));
				$chart->width += 7;
				$chart->setDataSet($dataSet);
			}
		}
		$chart->setTitle($langCourseVisits);
		if (!file_exists("../../courses/temp")) {
			mkdir("../../courses/temp", 0777);
		}
		$chart_path = 'courses/temp/chart_'.md5(serialize($chart)).'.png';
		$chart->render($webDir.$chart_path);
		if ($chart_content) {
			$tool_content .= '<p><img src="'.$urlServer.$chart_path.'" /></p>';
		}
		$made_chart = true;

    $totalDuration = format_time_duration(0 + $totalDuration);
    $tool_content .= "
	<p class='title1'>$langPlatformGenStats</p>
	<table class='tbl' width='99%'>
	<tr>
	  <td width='250'>$langTotalVisitsCourses:</td>
	  <td class='bold'>$totalHits</td>
	</tr>
	<tr>
	  <td>$langDurationVisits:</td>
	  <td class='bold'>$totalDuration</td>
	</tr>
	<tr>
	  <td valign='top'>$langDurationVisitsPerCourse:</td>
	  <td>
            <table class='tbl_alt' width='400'>
            <tr>
              <th>&nbsp;</th><th>$langCourseTitle</th>
              <th>$langDuration</th>
            </tr>";
                $i = 0;
                foreach ($duration as $code => $time) {
                        if ($i%2==0) {
                                $tool_content .= "\n<tr class='even'>";
                        } else {
                                $tool_content .= "\n<tr class='odd'>";
                        }
                        $i++;
                        $tool_content .= "
			<td width='1'><img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.png' alt='' /></td>
			<td>" . course_code_to_title($code) . "</td>
			<td class='center'>" . format_time_duration(0 + $time) . "</td>
		     </tr>";
                }
        $tool_content .= "\n</table>
	</td>
	</tr>
	</table>";
	}
}
// End of chart display; chart unlinked at end of script.


$sql = "SELECT * FROM loginout
    WHERE id_user = '".$_SESSION["uid"]."' ORDER by idLog DESC LIMIT 10";

$leResultat = db_query($sql, $mysqlMainDb);

    $tool_content .= "<table class=\"tbl\" width=\"99%\">
	<tr>
    <td width=\"250\" valign=\"top\">$langLastVisits:</td>
    <td>";

    $tool_content .= "
    <table class=\"tbl_alt\" width='400'>
    <tr>
        <th colspan=\"2\" class=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;$langDate</th>
        <th>$langAction</th>
    </tr>";
    $i = 0;

    $nomAction["LOGIN"] = "<div align=\"center\"><font color=\"#008000\">$langLogIn</font></div>";
    $nomAction["LOGOUT"] = "<div align=\"center\"><font color=\"#FF0000\">$langLogout</font></div>";
    $i=0;
    while ($leRecord = mysql_fetch_array($leResultat)) {
	   $when = $leRecord["when"];
	   $action = $leRecord["action"];
	   if ($i%2==0) {
		$tool_content .= "\n    <tr class=\"even\">";
	   } else {
		$tool_content .= "\n    <tr class=\"odd\">";
	   }
	   $tool_content .= "
        <td width=\"1\"><img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow.png' alt=''></td>
        <td>".strftime("%d/%m/%Y (%H:%M:%S) ", strtotime($when))."</td>
        <td>".$nomAction[$action]."</td>
	</tr>";
	$i++;
    }

$tool_content .= "\n    </tbody>\n    </table>\n";
$tool_content .= "</td></tr></thead></table>";
draw($tool_content, 1);