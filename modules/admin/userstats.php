<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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



/**===========================================================================
	userstats.php
	@last update: 04-09-2006 by Ophelia Neofytou
	@authors list:
==============================================================================
        @Description: user statistics

==============================================================================
*/

// BASETHEME, OTHER INCLUDES AND NAMETOOLS
$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
require_once '../../include/libchart/libchart.php';
$nameTools = $langVersion;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);

$nameTools = $langUserStats;

$u = isset($_REQUEST['u'])?intval($_REQUEST['u']):'';

if(!empty($u))	
{
	$sql = db_query("SELECT nom, prenom, username FROM user WHERE user_id = $u");
	$info = mysql_fetch_array($sql);
    	$tool_content .= "<p class='title1'>$langUserStats: <b>$info[2]</b></p>
		<p><b>$langStudentParticipation</b></p>";
        $sql = db_query("SELECT a.code, a.intitule, b.statut, a.cours_id
                                FROM cours AS a JOIN faculte ON a.faculteid = faculte.id
                                     LEFT JOIN cours_user AS b ON a.cours_id = b.cours_id
                                WHERE b.user_id = $u
                                ORDER BY b.statut, faculte.name");

	// αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα
	if (mysql_num_rows($sql) > 0) {
    		$tool_content .= "
		<table class='tbl_alt' width='99%' align='left'>
		<tr>
		  <th colspan='2'><div align='left'>&nbsp;&nbsp;$langCourseCode</div></th>
		  <th>$langProperty</th>
		</tr>";
		$k = 0;
		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
			$logs = mysql_fetch_array($sql);
			if ($k%2 == 0) {
		              $tool_content .= "<tr class=\"even\">";
	                } else {
		                $tool_content .= "<tr class=\"odd\">";
	                }
			$tool_content .= "<td class='bullet' width='1'></td>
				<td align='left'>".htmlspecialchars($logs[0])." (".htmlspecialchars($logs[1]).")</td>
				<td><div align='center'>";
			switch ($logs[2]) {
				case 1:
					$tool_content .= $langTeacher;
					break;
				case 5:
					$tool_content .= $langStudent;
					break;
				default:
					$tool_content .= $langVisitor;
					break;
				}
				$k++;
			}
			$tool_content .= "</div></td></tr></table>\n";
		} else {
			$tool_content .= "<p>$langNoStudentParticipation </p>";	
		}
	$tool_content .= "<p><b>$langTotalVisits</b>: ";
	// Chart display added - haniotak
	if (!extension_loaded('gd')) {
		$tool_content .= "$langGDRequired";
	} else {
		$totalHits = 0;
                $result = db_query("SELECT a.code, a.intitule, b.statut, a.cours_id
                                           FROM cours AS a JOIN faculte ON a.faculteid = faculte.id
                                                LEFT JOIN cours_user AS b ON a.cours_id = b.cours_id
                                           WHERE b.user_id = $u
                                           ORDER BY b.statut, faculte.name");
		$hits = array();
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$course_codes[] = $row['code'];
				$course_names[$row['code']]=$row['intitule'];
			}
			mysql_free_result($result);
			foreach ($course_codes as $course_code) {
				$sql = "SELECT COUNT(*) AS cnt FROM actions WHERE user_id = '$u'";
				$result = db_query($sql, $course_code);
				while ($row = mysql_fetch_assoc($result)) {
					$totalHits += $row['cnt'];
					$hits[$course_code] = $row['cnt'];
				}
				mysql_free_result($result);
			}
		}
		$tool_content .= "<b>$totalHits</b></p>";
		$chart = new PieChart(600, 300);
		$dataSet = new XYDataSet();
		foreach ($hits as $code => $count) {
			$chart_content=1;
			$chart->width += 1;
			$dataSet->addPoint(new Point($course_names[$code], $count));
		}
		$chart->setDataSet($dataSet);
		$chart->setTitle($langCourseVisits);
		$chart_path = 'courses/chart_'.md5(serialize($chart)).'.png';
		$chart->render($webDir.$chart_path);
		$tool_content .= '<p><img src="'.$urlServer.$chart_path.'" /></p>';
		$made_chart = true;
	}
	// End of chart display; chart unlinked at end of script.
	$sql = "SELECT * FROM loginout WHERE id_user = '$u' ORDER by idLog DESC LIMIT 15";
	
	$leResultat = db_query($sql, $mysqlMainDb);
	$tool_content .= "<p>$langLastUserVisits $info[2]</p>\n";
	$tool_content .= "
	      <table class='tbl_alt' width=\"99%\">
	      <tr>
		<th colspan=\"2\"><div align=\"left\">&nbsp;&nbsp;$langDate</div></th>
		<th>$langAction</th>
	      </tr>";
	$i = 0;
	$nomAction["LOGIN"] = "<font color=\"#008000\">$langLogIn</font>";
	$nomAction["LOGOUT"] = "<font color=\"#FF0000\">$langLogout</font>";
	$i=0;
	while ($leRecord = mysql_fetch_array($leResultat)) {
		$when = $leRecord["when"];
		$action = $leRecord["action"];
		if ($i%2 == 0) {
			$tool_content .= "<tr>";
		} else {
			$tool_content .= "
			<tr class=\"odd\">";
		}
		$tool_content .= "<td class='bullet' width='1'></td>
			<td>".strftime("%d/%m/%Y (%H:%M:%S) ", strtotime($when))."</td>
			<td align=\"center\"><div align=\"center\">".$nomAction[$action]."</div></td>
		      </tr>";
		$i++;
	}
	$tool_content .= "</table>\n";
} else {
    // Αλλιώς... τι γίνεται;
    $tool_content .= "<p class='caution'>$langError</p>\n<p align='right'>
	<a href='listcours.php'>$langBack</p>\n";
}

$tool_content .= "<p align='right'><a href='listusers.php'>$langBack</a></p>";
draw($tool_content,3);
?>
