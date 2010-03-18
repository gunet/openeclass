<?
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


/**===========================================================================
	userstats.php
	@last update: 04-09-2006 by Ophelia Neofytou
	@authors list:
==============================================================================
        @Description: user statistics

==============================================================================
*/

// BASETHEME, OTHER INCLUDES AND NAMETOOLS
$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';
include '../auth/auth.inc.php';
$nameTools = $langVersion;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);

// Initialise $tool_content
$tool_content = "";
$nameTools = $langUserStats;

$u = (string)isset($_GET['u'])?$_GET['u']:(isset($_POST['u'])?$_POST['u']:'');

if((!empty($u)) && ctype_digit($u))	// validate the user id
{
	$u = (int)$u;
	$sql = mysql_query("SELECT nom, prenom, username, password, email, phone, department, registered_at, expires_at FROM user WHERE user_id = '$u'");
	$info = mysql_fetch_array($sql);
    	$tool_content .= "<table class=\"FormData\" width=\"99%\" align=\"left\">
	<thead>
	<tr><th width=\"220\">&nbsp;</th>
	<td><b>$langUserStats</b>: $info[2]</td>
	</tr>";

	$sql = mysql_query("SELECT nom, prenom, username FROM user WHERE user_id = '$u'");
	$sql = mysql_query("SELECT a.code, a.intitule, b.statut, a.cours_id
		FROM cours AS a LEFT JOIN cours_user AS b ON a.cours_id = b.cours_id
		WHERE b.user_id = '$u' ORDER BY b.statut, a.faculte");

		// αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα
	if (mysql_num_rows($sql) > 0) {
    		$tool_content .= "<tr><th class=\"left\" valign=\"top\">$langStudentParticipation</th>
		<td>
		<table class=\"FormData\" width=\"99%\" align=\"left\">
		<thead><tr>
		<th colspan=\"2\"><div align=\"left\">&nbsp;&nbsp;$langCourseCode</div></th>
		<th>$langProperty</th>
		<th>$langActions</th>
		</tr></thead><tbody>";
		$k = 0;
		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
			$logs = mysql_fetch_array($sql);
			if ($k%2==0) {
		              $tool_content .= "<tr>";
	                } else {
		                $tool_content .= "<tr class=\"odd\">";
	                }
			$tool_content .= "<td width=\"1\">
			<img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>
         		<td align=\"left\">".htmlspecialchars($logs[0])." (".htmlspecialchars($logs[1]).")</td>
         		<td><div align=\"center\">";
			switch ($logs[2]) {
				case 1:
					$tool_content .= $langTeacher;
					$tool_content .= "</div></td><td><div align=\"center\">---</div></td>
      					</tr>";
					break;
				case 5:
					$tool_content .= $langStudent;
					$tool_content .= "</div></td><td><div align=\"center\">
					<a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">
					<img src='../../images/delete.gif' title='$langDelete' border='0'></img></a></div></td></tr>";
					break;
				default:
					$tool_content .= $langVisitor;
					$tool_content .= "</div></td><td><div align=\"center\">
					<a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">
					<img src='../../images/delete.gif' title='$langDelete' border='0'>
					</img></a></div></td></tr>";
					break;
				}
				$k++;
			}
		  $tool_content .= "
      </tbody>
      </table>\n";
		  $tool_content .= "\n    </td>\n  </tr>";

		} else {
		$tool_content .= "<tr><th class=\"left\">$langStudentParticipation</th><td>";

		$tool_content .= "$langNoStudentParticipation ";
		  if ($u > 1) {
				if (isset($logs))
			    $tool_content .= "<center><a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">$langDelete</a></center>";
				else
			    $tool_content .= "<center><a href=\"unreguser.php?u=$u&un=$info[2]&c=\">$langDelete</a></center>";
		  }
		  else
		  {
			$tool_content .= $langCannotDeleteAdmin;
		  }
    		$tool_content .= "</td></tr>";
		}
	
    $tool_content .= "
  <tr>
    <th class=\"left\" width=\"220\">$langTotalVisits:</th>
    <td>";
// Chart display added - haniotak
if (!extension_loaded('gd')) {
	$tool_content .= "$langGDRequired";
} else {
	$totalHits = 0;
	require_once '../../include/libchart/libchart.php';
	$sql = "SELECT a.code, a.intitule, b.statut, a.cours_id
			FROM cours AS a LEFT JOIN cours_user AS b ON a.cours_id = b.cours_id
			WHERE b.user_id = '$u' ORDER BY b.statut, a.faculte";
	$result = db_query($sql);
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
	$tool_content .= "<b>$totalHits</b></td>";
    	$tool_content .= "</tr><tr><td colspan=\"2\">";
	$chart = new PieChart(600, 300);
	$hits = array();
	foreach ($hits as $code => $count) {
		$chart_content=1;
		$chart->width += 1;
		$chart->addPoint(new Point($course_names[$code], $count));

	}
	$chart->setTitle($langCourseVisits);
	$chart_path = 'courses/chart_'.md5(serialize($chart)).'.png';
	$chart->render($webDir.$chart_path);
	$tool_content .= '<img src="'.$urlServer.$chart_path.'" />';
	$made_chart = true;
}
    $tool_content .= "
    </td>
  </tr>";

// End of chart display; chart unlinked at end of script.


$sql = "SELECT * FROM loginout WHERE id_user = '$u' ORDER by idLog DESC LIMIT 15";

$leResultat = db_query($sql, $mysqlMainDb);
    $tool_content .= "
  <tr>
    <th class=\"left\" width=\"220\" valign=\"top\">$langLastUserVisits $info[2]</th>
    <td>\n";
$tool_content .= "
      <table width=\"99%\">
      <thead>
      <tr>
        <th colspan=\"2\"><div align=\"left\">&nbsp;&nbsp;$langDate</div></th>
        <th>$langAction</th>
      </tr>
      </thead>
      <tbody>";
$i = 0;

$nomAction["LOGIN"] = "<font color=\"#008000\">$langLogIn</font>";
$nomAction["LOGOUT"] = "<font color=\"#FF0000\">$langLogout</font>";
$i=0;
while ($leRecord = mysql_fetch_array($leResultat)) {
	$when = $leRecord["when"];
	$action = $leRecord["action"];
	if ($i%2==0) {
		$tool_content .= "<tr>";
	} else {
		$tool_content .= "
      		<tr class=\"odd\">";
	}
	$tool_content .= "<td width=\"1\">
	<img style='border:0px; padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>
        <td>".strftime("%d/%m/%Y (%H:%M:%S) ", strtotime($when))."</td>
        <td align=\"center\"><div align=\"center\">".$nomAction[$action]."</div></td>
      </tr>";
	$i++;
}
$tool_content .= "
      </tbody>
      </table>\n";

$tool_content .= "
    </td>
  </tr>
  </thead>
  </table>";
}
else
{
    // Αλλιώς... τι γίνεται;
    $tool_content .= "<h1>$langError</h1>\n<p align=\"right\"><a href=\"listcours.php\">$langBack</p>\n";
}

$tool_content .= "<p align=\"right\"><a href=\"listusers.php\">$langBack</a></p>";
draw($tool_content,3);

// Unlink chart file - haniotak
if ($made_chart) {
//	ob_flush();
	flush();
	sleep(5);
	unlink($webDir.$chart_path);
}

?>


