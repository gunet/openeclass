<?PHP
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	Á full copyright notice can be read in "/info/copyright.txt".
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
 @include("./modules/lang/$language/admin.inc.php");
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
 

$tool_content .= "<table width=\"99%\"><thead>";
$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
		FROM cours, cours_user WHERE cours.code=cours_user.code_cours AND cours_user.user_id='".$uid."'
		AND (cours_user.statut='5' OR cours_user.statut='10')");
if (mysql_num_rows($result2) > 0) {
	$tool_content .=  '<tr><th>'.$langMyCoursesUser.'</th></tr>';

	$tool_content .= "</thead><tbody>";
	$i=0;
	// SHOW COURSES
	while ($mycours = mysql_fetch_array($result2)) {
		$dbname = $mycours["k"];
		$status[$dbname] = $mycours["s"];
		if ($i%2==0) $tool_content .=  '<tr>';
		elseif($i%2==1) $tool_content .= '<tr class="odd">';
		$tool_content .= '<td>
			<a href="courses/'.$mycours['k'].'/">'.$mycours['i'].'</a>
			<br>'.$mycours['t'].'<br>'.$mycours['c'].'
			</td>
			</tr>';
		$i++;
	}	// while
} // end of if

$tool_content .= "</tbody></table><br>";

$tool_content .= "<table width=\"99%\"><thead>";
// second check: Get all the course that are administered by the current user (professor)
$result2 = mysql_query("SELECT cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t, cours_user.statut s
        	FROM cours, cours_user WHERE cours.code=cours_user.code_cours 
		AND cours_user.user_id='".$uid."' AND cours_user.statut='1'");
if (mysql_num_rows($result2) > 0) {
	$tool_content .= '<tr><th>'.$langMyCoursesProf.'</th></tr>';
	$tool_content .= "</thead><tbody>";
	$i=0;
	while ($mycours = mysql_fetch_array($result2)) {
		$dbname = $mycours["k"];
		$status[$dbname] = $mycours["s"];
		if ($i%2==0) $tool_content .= '<tr>';
		elseif($i%2==1) $tool_content .= '<tr class=\"odd\">';
		$tool_content .= '<td>
                        <a href="'.$urlServer."courses/".$mycours['k'].'/">'.$mycours['i'].'</a>
                        <br>'.$mycours['t'].'<br>'.$mycours['c'].'
                        </td>
                        </tr>';
		$i++;
	}       // while
} // if
$tool_content .= '</tbody></table>';
session_register('status');

?>
