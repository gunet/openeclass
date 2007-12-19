<?
 //* @version $Id$
/*****************************************************************************
        DEAL WITH  BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue
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
$tool_content .= "<p><b>$langPlatformIdentity</b></p>
<ul class=\"listBullet\">
<li>$langAboutText <b>".$siteName." ".$langEclassVersion."</b></li>
<li>".$langHostName."<b>".$SERVER_NAME."</b></li>
<li>".$langWebVersion."<b>".$SERVER_SOFTWARE."</b></li>";
// Check if we have mysql database to display its information
if (extension_loaded('mysql'))
    $tool_content .= "<li>$langMySqlVersion<b>".mysql_get_server_info()."</b></li>";
else // If not display message no MySQL
    $tool_content .= "<li font color=\"red\">".$langNoMysql."</li>";
$tool_content .= "</ul>";

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
$tool_content .= "<p><b>$langStoixeia</b><p>
<ul class=\"listBullet\">
<li>".$langAboutCourses." <b>".$a[0]."</b> ".$langCourses." (<i><b>".$a1[0]."</b> ".$langOpen.", <b>".$a2[0]."</b> ".$langSemiopen.", <b>".$a3[0]."</b> ".$langClosed."</i>)</li>
<li>".$langAboutUsers." <b>".$e[0]."</b> ".$langUsersS." (<i><b>".$b[0]."</b> ".$mes_teacher.", <b>".$c[0]."</b> ".$mes_student." ".$langAnd." <b>".$d[0]."</b> ".$mes_guest."</i>)</li>
<li>".$langTotalHits.": <b>".$totalHits."</b></li>
<li>".$langUptime.": <b>".$uptime."</b></li></ul>";

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
$last_course_info = "<b>".$myrow['intitule']."</b> <i>(".$myrow['code'].", ".$myrow['titulaires'].")</i>";

// Find last prof registration
$sql = "SELECT prenom, nom, email, registered_at FROM user WHERE statut = 1 ORDER BY user_id DESC LIMIT 0,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$last_prof_info = "<b>".$myrow['prenom']." ".$myrow['nom']."</b> <i>(".$myrow['email'].", ".date("j/n/Y H:i",$myrow['registered_at']).")</i>";

// Find last stud registration
$sql = "SELECT prenom, nom, email, registered_at FROM user WHERE statut = 5 ORDER BY user_id DESC LIMIT 0,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$last_stud_info = "<b>".$myrow['prenom']." ".$myrow['nom']."</b> <i>(".$myrow['email'].", ".date("j/n/Y H:i",$myrow['registered_at']).")</i>";

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

$tool_content .= "<p><b><caption>$langInfoAdmin</b></p>
<ul class=\"listBullet\">
<li>$langOpenRequests: <b>".$prof_request_msg."</b></li>
<li>$langLastLesson $last_course_info</li>
<li>$langLastProf $last_prof_info</li>
<li>$langLastStud $last_stud_info</li>
<li>$langAfterLastLogin <i><b>".$lastregisteredprofs."</b> $langTeachers<b> ".$lastregisteredstuds."</b> $langUsersS </i></li>";

/*****************************************************************************
        DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>
