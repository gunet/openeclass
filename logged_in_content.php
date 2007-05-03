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
 * Logged In Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component creates the content of the start page when the 
 * user is logged in
 * 
 */


 /*
  * Make table with general platform information
  * ophelia neofytou - 2006/09/26
  */
/* @include("./modules/lang/$language/admin.inc.php");
 @include("./modules/lang/$language/about.inc.php");

//find uptime
$sql = "SELECT code FROM cours";
$result = db_query($sql);
$course_codes = array();
while ($row = mysql_fetch_assoc($result)) {
    $course_codes[] = $row['code'];
}
mysql_free_result($result);

$first_date_time = time();

foreach ($course_codes as $course_code) {
    $sql = "SELECT UNIX_TIMESTAMP(MIN(date_time)) AS first FROM actions";
    $result = db_query($sql, $course_code);
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

//count courses
$a=mysql_fetch_array(db_query("SELECT COUNT(*) FROM cours"));
$a1=mysql_fetch_array(db_query("SELECT COUNT(*) FROM cours WHERE visible='2'"));
$a2=mysql_fetch_array(db_query("SELECT COUNT(*) FROM cours WHERE visible='1'"));
$a3=mysql_fetch_array(db_query("SELECT COUNT(*) FROM cours WHERE visible='0'"));

// Count users
$e=mysql_fetch_array(db_query("SELECT COUNT(*) FROM user"));
$b=mysql_fetch_array(db_query("SELECT COUNT(*) FROM user where statut='1'"));
$c=mysql_fetch_array(db_query("SELECT COUNT(*) FROM user where statut='5'"));
$d=mysql_fetch_array(db_query("SELECT COUNT(*) FROM user where statut='10'"));
 
$tool_content .= "<table width=\"99%\"><thead>";
$tool_content .= "<tr><th>".$langInfo."</th></tr></thead><tbody>".
     "<tr class=\"odd\"><td>"."<p>".$langAboutCourses." <b>".$a[0]."</b> ".$langCourses."(<i><b>".$a1[0].
        "</b> ".$langOpen.", <b>".$a2[0]."</b> ".$langSemiopen.", <b>".$a3[0]."</b> ".$langClosed."</i>)</p>".
        "<p>".$langAboutUsers." <b>".$e[0]."</b> ".$langUsers." (<i><b>".$b[0]."</b> ".$langProf.", <b>".$c[0]."</b> ".$langStud." ".$langAnd." <b>".$d[0]."</b> ".$langGuest."</i>)</p>".
        "<p>".$langUptime.": <b>".$uptime."</b></p>".
        "<p>".$langLast30daysLogins.": <b>".$total_logins[0]."</b></p>";
           
 $tool_content .= "</td></tr></tbody></table><br>";
###### end of table with platform information
 */

$tool_content .= "<table cellpadding='4' width='100%' border='0' cellspacing='0'>";
$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
		FROM cours, cours_user WHERE cours.code=cours_user.code_cours AND cours_user.user_id='".$uid."'
		AND (cours_user.statut='5' OR cours_user.statut='10')");
if (mysql_num_rows($result2) > 0) {
			$tool_content .= "<tr><td><script type='text/javascript' src='modules/auth/sorttable.js'></script>
		      <table width='100%' border='0' cellpadding='0' cellspacing='1' valign=middle align=center style='border: 1px solid #DCDCDC;'>
          <tr><td>
          <table width='100%' border='0' cellpadding='0' cellspacing='0' height=30 valign=middle align=center style='border: 1px solid #DCDCDC;'>
          <tr><td class=color1 valign=middle style='border: 1px solid #F1F1F1;'><b>$langMyCoursesUser</b></td>
           </tr></table></td></tr>";

			$tool_content .= "<tr><td>
            <table width='100%' class='sortable' id='t1' border='0' cellpadding='0' cellspacing='0' align=center style='border: 1px solid #F1F1F1;'>
            <tr><td class='td_small_HeaderRow' align='left' width='65%'>$langCourseCode</td>
                <td class='td_small_HeaderRow' align='left' width='30%'>$langProfessor</td>
                <td class='td_small_HeaderRow' align='center' width='5%'>$langUnCourse</td>
               </tr>";

// display courses
while ($mycours = mysql_fetch_array($result2)) {
         $dbname = $mycours["k"];
         $status[$dbname] = $mycours["s"];
         $tool_content .= "<tr onMouseOver=\"this.style.backgroundColor='#F5F5F5'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
         $tool_content .= "<td class=kkk height=25>
                <a href='${urlServer}courses/$mycours[k]' class=CourseLink>$mycours[i]</a>
                <span class='explanationtext'><font color=#4175B9>$mycours[c]</font></span>
                </td><td class=kkk><span class='explanationtext'>$mycours[t]</span></td> 
                <td align=center><a href='${urlServer}modules/unreguser/unregcours.php?cid=$mycours[c]&u=$uid'>
								<img src='images/cunregister.gif' border='0' title='$langUnregCourse'></a></td>
                </tr>";
         }
				$tool_content .= "</table>";

 }  else  {
           if ($_SESSION['statut'] == '5')  // if we are login for first time
           $tool_content .= "<tr><td>$langWelcomeStud</td></tr>\n";
} // end of if (if we are student)

// second case check in which courses are registered as a professeror
     $result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s FROM cours, cours_user WHERE cours.code=cours_user.code_cours 
				AND cours_user.user_id='".$uid."' AND cours_user.statut='1'");
        if (mysql_num_rows($result2) > 0) {
         $tool_content .= "<tr><td>
              <script type='text/javascript' src='modules/auth/sorttable.js'></script>
              <table width='100%' border='0' cellpadding='0' cellspacing='1' valign=middle align=center style='border:1px solid #DCDCDC;'>
              <tr><td>
              <table width='100%' border='0' cellpadding='0' cellspacing='0' height=30 valign=middle align=center style='border: 1px solid #iDCDCDC;'>
              <tr><td class=color1 valign=middle style='border: 1px solid #F1F1F1;'><b>$langMyCoursesProf</b></td></tr>
  						</table></td></tr>
              <tr><td>
            	<table width='100%' class='sortable' id='t1' border='0' cellpadding='0' cellspacing='0' align=center style='border: 1px solid #F1F1F1;'>
                <tr>
     						<td class='td_small_HeaderRow' align='left' width='65%'>$langCourseCode</td>
                <td class='td_small_HeaderRow' align='left' width='30%'>$langProfessor</td>
                <td class='td_small_HeaderRow' align='center' width='5%'>$langManagement</td>
               </tr>";

while ($mycours = mysql_fetch_array($result2)) {
             $dbname = $mycours["k"];
             $status[$dbname] = $mycours["s"];
	           $tool_content .= "<tr onMouseOver=\"this.style.backgroundColor='#F5F5F5'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
             $tool_content .= "<td class=kkk height=26><a class='CourseLink' href='${urlServer}courses/$mycours[k]'>$mycours[i]</a><span class='explanationtext'><font color=#4175B9>($mycours[c])</font></span></td>
           <td class=kkk><span class='explanationtext'>$mycours[t]</span></td>
           <td align=center valign=middle>
           <a href='${urlServer}modules/course_info/infocours.php?from_home=TRUE&cid=$mycours[c]'>
           <img src='images/referencement.gif' border=0 title='$langManagement' align='absbottom'></img></a>
           </td></tr>";
        }
	$tool_content .= '</table>';
}  else {
         if ($_SESSION['statut'] == '1')  // if we are loggin for first time
         $tool_content .= "<tr><td>$langWelcomeProf</td></tr>\n";
} // if

session_register('status');
?>
