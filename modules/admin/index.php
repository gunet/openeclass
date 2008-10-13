<?
 //* @version $Id$
/*****************************************************************************
        DEAL WITH  BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue

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

// Othewise exit with appropriate message
$require_admin = true;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langAdmin;
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
        MAIN BODY
******************************************************************************/

/***************************************/
//This is used for inserting data in 'monthly_report' table.
//The work is done every time the admin page is called in order to
//ensure correct (up-to-date) information on the table.
require_once "summarizeMonthlyData.php";
/****************************************/

$sql = "SELECT code FROM cours";
$result = db_query($sql);
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
        if ($tmp < $first_date_time) {
            $first_date_time = $tmp;

        }
    }
    mysql_free_result($result);

}
$uptime = date("d-m-Y / H:i", $first_date_time);

mysql_select_db($mysqlMainDb);

// Count courses
$a=mysql_fetch_array(db_query("SELECT COUNT(*) FROM cours"));
$a1=mysql_fetch_array(db_query("SELECT COUNT(*) FROM cours WHERE visible='2'"));
$a2=mysql_fetch_array(db_query("SELECT COUNT(*) FROM cours WHERE visible='1'"));
$a3=mysql_fetch_array(db_query("SELECT COUNT(*) FROM cours WHERE visible='0'"));

// Count users
$e=mysql_fetch_array(db_query("SELECT COUNT(*) FROM user"));
$b=mysql_fetch_array(db_query("SELECT COUNT(*) FROM user where statut='1'"));
$c=mysql_fetch_array(db_query("SELECT COUNT(*) FROM user where statut='5'"));
$d=mysql_fetch_array(db_query("SELECT COUNT(*) FROM user where statut='10'"));

// Constract a table with platform identification info
$tool_content .= "
    <table width=\"75%\" class=\"Smart\" align=\"center\" >
    <tbody>
    <tr class=\"odd\">
      <th width=\"30%\" style=\"border-left: 1px solid #edecdf; border-top: 1px solid #edecdf;\">&nbsp;</th>
      <td><b>$langPlatformIdentity</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">Version:</th>
      <td>$langAboutText <b>".$siteName." ".$langEclassVersion."</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">IP Host:</th>
      <td>".$langHostName."<b>".$SERVER_NAME."</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">Web Server:</th>
      <td>".$langWebVersion."<b>".$SERVER_SOFTWARE."</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf; border-bottom: 1px solid #edecdf;\">Data Base Server:</th>
      <td>";
        if (extension_loaded('mysql'))
            $tool_content .= "$langMySqlVersion<b>".mysql_get_server_info()."</b>";
        else // If not display message no MySQL
            $tool_content .= "<font color=\"red\">".$langNoMysql."</font>";
    $tool_content .= "</td>
    </tr>
    </tbody>
    </table>

    <br>";

if ($b[0] == 1)
	$mes_teacher = $langTeacher;
else
	$mes_teacher = $langTeachers;

if ($c[0] == 1)
	$mes_student = $langStudent;
else
	$mes_student = $langStudents;

if ($d[0] == 1)
	$mes_guest= $langGuest;
else
	$mes_guest = $langGuests;

// Constract a table with platform statistical info
$tool_content .= "
    <table width=\"75%\" class=\"Smart\" align=\"center\" >
    <tbody>
    <tr class=\"odd\">
      <th width=\"30%\" style=\"border-left: 1px solid #edecdf; border-top: 1px solid #edecdf;\">&nbsp;</th>
      <td><b>$langStoixeia</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langCoursesHeader:</th>
      <td>".$langAboutCourses." <b>".$a[0]."</b> ".$langCourses."
         <ul>
           <li><b>".$a1[0]."</b> ".$langOpen.",</li>
           <li><b>".$a2[0]."</b> ".$langSemiopen.",</li>
           <li><b>".$a3[0]."</b> ".$langClosed."</i></li>
         </ul>
      </td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langUsers:</th>
      <td>".$langAboutUsers." <b>".$e[0]."</b> ".$langUsersS."
         <ul>
           <li><b>".$b[0]."</b> ".$mes_teacher.",</li>
           <li><b>".$c[0]."</b> ".$mes_student." ".$langAnd."</li>
           <li><b>".$d[0]."</b> ".$mes_guest."</i></li>
         </ul>
      </td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">".$langTotalHits.":</th>
      <td><b>".$totalHits."</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf; border-bottom: 1px solid #edecdf;\">".$langUptime.":</th>
      <td><b>".$uptime."</b></td>
    </tr>
    </tbody>
    </table>

    <br>";

// Count prof requests with status = 1
$sql = "SELECT COUNT(*) AS cnt FROM prof_request WHERE status = 1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$count_prof_requests = $myrow['cnt'];
if ($count_prof_requests > 0) {
    $prof_request_msg = "$langThereAre $count_prof_requests $langOpenRequests";
} else {
    $prof_request_msg = $langNoOpenRequests;
}

// Find last course created
$sql = "SELECT code, intitule, titulaires FROM cours ORDER BY cours_id DESC LIMIT 0,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$last_course_info = "<b>".$myrow['intitule']."</b> (".$myrow['code'].", ".$myrow['titulaires'].")";

// Find last prof registration
$sql = "SELECT prenom, nom, email, registered_at FROM user WHERE statut = 1 ORDER BY user_id DESC LIMIT 0,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$last_prof_info = "<b>".$myrow['prenom']." ".$myrow['nom']."</b> (".$myrow['email'].", ".date("j/n/Y H:i",$myrow['registered_at']).")";

// Find last stud registration
$sql = "SELECT prenom, nom, email, registered_at FROM user WHERE statut = 5 ORDER BY user_id DESC LIMIT 0,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$last_stud_info = "<b>".$myrow['prenom']." ".$myrow['nom']."</b> (".$myrow['email'].", ".date("j/n/Y H:i",$myrow['registered_at']).")";

// Find admin's last login
$sql = "SELECT `when` FROM loginout WHERE id_user = '".$uid."' AND action = 'LOGIN' ORDER BY `when` DESC LIMIT 1,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$lastadminlogin = strtotime($myrow['when']!=""?$myrow['when']:0);

// Count profs registered after last login
$sql = "SELECT COUNT(*) AS cnt FROM user WHERE statut = 1 AND registered_at > '".$lastadminlogin."'";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$lastregisteredprofs = $myrow['cnt'];

// Count studs registered after last login
$sql = "SELECT COUNT(*) AS cnt FROM user WHERE statut = 5 AND registered_at > '".$lastadminlogin."'";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$lastregisteredstuds = $myrow['cnt'];


$tool_content .= "
    <table width=\"75%\" class=\"Smart\" align=\"center\" >
    <tbody>
    <tr class=\"odd\">
      <th width=\"30%\" style=\"border-left: 1px solid #edecdf; border-top: 1px solid #edecdf;\">&nbsp;</th>
      <td><b>$langInfoAdmin</b></td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langOpenRequests:</th>
      <td>".$prof_request_msg."</td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langLastLesson</th>
      <td>$last_course_info</td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langLastProf</th>
      <td>$last_prof_info</td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langLastStud</th>
      <td>$last_stud_info</td>
    </tr>
    <tr class=\"odd\">
      <th class=\"left\" style=\"border-left: 1px solid #edecdf; border-bottom: 1px solid #edecdf;\">$langAfterLastLoginInfo</th>
      <td>$langAfterLastLogin
        <ul>
          <li><b>".$lastregisteredprofs."</b> $langTeachers</li>
          <li><b>".$lastregisteredstuds."</b> $langUsersS </li>
        </ul>
      </td>
    </tr>
    </tbody>
    </table>

    <br>";

/*****************************************************************************
        DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>
