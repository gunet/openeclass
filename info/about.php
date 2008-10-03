<?
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

$path2add=2;
include '../include/baseTheme.php';
$nameTools = $langInfo;
$tool_content ="";


    $tool_content .= "
    <p>$langIntro</p>
    <br>

          <table width=\"600\" class=\"Smart\" align=\"center\" >
          <tbody>
          <tr class=\"odd\">
            <th width=\"160\" style=\"border-left: 1px solid #edecdf; border-top: 1px solid #edecdf;\">&nbsp;</th>
            <td><b>$langStoixeia</b></td>
          </tr>
          <tr class=\"odd\">
            <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$l_version:</th>
            <td>$langAboutText:&nbsp;<b>$siteName $langEclassVersion</b>&nbsp;&nbsp;(<a href='http://portal.eclass.gunet.gr/' title='Portal eClass' target='_blank' border=0>>></a>)</td>
          </tr>
          <tr class=\"odd\">
            <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langCoursesHeader:</th>
            <td>";

/*
  * Make table with general platform information
  * ophelia neofytou - 2006/09/26
  */

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

$uptime = date("G:i d-n-Y", $first_date_time);

//find number of logins
mysql_select_db($mysqlMainDb);
$lastMonth = date("Y-m-d H:i:s", time()-24*3600*30);
$total_logins = mysql_fetch_array(db_query("SELECT COUNT(idLog) FROM loginout
													WHERE action='LOGIN' AND `when`> '$lastMonth'"));

$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours"));
$a1=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='2'"));
$a2=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='1'"));
$a3=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='0'"));

$tool_content .= "$langAboutCourses <b>$a[0]</b> $langCourses<br>";
$tool_content .= "
             <ul>
                <li><b>$a1[0]</b> $langOpen,</li>
                <li><b>$a2[0]</b> $langSemiopen,</li>
                <li><b>$a3[0]</b> $langClosed </li>
             </ul>
            </td>
          </tr>";


$e=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user"));
$b=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='1'"));
$c=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='5'"));
$d=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='10'"));

    $tool_content .= "
          <tr class=\"odd\">
            <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langUsers:</th>
            <td>$langAboutUsers <b>$e[0]</b> $langUsers
              <ul>
                <li><b>$b[0]</b> $langTeachers, </li>
                <li><b>$c[0]</b> $langStudents $langAnd </li>
                <li><b>$d[0]</b> $langGuest </li>
              </ul></td>
          </tr>
          <tr class=\"odd\">
            <th class=\"left\" style=\"border-left: 1px solid #edecdf;\">$langOperation:</th>
            <td>$langUptime<b> ".$uptime."</b> $langLast30daysLogins1 <b>".$total_logins[0]."</b>.</td>
          </tr>
          <tr class=\"odd\">
            <th class=\"left\" style=\"border-left: 1px solid #edecdf; border-bottom: 1px solid #edecdf;\">$langSupportUser</th>
            <td>$administratorName $administratorSurname</td>
          </tr>
          </tbody>
          </table>

          <br>";


if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}
