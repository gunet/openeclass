<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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


/*===========================================================================
    stateclass.php
    @last update: 05-07-2006 by Pitsiougas Vagelis
    @authors list: Karatzidis Stratos <kstratos@uom.gr>
               Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Various Statistics

==============================================================================*/

/*****************************************************************************
        DEAL WITH BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langPlatformGenStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
        general statistics
******************************************************************************/
$tool_content .= "
  <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li><a href='platformStats.php?first='>".$langVisitsStats."</a></li>
      <li><a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a></li>
      <li><a href='oldStats.php'>".$langOldStats."</a></li>
      <li><a href='monthlyReport.php'>".$langMonthlyReport."</a></li>
    </ul>
  </div>";

$result = db_query("SELECT code FROM cours");
$course_codes = array();
while ($row = mysql_fetch_assoc($result)) {
    $course_codes[] = $row['code'];
}
mysql_free_result($result);

$first_date_time = time();
$totalHits = 0;

foreach ($course_codes as $course_code) {
    $sql = "SELECT COUNT(*) AS cnt FROM actions";
    $result = db_query($sql, $course_code);
    while ($row = mysql_fetch_assoc($result)) {
        $totalHits += $row['cnt'];
    }
    mysql_free_result($result);

    $sql = "SELECT UNIX_TIMESTAMP(MIN(date_time)) AS first FROM actions";
    $result = db_query($sql, $course_code);
    while ($row = mysql_fetch_assoc($result)) {
        $tmp = $row['first'];
	if (!empty($tmp)) {
		if ($tmp < $first_date_time) {
			$first_date_time = $tmp;
		}
	}
    }
    mysql_free_result($result);
}
$uptime = date("d-m-Y", $first_date_time);

mysql_select_db($mysqlMainDb);

$tool_content .= "
  <table width='99%' align='center'>
  <thead>
  <tr>
    <td>
        <table width='90%' align='center'>
        <thead>
        <tr>
          <td width='49%'>
          <table width='100%' align='center' class=\"stateclass\" >
          <tr>
            <th class='left' colspan='2'><b>$langNbLogin</b></th>
          </tr>
          <tr>
            <td>$langFrom ".list_1Result("SELECT loginout.when FROM loginout ORDER by loginout.when limit 1").": </td>
            <td class='right' width='25%'><b>".list_1Result("SELECT count(*) FROM loginout WHERE loginout.action ='LOGIN'")."</b></td>
          </tr>
          <tr>
            <td>$langLast30Days :</td>
            <td class='right'><b>".list_1Result("SELECT count(*) FROM loginout WHERE action ='LOGIN' AND (loginout.when > DATE_SUB(CURDATE(),INTERVAL 30 DAY))")."</b></td>
          </tr>
          <tr>
            <td>$langLast7Days :</td>
            <td class='right'><b>".list_1Result("SELECT count(*) FROM loginout WHERE action ='LOGIN' AND (loginout.when > DATE_SUB(CURDATE(),INTERVAL 7 DAY))")."</b></td>
          </tr>
          <tr>
            <td>$langToday :</td>
            <td class='right'><b>".list_1Result("SELECT count(*) FROM loginout WHERE action ='LOGIN' AND (loginout.when > curdate())")."</b></td>
          </tr>
	<tr>
	<td>$langTotalHits :</td>
      	<td class='right'><b>$totalHits</td>
    </tr>
    <tr>
	<td>$langUptime :</td>
	<td class='right'><b>$uptime</b></td>
    </tr>
     </table>
      </td>
      <td width=2%>&nbsp;</td>";
$tool_content .= "<td width=49%>
          <table width=100% align=center class=\"stateclass\" >
          <tr>
            <th class='left' colspan='2'><b>$langUsers</th>
          </tr>
          <tr>
            <td>$langNbProf :</td>
            <td class='right' width='25%'><b>".list_1Result("SELECT count(*) FROM user WHERE statut = 1;")."</b></td>
          </tr>
          <tr>
            <td>$langNbStudents :</td>
            <td class='right'><b>".list_1Result("SELECT count(*) FROM user WHERE statut = 5;")."</b></td>
          </tr>
          <tr>
            <td>$langNumGuest :</td>
            <td class='right'><b>".list_1Result("SELECT count(*) FROM user WHERE statut = 10;")."</b></td>
          </tr>
 	    <td>$langTotal :</td>
            <td class='right'><b>".list_1Result("SELECT count(*) FROM user;")."</b></td>
          </tr>
          </table>
        </td>
      </tr>
      </thead>
      </table>
      <br />";


// Constract some tables with statistical information
$tool_content .= "
    <table width='99%' align='center'>
    <thead>
    <tr>
      <td>
        <table width='90%' align='center'>
        <thead>
        <tr>
          <td colspan='3'><b>$langStatCour</b></td>
        </tr>
        <tr>
          <td valign='top' width='49%'>
          <table width='100%' align=center class=\"stateclass\">
          <tr>
            <th class=\"left\" colspan='2'><b>$langCoursesHeader</b></th>
          </tr>
          <tr>
            <td class=\"left\">$langNumCourses : </td>
            <td class=\"right\"><b>".list_1Result("SELECT count(*) FROM cours")."</b></td>
          </tr>
  	</table>
	<table width='100%' align='center' class=\"stateclass\">
          <tr>
            <th class='left' colspan='2'><b>$langNunEachAccess</b></th>
          </tr>".tablize(list_ManyResult("SELECT DISTINCT visible, count(*) FROM cours GROUP BY visible "), $language)."
          </table>

          <br />

          <table width='100%' align='center' class=\"stateclass\">
          <tr>
            <th class='left' colspan='2'><b>$langNumEachCourse</b></th>
          </tr>".tablize(list_ManyResult("SELECT DISTINCT faculte, count(*) FROM cours Group by faculte"), $language)."
          </table>";

$tool_content .= "
          <br />

          <table width='100%' align='center' class=\"stateclass\">
          <tr>
            <th class='left' colspan='2'><b>$langNumEachLang</b></th>
          </tr>".tablize(list_ManyResult("SELECT DISTINCT languageCourse, count(*) FROM cours Group by languageCourse "), $language)."
          </table>

          <br />

          <table width='100%' align='center' class=\"stateclass\">
          <tr>
            <th class='left' colspan='2'><b>$langNumEachCat</b></th></tr>
          <tr>".tablize(list_ManyResult("SELECT DISTINCT type, count(*) FROM cours GROUP BY type"),$language)."
          </table>

          <br />

          <table width='100%' align='center' class=\"stateclass\">
          <tr>
            <th class='left' colspan='2'><b>$langAnnouncements</b></th>
          </tr>
          <tr>
            <td class='left'>$langNbAnnoucement :</td>
            <td class='right'><b>".list_1Result("SELECT count(*) FROM annonces;")."</b></td>
          </tr>
          </table>
          <br />
          </td>

          <td width='2%'>&nbsp;</td>

          <td valign=top width='49%' align=center>
          <table width='100%' align='center' class=\"stateclass\">
          <tr>
            <th class='left' colspan='2'><b>$langNumEachRec</b></td>
          </tr>".tablize(list_ManyResult("SELECT CONCAT(code_cours,\" Statut :\",statut), count(user_id) FROM cours_user GROUP BY code_cours, statut ORDER by code_cours"), $language)."
          </table>

          </td>
        </tr>
        </thead>
        </table>";

$tool_content .= "</td></tr></thead></table>";

$tool_content .= "<table width='99%' align='center'>
    <thead>
    <tr>
      <td colspan='2' width=90%><b>$langAlert !</b></td>
    </tr>
    <tr>
      <td>
        <table width='100%' align='center' class=\"stateclass\">";

$sqlLoginDouble = "SELECT DISTINCT username, count(*) as nb FROM user group by username HAVING nb > 1 ORDER by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);

$tool_content .= "
        <tr>
          <th><b>$langMultipleUsers :</b></th>
          <th class=right><strong>$langResult</strong></th>
        </tr>
        <tr>
          <td colspan='2'><small><i>(".$sqlLoginDouble.")</i></small></td>
        </tr>
        <tr>
          <td>";

if (count($loginDouble) > 0) {
        $tool_content .= tablize($loginDouble, $language);
      $tool_content .=  "</td>
          <td class='right' align=center>".error_message()." ";
} else {
        $tool_content .= "</td>
          <td class='right' align=center>".ok_message()." ";
}
$tool_content .= "</td></tr></table></td></tr>";

$sqlLoginDouble = "SELECT DISTINCT email, count(*) as nb FROM user group by email HAVING nb > 1  ORDER by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);

$tool_content .= "
      <tr>
        <td>
        <table width='100%' align='center' class=\"stateclass\">
        <tr>
          <th><b>$langMultipleAddr e-mail:</b></th>
          <th class='right'><strong>$langResult</strong></th>
        </tr>
        <tr>
          <td colspan='2'><small><i>(".$sqlLoginDouble.")</i></small></td>
        </tr>";

if (count($loginDouble) > 0) {
 $tool_content .= tablize($loginDouble, $language);
 $tool_content .= "<tr><td class=right colspan='2'>";
 $tool_content .=  error_message();
 $tool_content .= "</tr>";
}
else
{
 $tool_content .=  "
        <tr>
          <td class=right colspan='2'>";
 $tool_content .=  ok_message();
 $tool_content .= "
        </tr>";
}
$tool_content .= "
        </table>";
$tool_content .=  "
        </td>
      </tr>";

$sqlLoginDouble = "SELECT DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb FROM user group by paire HAVING nb > 1 ORDER by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);

$tool_content .= "
      <tr>
        <td>
        <table width='100%' align='center' class=\"stateclass\">
        <tr>
          <th><b>$langMultiplePairs LOGIN - PASS</b></th>
          <th class='right'><b>$langResult</b></td>
        </tr>
        <tr>
          <td colspan='2'><small><i>(".$sqlLoginDouble.")</i></small></td>
        </tr>";

if (count($loginDouble) > 0) {
	$tool_content .=  tablize($loginDouble, $language);
	$tool_content .= "
        <tr>
          <td class=right colspan='2'>";
	$tool_content .= error_message();
	$tool_content .= "
          </td>
        </tr>";
} else {
	$tool_content .= "
        <tr>
          <td class=right colspan='2'>";
	$tool_content .= ok_message();
	$tool_content .= "
          </td>
        </tr>";
}
$tool_content .= "</table></td></tr></table>";
$tool_content .= "</td></tr>
    <tr>
      <td colspan=2 class='right'><a href='index.php' class=mainpage>$langBackAdmin</a></td>
    </tr>
    </table>";


/*
 * output an <Table> with an array
 *
 * @return void
 * @param  array $tableau arrey to output
 * @desc output an <Table> with an array
 */

function tablize($tableau, $lang) {

	global $langClosed, $langTypesRegistration, $langOpen, $langStudents, $langGuest,
   		 $langPre, $langPost, $langOther, $langEnglish, $langGreek, $langTeachers;

    $ret = "";
	if (is_array($tableau)) {
    	while ( list( $key, $laValeur ) = each($tableau)) {
			$ret .= "\n          <tr>\n";
			switch ($key) {
				case '0': $key = $langClosed; break;
				case '1'; $key = $langTypesRegistration; break;
				case '2': $key = $langOpen; break;
				case '5': $key = $langStudents; break;
				case '10': $key = $langGuest; break;
				case 'pre': $key = $langPre; break;
				case 'post': $key = $langPost; break;
				case 'other': $key = $langOther; break;
				case 'english': $key = $langEnglish; break;
				case 'greek': $key = $langGreek; break;
			}
			if (strpos($key, 'Statut :10')) $key = substr_replace($key, $langGuest, strlen($key)-10);
			if (strpos($key, 'Statut :1')) $key = substr_replace($key, $langTeachers, strlen($key)-9);
			if (strpos($key, 'Statut :5')) $key = substr_replace($key, $langStudents, strlen($key)-9);
			$ret .= "            <td style=\"font-size: 90%\">".$key."</td>\n";
			$ret .= "            <td class=\"right\"><strong>".$laValeur."</strong></td>\n";
			$ret .= "          </tr>";
		}
	}
	return $ret;
}

function ok_message() {

	global $langNotExist;

	return " <b><span style=\"color: #00FF00\">$langNotExist</span></b>";
}

function error_message() {
	global $langExist;

	return " <b><span style=\"color: #FF0000\">$langExist</span></b>";
}


function list_1Result($sql) {
	global $db;

	$res = mysql_query($sql ,$db);
	$res = mysql_fetch_array($res);
	return $res[0];
}

function list_ManyResult($sql) {
	global $db;
	$resu=array();

	$res =db_query($sql ,$db);
	while ($resA = mysql_fetch_array($res))
	{
		$resu[$resA[0]]=$resA[1];
	}
	return $resu;
}

/*****************************************************************************
        DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>
