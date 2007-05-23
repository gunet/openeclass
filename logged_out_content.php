<?PHP
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**
 * Logged Out Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component creates the content of the index page when the 
 * user is not logged in
 * It includes: 
 * 1. The login form, 
 * 2. an optional content below the login form, 
 * 3. The introductory message
 * 4. Platform announcements (If there are any)
 *
 */

//query for greek announcements
$sql_el ="SELECT `date`, `gr_title` , `gr_body` , `gr_comment`
		FROM `admin_announcements`
		WHERE `visible` = \"V\"
		";
//query for english announcements
$sql_en ="SELECT `date`, `en_title` , `en_body` , `en_comment`
		FROM `admin_announcements`
		WHERE `visible` = \"V\"
		";

if(session_is_registered('langswitch')) {
	$language = $_SESSION['langswitch'];
}

if ($language == "greek") $sql = $sql_el;
else $sql = $sql_en;

$tool_content .= <<<lCont
<div id="container_login">

<div id="wrapper">
<div id="content_login">
<p>$langInfo</p>
lCont;

$tool_content .='<br>';

 /*
  * Make table with general platform information
  * ophelia neofytou - 2006/09/26
  */
 @include("./modules/lang/$language/admin.inc.php");
 @include("./modules/lang/$language/about.inc.php");
/*
//find uptime
$sql_stats = "SELECT code FROM cours";
$result = db_query($sql_stats);
$course_codes = array();
while ($row = mysql_fetch_assoc($result)) {
    $course_codes[] = $row['code'];
}
mysql_free_result($result);

$first_date_time = time();

foreach ($course_codes as $course_code) {
    $sql_stats = "SELECT UNIX_TIMESTAMP(MIN(date_time)) AS first FROM actions";
    $result = db_query($sql_stats, $course_code);
    while ($row = mysql_fetch_assoc($result)) {
        $tmp = $row['first'];
        if ($tmp < $first_date_time and $tmp !=0 ) {
            $first_date_time = $tmp;

        }
    }
    mysql_free_result($result);

}
$uptime = date("Y-m-d H:i:s", $first_date_time);

//find number of logins
mysql_select_db($mysqlMainDb);
$lastMonth = date("Y-m-d H:i:s", time()-24*3600*30);
$total_logins = mysql_fetch_array(db_query("SELECT COUNT(idLog) FROM loginout WHERE action='LOGIN' AND `when`> '$lastMonth'"));

//$tool_content .= "<table width=\"99%\"><thead>";
$tool_content .= "<p>".$langAboutCourses1." <b>".$a[0]."</b> ".$langCourses." (<i><b>".$a1[0].
        "</b> ".$langOpen.", <b>".$a2[0]."</b> ".$langSemiopen.", <b>".$a3[0]."</b> ".$langClosed."</i>). ".
        $langAboutUsers1." <b>".$e[0]."</b> (<i><b>".$b[0]."</b> ".$langProf.", <b>".$c[0]."</b> ".$langStud." ".$langAnd." <b>".$d[0]."</b> ".$langGuest."</i>). ".

        $langUptime." <b>".$uptime."</b> ".
        $langLast30daysLogins1." <b>".$total_logins[0]."</b>.<p>";
*/
// $tool_content .= "</td></tr></tbody></table><br>";
###### end of table with platform information



$result = db_query($sql, $mysqlMainDb);
if (mysql_num_rows($result) > 0) {
	$announceArr = array();
	while ($eclassAnnounce = mysql_fetch_array($result)) {
		array_push($announceArr, $eclassAnnounce);
	}

	$tool_content .= "
<br/>

<table width=\"99%\">
	<thead>
		<tr>
			<th> $langPlatformAnnounce </th>
		</tr>
	</thead>
	<tbody>";


	$numOfAnnouncements = count($announceArr);

	for($i=0; $i < $numOfAnnouncements; $i++) {

		if ($i%2 == 0) $rowClass = "class=\"odd\"";
		else $rowClass = "";

		$tool_content .= "
		<tr $rowClass>
			<td>
				<p><b>".$announceArr[$i][0].":</b> <u>".$announceArr[$i][1]."</u></p>
				<p>".$announceArr[$i][2]."</p>
				<p><i>".$announceArr[$i][3]."</i></p>
			</td>
		</tr>
		";

	}

	$tool_content .= "
			</tbody>
		</table>";
}
$tool_content .= <<<lCont2
</div>
</div>
<div id="navigation">

 <table width="99%">
      <thead>
      	<tr>
      		<th> $langUserLogin </th>
      	</tr>
      </thead>
      <tbody>
      	<tr class="odd">
      		<td>
      			<form action="index.php" method="post">
      		  $langUserName <br>
        			<input  name="uname" size="20"><br>
       			 $langPass <br>
        			<input name="pass" type="password" size="20"><br><br>
       			 <input value="$langEnter" name="submit" type="submit"><br>
				$warning<br>
				<a href="modules/auth/lostpass.php">$lang_forgot_pass</a>
     			 </form>
     		</td>
     	</tr>
      </tbody>
      </table>


</div>
<div id="extra">
<p>{ECLASS_HOME_EXTRAS_RIGHT}</p>
</div>

</div>

lCont2;



?>
