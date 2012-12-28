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

$require_help = TRUE;
$helpTopic = 'PersonalStats';
include '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

$require_valid_uid = TRUE;

check_uid();
$nameTools = $langPersonalStats;
check_guest();

// Chart display added - haniotak
if (!extension_loaded('gd')) {
	$tool_content .= $langGDRequired;
} else {
	$totalHits = 0;
        $totalDuration = 0;
	require_once 'include/libchart/classes/libchart.php';

        $sql = "SELECT a.code code, a.title title
                FROM course AS a LEFT JOIN course_user AS b
                     ON a.id = b.course_id
                WHERE b.user_id = $uid
                AND a.visible != ".COURSE_INACTIVE."
                ORDER BY a.title";
	$result = db_query($sql);
	if (mysql_num_rows($result) > 0) {  // found courses ?
		while ($row = mysql_fetch_assoc($result)) {
			$course_codes[] = $row['code'];
			$course_names[$row['code']]=$row['title'];
		}
		mysql_free_result($result);
		foreach ($course_codes as $code) {
                        $cid = course_code_to_id($code);
			$sql = "SELECT SUM(hits) AS cnt FROM actions_daily
                                WHERE user_id = $uid
                                AND course_id = $cid";
			$result = db_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$totalHits += $row['cnt'];
				$hits[$code] = $row['cnt'];
			}
			mysql_free_result($result);
			$sql = "SELECT SUM(duration) FROM actions_daily
                                        WHERE user_id = $uid
                                        AND course_id = $cid";
			$result = db_query($sql);
			list($duration[$code]) = mysql_fetch_row($result);
                        $totalDuration += $duration[$code];
			mysql_free_result($result);
		}

		$chart = new PieChart(600, 300);
		$dataSet = new XYDataSet();
		$chart_content = 0;
		foreach ($hits as $code => $count) {
			if ($count >0 ){
				$chart_content=5;
				$dataSet->addPoint(new Point($course_names[$code], $count));
				$chart->width += 7;
				$chart->setDataSet($dataSet);
			}
		}
		$chart->setTitle($langCourseVisits);
		if (!file_exists("courses/temp")) {
			mkdir("courses/temp", 0777);
		}
		$chart_path = '/courses/temp/chart_'.md5(serialize($chart)).'.png';
		if ($chart_content) {
			$chart->render($webDir.$chart_path);
			$tool_content .= '<p><img src="'.$urlServer.$chart_path.'" /></p>';
		}
		$made_chart = true;

                $totalDuration = format_time_duration(0 + $totalDuration);
                $tool_content .= "
                <fieldset>
                <legend>$langPlatformGenStats</legend>
                <table width='100%'>
                <tr>
                <th>$langTotalVisitsCourses:</th>
                <td>$totalHits</td>
                </tr>
                <tr>
                <th>$langDurationVisits:</th>
                <td>$totalDuration</td>
                </tr>
                <tr>
                <th valign='top'>$langDurationVisitsPerCourse:</th>
                <td>
                <table class='tbl_alt' width='550'>
                <tr>
                <th colspan='2'>$langCourseTitle</th>
                <th width='160'>$langDuration</th>
                </tr>";
                $i = 0;
                foreach ($duration as $code => $time) {
                        if ($i%2 == 0) {
                                $tool_content .= "<tr class='even'>";
                        } else {
                                $tool_content .= "<tr class='odd'>";
                        }
                        $i++;
                        $tool_content .= "
                        <td width='16'><img src='$themeimg/arrow.png' alt=''></td>
                        <td>" . q(course_code_to_title($code)) . "</td>
                        <td width='140'>" . format_time_duration(0 + $time) . "</td>
                        </tr>";
                }
                $tool_content .= "</table></td></tr>";
	}
}
// End of chart display; chart unlinked at end of script.

$sql = "SELECT * FROM loginout
    WHERE id_user = '".$_SESSION["uid"]."' ORDER by idLog DESC LIMIT 10";

$leResultat = db_query($sql);
$tool_content .= "<tr><th valign='top'>$langLastVisits:</th><td>";
$tool_content .= "<table class='tbl_alt' width='550'>
            <tr>
              <th colspan='2'>$langDate</th>
              <th width='140'>$langAction</th>
            </tr>";
$nomAction["LOGIN"] = "<font color='#008000'>$langLogIn</font>";
$nomAction["LOGOUT"] = "<font color='#FF0000'>$langLogout</font>";
$i=0;
while ($leRecord = mysql_fetch_array($leResultat)) {
        $when = $leRecord["when"];
        $action = $leRecord["action"];
        if ($i%2 == 0) {
                $tool_content .= "<tr class='even'>";
        } else {
                $tool_content .= "<tr class='odd'>";
        }
        $tool_content .= "
        <td width='16'><img src='$themeimg/arrow.png' alt=''></td>
        <td>".strftime("%d/%m/%Y (%H:%M:%S) ", strtotime($when))."</td>
        <td>".$nomAction[$action]."</td>
        </tr>";
        $i++;
}
$tool_content .= "</table>";
$tool_content .= "</td></tr></table></fieldset>";

draw($tool_content, 1);