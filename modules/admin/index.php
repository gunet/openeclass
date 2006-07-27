<?php
/*****************************************************************************
        DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = array('gunet','admin','about');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
check_admin();
// Define $nameTools
$nameTools = $langAdmin;
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
        MAIN BODY
******************************************************************************/



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
$uptime = date("Y-m-d H:i:s", $first_date_time);

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
$tool_content .= "<table width=\"99%\"><caption>Ταυτότητα Πλατφόρμας</caption><tbody><tr><td>
<p>$langAboutText <b>".$siteName." ".$langEclassVersion."</b></p>
<p>".$langHostName."<b>".$SERVER_NAME."</b></p>
<p>".$langWebVersion."<b>".$SERVER_SOFTWARE."</b></p>";
// Check if we have mysql database to display its information
if (extension_loaded('mysql'))
    $tool_content .= "<p>$langMySqlVersion<b>".mysql_get_server_info()."</b></p>";
else // If not display message no MySQL
    $tool_content .= "<p font color=\"red\">".$langNoMysql."</p>";
$tool_content .= "</td></tr></tbody></table><br>";

// Constract a table with platform statistical info
$tool_content .= "<table width=\"99%\"><caption>Στοιχεία Πλατφόρμας</caption><tbody><tr><td>
<p>".$langAboutCourses." <b>".$a[0]."</b> ".$langCourses." (<i><b>".$a1[0]."</b> ".$langOpen.", <b>".$a2[0]."</b> ".$langSemiopen.", <b>".$a3[0]."</b> ".$langClosed."</i>)</p>
<p>".$langAboutUsers." <b>".$e[0]."</b> ".$langUsers." (<i><b>".$b[0]."</b> ".$langProf.", <b>".$c[0]."</b> ".$langStud." ".$langAnd." <b>".$d[0]."</b> ".$langGuest."</i>)</p>
<p>".$langTotalHits.": <b>".$totalHits."</b></p>
<p>".$langUptime.": <b>".$uptime."</b></p>";

$tool_content .= "</td></tr></tbody></table><br>";

// Count prof requests with status = 1
$sql = "SELECT COUNT(*) AS cnt FROM prof_request WHERE status = 1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$count_prof_requests = $myrow['cnt'];
if ($count_prof_requests > 0) {
    $prof_request_msg = "Υπάρχουν ".$count_prof_requests." ανοικτές αιτήσεις καθηγητών";
} else {
    $prof_request_msg = "Δεν βρέθηκαν ανοικτές αιτήσεις καθηγητών";
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

$tool_content .= "<table width=\"99%\"><caption>Ενημερωτικά Στοιχεία για τον Διαχειριστή</caption><tbody><tr><td>
<p>Ανοικτές αιτήσεις καθηγητών: <b>".$prof_request_msg."</b></p>
<p>Τελευταίο μάθημα που δημιουργήθηκε: ".$last_course_info."</p>
<p>Τελευταία εγγραφή εκπαιδευτή: ".$last_prof_info."</p>
<p>Τελευταία εγγραφή εκπαιδευομένου: ".$last_stud_info."</p>
<p>Μετά το τελευταίο σας login έχουν εγγραφεί στην πλατφόρμα: <i><b>".$lastregisteredprofs."</b> Εκπαιδευτές και <b>".$lastregisteredstuds."</b> Εκπαιδευόμενοι</i></p>";

$tool_content .= "</td></tr></tbody></table><br>";

/*****************************************************************************
        DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>