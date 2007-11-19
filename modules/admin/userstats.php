<?
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
==============================================================================*/

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
// Initialise $tool_content
$tool_content = "";

$nameTools = $langUserStats;

$u = (string)isset($_GET['u'])?$_GET['u']:(isset($_POST['u'])?$_POST['u']:'');

#$u_submitted = isset($_POST['u_submitted'])?$_POST['u_submitted']:'';

if((!empty($u)) && ctype_digit($u))	// validate the user id
{
	$u = (int)$u;
		$sql = mysql_query("
		SELECT nom, prenom, username, password, email, phone, department, registered_at, expires_at FROM user
		WHERE user_id = '$u'");
		if (!$sql) 
		{
	    die("Unable to query database (user_id='$u')!");
		}
		$info = mysql_fetch_array($sql);
    $tool_content .= "<h4>Στατιστικά χρήστη $info[2]</h4>";
				
		$sql = mysql_query("
			SELECT nom, prenom, username FROM user WHERE user_id = '$u'");
		if (!$sql) 
		{
		    die("Unable to query database (user_id='$u')!");
		}
		
		$sql = mysql_query("SELECT a.code, a.intitule, b.statut, a.cours_id
			FROM cours AS a LEFT JOIN cours_user AS b ON a.code = b.code_cours
			WHERE b.user_id = '$u' ORDER BY b.statut, a.faculte");

		// αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα 
		if (mysql_num_rows($sql) > 0) 
		{
			$tool_content .= "<h4>$langStudentParticipation</h4>\n".
			"<table border=\"1\">\n<tr><th>$langLessonCode</th><th>$langLessonName</th>".
			"<th>$langProperty</th><th>$langActions</th></tr>";
	
		  for ($j = 0; $j < mysql_num_rows($sql); $j++) 
		  {
				$logs = mysql_fetch_array($sql);
				$tool_content .= "<tr><td>".htmlspecialchars($logs[0])."</td><td>".
				htmlspecialchars($logs[1])."</td><td align=\"center\">";
				switch ($logs[2]) 
				{
					case 1:
						$tool_content .= $langTeacher;
						$tool_content .= "</td><td align=\"center\">---</td></tr>\n";
						break;
					case 5:
						$tool_content .= $langStudent;
						$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">".
						"$langDelete</a></td></tr>\n";
						break;
					default:
						$tool_content .= $langVisitor;
						$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">".
	                    "$langDelete</a></td></tr>\n";
					break;
				}
			}
		  $tool_content .= "</table>\n";
		} 
		else 
		{ 
			$tool_content .= "<h2>$langNoStudentParticipation</h2>";	
		  if ($u > 1) 
		  {
				if (isset($logs))
			    $tool_content .= "<center><a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">$langDelete</a></center>";
				else 
			    $tool_content .= "<center><a href=\"unreguser.php?u=$u&un=$info[2]&c=\">$langDelete</a></center>";
			} 
		  else 
		  {
				$tool_content .= $langCannotDeleteAdmin;
		  }
		}
#	}

// Chart display added - haniotak
if (!extension_loaded('gd')) {
	$tool_content .= "$langGDRequired";
} else {
	$totalHits = 0;
	require_once '../../include/libchart/libchart.php';
	$sql = "SELECT code FROM cours";
	$result = db_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$course_codes[] = $row['code'];
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
	$tool_content .= "&nbsp;<p>$langTotalVisits: $totalHits</p>";
	$chart = new PieChart(500, 300);
	foreach ($hits as $code => $count) {
		$chart->addPoint(new Point($code, $count));
	}
	$chart->setTitle($langCourseVisits);
	$chart_path = 'courses/chart_'.md5(serialize($chart)).'.png';
	$chart->render($webDir.$chart_path);
	$tool_content .= '<img src="'.$urlServer.$chart_path.'" />';
	$made_chart = true;
}
// End of chart display; chart unlinked at end of script.

$sql = "SELECT * FROM loginout WHERE id_user = '".$_SESSION["uid"]."' ORDER by idLog DESC LIMIT 15";

$leResultat = db_query($sql, $mysqlMainDb);
$tool_content .= " &nbsp;
    <table width=\"99%\">
       <thead><tr><th>$langLastUserVisits $info[2]</th></tr></thead>
    </table>
    <br>
    <table width=\"99%\">
        <thead><tr><th>$langDate</th><th>$langAction</th></tr></thead>
        <tbody>
         ";
$i = 0;

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
	$tool_content .= "<td>".strftime("%Y-%m-%d %H:%M:%S ", strtotime($when))."</td>
    <td>".$nomAction[$action]."</td></tr>";
	$i++;
}

$tool_content .= "</tbody></table>";

} 
else 
{
    // Αλλιώς... τι γίνεται;
    $tool_content .= "<h1>$langError</h1>\n<p><a href=\"listcours.php\">$langBack</p>\n";
}

$tool_content .= "<center><p><a href=\"listusers.php\">$langBack</a></p></center>";
draw($tool_content,3);

// Unlink chart file - haniotak
if ($made_chart) {
//	ob_flush();
	flush();
	sleep(5);
	unlink($webDir.$chart_path);
}

?>


